<?php
define('CAIA_MENUMOBILE_VERSION', '2.6');

/*
30/08/2021: 
- Bổ sung setting menu mobile trong Caia Setting
- Style lại giao diện mặc định Menu
- Tối ưu lại jquery Menu
08/09/2021: 
- Fix thêm lỗi hiển thị menu
16/05/2022:
- Sửa HTML thẻ click menu lỗi Mobile
- Minify Js
20/7/2022
- Sửa lỗi click mở ra toàn bộ menu cấp 2,3
07/02/2023
- Sửa lại hiển thị nút click menu đẹp và gọn hơn
- Sửa thêm trường hợp lỗi click mở ra toàn bộ menu cấp 2,3
*/




if (! class_exists('ResponsiveMenu')) {

	register_nav_menus(array('mobile-menu' => 'Mobile Menu'));

	class Caia_Menumobile
	{

		function __construct()
		{

			add_action('caia_settings_metaboxes', array($this, 'add_theme_settings_boxes_menumobile'));

			// Load for every device; CSS controls the responsive breakpoint.
				add_action('genesis_after_footer', array($this, 'caia_add_function_menu_mobile_menu'));
				add_action('wp_head', array($this, 'caia_add_style_mobile'));
				add_action('wp_footer', array($this, 'caia_add_js_mobile'));
		}

		function add_theme_settings_boxes_menumobile($pagehook)
		{
			add_meta_box('caia-menumobile', __('Cài Đặt Menu Mobile', 'caia'), array($this, 'options_admin_menumobile'), $this->pagehook, 'main');
		}

		function options_admin_menumobile()
		{
			$cs_field = CAIA_SETTINGS_FIELD;

			$align_menu = caia_get_option('align_menu');
			$color_line = caia_get_option('color_line');
			$top_click = caia_get_option('top_click');
			$top_menu = caia_get_option('top_menu');
			$background_menu = caia_get_option('background_menu');
			$color_menu = caia_get_option('color_menu');

			echo '<table class="form-table">';

			echo '<tr>';
			echo '<th>Vị trí nút Click Menu</th>';
			echo '<td>';
			echo "<input type='radio' name='{$cs_field}[align_menu]' value='left' " . checked('left', $align_menu, false) . '> Bên trái &nbsp;&nbsp;&nbsp;&nbsp;';
			echo "<input type='radio' name='{$cs_field}[align_menu]' value='right' " . checked('right', $align_menu, false) . '> Bên phải';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th>Màu nút Click Menu</th>';
			echo '<td>';
			echo "<input name='{$cs_field}[color_line]' type='text' value='{$color_line}' size='60'>";
			echo '<p class="description">Ví dụ: #333, Mặc định: #fff.</p>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th>Khoảng cách Click Menu tới đầu trang</th>';
			echo '<td>';
			echo "<input name='{$cs_field}[top_click]' type='text' value='{$top_click}' size='60'>";
			echo '<p class="description">Ví dụ: 150px, Mặc định: 0px.</p>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th>Màu nền Menu</th>';
			echo '<td>';
			echo "<input name='{$cs_field}[background_menu]' type='text' value='{$background_menu}' size='60'>";
			echo '<p class="description">Ví dụ: #365899, Mặc định: #333.</p>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th>Màu chữ Menu</th>';
			echo '<td>';
			echo "<input name='{$cs_field}[color_menu]' type='text' value='{$color_menu}' size='60'>";
			echo '<p class="description">Ví dụ: #333, Mặc định: #fff.</p>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th>Khoảng cách Menu tới đầu trang</th>';
			echo '<td>';
			echo "<input name='{$cs_field}[top_menu]' type='text' value='{$top_menu}' size='60'>";
			echo '<p class="description">Ví dụ: 150px, Mặc định: 0px.</p>';
			echo '</td>';
			echo '</tr>';

			echo '</table>';
		}

		function caia_add_function_menu_mobile_menu()
		{
			$menu_locations = get_nav_menu_locations();
			$mobile_menu_id = (int) ( $menu_locations['mobile-menu'] ?? 0 );
			$header_menu_id = 0;
			$sidebars = wp_get_sidebars_widgets();
			$nav_widgets = get_option( 'widget_nav_menu', array() );
			foreach ( $sidebars['header-main'] ?? array() as $widget_id ) {
				if ( preg_match( '/^nav_menu-(\d+)$/', $widget_id, $matches ) && ! empty( $nav_widgets[ $matches[1] ]['nav_menu'] ) ) {
					$header_menu_id = (int) $nav_widgets[ $matches[1] ]['nav_menu'];
					break;
				}
			}

			$menu_id = $mobile_menu_id ?: $header_menu_id;
			if ( $menu_id ) {
				echo '<div id="mobile-menu-overlay" aria-hidden="true"></div>';
				echo '<div id="responsive-menu">';
				echo '<div class="logomobile"></div>';
				do_action('caia_before_mobile-menu');
				wp_nav_menu(array(
					'menu' => $menu_id,
					'menu_id' => 'menu-header-main-mobile',
					'theme_location' => 'mobile-menu',
					'container' => 'div',
					'container_class' => 'mobile-menu',
					'fallback_cb' => false,
				));
				do_action('caia_after_mobile-menu');
				echo '</div>';
				echo '<button type="button" id="click-menu" class="click-menu" aria-label="Menu" aria-controls="responsive-menu" aria-expanded="false">
				
					<span class="line line1"></span>
					<span class="line line2"></span>
					<span class="line line3"></span>
				</button>';
			}
		}

		function caia_add_style_mobile()
		{
			$align_menu = (empty(caia_get_option('align_menu'))) ? 'right' : caia_get_option('align_menu');
			$top_click = (empty(caia_get_option('top_click'))) ? '0px' : caia_get_option('top_click');
			$top_menu = (empty(caia_get_option('top_menu'))) ? '0px' : caia_get_option('top_menu');
			$background_menu = (empty(caia_get_option('background_menu'))) ? '#333' : caia_get_option('background_menu');
			$color_menu = (empty(caia_get_option('color_menu'))) ? '#fff' : caia_get_option('color_menu');
			$color_line = (empty(caia_get_option('color_line'))) ? '#fff' : caia_get_option('color_line');
?>
			<style>
				.click-menu {
					text-align: center;
					position: absolute;
					<?php echo $align_menu; ?>: 5%;
					top: <?php echo $top_click; ?>;
					z-index: 5000;
				}

				.logged-in .click-menu {
					top: calc(<?php echo $top_click; ?> + 46px);
				}

				.click-menu .line {
					height: 3px;
					margin-bottom: 6px;
					background: <?php echo $color_line; ?>;
					width: 30px;
					transition: 0.4s;
				}

				.click-menu.change {
					padding-top: 3px;
				}

				.click-menu.change .line1 {
					-webkit-transform: rotate(-45deg) translate(-9px, 6px);
					transform: rotate(-45deg) translate(-5px, 4px);
				}

				.click-menu.change .line2 {
					opacity: 0;
				}

				.click-menu.change .line3 {
					-webkit-transform: rotate(45deg) translate(-8px, -8px);
					transform: rotate(45deg) translate(-8px, -8px);
				}

				#responsive-menu {
					<?php if ($top_menu == '0px') { ?>position: fixed;
					height: 100%;
					width: 85%;
					<?php } else { ?>position: absolute;
					height: calc(100% - <?php echo $top_menu; ?>);
					width: 100%;
					<?php } ?>top: <?php echo $top_menu; ?>;
					left: auto;
					right: 0;
					background: <?php echo $background_menu; ?>;
					z-index: 999999;
					display: none;
					box-shadow: 0px 4px 10px #d6d6d6;
				}

				.logged-in #responsive-menu {
					<?php if ($top_menu == '0px') { ?>top: 0;
					height: 100%;
					<?php } else { ?>top: calc(<?php echo $top_menu; ?> + 46px);
					height: calc(100% + 46px - <?php echo $top_menu; ?>);
					<?php } ?>
				}

		

				.active-menu {
					color: <?php echo $color_menu; ?>;
					position: absolute;
					right: 10px;
					top: 0;
					width: 44px;
					height: 44px;
					display: flex;
					align-items: center;
					justify-content: center;
					z-index: 10;
					cursor: pointer;
					font-size: 13px;
					line-height: 1;
					user-select: none;
				}

				.active-menu.close {
					display: none;
				}

				#responsive-menu ul li > ul > li:last-child,
#responsive-menu ul li > ul > li:last-of-type {
  border-bottom: none !important;
      padding: 10px 30px 0px 15px !important;
}
/* ===== FORCE STACKED, FULL-WIDTH SUBMENUS ON MOBILE ===== */
.mobile-menu,
.mobile-menu ul,
.mobile-menu li {
  width: 100%;
  box-sizing: border-box;
  list-style: none;
  margin: 0;
  padding: 0;
}

