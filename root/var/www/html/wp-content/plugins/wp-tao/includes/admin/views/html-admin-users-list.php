
<?php if ( isset( $success ) && !empty( $success ) ) : ?>
	<div id="message" class="updated notice is-dismissible">
		<p><strong><?php echo $success ?></strong></p>
	</div>
<?php endif; ?>

<div class="wrap wptao-users-list-wrap">

	<div class="wptao-dashboard-head">
		<h2><span class="dashicons dashicons-admin-users"></span><?php _e( 'WP TAO Identified', 'wp-tao' ); ?></h2>
	</div>

    <form method="get">

		<?php $wptao_users_list->search_box( __( 'Search Identified', 'wp-tao' ), 'search_id' ); ?>

		<?php $wptao_users_list->display(); ?>

        <input type="hidden" name="page" value="wtbp-wptao-users">
    </form>
</div>