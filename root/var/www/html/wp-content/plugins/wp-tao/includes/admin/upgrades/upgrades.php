<?php
/**
 * Upgrade Screen
 *
 * WPTAO upgrades.php base on:
 * https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/admin/upgrades/upgrades.php
 * 
 * @package     WPTAO/Admin/Upgrade
 * @category    Admin
 * @since       1.0.3
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin menu page callback.
 * Displays the page content for the upgrade menu page
 *
 * @since 1.0.3
 */
// Receive parameters
$action = isset( $_GET[ 'wptao-upgrade' ] ) ? sanitize_text_field( $_GET[ 'wptao-upgrade' ] ) : '';

$doing_upgrade_args = array(
	'page'			 => 'wtbp-wptao-upgrades',
	'wptao-upgrade'	 => $action,
);

if ( !empty( $action ) ) {
	update_option( 'wptao_doing_upgrade', $doing_upgrade_args );
}
?>
<div class="wrap">
	<h2><?php _e( 'WP Tao - Upgrades', 'wp-tao' ); ?></h2>

<?php if ( !empty( $action ) ) : ?>


		<div id="edd-upgrade-status">
			<p>
		<?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'wp-tao' ); ?>
				<img src = "<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" width="15" height="15" />
			</p>
		</div>
		<script type="text/javascript">
			setTimeout( function () {
				document.location.href = "index.php?wtbp_wptao_action=<?php echo $action; ?>";
			}, 250 );
		</script>

<?php else : ?>

		<div id="wptao-upgrade-status">
			<p>
		<?php _e( 'There is nothing to upgrade!', 'wp-tao' ); ?>
			</p>
		</div>
		<script type="text/javascript">
			setTimeout( function () {
				document.location.href = "admin.php?page=wtbp-wptao";
			}, 250 );
		</script>

<?php endif; ?>

</div>
	<?php

