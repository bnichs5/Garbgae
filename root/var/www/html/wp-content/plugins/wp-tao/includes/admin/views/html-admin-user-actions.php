
<?php if ( isset( $updated[ 'success' ] ) && true == $updated[ 'success' ] ) : ?>
	<div id="message" class="updated notice is-dismissible">
		<p><strong><?php _e( 'Record updated.' ) ?></strong></p>
	</div>
<?php endif; ?>

<?php if ( isset( $updated[ 'email_exists' ] ) && false !== $updated[ 'email_exists' ] ) : ?>
	<div id="message" class="error notice is-dismissible">
		<p><strong><?php printf( __( 'This e-mail address already exists! ( %s )', 'wp-tao' ), $updated[ 'email_exists' ] ); ?></strong></p>
	</div>
<?php endif; ?>

<div class="wrap">


    <div id="wptao-user-profile-actions" class="wptao-user-profile-actions wptao-row">

        <div class="wptao-back">
			<a href="<?php echo admin_url( 'admin.php?page=wtbp-wptao-users' ); ?>" >
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php _e( 'Back to Identified', 'wp-tao' ); ?>
			</a>
		</div>

		<div class="wptao-dashboard-head">
			<h2><span class="dashicons dashicons-trash"></span><?php _e( 'Actions for record', 'wp-tao' ); ?></h2>
		</div>

		<div class="wptao-actions-user-wrap">
			<form id="wptao-user-actions-form" action="<?php echo esc_url( self_admin_url( 'admin.php?page=wtbp-wptao-users&action=actions' ) ); ?>" method="post" novalidate="novalidate">
				<?php wp_nonce_field( 'wptao-actions-user-' . $user->user_id ) ?>

				<h3><?php _e( 'Info', 'wp-tao' ) ?></h3>

				<table class="form-table">

					<?php if ( !empty( $user->email ) ): ?>
						<tr class="wptao-user-actions-info-email-wrap">
							<th scope="row"><?php _e( 'E-mail', 'wp-tao' ); ?></th><td><?php echo esc_attr( $user->email ); ?></td>
						</tr>
					<?php endif; ?>

					<?php if ( !empty( $user->phone ) ): ?>
						<tr class="wptao-user-actions-info-phone-wrap">
							<th scope="row"><?php _e( 'Phone', 'wp-tao' ); ?></th><td><?php echo esc_attr( $user->phone ); ?></td>
						</tr>
					<?php endif; ?>

				</table>				

				<h3><?php _e( 'Actions', 'wp-tao' ) ?></h3>

				<table class="form-table">

					<?php if ( 'blacklist' != $user->status ) { ?>
						<tr class="wptao-user-actions-add-to-blacklist-wrap">
							<th scope="row"><?php _e( 'Add to blacklist', 'wp-tao' ); ?></th>
							<td><label for="add_to_blacklist"><input name="add_to_blacklist" type="checkbox" id="add_to_blacklist" value="false" /> <?php _e( 'Add this record to the blacklist', 'wp-tao' ); ?></label></td>
						</tr>
					<?php } else { ?>
						<tr class="wptao-user-actions-remove-from-blacklist-wrap">
							<th scope="row"><?php _e( 'Remove from blacklist', 'wp-tao' ); ?></th>
							<td><label for="remove_from_blacklist"><input name="remove_from_blacklist" type="checkbox" id="remove_from_blacklist" value="false" /> <?php _e( 'Remove this record from the blacklist', 'wp-tao' ); ?></label></td>
						</tr>
					<?php } ?>

					<tr class="wptao-user-actions-delete-record-wrap">
						<th scope="row"><?php _e( 'Delete record', 'wp-tao' ); ?></th>
						<td><label for="delete_record"><input name="delete_record" type="checkbox" id="delete_record" value="false" /> <?php _e( 'Delete record (events and fingerprints will not be deleted)', 'wp-tao' ); ?></label></td>
					</tr>

					<tr class="wptao-user-actions-delete-events-wrap">
						<th scope="row"><?php _e( 'Delete events', 'wp-tao' ); ?></th>
						<td><label for="delete_events"><input name="delete_events" type="checkbox" id="delete_events" value="false" /> <?php _e( 'Delete all events and fingerprints connected with this record', 'wp-tao' ); ?></label></td>
					</tr>
				</table>

				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user->user_id ); ?>" />

				<?php submit_button( __( 'Perform actions', 'wp-tao' ) ); ?>

			</form>
		</div>

    </div>
</div>