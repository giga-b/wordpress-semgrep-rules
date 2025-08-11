<?php
/**
 * Posts unlocked template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/restrict-content/posts-unlocked.php
 */
global $gamipress_restrict_content_template_args;

// Shorthand
$a = $gamipress_restrict_content_template_args; ?>

<div class="gamipress-restrict-content-posts-unlocked">

    <?php if( $a['query']->query['post__in'] ) : ?>

        <?php
        /**
         * Before render posts unlocked
         *
         * @since 1.0.8
         *
         * @param array $template_args Template received arguments
         */
        do_action( 'gamipress_restrict_content_before_render_posts_unlocked', $a ); ?>

        <?php while( $a['query']->have_posts() ) :
            $a['query']->the_post();
            $post = get_post(); ?>
            

        <div class="gamipress-restrict-content-post-unlocked">

            <?php
            /**
             * Before render a post unlocked
             *
             * @since 1.0.8
             *
             * @param WP_Post   $post           Post unlocked
             * @param array     $template_args  Template received arguments
             */
            do_action( 'gamipress_restrict_content_before_render_posts_unlocked_post', $post, $a ); ?>

            <a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a>

            <?php
            /**
             * After render a post unlocked
             *
             * @since 1.0.8
             *
             * @param WP_Post   $post           Post unlocked
             * @param array     $template_args  Template received arguments
             */
            do_action( 'gamipress_restrict_content_after_render_posts_unlocked_post', $post, $a ); ?>

        </div>

        <?php endwhile; 
        wp_reset_postdata(); ?>

        <?php
        /**
         * After render posts unlocked
         *
         * @since 1.0.8
         *
         * @param array $template_args Template received arguments
         */
        do_action( 'gamipress_restrict_content_after_render_posts_unlocked', $a ); ?>

    <?php endif; ?>

</div>
