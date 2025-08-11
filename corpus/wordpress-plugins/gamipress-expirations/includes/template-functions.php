<?php
/**
 * Template Functions
 *
 * @package     GamiPress\Expirations\Template_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Common user pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_user_pattern_tags() {

    return apply_filters( 'gamipress_expirations_user_pattern_tags', array(
        '{user}'                => __( 'User display name.', 'gamipress-expirations' ),
        '{user_first}'          => __( 'User first name.', 'gamipress-expirations' ),
        '{user_last}'           => __( 'User last name.', 'gamipress-expirations' ),
        '{user_id}'             => __( 'User ID (useful for shortcodes that user ID can be passed as attribute).', 'gamipress-expirations' ),
    ) );

}

/**
 * Parse user pattern tags to a given pattern
 *
 * @since  1.0.0
 *
 * @param string    $pattern
 * @param int       $user_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_user_pattern( $pattern, $user_id ) {

    if( absint( $user_id ) === 0 ) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata( $user_id );

    $pattern_replacements = array(
        '{user}'                =>  ( $user ? $user->display_name : '' ),
        '{user_first}'          =>  ( $user ? $user->first_name : '' ),
        '{user_last}'           =>  ( $user ? $user->last_name : '' ),
        '{user_id}'             =>  ( $user ? $user->ID : '' ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_user_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_user_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}


/**
 * Get an array of achievement pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_achievement_pattern_tags() {

    return apply_filters( 'gamipress_expirations_achievement_pattern_tags', array_merge(
        gamipress_expirations_get_user_pattern_tags(),
        array(
            '{id}'                  => __( 'The achievement ID (useful for shortcodes that achievement ID can be passed as attribute).', 'gamipress-expirations' ),
            '{title}'               => __( 'The achievement title.', 'gamipress-expirations' ),
            '{url}'                 => __( 'URL to the achievement.', 'gamipress-expirations' ),
            '{link}'                => __( 'Link to the achievement with the achievement title as text.', 'gamipress-expirations' ),
            '{image}'               => __( 'The achievement featured image.', 'gamipress-expirations' ),
            '{excerpt}'             => __( 'The achievement excerpt.', 'gamipress-expirations' ),
            '{content}'             => __( 'The achievement content.', 'gamipress-expirations' ),
            '{steps}'               => __( 'The achievement steps.', 'gamipress-expirations' ),
            '{achievement_type}'    => __( 'The achievement type singular.', 'gamipress-expirations' ),
            '{congratulations}'     => __( 'The achievement congratulations text.', 'gamipress-expirations' ),
        )
    ) );

}

/**
 * Get a string with the desired achievement pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_expirations_get_achievement_pattern_tags_html() {

    $output = ' <a href="" class="gamipress-pattern-tags-list-toggle" data-show-text="' . __( 'Show tags', 'gamipress-expirations' ) . '" data-hide-text="' . __( 'Show tags', 'gamipress-expirations' ) . '">' . __( 'Show tags', 'gamipress-expirations' ) . '</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-expirations-achievement-pattern-tags-list" style="display: none;">';

    foreach( gamipress_expirations_get_achievement_pattern_tags() as $tag => $description ) {

        $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

        $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse pattern tags to a given achievement pattern
 *
 * @since  1.0.0
 *
 * @param string $pattern
 * @param int $user_id
 * @param int $post_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_achievement_pattern( $pattern, $user_id, $post_id ) {

    // The achievement post object
    $post = gamipress_get_post( $post_id );

    $achievement_types = gamipress_get_achievement_types();
    $achievement_type = $achievement_types[$post->post_type];

    // Parse user replacements
    $pattern = gamipress_expirations_parse_user_pattern( $pattern, $user_id );

    // Parse achievement replacements
    $pattern_replacements = array(
        '{id}'                  =>  $post->ID,
        '{title}'               =>  $post->post_title,
        '{url}'                 =>  get_the_permalink( $post->ID ),
        '{link}'                =>  sprintf( '<a href="%s" title="%s">%s</a>', get_the_permalink( $post->ID ), $post->post_title, $post->post_title ),
        '{image}'               =>  gamipress_get_achievement_post_thumbnail( $post->ID ),
        '{excerpt}'             =>  $post->post_excerpt,
        '{content}'             =>  $post->post_content,
        '{steps}'               =>  gamipress_expirations_get_achievement_steps_html( $post, $user_id ),
        '{achievement_type}'    =>  $achievement_type['singular_name'],
        '{congratulations}'     =>  gamipress_get_post_meta( $post->ID, '_gamipress_congratulations_text' ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_achievement_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_achievement_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Build a steps html to be used when parse notification tags
 *
 * @since  1.0.0
 *
 * @param object $achievement
 * @param int $user_id
 *
 * @return string
 */
