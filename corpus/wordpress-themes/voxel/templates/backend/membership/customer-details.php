<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="vx-single-customer" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<div class="vx-card-ui">
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Customer</p>
			</div>
			<div class="vx-card-content">
				<div class="vx-group">
					<span v-html="customer.avatar_markup"></span>
					<a :href="customer.edit_link">{{ customer.display_name }}</a>
				</div>
			</div>
		</div>
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>User ID</p>
			</div>
			<div class="vx-card-content">
				<a :href="customer.edit_link">#{{ customer.id }}</a>
			</div>
		</div>
		<div class="vx-card no-wp-style">
			<div class="vx-card-head">
				<p>Email</p>
			</div>
			<div class="vx-card-content">
				<a :href="'mailto:'+customer.email">{{ customer.email }}</a>
			</div>
		</div>

		<div class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Plan details</p>
			</div>
			<div class="vx-card-content">
				<table class="form-table">
					<tbody>
						<tr>
							<th>Plan</th>
							<td><strong><a :href="plan.edit_link">{{ plan.label }}</a></strong></td>
						</tr>

						<?php if ( $membership->get_type() === 'subscription' ): ?>
							<tr>
								<th>Status</th>
								<td>
									<?= sprintf(
										'<span class="%s">%s</span>',
										$membership->is_active() ? 'active' : '',
										ucwords( str_replace( '_', ' ', $membership->get_status() ) )
									) ?>
								</td>
							</tr>
							<tr>
								<th>Pricing</th>
								<td>
									<?= sprintf(
										'<strong>%s</strong> %s',
										\Voxel\currency_format( $membership->get_amount(), $membership->get_currency() ),
										\Voxel\interval_format( $membership->get_interval(), $membership->get_interval_count() )
									) ?>
								</td>
							</tr>
							<tr>
								<th>Created</th>
								<td>
									<?php if ( $timestamp = strtotime( $membership->get_created_at() ) ): ?>
										<?= \Voxel\datetime_format( $timestamp ) ?>
									<?php else: ?>
										&mdash;
									<?php endif ?>
								</td>
							</tr>

							<?php if ( $membership->will_cancel_at_period_end() ): ?>
								<tr>
									<th>Ends on</th>
									<td><?= \Voxel\date_format( $membership->get_current_period_end() ) ?> (renewal canceled by user)</td>
								</tr>
							<?php elseif ( $membership->get_status() === 'trialing' ): ?>
								<tr>
									<th>Trial end date</th>
									<td><?= \Voxel\date_format( $membership->get_trial_end() ) ?></td>
								</tr>
							<?php elseif ( $membership->get_status() === 'active' ): ?>
								<tr>
									<th>Renews on</th>
									<td><?= \Voxel\date_format( $membership->get_current_period_end() ) ?></td>
								</tr>
							<?php endif ?>

							<tr>
								<th>Subscription ID</th>
								<td><?= sprintf(
									'<a href="%s" target="_blank">%s %s</a>',
									$stripe_base_url . 'subscriptions/' . $membership->get_subscription_id(),
									$membership->get_subscription_id(),
									'<i class="las la-external-link-alt"></i>'
								) ?></td>
							</tr>
						<?php elseif ( $membership->get_type() === 'payment' ): ?>
							<tr>
								<th>Status</th>
								<td>
									<?= sprintf(
										'<span class="%s">%s</span>',
										$membership->is_active() ? 'active' : '',
										ucwords( str_replace( '_', ' ', $membership->get_status() ) )
									) ?>
								</td>
							</tr>
							<tr>
								<th>Pricing</th>
								<td>
									<?php if ( floatval( $membership->get_amount() ) === 0.0 ): ?>
										Free
									<?php else: ?>
										<?= sprintf(
											'<strong>%s</strong> one time payment',
											\Voxel\currency_format( $membership->get_amount(), $membership->get_currency() )
										) ?>
									<?php endif ?>
								</td>
							</tr>
							<tr>
								<th>Created</th>
								<td>
									<?php if ( $timestamp = strtotime( $membership->get_created_at() ) ): ?>
										<?= \Voxel\datetime_format( $timestamp ) ?>
									<?php else: ?>
										&mdash;
									<?php endif ?>
								</td>
							</tr>
							<tr>
								<th>Payment intent ID</th>
								<td><?= sprintf(
									'<a href="%s" target="_blank">%s %s</a>',
									$stripe_base_url . 'payments/' . $membership->get_payment_intent(),
									$membership->get_payment_intent(),
									'<i class="las la-external-link-alt"></i>'
								) ?></td>
							</tr>
						<?php elseif ( $membership->get_type() === 'default' ): ?>
							<?php if ( $plan->get_key() === 'default' ): ?>
								<tr>
									<th></th>
									<td>This user does not have an active paid membership plan.</td>
								</tr>
								<tr>
									<th>Eligible for free trial?</th>
									<td><?= $customer->is_eligible_for_free_trial() ? 'Yes' : 'No' ?></td>
								</tr>
							<?php else: ?>
								<tr>
									<th></th>
									<td>This membership plan was manually assigned to this user.</td>
								</tr>
							<?php endif ?>
						<?php endif ?>

						<?php if ( $customer->get_stripe_customer_id() ): ?>
							<tr>
								<th>Stripe Customer ID</th>
								<td>
									<?= sprintf(
										'<a href="%s" target="_blank">%s %s</a>',
										$stripe_base_url . 'customers/' . $customer->get_stripe_customer_id(),
										$customer->get_stripe_customer_id(),
										'<i class="las la-external-link-alt"></i>'
									) ?>
								</td>
							</tr>
						<?php endif ?>
						<!-- <tr>
							<th></th>
							<td>
								<div class="mt10">
									<a href="#" @click.prevent="state.show_edit_plan = !state.show_edit_plan">Edit plan</a>
								</div>
							</td>
						</tr> -->
					</tbody>
				</table>
			</div>
		</div>

		<div>
			<a class="button" href="#" @click.prevent="state.show_edit_plan = !state.show_edit_plan">Edit plan</a>
		</div>

		<div v-if="state.show_edit_plan" class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Edit plan</p>
			</div>
			<div class="vx-card-content">
				<form @submit.prevent="updatePlan">
					<table class="form-table">
						<tbody>
							<tr>
								<th>Plan</th>
								<td>
									<select v-model="edit_membership.plan" style="width: 250px;">
										<?php foreach ( \Voxel\Plan::all() as $plan ): ?>
											<option value="<?= esc_attr( $plan->get_key() ) ?>"><?= esc_html( $plan->get_label() ) ?></option>
										<?php endforeach ?>
									</select>
								</td>
							</tr>
							<template v-if="edit_membership.plan !== 'default'">
								<tr>
									<th>Payment method</th>
									<td>
										<select v-model="edit_membership.type" style="width: 250px;">
											<option value="subscription">Stripe subscription</option>
											<option value="payment">Stripe payment</option>
											<option value="default">No payment</option>
										</select>
									</td>
								</tr>
								<template v-if="edit_membership.type === 'subscription'">
									<tr>
										<th>Subscription ID</th>
										<td>
											<input type="text" v-model="edit_membership.subscription_id" style="width: 250px;">
										</td>
									</tr>
								</template>
								<template v-if="edit_membership.type === 'payment'">
									<tr>
										<th>Payment Intent ID</th>
										<td>
											<input type="text" v-model="edit_membership.payment_intent_id" style="width: 250px;">
										</td>
									</tr>
								</template>
							</template>
							<template v-if="edit_membership.plan === 'default'">
								<tr>
									<th>Eligible for free trial?</th>
									<td>
										<input type="checkbox" v-model="edit_membership.trial_allowed">
									</td>
								</tr>
							</template>
							<tr>
								<th></th>
								<td>
									<button type="submit" class="button button-primary" :class="{'vx-disabled': state.updating_plan}">Save changes</button>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
