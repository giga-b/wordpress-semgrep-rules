<?php

namespace Voxel\Controllers\Frontend\Payments\Stripe;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Subscriptions_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_stripe_subscriptions.checkout.success', '@checkout_success_endpoint' );
		$this->on( 'voxel_ajax_stripe_subscriptions.checkout.cancel', '@checkout_cancel_endpoint' );

		// checkout session events
		foreach ( [
			'checkout.session.completed',
			'checkout.session.async_payment_succeeded',
			'checkout.session.async_payment_failed',
		] as $event_type ) {
			$this->on( 'voxel/stripe_subscriptions/event:'.$event_type, '@checkout_session_updated', 10, 4 );
		}

		// subscription events
		foreach ( [
			'customer.subscription.created',
			'customer.subscription.updated',
			'customer.subscription.deleted',
		] as $event_type ) {
			$this->on( 'voxel/stripe_subscriptions/event:'.$event_type, '@subscription_updated', 10, 3 );
		}
	}

	protected function checkout_session_updated( $event, $session, $subscription, $order ) {
		$payment_method = $order->get_payment_method();
		$payment_method->subscription_updated( $subscription, $session );
	}

	protected function subscription_updated( $event, $subscription, $order ) {
		$payment_method = $order->get_payment_method();
		$payment_method->subscription_updated( $subscription );
	}

	protected function checkout_success_endpoint() {
		$order_id = $_REQUEST['order_id'] ?? null;
		$session_id = $_REQUEST['session_id'] ?? null;
		if ( ! is_numeric( $order_id ) || ! is_string( $session_id ) || empty( $session_id ) ) {
			exit;
		}

		$order = \Voxel\Product_Types\Orders\Order::find( [
			'id' => $order_id,
			'customer_id' => get_current_user_id(),
		] );

		if ( $order ) {
			wp_safe_redirect( $order->get_link() );
			exit;
		}

		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	protected function checkout_cancel_endpoint() {
		$order_id = $_REQUEST['order_id'] ?? null;
		$session_id = $_REQUEST['session_id'] ?? null;
		if ( ! is_numeric( $order_id ) || ! is_string( $session_id ) || empty( $session_id ) ) {
			exit;
		}

		$order = \Voxel\Product_Types\Orders\Order::find( [
			'id' => $order_id,
			'customer_id' => get_current_user_id(),
		] );

		if ( $order && $order->get_details( 'checkout.session_id' ) === $session_id ) {
			$order->delete();
		}

		wp_safe_redirect( \Voxel\get_redirect_url() );
		exit;
	}
}
