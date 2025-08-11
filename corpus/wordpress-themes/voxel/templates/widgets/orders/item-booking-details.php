<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="order-item-booking-details">
	<div v-if="booking.booking_status === 'canceled'" class="order-event">
		<div class="order-event-icon vx-red">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar') ) ?: \Voxel\get_svg( 'calendar.svg' ) ?>
		</div>

		<b v-if="booking.type === 'timeslots'"><?= _x( 'Your appointment was canceled', 'order booking details', 'voxel' ) ?></b>
		<b v-else><?= _x( 'Your booking was canceled', 'order booking details', 'voxel' ) ?></b>
	</div>
	<div v-else class="order-event">
		<div class="order-event-icon vx-blue">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar') ) ?: \Voxel\get_svg( 'calendar.svg' ) ?>
		</div>

		<b v-if="booking.type === 'timeslots'"><?= _x( 'Your appointment is confirmed', 'order booking details', 'voxel' ) ?></b>
		<b v-else><?= _x( 'Your booking is confirmed', 'order booking details', 'voxel' ) ?></b>

		<span>{{ booking.summary }}</span>

		<div class="further-actions">
			<template v-for="action, action_type in booking.actions">
				<template v-if="action_type === 'add_to_gcal' && ( ( parent.isCustomer() && action.enabled_for_customer ) || ( parent.isVendor() && action.enabled_for_vendor ) || ( parent.isAdmin() && action.enabled_for_vendor ) )">
					<a :href="action.link" target="_blank" class="ts-btn ts-btn-1">
						<?= _x( 'Add to Google Cal', 'order booking details', 'voxel' ) ?>
					</a>
				</template>
				<template v-if="action_type === 'add_to_ical' && ( ( parent.isCustomer() && action.enabled_for_customer ) || ( parent.isVendor() && action.enabled_for_vendor ) || ( parent.isAdmin() && action.enabled_for_vendor ) )">
					<a :href="action.link" :download="action.filename" role="button" target="_blank" class="ts-btn ts-btn-1">
						<?= _x( 'Add to iCal', 'order booking details', 'voxel' ) ?>
					</a>
				</template>
				<template v-if="action_type === 'cancel' && ( ( parent.isCustomer() && action.enabled_for_customer ) || ( parent.isVendor() && action.enabled_for_vendor ) )">
					<a href="#" @click.prevent="cancelBooking" target="_blank" class="ts-btn ts-btn-1">
						<?= _x( 'Cancel booking', 'order booking details', 'voxel' ) ?>
					</a>
				</template>
				<template v-if="action_type === 'reschedule' && booking.schedule !== null && ( ( parent.isCustomer() && action.enabled_for_customer ) || ( parent.isVendor() && action.enabled_for_vendor ) )">
					<form-group :popup-key="'booking-reschedule-'+order.id" wrapper-class="lg-width xl-height" ref="bookingCalendar" @save="confirmReschedule" @blur="blurReschedule" @clear="clearReschedule" clear-label="<?= esc_attr( _x( 'Cancel', 'order booking reschedule', 'voxel' ) ) ?>" save-label="<?= esc_attr( _x( 'Reschedule', 'order booking reschedule', 'voxel' ) ) ?>">
						<template #trigger>
							<a href="#" @click.prevent="openCalendar" target="_blank" class="ts-popup-target ts-btn ts-btn-1">
								<?= _x( 'Reschedule', 'order booking reschedule', 'voxel' ) ?>
							</a>
						</template>
						<template #popup>
							<div class="ts-booking-date ts-booking-date-single ts-form-group" ref="calendar">
								<input type="hidden" ref="calendarInput">
							</div>
							<div v-if="booking.type === 'timeslots' && reschedule.timeslots.date" class="ts-form-group">
								<label><?= _x( 'Time', 'order booking reschedule', 'voxel' ) ?></label>
								<div class="ts-filter">
									<select v-model="reschedule.timeslots.slot">
										<option :value="null"><?= _x( 'Pick a time', 'order booking reschedule', 'voxel' ) ?></option>
										<option v-for="s in currentDaySlots" :disabled="s._disabled" :value="s">
											{{ getSlotLabel(s) }}
										</option>
									</select>
								</div>
							</div>
						</template>
					</form-group>
				</template>
			</template>
		</div>
	</div>
</script>
