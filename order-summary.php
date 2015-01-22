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

if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'packing_list' ) ) {
	add_action( 'wp_ajax_packing_list'       		, 'packing_list_wrapper' );
	add_action( 'wp_ajax_nopriv_packing_list'		, 'packing_list_wrapper' );
}

function pbci_gs_exclude_status_string() {
	$exclude_status_list = array( WPSC_Purchase_Log::INCOMPLETE_SALE, WPSC_Purchase_log::PAYMENT_DECLINED, WPSC_PURCHASE_LOG::REFUNDED, WPSC_Purchase_Log::REFUND_PENDING,  );
	$exclude_status_string = implode( ',' , $exclude_status_list );
	return $exclude_status_string;
}

function pbci_gs_status_list() {
	$status_list = array(
		WPSC_Purchase_Log::ACCEPTED_PAYMENT,
		WPSC_Purchase_Log::CLOSED_ORDER,
		WPSC_Purchase_Log::ORDER_RECEIVED,
		WPSC_Purchase_Log::INCOMPLETE_SALE,
		WPSC_Purchase_log::PAYMENT_DECLINED,
		WPSC_PURCHASE_LOG::REFUNDED,
		WPSC_Purchase_Log::REFUND_PENDING,
		);

	return $status_list;
}


function pbci_gs_purchase_log_save_tracking_id($purchase_log_id, $track_id ) {
	global $wpdb;

	$result = $wpdb->update(
		WPSC_TABLE_PURCHASE_LOGS,
		array(
			'track_id' => $track_id
		),
		array(
			'id' => $purchase_log_id
		),
		'%s',
		'%d'
	);

	if ( ! $result )
		return new WP_Error( 'wpsc_cannot_save_tracking_id', __( "Couldn't save tracking ID of the transaction. Please try again.", 'wpsc' ) );

	$return = array(
		'rows_affected' => $result,
		'id'            => $_POST['log_id'],
		'track_id'      => $_POST['value'],
	);

	return $return;
}


