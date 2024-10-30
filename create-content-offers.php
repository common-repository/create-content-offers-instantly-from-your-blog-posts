<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://glennsantos.com
 * @since             1.0.1
 * @package           create_content_offers
 *
 * @wordpress-plugin
 * Plugin Name:       Create Content Offers Instantly From Your Blog Posts
 * Plugin URI:        https://glennsantos.com
 * Description:       Create content offers (aka lead magnets, opt-in bribes, content upgrades, ethical bribes, etc ) from your existing content automatically. Add readers who opt-in and share their emails to your mailing list.
 * Version:           1.0.1
 * Author:            Glenn Santos
 * Author URI:        https://glennsantos.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       create-content-offers
 * Domain Path:       /languages

Create Content Offers Instantly From Your Blog Posts is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Create Content Offers Instantly From Your Blog Posts is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Create Content Offers Instantly From Your Blog Posts. If not, see http://www.gnu.org/licenses/gpl-2.0.txt..
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently pligin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-create-content-offers-activator.php
 */
function activate_create_content_offers() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-create-content-offers-activator.php';
	Create_Content_Offers_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-create-content-offers-deactivator.php
 */
function deactivate_create_content_offers() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-create-content-offers-deactivator.php';
	Create_Content_Offers_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_create_content_offers' );
register_deactivation_hook( __FILE__, 'deactivate_create_content_offers' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-create-content-offers.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_create_content_offers() {

	$plugin = new Create_Content_Offers();
	$plugin->run();

}
run_create_content_offers();
