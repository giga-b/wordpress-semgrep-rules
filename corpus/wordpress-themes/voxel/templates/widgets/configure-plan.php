<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="ts-configure-plan" v-cloak>
	<div class="config-plans">
		

		<template v-for="post_type in config.post_types">
			<div class="ts-panel">
				<div class="ac-head">
					<span v-html="post_type.icon"></span>
					<b>{{ post_type.label }}</b>
					<div bn class="ts-limits">
						<span>
							<?= \Voxel\replace_vars( _x( '@current_count posted', 'configure plan', 'voxel' ), [
								'@current_count' => '{{ post_type.current_count }}',
							] ) ?>
						</span> &#47;
						<template v-if="config.is_current_plan">
							<span :class="{'limit-red': isOverLimit(post_type)}">
								<?= \Voxel\replace_vars( _x( '@current_limit allowed', 'configure plan', 'voxel' ), [
									'@current_limit' => '{{ post_type.current_limit + post_type.additional_items }}',
								] ) ?>
							</span>
						</template>
						<template v-else>
							<span :class="{'limit-red': isOverLimit(post_type)}">
								<?= \Voxel\replace_vars( _x( '@current_limit allowed', 'configure plan', 'voxel' ), [
									'@current_limit' => '{{ post_type.base_limit + post_type.additional_items }}',
								] ) ?>
							</span>
						</template>
					</div>
				</div>
				<template v-if="post_type.supports_addition">
					<div class="increase-limits">
						<div class="limit-info">
							<p><?= _x( 'Increase limits', 'configure plan', 'voxel' ) ?></p>
						</div>
						<div class="ts-form plan-stepper">
							<div class="ts-form-group">
								<div class="ts-stepper-input flexify">
									<button class="ts-stepper-left ts-icon-btn" @click.prevent="post_type.additional_items -= 1; validateValue(post_type);">
										<?php \Voxel\svg( 'minus.svg' ) ?>
									</button>
									<input v-model="post_type.additional_items" @change="validateValue(post_type)" @input="validateValue(post_type)" type="number" class="ts-input-box">
									<button class="ts-stepper-right ts-icon-btn" @click.prevent="post_type.additional_items += 1; validateValue(post_type);">
										<?php \Voxel\svg( 'plus.svg' ) ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</template>
				<template v-if="config.is_current_plan">
					<template v-if="config.price.type !== 'default'">
						<div class="limit-warning" v-if="post_type.current_count > ( post_type.current_limit + post_type.additional_items )">
							<span class="info-ico">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_info_icon') ) ?: \Voxel\get_svg( 'info.svg' ) ?>
							</span>
							<p>
								<?= \Voxel\replace_vars( _x( '@unpublish_count of your most recent posts will be unpublished upon activating this plan', 'configure plan', 'voxel' ), [
									'@unpublish_count' => '{{ post_type.current_count - ( post_type.current_limit + post_type.additional_items ) }}',
								] ) ?>
							</p>

						</div>
					</template>
				</template>
				<template v-else>
					<div class="limit-warning" v-if="post_type.current_count > ( post_type.base_limit + post_type.additional_items )">
						<span class="info-ico">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_info_icon') ) ?: \Voxel\get_svg( 'info.svg' ) ?>
						</span>
						<p>
							<?= \Voxel\replace_vars( _x( '@unpublish_count of your most recent posts will be unpublished upon activating this plan', 'configure plan', 'voxel' ), [
								'@unpublish_count' => '{{ post_type.current_count - ( post_type.base_limit + post_type.additional_items ) }}',
							] ) ?>
						</p>
					</div>
				</template>
			</div>
		</template>
	</div>
	<template v-if="config.price.type === 'payment'">
		<template v-if="config.is_current_plan">
			<div class="plan-checkout">
				<div class="ts-panel">
					<div class="ac-head">
						<b><?= _x( 'Price calculator', 'configure plan', 'voxel' ) ?></b>
					</div>
					<div class="ac-body">
						<ul v-if="additionalItems.length" class="ts-cost-calculator flexify simplify-ul">
							<template v-for="item in additionalItems">
								<li v-if="additionalItems.length">
									<div class="ts-item-name">
										<p>+{{ item.additional_items }} {{ item.label }} &times; {{ priceFormat( item.price_per_addition) }}</p>
									</div>
									<div class="ts-item-price">
										<p>{{ priceFormat( item.price_per_addition * item.additional_items ) }}</p>
									</div>
								</li>
							</template>
							<li class="ts-total">
								<div class="ts-item-name">
									<p><?= _x( 'Total price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( additionsPrice ) }}</p>
								</div>
							</li>
						</ul>
						<div class="ts-form-group ts-btn-2">
							<a href="#" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-disabled': !additionalItems.length, 'vx-pending': processingCheckout}" @click.prevent="checkout">
							<?= _x( 'Upgrade', 'configure plan', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('right_ico') ) ?: \Voxel\get_svg( 'chevron-right.svg' ) ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</template>
		<template v-else>
			<div class="plan-checkout">
				<div class="ts-panel">
					<div class="ac-head">
						<b><?= _x( 'Price calculator', 'configure plan', 'voxel' ) ?></b>
					</div>
					<div class="ac-body">
						<ul class="ts-cost-calculator flexify simplify-ul">
							<li>
								<div class="ts-item-name">
									<p><?= _x( 'Base price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( config.price.amount ) }}</p>
								</div>
							</li>
							<template v-for="item in additionalItems">
								<li v-if="additionalItems.length">
									<div class="ts-item-name">
										<p>+{{ item.additional_items }} {{ item.label }} &times; {{ priceFormat( item.price_per_addition) }}</p>
									</div>
									<div class="ts-item-price">
										<p>{{ priceFormat( item.price_per_addition * item.additional_items ) }}</p>
									</div>
								</li>
							</template>
							<li class="ts-total">
								<div class="ts-item-name">
									<p><?= _x( 'Upgraded plan price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( totalPrice ) }}</p>
								</div>
							</li>
						</ul>
						<div class="ts-form-group">
							<a href="#" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': processingCheckout}" @click.prevent="checkout"><?= _x( 'Purchase', 'configure plan', 'voxel' ) ?></a>
						</div>
					</div>
				</div>
			</div>
		</template>
	</template>
	<template v-else-if="config.price.type === 'subscription'">
		<template v-if="config.is_current_plan">
			<div class="plan-checkout">
				<div class="ts-panel">
					<div class="ac-head">
						<b><?= _x( 'Price calculator', 'configure plan', 'voxel' ) ?></b>
					</div>
					<div class="ac-body">
						<ul class="ts-cost-calculator flexify simplify-ul">
							<li>
								<div class="ts-item-name">
									<p><?= _x( 'Current price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( config.price.amount ) }} {{ config.l10n.interval }}</p>
								</div>
							</li>
							<template v-for="item in additionalItems">
								<li v-if="additionalItems.length">
									<div class="ts-item-name">
										<p>+{{ item.additional_items }} {{ item.label }} &times; {{ priceFormat( item.price_per_addition) }}</p>
									</div>
									<div class="ts-item-price">
										<p>{{ priceFormat( item.price_per_addition * item.additional_items ) }}</p>
									</div>
								</li>
							</template>
							<li class="ts-total">
								<div class="ts-item-name">
									<p><?= _x( 'Upgraded plan price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( totalPrice ) }} {{ config.l10n.interval }}</p>
								</div>
							</li>
						</ul>
						<div class="ts-form-group">
							<a href="#" class="ts-btn ts-btn-2 ts-btn-large" @click.prevent="checkout" :class="{'vx-disabled': !additionalItems.length, 'vx-pending': processingCheckout}">
								<?= _x( 'Upgrade', 'configure plan', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('right_ico') ) ?: \Voxel\get_svg( 'chevron-right.svg' ) ?></a>
						</div>
					</div>
				</div>
			</div>
		</template>
		<template v-else>
			<div class="plan-checkout">
				<div class="ts-panel">
					<div class="ac-head">
						<b><?= _x( 'Price calculator', 'configure plan', 'voxel' ) ?></b>
					</div>
					<div class="ac-body">
						<ul class="ts-cost-calculator flexify simplify-ul">
							<li>
								<div class="ts-item-name">
									<p><?= _x( 'Base price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( config.price.amount ) }} {{ config.l10n.interval }}</p>
								</div>
							</li>
							<template v-for="item in additionalItems">
								<li v-if="additionalItems.length">
									<div class="ts-item-name">
										<p>+{{ item.additional_items }} {{ item.label }} &times; {{ priceFormat( item.price_per_addition) }}</p>
									</div>
									<div class="ts-item-price">
										<p>{{ priceFormat( item.price_per_addition * item.additional_items ) }}</p>
									</div>
								</li>
							</template>
							<li>
								<div class="ts-item-name">
									<p><?= _x( 'Upgraded plan price', 'configure plan', 'voxel' ) ?></p>
								</div>
								<div class="ts-item-price">
									<p>{{ priceFormat( totalPrice ) }} {{ config.l10n.interval }}</p>
								</div>
							</li>
						</ul>
						<div class="ts-form-group">
							<a href="#" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': processingCheckout}" @click.prevent="checkout"><?= _x( 'Subscribe', 'configure plan', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('right_ico') ) ?: \Voxel\get_svg( 'chevron-right.svg' ) ?></a>
						</div>
					</div>
				</div>
			</div>
		</template>
	</template>
	<template v-else-if="config.price.type === 'default'">
		<template v-if="config.is_current_plan">
			<div class="plan-checkout">
				<div class="ts-panel">

					<?php if ( $role = ( $user->get_roles()[0] ?? null ) ):
						$switch_url = get_permalink( $role->get_pricing_page_id() );
						if ( $user->has_role('administrator') || $user->has_role('editor') ) {
							$switch_url = get_permalink( \Voxel\Role::get('subscriber')->get_pricing_page_id() );
						}
						?>
						<div class="ac-body">
							<p><?= _x( 'The submission limits of this plan cannot be modified', 'configure plan', 'voxel' ) ?></p>
							<div class="ts-form-group">
								<a href="<?= esc_url( $switch_url ) ?>" class="ts-btn ts-btn-2 ts-btn-large"><?= _x( 'Switch to another plan', 'configure plan', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('right_ico') ) ?: \Voxel\get_svg( 'chevron-right.svg' ) ?></a>
							</div>
						</div>
					<?php endif ?>
				</div>
			</div>
		</template>
		<template v-else>
			<div class="plan-checkout">
				<div class="ts-panel">
					<div class="ac-head">
						<b><?= _x( 'Confirm this action', 'configure plan', 'voxel' ) ?></b>
					</div>
					<div class="ac-body">
						<div class="ts-form-group">
							<a href="#" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': processingCheckout}" @click.prevent="checkout">
								<?= _x( 'Switch to free plan', 'configure plan', 'voxel' ) ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</template>
	</template>
</div>
