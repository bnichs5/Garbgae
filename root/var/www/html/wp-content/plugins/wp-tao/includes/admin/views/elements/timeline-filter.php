
<div class="wptao-filter-bg-ico-wrapp">
	<span class="dashicons wptao-filter-bg-ico-ep wptao-filter-bg-ico dashicons-search"></span>
</div>
<div class="wptao-filter-wrap wptao-filter-events-page" id="wptao-timeline-filter">

	<form id="wptao-timeline-form" method="POST" action="<?php echo esc_url( $this->base_url ); ?>">

		<h2><?php _e( 'Timeline filter', 'wp-tao' ); ?></h2>

		<div class="wptao-module-content">

			<?php do_action( 'wptao-before-timeline-form' ); ?>


			<div class="wptao-event-filter-dates">
				<h4><?php _e( 'Date', 'wp-tao' ); ?></h4>
				<?php
				WTBP_WPTAO_Helpers::datepicker( $args = array(
					'start_ts'	 => WTBP_WPTAO_Admin_Timeline::get_first_event_ts(),
					'range'		 => 'all'
				) );
				?>
			</div>


			<?php if ( isset( $filter[ 'categories' ] ) && is_array( $filter[ 'categories' ] ) && !empty( $filter[ 'categories' ] ) ): ?>
				<h4><?php _e( 'Categories', 'wp-tao' ); ?></h4>
				<div class="metabox-prefs wptao-event-filter-cat">
					<?php foreach ( $filter[ 'categories' ] as $cat_name => $cat_title ): ?>
						<label for="<?php echo sanitize_title( $cat_name ); ?>-cat"><input type="checkbox" <?php echo isset( $_GET[ 'cat' ] ) && in_array( $cat_name, explode( ',', $_GET[ 'cat' ] ) ) ? 'checked="checked"' : ''; ?> value="<?php echo sanitize_title( $cat_name ); ?>" id="<?php echo sanitize_title( $cat_name ); ?>-cat" name="cat[]" class="hide-column-tog"><?php echo esc_attr( $cat_title ); ?></label>
					<?php endforeach; ?>
					<br class="clear">
				</div>
			<?php endif; ?>

			<?php if ( isset( $filter[ 'actions' ] ) && is_array( $filter[ 'actions' ] ) && !empty( $filter[ 'actions' ] ) ): ?>
				<h4><?php _e( 'Actions', 'wp-tao' ); ?></h4>
				<div class="metabox-prefs wptao-event-filter-cat">
					<?php foreach ( $filter[ 'actions' ] as $key => $action ): ?>
						<label for="<?php echo sanitize_title( $action[ 'id' ] ); ?>"><input type="checkbox" <?php echo isset( $_GET[ 'a' ] ) && in_array( $action[ 'id' ], explode( ',', $_GET[ 'a' ] ) ) ? 'checked="checked"' : ''; ?> value="<?php echo sanitize_title( $action[ 'id' ] ); ?>" id="<?php echo sanitize_title( $action[ 'id' ] ); ?>" name="a[]" class="hide-column-tog"><?php echo esc_attr( $action[ 'title' ] ); ?></label>
					<?php endforeach; ?>
					<br class="clear">
				</div>
			<?php endif; ?>

			<?php if ( isset( $filter[ 'tags' ] ) && is_array( $filter[ 'tags' ] ) && !empty( $filter[ 'tags' ] ) ): ?>
				<h4><?php _e( 'Tags', 'wp-tao' ); ?></h4>
				<div class="metabox-prefs wptao-event-filter-tags">
					<?php foreach ( $filter[ 'tags' ] as $tag ): ?>
						<label for="<?php echo sanitize_title( $tag->tag ); ?>"><input type="checkbox" <?php echo isset( $_GET[ 'tags' ] ) && in_array( $tag->id, explode( ',', $_GET[ 'tags' ] ) ) ? 'checked="checked"' : ''; ?> value="<?php echo sanitize_title( $tag->id ); ?>" id="<?php echo sanitize_title( $tag->tag ); ?>" name="tags[]" class="hide-column-tog"><?php echo esc_attr( $tag->tag ); ?></label>
					<?php endforeach; ?>
					<br class="clear">
				</div>
			<?php endif; ?>

			<h4><?php _e( 'Display', 'wp-tao' ); ?></h4>

			<?php if ( TAO()->booleans->is_page_events === true ): ?>
				<div class="metabox-prefs wptao-event-filter-ident">
					<label for="wptao-show-by-ident"><?php _e( 'Identification: ', 'wp-tao' ); ?>
						<select id="wptao-show-by-ident" name="wptao-show-by-ident" >
							<option value="both" <?php echo!isset( $_GET[ 'identified' ] ) || !in_array( $_GET[ 'identified' ], array( '0', '1' ) ) ? 'selected="selected"' : ''; ?>><?php _e( 'Show all', 'wp-tao' ); ?></option>
							<option value="identified" <?php echo isset( $_GET[ 'identified' ] ) && $_GET[ 'identified' ] === '1' ? 'selected="selected"' : ''; ?>><?php _e( 'Identified only', 'wp-tao' ); ?></option>
							<option value="unidentified" <?php echo isset( $_GET[ 'identified' ] ) && $_GET[ 'identified' ] === '0' ? 'selected="selected"' : ''; ?>><?php _e( 'Unidentified only', 'wp-tao' ); ?></option>
						</select>
				</div>	
			<?php endif; ?>

			<label for="edit_post_per_page"><?php _e( 'Events per page:', 'wp-tao' ); ?> <input type="number" name="wptao-events-number" class="small-text" value="<?php echo isset( $_GET[ 'ipp' ] ) && is_numeric( $_GET[ 'ipp' ] ) ? (int) abs( $_GET[ 'ipp' ] ) : esc_attr( TAO()->events->events_per_page ); ?>" /></label>

			<div class="wptao-profile-sort-submit">

				<input type="hidden" value="wptao-event-filter" name="action" />

				<?php wp_nonce_field( 'wptao-event-filter', 'wptao-event-filter-nonce' ) ?>

				<button type="submit" class="button wptao-btn-sort button-primary"><?php _e( 'Sort', 'wp-tao' ); ?></button>

			</div>

			<?php do_action( 'wptao-after-timeline-form' ); ?>

		</div>

	</form>
</div>