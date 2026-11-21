<?php
/**
 * Plugin Name:       ForgeHub Disable Comments
 * Plugin URI:        https://wpforgehub.com/product/disable-comments/
 * Description:       Turn off comments, pingbacks and trackbacks site-wide or per post type, and hide comment menus, admin bar items and comment feeds.
 * Version:           1.0.3
 * Author:            WPForgeHub
 * Author URI:        https://wpforgehub.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       forgehub-disable-comments
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPFH_DSC_VERSION', '1.0.3' );
define( 'WPFH_DSC_FILE', __FILE__ );
define( 'WPFH_DSC_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPFH_DSC_URL', plugin_dir_url( __FILE__ ) );

/**
 * License gate wrapper (Freemius SDK is bundled only in the Pro build).
 */
if ( ! function_exists( 'wpfh_dsc_fs' ) ) {
	function wpfh_dsc_fs() {
		global $wpfh_dsc_fs;

		if ( ! isset( $wpfh_dsc_fs ) ) {
			$fs_product_id = '45921';
			if ( '0' !== $fs_product_id && file_exists( WPFH_DSC_PATH . 'freemius/start.php' ) ) {
				require_once WPFH_DSC_PATH . 'freemius/start.php';
			}

			if ( '0' !== $fs_product_id && function_exists( 'fs_dynamic_init' ) ) {
				$wpfh_dsc_fs = fs_dynamic_init( array(
					'id'             => $fs_product_id,
					'slug'           => 'forgehub-disable-comments',
					'type'           => 'plugin',
					'public_key'     => 'pk_9fc687b173af241e54a0083cb366d',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => true,
					'menu'           => array(
						'slug'       => 'forgehub-disable-comments',
						'first-path' => 'options-general.php?page=forgehub-disable-comments',
					),
				) );
			} else {
				$wpfh_dsc_fs = new class {
					public function can_use_premium_code() {
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						return (bool) apply_filters( 'fs_can_use_premium_code__forgehub-disable-comments', false );
					}
					public function is__premium_only() { return false; }
					public function get_upgrade_url() {
						return 'https://wpforgehub.com/product/disable-comments/#pricing';
					}
				};
			}
		}

		return $wpfh_dsc_fs;
	}

	wpfh_dsc_fs();
}

/**
 * Default settings.
 *
 * @return array
 */
function wpfh_dsc_defaults() {
	return array(
		'mode'       => 'all',
		'post_types' => array(),
		'hide_ui'    => 1,
		'roles'      => array(),
	);
}

/**
 * Get merged settings.
 *
 * @return array
 */
function wpfh_dsc_get_settings() {
	$opt = get_option( 'wpfh_dsc_settings', array() );
	return wp_parse_args( is_array( $opt ) ? $opt : array(), wpfh_dsc_defaults() );
}

/**
 * Is the given post type covered by the disable rule?
 *
 * @param string $post_type Post type.
 * @return bool
 */
function wpfh_dsc_is_disabled_for( $post_type ) {
	$s = wpfh_dsc_get_settings();
	if ( 'all' === $s['mode'] ) {
		return true;
	}
	return in_array( $post_type, (array) $s['post_types'], true );
}

require_once WPFH_DSC_PATH . 'includes/class-wpfh-dsc-engine.php';
require_once WPFH_DSC_PATH . 'includes/class-wpfh-dsc-admin.php';

add_action( 'plugins_loaded', function () {
	WPFH_DSC_Engine::get_instance();
	WPFH_DSC_Admin::get_instance();

	$pro = WPFH_DSC_PATH . 'includes/pro/class-wpfh-dsc-pro.php';
	if ( file_exists( $pro ) && wpfh_dsc_fs()->can_use_premium_code() ) {
		require_once $pro;
		WPFH_DSC_Pro::get_instance();
	}
}, 20 );
