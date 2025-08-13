<?php
	// XSS vulnerable: direct echo of user input
	$q = $_GET['q'];
	echo "<div>" . $q . "</div>";

