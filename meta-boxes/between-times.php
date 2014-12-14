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

		foreach ( $days_of_week as $day ) {
			$$day = $this->get_option( $id, $day ) == '1';
		}


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

