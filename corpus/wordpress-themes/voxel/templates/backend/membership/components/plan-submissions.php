<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="x-row h-center">
	<div class="x-col-12 ts-content-head">
		<h1>Post submission</h1>
		<p>Set the post limits (per post type) that users with this plan can create.</p>
	</div>
</div>
<div class="x-row">
	<div class="x-col-6 used-fields">
		<div class="sub-heading"><p>Added post types</p></div>
		<div v-for="limit, postType in plan.submissions" class="single-field wide" :class="{open: limits.activePostType === postType}">
			<div class="field-head" @click.prevent="toggleSubmission(postType)">
				<p class="field-name">{{ $root.postTypes[ postType ]?.label || postType }}</p>
				<div class="field-actions">
					<span class="field-action all-center">
						<a href="#" @click.stop.prevent="delete plan.submissions[ postType ]"><i class="lar la-trash-alt icon-sm"></i></a>
					</span>
				</div>
			</div>
			<div class="field-body" v-if="limits.activePostType === postType">
				<div class="x-row">
					<div class="x-col-12">
						<ul class="inner-tabs" style="padding-bottom: 0px;">
						  <li :class="{'current-item': limits.activeTab === 'main'}">
						    <a href="#" @click.prevent="limits.activeTab = 'main'">Limits</a>
						  </li>
						  <li :class="{'current-item': limits.activeTab === 'additions'}">
						    <a href="#" @click.prevent="limits.activeTab = 'additions'">Additions</a>
						  </li>
						<!--   <li :class="{'current-item': limits.activeTab === 'expiration'}">
						    <a href="#" @click.prevent="limits.activeTab = 'expiration'">Expiration</a>
						  </li> -->
						</ul>
					</div>
					<template v-if="limits.activeTab === 'main'">
						<div class="ts-form-group x-col-12">
							<label>Post limit</label>
							<input type="number" v-model="limit.count">
						</div>
						<div class="ts-form-group x-col-12">
							<label>Posts that count toward limit</label>
							<select v-model="limit.count_mode">
								<option value="submitted_posts">Submitted posts: All published and pending post submissions</option>
								<option value="active_posts">Active posts: All published post submissions</option>
								<option value="custom">Custom: Manually set what statuses count toward limit</option>
							</select>
						</div>
						<div v-if="limit.count_mode === 'custom'" class="ts-form-group x-col-12">
							<label>Custom statuses</label>
							<select class="min-scroll" v-model="limit.count_mode_custom" multiple>
								<option value="publish">Published</option>
								<option value="pending">Pending</option>
								<option value="expired">Expired</option>
								<option value="unpublished">Unpublished</option>
								<option value="rejected">Rejected</option>
								<option value="draft">Draft</option>
								<option value="trash">Trash</option>
							</select>
						</div>
						<div v-if="limit.count_mode === 'custom' && limit.count_mode_custom.includes('expired')" class="ts-form-group x-col-12">
							<label>When an expired post is relisted by the author, it should</label>
							<select v-model="limit.relist_behavior">
								<option value="same_slot">Use the same submission slot</option>
								<option value="new_slot">Use an additional submission slot</option>
							</select>
						</div>
					</template>
					<template v-else-if="limits.activeTab === 'additions'">
						<div class="ts-form-group x-col-12">
							<div class="x-row">
								<div class="x-col-12 ts-form-group">
									<p>Using additions, you can allow the customer to extend submission limits for that post type for an additional cost which you can set below.</p>
								</div>
								<div class="x-col-12 ts-form-group" v-if="plan.key === 'default'">
									<p>Additional submissions are not available on the default plan.</p>
								</div>
								<template v-for="mode in ['live', 'test']">
									<div class="x-col-12" :class="{'vx-disabled': plan.key === 'default'}">
										<div class="ts-form-group">
											<label>{{ mode === 'live' ? 'Live mode' : 'Test mode' }}</label>
										</div>
										<template v-for="details, price_id in limit.price_per_addition[mode]">
											<div v-if="getPriceById( price_id, mode )" class="single-field wide" :class="{open: limits.activePrice === details}">
												<div class="field-head" @click.prevent="limits.activePrice = ( limits.activePrice === details ) ? null : details">
													<p class="field-name">{{ getPriceLabel( getPriceById( price_id, mode ) ) }}</p>
													<span class="field-type">{{ getPricePeriod( getPriceById( price_id, mode ) ) }}</span>
													<div class="field-actions">
														<a href="#" @click.stop.prevent="delete limit.price_per_addition[mode][price_id]" class="field-action all-center"><i class="lar la-trash-alt icon-sm"></i></a>
													</div>
												</div>
												<div class="field-body" v-if="limits.activePrice === details">
													<div class="x-row">
														<div class="ts-form-group x-col-6">
															<label>Price per additional post</label>
															<input v-model="details.amount" type="number">
														</div>
														<div class="ts-form-group x-col-6">
															<label>Currency</label>
															<input type="text" readonly :value="getPriceById( price_id, mode ).currency.toUpperCase()">
														</div>
													</div>
												</div>
											</div>
										</template>
										<div class="ts-form-group">
											<select @change="addPricePerAddition($event, limit, mode)">
												<option value="">Choose price</option>
												<template v-for="price in plan.pricing[mode].prices">
													<option v-if="!limit.price_per_addition[mode][price.id]" :value="price.id">
														{{ getPriceLabel(price) }} &mdash; {{ getPricePeriod(price) }}
													</option>
												</template>
											</select>
										</div>
									</div>
								</template>
							</div>
						</div>
					</template>
					<!-- <template v-else-if="limits.activeTab === 'expiration'">
						<div class="ts-form-group x-col-12">
						  <label>Post expiration mode</label>
						  <div class="ts-radio-container two-column min-scroll">
						  	 <label class="container-radio">Never expire<input type="radio" value="email">
						  	  <span class="checkmark"></span>
						  	</label>

						    <label class="container-radio">Keep posts published as long as the plan is active<input type="radio" value="email" checked>
						      <span class="checkmark"></span>
						    </label>


						    <label class="container-radio">Expire after a fixed time<input type="radio" value="address">
						      <span class="checkmark"></span>
						    </label>

						  </div>
						</div>
						<div class="ts-form-group x-col-12">
							<label>Expire after (days)</label>
							<input type="number" placeholder="Number of days">
						</div>
					</template> -->
				</div>
			</div>
		</div>
	</div>
	<div class="x-col-1"></div>
	<div class="x-col-5">
		<div class="available-fields-container">
			<div class="sub-heading">
			  <p>Select post type</p>
			</div>
			<div class="add-field">
				<template v-for="postType in $root.postTypes">
					<div v-if="postType.submittable" :class="{'vx-disabled':plan.submissions[postType.key]}">
						<a class="ts-button ts-outline" href="#" @click.prevent="addSubmission(postType.key)">{{ postType.label }}</a>
					</div>
				</template>
			</div>
		</div>

		<!-- <pre debug>{{ plan.submissions }}</pre> -->
	</div>
</div>
