<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-edit-role" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="prepareSubmission">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2><?= $role->get_label() ?>
				</h2>
				<div class="">
					<input type="hidden" name="role_config" :value="submit_config">
					<input type="hidden" name="action" value="voxel_update_membership_role">
					<?php wp_nonce_field( 'voxel_manage_membership_roles' ) ?>
					<button v-if="config.settings.key !== 'subscriber'" type="button" name="remove_role" value="yes" class="ts-button ts-transparent"
						onclick="return confirm('Are you sure?') ? ( this.type = 'submit' ) && true : false">
						Stop managing with Voxel
					</button>
					&nbsp;&nbsp;
					<button type="submit" href="#" class="ts-button ts-save-settings btn-shadow">
						<i class="las la-save icon-sm"></i>
						Save changes
					</button>
				</div>
			</div>
		</div>


		<div class="x-container">
			<div class="ts-theme-options-nav">
				<div class="ts-nav">
					<div class="ts-nav-item" :class="{'current-item': tab === 'general'}">
						<a href="#" @click.prevent="setTab('general', 'general')">
							<span class="item-icon all-center">
								<i class="las la-home"></i>
							</span>
							<span class="item-name">
								General
							</span>
						</a>
					</div>
					<div class="ts-nav-item" :class="{'current-item': tab === 'registration_flow'}">
						<a href="#" @click.prevent="setTab('registration_flow', 'registration')">
							<span class="item-icon all-center">
								<i class="las la-grip-lines"></i>
							</span>
							<span class="item-name">
								Registration
							</span>
						</a>
					</div>
					<div class="ts-nav-item" :class="{'current-item': tab === 'registration_fields'}">
						<a href="#" @click.prevent="setTab('registration_fields')">
							<span class="item-icon all-center">
								<i class="las la-grip-lines"></i>
							</span>
							<span class="item-name">
								Fields
							</span>
						</a>
					</div>
					<div class="ts-nav-item" :class="{'current-item': tab === 'plans'}">
						<a href="#" @click.prevent="setTab('plans')">
							<span class="item-icon all-center">
								<i class="las la-grip-lines"></i>
							</span>
							<span class="item-name">
								Plans
							</span>
						</a>
					</div>
				</div>
			</div>
			<div class="ts-spacer"></div>
			<div v-if="tab === 'general'" class="ts-tab-content ts-container">
				<?php require_once locate_template( 'templates/backend/roles/components/role-settings.php' ) ?>
			</div>
			<div v-else-if="tab === 'registration_fields'" class="ts-tab-content ts-container">
				<?php require_once locate_template( 'templates/backend/roles/components/role-registration-fields.php' ) ?>
			</div>
			<div v-else-if="tab === 'registration_flow'" class="ts-tab-content ts-container">
				<?php require_once locate_template( 'templates/backend/roles/components/role-registration-flow.php' ) ?>
			</div>
			<div v-else-if="tab === 'plans'" class="ts-tab-content ts-container">
				<?php require_once locate_template( 'templates/backend/roles/components/role-plans.php' ) ?>
			</div>

		</div>

	</form>
</div>

<?php require_once locate_template( 'templates/backend/roles/components/role-field-modal.php' ) ?>
