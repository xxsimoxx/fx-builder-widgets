<?php
/**
 * Plugin Name:  FX Builder widgets
 * Description:  Display FX Builder pages as widget
 * Version:      0.2.0
 * License:      GPL2
 * Author:       Simone Fioravanti
 * Author URI:   https://www.simonefioravanti.it
 * Requires PHP: 7.4
 * Requires CP:  2.0
 * Text Domain:  fx-builder-widgets
 * Domain Path:  /languages
 */

namespace xxsimoxx\FXBuilderWidgets;

if (!file_exists(ABSPATH.'wp-content/plugins/fx-builder/includes/builder/class-functions.php')
	|| !is_plugin_active('fx-builder/fx-builder.php')
) {
	return;
}

add_action(
	'plugins_loaded',
	function (){
		load_plugin_textdomain('fx-builder-widgets', false, basename(dirname(__FILE__)).'/languages');
	}
);

require_once ABSPATH.'wp-content/plugins/fx-builder/includes/builder/class-functions.php';
require_once __DIR__.'/classes/fx-bulider-widgets.class.php';
require_once __DIR__.'/classes/custom-post-type.class.php';

function register_fxbuilder_widget() {
	register_widget('\xxsimoxx\FXBuilderWidgets\FXBuilderWidgets');
}
add_action('widgets_init', '\xxsimoxx\FXBuilderWidgets\register_fxbuilder_widget');

new CustomPostType();
