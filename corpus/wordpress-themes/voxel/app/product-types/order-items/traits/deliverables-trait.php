<?php

namespace Voxel\Product_Types\Order_Items\Traits;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Deliverables_Trait {

	protected function _get_deliverables_for_order_page(): ?array {
		$order = $this->get_order();
		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			return null;
		}

		$field = $this->get_product_field();
		if ( ! $field ) {
			return null;
		}

		$product_type = $field->get_product_type();
		if ( ! $product_type ) {
			return null;
		}

		$details = [
			'automatic' => [],
			'manual' => [],
			'uploads' => [
				'enabled' => false,
			],
		];

		// automatic deliverables
		$deliverables = $field->get_product_field('deliverables');
		if ( $deliverables ) {
			$file_ids = explode( ',', (string) $field->get_value()['deliverables']['files'] ?? '' );
			foreach ( $file_ids as $id ) {
				if ( $url = wp_get_attachment_url( $id ) ) {
					$display_filename = get_post_meta( $id, '_display_filename', true );
					$details['automatic'][] = [
						'name' => ! empty( $display_filename ) ? $display_filename : wp_basename( get_attached_file( $id ) ),
						'url' => add_query_arg( [
							'action' => 'products.single_order.deliverables.download',
							'order_id' => $order->get_id(),
							'order_item_id' => $this->get_id(),
							'file_id' => $id,
							'delivery_method' => 'automatic',
						], home_url('/?vx=1') ),
					];
				}
			}
		}

		// manual deliverables
		if ( $product_type->config('modules.deliverables.enabled') && $product_type->config('modules.deliverables.delivery_methods.manual') ) {
			if ( \Voxel\current_user()->is_vendor_of( $order->get_id() ) ) {
				$details['uploads'] = [
					'enabled' => true,
					'allowed_file_types' => $product_type->config( 'modules.deliverables.uploads.allowed_file_types' ),
					'max_size' => $product_type->config( 'modules.deliverables.uploads.max_size' ),
					'max_count' => $product_type->config( 'modules.deliverables.uploads.max_count' ),
				];
			}

			$file_groups = (array) $this->get_details( 'deliverables.manual', [] );

			foreach ( $file_groups as $group_index => $group ) {
				$file_ids = explode( ',', (string) $group['ids'] ?? '' );
				$files = [];
				foreach ( $file_ids as $id ) {
					if ( $url = wp_get_attachment_url( $id ) ) {
						$display_filename = get_post_meta( $id, '_display_filename', true );
						$files[] = [
							'id' => $id,
							'name' => ! empty( $display_filename ) ? $display_filename : wp_basename( get_attached_file( $id ) ),
							'url' => add_query_arg( [
								'action' => 'products.single_order.deliverables.download',
								'order_id' => $order->get_id(),
								'order_item_id' => $this->get_id(),
								'group_index' => $group_index,
								'file_id' => $id,
								'delivery_method' => 'manual',
							], home_url('/?vx=1') ),
						];
					}
				}

				if ( ! empty( $files ) ) {
					$details['manual'][] = [
						'files' => $files,
					];
				}
			}
		}

		return $details;
	}

}
