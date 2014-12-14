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

class GS_Metabox_Between_Dates extends PBCI_MetaBox {

	function do_metabox( $post ) {

		parent::do_metabox( $post );

		$id = $post->ID;

		$saved_start_date = $this->get_option( $id, 'start_date' );
		$saved_end_date   = $this->get_option( $id, 'end_date', true );
		$enabled          = $this->get_option( $id, 'enabled' ) == '1';

		// //////////////////////////////////////////////////////////////////
		// get some default times
		$datetime   = new DateTime( $post->post_date );
		$start_date = $datetime->format( 'Y-m-d' );
		$x          = strtotime( '+7 days', strtotime( $start_date ) );
		$end_date   = date( 'Y-m-d', $x );
		$start_time = '00:00';
		$end_time   = '23:59';

		// //////////////////////////////////////////////////////////////////
		// If there are saved times we can work with them
		$start_date = $post->post_date;

		if ( ! empty( $saved_start_date ) ) {
			try {
				$datetime   = new DateTime( $saved_start_date );
				$start_date = $datetime->format( 'Y-m-d' );
			} catch ( Exception $e ) {
				bling_log( 'malformed start date or time' );
				$saved_start_date = '';
			}
		}

		if ( ! empty( $saved_end_date ) ) {
			try {
				$datetime = new DateTime( $saved_end_date );
				$end_date = $datetime->format( 'Y-m-d' );
			} catch ( Exception $e ) {
				bling_log( 'malformed end date or time' );
				$saved_end_date = '';
			}
		}

		?>
		<table class="widefat">
			<tr>
				<td colspan="2">
					<input type="checkbox" name="<?php $this->option_element_name( 'enabled' );?>" value="1" <?php checked( $enabled ); ?>>
					<label for="<?php echo __FUNCTION__; ?>">Enabled</label>
				</td>
			</tr>

			<tr>
				<td>
					<label for="startdate">Start Date:</label>
				</td>
				<td>
					<input class="mydatepicker"
					       size="40" type="text" name="<?php $this->option_element_name( 'start_date' );?>"
					       value="<?php echo $saved_start_date; ?>">
				</td>
			</tr>

			<tr>
				<td>
					<label for="enddate">End Date:</label>
				</td>
				<td>
					<input size="40" class="mydatepicker" type="text" name="<?php $this->option_element_name( 'end_date' );?>"
					       value="<?php echo $saved_end_date; ?>">
				</td>
			</tr>

		</table>

		<input type="hidden" id="groupshipid" name="id" value="<?php echo $id; ?>">
	<?php
	}
}




function pbci_gs_setup_ship_mb_between_dates() {
	new GS_Metabox_Between_Dates( 'Available Between Dates',  pbci_gs_post_type() );
}

add_action( 'pbci_gs_setup_ship_mb', 'pbci_gs_setup_ship_mb_between_dates', 5 , 0 );


function pbci_gs_between_dates_applies( $shipping_method_post_id, $cart = false ) {
	$result = false;

	if ( false === $cart ) {
		$cart  = wpsc_get_cart();
	}

	$saved_start_date = $this->get_option( $shipping_method_post_id, 'start_date' );
	$saved_end_date   = $this->get_option( $shipping_method_post_id, 'end_date', true );
	$enabled          = $this->get_option( $shipping_method_post_id, 'enabled' ) == '1';

	if ( $enabled ) {

		try {
			$datetime = new DateTime($saved_start_date);
			$start_date = $datetime->getTimestamp();
		} catch (Exception $e) {
			pbci_log( 'malformed start date or time: '. $saved_start_date);
			$start_date = 0;
		}

		try {
			$datetime = new DateTime($saved_end_date);
			$end_date = $datetime->getTimestamp();
		} catch (Exception $e) {
			pbci_log( 'malformed start date or time: '. $saved_end_date);
			$end_date = 0;
		}

		$now = time();

		if ( $now >= $start_date  && $now <= $end_date ) {
			$result = true;
		}
	}

	return result;

}

add_filter( 'pbci_gs_')