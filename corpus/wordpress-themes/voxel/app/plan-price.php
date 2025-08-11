<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan_Price {

	public $plan;

	private
		$id,
		$mode,
		$price;

	public function __construct( $data ) {
		$this->mode = $data['mode'] ?? null;
		$this->id = $data['id'] ?? null;

		if ( ! is_string( $this->id ) || ! in_array( $this->mode, [ 'live', 'test' ], true ) ) {
			throw new \Exception( _x( 'Could not find plan.', 'pricing plans', 'voxel' ) );
		}

		$plan = \Voxel\Plan::get( $data['plan'] ?? '' );
		if ( ! $plan ) {
			throw new \Exception( _x( 'Plan does not exist.', 'pricing plans', 'voxel' ) );
		}

		$pricing = $plan->get_pricing();
		if ( empty( $pricing[ $this->mode ] ) || empty( $pricing[ $this->mode ]['prices'][ $this->id ] ) ) {
			throw new \Exception( _x( 'Price does not exist.', 'pricing plans', 'voxel' ) );
		}

		$this->plan = $plan;
		$this->price = $pricing[ $this->mode ]['prices'][ $this->id ];
	}

	public function get_id() {
		return $this->id;
	}

	public function get_mode() {
		return $this->mode;
	}

	public function get_type() {
		return $this->price['type'] ?? null;
	}

	public function is_enabled(): bool {
		return (bool) ( $this->price['active'] ?? false );
	}

	public function get_amount() {
		return $this->price['amount'] ?? null;
	}

	public function get_currency() {
		return $this->price['currency'] ?? null;
	}

	public function get_details() {
		return $this->price;
	}

	public function supports_addition( $post_type_key ): bool {
		$submissions = $this->plan->get_submission_limits();
		return isset( $submissions[ $post_type_key ]['price_per_addition'][ $this->mode ][ $this->id ] );
	}

	public function get_price_per_addition( $post_type_key ) {
		$submissions = $this->plan->get_submission_limits();
		$amount = $submissions[ $post_type_key ]['price_per_addition'][ $this->mode ][ $this->id ]['amount'] ?? null;

		if ( ! is_numeric( $amount ) || $amount <= 0 ) {
			return null;
		}

		if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $this->get_currency() ) ) {
			$amount *= 100;
		}

		return $amount;
	}

	public function to_key() {
		return sprintf( '%s@%s%s', $this->plan->get_key(), $this->mode === 'test' ? 'test:' : '', $this->id );
	}

	public static function from_key( string $price_key ) {
		$price_id = substr( strrchr( $price_key, '@' ), 1 );
		$plan_key = str_replace( '@'.$price_id, '', $price_key );
		$mode = substr( $price_id, 0, 5 ) === 'test:' ? 'test' : 'live';
		$price_id = str_replace( 'test:', '', $price_id );

		return new static( [
			'id' => $price_id,
			'plan' => $plan_key,
			'mode' => $mode,
		] );
	}

}
