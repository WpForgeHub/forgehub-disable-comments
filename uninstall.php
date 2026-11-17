<?php
/**
 * Uninstall.
 *
 * @package ForgeHub_Disable_Comments
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wpfh_dsc_settings' );
