<?php
/*
 * * Copyright 2010-2013, Pye Brook Company, Inc. * * Licensed under the Pye
 * Brook Company, Inc. License, Version 1.0 (the "License"); * you may not use
 * this file except in compliance with the License. * You may obtain a copy of
 * the License at * * http://www.pyebrook.com/ * * This software is not free may
 * not be distributed, and should not be shared. It is governed by the * license
 * included in its original distribution (license.pdf and/or license.txt) and by
 * the * license found at www.pyebrook.com. * This software is copyrighted and
 * the property of Pye Brook Company, Inc. * * See the License for the specific
 * language governing permissions and * limitations under the License. * *
 * Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
 */
include_once ( plugin_dir_path( __FILE__ ) . 'packing-lists.php' );
include_once ( plugin_dir_path( __FILE__ ) . 'order-summary.php' );
include_once ( plugin_dir_path( __FILE__ ) . 'mailing-labels.php' );

function pbci_group_shipping_admin_init() {
	add_meta_box( 'pbci_group_shipping_meta_box', 'This Group Ship Available for Orders Between These Dates', 'pbci_group_shipping_meta_box', 'group-shipping', 'normal', 'high' );

	wp_register_script( 'group-ship-admin', plugins_url( 'group-shipping-admin.js', __FILE__ ), array(), false, false );
	wp_localize_script( 'group-ship-admin', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'group-ship-admin' );

	add_submenu_page( 'edit.php?post_type=group-shipping', 'Packing Lists', 'Packing Lists', 'edit_posts', 'pbci_gs_packing_list', 'pbci_gs_packing_list' );
}

add_action( 'admin_menu', 'pbci_group_shipping_admin_init', 11 );

function pbci_group_shipping_meta_box( $post ) {
	$id = $post->ID;

	// //////////////////////////////////////////////////////////////////
	// get some default times
	$datetime = new DateTime( $post->post_date );
	$start_date = $datetime->format( 'Y-m-d' );
	$x = strtotime( '+7 days', strtotime( $start_date ) );
	$end_date = date( 'Y-m-d', $x );
	$start_time = '00:00';
	$end_time = '23:59';

	// //////////////////////////////////////////////////////////////////
	// If there are saved times we can work with them
	$start_date = $post->post_date;

	$saved_start_date = get_post_meta( $id, 'start_date', true );
	$saved_end_date = get_post_meta( $id, 'end_date', true );
	$cost = get_post_meta( $id, 'cost', true );

	if ( ! empty( $saved_start_date ) ) {
		try {
			$datetime = new DateTime( $saved_start_date );
			$start_date = $datetime->format( 'Y-m-d' );
			$start_time = $datetime->format( 'H:i' );
		} catch ( Exception $e ) {
			bling_log( 'malformed start date or time' );
		}
	}

	if ( ! empty( $saved_end_date ) ) {
		try {
			$datetime = new DateTime( $saved_end_date );
			$end_date = $datetime->format( 'Y-m-d' );
			$end_time = $datetime->format( 'H:i' );
		} catch ( Exception $e ) {
			bling_log( 'malformed end date or time' );
		}
	}

	?>
	<p>
	<label for="startdate">Start Date:</label> <input class="mydatepicker"
		type="text" id="startdate" name="startdate"
		value="<?php echo $start_date;?>">
	</p>
	<p>
	<label for="starttime">Start Time:</label> <input type="text"
		id="starttime" name="starttime" readonly="readonly"
		value="<?php echo $start_time;?>">
	</p>

	<p>
	<label for="enddate">End Date:</label> <input class="mydatepicker"
		type="text" id="enddate" name="enddate"
		value="<?php echo $end_date;?>">
	</p>

	<p>
	<label for="endtime">End Time:</label> <input type="text" id="endtime"
		name="endtime" readonly="readonly" value="<?php echo $end_time;?>">
	</p>

	<p>
	<label for="cost">Cost:</label> <input type="text" id="cost"
		name="cost" value="<?php echo $cost;?>">
	</p>

	<input type="hidden" id="groupshipid" name="id"
		value="<?php echo $id;?>">
	<?php
}

function pbci_group_shipping_meta_box_save( $id ) {
	if ( get_post_type( $id ) != 'group-shipping' )
		return;

	update_post_meta( $id, 'start_date', $_POST ['startdate'] . ' ' . $_POST ['starttime'] );
	update_post_meta( $id, 'end_date', $_POST ['enddate'] . ' ' . $_POST ['endtime'] );
	update_post_meta( $id, 'cost', $_POST ['cost'] );
}

add_action( 'save_post', 'pbci_group_shipping_meta_box_save' );

function pbci_gs_admin_register_head() {
	$siteurl = get_option( 'siteurl' );
	$url = $siteurl . '/wp-content/plugins/' . basename( dirname( __FILE__ ) ) . '/admin.css';
	echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}

if ( is_admin() ) {
	add_action( 'admin_head', 'pbci_gs_admin_register_head' );
}

