<?php
	add_action( 'wp_ajax_do_thing', function () {
		$val = $_POST['val'];
		update_option( 'do_thing', $val );
	} );

