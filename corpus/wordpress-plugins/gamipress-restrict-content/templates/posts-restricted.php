<?php
/**
 * Posts restricted template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/restrict-content/posts-restricted.php
 */
global $gamipress_restrict_content_template_args;

// Shorthand
$a = $gamipress_restrict_content_template_args; ?>

<div class="gamipress-restrict-content-posts-restricted">

    <?php if( $a['query']->have_posts() ) : ?>

        <?php
        /**
         * Before render posts restricted
         *
         * @since 1.0.8
         *
         * @param array $template_args Template received arguments
         */
        do_action( 'gamipress_restrict_content_before_render_posts_restricted', $a ); ?>

        <?php while( $a['query']->have_posts() ) :
            $a['query']->the_post();
            $post = get_post(); ?>

        <div class="gamipress-restrict-content-post-restricted">

            <?php
            /**
             * Before render a post restricted
             *
             * @since 1.0.8
             *
             * @param WP_Post   $post           Post restricted
             * @param array     $template_args  Template received arguments
             */
            do_action( 'gamipress_restrict_content_before_render_posts_restricted_post', $post, $a ); ?>

            <a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a>

            <?php
            /**
             * After render a post restricted
             *
             * @since 1.0.8
             *
             * @param WP_Post   $post           Post restricted
             * @param array     $template_args  Template received arguments
             */
            do_action( 'gamipress_restrict_content_after_render_posts_restricted_post', $post, $a ); ?>

        </div>

        <?php endwhile;
        wp_reset_postdata(); ?>

        <?php
        /**
         * After render posts restricted
         *
         * @since 1.0.8
         *
         * @param array $template_args Template received arguments
         */
        do_action( 'gamipress_restrict_content_after_render_posts_restricted', $a ); ?>

    <?php endif; ?>

</div>
