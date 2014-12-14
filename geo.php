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


function get_latlong( $address ) {
	$gocode_api_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
	$distance_matrix_api_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?';
	$api_key= 'AIzaSyCdqEJO9s8pvNbo8WmB3FYF-EPq2rcUNms';

	$address = str_replace( "\r\n", ',', $address );
	$address = str_replace( ' ', '+', $address );
	error_log( $address );

	$url = $gocode_api_url . $address . '&key=' . $api_key;

	error_log( $url );

	$response = wp_remote_get( $url );

	if ( ! is_wp_error( $response ) ) {
		$response_body = $response['body'];
		$json_response = json_decode( $response_body );

		$lat_long = $json_response->results[0]->geometry->location;
	} else {
		$lat_long = false;
	}

	return $lat_long;
}

function pbci_gs_get_distance_from_store_base( $to,  $units = 'metric', $from = '' ) {
	$gocode_api_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
	$distance_matrix_api_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?';
	$api_key= 'AIzaSyCdqEJO9s8pvNbo8WmB3FYF-EPq2rcUNms';

	if ( empty( $from ) ) {
		$from = pbci_gs_store_base_address();
	}

	$from = str_replace( "\r\n", ',', $from );
	$from = str_replace( ' ', '+', $from );

	$to = str_replace( "\r\n", ',', $to );
	$to = str_replace( ' ', '+', $to );

	$url = $distance_matrix_api_url . 'origins=' . $from . '&destinations=' . $to . '&units=' . $units .'&key=' . $api_key;

	error_log( $url );

	$response = wp_remote_get( $url );

	if ( ! is_wp_error( $response ) ) {
		$response_body = $response['body'];
		$json_response = json_decode( $response_body );

		$distance = $json_response->rows[0]->elements[0]->distance;
		if ( $units == 'imperial' ) {
			$distance->value = $distance->value * 0.000621371; // convert to miles
		} else {
			$distance->value = $distance->value * 0.001; // convert to km
		}
	} else {
		$distance = false;
	}

	return $distance;
}


function pbci_gs_store_base_address() {
	$address = "5 Comstock Lane, Topsfield, MA 01983";
	return $address;
}