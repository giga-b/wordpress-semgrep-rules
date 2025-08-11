<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$post = \Voxel\get_current_post();
if ( ! $post || $post->is_verified() ) {
	return;
}

$field = $post->get_field('voxel:claim');
if ( ! ( $field && $field->get_type() === 'product' ) ) {
	return;
}

try {
	$field->check_product_form_validity();
	$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( [
		'product' => [
			'post_id' => $post->get_id(),
			'field_key' => $field->get_key(),
		],
	] );

	$checkout_link = get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/');
	$checkout_link = add_query_arg( 'checkout_item', $cart_item->get_key(), $checkout_link );
} catch ( \Exception $e ) {
	return;
}

$alert_message = null;
$alert_actions = [];
if ( is_user_logged_in() ) {
	$user = \Voxel\current_user();
	if ( ! $user->can_create_post( $post->post_type->get_key() ) ) {
		$current_url = home_url( $GLOBALS['wp']->request );

		if ( $user->can_modify_limits_for_post_type( $post->post_type->get_key() ) ) {
			$alert = 'modify_or_upgrade';
			$alert_message = _x('Please upgrade plan to claim this listing', 'claim post', 'voxel');
			$alert_actions[] = [
				'label' => _x('Upgrade', 'claim post', 'voxel'),
				'link' => add_query_arg( 'redirect_to', $current_url, get_permalink( \Voxel\get( 'templates.current_plan' ) ) ),
			];

			$alert_actions[] = [
				'label' => _x('Modify', 'claim post', 'voxel'),
				'link' => add_query_arg( 'redirect_to', $current_url, get_permalink( \Voxel\get( 'templates.configure_plan' ) ) ),
			];
		} else {
			$alert_message = _x('Please upgrade plan to claim this listing', 'claim post', 'voxel');
			$alert_actions[] = [
				'label' => _x('Upgrade', 'claim post', 'voxel'),
				'link' => add_query_arg( 'redirect_to', $current_url, get_permalink( \Voxel\get( 'templates.current_plan' ) ) ),
			];
		}
	}
}

// dd($alert_message, $alert_actions);
?>
<?= $start_action ?>
<a
	href="<?= esc_url( $checkout_link ) ?>"
	rel="nofollow"
	class="ts-action-con"
	role="button"
	<?php if ( ! is_user_logged_in() ): ?>
		onclick="Voxel.requireAuth(event)"
	<?php elseif ( $alert_message !== null ): ?>
		data-alert-message="<?= esc_attr( $alert_message ) ?>"
		data-alert-actions="<?= esc_attr( wp_json_encode( $alert_actions ) ) ?>"
		onclick="event.preventDefault(); Voxel.alert( this.dataset.alertMessage, 'info', JSON.parse( this.dataset.alertActions ) )"
	<?php else: ?>
		data-cart-item="<?= esc_attr( wp_json_encode( [ $cart_item->get_key() => $cart_item->get_value() ] ) ) ?>"
		onclick="localStorage.setItem( 'voxel:direct_cart', this.dataset.cartItem )"
	<?php endif ?>
>
	<span class="ts-initial">
		<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
	</span>
</a>
<?= $end_action ?>
