<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Submission_Limit {

	/**
	 * Limit belongs to a user.
	 *
	 * @since 1.2
	 */
	protected $user;

	/**
	 * Limit applies to a single post type.
	 *
	 * @since 1.2
	 */
	protected $post_type;

	/**
	 * Limit belongs to a plan.
	 *
	 * @since 1.2.5
	 */
	protected $plan;

	/**
	 * Limit details.
	 */
	protected
		$count,
		$count_mode,
		$count_mode_custom,
		$price_per_addition,
		$relist_behavior;

	public function __construct( \Voxel\User $user, \Voxel\Post_Type $post_type, array $limit, \Voxel\Plan $plan ) {
		$this->user = $user;
		$this->post_type = $post_type;
		$this->plan = $plan;

		$this->count = absint( $limit['count'] ?? 0 );
		$this->count_mode = \Voxel\from_list( $limit['count_mode'] ?? '', [ 'active_posts', 'submitted_posts', 'custom' ], 'submitted_posts' );
		if ( $this->count_mode === 'custom' ) {
			$this->count_mode_custom = (array) ( $limit['count_mode_custom'] ?? [] );
		}

		if ( $this->count_mode === 'custom' && in_array( 'expired', $this->count_mode_custom, true ) ) {
			$this->relist_behavior = \Voxel\from_list( $limit['relist_behavior'] ?? '', [ 'same_slot', 'new_slot' ], 'same_slot' );
		}
	}

	public function get_count(): int {
		return $this->count;
	}

	public function get_count_mode(): string {
		return $this->count_mode;
	}

	public function get_count_mode_custom(): ?array {
		if ( $this->count_mode === 'custom' ) {
			return is_array( $this->count_mode_custom ) ? $this->count_mode_custom : [];
		}

		return null;
	}

	public function can_create_post(): bool {
		$count = $this->get_count();

		// bail early if count is 0
		if ( $count < 1 ) {
			return false;
		}

		$submission_count = $this->get_submission_count();

		return $submission_count < $count;
	}

	public function get_submission_count(): int {
		if ( $this->count_mode === 'custom' ) {
			$count = $this->get_submission_count_by_statuses( $this->count_mode_custom );
		} elseif ( $this->count_mode === 'active_posts' ) {
			$count = $this->get_submission_count_by_statuses( [ 'publish' ] );
		} else /* $this->count_mode === 'submitted_posts' */ {
			$count = $this->get_submission_count_by_statuses( [ 'publish', 'pending' ] );
		}

		$relist_count = $this->get_relist_count();
		return $count + $relist_count;
	}

	public function get_submission_count_by_statuses( array $statuses ): int {
		$stats = $this->user->get_post_stats();
		$count = 0;
		foreach ( $statuses as $status_key ) {
			$count += ( $stats[ $this->post_type->get_key() ][ $status_key ] ?? 0 );
		}

		return $count;
	}

	public function get_relist_count(): int {
		if ( $this->get_relist_behavior() === 'same_slot' ) {
			return 0;
		}

		$total_relist_count = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:relist_count', true ), true );
		$relist_count = $total_relist_count[ $this->plan->get_key() ][ $this->post_type->get_key() ] ?? 0;
		if ( ! is_numeric( $relist_count ) || $relist_count < 1 ) {
			return 0;
		}

		return absint( $relist_count );
	}

	public function get_relist_behavior() {
		return $this->relist_behavior;
	}
}
