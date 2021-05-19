<?php

/**
 * Admin users actions controller.
 *
 * The class handles control users views.
 *
 * @package     WPTAO/Admin/Users
 * @category    Admin
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WTBP_WPTAO_Admin_Users' ) ) :

	/**
	 * WTBP_WPTAO_Admin_Users Class
	 */
	class WTBP_WPTAO_Admin_Users {

		/**
		 * Handles output of the users
		 * 
		 * Shows the users list or profiles
		 */
		public static function output() {

			$user_id = isset( $_GET[ 'user' ] ) ? $_GET[ 'user' ] : '';
			$action	 = isset( $_GET[ 'action' ]) ? $_GET[ 'action' ] : '';
			
			// Process view actions only if isset user_id in URL GET parameters
			if($action === 'actions' && !isset($_GET[ 'user' ])){
				$action = '';
			}
			
			// Action processes
			switch ( $action ) {
				case 'wptao-profile' :

					$result = self::process_profile_page();
					return;

				case 'edit' :

					$result = self::process_edit_user();
					return;

				case 'actions' :

					self::process_actions_user();
					return;
				case 'wptao-unident-profile' :

					$result = self::process_unidentified_user_profile_page();
					return;
				default :
					self::process_users_list();
					return;
			}

			return;
		}

		/**
		 * User profile
		 */
		private static function process_profile_page() {

			$user	 = TAO()->user_profile->user_info();
			$user_id = $user->user_id;

			$timeline = TAO()->user_profile->timeline;

			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-user-profile.php' );
		}

		/**
		 * Edit user
		 */
		private static function process_edit_user() {

			$action	 = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
			$user_id = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );

			switch ( $action ) {

				case 'update':

					check_admin_referer( 'wptao-update-user-' . $user_id );

					if ( !current_user_can( 'manage_options', $user_id ) ) {
						wp_die( __( 'You do not have permission to edit this user.', 'wp-tao' ) );
					}

					$updated = TAO()->user_profile->update_user( $_POST );

					break;

				default:

					break;
			}

			$user = TAO()->user_profile->user_info();
			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-user-edit.php' );
		}

		/**
		 * User actions
		 */
		private static function process_actions_user() {

			$user = TAO()->user_profile->user_info();
			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-user-actions.php' );
		}

		/**
		 *  Unidentified user profile
		 */
		private static function process_unidentified_user_profile_page() {

			$fp = TAO()->unidentified_profile;

			$timeline = TAO()->unidentified_profile->timeline;

			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-unidentified-profile.php' );
		}

		/**
		 * Users lists in the admin panel
		 *
		 * Shows the interface of a users lists
		 */
		public static function process_users_list() {
			global $wptao_users_list;

			$action	 = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
			$user_id = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );

			switch ( $action ) {

				case 'actions':
					
					if(!$user_id){
						break;
					}
					
					$success = '';

					check_admin_referer( 'wptao-actions-user-' . $user_id );

					if ( !current_user_can( 'manage_options' ) ) {
						wp_die( __( 'You do not have permission to edit this user.', 'wp-tao' ) );
					}

					// delete events and fingerprints first (important)
					$delete_events			 = filter_input( INPUT_POST, 'delete_events', FILTER_SANITIZE_STRING );
					$delete_record			 = filter_input( INPUT_POST, 'delete_record', FILTER_SANITIZE_STRING );
					$add_to_blacklist		 = filter_input( INPUT_POST, 'add_to_blacklist', FILTER_SANITIZE_STRING );
					$remove_from_blacklist	 = filter_input( INPUT_POST, 'remove_from_blacklist', FILTER_SANITIZE_STRING );

					if ( false != $delete_events ) {
						TAO()->fingerprints->delete( $user_id, 'user_id' );
						TAO()->events->delete( $user_id, 'user_id' );
						TAO()->events->delete_events_meta();

						$success .= __( 'The events and fingerprints of the record were successfully deleted.', 'wp-tao' ) . '<br />';
					}

					if ( false != $add_to_blacklist ) {
						TAO()->users->update( $user_id, array(
							'status' => 'blacklist'
						) );

						$success .= __( 'The record was successfully added to the blacklist.', 'wp-tao' ) . '<br />';
					} else if ( false != $remove_from_blacklist ) {
						$user = TAO()->users->get( $user_id );

						TAO()->users->update( $user_id, array(
							'status' => TAO()->users->determine_status( $user->email )
						) );

						$success .= __( 'The record was successfully removed from the blacklist.', 'wp-tao' ) . '<br />';
					} else if ( false != $delete_record ) {
						TAO()->users->delete( $user_id );
						TAO()->users->delete_users_meta();

						TAO()->events->update( $user_id, array(
							'user_id' => 0,
						), 'user_id' );

						TAO()->fingerprints->update( $user_id, array(
							'user_id' => 0,
						), 'user_id' );

						$success .= __( 'The record was successfully deleted.', 'wp-tao' ) . '<br />';
					}

					if ( empty( $success ) ) {
						$success = __( 'No action was performed.', 'wp-tao' );
					}

					break;

				default:
					break;
			}

			$wptao_users_list->prepare_items();

			include_once( WTBP_WPTAO_DIR . 'includes/admin/views/html-admin-users-list.php' );
		}

	}

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

endif;