<?php

namespace Voxel\Controllers\Async;

if ( ! defined('ABSPATH') ) {
	exit;
}

class General_Actions extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_general.stripe.endpoint_status', '@check_stripe_endpoint_status' );
		$this->on( 'voxel_ajax_general.stripe.connect_endpoint_status', '@check_stripe_connect_endpoint_status' );
		$this->on( 'voxel_ajax_general.search_users', '@search_users' );
		$this->on( 'voxel_ajax_general.search_posts', '@search_posts' );
		$this->on( 'voxel_ajax_general.search_terms', '@search_terms' );
	}

	protected function check_stripe_endpoint_status() {
		try {
			$mode = \Voxel\from_list( $_GET['mode'] ?? null, [ 'live', 'test' ], 'test' );
			$stripe = $mode === 'live' ? \Voxel\Stripe::getLiveClient() : \Voxel\Stripe::getTestClient();
			$endpoint_id = \Voxel\get( 'settings.stripe.webhooks.'.$mode.'.id' );

			try {
				$endpoint = $stripe->webhookEndpoints->retrieve( $endpoint_id );
			} catch ( \Voxel\Vendor\Stripe\Exception\InvalidRequestException $e ) {
				if ( $e->getStripeCode() === 'resource_missing' ) {
					$endpoint = $stripe->webhookEndpoints->create( [
						'url' => home_url( '/?vx=1&action=stripe.webhooks' ),
						'enabled_events' => \Voxel\Stripe::WEBHOOK_EVENTS,
					] );

					\Voxel\set( 'settings.stripe.webhooks.'.$mode, [
						'id' => $endpoint->id,
						'secret' => $endpoint->secret,
					] );

					return wp_send_json( [
						'success' => true,
						'message' => __( 'Endpoint could not be found in the Stripe servers. A new endpoint has been generated as replacement.', 'voxel-backend' ),
						'id' => $endpoint->id,
						'secret' => $endpoint->secret,
					] );
				} else {
					throw $e;
				}
			}

			if (
				$endpoint->status !== 'enabled' ||
				$endpoint->url !== home_url( '/?vx=1&action=stripe.webhooks' ) ||
				! empty( array_diff( \Voxel\Stripe::WEBHOOK_EVENTS, $endpoint->enabled_events ) )
			) {
				$stripe->webhookEndpoints->update( $endpoint->id, [
					'url' => home_url( '/?vx=1&action=stripe.webhooks' ),
					'enabled_events' => \Voxel\Stripe::WEBHOOK_EVENTS,
					'disabled' => false,
				] );

				return wp_send_json( [
					'success' => true,
					'message' => __( 'Endpoint was using outdated configuration. It has been updated to the latest version.', 'voxel-backend' ),
				] );
			}

			return wp_send_json( [
				'success' => true,
				'message' => __( 'No issues with this endpoint were detected.', 'voxel-backend' ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function check_stripe_connect_endpoint_status() {
		try {
			$mode = \Voxel\from_list( $_GET['mode'] ?? null, [ 'live', 'test' ], 'test' );
			$stripe = $mode === 'live' ? \Voxel\Stripe::getLiveClient() : \Voxel\Stripe::getTestClient();
			$endpoint_id = \Voxel\get( 'settings.stripe.webhooks.'.$mode.'_connect.id' );

			$createEndpoint = function() use ( $stripe, $mode ) {
				$endpoint = $stripe->webhookEndpoints->create( [
					'url' => home_url( '/?vx=1&action=stripe.connect_webhooks' ),
					'connect' => true,
					'enabled_events' => \Voxel\Stripe::CONNECT_WEBHOOK_EVENTS,
				] );

				\Voxel\set( 'settings.stripe.webhooks.'.$mode.'_connect', [
					'id' => $endpoint->id,
					'secret' => $endpoint->secret,
				] );

				return wp_send_json( [
					'success' => true,
					'message' => __( 'Connect endpoint could not be found in the Stripe servers. A new endpoint has been generated as replacement.', 'voxel-backend' ),
					'id' => $endpoint->id,
					'secret' => $endpoint->secret,
				] );
			};

			try {
				$endpoint = $stripe->webhookEndpoints->retrieve( $endpoint_id );
				// if ( ! $endpoint->application ) {
				// 	return $createEndpoint();
				// }
			} catch ( \Voxel\Vendor\Stripe\Exception\InvalidRequestException $e ) {
				if ( $e->getStripeCode() === 'resource_missing' ) {
					return $createEndpoint();
				} else {
					throw $e;
				}
			}

			if (
				$endpoint->status !== 'enabled'
				|| $endpoint->url !== home_url( '/?vx=1&action=stripe.connect_webhooks' )
				|| ! empty( array_diff( \Voxel\Stripe::CONNECT_WEBHOOK_EVENTS, $endpoint->enabled_events ) )
			) {
				$stripe->webhookEndpoints->update( $endpoint->id, [
					'url' => home_url( '/?vx=1&action=stripe.webhooks' ),
					'enabled_events' => \Voxel\Stripe::CONNECT_WEBHOOK_EVENTS,
					'disabled' => false,
				] );

				return wp_send_json( [
					'success' => true,
					'message' => __( 'Connect endpoint was using outdated configuration. It has been updated to the latest version.', 'voxel-backend' ),
				] );
			}

			return wp_send_json( [
				'success' => true,
				'message' => __( 'No issues with this endpoint were detected.', 'voxel-backend' ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function search_users() {
		try {
			$search = sanitize_text_field( $_GET['search'] ?? '' );
			if ( empty( $search ) ) {
				throw new \Exception( __( 'No search term provided.', 'voxel-backend' ) );
			}

			global $wpdb;

			$like = '%'.$wpdb->esc_like( $search ).'%';

			$results = $wpdb->get_col( $wpdb->prepare( <<<SQL
				SELECT ID FROM {$wpdb->users}
				WHERE user_login = %s
					OR user_email = %s
					OR ID = %s
					OR display_name LIKE %s
				LIMIT 5
			SQL, $search, $search, $search, $like ) );

			$users = [];
			foreach ( $results as $user_id ) {
				if ( $user = \Voxel\User::get( $user_id ) ) {
					$users[] = [
						'id' => $user->get_id(),
						'avatar' => $user->get_avatar_markup(),
						'display_name' => $user->get_display_name(),
						'roles' => array_values( $user->get_role_keys() ),
						'edit_link' => $user->get_edit_link(),
					];
				}
			}

			return wp_send_json( [
				'success' => true,
				'results' => $users,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function search_posts() {
		try {
			$search = sanitize_text_field( $_GET['search'] ?? '' );
			$post_types = array_filter( explode( ',', $_GET['post_types'] ?? '' ) );
			if ( empty( $search ) || empty( $post_types ) ) {
				throw new \Exception( __( 'No search term provided.', 'voxel-backend' ) );
			}

			global $wpdb;

			$like = '%'.$wpdb->esc_like( $search ).'%';
			$post_types_in = '\''.join( '\',\'', array_map( 'esc_sql', $post_types ) ).'\'';

			$results = $wpdb->get_results( $wpdb->prepare( <<<SQL
				SELECT ID, post_title FROM {$wpdb->posts}
				WHERE
					post_status = 'publish'
					AND post_type IN ({$post_types_in})
					AND ( ID = %s OR post_title LIKE %s )
				ORDER BY post_title ASC
				LIMIT 10
			SQL, $search, $like ) );

			$posts = [];
			foreach ( $results as $result ) {
				$posts[] = [
					'id' => $result->ID,
					'title' => $result->post_title,
				];
			}

			return wp_send_json( [
				'success' => true,
				'results' => $posts,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function search_terms() {
		try {
			$search = sanitize_text_field( $_GET['search'] ?? '' );
			$taxonomies = array_filter( explode( ',', $_GET['taxonomy'] ?? '' ) );
			if ( empty( $search ) || empty( $taxonomies ) ) {
				throw new \Exception( __( 'No search term provided.', 'voxel-backend' ) );
			}

			global $wpdb;

			$like = '%'.$wpdb->esc_like( $search ).'%';
			$taxonomies_in = '\''.join( '\',\'', array_map( 'esc_sql', $taxonomies ) ).'\'';

			$results = $wpdb->get_results( $wpdb->prepare( <<<SQL
				SELECT t.term_id, t.name FROM {$wpdb->terms} t
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				WHERE
					tt.taxonomy IN ({$taxonomies_in})
					AND ( t.term_id = %s OR t.name LIKE %s OR t.slug LIKE %s )
				ORDER BY t.name ASC
				LIMIT 10
			SQL, $search, $like, $like ) );

			$terms = [];
			foreach ( $results as $result ) {
				$terms[] = [
					'id' => $result->term_id,
					'title' => $result->name,
				];
			}

			return wp_send_json( [
				'success' => true,
				'results' => $terms,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
