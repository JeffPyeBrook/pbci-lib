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

if ( ! class_exists( 'pbciPlugin' ) ) {
	class pbciPlugin {

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

		public function init( $file ) {
			error_log( __CLASS__ . '::' . __FUNCTION__ );
			$this->_plugin_file = $file;
			$this->_plugin_slug = basename( dirname( $file ) );
			error_log( $this->_plugin_slug );

			if ( is_admin() && class_exists( 'PBCIAutoUpdate' ) ) {
				$this->_plugster = new PBCIAutoUpdate( $file );
			} else {
				$this->_plugster = false;
			}

			if ( class_exists( 'pbciLog' ) ) {
				$this->_logster = new pbciLog( $this->_plugin_slug, dirname( $this->_plugin_file ) );
			}

			$plugin = plugin_basename( $this->_plugin_file );

			add_filter( 'plugin_action_links_' . $plugin, array( &$this, 'settings_links' ) );

			add_action( 'admin_menu', array( &$this, 'admin_menus' ) );

			add_action( $this->_plugin_slug . '_settings', array( &$this, 'register_my_plugin' ), 1, 0 );


			$this->_settings_page_link = '<a href="options-general.php?page=' . $this->_plugin_slug . '_settings' . '">Settings</a>';
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
			add_options_page( 'settings', 'settings', 'manage_options', $this->_plugin_slug . '_settings', array(
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

		function settings_page() {

			if ( isset( $_POST['save-settings'] ) && isset( $_POST['settings'] ) ) {
				$this->save_settings( $_POST['settings'] );
			}

			?>
			<div class="wrap">
			<h2><?php echo $this->settings_title(); ?></h2>
			<?php

			ob_start();
			do_action( $this->_plugin_slug . '_settings' );
			$this->settings();
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				?>
				<form method="post">
					<div style="clear:both;">
						<?php echo $buffer; ?>
					</div>
					<?php submit_button( 'Save', 'primary', 'save-settings' ); ?>
				</form>

			<?php
			}

			?></div><?php

		}

		function settings() {

		}

		function settings_title() {
			return $this->plugin_name() . ' Settings';
		}

		private function get_update_path() {
			if ( true || $this->testing ) {
				$this->update_path = 'http://' . 'pyebrook.local' . '/wp-content/plugins/auto-update/update.php';
			} else {
				$this->update_path = 'http://' . get_option( 'pbci_update_domain', 'www.pyebrook.com' ) . '/wp-content/plugins/auto-update/update.php';
			}

			return $this->update_path;
		}

		public function register_this_plugin() {
			$plugin_data = get_plugin_data( $this->_plugin_file, false, false );

			$body            = $plugin_data;
			$body ['action'] = 'license';
			$body ['slug']   = $this->_plugin_slug;

			$body ['site_name']        = get_bloginfo( 'name' );
			$body ['site_admin_email'] = get_bloginfo( 'admin_email' );
			$body ['site_wp_version']  = get_bloginfo( 'version' );
			$body ['site_url']         = parse_url( site_url(), PHP_URL_HOST );

			$cookies[] = new WP_Http_Cookie( array( 'name' => 'XDEBUG_SESSION', 'value' => 'PHPSTORM' ) );
			$url       = $this->get_update_path();
			$response  = wp_remote_post( $url, array( 'body' => $body, ) );
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
			} else {
				echo 'Response:<pre>';
				$this->log( var_export( $response, true ) );
				echo '</pre>';
			}

			return false;
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
					$this->license_code( $response['key'] );
				}
			}

			$key = $this->license_code();

			pbci_admin_nag( $this->plugin_settings_link() . '<br>' . 'test' );
			?>
			<style>
				table.widefat tr:first-child th {
					background-color: darkgray;
					color: white;
					font-weight: bold;

				}

				table.widefat tr td:first-child {
					width: 25%;
				}

				table.widefat td:first-child {
					font-weight: bold;
				}

				table.widefat {
					margin-bottom: 1.5em;
				}
			</style>

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

				<tr>
					<td colspan="2">
						<?php if ( empty( $key ) ) { ?>
							<?php echo submit_button( 'Register', 'primary', 'register' ); ?>
						<?php } else { ?>
							For support, or to get any of our other WP-eCommerce plugins, please visit <a
								href="http://www.pyebrook.com">http://www.pyebrook.com</a>.
						<?php } ?>
					</td>
				</tr>

			</table>
		<?php
		}
	}
}