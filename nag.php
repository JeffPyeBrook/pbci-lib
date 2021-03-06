<?php
/*
** Copyright 2010-2015, Pye Brook Company, Inc.
**
**
** This software is provided under the GNU General Public License, version
** 2 (GPLv2), that covers its  copying, distribution and modification. The 
** GPLv2 license specifically states that it only covers only copying,
** distribution and modification activities. The GPLv2 further states that 
** all other activities are outside of the scope of the GPLv2.
**
** All activities outside the scope of the GPLv2 are covered by the Pye Brook
** Company, Inc. License. Any right not explicitly granted by the GPLv2, and 
** not explicitly granted by the Pye Brook Company, Inc. License are reserved
** by the Pye Brook Company, Inc.
**
** This software is copyrighted and the property of Pye Brook Company, Inc.
**
** Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY 
** WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR 
** A PARTICULAR PURPOSE. 
**
*/


if ( ! class_exists( 'PBCI_Admin_Notifications' ) ) {

	class PBCI_Admin_Notifications {

		protected static $instance = null;
		protected static $option_name = 'pbci_admin_notifications';
		protected static $messages = array();

		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		private static function get_saved_notifications() {
			self::$messages = get_option( self::$option_name, array() );

			if ( ! is_array( self::$messages ) ) {
				self::$messages = array();
			}

			return self::$messages;
		}

		private static function save_notifications() {
			if ( empty( self::$messages ) ) {
				update_option( self::$option_name, self::$messages );
				delete_option( self::$option_name );
				self::$messages = get_option( self::$option_name, array() );
				error_log( 'DUMP2: ' . var_export( self::$messages ) );
			} else {
				update_option( self::$option_name, self::$messages );
			}

			return self::$instance;
		}


		function __construct() {

			if ( ! self::$instance ) {
				self::$instance = $this;

				self::$messages = self::get_saved_notifications();

				if ( is_admin() ) {
					add_action( 'admin_notices', array( $this, 'show_messages' ) );
					add_action( 'wp_ajax_pbci_dismiss_admin_msg', array( $this, 'dismiss_msg' ) );
				}
			}
		}

		/**
		 * add one or more new messages to be shown to the administrator
		 *
		 * @param string|array[string] $new_messages to show to the admin
		 */
		static function new_message( $new_messages ) {

			if ( empty ( $new_messages ) ) {
				return;
			}

			if ( is_string( $new_messages ) ) {
				$new_messages = array( $new_messages );
			}

			$save_admin_messages = false;

			foreach ( $new_messages as $new_message ) {
				// using the hash key is an easy way of preventing duplicate messages
				$id = md5( $new_message );

				error_log( 'NEW NAG: ' . $id . ' => ' . $new_message );

				if ( ! isset( self::$messages[ $id ] ) ) {
					self::$messages[ $id ] = $new_message;
					$save_admin_messages   = true;
				}
			}

			// only save the admin messages if they have been updated
			if ( $save_admin_messages ) {
				self::save_notifications();
			}

			if ( did_action( 'admin_notices' ) ) {
				self::show_messages();
			}
		}

		/**
		 * display admin messages(nags)
		 *
		 * @since 3.8.14.1
		 */
		static function show_messages() {
			if ( ! ( is_admin() && current_user_can( 'administrator' ) ) ) {
				return;
			}

			$messages = self::get_saved_notifications();

			static $script_already_sent = false;
			static $already_displayed = array();

			// first time though this function and we should add the admin nag script to the page
			if ( ! $script_already_sent ) {
				?>
                <script type="text/javascript">
                    // Admin nag handler

                    // make sure jQuery is defined to avoid inspection warnings
                    if (typeof jQuery == 'undefined') {
                        jQuery = function (foo) {
                        }
                    }

                    jQuery(document).ready(function ($) {
                        function pbci_dismiss_admin_msg(id) {
                            $("#pbci-admin-message-" + id).hide();
                            $.ajax({
                                type: "post",
                                dataType: "text",
                                url: "<?php echo admin_url( 'admin-ajax.php', 'relative' );?>",
                                data: {action: "pbci_dismiss_admin_msg", id: id}
                            });
                        }

                        $(".pbci-admin-message-dismiss").click(function (event) {
                            pbci_dismiss_admin_msg(event.target.id);
                            return false;
                        });
                    });
                </script>
				<?php
				$script_already_sent = true;
			}

			foreach ( self::$messages as $id => $message ) {
				if ( in_array( $id, $already_displayed ) ) {
					continue;
				}

				$already_displayed[] = $id;

				error_log( 'SHOW NAG: ' . $id . ' => ' . $message );

				?>
                <div class="updated pbci-admin-message" id="pbci-admin-message-<?php echo esc_attr( $id ); ?>">
                    <div class="message-text">
                        <p>
							<?php echo $message; ?>
                        </p>
                    </div>
                    <div class="pbci-admin-message-action" style="width: 100%; text-align: right;">
                        <a class="pbci-admin-message-dismiss"
                           id="<?php echo esc_attr( $id ); ?>"><?php _e( 'Dismiss' ) ?></a>
                    </div>
                </div>
				<?php
			}
		}

		/**
		 * Dismiss an admin message
		 *
		 * @param string|bool $message_id the unique message id to be dismissed
		 */
		static function dismiss_msg( $message_id = false ) {
			if ( ! $message_id ) {
				if ( isset( $_REQUEST['id'] ) ) {
					$message_id = $_REQUEST['id'];
				}
			}

			self::$messages = get_option( self::$option_name, array() );

			if ( isset( self::$messages[ $message_id ] ) ) {
				error_log( 'DISMISS NAG: ' . $message_id . ' => ' . self::$messages[ $message_id ] );
				error_log( var_export( self::$messages, true ) );
				unset( self::$messages[ $message_id ] );
				update_option( self::$option_name, self::$messages );
				$messages = get_option( self::$option_name, array() );
				error_log( 'DUMP2: ' . var_export( $messages, true ) );
			}

			wp_send_json_success( true );
		}
	}

	/**
	 * Show one or more admin notification(s) that must be acknowledged
	 *
	 * @param string|array[string] $messages admin the messages(nags) to show
	 */
	function pbci_admin_nag( $messages ) {
		$pbci_admin_notifications = PBCI_Admin_Notifications::get_instance();
		$pbci_admin_notifications::new_message( $messages );
	}

// If we are showing an admin page we want to show the admin nags
	if ( is_admin() ) {
		$pbci_admin_notifications = PBCI_Admin_Notifications::get_instance();
	}

}