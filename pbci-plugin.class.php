<?php
/*
** Copyright 2010-2014, Pye Brook Company, Inc.
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

if ( ! class_exists( 'pbciPluginV2' ) ) {
	class pbciPluginV2 {

		private $_plugin_slug;
		private $_plugin_file;
		private $_plugster = null;
		private $_logster = null;
		private $_logging_enabled = false;
		private $_license_code = '';
		private $_settings_page_url = '';
		private $_plugin_data = null;
		private $_plugin_name = '';

		public function __construct() {
		}

		function get_plugin_version() {
			$version = '';
			$plugin_data = $this->get_plugin_data();

			if ( ! empty( $plugin_data['Version'] ) ) {
				$version = $plugin_data['Version'];
			}

			return $version;
		}

		function get_plugin_data() {
			if ( null == $this->_plugin_data ) {
				$default_headers = array(
					'Name'        => 'Plugin Name',
					'PluginURI'   => 'Plugin URI',
					'Version'     => 'Version',
					'Description' => 'Description',
					'Author'      => 'Author',
					'AuthorURI'   => 'Author URI',
					'TextDomain'  => 'Text Domain',
					'DomainPath'  => 'Domain Path',
					'Network'     => 'Network',
					// Site Wide Only is deprecated in favor of Network.
					'_sitewide'   => 'Site Wide Only',
				);

				$this->plugin_data = get_file_data( $this->_plugin_file, $default_headers, 'plugin' );

				foreach ( $this->plugin_data as $key => $value ) {
					if ( empty( $value ) ) {
						unset( $this->plugin_data[ $key ] );
					}
				}
			}

			return $this->plugin_data;

		}

		function get_plugin_information( $plugin_information_array ) {

			$my_info = array( );

			$my_info = $this->get_plugin_data();

			$my_info[ 'plugin_slug' ]  = $this->_plugin_slug;
			$my_info[ 'plugin_file' ]  = $this->_plugin_file;
			$my_info[ 'license_code' ] = $this->license_code();

			$my_info[ 'WordPress Version' ] =  get_bloginfo( 'version' );
			$my_info[ 'WP-eCommerce Version' ] = WPSC_VERSION;
			$my_info[ 'WP-eCommerce Database Version' ] = WPSC_DB_VERSION;
			$my_info[ 'license_code' ] = $this->license_code();

			$my_info[ 'admin_email' ] =  get_bloginfo( 'admin_email' );
			$my_info[ 'wpurl' ] =  get_bloginfo( 'wpurl' );

			$plugin_information_array[ $my_info['Name'] . ' ' . $my_info['Version'] ] = $my_info;

			return $plugin_information_array;
		}

		function get_license_code() {
			return 	$this->license_code();
		}

		function set_license_code( $new_code ) {
			return 	$this->license_code( $new_code );
		}

		function set_purchase_id( $purchase_id ) {
			update_option( $this->_plugin_slug . '_purchase_id', $purchase_id );
		}

		function get_purchase_id( ) {
			return get_option( $this->_plugin_slug . '_purchase_id', '' );
		}

		function license_code( $new_code = '' ) {
			if ( ! empty( $new_code ) ) {
				update_option( $this->_plugin_slug . '_key', $new_code );
				$this->_license_code = $new_code;
			}

			if ( empty( $this->_license_code ) ) {
				$this->_license_code = get_option( $this->_plugin_slug . '_key', '' );
			}

			return $this->_license_code;
		}

		public function get_plugin_slug() {
			return $this->_plugin_slug;
		}

		public function init( $file ) {

			$this->_plugin_file = $file;
			$this->_plugin_slug = basename( dirname( $file ) );

			if ( is_admin() && class_exists( 'PBCIAutoUpdate' ) ) {
				$this->_plugster = new PBCIAutoUpdate( $file );
			} else {
				$this->_plugster = false;
			}

			if ( class_exists( 'pbciLogV2' ) ) {
				if ( method_exists ( 'pbciLogV2', 'get_instance' ) ) {
					$this->_logster = pbciLogV2::get_instance();
				} else {
					// backwards compatibility
					$this->_logster = new pbciLog( $this->_plugin_slug, dirname( $this->_plugin_file ) );
				}
			}

			$plugin = plugin_basename( $this->_plugin_file );

			$this->_settings_page_link = '<a href="options-general.php?page=' . $this->_plugin_slug . '_settings' . '">Settings</a>';

			add_action( $this->_plugin_slug . '_settings', array( &$this, 'register_my_plugin' ), 1, 0 );
			add_action( $this->_plugin_slug . '_settings', array( &$this, 'core_settings' ), 2, 0 );
			add_action( 'admin_menu', array( &$this, 'admin_menus' ) );

			add_filter( 'pbci_get_plugin_information', array( &$this, 'get_plugin_information' ), 10, 1 );
			add_filter( 'pbci_validate_license_key', array( &$this, 'validate_license_key' ), 10, 2 );
			add_filter( 'plugin_action_links_' . $plugin, array( &$this, 'settings_links' ) );

		}

		static function is_license_key_valid( $key ) {
			$valid = false;
			$valid = apply_filters( 'pbci_validate_license_key', $valid, $key );
			return $valid;
		}

		function validate_license_key( $valid, $key ) {

			if ( $key == $this->license_code() ) {
				$valid = true;
			}

			return $valid;
		}

		function plugin_settings_link() {
			return '<a href="options-general.php?page=' . $this->_plugin_slug . '_settings' . '">' . $this->plugin_name() . ' Settings</a>';
		}

		function plugin_data() {
			if ( empty( $this->_plugin_data ) ) {
				$this->_plugin_data = get_plugin_data( $this->_plugin_file, false, false );
			}

			return $this->_plugin_data;
		}

		function plugin_name() {
			if ( empty( $this->_plugin_name ) ) {
				$data               = $this->plugin_data();
				$this->_plugin_name = $data['Name'];
			}

			return $this->_plugin_name;
		}

		function admin_menus() {
			add_submenu_page( null, 'Settings', 'settings', 'manage_options', $this->_plugin_slug . '_settings', array(
				&$this,
				'settings_page'
			) );
		}

		function log( $message ) {
			$this->_logster->log( $message );
		}


		// Add settings link on plugin page
		function settings_links( $links ) {
			array_unshift( $links, $this->_settings_page_link );

			return $links;
		}

		function core_settings() {
			if ( isset( $_POST [ 'settings']['pbci_logging_is_enabled'] ) ) {
				do_action( 'pbci_set_logging_enabled', $_POST [ 'settings']['pbci_logging_is_enabled']  );
			}
			?>
			<table class="widefat support-settings">

				<tr>
					<th colspan="2">
						Support Settings
					</th>
				</tr>

				<tr>
					<td>
						<?php $this->echo_settings_checkbox( 'pbci_logging_is_enabled', false ) ?> Enable Logging
					</td>
					<td>
						Only enable logging if you are trying to diagnose an issue.  Enabling logging makes the log file
						visible at the link shown below. This is useful if you or a person helping you remotely needs to
						see what is happening "under the hood".
					</td>
				</tr>

				<tr>
					<td>
						Log File Link:
					</td>
					<td>
						<?php if ( ! empty( $this->_logster ) ) {
							echo '<a href="' . $this->_logster->get_log_file_url() . '">' . $this->_logster->get_log_file_url() . '</a>';
						} ?>
					</td>
				</tr>

				<tr>
					<td>
						WordPress Version:
					</td>
					<td>
						<?php echo get_bloginfo( 'version' );?>
					</td>
				</tr>

				<tr>
					<td>
						WP-eCommerce Version:
					</td>
					<td>
						<?php echo WPSC_VERSION;?>
					</td>
				</tr>

				<tr>
					<td>
						WP-eCommerce Database Version:
					</td>
					<td>
						<?php echo WPSC_DB_VERSION;?>
					</td>
				</tr>


				<?php $this->echo_save_settings_row(); ?>

			</table>
		<?php

		}

		function echo_save_settings_row() {
			?>
			<tr>
				<td colspan="0">
				<?php echo submit_button( 'Save', 'primary', 'save-settings', false ); ?>
				</td>
			</tr>
			<?php
		}

		function settings_page() {

			if ( isset( $_POST['save-settings'] ) && isset( $_POST['settings'] ) ) {
				$this->save_settings( $_POST['settings'] );
			}

			?>
			<div class="wrap">
			<h2><?php echo $this->settings_title(); ?></h2>
			<?php

			do_action( 'before_' . $this->_plugin_slug . '_settings' );

			ob_start();
			$this->collect_settings();
			$this->settings();
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				?>
				<form method="post">
					<div style="clear:both;">
						<?php echo $buffer; ?>
					</div>
				</form>

			<?php
			}

			?></div><?php
			do_action( 'after_' . $this->_plugin_slug . '_settings' );

		}

		function collect_settings() {
			ob_start();
			do_action( $this->_plugin_slug . '_settings' );
			$contents = ob_get_clean();
			echo $contents;
		}

		function settings_title() {
			return $this->plugin_name() . ' Settings';
		}

		function are_we_testing() {
			$we_are_testing = false;

			if ( false !== strpos( $_SERVER['HTTP_HOST'], '.local' ) ) {
				$we_are_testing = true;
			}

			if ( false !== strpos( $_SERVER['SERVER_ADDR'], '192.168.1.' ) ) {
				$we_are_testing = true;
			}

			if ( false !== strpos( $_SERVER['SERVER_ADDR'], '127.0.0.1' ) ) {
				$we_are_testing = true;
			}

			return $we_are_testing;
		}

		private function get_update_path() {
			if ( $this->are_we_testing() ) {
				$this->update_path = 'http://' . 'pyebrook.local' . '/wp-admin/admin-ajax.php';
			} else {
				$this->update_path = 'http://' . get_option( 'pbci_update_domain', 'www.pyebrook.com' ) . '/wp-admin/admin-ajax.php';
			}

			return $this->update_path;
		}

		public function register_this_plugin() {

			$request = $this->get_request_required_attributes( 'pbci_register_purchase' );

			$request['purchase_id'] = empty( $_REQUEST['pbci_purchase_id'] )  ? '' : absint( $_REQUEST['pbci_purchase_id'] );

			if ( false && $this->are_we_testing() ) {
				$cookies = array();
				$cookies[] = new WP_Http_Cookie( array( 'name' => 'XDEBUG_SESSION', 'value' => 'PHPSTORM' ) );
			} else {
				$cookies = array();
			}

			$url       = $this->get_update_path();
			$response  = wp_remote_post( $url, array( 'body' => $request, 'cookies' => $cookies, ) );

			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$response = maybe_unserialize( $response ['body'] );
				if ( isset( $response['message'] ) ) {
					pbci_admin_nag( $response['message'] . '<br>' . $this->plugin_settings_link() );
				}

				return $response;
			}

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				pbci_admin_nag( "Something went wrong: $error_message" );
				error_log( "Something went wrong: $error_message" );
			} else {
				echo 'Response:<pre>';
				$this->log( var_export( $response, true ) );
				echo '</pre>';
			}

			return false;
		}

		/**
		 * Add our self-hosted check for update results to the filter transient
		 *
		 * @param
		 *            $transient
		 *
		 * @return object $ transient
		 */
		public function is_plugin_update_available() {

			// Get the remote version
			$information = $this->get_plugin_information_from_repository();

			// If a newer version is available, add the update
			if ( $information !== false && is_object( $information ) ) {

				if ( property_exists( $information, 'version' ) ) {
					$remote_version = $information->version;
				} else {
					$remote_version = '';
				}

				$current_version = $this->get_plugin_version();
				if ( version_compare( $current_version, $remote_version, '<' ) ) {
					$obj = new stdClass();

					$obj->name        = ' hello i am here';
					$obj->slug        = $this->plugin_basename;
					$obj->new_version = $remote_version;
					$obj->url         = $this->get_update_path();
					$obj->package     = $this->update_path;

					// Get the upgrade notice for the new plugin version.
					if ( isset( $information->upgrade_notice ) ) {
						$obj->upgrade_notice = $information->upgrade_notice;
					} else {
						$obj->upgrade_notice = '';
					}

					return $obj;
				}
			}

			return false;
		}

		private function get_request_required_attributes( $action = '' ) {
			$required = array();

			if ( ! empty( $action ) ) {
				$required['action'] = $action;
			}

			$required ['slug']    = $this->get_plugin_slug();
			$required ['key']     = $this->get_license_code();
			$required ['version'] = $this->get_plugin_version();

			$required ['site_name']        = get_bloginfo( 'name' );
			$required ['site_admin_email'] = get_bloginfo( 'admin_email' );
			$required ['site_wp_version']  = get_bloginfo( 'version' );
			$required ['site_url']         = parse_url( site_url(), PHP_URL_HOST );

			$required ['unique_client_key'] = wp_hash_password( $required ['site_url'] . $required ['slug']  );

			return $required;
		}

		/**
		 * Get information about the remote version
		 *
		 * @return bool object
		 */
		public function get_plugin_information_from_repository() {

			$request = $this->get_request_required_attributes( 'pbci_get_plugin_info' );

			$cookies = array();

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$cookies[]       = new WP_Http_Cookie( array( 'name' => 'XDEBUG_SESSION', 'value' => 'PHPSTORM' ) );
			}

			$response = wp_remote_post(
				$this->get_update_path(),
				array(
					'body'     => $request,
					'timeout'  => 15,
					'cookies' => $cookies,
					'blocking' => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				pbci_log( $response->get_error_message() );
			} elseif ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$info = unserialize( $response ['body'] );
				if ( $info !== false ) {
					$this->update_path = $info->download_link;
					return $info;
				}
			}

			$obj              = new stdClass();
			$obj->slug        = $request ['slug'];
			$obj->plugin_name = $request ['slug'] . ' .php';
			$obj->tested      = get_bloginfo( 'version' );
			$obj->sections    = array( 'Update Error' => 'There was a problem getting update information from the server.' );

			return $obj;
		}



		function save_settings( $settings ) {
			update_option( $this->_plugin_slug . '_settings', $settings );
		}

		function get_setting_form_name( $setting_name ) {
			return 'settings[' . $setting_name . ']';
		}

		function get_setting_form_id( $setting_name ) {
			return sanitize_title( $setting_name );
		}

		function get_setting( $setting_name ) {
			$settings = get_option( $this->_plugin_slug . '_settings', array() );

			if ( isset( $settings[ $setting_name ] ) ) {
				$value = $settings[ $setting_name ];
			} else {
				$value = '';
			}

			return $value;
		}


		function register_my_plugin() {

			if ( isset( $_REQUEST['register'] ) ) {
				$response = $this->register_this_plugin();
				if ( $response ) {

					if ( isset( $response['license_key'] ) ) {
						$this->set_license_code( $response['license_key'] );
					}

					if ( isset( $response['purchase_id'] ) ) {
						$this->set_purchase_id( $response['purchase_id'] );
					}
				}
			}

			if ( isset( $_REQUEST['check-for-update'] ) ) {
				$update_is_available = $this->is_plugin_update_available();
				if ( $update_is_available ) {
					$this->log( ' an update is available ' );
				}
			}


			$key = $this->license_code();
			?>
			<style>
				table.widefat tr:first-child th {
					background-color: darkgray;
					color: white;
					font-weight: bold;
				}

				table.widefat tr td:first-child {
					width: 33%;
				}

				table.widefat td:first-child {
					font-weight: bold;
				}

				table.widefat {
					margin-bottom: 1.5em;
				}
			</style>

			<form method="post">

			<table class="widefat register">

				<tr>
					<th colspan="2">
						<?php if ( empty( $key ) ) { ?>
							Register your plugin to make sure you are notified whenever there is an update available and
							to enable support.
						<?php } else { ?>
							Your plugin is registered. Please contact us if you have any questions or ideas for new features.
						<?php } ?>
					</th>
				</tr>

				<tr>
					<td>
						Site URL:
					</td>
					<td>
						<?php echo site_url(); ?>
					</td>
				</tr>

				<tr>
					<td>
						Administrator eMail:
					</td>
					<td>
						<?php echo get_bloginfo( 'admin_email' ); ?>
					</td>
				</tr>

				<tr>
					<td>
						Your license code:
					</td>
					<td>
						<?php
						if ( empty( $key ) ) {
							pbci_admin_nag( "Please register your plugin." );
							echo '<span style="font-weight: bold; color: red">Not Registered</span>';
						} else {
							echo $key;
						}
						?>
					</td>
				</tr>

				<?php if ( empty( $key ) ) { ?>
					<tr>
						<td>
								Purchase ID:
						</td>
						<td>
							<input type="number" name="pbci_purchase_id" placeholder="purchase id">
						</td>
					</tr>

					<tr>
						<td colspan="2">
							<?php submit_button( 'Register', 'primary', 'register', false ); ?>
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<td>
							Purchase ID:
						</td>
						<td>
							<?php echo $this->get_purchase_id(); ?>
						</td>
					</tr>

					<tr>
						<td colspan="2">
							For support, or to get any of our other WP-eCommerce plugins, please visit <a
								href="http://www.pyebrook.com">http://www.pyebrook.com</a>.<br>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php submit_button( 'Check For Update', 'primary', 'check-for-update', false ); ?>
						</td>
					</tr>
				<?php } ?>

			</table>

			</form>


		<?php
		}

		function echo_settings_checkbox( $option_name, $default_is_checked = true ) {

			$option_value = get_option( $option_name, $default_is_checked ? '1' : '0' );

			?>
			<input type="hidden"
			       id="<?php echo $this->get_setting_form_id( $option_name ); ?>"
			       name="<?php echo $this->get_setting_form_name( $option_name ); ?>"
			       value="0">

			<input type="checkbox"
				<?php checked( $option_value ); ?>
				   id="<?php echo $this->get_setting_form_id( $option_name ); ?>"
				   name="<?php echo $this->get_setting_form_name( $option_name ); ?>"
				   value="1">

			<?php

		}

	}
} else {
	if ( function_exists( 'pbci_log' ) ) {
		pbci_log( 'pbci-plugin class already exists, skipping load in ' . __FILE__ );
	}
}