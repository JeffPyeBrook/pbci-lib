<?php
/*
 Plugin Name: PBCI Group Shipping
 Plugin URI:
 Description: Group Shipping Options
 Version: 2.0
 Author: PBCI / Jeffrey Schutzman
 Author URI:
*/


if ( is_admin() ) {
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin.php' ) ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin.php' );
	}
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'geo.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'geo.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'quoter.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'quoter.php' );
}




function pbci_group_shipping_post_type() {

		$labels = array(
				'name' => pbci_gs_module_name(),
				'singular_name' => pbci_gs_module_name(),
				'add_new' => 'Add New ' . pbci_gs_module_name(),
				'add_new_item' => 'Add New ' . pbci_gs_module_name(),
				'edit_item' => 'Edit ' . pbci_gs_module_name(),
				'new_item' => 'New ' . pbci_gs_module_name(),
				'view_item' => 'View ' . pbci_gs_module_name(),
				'search_items' => 'Search ' . pbci_gs_module_name(),
				'not_found' => 'No ' . pbci_gs_module_name() . ' found',
				'not_found_in_trash' => 'No ' . pbci_gs_module_name() . ' found in Trash',
				'parent_item_colon' => '',
		);

		$args = array(
				'menu_icon' => plugin_dir_url( __FILE__ ).'pye-brook-logo-16-16.png',
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
				'register_meta_box_cb' => 'add_groupship_metaboxes'
		);

		register_post_type(  pbci_gs_post_type(), $args );

}

add_action( 'wpsc_register_taxonomies_after', 'pbci_group_shipping_post_type', 99 );

function add_groupship_metaboxes() {
    add_meta_box('group_shipping_theme', 'Group Shipping For', 'group_shipping_callback',   pbci_gs_post_type(), 'side', 'high');
}

// The Event Location Metabox

function group_shipping_callback( $post ) {

	global $post;

	$current = get_post_meta( $post->ID, 'design-theme',  true );

	$term = get_term_by( 'name', 'Our Gear', 'design-theme' );

	$args = array(
		'show_option_all'    => '',
		'show_option_none'   => '',
		'orderby'            => 'name',
		'order'              => 'ASC',
		'show_count'         => 0,
		'hide_empty'         => 0,
		'child_of'           => $term->term_id,
		'exclude'            => '',
		'echo'               => 1,
		'selected'           => $current,
		'hierarchical'       => 0,
		'name'               => 'design-theme',
		'id'                 => '',
		'class'              => 'postform',
		'depth'              => 0,
		'tab_index'          => 0,
		'taxonomy'           => 'design-theme',
		'hide_if_empty'      => false,
	);




	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	wp_dropdown_categories( $args );
	?>
	<form action="<?php bloginfo('url'); ?>/" method="get">
		<div>
			<?php

			?>
			<noscript><div><input type="submit" value="View" /></div></noscript>
		</div>
	</form>
	<?php

}


function save_group_shipping( $post_id, $post, $update ) {

	// If this isn't a 'book' post, don't update it.
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
	return 'Group Shipping';
}