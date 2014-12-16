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

function pbci_gs_packing_list() {
	?>
	<div class="wrap">
		<?php //screen_icon(); ?>
		<?php if ( isset( $_REQUEST['gs'] ) ) {
			pbci_gs_get_purchases_table( $_REQUEST['gs'] );
		} else {
			pbci_gs_get_purchases_summary_table();
		}
		?>
	</div>
	<?php
}


/**
 * Retrieve or display list of pages as a dropdown (select list).
 *
 * @since 2.1.0
 *
 * @param array|string $args Optional. Override default arguments.
 * @return string HTML content, if not displaying.
 */
function wp_dropdown_shipping( $echo = true ) {

	$args = array(
			'post_type' =>  pbci_gs_post_type(),
			'post_status' => 'publish',
	);

	$group_ships = new WP_Query( $args );

	$output = '';

	$output = '<select name="group-ship" id="group-ship">';
	$output .= "\t<option value=\"-1\">None</option>";

	foreach ( $group_ships->posts as $group_ship ) {
		$n = $group_ship->post_title;
		$i = $group_ship->ID;
		$output .= '<option value="' . esc_attr($i) . '">' . $n . '</option>';
	}

	$output .= '</select>';

	if ( $echo )
		echo $output;

	return $output;
}

function pbci_gs_get_purchase_statii() {
	global $wpsc_purchlog_statuses;

	$statti = array('Shipped');

	foreach( $wpsc_purchlog_statuses as $index => $info ) {
		$statti[$index] = $info['label'];
	}

	return $statti;
}

function pbci_gs_get_status_id( $status_name ) {
	$id = -1;
	global $wpsc_purchlog_statuses;

	foreach( $wpsc_purchlog_statuses as $index => $info ) {
		if ( $info['label'] == $status_name ) {
			$id = absint( $info['order'] );
			break;
		}
	}

	return $id;
}


function pbci_gs_get_status_name( $status_id ) {
	$label = '';
	global $wpsc_purchlog_statuses;

	foreach( $wpsc_purchlog_statuses as $index => $info ) {
		if ( $info['order'] == $status_id ) {
			$label = $info['label'];
			break;
		}
	}

	return $label;
}


function pbci_gs_init_purchase_status_counts() {
	global $wpsc_purchlog_statuses;

	$list = pbci_gs_status_list();

	foreach( $list as $id ) {
		$statti[ pbci_gs_get_status_name( $id ) ] = 0;
	}

	return $statti;
}

function pbci_gs_get_purchase_order_status ( $purchase_log ) {
	$order_status = '';
	global $wpsc_purchlog_statuses;

	$track_id = $purchase_log->get( 'track_id' );
	if ( !empty( $track_id ) ) {
		$order_status = __( 'Shipped', 'pbci_gs' );
	} else {
		$order_status = wpsc_find_purchlog_status_name( $purchase_log->get( 'processed') );
	}

	return $order_status;
}

function pbci_gs_get_purchases_summary_table() {
	?>
	<h2><?php echo pbci_gs_module_name();?></h2>
	<hr>
	<?php

	global $wpdb;

	$sql = 'SELECT id FROM ' . WPSC_TABLE_PURCHASE_LOGS . ' WHERE shipping_method = "pbci_group_shipping" ORDER BY shipping_option'  ;
	$purchase_log_ids = $wpdb->get_col( $sql , 0 );

	$count = 0;

	$summaries = array();
	$item_counts = array();
	$statii = pbci_gs_init_purchase_status_counts();

	foreach ($purchase_log_ids as $purchase_log_id ) {

		$purchase_log = new WPSC_Purchase_Log( $purchase_log_id );
		$purchaser_user_id = $purchase_log->get( 'user_ID' );
		$cart_contents = $purchase_log->get_cart_contents();
		$option = $purchase_log->get( 'shipping_option' );
		$status = pbci_gs_get_purchase_order_status( $purchase_log );

		if ( !in_array( $status, $statii ) ) {
			$statii[] = $status;
		}

		if ( !isset( $summaries[$option] ) ) {
			$summaries[$option] = array();
			$item_counts[$option] = array();
		}

		if ( !isset( $summaries[$option][$status] ) ) {
			$summaries[$option][$status] = 0;
			$item_counts[$option][$status] = 0;
		}

		$item_counts[$option][$status] += count( $cart_contents );
		$summaries[$option][$status] ++;
	}

	?>
	<h3>All Purchases Summary</h3>
	<table class="widefat gs-summary gs-admin-table gs">
	<tr class="heading-row">
	<th>Shipping Option</th>
		<?php  foreach ( $statii as $statum => $count ) { ?>
			<th class="rotate"><div><span><?php echo $statum;?></span></div></th>
		<?php } ?>
	</tr>

	<?php
	foreach ( $summaries as $group_ship => $summary ) {
		?>
		<tr>
			<?php $url = '?page=' . $_REQUEST['page'] . '&' . 'post_type=' . $_REQUEST['post_type'] . '&' . 'gs=' . urlencode($group_ship); ?>
			<td><a href="<?php echo $url;?>"><?php echo $group_ship;?></a></td>
			<?php  foreach ( $statii as $statum => $count ) { ?>
				<td><?php echo isset( $summary[$statum] ) ? $summary[$statum] : '';?></td>
			<?php } ?>
		</tr>
		<?php
	}
	?>
	</table>

	<hr>

	<h3>All Purchases Item Summary</h3>
	<table class="widefat gs-summary gs-admin-table gs">
	<tr class="heading-row">
	<th>Shipping Option</th>
		<?php  foreach ( $statii as $statum => $count ) { ?>
			<th><?php echo $statum;?></th>
		<?php } ?>
	</tr>

	<?php
	foreach ( $item_counts as $group_ship => $summary ) {
		?>
		<tr>
			<?php $url = '?page=' . $_REQUEST['page'] . '&' . 'post_type=' . $_REQUEST['post_type'] . '&' . 'gs=' . urlencode($group_ship); ?>
			<td><a href="<?php echo $url;?>"><?php echo $group_ship;?></a></td>
			<?php  foreach ( $statii as $statum => $count ) { ?>
				<td>
					<?php if (  ! empty ( $summary[$statum] ) ) { ?>
						<a href="<?php echo $url . '&statum=' . pbci_gs_get_status_id( $statum );?>" > <?php echo urlencode( $summary[$statum] );?></a>
					<?php } ?>
				</td>
			<?php } ?>
		</tr>
		<?php
	}
	?>
	</table>
	<hr>

	<?php
}


