<?php
/** Product-category archives share the shop's filter and product layout. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_tax( 'product_cat' ) ) { return; }

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_catalog_page' );
