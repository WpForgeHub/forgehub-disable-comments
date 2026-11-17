<?php
/**
 * Pro: bulk delete existing comments and role based rules.
 *
 * @package ForgeHub_Disable_Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Users whose role is listed in "roles" keep comments (exempt from disabling).
 */
function wpfh_dsc_pro_user_exempt() {
	$s = wpfh_dsc_get_settings();
	if ( empty( $s['roles'] ) || ! is_user_logged_in() ) {
		return false;
	}
	$user = wp_get_current_user();
	return (bool) array_intersect( (array) $s['roles'], (array) $user->roles );
}

class WPFH_DSC_Pro {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wpfh_dsc_pro_settings_rows', array( $this, 'role_rows' ) );
		add_action( 'wpfh_dsc_pro_after_form', array( $this, 'bulk_form' ) );
		add_action( 'admin_post_wpfh_dsc_bulk_delete', array( $this, 'handle_bulk_delete' ) );
	}

	public function role_rows( $s ) {
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Role exceptions', 'forgehub-disable-comments' ); ?></th>
			<td>
				<?php foreach ( wp_roles()->get_names() as $key => $name ) : ?>
					<label style="display:block"><input type="checkbox" name="wpfh_dsc_settings[roles][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, (array) $s['roles'], true ) ); ?>> <?php echo esc_html( translate_user_role( $name ) ); ?></label>
				<?php endforeach; ?>
				<p class="description"><?php esc_html_e( 'Checked roles keep comments enabled.', 'forgehub-disable-comments' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public function bulk_form() {
		?>
		<hr>
		<h2><?php esc_html_e( 'Delete existing comments', 'forgehub-disable-comments' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wpfh_dsc_bulk_delete">
			<?php wp_nonce_field( 'wpfh_dsc_bulk_delete', 'wpfh_dsc_nonce' ); ?>
			<p><?php esc_html_e( 'Permanently deletes comments. This cannot be undone.', 'forgehub-disable-comments' ); ?></p>
			<select name="scope">
				<option value="all"><?php esc_html_e( 'All comments', 'forgehub-disable-comments' ); ?></option>
				<option value="junk"><?php esc_html_e( 'Spam and trash only', 'forgehub-disable-comments' ); ?></option>
			</select>
			<?php submit_button( __( 'Bulk delete comments', 'forgehub-disable-comments' ), 'delete', 'submit', false ); ?>
		</form>
		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['wpfh_dsc_deleted'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$n = absint( wp_unslash( $_GET['wpfh_dsc_deleted'] ) );
			/* translators: %d: number of deleted comments */
			$msg = sprintf( __( '%d comments deleted.', 'forgehub-disable-comments' ), $n );
			echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
		}
	}

	public function handle_bulk_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'forgehub-disable-comments' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'wpfh_dsc_bulk_delete', 'wpfh_dsc_nonce' );

		$scope  = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( $_POST['scope'] ) ) : 'all';
		$status = ( 'junk' === $scope ) ? array( 'spam', 'trash' ) : 'all';
		$count  = 0;

		do {
			$ids = get_comments( array( 'fields' => 'ids', 'number' => 500, 'status' => $status ) );
			foreach ( $ids as $id ) {
				if ( wp_delete_comment( (int) $id, true ) ) {
					++$count;
				}
			}
		} while ( ! empty( $ids ) && $count < 100000 );

		wp_safe_redirect( add_query_arg( 'wpfh_dsc_deleted', $count, admin_url( 'options-general.php?page=forgehub-disable-comments' ) ) );
		exit;
	}
}
