<div class="ts-panel active-plan plan-panel">
	<div class="ac-head">
		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plan_ico') ) ?: \Voxel\svg( 'badge.svg' ) ?>
		<?php if ( $membership->plan->get_key() === 'default' ): ?>
			<b><?= $membership->plan->get_label() ?></b>
		<?php else: ?>
			<b>
				<?= \Voxel\replace_vars( _x( 'Your current plan is @plan_label', 'current plan', 'voxel' ), [
					'@plan_label' => $membership->plan->get_label(),
				] ) ?>
			</b>
		<?php endif ?>
	</div>

	<?php if ( $membership->get_type() === 'subscription' ): ?>
		<div class="ac-body">
			<div class="ac-plan-pricing">
				<span class="ac-plan-price">
					<?= \Voxel\currency_format( $membership->get_amount(), $membership->get_currency() ) ?>
				</span>
				<div class="ac-price-period">
					/ <?= \Voxel\interval_format( $membership->get_interval(), $membership->get_interval_count() ) ?>
				</div>
			</div>
			<?php if ( $membership->will_cancel_at_period_end() ): ?>
				<p>
					<?= \Voxel\replace_vars(
						_x( 'Your subscription will be cancelled on @period_end. Click <a:reactivate>here</a> to reactivate.', 'current plan', 'voxel' ),
						[
							'@period_end' => \Voxel\date_format( $membership->get_current_period_end() ),
							'<a:reactivate>' => '<a href="'.esc_url( $reactivate_url ).'" vx-action>',
						]
					) ?>
				</p>
			<?php elseif ( $membership->get_status() === 'trialing' ): ?>
				<p>
					<?= \Voxel\replace_vars( _x( 'Your trial ends on @trial_end', 'current plan', 'voxel' ), [
						'@trial_end' => \Voxel\date_format( $membership->get_trial_end() ),
					] ) ?>
				</p>
			<?php elseif ( $membership->get_status() === 'active' ): ?>
				<p>
					<?= \Voxel\replace_vars( _x( 'Your subscription renews on @period_end', 'current plan', 'voxel' ), [
						'@period_end' => \Voxel\date_format( $membership->get_current_period_end() ),
					] ) ?>
				</p>
			<?php elseif ( $membership->get_status() === 'incomplete' ): ?>
				<p>
					<?= \Voxel\replace_vars(
						_x( '<a:update>Update your payment method</a>, then <a:finalize>finalize payment</a> to activate your subscription.', 'current plan', 'voxel' ),
						[
							'<a:update>' => '<a href="'.esc_url( $portal_url ).'" target="_blank">',
							'<a:finalize>' => '<a href="'.esc_url( $retry_payment_url ).'" vx-action>',
						]
					) ?>
				</p>
			<?php elseif ( $membership->get_status() === 'incomplete_expired' ): ?>
				<p>
					<?= \Voxel\replace_vars(
						_x( 'Subscription payment failed. Click <a:choose_plan>here</a> to pick a new plan.', 'current plan', 'voxel' ),
						[
							'<a:choose_plan>' => '<a href="'.esc_url( $switch_url ).'">',
						]
					) ?>
				</p>
			<?php elseif ( $membership->get_status() === 'past_due' ): ?>
				<p>
					<?= \Voxel\replace_vars(
						_x( 'Subscription renewal failed. <a:update>Update payment method</a>, then <a:finalize>finalize payment</a> to reactivate your subscription.', 'current plan', 'voxel' ),
						[
							'<a:update>' => '<a href="'.esc_url( $portal_url ).'" target="_blank">',
							'<a:finalize>' => '<a href="'.esc_url( $retry_payment_url ).'" vx-action>',
						]
					) ?>
				</p>
			<?php elseif ( $membership->get_status() === 'canceled' ): ?>
				<p><?= \Voxel\replace_vars(
					_x( 'Subscription has been canceled. Click <a:choose_plan>here</a> to pick a new plan.', 'current plan', 'voxel' ),
					[
						'<a:choose_plan>' => '<a href="'.esc_url( $switch_url ).'">',
					]
				) ?></p>
			<?php elseif ( $membership->get_status() === 'unpaid' ): ?>
				<p>
					<?= \Voxel\replace_vars(
						_x( 'Subscription has been deactivated due to failed renewal payments. <a:update>Update payment method</a>,
							then <a:finalize>finalize payment</a> to reactivate your subscription.', 'current plan', 'voxel' ),
						[
							'<a:update>' => '<a href="'.esc_url( $portal_url ).'" target="_blank">',
							'<a:finalize>' => '<a href="'.esc_url( $retry_payment_url ).'" vx-action>',
						]
					) ?>
				</p>
			<?php endif ?>
			<div class="ac-bottom">
				<ul class="simplify-ul current-plan-btn">
					<li>
						<a href="<?= esc_url( $switch_url ) ?>" class="ts-btn ts-btn-1">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_switch_ico') ) ?: \Voxel\svg( 'switch.svg' ) ?>
							<?= _x( 'Switch plan', 'current plan', 'voxel' ) ?>
						</a>
					</li>
					<?php if ( ! in_array( $membership->get_status(), [ 'canceled', 'incomplete_expired' ], true ) && ! $membership->will_cancel_at_period_end() ): ?>
						<li>
							<a href="<?= esc_url( $cancel_url ) ?>" vx-action class="ts-btn ts-btn-1" data-confirm="<?= esc_attr( _x( 'Are you sure you want to cancel your plan?', 'current plan', 'voxel' ) ) ?>">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_cancel_ico') ) ?: \Voxel\svg( 'cross-circle.svg' ) ?>
								<?= _x( 'Cancel', 'current plan', 'voxel' ) ?>
							</a>
						</li>
					<?php endif ?>
					<li>
						<a href="<?= esc_url( $portal_url ) ?>" target="_blank" class="ts-btn ts-btn-1">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_stripe_ico') ) ?: \Voxel\svg( 'link-alt.svg' ) ?>
							<?= _x( 'Stripe portal', 'current plan', 'voxel' ) ?>
						</a>
					</li>
					<?php if ( $membership->is_active() ): ?>
						<li>
							<a href="<?= esc_url( $modify_url ) ?>" class="ts-btn ts-btn-1">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_configure_ico') ) ?: \Voxel\svg( 'cog.svg' ) ?>
								<?= _x( 'Plan limits', 'current plan', 'voxel' ) ?>
							</a>
						</li>
					<?php endif ?>
				</ul>
			</div>
		</div>
	<?php elseif ( $membership->get_type() === 'payment' ): ?>
		<div class="ac-body">
			<div class="ac-plan-pricing">
				<?php if ( floatval( $membership->get_amount() ) === 0.0 ): ?>
					<span class="ac-plan-price"><?= _x( 'Free', 'current plan', 'voxel' ) ?></span>
				<?php else: ?>
					<span class="ac-plan-price">
						<?= \Voxel\currency_format( $membership->get_amount(), $membership->get_currency() ) ?>
					</span>
					<div class="ac-price-period">
						<?= _x( 'one time payment', 'current plan', 'voxel' ) ?>
					</div>
				<?php endif ?>
			</div>
			<div class="ac-bottom">
				<ul class="simplify-ul current-plan-btn">
					<li>
						<a href="<?= esc_url( $switch_url ) ?>" class="ts-btn ts-btn-1">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_switch_ico') ) ?: \Voxel\svg( 'switch.svg' ) ?>
							<?= _x( 'Switch', 'current plan', 'voxel' ) ?>
						</a>
					</li>
					<li>
						<a href="<?= esc_url( $portal_url ) ?>" target="_blank" class="ts-btn ts-btn-1">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_stripe_ico') ) ?: \Voxel\svg( 'link-alt.svg' ) ?>
							<?= _x( 'Stripe portal', 'current plan', 'voxel' ) ?>
						</a>
					</li>
					<?php if ( $membership->is_active() ): ?>
						<li>
							<a href="<?= esc_url( $modify_url ) ?>" class="ts-btn ts-btn-1">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_configure_ico') ) ?: \Voxel\svg( 'cog.svg' ) ?>
									<?= _x( 'Plan limits', 'current plan', 'voxel' ) ?>
							</a>
						</li>
					<?php endif ?>
				</ul>
			</div>
		</div>
	<?php elseif ( $membership->get_type() === 'default' ): ?>
		<?php if ( $membership->plan->get_key() === 'default' ): ?>
			<div class="ac-body">
				<p><?= _x( 'Your current plan is', 'current plan', 'voxel' ) ?> <?= $membership->plan->get_label() ?></p>
				<?php if ( $role && $role->is_managed_by_voxel() && $role->has_plans_enabled() ): ?>
					<div class="ac-bottom">
						<ul class="simplify-ul current-plan-btn">
							<li>
								<a href="<?= esc_url( $switch_url ) ?>" class="ts-btn ts-btn-1">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_viewplan_ico') ) ?: \Voxel\svg( 'eye.svg' ) ?>
									<?= _x( 'Explore plans', 'current plan', 'voxel' ) ?>
								</a>
							</li>
							<?php if ( $membership->plan->get_key() != 'default' ): ?>
								<li>
									<a href="<?= esc_url( $modify_url ) ?>" class="ts-btn ts-btn-1">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_configure_ico') ) ?: \Voxel\svg( 'cog.svg' ) ?>
										<?= _x( 'Plan limits', 'current plan', 'voxel' ) ?>
									</a>
								</li>
							<?php endif ?>
						</ul>
					</div>
				<?php endif ?>
			</div>
		<?php else: ?>
			<div class="ac-body">
				<p><?= _x( 'Your current membership plan was manually assigned.', 'current plan', 'voxel' ) ?></p>
				<div class="ac-bottom">
					<ul class="simplify-ul current-plan-btn">
						<li>
							<a href="<?= esc_url( $switch_url ) ?>" class="ts-btn ts-btn-1">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_switch_ico') ) ?: \Voxel\svg( 'switch.svg' ) ?>
								<?= _x( 'Switch', 'current plan', 'voxel' ) ?>
							</a>
						</li>
						<li>
							<a href="<?= esc_url( $modify_url ) ?>" class="ts-btn ts-btn-1">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_configure_ico') ) ?: \Voxel\svg( 'cog.svg' ) ?>
								<?= _x( 'Plan limits', 'current plan', 'voxel' ) ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php endif ?>
	<?php endif ?>
</div>
<?php if ( ! ( empty( $switchable_roles ) || $user->has_cap('administrator') || $user->has_cap('editor') ) ): ?>
<div class="ts-panel active-plan role-panel">
	<div class="ac-head">
		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_role_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
		<?php if ( ! empty( $current_roles ) ): ?>
			<b>
				<?= \Voxel\replace_vars( _x( 'Your current role is @role_label', 'current role', 'voxel' ), [
					'@role_label' => join( ', ', array_map( function( $role ) {
						return $role->get_label();
					}, $current_roles ) ),
				] ) ?>
			</b>
		<?php else: ?>
			<p><?= _x( 'You do not have a role assigned currently.', 'current role', 'voxel' ) ?></p>
		<?php endif ?>
	</div>

	
		<div class="ac-body">
			<!-- <div class="ac-bottom">
				<a href="#" onclick="event.preventDefault(); document.getElementById('vx-switchable-roles').classList.toggle('hidden')" class="ts-btn ts-btn-1">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_switch_ico') ) ?: \Voxel\svg( 'switch.svg' ) ?>
					<?= _x( 'Switch role', 'current role', 'voxel' ) ?>
				</a>
			</div> -->

			<div class="ac-bottom" id="vx-switchable-roles">
				<ul class="simplify-ul current-plan-btn">
					<?php foreach ( $switchable_roles as $role ): ?>
						<li>
							<a
								vx-action
								href="<?= add_query_arg( [
									'role_key' => $role->get_key(),
									'_wpnonce' => wp_create_nonce( 'vx_switch_role' ),
								], home_url( '/?vx=1&action=roles.switch_role' ) ) ?>"
								class="ts-btn ts-btn-1"
							>		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_switch_ico') ) ?: \Voxel\svg( 'switch.svg' ) ?>
								<?= \Voxel\replace_vars( _x( 'Switch to @role_label', 'current role', 'voxel' ), [
									'@role_label' => $role->get_label(),
								] ) ?>
							</a>
						</li>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
	
</div>
<?php endif ?>