<?php
/**
 * WooCommerce product-category archive entry point.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_stylesheet_directory() . '/custom/taxonomy.php';

genesis();
