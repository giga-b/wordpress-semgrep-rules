<?php
/**
 * Admin general settings.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
}

wp_enqueue_script('vue');
wp_enqueue_script('sortable');
wp_enqueue_script('vue-draggable');
wp_enqueue_script('vx:general-settings.js');

require_once locate_template( 'templates/backend/product-types/partials/rate-list-component.php' );

?>
<div class="wrap">
	<div id="vx-general-settings" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
		<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="state.submit_config = JSON.stringify( config )">
			<div class="sticky-top">
				<div class="vx-head x-container">
					<h2 v-if="tab === 'membership'">Membership</h2>
					<h2 v-else-if="tab === 'recaptcha'">Recaptcha</h2>
					<h2 v-else-if="tab === 'auth' && subtab === 'google'">Login with Google</h2>
					<div class="vxh-actions">
						<input type="hidden" name="config" :value="state.submit_config">
						<input type="hidden" name="action" value="voxel_save_membership_settings">
						<?php wp_nonce_field( 'voxel_save_membership_settings' ) ?>
						<button type="submit" class="ts-button btn-shadow ts-save-settings">
							<i class="las la-save icon-sm"></i>
							Save changes
						</button>
					</div>
				</div>
			</div>
			<div class="ts-spacer"></div>
			<div class="x-container">
				<div class="x-row">
					<div class="x-col-3">
						<ul class="inner-tabs vertical-tabs">
							<li :class="{'current-item': tab === 'membership'}">
								<a href="#" @click.prevent="setTab('membership')">Membership</a>
							</li>
							<li :class="{'current-item': tab === 'recaptcha'}">
								<a href="#" @click.prevent="setTab('recaptcha')">Recaptcha</a>
							</li>
							<li :class="{'current-item': tab === 'auth' && subtab === 'google'}">
								<a href="#" @click.prevent="setTab('auth', 'google')">Login with Google</a>
							</li>
						</ul>
					</div>

					<div v-if="tab === 'membership'" class="x-col-9">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Registration</h3>
							</div>

							<div class="x-row">
								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.membership.require_verification',
									'label' => 'Require email verification',
									'classes' => 'x-col-12',
								] ) ?>
							</div>

							<div class="x-row">
								<?php \Voxel\Form_Models\Select_Model::render( [
									'v-model' => 'config.membership.username_behavior',
									'label' => 'Username field',
									'classes' => 'x-col-12',
									'choices' => [
										'display_as_field' => 'Show: Display username as a field in the registration form',
										'generate_from_email' => 'Hide: Generate username automatically from the user email',
									],
								] ) ?>
							</div>
						</div>
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Checkout</h3>
							</div>

							<div class="x-row">
								<?php \Voxel\Form_Models\Select_Model::render( [
									'v-model' => 'config.membership.checkout.billing_address_collection',
									'label' => 'Billing address collection',
									'classes' => 'x-col-12',
									'choices' => [
										'auto' => 'Automatic: Collect billing address when necessary',
										'required' => 'Required: Always collect billing address',
									],
								] ) ?>

								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.membership.checkout.tax.tax_id_collection',
									'label' => 'Collect Tax ID in <a href="https://stripe.com/docs/tax/checkout/tax-ids#supported-types" target="_blank">supported countries</a>',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.membership.checkout.phone_number_collection.enabled',
									'label' => 'Phone number collection',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.membership.checkout.promotion_codes.enabled',
									'label' => sprintf( 'Allow <a href="%s" target="_blank">promotion codes</a> in checkout', esc_url( \Voxel\Stripe::dashboard_url( '/coupons' ) ) ),
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.membership.trial.enabled',
									'label' => 'Enable free trial',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Number_Model::render( [
									'v-if' => 'config.membership.trial.enabled',
									'v-model' => 'config.membership.trial.period_days',
									'label' => 'Trial period days',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Select_Model::render( [
									'v-model' => 'config.membership.update.proration_behavior',
									'label' => 'Proration behavior when switching between subscription plans',
									'classes' => 'x-col-12',
									'choices' => [
										'create_prorations' => 'Create prorations',
										'always_invoice' => 'Create prorations and invoice immediately',
										'none' => 'Disable prorations',
									],
								] ) ?>

								<?php \Voxel\Form_Models\Select_Model::render( [
									'v-model' => 'config.membership.cancel.behavior',
									'label' => 'When a cancel request is submitted, cancel the subscription:',
									'classes' => 'x-col-12',
									'choices' => [
										'at_period_end' => 'At the end of current billing period',
										'immediately' => 'Immediately',
									],
								] ) ?>
							</div>
						</div>
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Tax collection</h3>
							</div>

							<div class="x-row">
								<?php \Voxel\Form_Models\Select_Model::render( [
									'v-model' => 'config.membership.checkout.tax.mode',
									'label' => 'Tax collection mode',
									'classes' => 'x-col-12',
									'choices' => [
										'auto' => 'Automatic (Stripe Tax)',
										'manual' => 'Manual',
										'none' => 'None',
									],
								] ) ?>

								<template v-if="config.membership.checkout.tax.mode === 'manual'">
									<div class="ts-form-group x-col-12 basic-ul">
										<li>
											<a href="<?= esc_url( \Voxel\Stripe::dashboard_url( '/tax-rates' ) ) ?>" target="_blank" class="ts-button ts-outline">
												<i class="las la-external-link-alt icon-sm"></i>
												Setup tax rates
											</a>
										</li>
									</div>
									<div class="ts-form-group x-col-12">
										<h4>Live mode</h4>
										<rate-list
											v-model="config.membership.checkout.tax.manual.tax_rates"
											mode="live"
											source="backend.list_tax_rates"
										></rate-list>
									</div>

									<div class="ts-form-group x-col-12">
										<h4>Test mode</h4>
										<rate-list
											v-model="config.membership.checkout.tax.manual.test_tax_rates"
											mode="test"
											source="backend.list_tax_rates"
										></rate-list>
									</div>
								</template>
							</div>
						</div>
					</div>
					<div v-else-if="tab === 'recaptcha'" class="x-col-9">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Configuration</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.recaptcha.enabled',
									'label' => 'Enable reCAPTCHA',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Text_Model::render( [
									'v-model' => 'config.recaptcha.key',
									'label' => 'Site key',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Password_Model::render( [
									'v-model' => 'config.recaptcha.secret',
									'label' => 'Secret key',
									'classes' => 'x-col-12',
									'autocomplete' => 'new-password',
								] ) ?>

								<div class="ts-form-group x-col-12">
									<p>Configure Google reCAPTCHA in the <a href="https://www.google.com/recaptcha/admin" target="_blank">v3 Admin Console</a></p>
								</div>
							</div>
						</div>
					</div>
					<div v-else-if="tab === 'auth' && subtab === 'google'" class="x-col-9">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Login with Google</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.auth.google.enabled',
									'label' => 'Enable Login with Google',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Text_Model::render( [
									'v-model' => 'config.auth.google.client_id',
									'label' => 'Client ID',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Form_Models\Password_Model::render( [
									'v-model' => 'config.auth.google.client_secret',
									'label' => 'Client secret',
									'classes' => 'x-col-12',
									'autocomplete' => 'new-password',
								] ) ?>

								<div class="ts-form-group x-col-12">
									<details>
										<summary>Setup guide</summary>
										<p><b>How to get Google Client ID and Client Secret</b></p>
										<ol>
											<li>Go to the <a href="https://console.developers.google.com/apis" target="_blank">Google Developers Console</a></li>
											<li>Click <b>Select a project ➝ New Project</b></li>
										</ol>
										<p><b>Configure OAuth consent & register your app</b></p>
										<ol>
											<li>In the Google Cloud console, go to <b>Menu ➝ APIs & Services ➝ OAuth consent screen</b></li>
											<li>Select <b>External</b> user type for your app, then click <b>Create</b></li>
											<li>Complete the app registration form, then click <b>Save and Continue</b></li>
											<li>Review your app registration summary. To make changes, click <b>Edit</b>. If the app registration looks OK, click <b>Back to Dashboard.</b></li>
										</ol>
										<p><b>Credentials</b></p>
										<ol>
											<li>In the Google Cloud console, go to <b>APIs & Services ➝ Credentials</b></li>
											<li>Press <b>Create credentials ➝ OAuth Client ID</b></li>
											<li>Select <b>Web application</b> type</li>
											<li>Under <b>Authorized Javascript origins</b> enter your site URL: <pre class="ts-snippet"><?= home_url('/') ?></pre></li>
											<li>Under <b>Authorized redirect URIs</b> enter: <pre class="ts-snippet"><?= home_url('/?vx=1&action=auth.google.login ') ?></pre></li>
											<li>Copy the generated <b>Client ID</b> and <b>Client Secret</b> and paste them in the section above</li>
										</ol>
									</details>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
