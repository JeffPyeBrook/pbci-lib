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

if ( file_exists( plugin_dir_path( __FILE__ ) . 'packing-lists.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'packing-lists.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'order-summary.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'order-summary.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'mailing-labels.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'mailing-labels.php' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'wpec-hooks.php' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'wpec-hooks.php' );
}


function pbci_group_shipping_admin_init() {

	$timestamp = filemtime( plugin_dir_path( __FILE__ ) . 'script/admin.css' );
	wp_register_style( 'gs-admin-style', plugin_dir_url( __FILE__ ) . 'script/admin.css', false, $timestamp );
	wp_enqueue_style( 'gs-admin-style' );

	wp_register_script( 'group-ship-admin', plugins_url( 'script/group-shipping-admin.js', __FILE__ ), array(), false, false );
	wp_localize_script( 'group-ship-admin', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'group-ship-admin' );

	wp_register_script( 'group-ship-admin', plugins_url( 'script/group-shipping-admin.js', __FILE__ ), array(), false, false );
	wp_localize_script( 'group-ship-admin', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'group-ship-admin' );


//	wp_register_script(
//		'gs-datetimepicker',
//		plugins_url( 'script/datetimepicker.js', __FILE__ ),
//		array( 'jquery', 'jquery-ui-datepicker' ),
//		false,
//		false
//	);
//
//	wp_enqueue_script( 'gs-datetimepicker' );

	wp_register_script(
		'jquery-ui-timepicker-addon',
		plugins_url( 'script/jquery-ui-timepicker-addon.js', __FILE__ ),
		array( 'jquery', 'jquery-ui-datepicker' ),
		false,
		false
	);

	wp_enqueue_script( 'jquery-ui-timepicker-addon' );

	add_submenu_page( 'edit.php?post_type=group-shipping', 'Packing Lists', 'Packing Lists', 'edit_posts', 'pbci_gs_packing_list', 'pbci_gs_packing_list' );

	$admin_capability = apply_filters( 'wpsc_purchase_logs_cap', 'administrator' );
	add_submenu_page( 'index.php', __( 'Delivery Pro' ), __(  'Delivery Pro', 'wpsc' ), $admin_capability, 'pbci_gs_packing_list', 'pbci_gs_packing_list' );


}

add_action( 'admin_menu', 'pbci_group_shipping_admin_init', 11 );


function pbci_gs_mb_settings( $post ) {
	$id = $post->ID;

	$shipping_method_name = get_post_meta( $id, '_shipping_method_name', true );
	$shipping_option_name = get_post_meta( $id, '_shipping_option_name', true );

	$special_slug = get_post_meta( $id, '_special_slug', true );

	if ( empty( $shipping_method_name ) ) {
		$shipping_method_name = 'Special Shipping';
	}

	if ( empty( $shipping_option_name ) ) {
		$shipping_option_name = $post->post_title;
	}

	if ( empty( $special_slug ) ) {
		$special_slug = sanitize_title( $shipping_option_name );
	}

	?>
	<table class="widefat">
		<tr>
			<td>
				<label for="shipping-method-name">Shipping Method Name:</label>
			</td>
			<td>
				<input size="40"
				       class="shipping-method-name"
				       type="text"
				       id="shipping-method-name"
				       name="shipping-method-name"
				       value="<?php echo $shipping_method_name; ?>">
				<br>
				<em>
					This is the shipping method shown to the shopper on the checkout page. Shipping options are grouped
					by shipping methods.
				</em>

			</td>
		</tr>

		<tr>
			<td>
				<label for="shipping-option-name">Shipping Option Name:</label>
			</td>
			<td>
				<input size="40"
				       class="shipping-option-name"
				       type="text"
				       id="shipping-option-name"
				       name="shipping-option-name"
				       value="<?php echo $shipping_option_name; ?>">

				<br>
				<em>
					This is the shipping option shown to the shopper on the checkotu page. Shipping options are grouped
					by shipping methods.
				</em>

			</td>
		</tr>


		<tr>
			<td>
				<label for="special-slug">Special Slug:</label>
			</td>
			<td>
				<input size="40"
				       class="special-slug"
				       type="text"
				       id="special-slug"
				       name="special-slug"
				       value="<?php echo $special_slug; ?>">

				<br>
				<em>
					The special slug will be passed to the eligibilty filters (see below).
				</em>
			</td>
		</tr>

	</table>

<?php
}