function gamipress_expirations_get_achievement_steps_html( $achievement, $user_id ) {

    $achievement_steps_html = '';

    $steps = gamipress_get_required_achievements_for_achievement( $achievement->ID );

    if( is_array( $steps ) && count( $steps ) ) {

        $list_tag = gamipress_is_achievement_sequential( $achievement->ID ) ? 'ol' : 'ul';

        $achievement_steps_html .= "<{$list_tag}>";

        foreach( $steps as $step ) {
            // check if user has earned this Achievement, and add an 'earned' class
            $earned = count( gamipress_get_user_achievements( array(
                    'user_id' => absint( $user_id ),
                    'achievement_id' => absint( $step->ID ),
                    'since' => absint( gamipress_achievement_last_user_activity( $step->ID, $user_id ) )
                ) ) ) > 0;

            $title = $step->post_title;

            $achievement_steps_html .= '<li style="' . ( $earned ? 'text-decoration: line-through;' : '' ) . '">' . $title . '</li>';
        }

        $achievement_steps_html .= "</{$list_tag}>";
    }

    return $achievement_steps_html;

}

/**
 * Get an array of step pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_step_pattern_tags() {

    return apply_filters( 'gamipress_expirations_step_pattern_tags', array_merge(
        gamipress_expirations_get_user_pattern_tags(),
        array(
            '{label}'                       => __( 'The step label.', 'gamipress-expirations' ),
            '{achievement_id}'              => __( 'The achievement ID (useful for shortcodes that achievement ID can be passed as attribute).', 'gamipress-expirations' ),
            '{achievement_title}'           => __( 'The step achievement title.', 'gamipress-expirations' ),
            '{achievement_url}'             => __( 'URL to the step achievement.', 'gamipress-expirations' ),
            '{achievement_link}'            => __( 'Link to the step achievement with the achievement title as text.', 'gamipress-expirations' ),
            '{achievement_image}'           => __( 'The step achievement featured image.', 'gamipress-expirations' ),
            '{achievement_excerpt}'         => __( 'The step achievement excerpt.', 'gamipress-expirations' ),
            '{achievement_content}'         => __( 'The step achievement content.', 'gamipress-expirations' ),
            '{achievement_steps}'           => __( 'The step achievement list of steps.', 'gamipress-expirations' ),
            '{achievement_type}'            => __( 'The step achievement type singular.', 'gamipress-expirations' ),
            '{achievement_congratulations}' => __( 'The step achievement congratulations text.', 'gamipress-expirations' ),
        )
    ) );

}

/**
 * Get a string with the desired step pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_expirations_get_step_pattern_tags_html() {

    $output = ' <a href="" class="gamipress-pattern-tags-list-toggle" data-show-text="' . __( 'Show tags', 'gamipress-expirations' ) . '" data-hide-text="' . __( 'Show tags', 'gamipress-expirations' ) . '">' . __( 'Show tags', 'gamipress-expirations' ) . '</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-expirations-step-pattern-tags-list" style="display: none;">';

    foreach( gamipress_expirations_get_step_pattern_tags() as $tag => $description ) {

        $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

        $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse pattern tags to a given step pattern
 *
 * @since  1.0.0
 *
 * @param string    $pattern
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_step_pattern( $pattern, $user_id, $post_id ) {

    $post = gamipress_get_post( $post_id );

    $achievement = gamipress_get_step_achievement( $post_id );

    $achievement_types = gamipress_get_achievement_types();
    $achievement_type = $achievement_types[$achievement->post_type];

    // Parse user replacements
    $pattern = gamipress_expirations_parse_user_pattern( $pattern, $user_id );

    // Parse step replacements
    $pattern_replacements = array(
        '{label}'                       =>  $post->post_title,
        '{achievement_id}'              =>  $achievement->ID,
        '{achievement_title}'           =>  $achievement->post_title,
        '{achievement_url}'             =>  get_the_permalink( $achievement->ID ),
        '{achievement_link}'            =>  sprintf( '<a href="%s" title="%s">%s</a>', get_the_permalink( $achievement->ID ), $achievement->post_title, $achievement->post_title ),
        '{achievement_image}'           =>  gamipress_get_achievement_post_thumbnail( $achievement->ID ),
        '{achievement_excerpt}'         =>  $achievement->post_excerpt,
        '{achievement_content}'         =>  $achievement->post_content,
        '{achievement_steps}'           =>  gamipress_expirations_get_achievement_steps_html( $achievement, $user_id ),
        '{achievement_type}'            =>  $achievement_type['singular_name'],
        '{achievement_congratulations}' =>  gamipress_get_post_meta( $achievement->ID, '_gamipress_congratulations_text' ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_step_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_step_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Get an array of points award pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_points_award_pattern_tags() {

    return apply_filters( 'gamipress_expirations_points_award_pattern_tags', array_merge(
        gamipress_expirations_get_user_pattern_tags(),
        array(
            '{label}'                   => __( 'The points award label.', 'gamipress-expirations' ),
            '{points}'                  => __( 'The amount of points earned.', 'gamipress-expirations' ),
            '{points_label}'            => __( 'The points award points type. Singular or plural is based on the amount of points earned.', 'gamipress-expirations' ),
            '{points_balance}'          => __( 'The full amount of points user has been earned of this points type.', 'gamipress-expirations' ),
            '{points_balance_label}'    => __( 'The user\'s points amount label. Singular or plural is based on the current user\'s points amount.', 'gamipress-expirations' ),
            '{image}'                   => __( 'The points type featured image.', 'gamipress-expirations' ),
            '{points_type}'             => __( '(Deprecated user {points_label} tag instead) The points award points type. Singular or plural is based on the amount of points earned.', 'gamipress-expirations' ),
        )
    ) );

}

/**
 * Get a string with the desired points award pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_expirations_get_points_award_pattern_tags_html() {

    $output = ' <a href="" class="gamipress-pattern-tags-list-toggle" data-show-text="' . __( 'Show tags', 'gamipress-expirations' ) . '" data-hide-text="' . __( 'Show tags', 'gamipress-expirations' ) . '">' . __( 'Show tags', 'gamipress-expirations' ) . '</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-expirations-points-award-pattern-tags-list" style="display: none;">';

    foreach( gamipress_expirations_get_points_award_pattern_tags() as $tag => $description ) {

        $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

        $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse pattern tags to a given points award pattern
 *
 * @since  1.0.0
 *
 * @param string    $pattern
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_points_award_pattern( $pattern, $user_id, $post_id ) {

    $post = gamipress_get_post( $post_id );

    $points = absint( gamipress_get_post_meta( $post_id, '_gamipress_points' ) );

    $points_type = gamipress_get_points_award_points_type( $post_id );

    $points_balance = gamipress_get_user_points( $user_id, $points_type->post_name );

    $singular = $points_type->post_title;
    $plural = gamipress_get_post_meta( $points_type->ID, '_gamipress_plural_name' );

    // Parse user replacements
    $pattern = gamipress_expirations_parse_user_pattern( $pattern, $user_id );

    // Parse points award replacements
    $pattern_replacements = array(
        '{label}'                   =>  $post->post_title,
        '{points}'                  =>  gamipress_format_amount( $points, $points_type->post_name ),
        '{points_label}'            =>  _n( $singular, $plural, $points ),
        '{points_type}'             =>  _n( $singular, $plural, $points ),
        '{points_balance}'          =>  $points_balance,
        '{points_balance_label}'    =>  _n( $singular, $plural, $points_balance ),
        '{image}'                   =>  gamipress_get_points_type_thumbnail( $points_type->ID ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_points_award_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_points_award_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Get an array of points deduct pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_points_deduct_pattern_tags() {

    return apply_filters( 'gamipress_expirations_points_deduct_pattern_tags', array_merge(
        gamipress_expirations_get_user_pattern_tags(),
        array(
            '{label}'                   => __( 'The points deduct label.', 'gamipress-expirations' ),
            '{points}'                  => __( 'The amount of points deducted.', 'gamipress-expirations' ),
            '{points_label}'            => __( 'The points deduct points type label. Singular or plural is based on the amount of points earned.', 'gamipress-expirations' ),
            '{points_balance}'          => __( 'The full amount of points user has been earned of this points type.', 'gamipress-expirations' ),
            '{points_balance_label}'    => __( 'The user\'s points amount label. Singular or plural is based on the current user\'s points amount.', 'gamipress-expirations' ),
            '{image}'                   => __( 'The points type featured image.', 'gamipress-expirations' ),
            '{points_type}'             => __( '(Deprecated use {points_label} tag instead) The points deduct points type. Singular or plural is based on the amount of points earned.', 'gamipress-expirations' ),
        )
    ) );

}

/**
 * Get a string with the desired points deduct pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_expirations_get_points_deduct_pattern_tags_html() {

    $output = ' <a href="" class="gamipress-pattern-tags-list-toggle" data-show-text="' . __( 'Show tags', 'gamipress-expirations' ) . '" data-hide-text="' . __( 'Show tags', 'gamipress-expirations' ) . '">' . __( 'Show tags', 'gamipress-expirations' ) . '</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-expirations-points-deduct-pattern-tags-list" style="display: none;">';

    foreach( gamipress_expirations_get_points_deduct_pattern_tags() as $tag => $description ) {

        $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

        $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse pattern tags to a given points deduct pattern
 *
 * @since  1.0.0
 *
 * @param string    $pattern
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_points_deduct_pattern( $pattern, $user_id, $post_id ) {

    $post = gamipress_get_post( $post_id );

    $points = absint( gamipress_get_post_meta( $post_id, '_gamipress_points' ) );

    $points_type = gamipress_get_points_deduct_points_type( $post_id );

    $points_balance = gamipress_get_user_points( $user_id, $points_type->post_name );

    $singular = $points_type->post_title;
    $plural = gamipress_get_post_meta( $points_type->ID, '_gamipress_plural_name' );

    // Parse user replacements
    $pattern = gamipress_expirations_parse_user_pattern( $pattern, $user_id );

    // Parse points deduct replacements
    $pattern_replacements = array(
        '{label}'                   =>  $post->post_title,
        '{points}'                  =>  gamipress_format_amount( $points, $points_type->post_name ),
        '{points_label}'            =>  _n( $singular, $plural, $points ),
        '{points_type}'             =>  _n( $singular, $plural, $points ),
        '{points_balance}'          =>  $points_balance,
        '{points_balance_label}'    =>  _n( $singular, $plural, $points_balance ),
        '{image}'                   =>  gamipress_get_points_type_thumbnail( $points_type->ID ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_points_deduct_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_points_deduct_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Get an array of rank pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_rank_pattern_tags() {

    return apply_filters( 'gamipress_expirations_rank_pattern_tags', array_merge(
        gamipress_expirations_get_user_pattern_tags(),
        array(
            '{id}'                  => __( 'The rank ID (useful for shortcodes that rank ID can be passed as attribute).', 'gamipress-expirations' ),
            '{title}'               => __( 'The rank title.', 'gamipress-expirations' ),
            '{url}'                 => __( 'URL to the rank.', 'gamipress-expirations' ),
            '{link}'                => __( 'Link to the rank with the rank title as text.', 'gamipress-expirations' ),
            '{image}'               => __( 'The rank featured image.', 'gamipress-expirations' ),
            '{excerpt}'             => __( 'The rank excerpt.', 'gamipress-expirations' ),
            '{content}'             => __( 'The rank content.', 'gamipress-expirations' ),
            '{requirements}'        => __( 'The rank requirements.', 'gamipress-expirations' ),
            '{rank_type}'           => __( 'The rank type singular.', 'gamipress-expirations' ),
            '{congratulations}'     => __( 'The rank congratulations text.', 'gamipress-expirations' ),
        )
    ) );

}

/**
 * Get a string with the desired rank pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_expirations_get_rank_pattern_tags_html() {

    $output = ' <a href="" class="gamipress-pattern-tags-list-toggle" data-show-text="' . __( 'Show tags', 'gamipress-expirations' ) . '" data-hide-text="' . __( 'Show tags', 'gamipress-expirations' ) . '">' . __( 'Show tags', 'gamipress-expirations' ) . '</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-expirations-rank-pattern-tags-list" style="display: none;">';

    foreach( gamipress_expirations_get_rank_pattern_tags() as $tag => $description ) {

        $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

        $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse pattern tags to a given rank pattern
 *
 * @since  1.0.0
 *
 * @param string $pattern
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_rank_pattern( $pattern, $user_id, $post_id ) {

    // The rank post object
    $post = gamipress_get_post( $post_id );

    // Parse user replacements
    $pattern = gamipress_expirations_parse_user_pattern( $pattern, $user_id );

    // Parse rank replacements
    $pattern_replacements = array(
        '{id}'                  =>  $post->ID,
        '{title}'               =>  $post->post_title,
        '{url}'                 =>  get_the_permalink( $post->ID ),
        '{link}'                =>  sprintf( '<a href="%s" title="%s">%s</a>', get_the_permalink( $post->ID ), $post->post_title, $post->post_title ),
        '{image}'               =>  gamipress_get_rank_post_thumbnail( $post->ID ),
        '{excerpt}'             =>  $post->post_excerpt,
        '{content}'             =>  $post->post_content,
        '{requirements}'        =>  gamipress_expirations_get_rank_requirements_html( $post, $user_id ),
        '{rank_type}'           =>  gamipress_get_rank_type_singular( $post->post_type ),
        '{congratulations}'     =>  gamipress_get_post_meta( $post->ID, '_gamipress_congratulations_text' ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_rank_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_rank_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Build a steps html to be used when parse notification tags
 *
 * @since  1.0.0
 *
 * @param object $rank
 * @param int $user_id
 *
 * @return string
 */
