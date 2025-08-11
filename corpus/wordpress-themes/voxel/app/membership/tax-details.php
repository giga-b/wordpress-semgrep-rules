<?php

namespace Voxel\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Tax_Details {

	public static function apply_to_checkout_session( $args ) {
		$tax_mode = \Voxel\get( 'settings.membership.checkout.tax.mode' );
		if ( $tax_mode === 'auto' ) {
			$args['automatic_tax'] = [
				'enabled' => true,
			];
		} elseif ( $tax_mode === 'manual' ) {
			$tax_rates = \Voxel\Stripe::is_test_mode()
				? (array) \Voxel\get('settings.membership.checkout.tax.manual.test_tax_rates')
				: (array) \Voxel\get('settings.membership.checkout.tax.manual.tax_rates');

			if ( ! empty( $tax_rates ) ) {
				$args['line_items'][0]['tax_rates'] = $tax_rates;
			}
		}

		$args['tax_id_collection'] = [
			'enabled' => !! \Voxel\get( 'settings.membership.checkout.tax.tax_id_collection' ),
		];

		return $args;
	}

	public static function apply_to_subscription_upgrade( $args ) {
		$tax_mode = \Voxel\get( 'settings.membership.checkout.tax.mode' );
		if ( $tax_mode === 'auto' ) {
			$args['automatic_tax'] = [
				'enabled' => true,
			];
		} elseif ( $tax_mode === 'manual' ) {
			$tax_rates = \Voxel\Stripe::is_test_mode()
				? (array) \Voxel\get('settings.membership.checkout.tax.manual.test_tax_rates')
				: (array) \Voxel\get('settings.membership.checkout.tax.manual.tax_rates');

			if ( ! empty( $tax_rates ) ) {
				$args['items'][0]['tax_rates'] = $tax_rates;
			}
		}

		return $args;
	}

}
