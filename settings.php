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

function pbci_gs_options_page() {

	if ( isset( $_POST['gs-save-options'] ) ) {

		update_option( 'pbci-gs-store-base-address', trim( $_POST['pbci-gs-store-base-address'] ) );
		update_option( 'pbci-gs-google-api-key', trim( $_POST['pbci-gs-google-api-key'] ) );

	}
?>

	<div class="wrap">
		<h2>Delivery Pro Settings</h2>
		<br>
		<form method="post">
			<table class="widefat gs">
				<tr class="heading-row">
					<th colspan="2">
						Store Base Address
					</th>
				</tr>
				<tr>
					<td colspan="2">
						The store base address is used as a default for calculating the distance from a shoppers location/
					</td>
				</tr>
				<tr>
					<td>
						<label for="gs-store-base-address">Store Base Address:</label>
					</td>
					<td>
						<textarea id="pbci-gs-store-base-address"
						          name="pbci-gs-store-base-address" cols="50"
						          rows="6"><?php echo get_option( 'pbci-gs-store-base-address', '' );?></textarea>
					</td>
				</tr>
			</table>

			<br>
			<br>


			<table class="widefat gs">
				<tr class="heading-row">
					<th colspan="2">
						Google Distance and Geocode API Key
					</th>
				</tr>
				<tr>
					<td colspan="2">
					For the most current information on how to get an Google API key, and the free usage limitations see
					<a href="http://developers.google.com/maps/documentation/distancematrix/">http://developers.google.com/maps/documentation/distancematrix/</a>

					<br>
						<br>
					The Distance Matrix API uses an API key to identify your application. API keys are managed through the Google APIs console. To create your key:
						<br>
						<br>
					<ul>
						<li>Visit the APIs console at https://code.google.com/apis/console and log in with your Google Account.</li>
						<li>Click the Services link from the left-hand menu in the APIs Console,</li>
						<li>Activate the Distance Matrix API service.</li>
						<li>Activate the Geocoding API service.</li>
						<li>Once the service has been activated, your API key is available from the API Access page, in the Simple API Access section. </li>
						<li>Enter your key below.</li>
					</ul>
					</td>
				</tr>
				<tr>
					<td>
						<label for="gs-google-api-ley">Google API Key:</label>
					</td>
					<td>
						<input type="text" id="pbci-gs-google-api-key" name="pbci-gs-google-api-key" value="<?php echo get_option( 'pbci-gs-google-api-key', '' );?>" size="60">
					</td>
				</tr>
			</table>
			<br>
			<?php submit_button( 'Save Settings', 'primary', 'gs-save-options', true ); ?>

		</form>
	</div>
<?php

}

