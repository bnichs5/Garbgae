
<?php $this->the_screen_options(); ?>

<div class="wrap">

	<div class="wptao-dashboard-head">
		<h2 class="wptao-ico-before"><?php _e( 'WP TAO Overview. Last 30 days.', 'wp-tao' ); ?></h2>
	</div>

	
	<?php do_action( 'wptao_before_admin_dashboard' ); ?>
	
	<div class="wptao-dashboard-boxes wptao-row" data-token="<?php echo wp_create_nonce( 'wptao-dashboard-order' ); ?>">
		<?php $this->the_widgets(); ?>
	</div>


	<?php do_action( 'wptao_show_hints' ); ?>
	
	<?php do_action( 'wptao_after_admin_dashboard' ); ?>
	
</div>
