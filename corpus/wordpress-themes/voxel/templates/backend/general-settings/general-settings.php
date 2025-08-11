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
?>
<div class="wrap">
	<div id="vx-general-settings" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
		<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="state.submit_config = JSON.stringify( config )">
			<div class="sticky-top">
				<div class="vx-head x-container">
					<h2 v-if="tab === 'stripe' && subtab === 'general'">Stripe</h2>
					<h2 v-else-if="tab === 'stripe' && subtab === 'webhooks'">Webhooks</h2>
					<h2 v-else-if="tab === 'stripe' && subtab === 'portal'">Customer portal</h2>
					<h2 v-else-if="tab === 'addons'">Addons</h2>
					<h2 v-else-if="tab === 'maps'">Map providers</h2>
					<h2 v-else-if="tab === 'notifications'">Notifications</h2>
					<h2 v-else-if="tab === 'dms'">Direct messages</h2>
					<h2 v-else-if="tab === 'stats'">Statistics</h2>
					<h2 v-else-if="tab === 'emails'">Emails</h2>
					<h2 v-else-if="tab === 'nav_menus'">Nav menus</h2>
					<h2 v-else-if="tab === 'share_menu'">Share menu</h2>
					<h2 v-else-if="tab === 'db'">Database</h2>
					<h2 v-else-if="tab === 'other'">Other</h2>
					<h2 v-else></h2>

					<div class="vxh-actions">
						<input type="hidden" name="config" :value="state.submit_config">
						<input type="hidden" name="action" value="voxel_save_general_settings">
						<?php wp_nonce_field( 'voxel_save_general_settings' ) ?>
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
							<li :class="{'current-item': tab === 'addons'}">
								<a href="#" @click.prevent="setTab('addons')">Addons</a>
							</li>
							<li :class="{'current-item': tab === 'stripe'}">
								<a href="#" @click.prevent="setTab('stripe', 'general')">Stripe</a>
							</li>
							<li :class="{'current-item': tab === 'maps'}">
								<a href="#" @click.prevent="setTab('maps')">Map providers</a>
							</li>
							<li :class="{'current-item': tab === 'notifications'}">
								<a href="#" @click.prevent="setTab('notifications')">Notifications</a>
							</li>
							<li :class="{'current-item': tab === 'dms'}">
								<a href="#" @click.prevent="setTab('dms')">Direct Messages</a>
							</li>
							<li :class="{'current-item': tab === 'stats'}">
								<a href="#" @click.prevent="setTab('stats')">Statistics</a>
							</li>
							<li :class="{'current-item': tab === 'emails'}">
								<a href="#" @click.prevent="setTab('emails')">Emails</a>
							</li>
							<li :class="{'current-item': tab === 'nav_menus'}">
								<a href="#" @click.prevent="setTab('nav_menus')">Nav menus</a>
							</li>
							<li :class="{'current-item': tab === 'share_menu'}">
								<a href="#" @click.prevent="setTab('share_menu')">Share menu</a>
							</li>
							<li :class="{'current-item': tab === 'db'}">
								<a href="#" @click.prevent="setTab('db')">Database</a>
							</li>
							<li :class="{'current-item': tab === 'other'}">
								<a href="#" @click.prevent="setTab('other')">Other</a>
							</li>
						</ul>
					</div>

					<div v-if="tab === 'stripe'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/stripe/stripe-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'maps'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/map-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'notifications'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/notification-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'dms'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/dm-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'stats'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/tracking-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'emails'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/email-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'nav_menus'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/nav-menu-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'share_menu'" class="x-col-9">
						<share-menu></share-menu>
					</div>
					<div v-else-if="tab === 'db'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/general-settings/db-settings.php' ) ?>
					</div>
					<div v-else-if="tab === 'other'" class="x-col-9">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Viewport</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Form_Models\Select_Model::render( [
									'v-model' => 'config.perf.user_scalable',
									'label' => 'User scalable',
									'description' => 'Set whether whether zoom in and zoom out actions are allowed on the page',
									'classes' => 'x-col-12',
									'footnote' => 'Note: When enabled, font size in mobile should be set to at least 16px to prevent input zoom on focus',
									'choices' => [
										'yes' => 'Yes',
										'no' => 'No',
										'auto' => 'Auto',
									],
								] ) ?>
							</div>
						</div>

						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Icon packs</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Form_Models\Switcher_Model::render( [
									'v-model' => 'config.icons.line_awesome.enabled',
									'label' => 'Enable "Line Awesome" icon pack',
									'classes' => 'x-col-12',
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="tab === 'addons'" class="x-col-9">
						<div class="vx-panels">
							<div class="vx-panel">
								<div class="panel-image stripekit-membership">
									<img src="<?= \Voxel\get_image('svgs/stripekit.svg') ?>">
								</div>
								<div class="panel-info">
									<h3>Paid Membership</h3>
									<ul>
										<li>Sell one time or subscription based membership plans powered by StripeAPI</li>
									</ul>
								</div>
								<div class="panel-switcher" style="cursor: not-allowed;" title="This addon is required">
									<span style="opacity: .5;">Required</span>
								</div>
							</div>
							<div class="vx-panel">
								<div class="panel-image stripekit-product-types">
									<img src="<?= \Voxel\get_image('svgs/stripekit.svg') ?>">
								</div>
								<div class="panel-info">
									<h3>Product Types</h3>
									<ul>
										<li>Create, customize and sell products natively with Voxel, powered by StripeAPI.</li>
									</ul>
								</div>
								<div class="panel-switcher">
								   <?php \Voxel\Form_Models\Switcher_Model::render( [
										'v-model' => 'config.product_types.enabled',
										'classes' => 'x-col-12',
									] ) ?>
								</div>
							</div>
							<div class="vx-panel">
								<div class="panel-image timeline-addon">
									<img src="<?= \Voxel\get_image('svgs/comments-alt-1.svg') ?>">
								</div>
								<div class="panel-info">
									<h3>Timeline</h3>
									<ul>
										<li>The Timeline powers the social aspect of your Voxel site</li>
									</ul>
								</div>
								<div class="panel-switcher">
								   <?php \Voxel\Form_Models\Switcher_Model::render( [
										'v-model' => 'config.timeline.enabled',
										'classes' => 'x-col-12',
									] ) ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php require_once locate_template( 'templates/backend/product-types/partials/rate-list-component.php' ) ?>
<?php require_once locate_template( 'templates/backend/general-settings/share-menu-settings.php' ) ?>
