<?php
/**
 * Pricing plans widget template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<ul class="ts-plan-tabs simplify-ul flexify ts-generic-tabs">
	<?php foreach ( $groups as $group ): ?>
		<li class="<?= $group['_id'] === $default_group ? 'ts-tab-active' : '' ?>">
			<a href="#" data-id="<?= esc_attr( $group['_id'] ) ?>"><?= $group['group_label'] ?></a>
		</li>
	<?php endforeach ?>
</ul>
<div class="ts-plans-list <?= $allow_autoselect ? 'allow-autoselect' : '' ?>">
	<?php foreach ( $prices as $price ): ?>
		<div class="ts-plan-container <?= $price['group'] !== $default_group ? 'hidden' : '' ?>" data-group="<?= esc_attr( $price['group'] ) ?>">
			<div class="ts-plan-image flexify">
				<?= $price['image'] ?>
			</div>
			<div class="ts-plan-body">
				<div class="ts-plan-details">
					<span class="ts-plan-name"><?= $price['label'] ?></span>
				</div>
				<div class="ts-plan-pricing">
					<?php if ( $price['is_free'] ): ?>
						<span class="ts-plan-price"><?= _x( 'Free', 'pricing plans', 'voxel' ) ?></span>
					<?php else: ?>
						<span class="ts-plan-price"><?= $price['amount'] ?></span>
						<?php if ( $price['period'] ): ?>
							<div class="ts-price-period">/ <?= $price['period'] ?></div>
						<?php endif ?>
					<?php endif ?>
				</div>
				<?php if ( ! empty( $price['description'] ) ): ?>
					<div class="ts-plan-desc">
						<p><?= nl2br( $price['description'] ) ?></p>
					</div>
				<?php endif ?>
				<?php if ( ! empty( $price['features'] ) ): ?>
					<div class="ts-plan-features">
						<ul class="simplify-ul">
							<?php foreach ( $price['features'] as $feature ): ?>
								<li>
									<?= \Voxel\get_icon_markup( $feature['feature_ico'] ) ?: \Voxel\get_svg( 'checkmark-circle.svg' ) ?>

									<span><?= $feature['text'] ?></span>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				<?php endif ?>
				<div class="ts-plan-footer">
					<?php if ( is_user_logged_in() ):
						$membership = \Voxel\current_user()->get_membership();
						?>

						<?php if (
							( $membership->get_type() === 'subscription' && $membership->is_active() && $membership->get_price_id() === $price['price_id'] )
							|| ( $membership->get_type() === 'payment' && $membership->is_active() && $membership->get_price_id() === $price['price_id'] )
							|| ( $membership->get_type() === 'default' && $membership->plan->get_key() === 'default' && $price['key'] === 'default' && ! $membership->is_initial_state() )
						): ?>
							<a href="#" class="ts-btn ts-btn-1 ts-btn-large btn-disabled">
								<?= _x( 'Current plan', 'pricing plans', 'voxel' ) ?>
							</a>
						<?php else: ?>
							<a href="<?= esc_url( $price['link'] ) ?>" vx-action class="ts-btn ts-btn-2 ts-btn-large vx-pick-plan" data-price-key="<?= esc_attr( $price['key'] ) ?>" rel="nofollow">
								<?php if ( $membership->get_type() === 'default' ): ?>
									<?= _x( 'Pick plan', 'pricing plans', 'voxel' ) ?>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
								<?php else: ?>
									<?= _x( 'Switch to plan', 'pricing plans', 'voxel' ) ?>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
								<?php endif ?>
							</a>
						<?php endif ?>
					<?php else: ?>
						<a href="<?= esc_url( add_query_arg( 'register', '', \Voxel\get_auth_url() ) ) ?>" data-price-key="<?= esc_attr( $price['key'] ) ?>" class="ts-btn ts-btn-2 ts-btn-large vx-guest-plan">
							<?= _x( 'Pick plan', 'pricing plans', 'voxel' ) ?>
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
						</a>
					<?php endif ?>
				</div>
			</div>
		</div>
	<?php endforeach ?>
</div>
