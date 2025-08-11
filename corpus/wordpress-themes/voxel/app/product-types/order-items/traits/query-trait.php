<?php

namespace Voxel\Product_Types\Order_Items\Traits;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Query_Trait {

	protected static $instances = [];

	public static function get( $id ) {
		if ( is_array( $id ) ) {
			$data = $id;
			$id = absint( $data['id'] );
			if ( ! array_key_exists( $id, static::$instances ) ) {
				$data['details'] = (array) json_decode( (string) $data['details'], true );
				$product_mode = $data['details']['type'] ?? null;

				if ( $product_mode === 'regular' ) {
					static::$instances[ $id ] = new \Voxel\Product_Types\Order_Items\Order_Item_Regular( $data );
				} elseif ( $product_mode === 'variable' ) {
					static::$instances[ $id ] = new \Voxel\Product_Types\Order_Items\Order_Item_Variable( $data );
				} elseif ( $product_mode === 'booking' ) {
					static::$instances[ $id ] = new \Voxel\Product_Types\Order_Items\Order_Item_Booking( $data );
				} else {
					return null;
				}
			}
		} elseif ( is_numeric( $id ) ) {
			$id = absint( $id );
			if ( ! array_key_exists( $id, static::$instances ) ) {
				$results = static::query( [
					'id' => $id,
					'limit' => 1,
				] );

				static::$instances[ $id ] = isset( $results[0] ) ? $results[0] : null;
			}
		} else {
			return null;
		}

		return static::$instances[ $id ];
	}

	public static function force_get( $id ) {
		unset( static::$instances[ $id ] );
		return static::get( $id );
	}

	public static function query( array $args ) {
		global $wpdb;
		$args = array_merge( [
			'id' => null,
			'order_id' => null,
			'offset' => null,
			'limit' => 10,
		], $args );

		$where_clauses = [];
		$orderby_clauses = [];

		if ( ! is_null( $args['id'] ) ) {
			if ( is_numeric( $args['id'] ) ) {
				$where_clauses[] = sprintf( 'items.id = %d', absint( $args['id'] ) );
			} elseif ( is_array( $args['id'] ) ) {
				$where_clauses[] = sprintf( 'items.id IN (%s)', join( ',', array_map( 'absint', $args['id'] ) ) );
			}
		}

		if ( ! is_null( $args['order_id'] ) ) {
			if ( is_numeric( $args['order_id'] ) ) {
				$where_clauses[] = sprintf( 'items.order_id = %d', absint( $args['order_id'] ) );
			} elseif ( is_array( $args['order_id'] ) ) {
				$where_clauses[] = sprintf( 'items.order_id IN (%s)', join( ',', array_map( 'absint', $args['order_id'] ) ) );
			}
		}


		// generate sql string
		$wheres = '';
		if ( ! empty( $where_clauses ) ) {
			$wheres = sprintf( 'WHERE %s', join( ' AND ', $where_clauses ) );
		}

		$limit = '';
		if ( ! is_null( $args['limit'] ) ) {
			$limit = sprintf( 'LIMIT %d', absint( $args['limit'] ) );
		}

		$offset = '';
		if ( ! is_null( $args['offset'] ) ) {
			$offset = sprintf( 'OFFSET %d', absint( $args['offset'] ) );
		}

		$orderbys = '';
		if ( ! empty( $orderby_clauses ) ) {
			$orderbys = sprintf( 'ORDER BY %s', join( ", ", $orderby_clauses ) );
		}

		$sql = $wpdb->remove_placeholder_escape( "
			SELECT items.* FROM {$wpdb->prefix}vx_order_items AS items
			{$wheres} {$orderbys}
			ORDER BY items.id DESC
			{$limit} {$offset}
		" );

		if ( ! empty( $args['__dump_sql'] ) ) {
			dump_sql( $sql );
		}

		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_filter( array_map( function( $item_data ) {
			return static::get( $item_data );
		}, $results ) );
	}
}
