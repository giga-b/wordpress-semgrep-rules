<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Form_Models\Select_Model::render( [
			'v-model' => 'config.stripe_subscriptions.billing_address_collection',
			'label' => 'Billing address collection',
			'classes' => 'x-col-12',
			'choices' => [
				'auto' => 'Automatic: Collect billing address when necessary',
				'required' => 'Required: Always collect billing address',
			],
		] ) ?>

		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe_subscriptions.tax_id_collection.enabled',
			'label' => 'Collect Tax ID in <a href="https://stripe.com/docs/tax/checkout/tax-ids#supported-types" target="_blank">supported countries</a>',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe_subscriptions.phone_number_collection.enabled',
			'label' => 'Phone number collection',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe_subscriptions.promotion_codes.enabled',
			'label' => sprintf( 'Allow <a href="%s" target="_blank">promotion codes</a> in checkout', esc_url( \Voxel\Stripe::dashboard_url( '/coupons' ) ) ),
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>Customer actions</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe_subscriptions.customer_actions.cancel_renewal.enabled',
			'label' => 'Cancel subscription at the end of current period',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe_subscriptions.customer_actions.cancel_subscription.enabled',
			'label' => 'Cancel subscription immediately',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