/* Submenu mặc định ẩn */
.mobile-menu ul.sub-menu {
  display: none;
  position: static !important;
  width: 100% !important;
  margin: 0;
  padding: 0;
  box-shadow: none !important;
  border: 0 !important;
  background: none;
}

/* Thụt lề submenu theo cấp */
.mobile-menu ul.sub-menu > li            { padding-left: 20px; }
.mobile-menu ul.sub-menu .sub-menu > li  { padding-left: 35px; }
.mobile-menu ul.sub-menu .sub-menu .sub-menu > li { padding-left: 50px; }

/* Nút toggle */
.mobile-menu li {
  position: relative;
}
.mobile-menu li.menu-item-has-children > a {
  padding-right: 56px !important;
}
.mobile-menu .active-menu {
  position: absolute;
  right: 10px;
  top: 0;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 13px;
  line-height: 1;
  user-select: none;
}

			</style>
		<?php
		}

		function caia_add_js_mobile()
		{
		?>
			<script>
				jQuery(document).ready(function($) {
					var $menuToggle = $("#click-menu");
					var $mobileMenu = $("#responsive-menu");
					var $menuOverlay = $("#mobile-menu-overlay");
					var $menuHost = $(".site-header .header-middle-widgets").first();
					$menuToggle.appendTo($menuHost.length ? $menuHost : $(".site-header > .wrap").first());
					var $hotline = $('.header-menusub-widget-area a[href^="tel:"]').first();
					if ($hotline.length) {
						var phoneHref = $hotline.attr("href").trim();
						var phoneLabel = $hotline.closest(".mainposts").find(".noidung").text().trim() || phoneHref.replace(/^tel:/, "");
						var $mobileCall = $("<a>", { href: phoneHref, "class": "mobile-header-call" });
						$hotline.find("img").first().clone().removeAttr("id").attr("alt", "").appendTo($mobileCall);
						$("<span>").text(phoneLabel).appendTo($mobileCall);
						$mobileCall.appendTo($(".site-header > .wrap").first());
					}

					function syncMobileMenuOffset() {
						var $siteHeader = $(".site-header").first();
						var headerBottom = 64;

						if ($siteHeader.length) {
							headerBottom = Math.round($siteHeader[0].getBoundingClientRect().bottom);
						}

						document.documentElement.style.setProperty("--mobile-menu-offset", Math.max(headerBottom, 0) + "px");
					}

					function setMobileMenu(open) {
						syncMobileMenuOffset();

						$menuToggle.toggleClass("change", open).attr("aria-expanded", open ? "true" : "false");
						$mobileMenu.toggle(open);
						$menuOverlay.toggle(open).attr("aria-hidden", open ? "false" : "true");
						$("body").toggleClass("mobile-menu-open", open);
					}

					$menuToggle.attr({
						"role": "button",
						"aria-label": "Menu",
						"aria-controls": "responsive-menu",
						"aria-expanded": "false"
					});

					$("#click-menu").click(function() {
						setMobileMenu(!$(this).hasClass("change"));
					});

					$menuOverlay.on("click", function() {
						setMobileMenu(false);
					});

					$(document).on("keydown", function(e) {
						if (e.key === "Escape" && $menuToggle.hasClass("change")) {
							setMobileMenu(false);
						}
					});

					syncMobileMenuOffset();

					$(window).on("resize orientationchange", function() {
						if (window.innerWidth > 767) setMobileMenu(false);
						syncMobileMenuOffset();
					});

					$(".mobile-menu li.menu-item-has-children").each(function() {
						var $item = $(this);

						$item.removeClass("open");
						$item.children("ul.sub-menu").hide();

						var $toggles = $item.children(".active-menu.toggle");
						if (!$toggles.length) {
							$item.prepend('<span class="active-menu toggle" role="button" aria-expanded="false">▼</span>');
						} else {
							$toggles.not(":first").remove();
							$toggles.first().text("▼").attr("aria-expanded", "false");
						}
					});

					$(document).on("click", ".mobile-menu .active-menu.toggle", function(e) {
						e.preventDefault();
						e.stopPropagation();

						var $toggle = $(this);
						var $item = $toggle.closest("li");
						var $submenu = $item.children("ul.sub-menu");
						var isOpen = $item.hasClass("open");

						$item.toggleClass("open", !isOpen);
						$toggle.text(isOpen ? "▼" : "▲").attr("aria-expanded", isOpen ? "false" : "true");

						if (isOpen) {
							$submenu.stop(true, true).slideUp(220);
						} else {
							$submenu.stop(true, true).slideDown(220);
						}
					});

					$(document).on("click", ".mobile-menu li.menu-item-has-children > a", function(e) {
						e.preventDefault();
						$(this).closest("li").children(".active-menu.toggle").trigger("click");
					});
				});
			</script>

<?php
		}
	}
	$caia_menumobile = new Caia_Menumobile();
}
