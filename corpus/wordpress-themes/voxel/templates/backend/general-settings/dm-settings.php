<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Message length</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Number_Model::render( [
			'v-model' => 'config.messages.maxlength',
			'label' => 'Maximum message length (in characters)',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Uploads</h3>
	</div>

	<div class="x-row">
		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.messages.files.enabled',
			'label' => 'Enable file uploads',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="config.messages.files.enabled">
			<?php \Voxel\Form_Models\Number_Model::render( [
				'v-model' => 'config.messages.files.max_size',
				'label' => 'Max file size (kB)',
				'classes' => 'x-col-6',

			] ) ?>

			<?php \Voxel\Form_Models\Number_Model::render( [
				'v-model' => 'config.messages.files.max_count',
				'label' => 'Max file count',
				'classes' => 'x-col-6',

			] ) ?>

			<?php \Voxel\Form_Models\Checkboxes_Model::render( [
				'v-model' => 'config.messages.files.allowed_file_types',
				'label' => 'Allowed file types',
				'classes' => 'x-col-12',
				'choices' => array_combine( get_allowed_mime_types(), get_allowed_mime_types() ),
			] ) ?>
		</template>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Real time</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.messages.enable_real_time',
			'label' => 'Update chats in real-time',
			'classes' => 'x-col-12',
		] ) ?>

		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.messages.enable_seen',
			'label' => 'Show "Seen" badge',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>Storage</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Form_Models\Number_Model::render( [
			'v-model' => 'config.messages.persist_days',
			'label' => 'Delete messages older than (days)',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
