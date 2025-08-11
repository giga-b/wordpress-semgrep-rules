<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div v-if="!priceSetup[mode]" class="x-row h-center">
	<div class="x-col-12">
		<div class="ts-spacer"></div>
	</div>
	<div class="x-col-4" >
		<div v-if="priceSetup[mode] === null" class="ts-form-group text-center">
			<p>Loading...</p>
		</div>
		<div v-else class="ts-form-group">
			<h3>Stripe error</h3>

			<p>{{ priceSetup[mode+'Error'] }}</p>
			<br>
			<div class="basic-ul">
				<a href="<?= esc_url( admin_url( 'admin.php?page=voxel-settings&tab=stripe.general' ) ) ?>" class="ts-button ts-outline full-width">
					<i class="las la-external-link-alt icon-sm"></i> Configure Stripe
				</a>
			</div>
		</div>
	</div>
</div>
<div v-else class="">
	<div class="x-row h-center">
		<div class="x-col-12 ts-content-head">
			<h1>Pricing</h1>
			<p>Plan pricing settings</p>
		</div>
	</div>
	<div class="x-row">

		<div class="x-col-6">
			<div class="sub-heading"><p>Prices</p></div>
			<div v-if="plan.pricing[mode].prices.length" class="field-container">
				<div v-for="price in plan.pricing[mode].prices" class="single-field wide" :class="{open: price === activePrice}">
					<div class="field-head" @click.prevent="activePrice = price === activePrice ? null : price">
						<p class="field-name">{{ getPriceLabel(price) }}</p>
						<span class="field-type">{{ getPricePeriod(price) }}</span>
						<div class="field-actions left-actions">
							<span v-if="!price.active" class="field-action all-center">
								<a href="#" @click.prevent title="This price has been disabled"><i class="las la-minus-circle"></i></a>
							</span>
							<span class="field-action all-center">
								<a href="#" @click.prevent><i class="las la-angle-down"></i></a>
							</span>
						</div>
					</div>
					<div class="field-body" v-if="price === activePrice">
						<div class="x-row">
							<div class="ts-form-group x-col-12">
								<label>Price ID</label>
								<span style="color: #fff;">{{price.id}}</span>

							</div>
							<div class="ts-form-group x-col-12">
								<label>Tax behavior</label>
								<span style="color: #fff; text-transform: capitalize;">{{price.tax_behavior}}</span>
							</div>
							<div class="ts-form-group x-col-12">
								<div class="basic-ul">
									<a class="ts-button ts-outline" :href="stripePriceUrl( price.id )" target="_blank">
										<i class="las la-external-link-alt icon-sm"></i>
										View in Stripe
									</a>
									<a class="ts-button ts-outline" href="#" @click.prevent="togglePrice( price.id )">
										<i class="las icon-sm" :class="price.active ? 'la-minus-circle' : 'la-plus-circle'"></i>
										{{ price.active ? 'Disable price' : 'Enable price' }}
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>




		<div class="x-col-1"></div>
		<div class="x-col-5">
			<div class="sub-heading"><p>Create price</p></div>
			<div class="ts-group">

				<div class="x-row">


					<?php \Voxel\Form_Models\Number_Model::render( [
						'v-model' => 'createPrice[mode].amount',
						'label' => 'Amount',
						'classes' => 'x-col-6',
					] ) ?>

					<?php \Voxel\Form_Models\Select_Model::render( [
						'v-model' => 'createPrice[mode].currency',
						'label' => 'Currency',
						'choices' => \Voxel\Stripe\Currencies::all(),
						'classes' => 'x-col-6',
					] ) ?>

					<?php \Voxel\Form_Models\Radio_Buttons_Model::render( [
						'v-model' => 'createPrice[mode].type',
						'label' => 'Type',
						'classes' => 'x-col-12',
						'choices' => [
							'recurring' => 'Recurring',
							'one_time' => 'One time',
						],
					] ) ?>

					<template v-if="createPrice[mode].type === 'recurring'">
						<div class="ts-form-group x-col-12">
							<span>Billing period</span>
						</div>

						<?php \Voxel\Form_Models\Number_Model::render( [
							'v-model' => 'createPrice[mode].intervalCount',
							'label' => 'Every',
							'classes' => 'x-col-6',
						] ) ?>

						<?php \Voxel\Form_Models\Select_Model::render( [
							'v-model' => 'createPrice[mode].interval',
							'label' => 'Unit',
							'classes' => 'x-col-6',
							'choices' => [
								'day' => 'Day(s)',
								'week' => 'Week(s)',
								'month' => 'Month(s)',
							],
						] ) ?>
					</template>

					<?php \Voxel\Form_Models\Switcher_Model::render( [
						'v-model' => 'createPrice[mode].includeTax',
						'label' => 'Include tax in price',
						'classes' => 'x-col-12',
					] ) ?>

					<div class="x-col-12">
						<a href="#" @click.prevent="insertPrice" class="ts-button ts-faded full-width">Create</a>
					</div>
				</div>
			</div>

			<div class="x-row">
				<div class="x-col-12">
					<a href="#" @click.prevent="showPriceAdvanced = !showPriceAdvanced" class="ts-button ts-transparent full-width">
						<i class="las la-arrow-down icon-sm"></i>
						Stripe details
					</a>
				</div>
				<div v-if="showPriceAdvanced" class="x-col-12">
					<div class="ts-group">
						<div class="x-row">
							<div class="ts-form-group x-col-12">
								<label style="padding-bottom: 0;">Product ID: {{plan.pricing[mode].product_id}}</label>

							</div>
							<div class="ts-form-group x-col-12">
								<div class="basic-ul">
									<a class="ts-button ts-outline" :href="stripeProductUrl()" target="_blank">
										<i class="las la-external-link-alt icon-sm"></i>
										View in Stripe
									</a>
									<a class="ts-button ts-outline" href="#" @click.prevent="syncPrices">
										<i class="las la-sync icon-sm"></i>
										Sync prices with Stripe
									</a>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
