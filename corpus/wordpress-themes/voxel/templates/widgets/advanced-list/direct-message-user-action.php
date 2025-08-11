<?php
$current_post = \Voxel\get_current_post();
$author_id = $current_post ? $current_post->get_author_id() : null;
if ( ! $author_id ) {
	return;
}
?>
<?= $start_action ?>
<a href="<?= esc_url( add_query_arg( 'chat', 'u'.$author_id, get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/') ) ) ?>" rel="nofollow" class="ts-action-con" onclick="Voxel.requireAuth(event)">
	<span class="ts-initial">
		<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
	</span>
</a>
<?= $end_action ?>
