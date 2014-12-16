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

class GS_Metabox_Shipping_Method_Settings extends PBCI_MetaBox {

	function do_metabox( $post ) {

		parent::do_metabox( $post );

		$id = $post->ID;

		$cost             = $this->get_option( $id, 'cost', true );
		$group           = $this->get_option( $id, 'group', true );
		$enabled          = $this->get_option( $id, 'enabled' ) == '1';

		?>
		<table class="widefat">
			<tr>
				<td colspan="2">
					<input
						id="<?php $this->option_element_name( 'enabled' );?>"
						type="checkbox" name="<?php $this->option_element_name( 'enabled' );?>"
						value="1" <?php checked( $enabled ); ?>>
					<label for="<?php $this->option_element_name( 'enabled' );?>">Available</label>
				</td>
			</tr>

			<tr>
				<td>
					<label for="method">Shipping Option Group</label>
				</td>
				<td>
					<input type="text" name="<?php $this->option_element_name( 'group' );?>" value="<?php echo $group;?>">
				</td>
			</tr>

			<tr>
				<td>
					<label for="cost">Cost:</label>
				</td>
				<td>
					<input type="text" name="<?php $this->option_element_name( 'cost' );?>" value="<?php echo $cost;?>">
				</td>
			</tr>

		</table>

		<input type="hidden" id="groupshipid" name="id" value="<?php echo $id; ?>">
	<?php
	}
}


function pbci_gs_setup_ship_mb_settings() {
	new GS_Metabox_Shipping_Method_Settings( 'Shipping Method Settings',  pbci_gs_post_type() );
}

add_action( 'pbci_gs_setup_ship_mb', 'pbci_gs_setup_ship_mb_settings', 1 , 0 );


