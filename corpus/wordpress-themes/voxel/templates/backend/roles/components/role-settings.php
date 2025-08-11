<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="x-row h-center">
	<div class="x-col-6 ts-content-head">
		<h1>General</h1>
		<p>General configuration for this role</p>
	</div>
</div>
<div class="x-row h-center">
	<div class="x-col-6">
		<ul class="inner-tabs">
			<li :class="{'current-item': subtab === 'general'}">
				<a href="#" @click.prevent="setTab('general', 'general')">General</a>
			</li>
			<li :class="{'current-item': subtab === 'role-switch'}">
				<a href="#" @click.prevent="setTab('general', 'role-switch')">Role switch</a>
			</li>
		</ul>
	</div>
</div>
<template v-if="subtab === 'general'">
	<div class="x-row h-center">
		<div class="x-col-6">
			<div class="ts-group">
				<div class="ts-group-head">
					<h3>General</h3>
				</div>
				<div class="x-row">
					<?php \Voxel\Form_Models\Text_Model::render( [
						'v-model' => 'config.settings.label',
						'label' => 'Label',
						'classes' => 'x-col-12',
					] ) ?>

					<?php \Voxel\Form_Models\Key_Model::render( [
						'v-model' => 'config.settings.key',
						'label' => 'Key',
						'editable' => false,
						'classes' => 'x-col-12',
					] ) ?>
				</div>
			</div>
		</div>
	</div>
</template>
<template v-else-if="subtab === 'role-switch'">
	<div class="x-row h-center">
		<div class="x-col-6">
			<div class="ts-group">
				<div class="ts-group-head">
					<h3>Role switch settings</h3>
				</div>
				<div class="x-row">
					<?php \Voxel\Form_Models\Switcher_Model::render( [
						'v-model' => 'config.settings.role_switch.enabled',
						'label' => 'Allow registered users to switch to this role',
						'classes' => 'x-col-12',
					] ) ?>

					<template v-if="config.settings.role_switch.enabled">
						<?php \Voxel\Form_Models\Switcher_Model::render( [
							'v-model' => 'config.settings.role_switch.show_plans_on_switch',
							'label' => 'Show plans during switch process',
							'classes' => 'x-col-12',
						] ) ?>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>
