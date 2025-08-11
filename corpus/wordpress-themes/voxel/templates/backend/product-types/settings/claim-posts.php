<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.claims.enabled',
			'label' => 'Enable claim listing functionality',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="config.claims.enabled">
			<?php \Voxel\Form_Models\Select_Model::render( [
				'v-model' => 'config.claims.proof_of_ownership',
				'label' => 'Proof of ownership',
				'classes' => 'x-col-12',
				'choices' => [
					'required' => 'Required',
					'optional' => 'Optional',
					'disabled' => 'Disabled',
				],
			] ) ?>
		</template>
	</div>
</div>

<template v-if="config.claims.enabled">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Payments</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Form_Models\Select_Model::render( [
				'v-model' => 'config.claims.payments.mode',
				'label' => 'Payment mode',
				'classes' => 'x-col-12',
				'choices' => [
					'payment' => 'Single payment: User pays a one time fee',
					'offline' => 'Offline payment: Payments are handled off-site',
				],
			] ) ?>

			<?php \Voxel\Form_Models\Select_Model::render( [
				'v-model' => 'config.claims.order_approval',
				'label' => 'Order approval',
				'classes' => 'x-col-12',
				'choices' => [
					'automatic' => 'Automatic: Order is approved immediately',
					'manual' => 'Manual: Order is approved manually',
				],
			] ) ?>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Prices</h3>
		</div>
		<div class="x-row">
			<div class="x-col-12 field-container">
				<template v-if="config.claims.prices.length">
					<template v-for="price, index in config.claims.prices">
						<div
							v-if="props.claimable_post_types[ price.post_type ]"
							class="single-field wide"
							:class="{open: state.activeClaimPrice === price}"
						>
							<div class="field-head" @click="state.activeClaimPrice = state.activeClaimPrice === price ? null : price">
								<p class="field-name">{{ props.claimable_post_types[ price.post_type ].label }}</p>
								<p class="field-type">{{ ( price.amount !== null && price.amount !== '' ) ? currencyFormat( price.amount ) : 'No price added' }}</p>
								<span class="field-type"></span>
								<div class="field-actions">
									<span class="field-action all-center">
										<a href="#" @click.prevent="config.claims.prices.splice(index, 1)">
											<i class="lar la-trash-alt icon-sm"></i>
										</a>
									</span>
								</div>
							</div>
							<div v-if="state.activeClaimPrice === price" class="field-body">
								<div class="x-row">
									<?php \Voxel\Form_Models\Number_Model::render( [
										'v-model' => 'price.amount',
										'label' => 'Claim price',
										'classes' => 'x-col-12',
										'step' => 'any',
									] ) ?>
								</div>
							</div>
						</div>
					</template>
				</template>
				<div v-else class="ts-form-group">
					<p>You have not added any post types yet.</p>
				</div>
			</div>
			<div class="x-col-12">
				<div class="add-field">
					<template v-for="post_type in props.claimable_post_types">
						<div
							v-if="!config.claims.prices.find( price => price.post_type === post_type.key )"
							class="ts-button ts-outline"
							@click.prevent="config.claims.prices.push( {
								post_type: post_type.key,
								amount: null,
							} )"
						>
							<p class="field-name">{{ post_type.label }}</p>
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>