function gamipress_expirations_get_rank_requirements_html( $rank, $user_id ) {

    $rank_requirements_html = '';

    $requirements = gamipress_get_rank_requirements( $rank->ID );

    if( is_array( $requirements ) && count( $requirements ) ) {

        $list_tag = gamipress_is_achievement_sequential( $rank->ID ) ? 'ol' : 'ul';

        $rank_requirements_html .= "<{$list_tag}>";

        foreach( $requirements as $requirement ) {
            // Check if user has earned this requirement, and add an 'earned' class
            $earned = count( gamipress_get_user_achievements( array(
                    'user_id' => absint( $user_id ),
                    'achievement_id' => absint( $requirement->ID ),
                    'since' => absint( gamipress_achievement_last_user_activity( $requirement->ID, $user_id ) )
                ) ) ) > 0;

            $title = $requirement->post_title;

            $rank_requirements_html .= '<li style="' . ( $earned ? 'text-decoration: line-through;' : '' ) . '">' . $title . '</li>';
        }

        $rank_requirements_html .= "</{$list_tag}>";
    }

    return $rank_requirements_html;

}

/**
 * Get an array of rank requirement pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_expirations_get_rank_requirement_pattern_tags() {

    return apply_filters( 'gamipress_expirations_rank_requirement_pattern_tags', array_merge(
        gamipress_expirations_get_user_pattern_tags(),
        array(
            '{label}'                   => __( 'The rank requirement label.', 'gamipress-expirations' ),
            '{rank_id}'                 => __( 'The rank ID (useful for shortcodes that rank ID can be passed as attribute).', 'gamipress-expirations' ),
            '{rank_title}'              => __( 'The requirement rank title.', 'gamipress-expirations' ),
            '{rank_url}'                => __( 'URL to the requirement rank.', 'gamipress-expirations' ),
            '{rank_link}'               => __( 'Link to the requirement rank with the rank title as text.', 'gamipress-expirations' ),
            '{rank_image}'              => __( 'The requirement rank featured image.', 'gamipress-expirations' ),
            '{rank_excerpt}'            => __( 'The requirement rank excerpt.', 'gamipress-expirations' ),
            '{rank_content}'            => __( 'The requirement rank content.', 'gamipress-expirations' ),
            '{rank_requirements}'       => __( 'The requirement rank list of requirements.', 'gamipress-expirations' ),
            '{rank_type}'               => __( 'The requirement rank type singular.', 'gamipress-expirations' ),
            '{rank_congratulations}'    => __( 'The requirement rank congratulations text.', 'gamipress-expirations' ),
        )
    ) );

}

/**
 * Get a string with the desired rank requirement pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_expirations_get_rank_requirement_pattern_tags_html() {

    $output = ' <a href="" class="gamipress-pattern-tags-list-toggle" data-show-text="' . __( 'Show tags', 'gamipress-expirations' ) . '" data-hide-text="' . __( 'Show tags', 'gamipress-expirations' ) . '">' . __( 'Show tags', 'gamipress-expirations' ) . '</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-expirations-rank-requirement-pattern-tags-list" style="display: none;">';

    foreach( gamipress_expirations_get_rank_requirement_pattern_tags() as $tag => $description ) {

        $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

        $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse pattern tags to a given rank requirement pattern
 *
 * @since  1.0.0
 *
 * @param string    $pattern
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return string Parsed pattern
 */
