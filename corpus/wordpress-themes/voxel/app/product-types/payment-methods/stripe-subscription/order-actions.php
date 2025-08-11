<?php

namespace Voxel\Product_Types\Payment_Methods\Stripe_Subscription;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Order_Actions {

	public function get_vendor_actions(): array {
		$actions = [];

		return $actions;
	}

	public function get_customer_actions(): array {
		$actions = [];

		if ( \Voxel\get( 'product_settings.stripe_subscriptions.customer_actions.cancel_renewal.enabled', true ) ) {
			if (
				! in_array( $this->order->get_status(), [ 'sub_canceled', 'sub_incomplete_expired', 'pending_payment' ], true )
				&& ! $this->order->get_details( 'subscription.cancel_at_period_end' )
			) {
				$actions[] = [
					'action' => 'customer.subscriptions.cancel_renewal',
					'label' => _x( 'Cancel renewals', 'order customer actions', 'voxel' ),
					'handler' => function() {
						$stripe = \Voxel\Stripe::getClient();
						$subscription = $stripe->subscriptions->update( $this->order->get_transaction_id(), [
							'cancel_at_period_end' => true,
						] );

						$this->subscription_updated( $subscription );

						return wp_send_json( [
							'success' => true,
						] );
					},
				];
			}

			if ( $this->order->get_details( 'subscription.cancel_at_period_end' ) ) {
				$actions[] = [
					'action' => 'customer.subscriptions.enable_renewal',
					'label' => _x( 'Enable renewals', 'order customer actions', 'voxel' ),
					'handler' => function() {
						$stripe = \Voxel\Stripe::getClient();
						$subscription = $stripe->subscriptions->update( $this->order->get_transaction_id(), [
							'cancel_at_period_end' => false,
						] );

						$this->subscription_updated( $subscription );

						return wp_send_json( [
							'success' => true,
						] );
					},
				];
			}
		}

		if ( in_array( $this->order->get_status(), [ 'sub_incomplete', 'sub_unpaid', 'sub_past_due' ], true ) ) {
			$actions[] = [
				'action' => 'customer.subscriptions.finalize_payment',
				'label' => 'Finalize payment',
				'handler' => function() {
					$stripe = \Voxel\Stripe::getClient();

					$invoice = $stripe->invoices->retrieve( $this->order->get_details( 'subscription.latest_invoice.id' ) );

					if ( $invoice->status === 'draft' ) {
						$stripe->invoices->finalizeInvoice( $invoice->id, [
							'auto_advance' => true,
						] );
					} else {
						if ( $invoice->hosted_invoice_url ) {
							return wp_send_json( [
								'success' => true,
								'redirect_to' => $invoice->hosted_invoice_url,
							] );
						} else {
							$stripe->invoices->pay( $invoice->id );
						}
					}

					$subscription = $stripe->subscriptions->retrieve( $this->order->get_transaction_id() );
					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		if ( \Voxel\get( 'product_settings.stripe_subscriptions.customer_actions.cancel_subscription.enabled', false ) ) {
			if ( ! in_array( $this->order->get_status(), [ 'sub_canceled', 'sub_incomplete_expired', 'pending_payment' ], true ) ) {
				$actions[] = [
					'action' => 'customer.subscriptions.cancel_renewal',
					'label' => _x( 'Cancel subscription', 'order customer actions', 'voxel' ),
					'confirm' => 'This action is irreversible. Proceed anyway?',
					'handler' => function() {
						$stripe = \Voxel\Stripe::getClient();
						$subscription = $stripe->subscriptions->cancel( $this->order->get_transaction_id() );
						$this->subscription_updated( $subscription );

						return wp_send_json( [
							'success' => true,
						] );
					},
				];
			}
		}

		$actions[] = [
			'action' => 'customer.access_portal',
			'label' => _x( 'Customer portal', 'order customer actions', 'voxel' ),
			'handler' => function() {
				$stripe = \Voxel\Stripe::getClient();
				$session = $stripe->billingPortal->sessions->create( [
					'customer' => \Voxel\current_user()->get_stripe_customer_id(),
					'configuration' => \Voxel\Stripe::get_portal_configuration_id(),
					'return_url' => $this->order->get_link(),
				] );

				return wp_send_json( [
					'success' => true,
					'redirect_to' => $session->url,
				] );
			},
		];

		return $actions;
	}

}
