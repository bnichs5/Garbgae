
<?php global $wptao_settings; ?>

<div class="wptao-report-summary wptao-row">
	<div class="wptao-report-summary-item wptao-report-summary-col4">
		<h4><?php _e( 'Churn rate', 'wp-tao' ); ?></h4>
		<span class="wptao-report-summary-value"><?php echo isset($this->churn['churn_rate']) ? absint( $this->churn['churn_rate'] ) : ''; ?>%</span>
	</div>
	<div class="wptao-report-summary-item wptao-report-summary-col4">
		<h4><?php _e( 'Customers with a single order', 'wp-tao' ); ?></h4>
		<span class="wptao-report-summary-value"><?php echo isset($this->churn['new']) ? absint( $this->churn['new'] ) : ''; ?></span>
	</div>
	<div class="wptao-report-summary-item wptao-report-summary-col4">
		<h4><?php _e( 'Customers with multiple orders', 'wp-tao' ); ?></h4>
		<span class="wptao-report-summary-value"><?php echo isset($this->churn['returning']) ? absint( $this->churn['returning'] ) : ''; ?></span>
	</div>
</div>

<div class="wptao-report-content wptao-row">

	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'User', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php printf( __( 'Amount [%s]', 'wp-tao' ), WTBP_WPTAO_Helpers::get_currency_symbol( $wptao_settings[ 'currency' ] ) ) ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Orders', 'wp-tao' ) ?></th>
			</tr>
		</thead>

		<tbody id="the-list">
			<?php if ( !empty( $this->data ) ): ?>

				<?php
				$i = 1;
				foreach ( $this->data as $v ):
					?>
					<tr>
						<td class="wptao-report-column-primary"><?php echo $i; ?></td>
						<td data-colname="<?php _e( 'User', 'wp-tao' ) ?>"><b><?php
								if ( !empty( $v[ 'user_id' ] ) ) {
									$user_info = TAO()->user_profile->user_info( $v[ 'user_id' ] );
									echo '<a href="' . add_query_arg( 'a', 'payment', TAO()->user_profile->profile_url ) . '">' . $user_info->display_name . '</a>';
								} else {
									_e( 'Unidentified users', 'wp-tao' );
								}
								?></b></td>
						<td data-colname="<?php printf( __( 'Amount [%s]', 'wp-tao' ), WTBP_WPTAO_Helpers::get_currency_symbol( $wptao_settings[ 'currency' ] ) ); ?>" class="wptao-cell-number"><?php echo WTBP_WPTAO_Helpers::amount_format( $v[ 'amount' ], '' ); ?></td>
						<td data-colname="<?php _e( 'Orders', 'wp-tao' ) ?>" class="wptao-cell-number"><?php echo absint( $v[ 'orders' ] ); ?></td>
					</tr>
					<?php
					$i++;
				endforeach;
				?>

			<?php else: ?>
				<tr class="" id="">
					<td colspan="4"><?php _e( 'No results!', 'wp-tao' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>
				<th class="manage-column wptao-column-no column-primary" scope="col">#</th>
				<th class="manage-column wptao-column-date" scope="col"><?php _e( 'User', 'wp-tao' ) ?></th>
				<th class="manage-column" scope="col"><?php printf( __( 'Amount [%s]', 'wp-tao' ), WTBP_WPTAO_Helpers::get_currency_symbol( $wptao_settings[ 'currency' ] ) ); ?></th>
				<th class="manage-column" scope="col"><?php _e( 'Orders', 'wp-tao' ); ?></th>
			</tr>
		</tfoot>

	</table>


</div>