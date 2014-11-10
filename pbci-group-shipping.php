<?php
/*
 Plugin Name: PBCI Group Shipping
 Plugin URI:
 Description: Group Shipping Options
 Version: 1.1
 Author: PBCI / Jeffrey Schutzman
 Author URI:
*/


if ( is_admin() ) {
	include_once( plugin_dir_path( __FILE__ ) . 'admin.php' );
}


class pbci_group_shipping {

	var $internal_name, $name;

	/**
	 *
	 *
	 * @return unknown
	 */
	function pbci_group_shipping() {

		// An internal reference to the method - must be unique!
		$this->internal_name = "pbci_group_shipping";

		// $this->name is how the method will appear to end users
		$this->name = "Sparkle Gear Partner Pickup";

		// Set to FALSE - doesn't really do anything :)
		$this->is_external = TRUE;

		$result = add_filter( 'cart_eligible_for_free_shipping', array( &$this, 'cart_eligible_for_group_shipping' ) , 10, 2 );

		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_js') );

		return true;

	}

	function enqueue_js( ) {
	    wp_register_script( 'pbci_gs', plugin_dir_url( __FILE__ ) .  'group-shipping-admin.js' );
	    wp_localize_script( 'pbci_gs', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    	wp_enqueue_script( 'pbci_gs' );

	}



	function cart_eligible_for_group_shipping( $is_eligible, $wpsc_cart ) {

		if ( !function_exists('bling_get_design_id') )
			return;

		global $wpdb;

		if ( is_array ( $wpsc_cart->cart_items ) && !empty( $wpsc_cart->cart_items ) ) {
			foreach ( $wpsc_cart->cart_items as $cart_item ) {
				$product_id = $cart_item->product_id;
				$design_id = bling_get_design_id( $product_id );

				if ( ! empty ( $design_id ) ) {
					$design_themes = wp_get_object_terms( $design_id, 'bling_design_theme'  );
					$design_theme_ids = wp_get_object_terms( $design_id, 'bling_design_theme' ,  array( 'fields' => 'ids' ) );
					foreach ( $design_themes as $design_theme ) {
						if ( $design_theme->parent == 0 )
							continue;

						$design_theme_id = $design_theme->term_id;

						$args = array(
								'post_type' => 'group-shipping',
								'fields'    => 'ids',
								'tax_query' => array(
										array(
												'taxonomy' => 'bling_design_theme',
												'field' => 'id',
												'terms' => intval($design_theme_id),
										),
								)
						);

						$results = new WP_Query( $args );
						foreach ( $results->posts as $group_ship_id ) {
							$saved_start_date = get_post_meta($group_ship_id, 'start_date', true );
							$saved_end_date = get_post_meta($group_ship_id, 'end_date', true );

							try {
								$datetime = new DateTime($saved_start_date);
								$start_date = strtotime ($saved_start_date);// $datetime->getTimestamp();
							} catch (Exception $e) {
								bling_log( 'malformed start date or time: '. $saved_start_date);
							}

							try {
								$datetime = new DateTime($saved_end_date);
								$end_date = strtotime($saved_end_date);//$datetime->getTimestamp();
							} catch (Exception $e) {
								bling_log( 'malformed start date or time: '. $saved_end_date);
							}

							$now = time();

							if ( $now >= $start_date  && $now <= $end_date ) {
								$is_eligible = true;
							}
						}
					}
				}
			}
		}

		return $is_eligible;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	function getName() {
		return $this->name;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	function getInternalName() {
		return $this->internal_name;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function getForm() {
// 		$free_priority_shipping_threshold = get_option( 'free_priority_shipping_threshold', '50.0');

// 		ob_start();
// 		?>

		<?php

		return '';
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	function submit_form() {
// 		if (  ! isset( $_POST['free_priority_shipping_threshold'] ) )
// 			return false;

// 		$free_priority_shipping_threshold = $_POST['free_priority_shipping_threshold'];
// 		update_option( 'free_priority_shipping_enabled', $free_priority_shipping_enabled );
		return true;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	function getQuote() {

		if ( !function_exists('bling_get_design_id') )
			return;

		global $wpdb, $wpsc_cart;
		//bling_log(get_class().'::'.__FUNCTION__);

		$shipping_quotes = array();

		if ( is_array ( $wpsc_cart->cart_items ) && !empty( $wpsc_cart->cart_items ) ) {
			foreach ( $wpsc_cart->cart_items as $cart_item ) {
				$product_id = $cart_item->product_id;
				$design_id = bling_get_design_id( $product_id );
				$design_title = get_the_title( $design_id );
				if ( !empty ( $design_id ) ) {
					$design_themes = wp_get_object_terms ( $design_id, 'bling_design_theme'  );
					$design_theme_ids = wp_get_object_terms ( $design_id, 'bling_design_theme' ,  array( 'fields'=>'ids') );
					foreach ( $design_themes as $design_theme ) {
						if ( $design_theme->parent == 0 )
							continue;

						$design_theme_id = $design_theme->term_id;

						$args = array(
								'post_type' => 'group-shipping',
								'fields'    => 'ids',
								'tax_query' => array(
										array(
												'taxonomy' => 'bling_design_theme',
												'field' => 'id',
												'terms' => intval($design_theme_id),
										),
								)
						);

						$results = new WP_Query( $args );
						foreach ( $results->posts as $group_ship_id ) {
							$saved_start_date = get_post_meta($group_ship_id, 'start_date', true );
							$saved_end_date = get_post_meta($group_ship_id, 'end_date', true );

							try {
								$datetime = new DateTime($saved_start_date);
								$start_date = strtotime ($saved_start_date);// $datetime->getTimestamp();
							} catch (Exception $e) {
								bling_log( 'malformed start date or time: '. $saved_start_date);
							}

							try {
								$datetime = new DateTime($saved_end_date);
								$end_date = strtotime($saved_end_date);//$datetime->getTimestamp();
							} catch (Exception $e) {
								bling_log( 'malformed start date or time: '. $saved_end_date);
							}

							$now = time();

							if ( $now >= $start_date  && $now <= $end_date ) {
								$group_ship_name = get_the_title( $group_ship_id );
								$group_ship_cost = get_post_meta( $group_ship_id, 'cost', true );
								$shipping_quotes[$group_ship_name] = $group_ship_cost;
							}
						}
					}
				}
			}
		}


		return $shipping_quotes;

	}

/**
	 *
	 *
	 * @param unknown $cart_item (reference)
	 * @return unknown
	 */
	function get_item_shipping(&$cart_item) {
		return 0;
	}

}

function pbci_group_shipping_post_type() {

		$labels = array(
				'name' => 'Group Shipping',
				'singular_name' => 'Group Shipping',
				'add_new' => 'Add New Group Shipping',
				'add_new_item' => 'Add New Group Shipping',
				'edit_item' => 'Edit Group Shipping',
				'new_item' => 'New Group Shipping',
				'view_item' => 'View Group Shipping',
				'search_items' => 'Search Group Shipping',
				'not_found' => 'No Group Shipping found',
				'not_found_in_trash' => 'No Group Shipping found in Trash',
				'parent_item_colon' => '',
		);

		$args = array(
				'menu_icon' => plugin_dir_url( __FILE__ ).'pye-brook-logo-16-16.png',
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'query_var' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'supports' => array( 'title' ),
				'taxonomies' => array( 'bling_design_theme' ),
		);

		register_post_type( 'group-shipping', $args );

}

add_action( 'wpsc_register_taxonomies_after', 'pbci_group_shipping_post_type', 99 );


function pbci_group_shipping_add( $wpsc_shipping_modules ) {
	$rates = new pbci_group_shipping();
	$wpsc_shipping_modules[$rates->getInternalName()] = $rates;
	return $wpsc_shipping_modules;
}

add_filter( 'wpsc_shipping_modules', 'pbci_group_shipping_add' );

