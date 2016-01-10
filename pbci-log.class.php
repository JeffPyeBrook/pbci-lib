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

if ( ! class_exists( 'pbciLogV2' ) ) {

	class pbciLogV2 {

		private static $instance = null;

		protected $_log_file_dir = false;
		protected $_log_file = false;
		protected $_slug = false;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  pbciLog A single instance of this class.
		 */
		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		} // end get_instance;


		public static function is_logging_enabled() {
			$logging_is_enabled = get_option( 'pbci_logging_is_enabled', '0' );
			$logging_is_enabled = (bool) absint( $logging_is_enabled );

			return $logging_is_enabled;
		}

		public static function disable_logging() {
			self::set_logging_enabled( '0' );

			return self::is_logging_enabled();
		}

		public static function enable_logging() {
			self::set_logging_enabled( '1' );

			return self::is_logging_enabled();
		}

		public static function set_logging_enabled( $value ) {
			update_option( 'pbci_logging_is_enabled', $value );

			return self::is_logging_enabled();
		}

		function __construct( $slug = '' ) {
			$this->_slug         = $slug;
			$upload_dir          = wp_upload_dir();

			if ( ( defined( WP_DEBUG ) && WP_DEBUG ) || ( false !== stripos( $_SERVER['HTTP_HOST'], 'local' ) || empty( $this->slug ) ) ) {
				$this->_log_file  = ini_get('error_log');
				$this->_use_php_error_log = true;
				$this->_log_file_url = '';
			} else {
				$this->_log_file_dir = trailingslashit( $upload_dir['basedir'] );
				$this->_log_file     = $this->_log_file_dir . $this->_slug . 'pbci.log';
				$this->_log_file_url = trailingslashit( $upload_dir['baseurl'] ) . $this->_slug . 'pbci.log';
				$this->_use_php_error_log = false;
			}

			add_action( 'template_redirect', array( &$this, 'load_log_file' ) );
			add_action( 'pbci_set_logging_enabled', array( __CLASS__, 'set_logging_enabled' ), 10, 1 );
		}

		function ends_with_log_file_path() {

			$partial_path_to_log_file = strtok( $_SERVER["REQUEST_URI"], '?' );
			$path_to_log_file         = $this->get_log_file_url();

			$strlen  = strlen( $path_to_log_file );
			$testlen = strlen( $partial_path_to_log_file );

			if ( $testlen > $strlen ) {
				return false;
			}

			return substr_compare( $path_to_log_file, $partial_path_to_log_file, $strlen - $testlen, $testlen ) === 0;
		}

		function get_log_key() {
			$key  = '';
			$url  = $_SERVER["REQUEST_URI"];
			$args = parse_url( $url, PHP_URL_QUERY );
			parse_str( $args, $values );

			if ( isset( $values['key'] ) ) {
				$key = $values['key'];
			}

			return $key;
		}

		function load_log_file( $template ) {

			if ( ! $this->ends_with_log_file_path() ) {
				return false;
			}

			$key = $this->get_log_key();

			if ( ! empty( $key ) ) {
				$valid = pbciPluginV2::is_license_key_valid( $key );
				if ( $valid ) {
					self::enable_logging();
				} else {
					self::disable_logging();
				}
			}

			$logging_is_enabled = self::is_logging_enabled();

			if ( ! $logging_is_enabled ) {
				return false;
			}

			if ( is_404() ) {
				$plugin_info = apply_filters( 'pbci_get_plugin_information', array(), 10, 1 );
				foreach ( $plugin_info as $plugin_name => $info ) {
					?>
					<table>
						<tr>
							<th colspan="0"><?php echo $plugin_name; ?></th>
						</tr>
						<?php

						foreach ( $info as $key => $value ) {
							?>
							<tr>
								<td><?php echo $key; ?></td>
								<td><?php echo $value; ?></td>
							</tr>
						<?php
						}
						?>
					</table>
				<?php
				}
				echo '<hr>';
				$buffer = file_get_contents( $this->log_file() );
				echo nl2br( $buffer );
				exit( 0 );
			}
		}


		public function get_log_file_url() {
			return $this->_log_file_url;
		}

		protected function slug() {
			return $this->_slug;
		}

		public function log_file_dir() {
			return $this->_log_file_dir;
		}

		public function log_file() {
			return $this->_log_file;
		}

		static function get_caller_info() {
			$backtrace_index = 4;

			// setup some defaults in case things go wrong
			$logger_info = array();
			$logger_info['file']     = '';
			$logger_info['line']     = '';
			$logger_info['function'] = '';
			$logger_info['class']    = '';

			// get the backtrace, if we can
			if ( function_exists( 'debug_backtrace' ) ) {
				if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0 ) {
					$traces = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, $backtrace_index );
				} else {
					$traces = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT );
				}

				$backtrace_index --;

				if ( isset( $traces[ $backtrace_index ] ) ) {
					$trace                   = $traces[ $backtrace_index ];
					$logger_info['file']     = isset( $trace['file'] ) ? wp_normalize_path( $trace['file'] ) : '';
					$logger_info['line']     = isset( $trace['line'] ) ? $trace['line'] : '';
					$logger_info['function'] = isset( $trace['function'] ) ? $trace['function'] : '';
					$logger_info['class']    = isset( $trace['class'] ) ? $trace['class'] : '';

					$backtrace_index --;
					$trace = $traces[ $backtrace_index ];

					if ( empty( $logger_info['file'] ) ) {
						$logger_info['file'] = isset( $trace['file'] ) ? wp_normalize_path( $trace['file'] ) : '';
					}

					if ( empty( $logger_info['line'] ) ) {
						$logger_info['line'] = isset( $trace['line'] ) ? $trace['line'] : '';
					}
				}
			}

			return $logger_info;
		}

		/**
		 * @param string $text
		 * @param string $line
		 * @param string $file
		 * @param string $function
		 * @param string $class
		 */
		function log( $text = '', $line = '', $file = '', $function = '', $class = '' ) {

			$do_the_log = self::is_logging_enabled() || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'PBCI_DEBUG' ) && PBCI_DEBUG );

			if ( ! $do_the_log ) {
				$pbci_log_files = get_option( 'pbci_log_files', array() );
				if ( ! empty( $pbci_log_files ) ) {

					foreach ( $pbci_log_files as $log_file_path => $dummy_value ) {
						// if logging is turned off, and there is an old log file, make it go away
						if ( file_exists( $log_file_path ) ) {
							unlink( $log_file_path );
						}
					}

					update_option( 'pbci_log_files', array() );
				}
			} else {

				$text = html_entity_decode( $text );

				$caller_info = self::get_caller_info();
				extract( $caller_info );

				$file = plugin_basename( $file );
				$slug = $this->slug();
				if ( ! empty ( $slug ) ) {
					$slug = str_pad( $this->slug() . ':', 16, ' ' );
				}

				$log_file_path = $this->log_file();
				$log_file_path = apply_filters( 'pbci_log', $log_file_path );

				$pbci_log_files = get_option( 'pbci_log_files', array() );
				if ( ! isset( $pbci_log_files[ $log_file_path ] ) ) {
					$pbci_log_files[ $log_file_path ] = true;
					update_option( 'pbci_log_files', $pbci_log_files );
				}

				$msg = $slug;

				if ( ! empty ( $text ) ) {
					if ( ! empty( $msg ) ) {
						$msg .= ' ';
					}

					$msg .= $text;
				}

				$msg = str_pad( $msg, 85, ' ' );
				if ( ! empty( $file ) ) {
					if ( ! empty( $msg ) ) {
						$msg .= ' ';
					}

					$msg .= $file . ' ';
				}

				if ( ! empty ( $class ) ) {
					if ( ! empty( $msg ) ) {
						$msg .= ' ';
					}

					$msg .= $class;
				}

				if ( ! empty ( $function ) ) {
					if ( ! empty( $msg ) ) {
						if ( ! empty ( $class ) ) {
							$msg .= '::';
						} else {
							$msg .= ' ';
						}
					}

					$msg .= $function;
				}

				if ( ! empty ( $line ) ) {
					if ( ! empty( $msg ) ) {
						$msg .= '@';
					}

					$msg .= $line;
				}

				error_log( $msg . "\n", 3, $log_file_path );
			}
		}
	}

	if ( ! function_exists( 'pbci_log' ) ) {
		function pbci_log( $text = '', $line = '', $file = '', $function = '', $class = '' ) {
			$_logger = pbciLogV2::get_instance();
			$_logger->log( $text );
		}
	}

	if ( ! function_exists( 'pbci_get_log_file_url' ) ) {
		function pbci_get_log_file_url() {
			$_logger = pbciLogV2::get_instance();

			return $_logger->get_log_file_url();
		}
	}
}
