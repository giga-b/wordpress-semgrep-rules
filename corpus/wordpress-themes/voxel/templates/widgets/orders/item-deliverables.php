<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="order-item-deliverables">
	<template v-if="order.current_user.is_vendor && ( deliverables.automatic.length || deliverables.uploads.enabled )">
		<div class="order-event">
			<div class="order-event-icon vx-blue">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_files') ) ?: \Voxel\get_svg( 'file.svg' ) ?>
			</div>
			<b>{{ item.product.label }}</b>
			<span v-if="deliverables.automatic.length || deliverables.manual.length"><?= _x( 'Files shared with customer', 'order downloads', 'voxel' ) ?></span>
			<span v-else><?= _x( 'Share files with customer', 'order downloads', 'voxel' ) ?></span>
			<ul class="flexify simplify-ul vx-order-files">
				<li v-for="file in deliverables.automatic">
					<a :href="file.url" class="ts-order-file">{{ file.name }}</a>
				</li>
				<template v-for="group, groupIndex in deliverables.manual">
					<li v-for="file in group.files">
						<a :href="file.url" class="ts-order-file">{{ file.name }}</a>
						<a class="vx-remove-file" href="#" @click.prevent="removeFile(file, groupIndex)">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_trash') ) ?: \Voxel\get_svg( 'trash-can.svg' ) ?>
						</a>
					</li>
				</template>
			</ul>
			<div v-if="deliverables.uploads.enabled" class="further-actions">
				<file-upload
					:allowed-file-types="deliverables.uploads.allowed_file_types.join(',')"
					:max-file-count="deliverables.uploads.max_count"
					:sortable="false"
					ref="files"
					@update:modelValue="uploadsUpdated"
					v-model="new_uploads"
				></file-upload>
			</div>
		</div>
	</template>
	<template v-else-if="order.current_user.is_customer && ( deliverables.automatic.length || deliverables.manual.length )">
		<div class="order-event">
			<div class="order-event-icon vx-blue">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_files') ) ?: \Voxel\get_svg( 'file.svg' ) ?>
			</div>
			<b>{{ item.product.label }}</b>
			<span><?= _x( 'Files available to download', 'order downloads', 'voxel' ) ?></span>
			<ul class="flexify simplify-ul vx-order-files">
				<li v-for="file in deliverables.automatic">
					<a :href="file.url" class="ts-order-file">{{ file.name }}</a>
				</li>
				<template v-for="group, groupIndex in deliverables.manual">
					<li v-for="file in group.files">
						<a :href="file.url" class="ts-order-file">{{ file.name }}</a>
					</li>
				</template>
			</ul>
		</div>
	</template>
</script>

<script type="text/html" id="vx-file-upload">
	<a href="#" @click.prevent="$refs.input.click()" class="ts-btn ts-btn-1"><?= _x( 'Upload file', 'order downloads', 'voxel' ) ?></a>
	<input ref="input" type="file" class="hidden" :multiple="maxFileCount > 1" :accept="allowedFileTypes">
</script>
