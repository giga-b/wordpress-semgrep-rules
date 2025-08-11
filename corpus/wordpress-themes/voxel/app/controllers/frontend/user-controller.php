<?php

namespace Voxel\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_user.follow_user', '@follow_user' );
		$this->on( 'voxel_ajax_user.follow_post', '@follow_post' );

		$this->on( 'voxel_ajax_user.collections.toggle_item', '@toggle_collection_item' );
		$this->on( 'voxel_ajax_user.collections.list', '@list_collections' );
		$this->on( 'voxel_ajax_user.collections.create', '@create_collection' );

		$this->on( 'voxel_ajax_user.posts.delete_post', '@delete_post' );
		$this->on( 'voxel_ajax_user.posts.unpublish_post', '@unpublish_post' );
		$this->on( 'voxel_ajax_user.posts.republish_post', '@republish_post' );
		$this->on( 'voxel_ajax_user.posts.relist_post', '@relist_post' );

		$this->on( 'voxel_ajax_user.profile', '@open_user_profile' );
		$this->on( 'voxel_ajax_nopriv_user.profile', '@open_user_profile' );
	}

	protected function follow_user() {
		try {
			$current_user = \Voxel\current_user();
			$user_id = ! empty( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : null;
			$user = \Voxel\User::get( $user_id );
			if ( ! $user ) {
				throw new \Exception( _x( 'User not found.', 'timeline', 'voxel' ) );
			}

			$profile = $user->get_or_create_profile();

			if ( $current_user->get_follow_status( 'user', $user->get_id() ) === \Voxel\FOLLOW_ACCEPTED ) {
				$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_NONE );
				$current_user->set_follow_status( 'post', $profile->get_id(), \Voxel\FOLLOW_NONE );
			} else {
				$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_ACCEPTED );
				$current_user->set_follow_status( 'post', $profile->get_id(), \Voxel\FOLLOW_ACCEPTED );

				( new \Voxel\Events\Timeline\Followers\User_Followed_Event )->dispatch( $user->get_id(), $current_user->get_id() );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function follow_post() {
		try {
			$current_user = \Voxel\current_user();
			$post_id = ! empty( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null;
			$post = \Voxel\Post::get( $post_id );
			if ( ! ( $post && $post->post_type ) ) {
				throw new \Exception( _x( 'Post not found.', 'timeline', 'voxel' ) );
			}

			if ( $post->post_type->get_key() === 'profile' ) {
				$user = \Voxel\User::get_by_profile_id( $post->get_id() );
				if ( ! $user ) {
					throw new \Exception( _x( 'User not found.', 'timeline', 'voxel' ) );
				}

				if ( $current_user->get_follow_status( 'user', $user->get_id() ) === \Voxel\FOLLOW_ACCEPTED ) {
					$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_NONE );
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_NONE );
				} else {
					$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_ACCEPTED );
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_ACCEPTED );

					( new \Voxel\Events\Timeline\Followers\User_Followed_Event )->dispatch( $user->get_id(), $current_user->get_id() );
				}
			} else {
				$current_status = $current_user->get_follow_status( 'post', $post->get_id() );
				if ( $current_status === \Voxel\FOLLOW_ACCEPTED ) {
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_NONE );
				} else {
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_ACCEPTED );

					( new \Voxel\Events\Timeline\Followers\Post_Followed_Event )->dispatch( $post->get_id(), $current_user->get_id() );
				}
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function toggle_collection_item() {
		try {
			global $wpdb;

			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$collection = \Voxel\Post::get( $_POST['collection_id'] ?? null );
			if ( ! (
				$collection
				&& $collection->post_type
				&& $collection->get_status() === 'publish'
				&& $collection->post_type->get_key() === 'collection'
				&& absint( $collection->get_author_id() ) === absint( get_current_user_id() )
			) ) {
				throw new \Exception( _x( 'Collection not found.', 'collections', 'voxel' ) );
			}

			$post = \Voxel\Post::get( $_POST['post_id'] ?? null );
			if ( ! ( $post && $post->post_type ) ) {
				throw new \Exception( _x( 'Post not found.', 'collections', 'voxel' ) );
			}

			$field = $collection->get_field('items');
			$current_user = \Voxel\get_current_user();

			if ( ! in_array( $post->post_type->get_key(), (array) $field->get_prop('post_types'), true ) ) {
				throw new \Exception( _x( 'This post cannot be added to this collection.', 'collections', 'voxel' ) );
			}

			$allowed_statuses = array_merge( [ 'publish' ], (array) $field->get_prop('allowed_statuses') );
			if ( ! in_array( $post->get_status(), $allowed_statuses, true ) ) {
				throw new \Exception( _x( 'This post cannot be added to this collection.', 'collections', 'voxel' ) );
			}

			$toggle = ( $_POST['toggle'] ?? null ) === 'add' ? 'add' : 'remove';

			if ( $toggle === 'add' ) {
				$max_count = $field->get_prop('max_count');
				if ( ! empty( $max_count ) ) {
					$current_count = absint( $wpdb->get_var( <<<SQL
						SELECT COUNT(*) FROM {$wpdb->prefix}voxel_relations
						WHERE parent_id = {$collection->get_id()} AND relation_key = 'items'
					SQL ) );

					if ( absint( $current_count ) >= absint( $max_count ) ) {
						throw new \Exception( _x( 'You cannot add any more items to this collection.', 'collections', 'voxel' ) );
					}
				}

				$exists = !! $wpdb->get_var( <<<SQL
					SELECT id FROM {$wpdb->prefix}voxel_relations
					WHERE parent_id = {$collection->get_id()}
						AND child_id = {$post->get_id()}
						AND relation_key = 'items'
					LIMIT 1
				SQL );

				if ( ! $exists ) {
					$wpdb->query( <<<SQL
						INSERT INTO {$wpdb->prefix}voxel_relations (`parent_id`, `child_id`, `relation_key`, `order`)
						VALUES ({$collection->get_id()}, {$post->get_id()}, 'items', 0)
					SQL );
				}

				$cache_key = sprintf( 'user_collections:%d', $current_user->get_id() );
				$cache = wp_cache_get( $cache_key, 'voxel' );
				if ( ! is_array( $cache ) ) {
					$cache = [];
				}

				$cache[ $post->get_id() ] = true;
				wp_cache_set( $cache_key, $cache, 'voxel' );

				return wp_send_json( [
					'success' => true,
					'status' => 'added',
					'is_saved' => true,
					'message' => sprintf( 'Saved to %s', $collection->get_title() ),
					'message' => \Voxel\replace_vars( _x( 'Saved to @collection', 'collections', 'voxel' ), [
						'@collection' => $collection->get_title(),
					] ),
					'actions' => [ [
						'label' => _x( 'View collection', 'collections', 'voxel' ),
						'link' => $collection->get_link(),
					] ],
				] );
			} else {
				$wpdb->query( <<<SQL
					DELETE FROM {$wpdb->prefix}voxel_relations
					WHERE parent_id = {$collection->get_id()}
						AND child_id = {$post->get_id()}
						AND relation_key = 'items'
				SQL );

				$cache_key = sprintf( 'user_collections:%d', $current_user->get_id() );
				$cache = wp_cache_get( $cache_key, 'voxel' );
				if ( isset( $cache[ $post->get_id() ] ) ) {
					unset( $cache[ $post->get_id() ] );
					wp_cache_set( $cache_key, $cache, 'voxel' );
				}

				return wp_send_json( [
					'success' => true,
					'status' => 'removed',
					'is_saved' => $current_user->has_saved_post_to_collection( $post->get_id() ),
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function list_collections() {
		try {
			$post_id = absint( $_GET['post_id'] ?? null );
			if ( ! $post_id ) {
				throw new \Exception( _x( 'Could not retrieve collections.', 'collections', 'voxel' ) );
			}

			$page = absint( $_GET['pg'] ?? 1 );
			$per_page = 10;

			$user_id = absint( get_current_user_id() );
			$limit = $per_page + 1;
			$offset = ( $page - 1 ) * $per_page;

			global $wpdb;

			$results = $wpdb->get_results( <<<SQL
				SELECT posts.ID AS post_id, posts.post_title AS title, relations.id AS is_selected
					FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->prefix}voxel_relations AS relations ON (
					relations.parent_id = posts.ID
					AND relations.child_id = {$post_id}
					AND relations.relation_key = 'items'
				)
				WHERE posts.post_type = 'collection'
					AND posts.post_author = {$user_id}
					AND posts.post_status = 'publish'
				ORDER BY posts.post_title ASC
				LIMIT {$limit} OFFSET {$offset}
			SQL );

			$has_more = count( $results ) > $per_page;
			if ( $has_more ) {
				array_pop( $results );
			}

			$list = [];
			foreach ( $results as $collection ) {
				$list[] = [
					'id' => absint( $collection->post_id ),
					'title' => $collection->title,
					'selected' => !! $collection->is_selected,
				];
			}

			return wp_send_json( [
				'success' => true,
				'has_more' => $has_more,
				'list' => $list,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function create_collection() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$user = \Voxel\current_user();
			if ( ! $user->can_create_post( 'collection' ) ) {
				throw new \Exception( _x( 'You have reached the collection limit.', 'collections', 'voxel' ) );
			}

			$post_type = \Voxel\Post_Type::get( 'collection' );
			$field = $post_type->get_field('title');
			$field->set_prop( 'required', true );

			$title = $field->sanitize( $_POST['title'] ?? null );
			$field->check_validity( $title );

			$post_id = wp_insert_post( [
				'post_type' => 'collection',
				'post_title' => $title,
				'post_name' => sanitize_title( $title ),
				'post_status' => 'publish',
				'post_author' => $user->get_id(),
			] );

			if ( is_wp_error( $post_id ) ) {
				throw new \Exception( _x( 'Could not create collection.', 'collections', 'voxel' ) );
			}

			return wp_send_json( [
				'success' => true,
				'item' => [
					'id' => $post_id,
					'title' => $title,
					'selected' => false,
				],
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function delete_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_delete_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( ! ( $post && $post->is_deletable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			wp_trash_post( $post->get_id() );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function unpublish_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_modify_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\current_user();
			if ( ! ( $post && $post->is_editable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( ! ( $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			// excluded post types
			if ( in_array( $post->post_type->get_key(), [ 'profile' ], true ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( $post->get_status() !== 'publish' ) {
				throw new \Exception( __( 'Only published posts can be unpublished.', 'voxel' ) );
			}

			wp_update_post( [
				'ID' => $post->get_id(),
				'post_status' => 'unpublished',
			] );

			delete_post_meta( $post->get_id(), '_pending_review_on_republish' );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function republish_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_modify_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\current_user();
			if ( ! ( $post && $post->is_editable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( ! ( $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			// excluded post types
			if ( in_array( $post->post_type->get_key(), [ 'profile' ], true ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( $post->get_status() !== 'unpublished' ) {
				throw new \Exception( __( 'Only unpublished posts can be republished using this action.', 'voxel' ) );
			}

			$new_post_status = 'publish';

			// if the user edited this post after unpublishing, the post must transition to pending first
			if (
				$post->post_type->get_setting( 'submissions.update_status' ) === 'pending'
				&& !! get_post_meta( $post->get_id(), '_pending_review_on_republish', true )
			) {
				$new_post_status = 'pending';
			}

			if ( ! $user->can_create_post( $post->post_type->get_key() ) ) {
				$limit = $user->get_submission_limit_for_post_type( $post->post_type->get_key() );

				// If the limit mode is "custom" and both "publish"/"pending" and "unpublished" posts count toward the limit,
				// then transitioning a post from "unpublished" to "published"/"pending" won't add to the total submission limit.
				// In that case, the action should be allowed even if the user is at maximum posting capacity.
				if (
					$limit->get_count_mode() === 'custom'
					&& in_array( 'unpublished', $limit->get_count_mode_custom(), true )
					&& in_array( $new_post_status, $limit->get_count_mode_custom(), true )
				) {
					$submission_count = $limit->get_submission_count();
					if ( ( $submission_count - 1 ) >= $limit->get_count() ) {
						throw new \Exception( __( 'You have reached your allowed submission limit.', 'publish post action', 'voxel' ) );
					}
				} else {
					throw new \Exception( __( 'You have reached your allowed submission limit.', 'publish post action', 'voxel' ) );
				}
			}

			wp_update_post( [
				'ID' => $post->get_id(),
				'post_status' => $new_post_status,
			] );

			delete_post_meta( $post->get_id(), '_pending_review_on_republish' );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function relist_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_relist_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\current_user();
			if ( ! ( $post && $post->is_editable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( ! ( $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			// excluded post types
			if ( in_array( $post->post_type->get_key(), [ 'profile' ], true ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( $post->get_status() !== 'expired' ) {
				throw new \Exception( __( 'Only expired posts can be relisted using this action.', 'voxel' ) );
			}

			$membership = $user->get_membership();
			$plan = $membership->plan;
			$post_type = $post->post_type;
			$limit = $user->get_submission_limit_for_post_type( $post_type->get_key() );

			if ( ! $user->can_create_post( $post->post_type->get_key() ) ) {
				/**
				 * Edge case: If submission limit has been configured to count Expired posts, and the
				 * relist behavior is set to "same_slot", user should be able to relist an expired post
				 * even if their submission limit says e.g. 10 out of 10 posted.
				 *
				 * @since 1.2.5
				 */
				if ( $limit && $limit->get_relist_behavior() === 'same_slot' ) {
					$count = $limit->get_count();
					$submission_count = $limit->get_submission_count();
					$can_create_post = ( $submission_count - 1 ) < $count;
					if ( ! $can_create_post ) {
						throw new \Exception( __( 'You have reached your allowed submission limit.', 'publish post action', 'voxel' ) );
					}
				} else {
					throw new \Exception( __( 'You have reached your allowed submission limit.', 'publish post action', 'voxel' ) );
				}
			}

			wp_update_post( [
				'ID' => $post->get_id(),
				'post_status' => 'publish',
				'post_date' => current_time( 'mysql' ),
				'post_date_gmt' => current_time( 'mysql', true ),
			] );

			// update relist count for user
			if ( $limit && $limit->get_relist_behavior() === 'new_slot' ) {
				$relist_count = (array) json_decode( get_user_meta( $user->get_id(), 'voxel:relist_count', true ), true );
				if ( ! is_array( $relist_count[ $plan->get_key() ] ?? null ) ) {
					$relist_count[ $plan->get_key() ] = [];
				}

				$current_count = $relist_count[ $plan->get_key() ][ $post_type->get_key()] ?? null;
				if ( ! is_numeric( $current_count ) || $current_count < 1 ) {
					$relist_count[ $plan->get_key() ][ $post_type->get_key()] = 0;
				}

				$relist_count[ $plan->get_key() ][ $post_type->get_key()]++;
				update_user_meta( $user->get_id(), 'voxel:relist_count', wp_slash( wp_json_encode( $relist_count ) ) );
			}

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function open_user_profile() {
		$user_id = -1;

		$username = $_REQUEST['username'] ?? null;
		if ( is_string( $username ) && ! empty( $username ) ) {
			$user = get_user_by( 'login', sanitize_user( str_replace( '·', ' ', $username ) ) );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		wp_safe_redirect( add_query_arg( 'author', $user_id, home_url() ) );
		exit;
	}
}
