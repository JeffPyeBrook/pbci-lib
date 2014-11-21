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
	 * @param string $text
	 * @param string $line
	 * @param string $file
	 * @param string $function
	 * @param string $class
	 */
	function pbci_log( $text = '', $line = '', $file = '', $function = '', $class = '' ) {
		$do_the_log = ( defined( 'WP_DEBUG' ) && WP_DEBUG );

		$log_file_path = trailingslashit( dirname( __FILE__ ) ) . 'testing.log';

		if ( ! $do_the_log ) {
			// if logging is turned off, and there is an old log file, make it go away
			if ( file_exists( $log_file_path ) ) {
				unlink( $log_file_path );
			}
		} else {
			$log_file_path = apply_filters( 'pbci_log', $log_file_path );

			$slug = basename( dirname ( __FILE__ ) );

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
