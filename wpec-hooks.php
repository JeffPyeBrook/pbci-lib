<?php
/*
** Copyright 2010-2014, Pye Brook Company, Inc.
**
** Licensed under the Pye Brook Company, Inc. License, Version 1.0 (the "License");
** you may not use this file except in compliance with the License.
** You may obtain a copy of the License at
**
**     http://www.pyebrook.com/
**
** You may use this software for its intended purchase on web sites that you own.
**  
** This software is not free, may not be distributed, and should not be shared.  It is governed by the
** license included in its original distribution (license.pdf and/or license.txt) and by the
** license found at www.pyebrook.com. Unless you are explicitly granted a right you are explicitly 
** forbidden from inferring that right.   
**
** This software is copyrighted and the property of Pye Brook Company, Inc.
**
** See the License for the specific language governing permissions and
** limitations under the License.
**
** Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
*/


add_filter( 'wpsc_manage_purchase_logs_custom_column', 'pbci_gs_wpsc_manage_purchase_logs_custom_column_filter', 10, 3 );

function pbci_gs_wpsc_manage_purchase_logs_custom_column_filter( $default, $column_name, $item ) {
	$output = $default;

	if ( $column_name == 'shipping-method' ) {

		$purchase_log = new WPSC_Purchase_Log( absint( $item->id ) );

		$shipping_method = $purchase_log->get( 'shipping_method' );
		$shipping_option = $purchase_log->get( 'shipping_option' );

		$output = $shipping_method;

		if ( ! empty( $output ) && ! empty($shipping_option  ) ) {
			$output .='<br>';
		}

		$output .= $shipping_option;
	}

	return $output;
}

add_filter( 'manage_dashboard_page_wpsc-purchase-logs_columns',  'pbci_gs_add_email_column_filter', 99 ,1 );
function pbci_gs_add_email_column_filter( $columns ) {

	$new_columns = array();

	foreach ( $columns as $key => $value ) {
		if ( 'amount' == $key ) {
			$new_columns['shipping-method'] = 'Shipping';
		}

		$new_columns[$key] = $value;
	}

	return $new_columns;
}


