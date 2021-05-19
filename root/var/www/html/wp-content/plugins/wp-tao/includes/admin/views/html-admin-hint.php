
<div class="wptao-hint-box" <?php echo isset( $hint[ 'style' ][ 'color' ] ) ? 'style="border-left-color:' . sanitize_text_field( $hint[ 'style' ][ 'color' ] ) . '"' : ''; ?>>
	<div class="wptao-hint-head wptao-row">
		<div class="wptao-hint-ico dashicons <?php echo isset( $hint[ 'style' ][ 'icon' ] ) ? sanitize_text_field( $hint[ 'style' ][ 'icon' ] ) : 'dashicons-lightbulb'; ?>" <?php echo isset( $hint[ 'style' ][ 'color' ] ) ? 'style="background-color:' . sanitize_text_field( $hint[ 'style' ][ 'color' ] ) . '"' : ''; ?>></div>
		<h4 class="wptao-hint-title"><?php echo $hint[ 'title' ]; ?></h4>
		<div class="wptao-hint-close"></div>
	</div>
	<div class="wptao-hint-content"><?php echo $hint[ 'content' ]; ?></div>
	<div class="wptao-hint-footer">
	</div>
</div>
