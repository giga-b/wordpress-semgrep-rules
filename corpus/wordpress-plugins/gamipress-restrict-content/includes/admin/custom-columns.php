<?php
/**
 * Custom Columns
 *
 * @package     GamiPress\Restrict_Content\Admin\Custom_Columns
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_restrict_content_load_custom_columns() {

    foreach( gamipress_restrict_content_post_types_slugs() as $post_type ) {
        add_filter( "manage_{$post_type}_posts_columns", 'gamipress_restrict_content_manage_posts_columns' );
        add_action( "manage_{$post_type}_posts_custom_column", 'gamipress_restrict_content_manage_posts_custom_column', 10, 2 );
    }

}
add_action( 'admin_init', 'gamipress_restrict_content_load_custom_columns' );

function gamipress_restrict_content_manage_posts_columns( $posts_columns ) {

    $posts_columns['gamipress_restrict_content'] = __( 'Content Restrictions', 'gamipress-restrict-content' );

    return $posts_columns;
}

function gamipress_restrict_content_manage_posts_custom_column( $column_name, $post_id ) {

    if( $column_name !== 'gamipress_restrict_content' ) {
        return;
    }

    $restricted = gamipress_restrict_content_is_restricted( $post_id );

    if( ! $restricted ) {
        _e( 'Not restricted', 'gamipress-restrict-content' );
        return;
    }

    $restrict_access = gamipress_restrict_content_is_restricted_access( $post_id );
    $restrict_content = gamipress_restrict_content_is_restricted_content( $post_id );
    $restrict_links = gamipress_restrict_content_is_restricted_links( $post_id );
    $restrict_images = gamipress_restrict_content_is_restricted_images( $post_id );

    // Restriction icons
    ?>
    <span title="<?php if( $restrict_access ) : _e( 'Access restricted', 'gamipress-restrict-content' ); else: _e( 'Access not restricted', 'gamipress-restrict-content' ); endif; ?>" class="gamipress-restrict-content-restriction">
        <i class="dashicons dashicons-admin-network"></i>
        <i class="dashicons dashicons-<?php echo ( $restrict_access ) ? 'yes' : 'no' ; ?>"></i>
    </span>

    <span title="<?php if( $restrict_content ) : _e( 'Content restricted', 'gamipress-restrict-content' ); else: _e( 'Content not restricted', 'gamipress-restrict-content' ); endif; ?>" class="gamipress-restrict-content-restriction">
        <i class="dashicons dashicons-align-left"></i>
        <i class="dashicons dashicons-<?php echo ( $restrict_content ) ? 'yes' : 'no' ; ?>"></i>
    </span>

    <span title="<?php if( $restrict_links ) : _e( 'Links restricted', 'gamipress-restrict-content' ); else: _e( 'Links not restricted', 'gamipress-restrict-content' ); endif; ?>" class="gamipress-restrict-content-restriction">
        <i class="dashicons dashicons-admin-links"></i>
        <i class="dashicons dashicons-<?php echo ( $restrict_links ) ? 'yes' : 'no' ; ?>"></i>
    </span>

    <span title="<?php if( $restrict_images ) : _e( 'Images restricted', 'gamipress-restrict-content' ); else: _e( 'Images not restricted', 'gamipress-restrict-content' ); endif; ?>" class="gamipress-restrict-content-restriction">
        <i class="dashicons dashicons-format-image"></i>
        <i class="dashicons dashicons-<?php echo ( $restrict_images ) ? 'yes' : 'no' ; ?>"></i>
    </span>

    <?php // Redirect details
    if( $restrict_access ) : ?>
        <div class="gamipress-restrict-content-redirect-details">
            <?php $redirect_page = gamipress_restrict_content_get_redirect_page( $post_id ); ?>

            <?php _e( 'Redirects to', 'gamipress-restrict-content' ); ?>

            <?php if( $redirect_page === 0 ) : ?>
                <?php _e( 'WordPress error page', 'gamipress-restrict-content' ); ?>
            <?php else : ?>
                <?php _e( sprintf( '<a href="%s">%s</a>', get_edit_post_link( $redirect_page ), get_the_title( $redirect_page ) ) ); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php // Content restriction details
    if( $restrict_content ) : ?>
        <div class="gamipress-restrict-content-content-details">
            <?php $partial_content = gamipress_restrict_content_get_content_replacement( $post_id ); ?>

            <?php if( $partial_content == 'read-more' ) : ?>
                <?php _e( 'Until read more tag', 'gamipress-restrict-content' ); ?>
            <?php elseif( $partial_content == 'excerpt' ) : ?>
                <?php _e( 'Replace content with excerpt', 'gamipress-restrict-content' ); ?>
            <?php elseif( $partial_content == 'content' ) : ?>
                <?php echo sprintf( __( 'Content (%s characters)', 'gamipress-restrict-content' ), gamipress_restrict_content_get_content_length( $post_id ) ); ?>
            <?php endif ?>
        </div>
    <?php endif;

}