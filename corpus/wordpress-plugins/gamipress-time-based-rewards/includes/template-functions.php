<?php
/**
 * Template Functions
 *
 * @package GamiPress\Time_Based_Rewards\Template_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin templates directory on GamiPress template engine
 *
 * @since 1.0.0
 *
 * @param array $file_paths
 *
 * @return array
 */
function gamipress_time_based_rewards_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/time_based_rewards/';
    $file_paths[] = trailingslashit( get_template_directory() ) . 'gamipress/time_based_rewards/';
    $file_paths[] =  GAMIPRESS_TIME_BASED_REWARDS_DIR . 'templates/';

    return $file_paths;

}
add_filter( 'gamipress_template_paths', 'gamipress_time_based_rewards_template_paths' );

/**
 * Pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_time_based_rewards_get_pattern_tags() {

    $pattern_tags = array(
        '{amount}'              => __( 'Amount awarded to the user.', 'gamipress-time-based-rewards' ),
        '{label}'               => __( 'Label of the element type earned. Singular or plural is based on the amount.', 'gamipress-time-based-rewards' ),
        '{image}'               => __( 'Featured image of the element earned.', 'gamipress-time-based-rewards' ),
        '{title}'               => __( 'Title of the element earned. Only for achievements and ranks.', 'gamipress-time-based-rewards' ),
        '{singular}'            => __( 'Singular label of the element type earned.', 'gamipress-time-based-rewards' ),
        '{plural}'              => __( 'Plural label of the element type earned.', 'gamipress-time-based-rewards' ),
    );

    return apply_filters( 'gamipress_time_based_rewards_pattern_tags', $pattern_tags );

}

/**
 * Parse pattern tags to a given pattern
 *
 * @since  1.0.0
 *
 * @param string   $pattern
 * @param array    $reward
 * @param mixed    $amount
 *
 * @return string Parsed pattern
 */
