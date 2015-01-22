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
					if ( $distance_from_store_base && ha) {
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


function pbci_gs_within_distance_applies( $applies = false, $shipping_method_post_id = 0, $cart = false ) {

	$shipping_method_post_id = absint( $shipping_method_post_id );
	if ( empty( $shipping_method_post_id ) ) {
		return $applies;
	}

	$mb = new GS_Metabox_Within_Distance( 'Available Between Dates',  pbci_gs_post_type() );
	$enabled = $mb->get_option( $shipping_method_post_id, 'enabled' ) == '1';
	if ( ! $enabled ) {
		pbci_log( 'check not enabled for ' . $shipping_method_post_id );
		return $applies;
	}

	$shipping_region = wpsc_get_customer_meta( 'shippingregion' );
	if ( ! empty( $shipping_region ) ) {
		if ( is_numeric( $shipping_region ) ) {
			$shipping_region = absint( $shipping_region );
			$shipping_region = wpsc_get_state_by_id( $shipping_region, 'code' );
		}
	}

	$shipping_address = '';
	$shipping_address .= ' ' . wpsc_get_customer_meta( 'shippingaddress' );
	$shipping_address .= ' ' . wpsc_get_customer_meta( 'shippingcity' );
	$shipping_address .= ' ' . $shipping_region;
	$shipping_address .= ' ' . wpsc_get_customer_meta( 'shippingcountry' );
	$shipping_address .= ' ' . wpsc_get_customer_meta( 'shippingpostalcode' );

	pbci_log( 'checking distance from billing address ' . $shipping_address );


	$from = $mb->get_option( $shipping_method_post_id, 'address' );
	$units = $mb->get_option( $shipping_method_post_id, 'distance_units' );

	$units_name = ( $units == 'imperial' ) ? 'miles' : 'km';

	$ship_to_distance_struct = pbci_gs_get_distance_from_store_base( $from,  $units, $shipping_address );

	if ( $ship_to_distance_struct ) {
		$ship_to_distance = $ship_to_distance_struct->value;

		$distance_limit = $mb->get_option( $shipping_method_post_id, 'distance' );

		if ( $ship_to_distance <= $distance_limit ) {
			pbci_log( 'distance to shippings address is close enough (' . $distance_limit . ' '. $units_name . ') ' . $ship_to_distance . ' ' . $units . ', shipping option applies' );
			$applies = true;
		} {
			pbci_log( 'distance to shippings address is too far ' . $ship_to_distance . ' ' . $units_name . ', shipping option not available' );
		}
	} else {
		pbci_log( 'distance to shippings address "' . $shipping_address . ' could not bve calculated' );
		$applies = false;
	}

	return $applies;

}

add_filter( 'pbci_gs_check_condition', 'pbci_gs_within_distance_applies', 3, 10 );

