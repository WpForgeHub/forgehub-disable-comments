<?php
/**
 * Settings page.
 *
 * @package ForgeHub_Disable_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPFH_DSC_Admin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WPFH_DSC_FILE ), array( $this, 'action_links' ) );
	}

	public function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=forgehub-disable-comments' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'forgehub-disable-comments' ) . '</a>' );
		return $links;
	}

	public function menu() {
		add_options_page(
			__( 'Disable Comments', 'forgehub-disable-comments' ),
			__( 'Disable Comments', 'forgehub-disable-comments' ),
			'manage_options',
			'forgehub-disable-comments',
			array( $this, 'render' )
		);
	}

	public function register() {
		register_setting( 'wpfh_dsc_group', 'wpfh_dsc_settings', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize' ),
			'default'           => wpfh_dsc_defaults(),
		) );
	}

	public function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$out   = wpfh_dsc_defaults();

		$out['mode']    = ( isset( $input['mode'] ) && 'selected' === $input['mode'] ) ? 'selected' : 'all';
		$out['hide_ui'] = empty( $input['hide_ui'] ) ? 0 : 1;

		$valid = get_post_types( array(), 'names' );
		if ( ! empty( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			foreach ( $input['post_types'] as $pt ) {
				$pt = sanitize_key( $pt );
				if ( isset( $valid[ $pt ] ) ) {
					$out['post_types'][] = $pt;
				}
			}
		}

		if ( ! empty( $input['roles'] ) && is_array( $input['roles'] ) && wpfh_dsc_fs()->can_use_premium_code() ) {
			$roles = wp_roles()->get_names();
			foreach ( $input['roles'] as $r ) {
				$r = sanitize_key( $r );
				if ( isset( $roles[ $r ] ) ) {
					$out['roles'][] = $r;
				}
			}
		}
		return $out;
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s      = wpfh_dsc_get_settings();
		$types  = get_post_types( array( 'public' => true ), 'objects' );
		$is_pro = wpfh_dsc_fs()->can_use_premium_code();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ForgeHub Disable Comments', 'forgehub-disable-comments' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'wpfh_dsc_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Scope', 'forgehub-disable-comments' ); ?></th>
						<td>
							<label><input type="radio" name="wpfh_dsc_settings[mode]" value="all" <?php checked( $s['mode'], 'all' ); ?>> <?php esc_html_e( 'Disable everywhere (all post types)', 'forgehub-disable-comments' ); ?></label><br>
							<label><input type="radio" name="wpfh_dsc_settings[mode]" value="selected" <?php checked( $s['mode'], 'selected' ); ?>> <?php esc_html_e( 'Only on selected post types', 'forgehub-disable-comments' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Post types', 'forgehub-disable-comments' ); ?></th>
						<td>
							<?php foreach ( $types as $pt ) : ?>
								<label style="display:block"><input type="checkbox" name="wpfh_dsc_settings[post_types][]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $s['post_types'], true ) ); ?>> <?php echo esc_html( $pt->labels->name ); ?></label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Hide interface', 'forgehub-disable-comments' ); ?></th>
						<td><label><input type="checkbox" name="wpfh_dsc_settings[hide_ui]" value="1" <?php checked( ! empty( $s['hide_ui'] ) ); ?>> <?php esc_html_e( 'Hide Comments menu, admin bar item and comments feed when disabled everywhere', 'forgehub-disable-comments' ); ?></label></td>
					</tr>
					<?php if ( $is_pro ) : ?>
						<?php do_action( 'wpfh_dsc_pro_settings_rows', $s ); ?>
					<?php else : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Pro', 'forgehub-disable-comments' ); ?></th>
						<td><p class="description"><?php esc_html_e( 'Bulk delete existing comments and role based rules are available in Pro.', 'forgehub-disable-comments' ); ?></p></td>
					</tr>
					<?php endif; ?>
				</table>
				<?php submit_button(); ?>
			</form>
			<?php
			if ( $is_pro ) {
				do_action( 'wpfh_dsc_pro_after_form' );
			}
			?>
		</div>
		<?php
	}
}
