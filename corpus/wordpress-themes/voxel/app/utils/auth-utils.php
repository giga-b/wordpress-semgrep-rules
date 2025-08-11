<?php

namespace Voxel\Auth;

if ( ! defined('ABSPATH') ) {
	exit;
}

function validate_registration_fields( \Voxel\Role $role, $postdata ) {
	$fields = $role->get_fields();
	$sanitized = [];
	$errors = [];

	// store sanitized values
	foreach ( $fields as $field ) {
		if ( $field instanceof \Voxel\Membership\Fields\Base_Membership_Field ) {
			unset( $fields[ $field->get_key() ] );
			continue;
		}

		if ( ! isset( $postdata[ $field->get_key() ] ) ) {
			$sanitized[ $field->get_key() ] = null;
		} else {
			$sanitized[ $field->get_key() ] = $field->sanitize( $postdata[ $field->get_key() ] );
		}
	}

	// run conditional logic and remove fields that don't pass conditions
	foreach ( $fields as $field_key => $field ) {
		if ( ! $field->passes_conditional_logic( $sanitized ) ) {
			unset( $fields[ $field_key ] );
		}
	}

	// run validations on sanitized value
	foreach ( $fields as $field ) {
		try {
			$value = $sanitized[ $field->get_key() ];
			$field->check_validity( $value );
		} catch ( \Exception $e ) {
			$errors[] = $e->getMessage();
		}
	}

	if ( ! empty( $errors ) ) {
		throw new \Exception( join( '<br>', $errors ) );
	}

	return [
		'fields' => $fields,
		'sanitized' => $sanitized,
	];
}

function save_registration_fields( $user, $fields, $sanitized ) {
	$profile = $user->get_or_create_profile();

	if ( ! empty( $sanitized['title'] ) && is_string( $sanitized['title'] ) ) {
		wp_update_post( [
			'ID' => $profile->get_id(),
			'post_title' => $sanitized['title'],
		] );
	}

	foreach ( $fields as $field ) {
		$field->set_post( $profile );
		$field->update( $sanitized[ $field->get_key() ] );
	}

	// clean user and profile cache
	\Voxel\Post::force_get( $profile->get_id() );
	\Voxel\User::force_get( $user->get_id() );
}

function send_confirmation_code( $email, $code = null ) {
	global $wpdb;

	if ( $code === null ) {
		$code = \Voxel\random_string(5);
	}

	$subject = _x( 'Account confirmation', 'auth', 'voxel' );
	$message = sprintf( _x( 'Your confirmation code is %s', 'auth', 'voxel' ), $code );

	// store in db
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}voxel_auth_codes WHERE user_login = %s", $email ) );
	$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}voxel_auth_codes (`user_login`, `code`, `created_at`) VALUES (%s, %s, %s)", $email, $code, date( 'Y-m-d H:i:s', time() ) ) );

	// send email
	wp_mail( $email, $subject, \Voxel\email_template( $message ), [
		'Content-type: text/html;',
	] );
}

function verify_confirmation_code( $email, $code ) {
	global $wpdb;

	$code = $wpdb->get_row( $wpdb->prepare( <<<SQL
		SELECT `created_at` FROM {$wpdb->prefix}voxel_auth_codes
		WHERE `user_login` = %s AND `code` = %s
	SQL, $email, $code ) );

	if ( ! $code ) {
		throw new \Exception( __( 'Code verification failed.', 'voxel' ) );
	}

	$created_at = strtotime( $code->created_at ?? '' );
	if ( ! $created_at ) {
		throw new \Exception( __( 'Please try again.', 'voxel' ) );
	}

	if ( ( $created_at + ( 10 * MINUTE_IN_SECONDS ) ) < time() ) {
		throw new \Exception( __( 'Please try again.', 'voxel' ) );
	}

	// code verified, remove record from db
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}voxel_auth_codes WHERE user_login = %s", $email ) );
}

function get_registration_redirect( $role ) {
	// edge case for legacy unconfirmed accounts
	// @todo remove in v1.3
	if ( ! $role ) {
		return '{REDIRECT_URL}';
	}

	if ( $role->has_plans_enabled() && $role->config( 'registration.show_plans_on_signup', true ) ) {
		$plans_page = get_permalink( $role->get_pricing_page_id() ) ?: home_url('/');
		return add_query_arg( [
			'redirect_to' => '{REDIRECT_URL}',
			'context' => 'signup',
		], $plans_page );
	}

	$after_registration = $role->config( 'registration.after_registration', 'welcome_step' );
	if ( $after_registration === 'welcome_step' ) {
		return add_query_arg( [
			'welcome' => '',
			'redirect_to' => '{REDIRECT_URL}',
		], get_permalink( \Voxel\get( 'templates.auth' ) ) ?: home_url('/') );
	} elseif ( $after_registration === 'custom_redirect' ) {
		return \Voxel\render( $role->config( 'registration.custom_redirect', '' ), [
			'user' => \Voxel\Dynamic_Data\Group::User( \Voxel\get_current_user() ),
			'site' => \Voxel\Dynamic_Data\Group::Site(),
		] );
	} else {
		return '{REDIRECT_URL}';
	}
}
