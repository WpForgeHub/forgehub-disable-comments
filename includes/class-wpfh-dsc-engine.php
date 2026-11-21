<?php
/**
 * Front/back end enforcement.
 *
 * @package ForgeHub_Disable_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPFH_DSC_Engine {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'strip_support' ), 100 );
		add_filter( 'comments_open', array( $this, 'filter_open' ), 20, 2 );
		add_filter( 'pings_open', array( $this, 'filter_open' ), 20, 2 );
		add_filter( 'comments_array', array( $this, 'filter_comments_array' ), 20, 2 );
		add_filter( 'feed_links_show_comments_feed', array( $this, 'filter_comments_feed_link' ) );
		add_action( 'template_redirect', array( $this, 'kill_comment_feed' ), 1 );
		add_action( 'admin_menu', array( $this, 'hide_menu' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'hide_admin_bar' ), 999 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'hide_admin_bar_late' ) );
		add_action( 'admin_init', array( $this, 'block_comments_screen' ) );
		add_filter( 'rest_endpoints', array( $this, 'filter_rest' ) );
		add_filter( 'xmlrpc_methods', array( $this, 'filter_xmlrpc' ) );
		add_action( 'wp_head', array( $this, 'remove_feed_links' ), 1 );
	}

	/**
	 * False when the current user is exempt via Pro role rules.
	 */
	private function active_for_current_user() {
		if ( function_exists( 'wpfh_dsc_pro_user_exempt' ) && wpfh_dsc_pro_user_exempt() ) {
			return false;
		}
		return true;
	}

	private function hide_ui_everywhere() {
		$s = wpfh_dsc_get_settings();
		return ! empty( $s['hide_ui'] ) && 'all' === $s['mode'] && $this->active_for_current_user();
	}

	public function strip_support() {
		if ( ! $this->active_for_current_user() ) {
			return;
		}
		foreach ( get_post_types( array(), 'names' ) as $type ) {
			if ( wpfh_dsc_is_disabled_for( $type ) ) {
				remove_post_type_support( $type, 'comments' );
				remove_post_type_support( $type, 'trackbacks' );
			}
		}
	}

	public function filter_open( $open, $post_id ) {
		if ( ! $this->active_for_current_user() ) {
			return $open;
		}
		$type = get_post_type( $post_id );
		if ( $type && wpfh_dsc_is_disabled_for( $type ) ) {
			return false;
		}
		return $open;
	}

	public function filter_comments_array( $comments, $post_id ) {
		if ( ! $this->active_for_current_user() ) {
			return $comments;
		}
		$type = get_post_type( $post_id );
		if ( $type && wpfh_dsc_is_disabled_for( $type ) ) {
			return array();
		}
		return $comments;
	}

	public function remove_feed_links() {
		if ( $this->hide_ui_everywhere() ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}

	public function kill_comment_feed() {
		if ( is_comment_feed() && $this->hide_ui_everywhere() ) {
			wp_die( esc_html__( 'Comments are disabled on this site.', 'forgehub-disable-comments' ), '', array( 'response' => 403 ) );
		}
	}

	public function hide_menu() {
		if ( $this->hide_ui_everywhere() ) {
			remove_menu_page( 'edit-comments.php' );
			remove_submenu_page( 'options-general.php', 'options-discussion.php' );
		}
	}

	public function hide_admin_bar( $wp_admin_bar ) {
		if ( $this->hide_ui_everywhere() ) {
			$wp_admin_bar->remove_node( 'comments' );
		}
	}

	public function hide_admin_bar_late() {
		global $wp_admin_bar;
		if ( $this->hide_ui_everywhere() && is_object( $wp_admin_bar ) ) {
			$wp_admin_bar->remove_menu( 'comments' );
		}
	}

	public function block_comments_screen() {
		global $pagenow;
		if ( 'edit-comments.php' === $pagenow && $this->hide_ui_everywhere() ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}

	public function filter_rest( $endpoints ) {
		if ( 'all' === wpfh_dsc_get_settings()['mode'] && $this->active_for_current_user() ) {
			unset( $endpoints['/wp/v2/comments'], $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}

	public function filter_comments_feed_link( $show ) {
		if ( $this->hide_ui_everywhere() ) {
			return false;
		}
		return $show;
	}

	public function filter_xmlrpc( $methods ) {
		if ( 'all' === wpfh_dsc_get_settings()['mode'] && $this->active_for_current_user() ) {
			unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
		}
		return $methods;
	}
}
