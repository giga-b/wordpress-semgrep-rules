<?php

namespace Voxel\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Type_Payment extends Base_Type {

	protected $type = 'payment';

	protected
		$payment_intent,
		$amount,
		$currency,
		$price_id,
		$status,
		$created,
		$metadata,
		$additional_submissions;

	protected function init( array $config ) {
		$this->payment_intent = $config['payment_intent'] ?? null;
		$this->amount = $config['amount'] ?? null;
		$this->currency = $config['currency'] ?? null;
		$this->price_id = $config['price_id'] ?? null;
		$this->status = $config['status'] ?? null;
		$this->created = $config['created'] ?? null;
		$this->metadata = $config['metadata'] ?? null;
		$this->additional_submissions = $config['additional_submissions'] ?? null;

		if ( ! $this->is_active() ) {
			$this->plan = \Voxel\Plan::get( 'default' );
		}
	}

	public function is_active() {
		return $this->status === 'succeeded';
	}

	public function get_payment_intent() {
		return $this->payment_intent;
	}

	public function get_payment_intent_id() {
		return $this->payment_intent;
	}

	public function get_price_id() {
		if ( ! empty( $this->metadata['voxel:original_price_id'] ) ) {
			return $this->metadata['voxel:original_price_id'];
		}

		return $this->price_id;
	}

	public function get_status() {
		return $this->status;
	}

	public function get_amount() {
		return $this->amount;
	}

	public function get_currency() {
		return $this->currency;
	}

	public function get_created_at() {
		return $this->created;
	}

	public function get_metadata() {
		return $this->metadata;
	}

	public function get_additional_limits() {
		if ( ! $this->is_active() ) {
			return [];
		}

		$limits = json_decode( $this->metadata['voxel:limits'] ?? '', true );
		if ( ! is_array( $limits ) ) {
			$limits = [];
		}

		if ( is_array( $this->additional_submissions ) && ! empty( $this->additional_submissions ) ) {
			foreach ( $this->additional_submissions as $payment_intent_id => $additional_limits ) {
				foreach ( $additional_limits as $post_type_key => $post_type_limit ) {
					if ( ! isset( $limits[ $post_type_key ] ) ) {
						$limits[ $post_type_key ] = 0;
					}

					$limits[ $post_type_key ] += $post_type_limit;
				}
			}
		}

		return array_filter( array_map( 'absint', $limits ) );
	}
}
