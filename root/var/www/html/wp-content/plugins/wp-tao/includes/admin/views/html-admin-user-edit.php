
<?php if ( isset( $updated[ 'success' ] ) && true == $updated[ 'success' ] ) : ?>
	<div id="message" class="updated notice is-dismissible">
		<p><strong><?php _e( 'Record updated.', 'wp-tao' ) ?></strong></p>
	</div>
<?php endif; ?>

<?php if ( isset( $updated[ 'email_exists' ] ) && false !== $updated[ 'email_exists' ] ) : ?>
	<div id="message" class="error notice is-dismissible">
		<p><strong><?php printf(__( 'This e-mail address already exists! ( %s )', 'wp-tao'), $updated[ 'email_exists' ] ); ?></strong></p>
	</div>
<?php endif; ?>

<div class="wrap">


    <div id="wptao-user-profile-edit" class="wptao-user-profile-edit wptao-row">

        <div class="wptao-back">
			<a href="<?php echo admin_url( 'admin.php?page=wtbp-wptao-users' ); ?>" >
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php _e( 'Back to Identified', 'wp-tao' ); ?>
			</a>
		</div>

		<div class="wptao-dashboard-head">
			<h2><span class="dashicons dashicons-edit"></span><?php _e( 'Edit record', 'wp-tao' ); ?></h2>
		</div>

		<div class="wptao-update-user-wrap">
			<span class="dashicons dashicons-businessman wptao-dashicon-edit-user"></span>
			<form id="wptao-user-profile-form" action="<?php echo esc_url( self_admin_url( 'admin.php?page=wtbp-wptao-users&action=edit&user=' . $user->user_id ) ); ?>" method="post" novalidate="novalidate">
				<?php wp_nonce_field( 'wptao-update-user-' . $user->user_id ) ?>

				<h3><?php _e( 'Personal info', 'wp-tao' ) ?></h3>

				<table class="form-table">

					<tr class="wptao-user-first-name-wrap">
						<th><label for="user_first_name"><?php _e( 'First name', 'wp-tao' ); ?></label></th>
						<td><input type="text" name="user_first_name" id="user_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" class="regular-text" /> <!--<span class="description"><?php _e( 'User\'s first name.', 'wp-tao' ); ?></span>--></td>
					</tr>

					<tr class="wptao-user-last-name-wrap">
						<th><label for="user_last_name"><?php _e( 'Last name', 'wp-tao' ); ?></label></th>
						<td><input type="text" name="user_last_name" id="user_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" class="regular-text" /></td>
					</tr>
				</table>

				<h3><?php _e( 'Contact info', 'wp-tao' ) ?></h3>

				<table class="form-table">

					<tr class="wptao-user-email-wrap">
						<th><label for="user_email"><?php _e( 'Email', 'wp-tao' ); ?></label></th>
						<td><input type="text" name="user_email" id="user_email" value="<?php echo esc_attr( $user->email ); ?>" class="regular-text" /></td>
					</tr>

					<tr class="wptao-user-phone-wrap">
						<th><label for="user_phone"><?php _e( 'Phone', 'wp-tao' ); ?></label></th>
						<td><input type="text" name="user_phone" id="user_phone" value="<?php echo esc_attr( $user->phone ); ?>" class="regular-text" /></td>
					</tr>

				</table>

				<h3><?php _e( 'Other info', 'wp-tao' ) ?></h3>

				<table class="form-table">

					<tr class="wptao-user-email-wrap">
						<th><label for="user_notes"><?php _e( 'Notes', 'wp-tao' ); ?></label></th>
						<td><textarea name="user_notes" id="user_notes" rows="5" cols="30"><?php echo $user->notes; ?></textarea></td>
					</tr>

				</table>

				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user->user_id ); ?>" />

				<?php submit_button( __( 'Update record', 'wp-tao' ) ); ?>

			</form>
		</div>

    </div>
</div>