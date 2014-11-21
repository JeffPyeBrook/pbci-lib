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

if ( !function_exists( 'pbci_log' ) ) {

	/**
	 * Gets the basename of a plugin.
	 *
	 * This method extracts the name of a plugin from its filename.
	 *
	 * @uses WP_PLUGIN_DIR, WPMU_PLUGIN_DIR
	 *
	 * @param string $file The filename of plugin.
	 * @return string The name of a plugin.
	 */
	function pbci_plugin_basedir( $file ) {
		global $wp_plugin_paths;

		foreach ( $wp_plugin_paths as $dir => $realdir ) {
			if ( strpos( $file, $realdir ) === 0 ) {
				$file = $dir . substr( $file, strlen( $realdir ) );
			}
		}

		$file = wp_normalize_path( $file );
		$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );
		$mu_plugin_dir = wp_normalize_path( WPMU_PLUGIN_DIR );

		$base = ( strpos( $file, $plugin_dir ) !== false ) ? $plugin_dir : $mu_plugin_dir;

		$file = preg_replace('#^' . preg_quote($plugin_dir, '#') . '/|^' . preg_quote($mu_plugin_dir, '#') . '/#','',$file); // get relative path from plugins dir
		$file = trim($file, '/');

		$file = $base . '/' . $file . '/';

		return $file;
	}

	function pbci_get_calling_file() {
		$t = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT , 2 );

		if ( isset( $t[1]['file'] ) ) {
			$calling_file = isset( $t[1]['file'] );
		} else {
			if ( isset( $t[0]['file'] ) ) {
				$calling_file = isset( $t[1]['file'] );
			} else {
				$calling_file = __FILE__;
			}
		}

		return $calling_file;
	}

	/**
	 * @param string $text
	 * @param string $line
	 * @param string $file
	 * @param string $function
	 * @param string $class
	 */
	function pbci_log( $text = '', $line = '', $file = '', $function = '', $class = '' ) {

		$do_the_log = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'PBCI_DEBUG' ) && PBCI_DEBUG );

		$log_file_path = trailingslashit( pbci_plugin_basedir( $file ) ) . 'testing.log';

		if ( ! $do_the_log ) {
			// if logging is turned off, and there is an old log file, make it go away
			if ( file_exists( $log_file_path ) ) {
				unlink( $log_file_path );
			}
		} else {
			$log_file_path = apply_filters( 'pbci_log', $log_file_path );

			$slug = strtolower( plugin_basename( $file ) );

			$text = html_entity_decode( $text );
			$msg  =  $slug . ':';

			$file = str_replace( dirname ( __FILE__ ), '', $file );

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

				$msg .= $file;
			}

			if ( ! empty ( $class ) ) {
				if ( ! empty( $msg ) ) {
					$msg .= ' ';
				}

				$msg .= $class;
			}

			if ( ! empty ( $function ) ) {
				if ( ! empty( $msg ) ) {
					$msg .= ' ';
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
