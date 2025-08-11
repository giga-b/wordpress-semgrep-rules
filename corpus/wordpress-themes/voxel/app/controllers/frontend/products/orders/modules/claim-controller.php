<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
	}

	protected function order_updated( $order ) {
		if ( in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			foreach ( $order->get_items() as $order_item ) {
				if ( ! ( $order_item->get_type() === 'regular' && $order_item->get_product_field_key() === 'voxel:claim' ) ) {
					continue;
				}

				if ( $order_item->get_details( 'claim.approved' ) ) {
					continue;
				}

				$post = $order_item->get_post();
				$vendor = $order->get_vendor();
				$customer = $order->get_customer();
				if ( ! ( $post && $vendor && $customer ) ) {
					continue;
				}

				if ( $post->get_author_id() === $vendor->get_id() || $vendor->has_cap('administrator') || $vendor->has_cap('editor') ) {
					wp_update_post( [
						'ID' => $post->get_id(),
						'post_author' => $customer->get_id(),
					] );

					$post->set_verified(true);

					delete_user_meta( $customer->get_id(), 'voxel:post_stats' );
					delete_user_meta( $vendor->get_id(), 'voxel:post_stats' );

					$order_item->set_details( 'claim.approved', true );
					$order_item->save();

					( new \Voxel\Events\Claims\Claim_Processed_Event )->dispatch( $order_item->get_id() );
				}
			}
		} else {
			foreach ( $order->get_items() as $order_item ) {
				if ( ! ( $order_item->get_type() === 'regular' && $order_item->get_product_field_key() === 'voxel:claim' ) ) {
					continue;
				}

				if ( ! $order_item->get_details( 'claim.approved' ) ) {
					continue;
				}

				$post = $order_item->get_post();
				$vendor = $order->get_vendor();
				$customer = $order->get_customer();
				if ( ! ( $post && $vendor && $customer ) ) {
					continue;
				}

				if ( $post->get_author_id() === $customer->get_id() ) {
					wp_update_post( [
						'ID' => $post->get_id(),
						'post_author' => $vendor->get_id(),
					] );

					$post->set_verified(false);

					delete_user_meta( $customer->get_id(), 'voxel:post_stats' );
					delete_user_meta( $vendor->get_id(), 'voxel:post_stats' );

					$order_item->set_details( 'claim.approved', false );
					$order_item->save();
				}
			}
		}
	}
}
