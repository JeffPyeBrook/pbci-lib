<?php
/*
** Copyright 2010-2013, Pye Brook Company, Inc.
**
** Licensed under the Pye Brook Company, Inc. License, Version 1.0 (the "License");
** you may not use this file except in compliance with the License.
** You may obtain a copy of the License at
**
**     http://www.pyebrook.com/
**
** This software is not free may not be distributed, and should not be shared.  It is governed by the
** license included in its original distribution (license.pdf and/or license.txt) and by the
** license found at www.pyebrook.com.
*
** This software is copyrighted and the property of Pye Brook Company, Inc.
**
** See the License for the specific language governing permissions and
** limitations under the License.
**
** Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
*/

add_action( 'wp_ajax_get_mailing_labels', 'pbci_gs_mailing_labels_html_and_css' );
add_action( 'wp_ajax_no_priv_get_mailing_labels', 'pbci_gs_mailing_labels_html_and_css' );

function pbci_gs_mailing_labels_html_and_css() {

	global $wpdb;

	$group_ship = urldecode( $_REQUEST['group_ship'] );

	$sql = 'SELECT id FROM ' . WPSC_TABLE_PURCHASE_LOGS . ' WHERE shipping_method = "pbci_group_shipping" AND shipping_option = "' . $group_ship . '" ORDER BY shipping_option'  ;
	$purchase_log_ids = $wpdb->get_col( $sql , 0 );

	$summaries = array();
	$products = array();
	$labels_info = array();

	foreach ($purchase_log_ids as $purchase_log_id ) {

		$purchase_log = new WPSC_Purchase_Log( $purchase_log_id );
		$purchaser_user_id = $purchase_log->get( 'user_ID' );
		$cart_contents = $purchase_log->get_cart_contents();

		$checkout_form_data = new WPSC_Checkout_Form_Data( $purchase_log_id );
		$firstname = trim( $checkout_form_data->get( 'billingfirstname' ) );
		$lastname  = trim( $checkout_form_data->get( 'billinglastname' ) );


		foreach ( $cart_contents as $cart_item ) {
			for ( $index = 0; $index <  $cart_item->quantity; $index ++ ) {
				$custom_message = pbci_gs_cart_item_custom_message($cart_item->id);
				$info = array();

				$info['order_id']        = $purchase_log_id;
				$info['purchaser_name']  = ucwords($lastname . ', ' . $firstname);
				$info['quantity']        = $cart_item->quantity;
				$info['item_name']       = $cart_item->name;
				$info['custom_message']  = $custom_message;
				$info['purchaser_count'] = 0;
				$info['purchaser_index'] = 0;

				$labels_info[] = $info;
			}
		}
	}

	$name_counts = array();

	foreach ( $labels_info as $index => $info ) {
		if ( !isset( $name_counts[ $info['purchaser_name'] ] ) ) {
			$name_counts[ $info['purchaser_name'] ] = 0;
		}

		$name_counts[ $info['purchaser_name'] ]++;
		$labels_info[ $index ]['purchaser_index']  = $name_counts[ $info['purchaser_name'] ];
	}

	foreach ( $labels_info as $index => $info ) {
		$labels_info[ $index ]['purchaser_count']  = $name_counts[ $info['purchaser_name'] ];
	}

	?>
	<!doctype html>
	<html lang="en">
	<head>
	<meta charset="utf-8">
	<title>HTML & CSS Avery Labels (5160) by MM at Boulder Information Services</title>
	<link href="labels.css" rel="stylesheet" type="text/css" >
	<style>
	body {
		width: 8.5in;
		margin-right: 0.5cm;
		margin-left: 0.5cm;
		margin-top: 1.3 cm;
		margin-bottom: 1.3cm;
	}
	.label{
		/* Avery 5160 labels -- CSS and HTML by MM at Boulder Information Services */
		width: 6.7cm;
		height: 2.5cm; /* plus .125 inches from padding */
/*		padding-top: .125in;
		padding-left: .15in; */
		margin-right: .125in; /* the gutter */

		float: left;

		text-align: center;
		overflow: hidden;
		position:relative;
		/*outline: 1px dotted;  outline doesn't occupy space like border does */
	}
	.page-break  {
		clear: left;
		display:block;
		page-break-after:always;
	}
	</style>

	</head>
	<body>

	<?php
	$count = 0;
	foreach ( $labels_info as $info ) {
		?>
		<div class="label">
			<div style="position:absolute;top:10px;left:10px;"><?php echo $info['purchaser_name'];?></div>
			<div style="position:absolute;top:10px;right:10px;"><?php echo $info['order_id'];?></div>
			<div style="font-weight:bold;font-size:0.8em;position:absolute;top:35px;left:15px;right:15px;bottom:25px;"><?php echo $info['item_name'];?></div>
			<div style="position:absolute;bottom:10px;right:10px;"><?php echo $info['purchaser_index'] . ' of ' . $info['purchaser_count'];?></div>
		</div>

		<?php
		$count++;

		if ( !($count % 30) ) {
			?>
			<div class="page-break"></div>
			<?php
		}
		?>

	</body>
	</html>
	<?php
	}

	exit();
}