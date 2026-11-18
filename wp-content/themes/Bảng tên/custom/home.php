<?php

// Thêm nôi dung slider trang chủ
add_action('genesis_after_header','caia_add_content_slider');
function caia_add_content_slider(){

	if( is_active_sidebar( 'content-banner' ) ){
		echo '<div class="content-banner section">';
			dynamic_sidebar( 'Trang chủ - Banner' );
		echo '</div>';
	}


}

if (wp_is_mobile() ){

}