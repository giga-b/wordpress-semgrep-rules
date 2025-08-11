<?php

namespace Voxel\Product_Types\Cart;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Direct_Cart extends Base_Cart {

	public function get_type(): string {
		return 'direct_cart';
	}

	public function update(): void {
		//
	}

	public function get_max_cart_quantity(): int {
		return 1;
	}

	public function supports_cart_item( \Voxel\Product_Types\Cart_Items\Cart_Item $item ): bool {
		return true;
	}

	public function get_payment_method(): ?string {
		foreach ( $this->items as $item ) {
			return $item->get_payment_method();
		}

		return null;
	}
}
