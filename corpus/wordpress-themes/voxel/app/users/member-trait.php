<?php

namespace Voxel\Users;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Member_Trait {

	private $membership;

	public function get_membership( $refresh_cache = false ) {
		if ( $refresh_cache ) {
			$this->membership = null;
		}

		if ( ! is_null( $this->membership ) ) {
			return $this->membership;
		}

		$meta_key = \Voxel\Stripe::is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
		$details = (array) json_decode( get_user_meta( $this->get_id(), $meta_key, true ), ARRAY_A );
		$type = $details['type'] ?? 'default';

		if ( $type === 'subscription' ) {
			$this->membership = new \Voxel\Membership\Type_Subscription( $details );
		} elseif ( $type === 'payment' ) {
			$this->membership = new \Voxel\Membership\Type_Payment( $details );
		} else {
			$this->membership = new \Voxel\Membership\Type_Default( $details );
		}

		return $this->membership;
	}

	public function is_eligible_for_free_trial(): bool {
		$meta_key = \Voxel\Stripe::is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
		if ( ! metadata_exists( 'user', $this->get_id(), $meta_key ) ) {
			return true;
		}

		$details = (array) json_decode( get_user_meta( $this->get_id(), $meta_key, true ), true );
		if ( ( $details['plan'] ?? null ) === 'default' && ( $details['trial_allowed'] ?? null ) ) {
			return true;
		}

		return false;
	}

}
