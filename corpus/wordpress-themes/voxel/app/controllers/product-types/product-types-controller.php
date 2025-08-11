<?php

namespace Voxel\Controllers\Product_Types;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Types_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return \Voxel\get('settings.product_types.enabled', true);
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_pages' );
		$this->on( 'voxel/backend/product-types/screen:manage-types', '@render_manage_types_screen' );
		$this->on( 'voxel/backend/product-types/screen:create-type', '@render_create_type_screen' );
		$this->on( 'admin_post_voxel_create_product_type', '@create_product_type' );
		$this->on( 'admin_post_voxel_save_product_types_settings', '@save_settings' );
		$this->on( 'voxel_ajax_backend.orders.delete_order', '@delete_order' );
	}

	protected function add_menu_pages() {
		add_menu_page(
			__( 'Product Types', 'voxel-backend' ),
			__( 'Product Types', 'voxel-backend' ),
			'manage_options',
			'voxel-product-types',
			function() {
				$action_key = $_GET['action'] ?? 'manage-types';
				$allowed_actions = ['manage-types', 'create-type', 'edit-type'];
				$action = in_array( $action_key, $allowed_actions, true ) ? $action_key : 'manage-types';
				do_action( 'voxel/backend/product-types/screen:'.$action );
			},
			sprintf( 'data:image/svg+xml;base64,%s', base64_encode( \Voxel\paint_svg(
				file_get_contents( locate_template( 'assets/images/svgs/shopping-bag.svg' ) ),
				'#a7aaad'
			) ) ),
			'0.300'
		);

		add_submenu_page(
			'voxel-product-types',
			__( 'Orders', 'voxel-backend' ),
			__( 'Orders', 'voxel-backend' ),
			'manage_options',
			'voxel-orders',
			function() {
				if ( ! empty( $_GET['order_id'] ) ) {
					$order = \Voxel\Product_Types\Orders\Order::get( $_GET['order_id'] );
					if ( ! $order ) {
						echo '<div class="wrap">'.__( 'Order not found.', 'voxel-backend' ).'</div>';
						return;
					}

					$payment_method = $order->get_payment_method();
					$customer = $order->get_customer();
					$vendor = $order->get_vendor();
					$order_items = $order->get_items();
					$child_orders = $order->get_child_orders();
					$parent_order = $order->get_parent_order();
					$vendor_fees = $order->get_vendor_fees_summary();
					$billing_interval = $order->get_billing_interval();
					$order_amount = $order->get_total();
					if ( ! is_numeric( $order_amount ) ) {
						$order_amount = $order->get_subtotal();
					}

					$stripe_base_url = $order->is_test_mode() ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';

					// $vendor_actions = $payment_method !== null ? $payment_method->get_vendor_actions() : [];
					// $admin_actions = $payment_method !== null ? $payment_method->get_admin_actions() : [];

					require locate_template( 'templates/backend/orders/edit-order.php' );
				} else {
					$table = new \Voxel\Product_Types\Order_List_Table;
					$table->prepare_items();
					require locate_template( 'templates/backend/orders/view-orders.php' );
				}
			},
			10
		);

		add_submenu_page(
			'voxel-product-types',
			__( 'Settings', 'voxel-backend' ),
			__( 'Settings', 'voxel-backend' ),
			'manage_options',
			'voxel-product-types-settings',
			function() {
				$schema = $this->get_settings_schema();
				foreach ( (array) \Voxel\get( 'product_settings', [] ) as $group_key => $group_values ) {
					if ( $prop = $schema->get_prop( $group_key ) ) {
						$prop->set_value( $group_values );
					}
				}

				$config = $schema->export();
				$config['tab'] = $_GET['tab'] ?? 'stripe_payments';

				$product_types = [];
				foreach ( array_merge( \Voxel\Product_Type::get_all(), [
					\Voxel\Product_Type::get_claims_product_type(),
					\Voxel\Product_Type::get_promotions_product_type(),
				] ) as $product_type ) {
					$product_types[ $product_type->get_key() ] = [
						'label' => $product_type->get_label(),
						'key' => $product_type->get_key(),
					];
				}

				$props = [
					'claimable_post_types' => array_map( function( $post_type ) {
						return [
							'key' => $post_type->get_key(),
							'label' => $post_type->get_label(),
						];
					}, \Voxel\Post_Type::get_voxel_types() ),
					'product_types' => $product_types,
					'shipping_countries' => \Voxel\Stripe\Country_Codes::shipping_supported(),
				];

				require locate_template( 'templates/backend/product-types/settings/settings.php' );
			},
			'100.0'
		);
	}

	protected function create_product_type() {
		check_admin_referer( 'voxel_manage_product_types' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['product_type'] ) || ! is_array( $_POST['product_type'] ) ) {
			die;
		}

		$key = sanitize_key( $_POST['product_type']['key'] ?? '' );
		$label = sanitize_text_field( $_POST['product_type']['label'] ?? '' );

		$product_types = \Voxel\get( 'product_types', [] );

		if ( $key && $label && ! isset( $product_types[ $key ] ) ) {
			$product_types[ $key ] = [
				'settings' => [
					'key' => $key,
					'label' => $label,
				],
				'fields' => [],
			];
		}

		\Voxel\set( 'product_types', $product_types );

		wp_safe_redirect( admin_url( 'admin.php?page=voxel-product-types&action=edit-type&product_type='.$key ) );
		exit;
	}

	protected function render_manage_types_screen() {
		$add_type_url = admin_url('admin.php?page=voxel-product-types&action=create-type');
		$product_types = \Voxel\Product_Type::get_all();

		require locate_template( 'templates/backend/product-types/view-product-types.php' );
	}

	protected function render_create_type_screen() {
		require locate_template( 'templates/backend/product-types/add-product-type.php' );
	}


	protected function save_settings() {
		check_admin_referer( 'voxel_save_product_types_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['config'] ) ) {
			die;
		}

		$previous_config = \Voxel\get( 'product_settings', [] );
		$submitted_config = json_decode( stripslashes( $_POST['config'] ), true );

		$schema = $this->get_settings_schema();
		$schema->set_value( $previous_config );

		foreach ( $submitted_config as $group_key => $group_values ) {
			if ( $prop = $schema->get_prop( $group_key ) ) {
				$prop->set_value( $group_values );
			}
		}

		$config = $schema->export();

		\Voxel\set( 'product_settings', Schema::optimize_for_storage( $config ) );

		wp_safe_redirect( add_query_arg( 'tab', $submitted_config['tab'] ?? null, admin_url( 'admin.php?page=voxel-product-types-settings' ) ) );
		die;
	}

	public static function get_settings_schema() {
		return Schema::Object( [
			'stripe_payments' => Schema::Object( [
				'order_approval' => Schema::enum( [ 'automatic', 'deferred', 'manual' ] )->default('automatic'),
				'billing_address_collection' => Schema::enum( [ 'auto', 'required' ] )->default('auto'),
				'tax_id_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
				'phone_number_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
				'promotion_codes' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),

			'stripe_subscriptions' => Schema::Object( [
				'billing_address_collection' => Schema::enum( [ 'auto', 'required' ] )->default('auto'),
				'tax_id_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
				'customer_actions' => Schema::Object( [
					'cancel_renewal' => Schema::Object( [
						'enabled' => Schema::Bool()->default(true),
					] ),
					'cancel_subscription' => Schema::Object( [
						'enabled' => Schema::Bool()->default(false),
					] ),
				] ),
				'phone_number_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
				'promotion_codes' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),

			'offline_payments' => Schema::Object( [
				'order_approval' => Schema::enum( [ 'automatic', 'manual' ] )->default('automatic'),
				'notes_to_customer' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'content' => Schema::String(),
				] ),
			] ),

			'multivendor' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'charge_type' => Schema::Enum( [ 'destination_charges', 'separate_charges_and_transfers' ] )->default('destination_charges'),
				'settlement_merchant' => Schema::Enum( [ 'platform', 'vendor' ] )->default('platform'),
				'subscriptions' => Schema::Object( [
					'charge_type' => Schema::Enum( [ 'destination_charges' ] )->default('destination_charges'),
					'settlement_merchant' => Schema::Enum( [ 'platform', 'vendor' ] )->default('platform'),
				] ),
				'vendor_fees' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
					'type' => Schema::Enum( [ 'fixed', 'percentage' ] )->default('fixed'),
					'fixed_amount' => Schema::Float()->min(0),
					'percentage_amount' => Schema::Float()->min(0)->max(100),
					'apply_to' => Schema::Enum( [ 'all', 'custom' ] )->default('all'),
					'conditions' => Schema::Object_List( [
						'source' => Schema::Enum( [ 'vendor_plan', 'vendor_role', 'vendor_id' ] ),
						'comparison' => Schema::Enum( [ 'equals', 'not_equals' ] ),
						'value' => Schema::String(),
					] )->default([]),
				] )->default([]),
				'shipping' => Schema::Object( [
					'responsibility' => Schema::Enum( [ 'platform', 'vendor' ] )->default('platform'),
				] ),
			] ),

			'claims' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'proof_of_ownership' => Schema::Enum( [ 'required', 'optional', 'disabled' ] )->default('optional'),
				'prices' => Schema::Object_List( [
					'post_type' => Schema::String()->default(''),
					'amount' => Schema::Float()->min(0),
				] )->default( [] ),
				'payments' => Schema::Object( [
					'mode' => Schema::Enum( [ 'payment', 'offline' ] )->default('payment'),
				] ),
				'order_approval' => Schema::enum( [ 'automatic', 'manual' ] )->default('automatic'),
			] ),

			'promotions' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'packages' => Schema::Object_List( [
					'key' => Schema::String(),
					'post_types' => Schema::List()->default([]),
					'duration' => Schema::Object( [
						'type' => Schema::Enum( ['days'] ),
						'amount' => Schema::Int()->min(1)->default(7),
					] ),
					'priority' => Schema::Int()->min(1)->default(2),
					'price' => Schema::Object( [
						'amount' => Schema::Float()->min(0),
					] ),
					'ui' => Schema::Object( [
						'label' => Schema::String(),
						'description' => Schema::String(),
						'icon' => Schema::String(),
						'color' => Schema::String(),
					] ),
				] )->default( [] ),
				'payments' => Schema::Object( [
					'mode' => Schema::Enum( [ 'payment', 'offline' ] )->default('payment'),
				] ),
				'order_approval' => Schema::enum( [ 'automatic', 'manual' ] )->default('automatic'),
			] ),

			'tax_collection' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'collection_method' => Schema::enum( [ 'stripe_tax', 'tax_rates' ] )->default('stripe_tax'),
				'stripe_tax' => Schema::Object( [
					'product_types' => Schema::Keyed_Object_List( [
						'tax_behavior' => Schema::Enum( [ 'default', 'inclusive', 'exclusive' ] )->default('default'),
						'tax_code' => Schema::String()->default(''),
					] )->validator( function( $item, $key ) {
						return \Voxel\Product_Type::get( $key ) !== null;
					} ),
				] ),
				'tax_rates' => Schema::Object( [
					'product_types' => Schema::Keyed_Object_List( [
						'fixed_rates' => Schema::Object( [
							'live_mode' => Schema::List()->validator('is_string')->default([]),
							'test_mode' => Schema::List()->validator('is_string')->default([]),
						] ),
						'dynamic_rates' => Schema::Object( [
							'live_mode' => Schema::List()->validator('is_string')->default([]),
							'test_mode' => Schema::List()->validator('is_string')->default([]),
						] ),
						'calculation_method' => Schema::enum( [ 'fixed', 'dynamic' ] )->default('fixed'),
					] )->validator( function( $item, $key ) {
						return \Voxel\Product_Type::get( $key ) !== null;
					} ),
				] ),
			] ),

			'cart_summary' => Schema::Object( [
				'guest_customers' => Schema::Object( [
					'behavior' => Schema::Enum( [ 'require_account', 'proceed_with_email' ] )->default( 'proceed_with_email' ),
					'proceed_with_email' => Schema::Object( [
						'require_verification' => Schema::Bool()->default(true),
						'require_tos' => Schema::Bool()->default(false),
						'email_account_details' => Schema::Bool()->default(true),
					] ),
				] ),
			] ),

			'shipping' => Schema::Object( [
				'shipping_classes' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
					'description' => Schema::String(),
				] )->default([]),
				'shipping_zones' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
					'regions' => Schema::Object_List( [
						'type' => Schema::Enum( [ 'country' ] )->default('country'),
						'country' => Schema::String(),
					] )->default([]),
					'rates' => Schema::Object_List( [
						'key' => Schema::String(),
						'label' => Schema::String(),
						'type' => Schema::Enum( [ 'free_shipping', 'fixed_rate' ] )->default('free_shipping'),
						'free_shipping' => Schema::Object( [
							'requirements' => Schema::Enum( [ 'none', 'minimum_order_amount' ] )->default('none'),
							'minimum_order_amount' => Schema::Float()->min(0),
							'delivery_estimate' => Schema::Object( [
								'minimum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
								'maximum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
							] ),
						] ),
						'fixed_rate' => Schema::Object( [
							'tax_behavior' => Schema::Enum( [ 'default', 'inclusive', 'exclusive' ] )->default('default'),
							'tax_code' => Schema::Enum( [ 'shipping', 'nontaxable' ] )->default('shipping'),
							'delivery_estimate' => Schema::Object( [
								'minimum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
								'maximum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
							] ),
							'amount_per_unit' => Schema::Float()->min(0)->default(0),
							'shipping_classes' => Schema::Object_List( [
								'shipping_class' => Schema::String(),
								'amount_per_unit' => Schema::Float()->min(0)->default(0),
							] )->default([]),
						] ),
					] )->default([]),
				] )->default([]),
			] ),

			'orders' => Schema::Object( [
				'managed_by' => Schema::Enum( [ 'platform', 'product_author' ] )->default('product_author'),
				'direct_messages' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
			] ),
		] );
	}

	protected function delete_order() {
		if ( ! current_user_can('manage_options') ) {
			wp_safe_redirect( admin_url( 'admin.php?page=voxel-orders' ) );
			exit;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'voxel_backend_delete_order' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=voxel-orders' ) );
			exit;
		}

		$order = \Voxel\Product_Types\Orders\Order::get( $_REQUEST['order_id'] ?? null );
		$order->delete();

		wp_safe_redirect( admin_url( 'admin.php?page=voxel-orders' ) );
		exit;
	}
}
