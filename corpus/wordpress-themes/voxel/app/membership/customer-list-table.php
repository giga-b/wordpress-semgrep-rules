<?php

namespace Voxel\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Extends \WP_List_Table to display active customers.
 *
 * @link  https://github.com/Veraxus/wp-list-table-example
 * @since 1.0
 */
class Customer_List_Table extends \WP_List_Table {

	public function get_columns() {
		$columns = [
			// 'cb' => '<input type="checkbox">',
			'title' => _x( 'Username', 'members table', 'voxel-backend' ),
			'id' => _x( 'ID', 'members table', 'voxel-backend' ),
			'email' => _x( 'Email', 'members table', 'voxel-backend' ),
			'plan' => _x( 'Active plan', 'members table', 'voxel-backend' ),
			'amount' => _x( 'Amount', 'members table', 'voxel-backend' ),
			'status' => _x( 'Status', 'members table', 'voxel-backend' ),
			'created' => _x( 'Created', 'members table', 'voxel-backend' ),
			'details' => _x( '', 'members table', 'voxel-backend' ),
		];

		return $columns;
	}

	protected function get_sortable_columns() {
		$sortable_columns = [
			'id'    => [ 'id', 'desc' ],
			'title'    => [ 'username', 'asc' ],
			'email'   => [ 'email', 'asc' ],
			'plan' => [ 'plan', 'asc' ],
			'amount' => [ 'amount', 'desc' ],
			'status' => [ 'status', 'asc' ],
			'created' => [ 'created', 'desc' ],
		];

		return $sortable_columns;
	}

	protected function column_default( $item, $column_name ) {
		$user = \Voxel\User::get( $item['id'] );
		$membership = $user->get_membership();

		if ( $column_name === 'email' ) {
			return sprintf( '<a href="mailto:%s">%s</a>', esc_attr( $user->get_email() ), esc_html( $user->get_email() ) );
		} elseif ( $column_name === 'id' ) {
			return '<span>'.$user->get_id().'</span>';
		} elseif ( $column_name === 'plan' ) {
			return sprintf( '<strong><a href="%s">%s</a></strong>', $membership->plan->get_edit_link(), $membership->plan->get_label() );
		} elseif ( $column_name === 'amount' ) {
			if ( $membership->get_type() === 'subscription' ) {
				return sprintf(
					'<span class="price-amount">%s</span> %s',
					\Voxel\currency_format( $membership->get_amount(), $membership->get_currency() ),
					\Voxel\interval_format( $membership->get_interval(), $membership->get_interval_count() )
				);
			} elseif ( $membership->get_type() === 'payment' ) {
				if ( floatval( $membership->get_amount() ) === 0.0 ) {
					return 'Free';
				}

				return sprintf(
					'<span class="price-amount">%s</span> %s',
					\Voxel\currency_format( $membership->get_amount(), $membership->get_currency() ),
					_x( 'one time', 'members table', 'voxel-backend' )
				);
			}

			return '&mdash;';
		} elseif ( $column_name === 'status' ) {
			$status = $membership->get_status();
			if ( is_string( $status ) && ! empty( $status ) ) {
				$label = ucwords( str_replace( '_', ' ', $membership->get_status() ) );
				return sprintf( '<span class="%s">%s</span>', $membership->is_active() ? 'active' : '', $label );
			}

			return '&mdash;';
		} elseif ( $column_name === 'created' ) {
			if ( $timestamp = strtotime( $item['created'] ?? '' ) ) {
				return \Voxel\datetime_format( $timestamp );
			}

			return '&mdash;';
		} elseif ( $column_name === 'details' ) {
			return sprintf(
				'<a href="%s" class="button right">%s</a>',
				esc_url( admin_url( 'admin.php?page=voxel-customers&customer='.$user->get_id() ) ),
				_x( 'Details', 'members table', 'voxel-backend' )
			);
		}
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="customers[]" value="%d">', $item['id'] );
	}

	protected function column_title( $item ) {
		$user = \Voxel\User::get( $item['id'] );
		return sprintf( '%s<span class="item-title"><a href="%s">%s</a></span>',
			$user->get_avatar_markup(24),
			$user->get_edit_link(),
			$item['title']
		);
	}

	protected function get_views() {
		global $wpdb;
		$meta_key = \Voxel\Stripe::is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';

		// $total_counts = $wpdb->get_results( <<<SQL
		// 	SELECT COUNT(*) AS total, JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) AS plan
		// 	FROM {$wpdb->usermeta} as m
		// 	WHERE m.meta_key = '{$meta_key}'
		// 	GROUP BY plan
		// SQL );

		$join_blog = '';
		if ( get_current_blog_id() && is_multisite() ) {
			$blog_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
			$join_blog = "INNER JOIN {$wpdb->usermeta} AS b ON ( u.ID = b.user_id AND b.meta_key = '{$blog_prefix}capabilities' )";
		}

		$total_counts = $wpdb->get_results( <<<SQL
			SELECT COUNT(*) AS total, IF ( JSON_VALID( m.meta_value ), JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ), 'default' ) AS plan
			FROM {$wpdb->users} AS u
			{$join_blog}
			LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
			GROUP BY plan
		SQL );

