<?php

namespace Voxel\Dynamic_Data\Data_Groups\User;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Membership_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'user/membership';
	}

	public $membership;
	public function __construct( \Voxel\Membership\Base_Type $membership ) {
		$this->membership = $membership;
	}

	protected function properties(): array {
		return [
			'plan' => Tag::Object('Plan')->properties( function() {
				return [
					'key' => Tag::String('Key')->render( function() {
						return $this->membership->plan->get_key();
					} ),
					'label' => Tag::String('Label')->render( function() {
						return $this->membership->plan->get_label();
					} ),
					'description' => Tag::String('Description')->render( function() {
						return $this->membership->plan->get_description();
					} ),
				];
			} ),
			'pricing' => Tag::Object('Pricing')->properties( function() {
				return [
					'amount' => Tag::String('Amount')->render( function() {
						$currency = \Voxel\get('settings.stripe.currency', 'usd');
						if ( $this->membership->get_type() === 'subscription' || $this->membership->get_type() === 'payment' ) {
							$currency = $this->membership->get_currency();
						}

						$amount = 0;
						if ( $this->membership->get_type() === 'subscription' || $this->membership->get_type() === 'payment' ) {
							$amount = $this->membership->get_amount();
						}

						return \Voxel\currency_format( $amount, $currency, true );
					} ),
					'period' => Tag::String('Period')->render( function() {
						if ( $this->membership->get_type() === 'subscription' ) {
							return \Voxel\interval_format( $this->membership->get_interval(), $this->membership->get_interval_count() );
						} elseif ( $this->membership->get_type() === 'payment' ) {
							return _x( 'one time', 'price interval', 'voxel' );
						} else {
							return '';
						}
					} ),
				];
			} ),
		];
	}

	public static function mock(): self {
		return new static( \Voxel\User::dummy()->get_membership() );
	}
}
