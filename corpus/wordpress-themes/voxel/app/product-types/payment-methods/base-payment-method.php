<?php

namespace Voxel\Product_Types\Payment_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Payment_Method {

	protected $order;

	abstract public function get_type(): string;

	abstract public function get_label(): string;

	abstract public function process_payment();

	public function get_vendor_actions(): array {
		return [];
	}

	public function get_customer_actions(): array {
		return [];
	}

	public function should_sync(): bool {
		return false;
	}

	public function sync(): void {
		//
	}

	public function __construct( \Voxel\Product_Types\Orders\Order $order ) {
		$this->order = $order;
	}

	public static function get_all(): array {
		return apply_filters( 'voxel/product-types/payment-methods', [
			'stripe_payment' => Stripe_Payment::class,
			'stripe_subscription' => Stripe_Subscription::class,
			'offline_payment' => Offline_Payment::class,

			'stripe_transfer' => Stripe_Transfer::class,
			'stripe_transfer_platform' => Stripe_Transfer_Platform::class,
		] );
	}

	public function get_line_items(): array {
		$line_items = [];

		// remove any non-ASCII characters from the URL (which trigger url_invalid in Stripe)
		$clean_url = function( $url ) {
			return is_string( $url ) ? preg_replace( '/[^\x20-\x7E]/', '', $url ) : null;
		};

		foreach ( $this->order->get_items() as $item ) {
			$currency = $item->get_currency();
			if ( $item->get_quantity() === null ) {
				$quantity = 1;
				$amount = $item->get_subtotal();
			} else {
				$quantity = $item->get_quantity();
				$amount = $item->get_subtotal_per_unit();
			}

			if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $currency ) ) {
				$amount_in_cents = $amount * 100;
			} else {
				$amount_in_cents = $amount;
			}

			$line_items[] = [
				'order_item' => $item,
				'id' => $item->get_id(),
				'quantity' => $quantity,
				'currency' => $currency,
				'amount' => $amount,
				'amount_in_cents' => $amount_in_cents,
				'product' => [
					'label' => $item->get_product_label(),
					'description' => $item->get_product_description(),
					'thumbnail_url' => $clean_url( $item->get_product_thumbnail_url() ),
				],
			];
		}

		return $line_items;
	}

	public function get_single_order_config(): array {
		return [];
	}

	public function get_admin_actions(): array {
		$actions = [];

		if ( in_array( $this->get_type(), [ 'stripe_payment', 'stripe_subscription' ], true ) ) {
			$actions[] = [
				'action' => 'admin.sync_with_stripe',
				'label' => _x( 'Sync with Stripe', 'order actions', 'voxel' ),
				'handler' => function() {
					$this->sync();
					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		$actions[] = [
			'action' => 'admin.view_in_backend',
			'label' => _x( 'Open order in backend', 'order actions', 'voxel' ),
			'handler' => function() {
				return wp_send_json( [
					'success' => true,
					'redirect_to' => $this->order->get_backend_link(),
				] );
			},
		];

		return $actions;
	}
}