		$counts = [];
		$total_count = 0;

		foreach ( $total_counts as $count ) {
			$counts[ $count->plan ] = absint( $count->total );
			$total_count += absint( $count->total );
		}

		$active = $_GET['plan'] ?? null;
		$plans = \Voxel\Plan::all();

		$views['all'] = sprintf(
			'<a href="%s" class="%s">%s%s</a>',
			admin_url('admin.php?page=voxel-customers'),
			empty( $active ) ? 'current' : '',
			_x( 'All', 'members table', 'voxel-backend' ),
			$total_count > 0 ? sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $total_count ) ) : '',
		);

		foreach ( $plans as $plan ) {
			$views[ $plan->get_key() ] = sprintf(
				'<a href="%s" class="%s">%s%s</a>',
				admin_url( 'admin.php?page=voxel-customers&plan='.$plan->get_key() ),
				$active === $plan->get_key() ? 'current' : '',
				$plan->get_label(),
				isset( $counts[ $plan->get_key() ] ) ? sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $counts[ $plan->get_key() ] ) ) : '',
			);
		}

		return $views;
	}

	public function prepare_items() {
		global $wpdb;

		$page = $this->get_pagenum();
		$limit = 25;
		$offset = $limit * ( $page - 1 );
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$orderby = 'm.user_id';
		$order = ( $_GET['order'] ?? null ) === 'asc' ? 'ASC' : 'DESC';

		$custom_order = $_GET['orderby'] ?? null;
		if ( $custom_order === 'username' ) {
			$orderby = 'title';
		} elseif ( $custom_order === 'email' ) {
			$orderby = 'email';
		} elseif ( $custom_order === 'plan' ) {
			$orderby = 'plan';
		} elseif ( $custom_order === 'amount' ) {
			$orderby = 'amount';
		} elseif ( $custom_order === 'status' ) {
			$orderby = 'status';
		} elseif ( $custom_order === 'created' ) {
			$orderby = 'created';
		} elseif ( $custom_order === 'id' ) {
			$orderby = 'm.user_id';
		}

		$search = '';
		if ( ! empty( $_GET['s'] ) ) {
			$search_string = esc_sql( $_GET['s'] );
			$search_like = '%'.$wpdb->esc_like( $search_string ).'%';
			$search = $wpdb->prepare(
				"AND ( u.user_login = %s OR u.user_email = %s OR u.ID = %s OR u.display_name LIKE %s )",
				$search_string, $search_string, $search_string, $search_like
			);
		}

		$search_plan = '';
		if ( ! empty( $_GET['plan'] ) ) {
			$plan = esc_sql( $_GET['plan'] );
			if ( $plan === 'default' ) {
				$search_plan = "AND ( ( JSON_VALID( m.meta_value ) AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) = '{$plan}' ) OR m.meta_key IS NULL )";
			} else {
				$search_plan = "AND ( JSON_VALID( m.meta_value ) AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) = '{$plan}' )";
			}
		}

		$join_blog = '';
		if ( get_current_blog_id() && is_multisite() ) {
			$blog_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
			$join_blog = "INNER JOIN {$wpdb->usermeta} AS b ON ( u.ID = b.user_id AND b.meta_key = '{$blog_prefix}capabilities' )";
		}

		$meta_key = \Voxel\Stripe::is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';

		$sql = <<<SQL
			SELECT
				u.ID AS id,
				u.user_login AS title,
				u.user_email AS email,
				m.meta_value AS details,
				IF ( JSON_VALID( m.meta_value ), JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ), NULL ) AS plan,
				IF ( JSON_VALID( m.meta_value ), CAST( JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.amount' ) ) AS SIGNED ), NULL ) AS amount,
				IF ( JSON_VALID( m.meta_value ), JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.status' ) ), NULL ) AS status,
				IF ( JSON_VALID( m.meta_value ), CAST( JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.created' ) ) AS DATETIME ), NULL ) AS created
			FROM {$wpdb->users} as u
			{$join_blog}
			LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
			WHERE 1=1 {$search} {$search_plan}
			ORDER BY {$orderby} {$order}
			LIMIT {$limit} OFFSET {$offset}
		SQL;

		// dd_sql($sql);

		$results = $wpdb->get_results( $sql, ARRAY_A );

		foreach ( $results as $i => $result ) {
			$details = (array) json_decode( $result['details'] ?? '', true );
			$results[ $i ]['details'] = json_last_error() === JSON_ERROR_NONE ? $details : [];
		}

		cache_users( array_column( $results, 'id' ) );

		$count = absint( $wpdb->get_var( <<<SQL
			SELECT COUNT(*)
			FROM {$wpdb->users} as u
			{$join_blog}
			LEFT JOIN {$wpdb->usermeta} AS m ON ( u.ID = m.user_id AND m.meta_key = '{$meta_key}' )
			WHERE 1=1 {$search} {$search_plan}
		SQL ) );

		$this->items = $results;
		$this->set_pagination_args( [
			'total_items' => $count,
			'per_page' => $limit,
			'total_pages' => ceil( $count / $limit ),
		] );
	}
}
