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


function pbci_gs_mb_filters( $post ) {
	?>
	<h3>You can create these filter
		you can make a product or cart eligible based on any criteria you would like to use.</h3>

<pre style="font-size: small;">
add_filter( 'pbci_product_is_eligible', 'my_product_is_eligible', 10 , 3 );

function my_product_is_eligible( $is_eligible, $special_slug, $product_id ) {
    // only product ids over 1000 are eligible
    if ( $product_id > 1000 ) {
        $is_eligible = true;
    }

    return $is_eligible;
}



add_filter( 'pbci_customer_is_eligible', 'my_cart_is_eligible', 10 , 2 );

function my_cart_is_eligible( $is_eligible, $special_slug ) {
    // option is available for shipping to Beverly Hills CA. Only
    $postcode = wpsc_get_customer_meta( 'shippingpostcode' );
    if ( false !== strpos( $postcode '90210' ) ) {
        $is_eligible = true;
    }

    return $is_eligible;
}


</pre>


<?php

}
