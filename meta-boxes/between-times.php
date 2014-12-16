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


class GS_Metabox_Between_Times extends PBCI_MetaBox {

	function do_metabox( $post ) {

		parent::do_metabox( $post );

		$id = $post->ID;

		$start_time       = $this->get_option( $id, 'start_time' );
		$end_time         = $this->get_option( $id, 'end_time' );
		$cost             = $this->get_option( $id, 'cost', true );
		$enabled          = $this->get_option( $id, 'enabled' ) == '1';

		$days_of_week = array( 'monday', 'tuesday', 'wednesday',  'thursday', 'friday', 'saturday', 'sunday' );

		?>
		<table class="widefat">
			<tr>
				<td colspan="2">
					<input type="checkbox" name="<?php $this->option_element_name( 'enabled' ); ?>" value="1"
						<?php checked( $enabled ); ?>>
					<label for="<?php echo __FUNCTION__; ?>">Enabled</label>
				</td>
			</tr>

			<tr>
				<td>
					<label for="startdate">Start Time:</label>
				</td>
				<td>
					<input class="mytimepicker"
					       size="40" type="text" name="<?php $this->option_element_name( 'start_time' ); ?>"
					       value="<?php echo $start_time; ?>">
				</td>
			</tr>

			<tr>
				<td>
					<label for="enddate">End Time:</label>
				</td>
				<td>
					<input class="mytimepicker"
					       size="40" type="text" name="<?php $this->option_element_name( 'end_time' ); ?>"
					       value="<?php echo $end_time; ?>">
				</td>
			</tr>


			<tr>
				<td>
					On days of week:
				</td>
				<td>
				<?php
				foreach ( $days_of_week as $day ) {
					$enabled = $this->get_option( $id, $day ) == '1';
					?>
					<input
						type="checkbox"
						name="<?php $this->option_element_name( $day ); ?>"
						     <?php checked( $enabled ); ?>
						value="1"
						>

					<label for="<?php echo $day;?>"><?php echo ucfirst( $day );?></label><br>
					<?php

				}

				?>
				</td>
			</tr>


		</table>

		<input type="hidden" id="groupshipid" name="id" value="<?php echo $id; ?>">
	<?php
	}
}

function pbci_gs_setup_ship_mb_between_times() {
	new GS_Metabox_Between_Times( 'Available Between Times',  pbci_gs_post_type() );
}

add_action( 'pbci_gs_setup_ship_mb', 'pbci_gs_setup_ship_mb_between_times', 5 , 0 );



function pbci_gs_between_times_applies( $applies = false, $shipping_method_post_id = 0, $cart = false ) {

	if ( empty( $shipping_method_post_id ) ) {
		return $applies;
	}

	$mb = new GS_Metabox_Between_Times( 'Available Between Dates',  pbci_gs_post_type() );
	$enabled = $mb->get_option( $shipping_method_post_id, 'enabled' ) == '1';
	if ( ! $enabled ) {
		pbci_log( 'check not enabled for ' . $shipping_method_post_id );
		return $applies;
	}

	if ( ! $applies ) {
		pbci_log( 'check skipped, already does not apply for ' . $shipping_method_post_id );
		return $applies;
	}


	$shipping_method_post_id = absint( $shipping_method_post_id );



	$days_of_week = array( 'monday', 'tuesday', 'wednesday',  'thursday', 'friday', 'saturday', 'sunday' );

	$count_of_days_to_check = 0;
	foreach ( $days_of_week as $day ) {
		$enabled = $mb->get_option( $shipping_method_post_id, $day ) == '1';
		if ( $enabled ) {
			$count_of_days_to_check++;
		}
	}

	$day_of_week_check_passed = false;

	if ( 0 == $count_of_days_to_check ) {
		$day_of_week_check_passed = true;
	} else {
		$this_day_is = strtolower( date( 'l' ) );
		$enabled = $mb->get_option( $shipping_method_post_id, $this_day_is ) == '1';
		if ( $enabled ) {
			$day_of_week_check_passed = true;
		}
	}

	if ( ! $day_of_week_check_passed ) {
		pbci_log( 'day of week '. $this_day_is . ' check for shipping id ' . $shipping_method_post_id . ' failed');
		return false;
	}

	$saved_start_time = $mb->get_option( $shipping_method_post_id, 'start_time' );

	$saved_start_time_seconds = strtotime( $saved_start_time ); // Do some verification before this step

	if ( time() < $saved_start_time_seconds ) {
		pbci_log( 'before start time '. $saved_start_time . ' for shipping id ' . $shipping_method_post_id . ' check failed' );
		return false;
	}

	$saved_end_time = $mb->get_option( $shipping_method_post_id, 'end_time' );

	$saved_end_time_seconds = strtotime( $saved_end_time ); // Do some verification before this step

	if ( time() > $saved_end_time_seconds ) {
		pbci_log( 'after end time '. $saved_end_time . ' for shipping id ' . $shipping_method_post_id . ' check failed'  );
		return false;
	}


	return true;

}

add_filter( 'pbci_gs_check_condition', 'pbci_gs_between_times_applies', 3, 10 );