function gamipress_expirations_parse_rank_requirement_pattern( $pattern, $user_id, $post_id ) {

    $post = gamipress_get_post( $post_id );

    $rank = gamipress_get_rank_requirement_rank( $post_id );

    // Parse user replacements
    $pattern = gamipress_expirations_parse_user_pattern( $pattern, $user_id );

    // Parse rank requirement replacements
    $pattern_replacements = array(
        '{label}'                   =>  $post->post_title,
        '{rank_id}'                 =>  $rank->ID,
        '{rank_title}'              =>  $rank->post_title,
        '{rank_url}'                =>  get_the_permalink( $rank->ID ),
        '{rank_link}'               =>  sprintf( '<a href="%s" title="%s">%s</a>', get_the_permalink( $rank->ID ), $rank->post_title, $rank->post_title ),
        '{rank_image}'              =>  gamipress_get_rank_post_thumbnail( $rank->ID ),
        '{rank_excerpt}'            =>  $rank->post_excerpt,
        '{rank_content}'            =>  $rank->post_content,
        '{rank_requirements}'       =>  gamipress_expirations_get_rank_requirements_html( $rank, $user_id ),
        '{rank_type}'               =>  gamipress_get_rank_type_singular( $rank->post_type ),
        '{rank_congratulations}'    =>  gamipress_get_post_meta( $rank->ID, '_gamipress_congratulations_text' ),
    );

    $pattern_replacements = apply_filters( 'gamipress_expirations_parse_rank_requirement_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_expirations_parse_rank_requirement_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}