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




class GS_Metabox_With_Keyword extends PBCI_MetaBox {

	function do_metabox( $post ) {

		parent::do_metabox( $post );

		$id = $post->ID;

		$keywords = $this->get_option( $id, 'keywords' );
		$enabled  = $this->get_option( $id, 'enabled' ) == '1';

		?>
		<table class="widefat">
			<tr>
				<td colspan="2">
					<input type="checkbox" name="<?php $this->option_element_name( 'enabled' );?>" value="1" <?php checked( $enabled ); ?>>
					<label for="<?php echo __FUNCTION__; ?>">Enabled</label>
				</td>
			</tr>

			<tr>
				<td>
					<label for="keywords">Keywords:</label><br>
				</td>
				<td>
					<textarea
						id="keywords"
						name="<?php $this->option_element_name( 'keywords' );?>"
						rows="7"
						cols="50"
						><?php echo $keywords;?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}
}



function pbci_gs_setup_ship_mb_with_keywords() {
	new GS_Metabox_With_Keyword( 'For Products With Keyword',  pbci_gs_post_type() );
}

add_action( 'pbci_gs_setup_ship_mb', 'pbci_gs_setup_ship_mb_with_keywords', 5 , 0 );


function pbci_gs_keyword_applies( $applies = false, $shipping_method_post_id = 0, $cart = false ) {

	$shipping_method_post_id = absint( $shipping_method_post_id );
	if ( empty( $shipping_method_post_id ) ) {
		return $applies;
	}

	$mb = new GS_Metabox_With_Keyword( 'For Products With Keyword in Title',  pbci_gs_post_type() );
	$enabled = $mb->get_option( $shipping_method_post_id, 'enabled' ) == '1';
	if ( ! $enabled ) {
		pbci_log( 'check not enabled for ' . $shipping_method_post_id );
		return $applies;
	}

	if ( ! $applies ) {
		pbci_log( 'check skipped, already does not apply for ' . $shipping_method_post_id );
		return $applies;
	}


	if ( false === $cart ) {
		$cart  = wpsc_get_cart();
	}

	$keywords = $mb->get_option( $shipping_method_post_id, 'keywords' );

	$keywords = explode( "\r\n", $keywords );

	if ( is_array ( $cart->cart_items ) && !empty( $cart->cart_items ) ) {
		foreach ( $cart->cart_items as $cart_item ) {
			$product_id = absint( $cart_item->product_id );
			$title = get_the_title( $product_id );
			foreach ( $keywords as $keyword ) {
				if ( false !== stripos( $title, $keyword ) ) {
					$applies = true;
					break;
				}
			}
		}
	}

	return $applies;

}

add_filter( 'pbci_gs_check_condition', 'pbci_gs_keyword_applies', 3, 10 );

