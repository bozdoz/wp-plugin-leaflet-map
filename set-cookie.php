<?php 
	/* save location in cookie: expiry 12 days */
	session_start();
    $expiry = time() + 60 * 60 * 24 * 12;
    $address = isset($_GET['address']) ? $_GET['address'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    setcookie($address, $location, $expiry, "/");
?>
