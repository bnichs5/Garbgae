
<div class="wptao-datepicker">
	<div class="wptao-report-filter-button wptao-date-button" role="button" aria-expanded="true">
		<span class="wptao-date-arrow-down"></span>
		<div class="wptao-date-btn-inner">
			<?php
			$dr = isset( $_GET[ 'dr' ] ) ? $_GET[ 'dr' ] : $args[ 'range' ];
			switch ( $dr ) {
				case 'today':
					_e( 'Today', 'wp-tao' );
					break;
				case 'c_month':
					_e( 'Current month', 'wp-tao' );
					break;
				case 'yesterday':
					_e( 'Yesterday', 'wp-tao' );
					break;
				case 'l_month':
					_e( 'Last month', 'wp-tao' );
					break;
				case 'all':
					_e( 'All time', 'wp-tao' );
					break;
				case '7_days':
					_e( 'Last 7 days', 'wp-tao' );
					break;
				case '30_days':
					_e( 'Last 30 days', 'wp-tao' );
					break;
				case 'custom':
					$start	 = isset( $_GET[ 'ds' ] ) && is_numeric( $_GET[ 'ds' ] ) ? date_i18n( 'd M Y', $_GET[ 'ds' ] ) : '';
					$end	 = isset( $_GET[ 'de' ] ) && is_numeric( $_GET[ 'de' ] ) ? date_i18n( 'd M Y', $_GET[ 'de' ] ) : '';
					echo $start . ' &ndash; ' . $end;
					break;
				default:
			}
			?>
		</div>
	</div>
	<div class="wptao-filter-expanded">
		<div class="wptao-filter-expanded-inner">
			<div>
				<div class="wptao-date-qd-wrapp">
					<div class="wptao-date-exp-label"><?php _e( 'Quick dates', 'wp-tao' ); ?></div>
					<div class="wptao-date-qd">
						<table>
							<colgroup>
								<col><col>
							</colgroup>
							<tbody>
								<tr>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => $args[ 'quick_dates' ][ 'today' ][ 'start_ts' ],
											'de' => $args[ 'quick_dates' ][ 'today' ][ 'end_ts' ],
											'dr' => 'today'
										) );
										?>"><?php printf( __( 'Today: %s', 'wp-tao' ), $args[ 'quick_dates' ][ 'today' ][ 'human_format' ] ); ?>
										</a>
									</td>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => $args[ 'quick_dates' ][ 'current_month' ][ 'start_ts' ],
											'de' => $args[ 'quick_dates' ][ 'current_month' ][ 'end_ts' ],
											'dr' => 'c_month'
										) );
										?>"><?php printf( __( 'Current month: %s', 'wp-tao' ), $args[ 'quick_dates' ][ 'current_month' ][ 'human_format' ] ); ?>
										</a>
									</td>
								</tr>
								<tr>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => $args[ 'quick_dates' ][ 'yesterday' ][ 'start_ts' ],
											'de' => $args[ 'quick_dates' ][ 'yesterday' ][ 'end_ts' ],
											'dr' => 'yesterday'
										) );
										?>"><?php printf( __( 'Yesterday: %s', 'wp-tao' ), $args[ 'quick_dates' ][ 'yesterday' ][ 'human_format' ] ); ?>
										</a>
									</td>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => $args[ 'quick_dates' ][ 'last_month' ][ 'start_ts' ],
											'de' => $args[ 'quick_dates' ][ 'last_month' ][ 'end_ts' ],
											'dr' => 'l_month'
										) );
										?>"><?php printf( __( 'Last month: %s', 'wp-tao' ), $args[ 'quick_dates' ][ 'last_month' ][ 'human_format' ] ); ?>
										</a>
									</td>
								</tr>
								<tr>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => $args[ 'quick_dates' ][ 'last_7_days' ][ 'start_ts' ],
											'de' => $args[ 'quick_dates' ][ 'last_7_days' ][ 'end_ts' ],
											'dr' => '7_days'
										) );
										?>"><?php _e( 'Last 7 days', 'wp-tao' ); ?>
										</a>
									</td>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => WTBP_WPTAO_Admin_Timeline::get_first_event_ts(),
											'de' => $args[ 'quick_dates' ][ 'today' ][ 'end_ts' ],
											'dr' => 'all'
										) );
										?>"><?php _e( 'All time', 'wp-tao' ); ?>
										</a>
									</td>
								</tr>
								<tr>
									<td>
										<a class="wptao-date-qd-link" href="<?php
										echo add_query_arg(
										array(
											'ds' => $args[ 'quick_dates' ][ 'last_30_days' ][ 'start_ts' ],
											'de' => $args[ 'quick_dates' ][ 'last_30_days' ][ 'end_ts' ],
											'dr' => '30_days'
										) );
										?>"><?php _e( 'Last 30 days', 'wp-tao' ); ?>
										</a>
									</td>
									<td>&nbsp;</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="wptao-date-range-wrapp">
					<div class="wptao-date-exp-label"><?php _e( 'Date Range', 'wp-tao' ); ?></div>
					<div class="wptao-date-range">
						<input name="<?php echo $args[ 'start_date_name' ]; ?>" value="<?php echo isset( $_GET[ 'ds' ] ) && is_numeric( $_GET[ 'ds' ] ) ? date_i18n( 'Y-m-d', $_GET[ 'ds' ] ) : ''; ?>" placeholder="<?php echo date( 'Y-m-d', $args[ 'start_ts' ] ); ?>" type="text" class="wptao-date-input wptao-date-range-box">
						<span> &ndash; </span>
						<input name="<?php echo $args[ 'end_date_name' ]; ?>" value="<?php echo isset( $_GET[ 'de' ] ) && is_numeric( $_GET[ 'de' ] ) ? date_i18n( 'Y-m-d', $_GET[ 'de' ] ) : ''; ?>" placeholder="<?php echo date( 'Y-m-d', $args[ 'end_ts' ] ); ?>" type="text" class="wptao-date-input wptao-date-range-box">
						
					</div>
					<div class="wptao-date-exp-btns"  aria-hidden="true">
						<button class="wptao-date-exp-btn button button-primary wptao-date-exp-btn-apply" name="wptao_report_filter_custom_sumbit" role="button" type="submit">
							<?php _e( 'Apply', 'wp-tao' ); ?>
						</button>
						<button class="wptao-date-exp-btn button wptao-date-exp-btn-cancel" role="button" type="button">
							<?php _e( 'Cancel', 'wp-tao' ); ?>
						</button>

					</div>
					
					<input type="hidden" name="dr" value="<?php echo isset( $_GET[ 'dr' ] ) ? sanitize_title( $_GET[ 'dr' ] ) : 'custom'; ?>" />
					
				</div>
			</div>


		</div>

	</div>

</div>