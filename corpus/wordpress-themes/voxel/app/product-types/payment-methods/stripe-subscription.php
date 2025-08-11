<?php

namespace Voxel\Product_Types\Payment_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Subscription extends Base_Payment_Method {
	use Stripe_Subscription\Order_Actions;
	use Traits\Stripe_Commons;

	public function get_type(): string {
		return 'stripe_subscription';
	}

	public function get_label(): string {
		return _x( 'Stripe subscription', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		try {
			$customer = $this->order->get_customer();
			$stripe_customer = $customer->get_or_create_stripe_customer();
			$billing_address_collection = \Voxel\get( 'product_settings.stripe_subscriptions.billing_address_collection', 'auto' );
			$tax_id_collection = !! \Voxel\get( 'product_settings.stripe_subscriptions.tax_id_collection.enabled', true );

			$tax_collection_method = null;
			if ( \Voxel\get( 'product_settings.tax_collection.enabled' ) ) {
				$tax_collection_method = \Voxel\get( 'product_settings.tax_collection.collection_method', 'stripe_tax' );
			}

			$args = [
				'client_reference_id' => sprintf( 'order:%d', $this->order->get_id() ),
				'customer' => $stripe_customer->id,
				'mode' => 'subscription',
				'currency' => $this->order->get_currency(),
				'customer_update' => [
					'address' => 'auto',
					'name' => 'auto',
					'shipping' => 'auto',
				],
				'locale' => 'auto',
				'line_items' => array_map( function( $line_item ) use ( $tax_collection_method ) {
					$order_item = $line_item['order_item'];
					$data = [
						'quantity' => $line_item['quantity'],
						'price_data' => [
							'currency' => $line_item['currency'],
							'unit_amount_decimal' => $line_item['amount_in_cents'],
							'product_data' => [
								'name' => $line_item['product']['label'],
							],
						],
					];

					if ( ! empty( $line_item['product']['description'] ) ) {
						$data['price_data']['product_data']['description'] = $line_item['product']['description'];
					}

					if ( ! empty( $line_item['product']['thumbnail_url'] ) ) {
						$data['price_data']['product_data']['images'] = [ $line_item['product']['thumbnail_url'] ];
					}

					if ( $tax_collection_method === 'stripe_tax' ) {
						$tax_behavior = \Voxel\get( sprintf(
							'product_settings.tax_collection.stripe_tax.product_types.%s.tax_behavior',
							$order_item->get_product_type_key()
						), 'default' );

						if ( in_array( $tax_behavior, [ 'inclusive', 'exclusive' ], true ) ) {
							$data['price_data']['tax_behavior'] = $tax_behavior;
						}

						$tax_code = \Voxel\get( sprintf(
							'product_settings.tax_collection.stripe_tax.product_types.%s.tax_code',
							$order_item->get_product_type_key()
						) );

						if ( ! empty( $tax_code ) ) {
							$data['price_data']['product_data']['tax_code'] = $tax_code;
						}
					} elseif ( $tax_collection_method === 'tax_rates' ) {
						$tax_calculation_method = \Voxel\get( sprintf(
							'product_settings.tax_collection.tax_rates.product_types.%s.calculation_method',
							$order_item->get_product_type_key()
						), 'fixed' );

						if ( $tax_calculation_method === 'fixed' ) {
							$tax_rates = \Voxel\get( sprintf(
								'product_settings.tax_collection.tax_rates.product_types.%s.fixed_rates.%s',
								$order_item->get_product_type_key(),
								\Voxel\Stripe::is_test_mode() ? 'test_mode' : 'live_mode'
							), [] );

							if ( ! empty( $tax_rates ) ) {
								$data['tax_rates'] = $tax_rates;
							}
						} elseif ( $tax_calculation_method === 'dynamic' ) {
							$dynamic_tax_rates = \Voxel\get( sprintf(
								'product_settings.tax_collection.tax_rates.product_types.%s.dynamic_rates.%s',
								$order_item->get_product_type_key(),
								\Voxel\Stripe::is_test_mode() ? 'test_mode' : 'live_mode'
							), [] );

							if ( ! empty( $dynamic_tax_rates ) ) {
								$data['dynamic_tax_rates'] = $dynamic_tax_rates;
							}
						}
					}

					$data['price_data']['recurring'] = [
						'interval' => $order_item->get_details('subscription.unit'),
						'interval_count' => $order_item->get_details('subscription.frequency'),
					];

					return $data;
				}, $this->get_line_items() ),
				'subscription_data' => [
					'metadata' => [
						'voxel:payment_for' => 'order',
						'voxel:order_id' => $this->order->get_id(),
					],
				],
				'success_url' => $this->get_success_url(),
				'cancel_url' => $this->get_cancel_url(),
				'metadata' => [
					'voxel:payment_for' => 'order',
					'voxel:order_id' => $this->order->get_id(),
				],
				'billing_address_collection' => $billing_address_collection === 'required' ? 'required' : 'auto',
				'tax_id_collection' => [
					'enabled' => $tax_id_collection,
				],
				'allow_promotion_codes' => !! \Voxel\get( 'product_settings.stripe_subscriptions.promotion_codes.enabled', false ),
			];

			if ( $tax_collection_method === 'stripe_tax' ) {
				$args['automatic_tax'] = [
					'enabled' => true,
				];
			}

			if ( \Voxel\get( 'product_settings.stripe_subscriptions.phone_number_collection.enabled' ) ) {
				$args['phone_number_collection'] = [
					'enabled' => true,
				];
			}

			$vendor = $this->order->get_vendor();
			if ( $vendor !== null && $vendor->is_active_vendor() ) {
				if ( \Voxel\get('product_settings.multivendor.subscriptions.charge_type') === 'destination_charges' ) {
					$args['subscription_data']['application_fee_percent'] = $this->get_application_fee_percent();
					$args['subscription_data']['transfer_data'] = [
						'destination' => $vendor->get_stripe_vendor_id(),
					];

					$args['allow_promotion_codes'] = false;

					if ( \Voxel\get('product_settings.multivendor.subscriptions.settlement_merchant') === 'vendor' ) {
						$args['subscription_data']['on_behalf_of'] = $vendor->get_stripe_vendor_id();

						if ( $tax_collection_method === 'stripe_tax' ) {
							$args['automatic_tax'] = [
								'enabled' => true,
								'liability' => [
									'type' => 'self',
								],
							];

							$args['subscription_data']['invoice_settings'] = [
								'issuer' => [
									'type' => 'self',
								],
							];
						}
					}

					$this->order->set_details( 'multivendor.mode', 'destination_charges' );
					$this->order->set_details( 'multivendor.vendor_fees', $vendor->get_vendor_fees() );
				}
			}

			$session = \Voxel\Vendor\Stripe\Checkout\Session::create( $args );

			$total_order_amount = $session->amount_total;
			if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $session->currency ) ) {
				$total_order_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );
			$this->order->set_details( 'checkout.session_id', $session->id );

			$this->order->save();

			return wp_send_json( [
				'success' => true,
				'redirect_url' => $session->url,
			] );
		} catch ( \Voxel\Vendor\Stripe\Exception\ApiErrorException | \Voxel\Vendor\Stripe\Exception\InvalidArgumentException $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => _x( 'Something went wrong', 'checkout', 'voxel' ),
				'debug' => [
					'type' => 'stripe_error',
					'code' => method_exists( $e, 'getStripeCode' ) ? $e->getStripeCode() : $e->getCode(),
					'message' => $e->getMessage(),
				],
			] );
		}
	}

	protected function get_success_url() {
		return add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_subscriptions.checkout.success',
			'session_id' => '{CHECKOUT_SESSION_ID}',
			'order_id' => $this->order->get_id(),
		], home_url('/') );
	}

	protected function get_cancel_url() {
		$redirect_url = wp_get_referer() ?: home_url('/');
		$redirect_url = add_query_arg( 't', time(), $redirect_url );

		return add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_subscriptions.checkout.cancel',
			'session_id' => '{CHECKOUT_SESSION_ID}',
			'order_id' => $this->order->get_id(),
			'redirect_to' => rawurlencode( $redirect_url ),
		], home_url('/') );
	}

	public function subscription_updated(
		\Voxel\Vendor\Stripe\Subscription $subscription,
		\Voxel\Vendor\Stripe\Checkout\Session $session = null
	) {
		$stripe = \Voxel\Stripe::getClient();

		$this->order->set_status( sprintf( 'sub_%s', $subscription->status ) );
		$this->order->set_transaction_id( $subscription->id );

		$this->order->set_details( 'subscription', [
			'id' => $subscription->id,
			'cancel_at_period_end' => $subscription->cancel_at_period_end,
			'currency' => $subscription->currency,
			'current_period_end' => $subscription->current_period_end,
			'current_period_start' => $subscription->current_period_start,
			'customer' => $subscription->customer,
			'status' => $subscription->status,
			'cancel_at' => $subscription->cancel_at,
			'canceled_at' => $subscription->canceled_at,
			'cancellation_details' => [
				'reason' => $subscription->cancellation_details->reason,
			],
			'ended_at' => $subscription->ended_at,
			'livemode' => $subscription->livemode,
			'trial_end' => $subscription->trial_end,
			'application_fee_percent' => $subscription->application_fee_percent,
			'transfer_data' => [
				'destination' => $subscription->transfer_data->destination ?? null,
			],
			'items' => array_map( function( $item ) {
				$price = $item->price;
				return [
					'id' => $item->id,
					'price' => [
						'currency' => $price->currency,
						'recurring' => [
							'interval' => $price->recurring->interval,
							'interval_count' => $price->recurring->interval_count,
						],
						'unit_amount' => $price->unit_amount,
					],
				];
			}, $subscription->items->data ),
			'latest_invoice' => null,
		] );

		if ( $subscription->latest_invoice !== null ) {
			if ( $subscription->latest_invoice instanceof \Voxel\Vendor\Stripe\Invoice ) {
				$latest_invoice = $subscription->latest_invoice;
			} else {
				$latest_invoice = $stripe->invoices->retrieve( $subscription->latest_invoice, [] );
			}
		}

		if ( $latest_invoice instanceof \Voxel\Vendor\Stripe\Invoice ) {
			$this->order->set_details( 'subscription.latest_invoice', [
				'id' => $latest_invoice->id,
				'currency' => $latest_invoice->currency,
				'status' => $latest_invoice->status,
				'total' => $latest_invoice->total,
				'amount_shipping' => $latest_invoice->amount_shipping,
				'billing_reason' => $latest_invoice->billing_reason,
				'tax' => $latest_invoice->tax,
				'application_fee_amount' => $latest_invoice->application_fee_amount,
				'transfer_data' => [
					'destination' => $latest_invoice->transfer_data->destination ?? null,
				],
				'_discount' => array_sum( array_column( $latest_invoice->total_discount_amounts, 'amount' ) ),
			] );

			$total_order_amount = $latest_invoice->total;
			if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $latest_invoice->currency ) ) {
				$total_order_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );

			$tax_amount = $this->_get_tax_amount_from_invoice( $latest_invoice );
			$discount_amount = $this->_get_discount_amount_from_invoice( $latest_invoice );
			$shipping_amount = $this->_get_shipping_amount_from_invoice( $latest_invoice );

			if ( $tax_amount !== null ) {
				$this->order->set_details( 'pricing.tax', $tax_amount );
			}

			if ( $discount_amount !== null ) {
				$this->order->set_details( 'pricing.discount', $discount_amount );
			}

			if ( $shipping_amount !== null ) {
				$this->order->set_details( 'pricing.shipping', $shipping );
			}
		}

		$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );

		if ( $session ) {
			$this->order->set_details( 'checkout.session_details', $this->_get_checkout_session_details_for_storage( $session ) );
		}

		$this->order->save();
	}

	protected function _get_checkout_session_details_for_storage( \Voxel\Vendor\Stripe\Checkout\Session $session ) {
		return [
			'customer_details' => [
				'address' => [
					'city' => $session->customer_details->address->city ?? null,
					'country' => $session->customer_details->address->country ?? null,
					'line1' => $session->customer_details->address->line1 ?? null,
					'line2' => $session->customer_details->address->line2 ?? null,
					'postal_code' => $session->customer_details->address->postal_code ?? null,
					'state' => $session->customer_details->address->state ?? null,
				],
				'email' => $session->customer_details->email ?? null,
				'name' => $session->customer_details->name ?? null,
				'phone' => $session->customer_details->phone ?? null,
			],
			'shipping_details' => [
				'address' => [
					'city' => $session->shipping_details->address->city ?? null,
					'country' => $session->shipping_details->address->country ?? null,
					'line1' => $session->shipping_details->address->line1 ?? null,
					'line2' => $session->shipping_details->address->line2 ?? null,
					'postal_code' => $session->shipping_details->address->postal_code ?? null,
					'state' => $session->shipping_details->address->state ?? null,
				],
				'name' => $session->shipping_details->name ?? null,
			],
		];
	}

	protected function _get_tax_amount_from_invoice( \Voxel\Vendor\Stripe\Invoice $invoice ) {
		$tax_amount = $invoice->tax;
		if ( ! is_numeric( $tax_amount ) ) {
			return null;
		}

		if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $invoice->currency ) ) {
			$tax_amount /= 100;
		}

		if ( $tax_amount === 0 ) {
			return null;
		}

		return $tax_amount;
	}

	protected function _get_discount_amount_from_invoice( \Voxel\Vendor\Stripe\Invoice $invoice ) {
		$discount_amount = array_sum( array_column( $invoice->total_discount_amounts, 'amount' ) );
		if ( ! is_numeric( $discount_amount ) ) {
			return null;
		}

		if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $invoice->currency ) ) {
			$discount_amount /= 100;
		}

		if ( $discount_amount === 0 ) {
			return null;
		}

		return $discount_amount;
	}

	protected function _get_shipping_amount_from_invoice( \Voxel\Vendor\Stripe\Invoice $invoice ) {
		$shipping_amount = $invoice->amount_shipping;
		if ( ! is_numeric( $shipping_amount ) ) {
			return null;
		}

		if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $invoice->currency ) ) {
			$shipping_amount /= 100;
		}

		if ( $shipping_amount === 0 ) {
			return null;
		}

		return $shipping_amount;
	}

	public function should_sync(): bool {
		return ! $this->order->get_details( 'checkout.last_synced_at' );
	}

	public function sync(): void {
		$stripe = \Voxel\Stripe::getClient();
		if ( $transaction_id = $this->order->get_transaction_id() ) {
			$subscription = $stripe->subscriptions->retrieve( $transaction_id );
			$this->subscription_updated( $subscription );
		} elseif ( $checkout_session_id = $this->order->get_details( 'checkout.session_id' ) ) {
			$session = $stripe->checkout->sessions->retrieve( $checkout_session_id, [
				'expand' => [ 'subscription' ],
			] );

			$subscription = $session->subscription;
			if ( $subscription !== null ) {
				$this->subscription_updated( $subscription, $session );
			}
		} else {
			//
		}
	}

	public function get_single_order_config(): array {
		$tz_offset = (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$config = [
			'cancel_at_period_end' => $this->order->get_details( 'subscription.cancel_at_period_end' ),
			'current_period_end' => $this->order->get_details( 'subscription.current_period_end' ),
			'status' => $this->order->get_details( 'subscription.status' ),
			'trial_end' => $this->order->get_details( 'subscription.trial_end' ),
		];

		if ( is_numeric( $config['current_period_end'] ) ) {
			$config['current_period_end_display'] = \Voxel\date_format( $config['current_period_end'] + $tz_offset );
		}

		if ( is_numeric( $config['trial_end'] ) ) {
			$config['trial_end_display'] = \Voxel\date_format( $config['trial_end'] + $tz_offset );
		}

		return $config;
	}

	protected function get_application_fee_percent() {
		$currency = $this->order->get_currency();
		$subtotal_in_cents = $this->order->get_subtotal();
		if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $currency ) ) {
			$subtotal_in_cents *= 100;
		}

		$application_fee_amount = 0;
		foreach ( $this->order->get_vendor()->get_vendor_fees() as $fee ) {
			if ( $fee['type'] === 'fixed' ) {
				$fee_amount_in_cents = $fee['fixed_amount'];
				if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $currency ) ) {
					$fee_amount_in_cents *= 100;
				}

				$application_fee_amount += $fee_amount_in_cents;
			} elseif ( $fee['type'] === 'percentage' ) {
				$pct = $fee['percentage_amount'];
				$application_fee_amount += ( $subtotal_in_cents * ( $pct / 100 ) );
			}
		}

		if ( $subtotal_in_cents <= 0 || $subtotal_in_cents < $application_fee_amount ) {
			return 0;
		}

		$percentage = abs( ( $application_fee_amount / $subtotal_in_cents ) * 100 );

		return round( $percentage, 2 );
	}

	public function get_vendor_fees_summary(): array {
		if ( $this->order->get_details('multivendor.mode') === 'destination_charges' ) {
			$currency = $this->order->get_currency();
			$application_fee_amount = $this->order->get_details( 'subscription.latest_invoice.application_fee_amount' );
			if ( ! is_numeric( $application_fee_amount ) ) {
				return [];
			}

			if ( ! \Voxel\Stripe\Currencies::is_zero_decimal( $currency ) ) {
				$application_fee_amount /= 100;
			}

			$details = [
				'total' => $application_fee_amount,
				'breakdown' => [],
			];

			foreach ( (array) $this->order->get_details('multivendor.vendor_fees', []) as $fee ) {
				if ( ( $fee['type'] ?? null ) === 'fixed' ) {
					if ( ! is_numeric( $fee['fixed_amount'] ?? null ) && $fee['fixed_amount'] > 0 ) {
						continue;
					}

					$subtotal = $this->order->get_subtotal();
					if ( ! is_numeric( $subtotal ) || $subtotal <= 0 || $subtotal < $fee['fixed_amount'] ) {
						continue;
					}

					$pct = round( abs( ( $fee['fixed_amount'] / $subtotal ) * 100 ), 2 );

					$details['breakdown'][] = [
						'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
						// 'content' => \Voxel\currency_format( $fee['fixed_amount'], $currency, false ),
						'content' => $pct.'%',
					];
				} elseif ( ( $fee['type'] ?? null ) === 'percentage' ) {
					if ( ! is_numeric( $fee['percentage_amount'] ?? null ) && $fee['percentage_amount'] > 0 && $fee['percentage_amount'] <= 100 ) {
						continue;
					}

					$details['breakdown'][] = [
						'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
						'content' => round( $fee['percentage_amount'], 2 ).'%',
					];
				}
			}

			return $details;
		} else {
			return [];
		}
	}
}
