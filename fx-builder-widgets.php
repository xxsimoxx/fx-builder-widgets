<?php
/**
 * Plugin Name:  FX Builder widgets
 * Description:  Display FX Builder pages as widget
 * Version:      1.0.0
 * License:      GPL2
 * Author:       Simone Fioravanti
 * Author URI:   https://www.simonefioravanti.it
 * Requires PHP: 7.4
 * Requires CP:  2.4
 * Text Domain:  fx-builder-widgets
 * Domain Path:  /languages
 */

namespace xxsimoxx\FXBuilderWidgets;

if (!file_exists(WP_PLUGIN_DIR.'/fx-builder/includes/builder/class-functions.php')
	|| !is_plugin_active('fx-builder/fx-builder.php')
) {
	add_action( 'admin_notices', function () {
		global $pagenow;
		if ($pagenow !== 'plugins.php') {
			return;
		}
		wp_admin_notice(
			esc_html__('"FX Builder widgets" plugin is not working because it needs "FX Builder" to be installed and activated.', 'fx-builder-widgets'),
			[
				'id'          => 'fx-builder-widgets-fx-missing',
				'dismissible' => true,
				'type'        => 'warning',
			]
		);
	});
	return;
}

add_action(
	'plugins_loaded',
	function (){
		load_plugin_textdomain('fx-builder-widgets', false, basename(dirname(__FILE__)).'/languages');
	}
);

require_once WP_PLUGIN_DIR.'/fx-builder/includes/builder/class-functions.php';
require_once __DIR__.'/classes/fx-bulider-widgets.class.php';
require_once __DIR__.'/classes/custom-post-type.class.php';

function register_fxbuilder_widget() {
	register_widget('\xxsimoxx\FXBuilderWidgets\FXBuilderWidgets');
}
add_action('widgets_init', '\xxsimoxx\FXBuilderWidgets\register_fxbuilder_widget');

new CustomPostType();
