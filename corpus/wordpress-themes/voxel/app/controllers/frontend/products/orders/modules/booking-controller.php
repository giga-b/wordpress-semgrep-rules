<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.single_order.bookings.cancel_booking', '@cancel_booking' );
		$this->on( 'voxel_ajax_products.single_order.bookings.reschedule_booking', '@reschedule_booking' );
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
	}

	protected function cancel_booking() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			if ( ! ( $order_id && $order_item_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			if ( ! ( $current_user->is_customer_of( $order->get_id() ) || $current_user->is_vendor_of( $order->get_id() ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 111 );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! ( $order_item && $order_item->get_type() === 'booking' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 109 );
			}

			$product_type = $order_item->get_product_type();
			if ( ! $product_type ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 110 );
			}

			if ( $current_user->is_customer_of( $order->get_id() ) && ! $product_type->config('modules.booking.actions.cancel.customer.enabled') ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 112 );
			}

			if ( $current_user->is_vendor_of( $order->get_id() ) && ! $product_type->config('modules.booking.actions.cancel.vendor.enabled') ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 118 );
			}

			if ( $order_item->get_details( 'booking_status' ) === 'canceled' ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 113 );
			}

			$post = $order_item->get_post();
			$field = $order_item->get_product_field();
			if ( ! ( $field && $post ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 114 );
			}

			$booking = $field->get_product_field('booking');
			if ( ! $booking ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 115 );
			}

			$order_item->set_details( 'booking_status', 'canceled' );
			$order_item->save();

			$booking->cache_fully_booked_dates();

			$post = \Voxel\Post::force_get( $post->get_id() );
			$post->should_index() ? $post->index() : $post->unindex();

			if ( $current_user->is_vendor_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Canceled_By_Vendor_Event( $product_type ) )->dispatch( $order_item->get_id() );
			} elseif ( $current_user->is_customer_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Canceled_By_Customer_Event( $product_type ) )->dispatch( $order_item->get_id() );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function reschedule_booking() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			$order_item_id = absint( $_REQUEST['order_item_id'] ?? null );
			if ( ! ( $order_id && $order_item_id ) ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			if ( ! ( $current_user->is_customer_of( $order->get_id() ) || $current_user->is_vendor_of( $order->get_id() ) ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 111 );
			}

			$order_item = $order->get_item( $order_item_id );
			if ( ! ( $order_item && $order_item->get_type() === 'booking' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 109 );
			}

			$product_type = $order_item->get_product_type();
			if ( ! $product_type ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 110 );
			}

			if ( $current_user->is_customer_of( $order->get_id() ) && ! $product_type->config('modules.booking.actions.reschedule.customer.enabled') ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 112 );
			}

			if ( $current_user->is_vendor_of( $order->get_id() ) && ! $product_type->config('modules.booking.actions.reschedule.vendor.enabled') ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 118 );
			}

			if ( $order_item->get_details( 'booking_status' ) === 'canceled' ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 113 );
			}

			$post = $order_item->get_post();
			$field = $order_item->get_product_field();
			if ( ! ( $field && $post ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 114 );
			}

			$booking = $field->get_product_field('booking');
			$form_booking = $field->get_form_field('form-booking');
			if ( ! ( $booking && $form_booking ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 115 );
			}

			if ( $order_item->get_details( 'booking.type' ) === 'timeslots' ) {
				$date = \DateTime::createFromFormat( 'Y-m-d', $_REQUEST['reschedule_to']['date'] ?? null );
				$from = \DateTime::createFromFormat( 'H:i', $_REQUEST['reschedule_to']['slot']['from'] ?? null );
				$to = \DateTime::createFromFormat( 'H:i', $_REQUEST['reschedule_to']['slot']['to'] ?? null );

				if ( $date === false || $from === false || $to === false ) {
					throw new \Exception( _x( 'Please select a date and time', 'reschedule booking', 'voxel' ), 116 );
				}

				$form_booking->validate_timeslot( $date, $from, $to );

				$order_item->set_details( 'booking.date', $date->format('Y-m-d') );
				$order_item->set_details( 'booking.slot.from', $from->format('H:i') );
				$order_item->set_details( 'booking.slot.to', $to->format('H:i') );
				$order_item->save();
			} else {
				$date = \DateTime::createFromFormat( 'Y-m-d', $_REQUEST['reschedule_to']['date'] ?? null );
				if ( $date === false ) {
					throw new \Exception( _x( 'Please select a date', 'reschedule booking', 'voxel' ), 117 );
				}

				$form_booking->validate_single_day( $date );

				$order_item->set_details( 'booking.date', $date->format('Y-m-d') );
				$order_item->save();
			}

			$booking->cache_fully_booked_dates();

			$post = \Voxel\Post::force_get( $post->get_id() );
			$post->should_index() ? $post->index() : $post->unindex();

			if ( $current_user->is_vendor_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Rescheduled_By_Vendor_Event( $product_type ) )->dispatch( $order_item->get_id() );
			} elseif ( $current_user->is_customer_of( $order->get_id() ) ) {
				( new \Voxel\Events\Bookings\Booking_Rescheduled_By_Customer_Event( $product_type ) )->dispatch( $order_item->get_id() );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function order_updated( $order ) {
		if (
			$order->get_previous_status() === \Voxel\ORDER_PENDING_PAYMENT
			&& in_array( $order->get_status(), [ 'completed', 'pending_approval', 'sub_active' ], true )
		) {
			foreach ( $order->get_items() as $order_item ) {
				$product_type = $order_item->get_product_type();
				if ( $order_item->get_type() === 'booking' && $product_type !== null ) {
					( new \Voxel\Events\Bookings\Booking_Placed_Event( $product_type ) )->dispatch( $order_item->get_id() );
				}
			}
		}

		if (
			! in_array( $order->get_previous_status(), [ 'completed', 'sub_active' ], true )
			&& in_array( $order->get_status(), [ 'completed', 'sub_active' ], true )
		) {
			foreach ( $order->get_items() as $order_item ) {
				$product_type = $order_item->get_product_type();
				if ( $order_item->get_type() === 'booking' && $product_type !== null ) {
					( new \Voxel\Events\Bookings\Booking_Confirmed_Event( $product_type ) )->dispatch( $order_item->get_id() );
				}
			}
		}
	}
}
