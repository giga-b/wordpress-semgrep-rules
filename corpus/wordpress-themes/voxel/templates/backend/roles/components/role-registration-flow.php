<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="x-row h-center">
	<div class="x-col-6 ts-content-head">
		<h1>Registration</h1>
		<p>Configure registration for this role</p>
	</div>
</div>
<div class="x-row h-center">
	<div class="x-col-6">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Registration settings</h3>
			</div>
			<div class="x-row">
				<?php \Voxel\Form_Models\Switcher_Model::render( [
					'v-model' => 'config.registration.enabled',
					'label' => 'Enable user registration for this role',
					'classes' => 'x-col-12',
				] ) ?>

				<template v-if="config.registration.enabled">
					<?php \Voxel\Form_Models\Switcher_Model::render( [
						'v-model' => 'config.registration.allow_social_login',
						'label' => 'Enable social login for this role',
						'description' => 'Allows visitors to register for this role through social login',
						'classes' => 'x-col-12',
					] ) ?>

					<?php \Voxel\Form_Models\Select_Model::render( [
						'v-model' => 'config.registration.after_registration',
						'label' => 'After registration is complete',
						'classes' => 'x-col-12',
						'choices' => [
							'welcome_step' => 'Show welcome screen',
							'redirect_back' => 'Redirect back where the user left off',
							'custom_redirect' => 'Custom redirect',
						],
					] ) ?>

					<div v-if="config.registration.after_registration === 'custom_redirect'" class="ts-form-group x-col-12">
						<label>Custom redirect URL</label>
						<dtag-input
							v-model="config.registration.custom_redirect"
							:tag-groups="{site: { label: 'Site', type: 'site' }, user: { label: 'User', type: 'user' }}"
						></dtag-input>
					</div>
				</template>
			</div>
		</div>
	</div>
</div>
