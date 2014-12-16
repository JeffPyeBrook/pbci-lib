<?php
/*
 Plugin Name: Delivery Pro Shipping for WP-eCommerce
 Plugin URI: www.pyebrook.com/delivery-pro
 Description: Flexible Shipping, Pick-Up and Delivery Options for WP-eCommerce Stores
 Version: 3.0
 Author: PBCI / Jeffrey Schutzman
 Author URI: http://www.pyebrook.com
*/

require __DIR__ . '/vendor/pyebrook/pbci-lib/pbci-lib.php';

if ( is_admin() ) {
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin.php' ) ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin.php' );
	}

	if ( file_exists( plugin_dir_path( __FILE__ ) . 'settings.php' ) ) {
		include_once( plugin_dir_path( __FILE__ ) . 'settings.php' );
	}

}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'geo.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'geo.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'quoter.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'quoter.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/pbci-metabox.class.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/pbci-metabox.class.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/shipping-option-settings.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/shipping-option-settings.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/between-dates.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/between-dates.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/between-times.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/between-times.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/within-distance.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/within-distance.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/with-keyword.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/with-keyword.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'meta-boxes/filters.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'meta-boxes/filters.php' );
}


if ( is_admin() ) {
	do_action( 'pbci_gs_setup_ship_mb' );
}


function pbci_group_shipping_post_type() {

		$labels = array(
				'name' => pbci_gs_module_name(),
				'singular_name' => pbci_gs_module_name() . ' Shipping Option',
				'add_new' => 'Add New ' . pbci_gs_module_name() . ' Shipping Option',
				'add_new_item' => 'Add New ' . pbci_gs_module_name() . ' Shipping Option',
				'edit_item' => 'Edit ' . pbci_gs_module_name() . ' Shipping Option',
				'new_item' => 'New ' . pbci_gs_module_name() . ' Shipping Option',
				'view_item' => 'View ' . pbci_gs_module_name() . ' Shipping Option',
				'search_items' => 'Search ' . pbci_gs_module_name() . ' Shipping Options',
				'not_found' => 'No ' . pbci_gs_module_name() . ' Shipping Option' . ' found',
				'not_found_in_trash' => 'No ' . pbci_gs_module_name() . ' Shipping Options' . ' found in Trash',
				'parent_item_colon' => '',
		);

		$args = array(
				'menu_icon' => plugin_dir_url( __FILE__ ).'assets/pye-brook-logo-delivery-pro-wpec-16.png',
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'query_var' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'supports' => array( 'title' ),
//				'taxonomies' => array( 'product_tag', 'wpsc_product_category' ),
//				'register_meta_box_cb' => 'add_groupship_metaboxes'
		);

		register_post_type(  pbci_gs_post_type(), $args );

}

add_action( 'wpsc_register_taxonomies_after', 'pbci_group_shipping_post_type', 99 );

function save_group_shipping( $post_id, $post, $update ) {

	if (  pbci_gs_post_type() != $post->post_type ) {
		return;
	}

	if ( isset( $_POST['design-theme'] ) ) {
		update_post_meta( $post_id, 'design-theme',  $_POST['design-theme'] );
	}

}

add_action( 'save_post', 'save_group_shipping', 10, 3 );

function pbci_gs_post_type() {
	return 'group-shipping';
}

function pbci_gs_module_name() {
	return 'Delivery Pro';
}


$plugin = plugin_basename(__FILE__);
add_filter( 'plugin_action_links_' . $plugin, 'pbci_gs_settings_link' );


function pbci_gs_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=pbci_gs_options_page">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}

if ( is_admin() ) {
	add_action('admin_menu', 'pbci_gs_settings_menu');
}


function pbci_gs_settings_menu() {
	add_options_page( 'Delivery Pro', 'Delivery Pro', 'manage_options', 'pbci_gs_options_page', 'pbci_gs_options_page' );
}

