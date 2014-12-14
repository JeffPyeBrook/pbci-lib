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



class GS_Metabox_Within_Distance extends PBCI_MetaBox {

	function do_metabox( $post ) {

		parent::do_metabox( $post );

		$id = $post->ID;

		$address = $this->get_option( $id, 'address' );
		$distance = $this->get_option( $id, 'distance' );
		$distance_units = $this->get_option( $id, 'distance_units' );
		$enabled  = $this->get_option( $id, 'enabled' ) == '1';


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
					Location Check:
				</td>
				<td>
					<?php
					$latlong = get_latlong( $address );
					if ( $latlong ) {
						echo 'Found it!<br>';
						echo $latlong->lat . ' lat. ' . $latlong->lng . ' lng.<br>';
					}

					$distance_from_store_base = pbci_gs_get_distance_from_store_base( $address, $distance_units );
					if ( $distance_from_store_base ) {
						echo $distance_from_store_base->text . ' from store base address';
					} else {
						echo 'Could not find distance from store base address';
					}

					?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="address">Address:</label><br>
				</td>
				<td>
					<textarea
						id="address"
						name="<?php $this->option_element_name( 'address' );?>"
						rows="5"
						cols="50"
						><?php echo $address;?></textarea>
				</td>
			</tr>

			<tr>
				<td>
					Distance from Address:
				</td>
				<td>
					<input type="text" name="<?php $this->option_element_name( 'distance' );?>" value="<?php echo $distance;?>">
					<input <?php checked( $distance_units, 'imperial' );?> type="radio" name="<?php $this->option_element_name( 'distance_units' );?>" value="imperial" checked>Miles
					<input <?php checked( $distance_units, 'metric' );?> type="radio" name="<?php $this->option_element_name( 'distance_units' );?>" value="metric">KM
				</td>
			</tr>
		</table>

	<?php
	}
}




function pbci_gs_setup_ship_mb_within_distance() {
	new GS_Metabox_Within_Distance( 'For Deliveries Within Distance of Address', pbci_gs_post_type() );
}

add_action( 'pbci_gs_setup_ship_mb', 'pbci_gs_setup_ship_mb_within_distance', 5 , 0 );

