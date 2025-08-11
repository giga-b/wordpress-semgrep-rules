<?php
/**
 * Time-based reward template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/time_based_rewards/time-based-reward.php
 */
global $gamipress_time_based_rewards_template_args;

// Shorthand
$a = $gamipress_time_based_rewards_template_args;

// Setup vars
$prefix = '_gamipress_time_based_rewards_';
$user_id = get_current_user_id();
$public = (bool) gamipress_time_based_rewards_get_option( 'public', false );
$rewards = gamipress_get_post_meta( get_the_ID(), $prefix . 'rewards' );
$can_claim = gamipress_time_based_rewards_user_can_claim( get_the_ID(), $user_id ); ?>

<div id="gamipress-time-based-reward-<?php the_ID(); ?>" class="gamipress-time-based-reward <?php if( $can_claim ) : ?>can-claim<?php endif; ?>">

    <?php
    /**
     * Before render time-based reward
     *
     * @param int   $time_based_reward_id   The time-based reward ID
     * @param array $template_args          Template received arguments
     */
    do_action( 'gamipress_before_render_time_based_reward', get_the_ID(), $a ); ?>

    <?php // Thumbnail
    if( $a['thumbnail'] === 'yes' && post_type_supports( 'time-based-reward', 'thumbnail' ) ) : ?>
        <div class="gamipress-time-based-reward-thumbnail">

            <?php // Link to the time-based reward page
            if( $a['link'] === 'yes' && $public ) : ?>
                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php echo get_the_post_thumbnail( get_the_ID() ); ?></a>
            <?php else : ?>
                <?php echo get_the_post_thumbnail( get_the_ID() ); ?>
            <?php endif; ?>

        </div>

        <?php
        /**
         * After time-based reward thumbnail
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param array $template_args          Template received arguments
         */
        do_action( 'gamipress_after_time_based_reward_thumbnail', get_the_ID(), $a ); ?>

    <?php endif; ?>

    <?php // Title
    if( $a['title'] === 'yes' ) : ?>
        <h2 class="gamipress-time-based-reward-title">

            <?php // Link to the time-based reward page
            if( $a['link'] === 'yes' && $public ) : ?>
                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
            <?php else : ?>
                <?php the_title(); ?>
            <?php endif; ?>

        </h2>

        <?php
        /**
         * After time-based reward title
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param array $template_args          Template received arguments
         */
        do_action( 'gamipress_after_time_based_reward_title', get_the_ID(), $a ); ?>
    <?php endif; ?>

    <?php // Excerpt
    if( $a['excerpt'] === 'yes' ) :  ?>
        <div class="gamipress-time-based-reward-excerpt">
            <?php
            $excerpt = has_excerpt() ? gamipress_get_post_field( 'post_excerpt', get_the_ID() ) : gamipress_get_post_field( 'post_content', get_the_ID() );
            echo do_shortcode( wpautop( apply_filters( 'get_the_excerpt', $excerpt, get_post() ) ) );
            ?>
        </div><!-- .gamipress-time-based-reward-excerpt -->

        <?php
        /**
         * After time-based reward excerpt
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param array $template_args          Template received arguments
         */
        do_action( 'gamipress_after_time_based_reward_excerpt', get_the_ID(), $a ); ?>
    <?php endif; ?>

    <?php // Rewards
    if( $a['rewards'] === 'yes' && ( is_array( $rewards ) && count( $rewards ) ) ) :  ?>

        <?php echo gamipress_time_based_rewards_get_rewards_markup( get_the_ID(), $a ); ?>

        <?php
        /**
         * After time-based reward rewards
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param array $template_args          Template received arguments
         */
        do_action( 'gamipress_after_time_based_reward_rewards', get_the_ID(), $a ); ?>
    <?php endif; ?>

    <?php if( $user_id ) : ?>

        <?php // Next Reward In
        $next_reward_text = gamipress_get_post_meta( get_the_ID(), $prefix . 'next_reward_text' );

        // If not setup on time-based reward get from settings
        if( empty( $next_reward_text ) )
            $next_reward_text = gamipress_time_based_rewards_get_option( 'next_reward_text', __( 'Next reward in:', 'gamipress-time-based-rewards' ) );

        $next_claim_date = gamipress_time_based_rewards_get_next_claim_date( get_the_ID(), $user_id );
        $human_next_claim_date = gamipress_time_based_rewards_get_human_next_claim_date( get_the_ID(), $user_id ); ?>

        <p class="gamipress-time-based-reward-next-reward">

            <span class="gamipress-time-based-reward-next-reward-text"><?php echo $next_reward_text; ?></span>
            <span class="gamipress-time-based-reward-next-reward-counter" data-next-date="<?php echo $next_claim_date; ?>"><?php echo $human_next_claim_date; ?></span>

        </p>

        <?php
        /**
         * After time-based reward next reward text
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param array $template_args          Template received arguments
         */
        do_action( 'gamipress_after_time_based_reward_next_reward', get_the_ID(), $a ); ?>

        <?php // Claim
        $claim_button_text = gamipress_get_post_meta( get_the_ID(), $prefix . 'claim_button_text' );

        // If not setup on time-based reward get from settings
        if( empty( $claim_button_text ) )
            $claim_button_text = gamipress_time_based_rewards_get_option( 'claim_button_text', __( 'Claim', 'gamipress-time-based-rewards' ) ); ?>

        <div class="gamipress-time-based-reward-claim">

            <div class="gamipress-spinner gamipress-time-based-reward-claim-spinner" style="display: none;"></div>

            <button type="button" class="gamipress-time-based-reward-claim-button" data-id="<?php the_ID(); ?>" <?php if( ! $can_claim ) : ?>disabled="disabled"<?php endif; ?>><?php echo $claim_button_text; ?></button>

        </div>

        <?php
        /**
         * After time-based reward claim button
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param array $template_args          Template received arguments
         */
        do_action( 'gamipress_after_time_based_reward_claim', get_the_ID(), $a ); ?>

    <?php else : ?>

        <?php // Guest message
        $guest_message = gamipress_get_post_meta( get_the_ID(), $prefix . 'guest_message' );

        // If not setup on time-based reward get from settings
        if( empty( $guest_message ) )
            $guest_message = gamipress_time_based_rewards_get_option( 'guest_message', __( 'Log in to claim', 'gamipress-time-based-rewards' ) );

        if( ! empty( $guest_message ) ) :
            // Execute shortcodes in guest message
            $guest_message = do_shortcode( $guest_message ); ?>

            <div class="gamipress-time-based-reward-guest-message"><?php echo $guest_message; ?></div>

            <?php
            /**
             * After time-based reward guest message
             *
             * @param int   $time_based_reward_id   The time-based reward ID
             * @param array $template_args          Template received arguments
             */
            do_action( 'gamipress_after_time_based_reward_guest_message', get_the_ID(), $a ); ?>
        <?php endif; ?>

    <?php endif; ?>

    <?php
    /**
     * After render time-based reward
     *
     * @param int   $time_based_reward_id   The time-based reward ID
     * @param array $template_args          Template received arguments
     */
    do_action( 'gamipress_after_render_time_based_reward', get_the_ID(), $a ); ?>

</div>
