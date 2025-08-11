<?php
/**
 * Admin membership settings.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<?php $displayPlan = function( $plan ) { ?>
	<div class="vx-panel">
		
			<div class="panel-icon">
				<i class="las la-user-circle"></i>
			</div>
			<div class="panel-info">
				<h3><?= $plan->get_label() ?></h3>
				<ul>
					<li><?= $plan->get_key() ?></li>
					<!-- <?php if ( $live_price_count = count( $plan->get_pricing()['live']['prices'] ?? [] ) ): ?>
						<li><?= $live_price_count ?> live prices</li>
					<?php else: ?>
						<li>No live prices created.</li>
					<?php endif ?>

					<?php if ( $test_price_count = count( $plan->get_pricing()['test']['prices'] ?? [] ) ): ?>
						<li><?= $test_price_count ?> test prices</li>
					<?php else: ?>
						<li>No test prices created.</li>
					<?php endif ?> -->
				</ul>
			</div>
			<a href="<?= esc_url( $plan->get_edit_link() ) ?>" class="ts-button edit-voxel ts-outline">
				Edit with Voxel
						<img src="<?php echo esc_url( \Voxel\get_image('post-types/logo.svg') ) ?>">
			</a>
		
	</div>
<?php } ?>

<div class="sticky-top">
	<div class="vx-head x-container">
			
		<h2>Membership plans
		</h2>
		
		<div class="cpt-header-buttons ts-col-1-3">
			<a href="<?= esc_url( $add_plan_url ) ?>" class="ts-button ts-save-settings btn-shadow">
				<i class="las la-plus icon-sm"></i>
				Create plan
			</a>
		</div>
		
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">
	<div class="vx-panels">
		<?php array_map( $displayPlan, $active_plans ) ?>
	</div>

</div>
<div class="ts-spacer"></div>
<div class="x-container ">
	
	<?php if ( ! empty( $archived_plans ) ): ?>
		<div class="x-row h-center">
			
				<a href="#" class="ts-button ts-transparent ts-btn-small" onclick="event.preventDefault(); document.getElementById('vx-archived-plans').classList.toggle('hide')">
					<i class="las la-arrow-down icon-sm"></i>
					Show archived plans
				</a>
			
		</div>
		<div class="ts-spacer"></div>
		<div class="vx-panels hide" id="vx-archived-plans">
			
				<?php array_map( $displayPlan, $archived_plans ) ?>
			
		</div>
	<?php endif ?>
</div>
