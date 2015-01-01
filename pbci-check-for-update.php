<?php

/*
** Copyright 2010-2014, Pye Brook Company, Inc.
**
**
** This software is provided under the GNU General Public License, version 2 (GPLv2), that covers its  copying,
** distribution and modification. The GPLv2 license specifically states that it only covers only copying,
** distribution and modification activities. The GPLv2 further states that all other activities are outside of the
** scope of the GPLv2.
**
** All activities outside the scope of the GPLv2 are covered by the Pye Brook Company, Inc. License. Any right
** not explicitly granted by the GPLv2, and not explicitly granted by the Pye Brook Company, Inc. License are reserved
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

if ( ! class_exists( 'PBCIAutoUpdate' ) ) {


// only update when we are showing an admin page or doing cron processing
	if ( is_admin() || ( defined( 'WP_CRON' ) && WP_CRON ) ) {

		/**
		 * Class PBCIAutoUpdate
		 */
		class PBCIAutoUpdate {
			/**
			 * The plugin current version
			 *
			 * @var string
			 */
			public $current_version = '';

			/**
			 * The plugin remote update path
			 *
			 * @var string
			 */
			public $update_path = '';

			/**
			 * Plugin Slug (plugin_directory/plugin_file.php)
			 *
			 * @var string
			 */
			public $plugin_slug = '';

			/**
			 * Plugin data from plugin file header
			 *
			 * @var object
			 */
			public $plugin_data = false;

			/**
			 * Plugin file path
			 *
			 * @var string
			 */
			protected $plugin_file = '';

			/**
			 * Have plugin data from plugin header
			 *
			 * @var boolean
			 */
			protected $have_plugin_data = false;

			/**
			 * @var string
			 */
			protected $plugin_basename = '';


			private function are_we_testing() {
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
					$this->update_path = 'http://' . 'pyebrook.local' . '/wp-content/plugins/auto-update/update.php';
				} else {
					$this->update_path = 'http://' . get_option( 'pbci_update_domain', 'www.pyebrook.com' ) . '/wp-content/plugins/auto-update/update.php';
				}

				return $this->update_path;
			}

			/**
			 * @return bool
			 */
			private function check_plugin_data() {

				if ( $this->have_plugin_data ) {
					return true;
				}

				if ( function_exists( 'get_plugin_data' ) ) {
					add_filter( 'extra_plugin_headers', array( &$this, 'extra_headers' ), 1 );
					$this->plugin_data = get_plugin_data( $this->plugin_file, false, false );
					remove_filter( 'extra_plugin_headers', array( &$this, 'extra_headers' ) );
				} else {
					return false;
				}

				// Set the class public variables
				$this->current_version = $this->plugin_data ['Version'];
				if ( empty( $this->current_version ) ) {
					$this->current_version = '0.0';
				}

				$this->plugin_slug     = basename( dirname( $this->plugin_file ) );
				$this->plugin_basename = plugin_basename( $this->plugin_file );

				$this->have_plugin_data = true;

				return true;
			}

			/**
			 * @param $locales
			 *
			 * @return mixed
			 */
			function hook_set_plugin_update_transient( $locales ) {
				add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );
				return $locales;
			}

			/**
			 * Initialize a new instance of the WordPress Auto-Update class
			 *
			 * @param string $plugin_file
			 */
			function __construct( $plugin_file ) {
				$this->plugin_file = $plugin_file;

				// define the alternative API for updating checking
				add_filter( 'plugins_update_check_locales', array( &$this, 'hook_set_plugin_update_transient' ) );

				// Define the alternative response for information checking
				add_filter( 'plugins_api', array( &$this, 'check_info' ), 10, 3 );
			}

			/**
			 * @param $headers
			 *
			 * @return mixed
			 */
			function extra_headers( $headers ) {
				$headers ['UpdateURI']  = 'UpdateURI';
				$headers ['LicenseKey'] = 'LicenseKey';

				return $headers;
			}

			/**
			 * Add our self-hosted check for update results to the filter transient
			 *
			 * @param
			 *            $transient
			 *
			 * @return object $ transient
			 */
			public function check_update( $transient ) {
				if ( empty( $transient->last_checked ) ) {
					return $transient;
				}

				if ( ! $this->check_plugin_data() ) {
					return false;
				}

				remove_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );

				// Get the remote version
				$information = $this->get_remote_information();

				// If a newer version is available, add the update
				if ( $information !== false && is_object( $information ) ) {

					if ( property_exists( $information, 'version' ) ) {
						$remote_version = $information->version;
					} else {
						$remote_version = '';
					}

					if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
						$obj = new stdClass();

						$obj->name        = 'hello i am here';
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

						$transient->response [ $this->plugin_basename ] = $obj;
					}
				}

				return $transient;
			}

			/**
			 * Add our self-hosted description to the filter
			 *
			 * @param string $current_filter_value
			 * @param string $action
			 * @param string $arg
			 *
			 * @return bool|string $current_filter_value
			 */
			public function check_info( $current_filter_value, $action, $arg ) {
				if ( ! $this->check_plugin_data() ) {
					return false;
				}

				if ( $action ) {
					; // avoid the unused parameter warning
				}

				if ( property_exists( $arg, 'slug' ) ) {
					if ( $arg->slug === $this->plugin_basename ) {
						$information = $this->get_remote_information();

						return $information;
					}
				}

				return $current_filter_value;
			}

			/**
			 * Return the remote version
			 *
			 * @return string $remote_version
			 */
			public function get_remote_version() {
				if ( ! $this->check_plugin_data() ) {
					return false;
				}

				$body            = $this->plugin_data;
				$body ['slug']   = $this->plugin_slug;
				$body ['action'] = 'version';

				$body ['site_name']        = get_bloginfo( 'name' );
				$body ['site_admin_email'] = get_bloginfo( 'admin_email' );
				$body ['site_wp_version']  = get_bloginfo( 'version' );
				$body ['site_url']         = parse_url( site_url(), PHP_URL_HOST );

				$args = array( 'body' => $body, );

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$cookies[]       = new WP_Http_Cookie( array( 'name' => 'XDEBUG_SESSION', 'value' => 'PHPSTORM' ) );
					$args['cookies'] = $cookies;
				}

				$request = wp_remote_post( $this->get_update_path(), $args );
				if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {
					$info = unserialize( $request ['body'] );
					if ( $info !== false ) {
						$this->update_path = $info->download_link;

						return $info->version;
					}
				}

				return false;
			}

			/**
			 * Get information about the remote version
			 *
			 * @return bool object
			 */
			public function get_remote_information() {
				if ( ! $this->check_plugin_data() ) {
					return false;
				}

				$body            = $this->plugin_data;
				$body ['slug']   = $this->plugin_slug;
				$body ['action'] = 'info';

				$body ['site_name']        = get_bloginfo( 'name' );
				$body ['site_admin_email'] = get_bloginfo( 'admin_email' );
				$body ['site_wp_version']  = get_bloginfo( 'version' );
				$body ['site_url']         = parse_url( site_url(), PHP_URL_HOST );

				$request = wp_remote_post(
					$this->get_update_path(),
					array(
						'body'     => $body,
						'timeout'  => 15,
						'blocking' => true
					)
				);

				if ( is_wp_error( $request ) ) {
					pbci_log( $request->get_error_message() );
				} elseif ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
					$info = unserialize( $request ['body'] );
					if ( $info !== false ) {
						$this->update_path = $info->download_link;
						return $info;
					}
				}

				$obj              = new stdClass();
				$obj->slug        = $this->plugin_slug;
				$obj->plugin_name = $_REQUEST ['slug'] . ' .php';
				$obj->tested      = get_bloginfo( 'version' );
				$obj->sections    = array( 'Update Error' => 'There was a problem getting update information from the server.' );

				return $obj;
			}

			/**
			 * Return the status of the plugin licensing
			 *
			 * @return boolean $remote_license
			 */
			public function get_remote_license() {
				if ( ! $this->check_plugin_data() ) {
					return false;
				}

				$body            = $this->plugin_data;
				$body ['action'] = 'license';
				$body ['slug']   = $this->plugin_slug;

				$body ['site_name']        = get_bloginfo( 'name' );
				$body ['site_admin_email'] = get_bloginfo( 'admin_email' );
				$body ['site_wp_version']  = get_bloginfo( 'version' );
				$body ['site_url']         = parse_url( site_url(), PHP_URL_HOST );

				$request = wp_remote_post( $this->get_update_path(), array( 'body' => $body ) );
				if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
					return $request ['body'];
				}

				return false;
			}
		}

		// get an auto update object specific to this directory
		$plugin_main_file = dirname( __FILE__ ) . '/' . basename( dirname( __FILE__ ) ) . '.php';
		if ( file_exists( $plugin_main_file ) ) {
			$token  = str_replace( '-', '_', basename( __DIR__ ) ) . '_auto_update';
			$$token = new PBCIAutoUpdate( $plugin_main_file );
		} else {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				pbci_log( 'plugin main file does not exist at ' . $plugin_main_file . ', auto update not setup' );
			}
		}
	}

} // end class exists


