<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="x-row">
	<div class="x-col-12 ts-content-head">
		<h1>Registration fields</h1>
		<p>Configure fields that will show up in the user registration form for this role.</p>
	</div>
</div>
<div class="x-row">



	<div class="used-fields x-col-6">
		<div class="sub-heading">
			<p>Used fields</p>
		</div>
		<div class="field-container ts-draggable-inserts" ref="fields-container">
			<draggable
				v-model="$root.config.registration.fields"
				group="fields"
				handle=".field-head"
				item-key="key"
				@start="$refs['fields-container'].classList.add('drag-active')"
				@end="$refs['fields-container'].classList.remove('drag-active')"
				@add="onRegistrationFieldAdd"
			>
				<template #item="{element: field, index: index}">
					<template v-if="field.source === 'auth'">
						<div class="single-field wide">
							<div class="field-head" @click="active_field = field">
								<p class="field-name">{{ field.label }}</p>
								<span class="field-type">{{ field.key.replace('voxel:auth-', '') }}</span>
							</div>
						</div>
					</template>
					<template v-else>
						<div class="single-field wide">
							<div class="field-head" @click="active_field = field">
								<p class="field-name">{{ field.label || fieldProp( field.key, 'label' ) }}</p>
								<span class="field-type">{{ fieldProp( field.key, 'type' ) }}</span>
								<div class="field-actions">
									<span class="field-action all-center" v-if="field['enable-conditions']">
										<a href="#" @click.prevent="active_field = field" title="Conditional logic is enabled for this field">
											<i class="las la-code-branch icon-sm"></i>
										</a>
									</span>
									<span class="field-action all-center" @click.stop.prevent="deleteField(field)">
										<i class="lar la-trash-alt icon-sm"></i>
									</span>
								</div>
							</div>
						</div>
					</template>
				</template>
			</draggable>
		</div>
	</div>
	<div class="x-col-1"></div>
	<div class="x-col-5">
		<div class="available-fields-container">
			<div class="sub-heading">
				<p>Available profile fields</p>

			</div>
			<div>
				<span style="opacity: .5; padding-bottom: 15px; display: block;">The following fields are profile fields which can be featured during user registration for this role. To add more profile fields, edit the Profiles post type.</span>
			</div>

			<draggable class="add-field" :list="Object.values(available_fields)" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="key">
				<template #item="{element: field}">
					<div :class="{'vx-disabled': !canUseField(field)}">
						<div @click.prevent="useField( field )" class="ts-button ts-outline c-move">
							{{ field.props.label }}
						</div>
					</div>
				</template>
			</draggable>

		</div>
	</div>


</div>

<field-modal v-if="active_field" :field="active_field"></field-modal>
