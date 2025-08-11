<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Live mode webhook endpoints</h3>

	</div>
	<?php if ( $config['stripe']['secret'] ): ?>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<!-- <p> Live mode webhook endpoints are active.</p> -->
				<div class="basic-ul">
					<li>
						<a href="#" @click.prevent="checkEndpointStatus('live')" ref="liveEndpointStatus" class="ts-button ts-outline">Check status</a>
					</li>
					<li>
						<a href="#" @click.prevent="checkEndpointStatus('live', true)" class="ts-button ts-outline">Stripe Connect status</a>
					</li>

					<li>
						<a href="#" @click.prevent="webhooks.liveDetails = ! webhooks.liveDetails" class="ts-button ts-outline">Details</a>
					</li>

					<li>
						<a
					href="https://dashboard.stripe.com/webhooks/<?= esc_attr( $config['stripe']['webhooks']['live']['id'] ) ?>"
					target="_blank"
					class="ts-button ts-outline"
				>Open in Stripe Dashboard</a>
					</li>
				</div>
			</div>
		</div>
		<template v-if="webhooks.liveDetails">
			<div class="x-row" :class="{'vx-disabled': !webhooks.editLiveDetails}">
				<div class="ts-form-group x-col-12">
					<label>Endpoint ID</label>
					<input type="text" v-model="config.stripe.webhooks.live.id">
				</div>
				<div class="ts-form-group x-col-12">
					<label>Endpoint secret</label>
					<input type="text" v-model="config.stripe.webhooks.live.secret">
				</div>
				<div class="ts-form-group x-col-12">
					<label>Connect endpoint ID</label>
					<input type="text" v-model="config.stripe.webhooks.live_connect.id">
				</div>
				<div class="ts-form-group x-col-12">
					<label>Connect endpoint secret</label>
					<input type="text" v-model="config.stripe.webhooks.live_connect.secret">
				</div>
			</div>
			<div class="x-row">
				<div class="ts-form-group x-col-12">
					<a
						href="#"
						class="ts-button ts-outline"
						@click.prevent="webhooks.editLiveDetails = !webhooks.editLiveDetails"
					>Modify</a>
				</div>
			</div>
		</template>
	<?php else: ?>
		<div class="ts-form-group ">
			<p>Stripe API keys are required to setup webhook endpoints.</p>
		</div>
	<?php endif ?>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Test mode webhook endpoints</h3>
	</div>
	<?php if ( $config['stripe']['test_secret'] ): ?>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<!-- <p><i class="las la-check"></i> Test mode webhook endpoints are active.</p> -->

				<div class="basic-ul">
					<li>
						<a href="#" @click.prevent="checkEndpointStatus('test')" class="ts-button ts-button ts-outline" ref="testEndpointStatus">Check status</a>
					</li>
					<li>
						<a href="#" @click.prevent="checkEndpointStatus('test', true)" class="ts-button ts-button ts-outline">Stripe Connect status</a>
					</li>
					<li>
						<a href="#" @click.prevent="webhooks.testDetails = ! webhooks.testDetails" class="ts-button ts-button ts-outline">Details</a>
					</li>
					<li>
						<a
							href="https://dashboard.stripe.com/test/webhooks/<?= esc_attr( $config['stripe']['webhooks']['test']['id'] ) ?>"
							target="_blank"
							class="ts-button ts-button ts-outline"
						>Open in Stripe Dashboard</a>
					</li>
				</div>
			</div>
		</div>
		<template v-if="webhooks.testDetails">
			<div class="x-row" :class="{'vx-disabled': !webhooks.editTestDetails}">
				<div class="ts-form-group x-col-12">
					<label>Endpoint ID</label>
					<input type="text" v-model="config.stripe.webhooks.test.id">
				</div>
				<div class="ts-form-group x-col-12">
					<label>Endpoint secret</label>
					<input type="text" v-model="config.stripe.webhooks.test.secret">
				</div>
				<div class="ts-form-group x-col-12">
					<label>Connect endpoint ID</label>
					<input type="text" v-model="config.stripe.webhooks.test_connect.id">
				</div>
				<div class="ts-form-group x-col-12">
					<label>Connect endpoint secret</label>
					<input type="text" v-model="config.stripe.webhooks.test_connect.secret">
				</div>
			</div>
			<div class="x-row">
				<div class="ts-form-group x-col-12">
					<a
						href="#"
						class="ts-button ts-outline"
						@click.prevent="webhooks.editTestDetails = !webhooks.editTestDetails"
					>Modify</a>
				</div>
			</div>
		</template>
	<?php else: ?>
		<div class="ts-form-group ">
			<p>Test mode API keys are required to setup webhook endpoints.</p>
		</div>
	<?php endif ?>
</div>
