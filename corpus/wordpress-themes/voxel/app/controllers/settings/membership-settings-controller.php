<?php

namespace Voxel\Controllers\Settings;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Membership_Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/global-settings/register', '@register_settings' );
	}

	protected function register_settings( $settings ) {
		$settings['membership'] = Schema::Object( [
			'require_verification' => Schema::Bool()->default(true),
			'username_behavior' => Schema::Enum( [ 'display_as_field', 'generate_from_email' ] )->default( 'display_as_field' ),
			'trial' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'period_days' => Schema::Int()->min(0)->default(0),
			] ),
			'update' => Schema::Object( [
				'proration_behavior' => Schema::Enum( [
					'create_prorations',
					'none',
					'always_invoice',
				] )->default('always_invoice'),
			] ),
			'cancel' => Schema::Object( [
				'behavior' => Schema::Enum( [
					'at_period_end',
					'immediately',
				] )->default('at_period_end'),
			] ),
			'checkout' => Schema::Object( [
				'tax' => Schema::Object( [
					'mode' => Schema::Enum( [
						'auto',
						'manual',
						'none',
					] )->default('none'),
					'manual' => Schema::Object( [
						'tax_rates' => Schema::List()
							->validator('is_string')
							->default([]),
						'test_tax_rates' => Schema::List()
							->validator('is_string')
							->default([]),
					] ),
					'tax_id_collection' => Schema::Bool()->default(false),
				] ),
				'promotion_codes' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
				'billing_address_collection' => Schema::enum( [ 'auto', 'required' ] )->default('auto'),
				'phone_number_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),
		] );

		return $settings;
	}
}
