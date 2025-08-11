<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$ics = \Voxel\Utils\Sharer::get_icalendar_data( [
	'start' => $action['ts_action_cal_start_date'],
	'end' => $action['ts_action_cal_end_date'],
	'title' => $action['ts_action_cal_title'],
	'description' => $action['ts_action_cal_desc'],
	'location' => $action['ts_action_cal_location'],
	'url' => $action['ts_action_cal_url'],
] );

if ( ! $ics ) {
	return;
}
?>
<?= $start_action ?>
<a href="data:text/calendar;base64,<?= base64_encode( $ics ) ?>" download="<?= esc_attr( $action['ts_action_cal_title'] ?: 'event' ) ?>.ics" role="button" class="ts-action-con" rel="nofollow">
	<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
	<?= $action['ts_acw_initial_text'] ?>
</a>
<?= $end_action ?>
