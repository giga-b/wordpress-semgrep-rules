<?php

namespace Voxel\Controllers\Settings;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/global-settings/register', '@register_settings' );
		$this->on( 'voxel/global-settings/saved', '@settings_updated', 10, 2 );
	}

	protected function register_settings( $settings ) {
		$settings['stripe'] = Schema::Object( [
			'test_mode' => Schema::Bool()->default(true),
			'key' => Schema::String(),
			'secret' => Schema::String(),
			'test_key' => Schema::String(),
			'test_secret' => Schema::String(),
			'currency' => Schema::String(),

			'portal' => Schema::Object( [
				'invoice_history' => Schema::Bool()->default(true),
				'customer_update' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'allowed_updates' => Schema::List()
						->allowed_values( [
							'email',
							'address',
							'phone',
							'shipping',
							'tax_id',
						] )
						->default( [ 'email', 'address', 'phone' ] ),
				] ),
				'live_config_id' => Schema::String(),
				'test_config_id' => Schema::String(),
			] ),

			'webhooks' => Schema::Object( [
				'live' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
				'live_connect' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
				'test' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
				'test_connect' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
				'local' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'secret' => Schema::String(),
				] ),
			] ),
		] );

		return $settings;
	}

	protected function settings_updated( $config, $previous_config ) {
		// if customer portal settings have changed, update configuration (or create new if it doesn't exist)
		if ( \Voxel\get( 'settings.stripe.secret' ) ) {
			if ( empty( \Voxel\get( 'settings.stripe.portal.live_config_id' ) ) ) {
				// \Voxel\log( 'create_live_customer_portal' );
				$this->create_live_customer_portal();
			} elseif ( ( $previous_config['stripe']['portal'] ?? [] ) !== \Voxel\get( 'settings.stripe.portal', [] ) ) {
				// \Voxel\log( 'update_live_customer_portal' );
				$this->update_live_customer_portal();
			}
		}

		if ( \Voxel\get( 'settings.stripe.test_secret' ) ) {
			if ( empty( \Voxel\get( 'settings.stripe.portal.test_config_id' ) ) ) {
				// \Voxel\log( 'create_test_customer_portal' );
				$this->create_test_customer_portal();
			} elseif ( ( $previous_config['stripe']['portal'] ?? [] ) !== \Voxel\get( 'settings.stripe.portal', [] ) ) {
				// \Voxel\log( 'update_test_customer_portal' );
				$this->update_test_customer_portal();
			}
		}

		if ( ! empty( \Voxel\get( 'settings.stripe.secret' ) ) && empty( \Voxel\get( 'settings.stripe.webhooks.live.id' ) ) ) {
			// \Voxel\log( 'create_live_webhook_endpoint' );
			$this->create_live_webhook_endpoint();
		}

		if ( ! empty( \Voxel\get( 'settings.stripe.secret' ) ) && empty( \Voxel\get( 'settings.stripe.webhooks.live_connect.id' ) ) ) {
			// \Voxel\log( 'create_live_connect_webhook_endpoint' );
			$this->create_live_connect_webhook_endpoint();
		}

		if ( ! empty( \Voxel\get( 'settings.stripe.test_secret' ) ) && empty( \Voxel\get( 'settings.stripe.webhooks.test.id' ) ) ) {
			// \Voxel\log( 'create_test_webhook_endpoint' );
			$this->create_test_webhook_endpoint();
		}

		if ( ! empty( \Voxel\get( 'settings.stripe.test_secret' ) ) && empty( \Voxel\get( 'settings.stripe.webhooks.test_connect.id' ) ) ) {
			// \Voxel\log( 'create_test_connect_webhook_endpoint' );
			$this->create_test_connect_webhook_endpoint();
		}
	}

	protected function create_live_webhook_endpoint() {
		try {
			$stripe = \Voxel\Stripe::getLiveClient();
			$endpoint = $stripe->webhookEndpoints->create( [
				'url' => home_url( '/?vx=1&action=stripe.webhooks' ),
				'enabled_events' => \Voxel\Stripe::WEBHOOK_EVENTS,
			] );

			\Voxel\set( 'settings.stripe.webhooks.live', [
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function create_test_webhook_endpoint() {
		try {
			$stripe = \Voxel\Stripe::getTestClient();
			$endpoint = $stripe->webhookEndpoints->create( [
				'url' => home_url( '/?vx=1&action=stripe.webhooks' ),
				'enabled_events' => \Voxel\Stripe::WEBHOOK_EVENTS,
			] );

			\Voxel\set( 'settings.stripe.webhooks.test', [
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function create_live_connect_webhook_endpoint() {
		try {
			$stripe = \Voxel\Stripe::getLiveClient();
			$endpoint = $stripe->webhookEndpoints->create( [
				'url' => home_url( '/?vx=1&action=stripe.connect_webhooks' ),
				'connect' => true,
				'enabled_events' => \Voxel\Stripe::CONNECT_WEBHOOK_EVENTS,
			] );

			\Voxel\set( 'settings.stripe.webhooks.live_connect', [
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function create_test_connect_webhook_endpoint() {
		try {
			$stripe = \Voxel\Stripe::getTestClient();
			$endpoint = $stripe->webhookEndpoints->create( [
				'url' => home_url( '/?vx=1&action=stripe.connect_webhooks' ),
				'connect' => true,
				'enabled_events' => \Voxel\Stripe::CONNECT_WEBHOOK_EVENTS,
			] );

			\Voxel\set( 'settings.stripe.webhooks.test_connect', [
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function create_live_customer_portal() {
		try {
			$stripe = \Voxel\Stripe::getLiveClient();
			$configuration = $stripe->billingPortal->configurations->create( $this->_get_portal_config() );
			\Voxel\set( 'settings.stripe.portal.live_config_id', $configuration->id );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function update_live_customer_portal() {
		try {
			$stripe = \Voxel\Stripe::getLiveClient();
			$configuration_id = \Voxel\get( 'settings.stripe.portal.live_config_id' );
			$stripe->billingPortal->configurations->update( $configuration_id, $this->_get_portal_config() );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function create_test_customer_portal() {
		try {
			$stripe = \Voxel\Stripe::getTestClient();
			$configuration = $stripe->billingPortal->configurations->create( $this->_get_portal_config() );
			\Voxel\set( 'settings.stripe.portal.test_config_id', $configuration->id );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function update_test_customer_portal() {
		try {
			$stripe = \Voxel\Stripe::getTestClient();
			$configuration_id = \Voxel\get( 'settings.stripe.portal.test_config_id' );
			$stripe->billingPortal->configurations->update( $configuration_id, $this->_get_portal_config() );
		} catch ( \Exception $e ) {
			\Voxel\log( $e );
		}
	}

	protected function _get_portal_config() {
		$portal = \Voxel\get( 'settings.stripe.portal', [] );
		return [
			'business_profile' => [
				'headline' => get_bloginfo( 'name' ),
				'privacy_policy_url' => get_permalink( \Voxel\get( 'templates.privacy_policy' ) ) ?: home_url('/'),
				'terms_of_service_url' => get_permalink( \Voxel\get( 'templates.terms' ) ) ?: home_url('/'),
			],
			'features' => [
				'payment_method_update' => [ 'enabled' => true ],
				'customer_update' => [
					'allowed_updates' => $portal['customer_update']['allowed_updates'] ?? [ 'email', 'address', 'phone' ],
					'enabled' => $portal['customer_update']['enabled'] ?? true,
				],
				'invoice_history' => [ 'enabled' => $portal['invoice_history'] ?? true ],
			],
		];
	}
}
