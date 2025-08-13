<?php
	// XSS safe: escaped output
	$q = $_GET['q'];
	echo '<div>' . esc_html( $q ) . '</div>';

