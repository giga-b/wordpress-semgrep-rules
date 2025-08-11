<?php

namespace Voxel\Events\Claims;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Processed_Event extends \Voxel\Events\Base_Event {

	public $order, $order_item, $post, $customer;

	public function prepare( $order_item_id ) {
		$order_item = \Voxel\Product_Types\Order_Items\Order_Item::get( $order_item_id );
		if ( ! $order_item ) {
			throw new \Exception( 'Missing information.' );
		}

		$order = $order_item->get_order();
		$post = $order_item->get_post();
		if ( ! ( $order && $post ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$customer = $order->get_customer();
		if ( ! $customer ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->order_item = $order_item;
		$this->order = $order;
		$this->post = $post;
		$this->customer = $customer;
	}

	public function get_key(): string {
		return 'claims/claim:processed';
	}

	public function get_label(): string {
		return 'Claim processed';
	}

	public function get_category() {
		return 'claims';
	}

	public static function notifications(): array {
		return [
			'customer' => [
				'label' => 'Notify customer',
				'recipient' => function( $event ) {
					return $event->customer;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'Your claim request has been approved',
					'details' => function( $event ) {
						return [
							'order_item_id' => $event->order_item->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_item_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your claim request has been approved',
					'message' => <<<HTML
					Your claim request on <strong>@post(:title)</strong> has been approved and processed.<br>
					<a href="@order(:link)">Order details</a>
					<a href="@post(:url)">View post</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'Claim request by @customer(:display_name) has been approved',
					'details' => function( $event ) {
						return [
							'order_item_id' => $event->order_item->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_item_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => function( $event ) { return $event->customer->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Claim request by @customer(:display_name) has been approved',
					'message' => <<<HTML
					Claim request by <strong>@customer(:display_name)</strong> on <strong>@post(:title)</strong>
					has been approved and processed.<br>
					<a href="@order(:link)">Order details</a>
					<a href="@post(:url)">View post</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->customer = \Voxel\User::mock();
		$this->post = \Voxel\Post::mock();
		$this->order = \Voxel\Product_Types\Orders\Order::mock();
	}

	public function dynamic_tags(): array {
		return [
			'customer' => \Voxel\Dynamic_Data\Group::User( $this->customer ),
			'post' => \Voxel\Dynamic_Data\Group::Simple_Post( $this->post ),
			'order' => \Voxel\Dynamic_Data\Group::Order( $this->order ),
		];
	}
}
