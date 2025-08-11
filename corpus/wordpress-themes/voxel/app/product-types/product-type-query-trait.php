<?php

namespace Voxel\Product_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Product_Type_Query_Trait {

	private static $instances = [];

	/**
	 * Get a product type based on its key.
	 *
	 * @since 1.0
	 */
	public static function get( $key ) {
		if ( ! isset( static::$instances[ $key ] ) ) {
			if ( $key === 'voxel:claim' ) {
				static::$instances[ $key ] = static::get_claims_product_type();
			} elseif ( $key === 'voxel:promotion' ) {
				static::$instances[ $key ] = static::get_promotions_product_type();
			} else {
				$product_types = \Voxel\get( 'product_types', [] );
				if ( ! isset( $product_types[ $key ] ) ) {
					return null;
				}

				static::$instances[ $key ] = new static( (array) $product_types[ $key ] );
			}
		}

		return static::$instances[ $key ];
	}

	public static function get_all() {
		$keys = array_keys( \Voxel\get( 'product_types', [] ) );
		return array_map( '\Voxel\Product_Type::get', $keys );
	}

	public static function get_claims_product_type() {
		return new static( [
			'settings' => [
				'key' => 'voxel:claim',
				'label' => 'Claims',
				'product_mode' => 'regular',
				'payments' => [
					'mode' => \Voxel\get( 'product_settings.claims.payments.mode', 'payment' ) === 'offline' ? 'offline' : 'payment',
				],
			],
			'modules' => [
				'base_price' => [
					'enabled' => true,
					'discount_price' => [
						'enabled' => false,
					],
				],
				'cart' => [
					'enabled' => false,
				],
			],
		] );
	}

	public static function get_promotions_product_type() {
		return new static( [
			'settings' => [
				'key' => 'voxel:promotion',
				'label' => 'Promotions',
				'product_mode' => 'regular',
				'payments' => [
					'mode' => \Voxel\get( 'product_settings.promotions.payments.mode', 'payment' ) === 'offline' ? 'offline' : 'payment',
				],
			],
			'modules' => [
				'base_price' => [
					'enabled' => false,
				],
				'cart' => [
					'enabled' => false,
				],
			],
		] );
	}
}
