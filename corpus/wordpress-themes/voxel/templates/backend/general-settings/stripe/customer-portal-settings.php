<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>General</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe.portal.invoice_history',
			'label' => 'Show invoice history',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.stripe.portal.customer_update.enabled',
			'label' => 'Allow updating details',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Fields</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Checkboxes_Model::render( [
			'v-if' => 'config.stripe.portal.customer_update.enabled',
			'v-model' => 'config.stripe.portal.customer_update.allowed_updates',
			'label' => 'Allowed fields',
			'classes' => 'x-col-12',
			'choices' => [
				'email' => 'Email',
				'address' => 'Billing address',
				'shipping' => 'Shipping address',
				'phone' => 'Phone numbers',
				'tax_id' => 'Tax IDs',
			],
		] ) ?>
	</div>
</div>

<div class="x-col-12 h-center">
	<a href="#" @click.prevent="portal.editIds = !portal.editIds" class="ts-button ts-transparent full-width">
		<i class="las la-arrow-down icon-sm"></i> Advanced
	</a>
</div>

<div v-if="portal.editIds" class="ts-group">
	<div class="ts-group-head">
		<h3>Portal configuration IDs</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Key_Model::render( [
			'v-model' => 'config.stripe.portal.live_config_id',
			'label' => 'Live configuration ID',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Form_Models\Key_Model::render( [
			'v-model' => 'config.stripe.portal.test_config_id',
			'label' => 'Test configuration ID',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
