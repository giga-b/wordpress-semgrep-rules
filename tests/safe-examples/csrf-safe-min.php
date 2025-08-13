<?php
	add_action( 'wp_ajax_do_thing', function () {
		check_ajax_referer( 'do_thing', 'nonce' );
		$val = sanitize_text_field( $_POST['val'] ?? '' );
		update_option( 'do_thing', $val );
	} );

