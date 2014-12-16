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


class pbci_group_shipping {

	public $internal_name;
	public $name;
	protected $shipping_option_post_ids = false;

	function __construct( $method_name = 'Delivery Options' ) {

		// An internal reference to the method - must be unique!
		$this->internal_name = sanitize_title( $method_name );

		// $this->name is how the method will appear to end users
		$this->name        = $method_name;
		$this->is_external = false;

		return true;
	}

	function getName() {
		return $this->name;
	}

	function getInternalName() {
		return $this->internal_name;
	}

	function get_shipping_option_ids() {

		if ( ! $this->shipping_option_post_ids ) {
			$shipping_groups = get_option( 'pbcs_gs_shipping_groups', array() );

			if ( isset( $shipping_groups[ $this->getName() ] ) ) {
				$this->shipping_option_post_ids = $shipping_groups[ $this->getName() ];
			}
		}

		return $this->shipping_option_post_ids;
	}


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


	function getQuote() {

		global $wpsc_cart;

		$shipping_quotes = array();

		$shipping_option_ids = $this->get_shipping_option_ids();

		foreach ( $shipping_option_ids as $shipping_method_id ) {
				$settings_mb = new GS_Metabox_Shipping_Method_Settings( 'Settings', pbci_gs_post_type() );

				if ( $settings_mb->get_option( $shipping_method_id, 'enabled' ) ) {
					$applies = apply_filters( 'pbci_gs_check_condition', true, $shipping_method_id, $wpsc_cart );
					if ( $applies ) {
						$shipping_method_name = get_the_title( $shipping_method_id );

						$cost = floatval( $settings_mb->get_option( $shipping_method_id, 'cost' ) );

						if ( empty( $cost ) ) {
							$cost = 0.00;
						}

						$shipping_quotes[ $shipping_method_name ] = $cost;
					}
				}
		}

		return $shipping_quotes;

	}

	/**
	 *
	 *
	 * @param unknown $cart_item (reference)
	 *
	 * @return unknown
	 */
	function get_item_shipping( &$cart_item ) {
		return 0;
	}

}

$methods = array();

function pbci_group_shipping_add( $wpsc_shipping_modules ) {

	$shipping_groups = get_option( 'pbcs_gs_shipping_groups', array() );

	foreach ( $shipping_groups as $shipping_group => $shipping_ids ) {
		$shipping = new pbci_group_shipping( $shipping_group );
		$wpsc_shipping_modules[ $shipping->getInternalName() ] = $shipping;
	}
	return $wpsc_shipping_modules;
}


function pbci_gs_update_shipping_method_names( $post_id ) {

	$post_type = get_post_type( $post_id );
	if ( $post_type != pbci_gs_post_type() ) {
		return;
	}

	$shipping_methods = array();

	$ids     = pbci_gs_get_active_shipping_method_ids();

	foreach ( $ids as $shipping_method_id ) {
		$settings_mb = new GS_Metabox_Shipping_Method_Settings( 'Settings', pbci_gs_post_type() );
		if ( $settings_mb->get_option( $shipping_method_id, 'enabled' ) ) {
			$method_name = trim( $settings_mb->get_option( $shipping_method_id, 'group' ) );

			if ( empty( $method_name ) ) {
				$method_name = 'Delivery Options';
			}

			if ( ! isset( $shipping_methods[$method_name] ) ) {
				$shipping_methods[$method_name] = array();
			}

			if ( ! in_array( $shipping_method_id, $shipping_methods[$method_name] ) ) {
				$shipping_methods[ $method_name ][] = $shipping_method_id;
			}
		}
	}

	update_option( 'pbcs_gs_shipping_groups', $shipping_methods );
}

add_action( 'save_post', 'pbci_gs_update_shipping_method_names' );

add_filter( 'wpsc_shipping_modules', 'pbci_group_shipping_add' );

if ( ! is_admin() ) {
	//add_action( 'wpsc_before_get_shipping_method', 'pbci_group_shipping_add_wrapper' );
	//add_action( 'wpsc_after_get_shipping_method', 'pbci_group_shipping_remove_wrapper' );
}


function pbci_gs_get_active_shipping_method_ids() {
	$args = array(
		'post_type' => pbci_gs_post_type(),
		'fields'    => 'ids',
	);

	$query = new WP_Query( $args );

	return $query->posts;
}

add_filter( 'option_' . 'custom_shipping_options', 'pbci_gs_add_custom_options', 10 , 1 );

function pbci_gs_add_custom_options( $option_value ) {
	$shipping_methods = get_option( 'pbcs_gs_shipping_groups', array() );

	foreach( $shipping_methods as $method => $method_ids ) {
		$method_slug = sanitize_title( $method );
		if ( ! in_array( $method_slug,$option_value  ) ) {
			$option_value[] = $method_slug;
		}
	}

	pbci_log( 'added special shipping options to enabled methodws list' );
	return $option_value;
}