function gamipress_time_based_rewards_parse_pattern_tags( $pattern, $amount, $reward ) {

    $image = '';
    $title = '';
    $singular = '';
    $plural = '';

    if( in_array( $reward['post_type'], gamipress_get_points_types_slugs() ) ) {
        // Points type

        $points_type = gamipress_get_points_type( $reward['post_type'] );

        // If post type exists, setup its tags
        if( $points_type ) {
            $image = gamipress_get_points_type_thumbnail( $reward['post_type'] );
            $singular = $points_type['singular_name'];
            $plural = $points_type['plural_name'];
        }

    } else if( $reward['post_type'] === 'random_achievement' ) {

        $achievement_type = $reward['achievement_type'];

        if( empty( $achievement_type ) || $achievement_type === 'all' ) {
            $singular = __( 'Achievement', 'gamipress-time-based-rewards' );
            $plural = __( 'Achievements', 'gamipress-time-based-rewards' );
        } else {
            $achievement_type_data = gamipress_get_achievement_type( $achievement_type );

            if( $achievement_type_data ) {
                $singular = $achievement_type_data['singular_name'];
                $plural = $achievement_type_data['plural_name'];
            }
        }

        if( isset( $reward['achievement_id'] ) && absint( $reward['achievement_id'] ) !== 0 ) {

            // If random achievement has been assigned use the achievement title as title
            $image = gamipress_get_achievement_post_thumbnail( $reward['achievement_id'] );
            $title = gamipress_get_post_field( 'post_title', $reward['achievement_id'] );

        } else {

            // If random achievement hasn't been assigned set "Random {type}" as title
            if( $achievement_type === 'all' ) {
                $title = __( 'Random Achievement', 'gamipress-time-based-rewards' );
            } else {
                $title = sprintf( __( 'Random %s', 'gamipress-time-based-rewards' ), $singular );
            }

        }

    } else if( in_array( $reward['post_type'], gamipress_get_achievement_types_slugs() ) ) {
        // Achievement type

        $achievement_type = gamipress_get_achievement_type( $reward['post_type'] );

        if( $achievement_type ) {
            $singular = $achievement_type['singular_name'];
            $plural = $achievement_type['plural_name'];
        }

        // If achievement found, setup its tags
        if( absint( $reward['achievement_id'] ) !== 0 ) {
            $image = gamipress_get_achievement_post_thumbnail( $reward['achievement_id'] );
            $title = gamipress_get_post_field( 'post_title', $reward['achievement_id'] );
        }

    } else if( in_array( $reward['post_type'], gamipress_get_rank_types_slugs() ) ) {
        // Rank type

        $rank_type = gamipress_get_rank_type( $reward['post_type'] );

        if( $rank_type ) {
            $singular = $rank_type['singular_name'];
            $plural = $rank_type['plural_name'];
        }

        // If rank found, setup its tags
        if( absint( $reward['rank_id'] ) !== 0 ) {
            $image = gamipress_get_rank_post_thumbnail( $reward['rank_id'] );
            $title = gamipress_get_post_field( 'post_title', $reward['rank_id'] );
        }

        // Force amount to 1
        $amount = 1;

    }

    // Setup label singular or plural based on amount
    $label = $plural;

    if( is_numeric( $amount ) ) {
        $amount = absint( $amount );
    }

    if( is_int( $amount ) && absint( $amount ) === 1 ) {
        $label = $singular;
    }

    if( is_int( $amount ) ) {
        $thousands_sep = gamipress_time_based_rewards_get_option( 'thousands_separator', ',' );

        $amount = number_format( $amount, 0, '.', $thousands_sep );
    }

    $pattern_replacements = array(
        '{amount}'      =>  $amount,
        '{label}'       =>  $label,
        '{image}'       =>  $image,
        '{title}'       =>  $title,
        '{singular}'    =>  $singular,
        '{plural}'      =>  $plural,
    );

    /**
     * Filter pattern replacements
     *
     * @since 1.0.0
     *
     * @param array     $pattern_replacements
     * @param string    $pattern
     *
     * @return array
     */
    $pattern_replacements = apply_filters( 'gamipress_time_based_rewards_parse_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_time_based_rewards_parse_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Get a string with the desired pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_time_based_rewards_get_pattern_tags_html() {

    $js = 'jQuery(this).parent().parent().find(\'.gamipress-pattern-tags-list\').slideToggle();'
        .'jQuery(this).text( ( jQuery(this).text() === \'Hide\' ? \'Show\' : \'Hide\') );';

    $output = '<a href="javascript:void(0);" onclick="' . $js . '">Show</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-time-based-rewards-pattern-tags-list" style="display: none;">';

    foreach( gamipress_time_based_rewards_get_pattern_tags() as $tag => $description ) {

        if( is_numeric( $tag ) ) {
            $output .= "<li id='{$tag}'>{$description}</li>";
        } else {
            $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

            $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
        }
    }

    $output .= '</ul>';

    return $output;

}

/**
 * Parse popup pattern tags to a given pattern
 *
 * @since  1.0.0
 *
 * @param string    $pattern
 * @param int       $time_based_reward_id
 * @param int       $user_id
 * @param array     $rewards
 *
 * @return string Parsed pattern
 */
function gamipress_time_based_rewards_parse_popup_pattern_tags( $pattern, $time_based_reward_id, $user_id, $rewards ) {

    if( absint( $user_id ) === 0 )
        $user_id = get_current_user_id();

    $user = get_userdata( $user_id );

    $pattern_replacements = array();

    // User
    $pattern_replacements = array_merge( $pattern_replacements, array(
        '{user}'                =>  ( $user ? $user->display_name : '' ),
        '{user_first}'          =>  ( $user ? $user->first_name : '' ),
        '{user_last}'           =>  ( $user ? $user->last_name : '' ),
        '{user_id}'             =>  ( $user ? $user->ID : '' ),
    ) );

    // Time-based reward
    $time_based_reward = gamipress_get_post( $time_based_reward_id );

    $pattern_replacements['{id}']                 = ( $time_based_reward ? $time_based_reward->ID : '' );
    $pattern_replacements['{title}']              = ( $time_based_reward ? $time_based_reward->post_title : '' );
    $pattern_replacements['{url}']                = ( $time_based_reward ? get_the_permalink( $time_based_reward->ID ) : '' );
    $pattern_replacements['{link}']               = ( $time_based_reward ? sprintf( '<a href="%s" title="%s">%s</a>', get_the_permalink( $time_based_reward->ID ), $time_based_reward->post_title, $time_based_reward->post_title ) : '' );
    $pattern_replacements['{excerpt}']            = ( $time_based_reward ? $time_based_reward->post_excerpt : '' );
    $pattern_replacements['{image}']              = ( $time_based_reward ? get_the_post_thumbnail( $time_based_reward->ID ) : '' );

    // Rewards
    $rewards_html = '<ul class="gamipress-time-based-reward-popup-rewards">';

    foreach( $rewards as $reward ) {
        $rewards_html .= '<li class="gamipress-time-based-reward-popup-reward">' . $reward['label_parsed'] . '</li>';
    }

    $rewards_html .= '</ul>';

    $pattern_replacements['{rewards}'] = $rewards_html;

    $pattern_replacements = apply_filters( 'gamipress_time_based_rewards_parse_popup_pattern_replacements', $pattern_replacements, $pattern );

    return apply_filters( 'gamipress_time_based_rewards_parse_popup_pattern', str_replace( array_keys( $pattern_replacements ), $pattern_replacements, $pattern ), $pattern );

}

/**
 * Pop-up Pattern tags
 *
 * @since  1.0.0

 * @return array The registered pattern tags
 */
function gamipress_time_based_rewards_get_popup_pattern_tags() {

    $pattern_tags = array(
        // User
        '{user}'                => __( 'User display name.', 'gamipress-time-based-rewards' ),
        '{user_first}'          => __( 'User first name.', 'gamipress-time-based-rewards' ),
        '{user_last}'           => __( 'User last name.', 'gamipress-time-based-rewards' ),
        '{user_id}'             => __( 'User ID (useful for shortcodes that user ID can be passed as attribute).', 'gamipress-time-based-rewards' ),
        // Time-based reward
        '{id}'                  => __( 'The time-based reward ID (useful for shortcodes that time-based reward ID can be passed as attribute).', 'gamipress-time-based-rewards' ),
        '{title}'               => __( 'The time-based reward title.', 'gamipress-time-based-rewards' ),
        '{url}'                 => __( 'URL to the time-based reward.', 'gamipress-time-based-rewards' ),
        '{link}'                => __( 'Link to the time-based reward.', 'gamipress-time-based-rewards' ),
        '{image}'               => __( 'The time-based reward featured image.', 'gamipress-notifications' ),
        '{excerpt}'             => __( 'The time-based reward excerpt.', 'gamipress-notifications' ),
        '{content}'             => __( 'The time-based reward content.', 'gamipress-notifications' ),
        // Rewards
        '{rewards}'             => __( 'List of rewards user earned.', 'gamipress-time-based-rewards' ),
    );

    return apply_filters( 'gamipress_time_based_rewards_popup_pattern_tags', $pattern_tags );

}

/**
 * Get a string with the desired pattern tags html markup
 *
 * @since  1.0.0
 *
 * @return string Pattern tags html markup
 */
function gamipress_time_based_rewards_get_popup_pattern_tags_html() {

    $js = 'jQuery(this).parent().parent().find(\'.gamipress-pattern-tags-list\').slideToggle();'
        .'jQuery(this).text( ( jQuery(this).text() === \'Hide\' ? \'Show\' : \'Hide\') );';

    $output = '<a href="javascript:void(0);" onclick="' . $js . '">Show</a>';
    $output .= '<ul class="gamipress-pattern-tags-list gamipress-time-based-rewards-popup-pattern-tags-list" style="display: none;">';

    foreach( gamipress_time_based_rewards_get_popup_pattern_tags() as $tag => $description ) {

        if( is_numeric( $tag ) ) {
            $output .= "<li id='{$tag}'>{$description}</li>";
        } else {
            $attr_id = 'tag-' . str_replace( array( '{', '}', '_' ), array( '', '', '-' ), $tag );

            $output .= "<li id='{$attr_id}'><code>{$tag}</code> - {$description}</li>";
        }
    }

    $output .= '</ul>';

    return $output;

}