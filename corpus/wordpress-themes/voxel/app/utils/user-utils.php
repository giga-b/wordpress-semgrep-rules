<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

function current_user(): ?\Voxel\User {
	return \Voxel\User::get( get_current_user_id() );
}

function get_current_user(): ?\Voxel\User {
	return \Voxel\User::get( get_current_user_id() );
}

function cache_user_follow_stats( $user_id ) {
	global $wpdb;

	$stats = [
		'following' => [],
		'followed' => [],
		'following_by_post_type' => [],
	];

	// following
	$following = $wpdb->get_results( $wpdb->prepare( <<<SQL
		SELECT `status`, COUNT(*) AS `count`
		FROM {$wpdb->prefix}voxel_followers
		WHERE `follower_type` = 'user' AND `follower_id` = %d AND `object_type` = 'user'
		GROUP BY `status`
	SQL, $user_id ) );

	foreach ( $following as $status ) {
		$stats['following'][ (int) $status->status ] = absint( $status->count );
	}

	// followed_by
	$followed = $wpdb->get_results( $wpdb->prepare( <<<SQL
		SELECT `status`, COUNT(*) AS `count`
		FROM {$wpdb->prefix}voxel_followers
		WHERE `object_type` = 'user' AND `object_id` = %d AND `follower_type` = 'user'
		GROUP BY `status`
	SQL, $user_id ) );

	foreach ( $followed as $status ) {
		$stats['followed'][ (int) $status->status ] = absint( $status->count );
	}

	$by_post_type = [];
	$results = $wpdb->get_results( $wpdb->prepare( <<<SQL
		SELECT p.post_type AS post_type, f.status AS status, COUNT(*) AS count
		FROM {$wpdb->prefix}voxel_followers f
		LEFT JOIN {$wpdb->prefix}posts p ON ( f.object_type = 'post' AND f.object_id = p.ID )
		WHERE object_type = 'post' AND follower_type = 'user' AND follower_id = %d
		GROUP BY p.post_type, f.status
	SQL, $user_id ), ARRAY_A );

	foreach ( $results as $result ) {
		if ( empty( $result['post_type'] ) || ! is_string( $result['post_type'] ) ) {
			continue;
		}

		if ( ! isset( $by_post_type[ $result['post_type'] ] ) ) {
			$by_post_type[ $result['post_type'] ] = [];
		}

		$by_post_type[ $result['post_type'] ][ (int) $result['status'] ] = absint( $result['count'] );
	}

	$stats['following_by_post_type'] = $by_post_type;

	update_user_meta( $user_id, 'voxel:follow_stats', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

function cache_post_follow_stats( $post_id ) {
	global $wpdb;

	$stats = [
		'followed' => [],
	];

	// followed_by
	$followed = $wpdb->get_results( $wpdb->prepare( <<<SQL
		SELECT `status`, COUNT(*) AS `count`
		FROM {$wpdb->prefix}voxel_followers
		WHERE `object_type` = 'post' AND `object_id` = %d AND status = 1
		GROUP BY `status`
	SQL, $post_id ) );

	foreach ( $followed as $status ) {
		$stats['followed'][ (int) $status->status ] = absint( $status->count );
	}

	update_post_meta( $post_id, 'voxel:follow_stats', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

/**
 * Queue `cache_user_post_stats()` for execution on the `shutdown` hook, which allows for
 * efficiently update the post stats meta cache on bulk post updates.
 *
 * @since 1.2.6
 */
function queue_user_post_stats_for_caching( $user_id ) {
	static $hooked = false;

	if ( ! isset( $GLOBALS['_vx_post_stats_cache_ids'] ) ) {
		$GLOBALS['_vx_post_stats_cache_ids'] = [];
	}

	$GLOBALS['_vx_post_stats_cache_ids'][ $user_id ] = true;

	if ( ! $hooked ) {
		$hooked = true;
		add_action( 'shutdown', function() {
			foreach ( $GLOBALS['_vx_post_stats_cache_ids'] as $user_id => $true ) {
				// \Voxel\log( 'Caching user post stats for '.$user_id );
				cache_user_post_stats( $user_id );
			}
		} );
	}
}

/**
 * Updates the post stats meta cache for the given user and returns the array of stats.
 *
 * @since 1.0
 */
function cache_user_post_stats( $user_id ) {
	global $wpdb;

	$stats = [];

	$user_id = absint( $user_id );
	$post_types = [];
	foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
		$post_types[] = $wpdb->prepare( '%s', $post_type->get_key() );
	}

	if ( empty( $post_types ) ) {
		update_user_meta( $user_id, 'voxel:post_stats', wp_slash( wp_json_encode( $stats ) ) );
		return $stats;
	}

	$post_types = join( ',', $post_types );
	$results = $wpdb->get_results( <<<SQL
		SELECT COUNT(*) AS total, post_type, post_status FROM {$wpdb->posts}
		WHERE post_author = {$user_id}
			AND post_type IN ({$post_types})
			AND post_status IN ('publish','pending','rejected','draft','unpublished','expired','trash')
		GROUP BY post_type, post_status
		ORDER BY post_type
	SQL );

	foreach ( $results as $result ) {
		if ( ! isset( $stats[ $result->post_type ] ) ) {
			$stats[ $result->post_type ] = [];
		}

		$stats[ $result->post_type ][ $result->post_status ] = absint( $result->total );
	}

	update_user_meta( $user_id, 'voxel:post_stats', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

function get_user_by_id_or_email( $id_or_email ) {
	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', absint( $id_or_email ) );
	} elseif ( $id_or_email instanceof \WP_User ) {
		$user = $id_or_email;
	} elseif ( $id_or_email instanceof \WP_Post ) {
		$user = get_user_by( 'id', (int) $id_or_email->post_author );
	} elseif ( $id_or_email instanceof \WP_Comment && ! empty( $id_or_email->user_id ) ) {
		$user = get_user_by( 'id', (int) $id_or_email->user_id );
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	} else {
		$user = null;
	}

	return \Voxel\User::get( $user );
}

function unpublish_posts_over_the_limit_for_user( \Voxel\User $user ) {
	global $wpdb;

	// exclude administrators and editors from having their posts unpublished
	if ( $user->has_role( 'administrator' ) || $user->has_role( 'editor' ) ) {
		return;
	}

	$stats = $user->get_post_stats();
	$to_unpublish = [];

	foreach ( $stats as $post_type_key => $post_type_stats ) {
		// no posts to unpublish
		if ( ( $post_type_stats['publish'] ?? 0 ) < 1 ) {
			continue;
		}

		// excluded post types from unpublishing
		if ( in_array( $post_type_key, [ 'profile' ], true ) ) {
			continue;
		}

		// validate post type
		$post_type = \Voxel\Post_Type::get( $post_type_key );
		if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
			continue;
		}

		$limit = $user->get_submission_limit_for_post_type( $post_type->get_key() );

		if ( $limit ) {
			// if a limit exists and has been reached, unpublish all posts above the limit
			if ( $limit->get_count() < $post_type_stats['publish'] ) {
				$to_unpublish[ $post_type->get_key() ] = ( $post_type_stats['publish'] - $limit->get_count() );
			}
		} else {
			// if a limit for this post type has not been configured, unpublish all posts
			$to_unpublish[ $post_type->get_key() ] = $post_type_stats['publish'];
		}
	}

	foreach ( $to_unpublish as $post_type_key => $unpublish_count ) {
		$unpublish_count = absint( $unpublish_count );
		if ( $unpublish_count < 1 ) {
			continue;
		}

		$unpublish_ids = $wpdb->get_col( $wpdb->prepare( <<<SQL
			SELECT ID FROM {$wpdb->posts}
			WHERE post_author = %d
				AND post_type = %s
				AND post_status = 'publish'
			ORDER BY post_date DESC
			LIMIT %d OFFSET 0
		SQL, $user->get_id(), $post_type_key, $unpublish_count ) );

		if ( empty( $unpublish_ids ) ) {
			continue;
		}

		foreach ( $unpublish_ids as $post_id ) {
			$post_id = absint( $post_id );
			if ( $post_id < 1 ) {
				continue;
			}

			wp_update_post( [
				'ID' => $post_id,
				'post_status' => 'unpublished',
			] );
		}
	}
}

function update_site_specific_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
	if ( is_multisite() ) {
		$blog_id = get_current_blog_id();
		$site_specific_key = 'site_' . $blog_id . '_' . $meta_key;

		return update_user_meta( $user_id, $site_specific_key, $meta_value, $prev_value );
	} else {
		return update_user_meta( $user_id, $meta_key, $meta_value, $prev_value );
	}
}

function get_site_specific_user_meta( $user_id, $meta_key = '', $single = false ) {
	if ( is_multisite() ) {
		$blog_id         = get_current_blog_id();
		$site_specific_key = 'site_' . $blog_id . '_' . $meta_key;

		return get_user_meta( $user_id, $site_specific_key, $single );
	} else {
		return get_user_meta( $user_id, $meta_key, $single );
	}
}

function get_site_specific_user_meta_key( string $meta_key ): string {
	if ( is_multisite() ) {
		$blog_id         = get_current_blog_id();
		$site_specific_key = 'site_' . $blog_id . '_' . $meta_key;

		return $site_specific_key;
	} else {
		return $meta_key;
	}
}
