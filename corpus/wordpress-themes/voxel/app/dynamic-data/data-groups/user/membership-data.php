<?php

namespace Voxel\Dynamic_Data\Data_Groups\User;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Membership_Data {

	protected function get_membership_plan_data(): Base_Data_Type {
		return Tag::Object('Membership plan')->properties( function() {
			return [
				'key' => Tag::String('Key')->render( function() {
					$membership = $this->user->get_membership();
					return $membership->is_active() ? $membership->plan->get_key() : 'default';
				} ),
				'label' => Tag::String('Label')->render( function() {
					$membership = $this->user->get_membership();
					$default_plan = \Voxel\Plan::get_or_create_default_plan();
					return $membership->is_active() ? $membership->plan->get_label() : $default_plan->get_label();
				} ),
				'description' => Tag::String('Description')->render( function() {
					$membership = $this->user->get_membership();
					$default_plan = \Voxel\Plan::get_or_create_default_plan();
					return $membership->is_active() ? $membership->plan->get_description() : $default_plan->get_description();
				} ),
				'pricing' => Tag::Object('Pricing')->properties( function() {
					return [
						'amount' => Tag::Number('Amount')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'subscription' || $membership->get_type() === 'payment' ) {
								$amount = $membership->get_amount();
								if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $membership->get_currency() ) ) {
									$amount = round( $amount / 100, 2 );
								}

								return $amount;
							} else {
								return 0;
							}
						} ),
						'period' => Tag::String('Period')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'subscription' ) {
								return \Voxel\interval_format( $membership->get_interval(), $membership->get_interval_count() );
							} elseif ( $membership->get_type() === 'payment' ) {
								return _x( 'one time', 'price interval', 'voxel' );
							} else {
								return '';
							}
						} ),
						'currency' => Tag::String('Currency')->render( function() {
							$membership = $this->user->get_membership();
							if ( $membership->get_type() === 'subscription' || $membership->get_type() === 'payment' ) {
								return $membership->get_currency();
							} else {
								return \Voxel\get( 'settings.stripe.currency' );
							}
						} ),
						'status' => Tag::String('Status')->render( function() {
							$membership = $this->user->get_membership();
							$status = $membership->get_status();
							if ( is_string( $status ) && ! empty( $status ) ) {
								return $status;
							}

							return '';
						} ),
						'start_date' => Tag::Date('Purchase date')->render( function() {
							$membership = $this->user->get_membership();
							$created_at = $membership->get_created_at();
							$timestamp = strtotime( (string) $created_at );
							if ( $timestamp ) {
								return date( 'Y-m-d H:i:s', $timestamp );
							}

							return '';
						} ),
					];
				} ),
			];
		} );
	}

}