function packing_list_wrapper( ) {

	$shipping_option = $_REQUEST['group_ship'];

	?>
		<!doctype html>
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<title>Packing List for <?php echo $shipping_option;?></title>

		<link rel="stylesheet" type="text/css" href="script/admin.css" />
		<style>
		.print-link, .mailing-labels-links, .back-link {
			display: none;
		}
		.page-break  {
			clear: left;
			display:block;
			page-break-after:always;
		}

		tr:nth-child(odd)		{ background-color:#eee; }
		tr:nth-child(even)		{ background-color:#fff; }
		</style>

		</head>
		<body onload="window.print()">

		<?php
		pbci_gs_get_purchases_table( $shipping_option );
		?>
		</body>
		</html>
		<?php
		exit();

}

function pbci_gs_get_purchases_table( $group_ship ) {

	if ( isset( $_REQUEST['gs-mark-as-closed'] ) && ! empty( $_REQUEST['gs-order-ids'] ) ) {
		$purchase_log_ids = array_map( 'intval', $_REQUEST['gs-order-ids'] );
		$tracking_id_to_set = isset( $_REQUEST['gs-tracking-ref'] ) ? $_REQUEST['gs-tracking-ref'] : false;
		foreach ( $purchase_log_ids as $purchase_log_id ) {
			wpsc_purchlog_edit_status( $purchase_log_id, WPSC_Purchase_Log::CLOSED_ORDER  );
			if ( $tracking_id_to_set !== false ) {
				pbci_gs_purchase_log_save_tracking_id( $purchase_log_id, $tracking_id_to_set );
			}
		}
	}

	$url = '?page=' . $_REQUEST['page'] ;

	if ( isset( $_REQUEST['statum'] ) ) {
		$statum_clause = ' AND processed=' . $_REQUEST['statum'] . ' ';
		$status_name = pbci_gs_get_status_name( $_REQUEST['statum'] );
	} else {
		$statum_clause = '';
		$status_name = '';
	}

	global $wpdb;
	$sql = 'SELECT id FROM ' . WPSC_TABLE_PURCHASE_LOGS
	        . ' WHERE (shipping_option = "' . $group_ship . '" ) '
	            . $statum_clause
					. ' AND (processed NOT IN (' . pbci_gs_exclude_status_string() . ') ) '
						. ' ORDER BY ID' ;

	$purchase_log_ids = $wpdb->get_col( $sql , 0 );

	?>
	<h2><?php echo $status_name;?> Status for Shipping Option <?php echo $group_ship;?></h2>

	<a class="back-link" href="<?php echo $url;?>" title="back">&larr;Back</a>&nbsp;
	<a id="<?php echo urlencode($group_ship);?>" href="#" class="print-packing-list-popup print-link">Print</a>

	<hr>
	<form method="post">
	<table  class="widefat gs gs-order-status gs-admin-table-left">
		<tr class="heading-row">
			<th>
				<input id="all-order-ids" type="checkbox" name="all-order-ids" value="1">&nbsp;Order&nbsp;ID
			</th>

			<th>
				Date
			</th>

			<th>
				Name
			</th>

			<th>
				Items
			</th>

			<th>
				Status
			</th>
			<th>
				Track Ref.
			</th>

		</tr>
	<?php
	foreach ($purchase_log_ids as $purchase_log_id ) {
		$purchase_log = new WPSC_Purchase_Log( $purchase_log_id );
		$cart_contents = $purchase_log->get_cart_contents();
		$checkout_form_data = new WPSC_Checkout_Form_Data( $purchase_log_id );
		$firstname = trim( $checkout_form_data->get( 'billingfirstname' ) );
		$lastname  = trim( $checkout_form_data->get( 'billinglastname' ) );

		?>
		<tr>
			<td class="gs-order-id"><input  class="gs-order-id-checkbox" type="checkbox" name="gs-order-ids[]" value="<?php echo $purchase_log_id;?>">&nbsp;<?php pbci_gs_sales_log_link( $purchase_log_id );?></td>
			<td class="gs-date"><?php echo date_i18n('M d, Y',$purchase_log->get( 'date' ));?></td>
			<td class="gs-name"><span class="ppi"><?php echo ucwords($lastname . ', ' . $firstname);?></span></td>
			<td class="gs-item-count"><?php echo count($cart_contents);?></td>
			<td class="gs-status"><?php echo pbci_gs_get_purchase_order_status( $purchase_log );?></td>
			<td class="gs-track-id"><?php echo $purchase_log->get( 'track_id' );?></td>
			</tr>
		<?php
	}

	?>
	</table>

	<div class="gs-action-buttons">
		<label for="gs-tracking-ref">Tracking Ref:</label>&nbsp;<input type="text" id="gs-tracking-ref" name="gs-tracking-ref" size="30">
		<?php submit_button( 'Mark As Closed', 'primary', 'gs-mark-as-closed', false ); ?>
	</div>

	</form>

	<?php
	$summaries = array();
	$statii = pbci_gs_init_purchase_status_counts();

	foreach ($purchase_log_ids as $purchase_log_id ) {

		$purchase_log = new WPSC_Purchase_Log( $purchase_log_id );
		$cart_contents = $purchase_log->get_cart_contents();
		$option = $purchase_log->get( 'shipping_option' );
		$status = pbci_gs_get_purchase_order_status( $purchase_log );

		if ( !in_array( $status, $statii ) ) {
			$statii[] = $status;
		}

		if ( !isset( $summaries[$option] ) ) {
			$summaries[$option] = array();
		}

		if ( !isset( $summary[$option][$status] ) ) {
			$summaries[$option][$status] = 0;
		}

		$summaries[$option][$status] =+ count( $cart_contents );
	}


	?>

	<hr>
	<div class="page-break"></div>
	<h3>Purchases Product Details</h3>
	<table  class="widefat gs gs-product-list gs-admin-table-left">
		<tr class="heading-row">
			<th>
				Order&nbsp;ID
			</th>

			<th>
				Quantity
			</th>

			<th>
				Name
			</th>

			<th>
				Item
			</th>
		</tr>

	<?php

	$summaries = array();
	$products = array();

	foreach ($purchase_log_ids as $purchase_log_id ) {

		$purchase_log = new WPSC_Purchase_Log( $purchase_log_id );
		$cart_contents = $purchase_log->get_cart_contents();

		$checkout_form_data = new WPSC_Checkout_Form_Data( $purchase_log_id );
		$firstname = trim( $checkout_form_data->get( 'billingfirstname' ) );
		$lastname  = trim( $checkout_form_data->get( 'billinglastname' ) );

		foreach ( $cart_contents as $cart_item ) {
			?>
			<tr>
				<td><?php pbci_gs_sales_log_link( $purchase_log_id );?></td>
				<td><?php echo $cart_item->quantity;?></td>
				<td><span class="ppi"><?php echo ucwords($lastname . ', ' . $firstname);?></span></td>
				<td>
					<?php echo esc_html( $cart_item->name );?>
					<?php
					$custom_messages = apply_filters( 'pbci_get_cart_item_extra_message', '', $cart_item, $purchase_log_id );
					if ( ! empty( $custom_messages ) ) {
						foreach( $custom_messages as $custom_message )
						if ( ! empty( $custom_message ) ) {
							echo '<br>' . $custom_message;
						}
					}
					?>
				</td>
			</tr>
			<?php
			$option = $purchase_log->get( 'shipping_option' );
			$status = pbci_gs_get_purchase_order_status( $purchase_log );

			if ( !in_array( $status, $statii ) ) {
				$statii[] = $status;
			}

			if ( !isset( $products['product_id'] ) ) {
				$products['product_id'] = get_the_title( $cart_item->prodid );
			}

			$summaries[$option][$status] =+ count( $cart_contents );
		}
	}

	?>
	</table>

	<hr>
	<div class="page-break"></div>
	<br>
	<div class="mailing-labels-links">
		<input type="hidden" id="group-ship" value="<?php echo urlencode($group_ship);?>">
		<a style="float:right;" href="#" class="mailing-labels-popup">Mailing Labels</a>
	</div>
	<br>
	<hr>
	<?php
}


function pbci_gs_sales_log_link( $id ) {
	echo 	"<a href='?page=wpsc-purchase-logs&c=item_details&id={$id}'>{$id}</a>";
}

function pbci_gs_item_link( $id ) {
	edit_post_link( $id , '', '', $id );
}