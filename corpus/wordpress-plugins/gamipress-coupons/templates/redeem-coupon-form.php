<?php
/**
 * Redeem Coupon Form template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/coupons/redeem-coupon-form.php
 */
global $gamipress_coupons_template_args;

// Shorthand
$a = $gamipress_coupons_template_args;

// Setup vars
$user_id = get_current_user_id(); ?>

<fieldset class="gamipress-coupons-form-wrapper gamipress-coupons-redeem-coupon-form-wrapper">

    <form class="gamipress-coupons-form gamipress-coupons-redeem-coupon-form" action="" method="POST">

        <?php
        /**
         * Before render redeem coupon form
         *
         * @since 1.0.0
         *
         * @param integer     $user_id          User ID
         * @param array       $template_args    Template received arguments
         */
        do_action( 'gamipress_coupons_before_redeem_coupon_form', $user_id, $a ); ?>

        <?php // Code field ?>

        <p id="gamipress-coupons-redeem-coupon-form-code" class="gamipress-coupons-redeem-coupon-form-code-input">

            <?php if( ! empty( $a['label'] ) ) : ?>
                <label for="gamipress-coupons-redeem-coupon-form-code-label"><?php echo $a['label']; ?></label>
            <?php endif; ?>

            <input
                id="gamipress-coupons-redeem-coupon-form-code-input"
                class="gamipress-coupons-redeem-coupon-form-code-input"
                name="code"
                type="text"
                placeholder="<?php echo $a['placeholder']; ?>">

        </p>

        <?php // Setup submit actions ?>

        <p class="gamipress-coupons-form-submit gamipress-coupons-redeem-coupon-form-submit">
            <?php // Loading spinner ?>
            <span class="gamipress-spinner" style="display: none;"></span>
            <input
                id="gamipress-coupons-redeem-coupon-form-submit-button"
                class="gamipress-coupons-form-submit-button gamipress-coupons-redeem-coupon-form-submit-button"
                type="submit"
                value="<?php echo $a['button_text']; ?>">
        </p>

        <?php // Output hidden fields ?>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'gamipress_coupons_redeem_coupon_form' ); ?>">
        <input type="hidden" name="referrer" value="<?php echo get_the_permalink(); ?>">

        <?php
        /**
         * After render coupon form
         *
         * @since 1.0.0
         *
         * @param integer     $user_id          User ID
         * @param array       $template_args    Template received arguments
         */
        do_action( 'gamipress_coupons_after_redeem_coupon_form', $user_id, $a ); ?>

    </form>

</fieldset>
