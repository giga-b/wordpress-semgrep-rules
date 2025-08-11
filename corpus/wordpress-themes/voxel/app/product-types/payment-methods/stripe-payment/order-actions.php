<?php

namespace Voxel\Product_Types\Payment_Methods\Stripe_Payment;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Order_Actions {

	public function get_vendor_actions(): array {
		$actions = [];
		if ( $this->order->get_status() === \Voxel\ORDER_PENDING_APPROVAL ) {
			$actions[] = [
				'action' => 'vendor.approve',
				'label' => _x( 'Approve', 'order actions', 'voxel' ),
				'type' => 'primary',
				'handler' => function() {
					if ( $this->is_zero_amount() ) {
						$this->order->set_status( \Voxel\ORDER_COMPLETED );
						$this->order->save();
					} else {
						$stripe = \Voxel\Stripe::getClient();
						$payment_intent = $stripe->paymentIntents->retrieve( $this->order->get_transaction_id() );
						$payment_intent = $payment_intent->capture();

						$this->payment_intent_updated( $payment_intent );
					}

					( new \Voxel\Events\Products\Orders\Vendor_Approved_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'vendor.decline',
				'label' => _x( 'Decline', 'order actions', 'voxel' ),
				'handler' => function() {
					if ( $this->is_zero_amount() ) {
						$this->order->set_status( \Voxel\ORDER_CANCELED );
						$this->order->save();
					} else {
						$stripe = \Voxel\Stripe::getClient();
						$payment_intent = $stripe->paymentIntents->cancel( $this->order->get_transaction_id() );

						$this->payment_intent_updated( $payment_intent );
					}

					( new \Voxel\Events\Products\Orders\Vendor_Declined_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		return $actions;
	}

	public function get_customer_actions(): array {
		$actions = [];
		if ( $this->order->get_status() === \Voxel\ORDER_PENDING_APPROVAL ) {
			$actions[] = [
				'action' => 'customer.cancel',
				'label' => _x( 'Cancel order', 'order customer actions', 'voxel' ),
				'handler' => function() {
					if ( $this->is_zero_amount() ) {
						$this->order->set_status( \Voxel\ORDER_CANCELED );
						$this->order->save();
					} else {
						$stripe = \Voxel\Stripe::getClient();
						$payment_intent = $stripe->paymentIntents->cancel( $this->order->get_transaction_id(), [
							'cancellation_reason' => 'requested_by_customer',
						] );

						$this->payment_intent_updated( $payment_intent );
					}

					( new \Voxel\Events\Products\Orders\Customer_Canceled_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
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
