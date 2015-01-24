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

if ( ! function_exists( 'pbci_plugin_page_title_box' ) ) {
	function pbci_plugin_page_title_box( $title, $plugin_name = '' ) {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			if ( $current_screen && isset( $current_screen->id ) ) {
				$current_screen = $current_screen->id;
			}
		} else {
			$current_screen = '';
		}

		$title_class = 'pbci-plugin-page-title';

		if ( empty( $current_screen ) ) {
			$title_class = trim( $title_class . ' ' . $current_screen );
			if ( ! empty( $title_class ) ) {
				$title_class = 'class="snappy ' . $title_class . '"';
			}
		}
		?>
		<h2 <?php echo $title_class; ?> >
			<div class="icon <?php echo( trim( $plugin_name ) ); ?>-icon"></div> <?php echo esc_html( $title ); ?>
			<div class="pbci-admin-byline"><span><a href="www.pyebrook.com">by www.pyebrook.com</a></span></div>

		</h2>

		<hr>
		<?php

		do_action( 'PBCI_ADMIN_MESSAGES' );
	}
}