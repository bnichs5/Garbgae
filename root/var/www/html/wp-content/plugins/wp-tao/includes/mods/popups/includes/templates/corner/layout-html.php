
<div class="wtbp-247p-popup-wrapp wtbp-247p-style-corner">
	<div class="wtbp-247p-popup">
		<span class="wtbp-247p-popup-close"></span>
		<div class="wtbp-247p-header">
			<?php do_action('wtbp_247p_pre_popup_title', $this->options); ?>
			<h4><?php echo $this->content[ 'title' ]; ?></h4>
		</div>
		<div class="wtbp-247p-content">
			<?php echo $this->content[ 'message' ]; ?>
		</div>

		<?php include WTBP_247P_DIR . 'includes/templates/optin.php'; ?>
	</div>
</div>