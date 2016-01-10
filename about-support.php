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

if ( ! function_exists( 'pbci_news' ) ) {
	function pbci_news() {
		$feed_url = 'http://www.pyebrook.com/tag/plugin-news/feed/?donotcachepage=76fbf08c731642f0ade0fbcc4ecfb31e';
		//$feed_url = 'http://getshopped.org/feed/?category_name=wp-e-commerce-plugin';
		//$feed_url = 'http://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml';
		$rss        = fetch_feed( $feed_url );
		$rss->cache = false;
		$args       = array( 'show_author' => 1, 'show_date' => 1, 'show_summary' => 1, 'items' => 5 );
		wp_widget_rss_output( $rss, $args );
	}
}

if ( ! function_exists( 'pbci_about_help_support' ) ) {
	function pbci_about_help_support() {
		error_log( __FUNCTION__ );
		?>

		<div class="wrap snappy-database-check">
			<?php //pbci_plugin_page_title_box( 'WP-eCommerce Check-Up and Fix-Up', 'snappy' ); ?>

			<table class="widefat">
				<tr class="pbci-widefat-header-row">
					<th colspan="2">About this plugin</th>
				</tr>

				<tr>
					<td colspan="2">
						We have provided this plugin in the hope that it will help you identify issues with your
						WordPress
						and WP-eCommerce configuration. If you have other ideas on how we could enhance this plugin
						please
						<a href="mailto:info@pyebrook.com">email us</a> with your thoughts.
					</td>
				</tr>

				<tr>
					<td></td>
				</tr>

				<tr class="pbci-widefat-header-row">
					<th colspan="2">Support Us</th>
				</tr>

				<tr>
					<td colspan="2">
						Instead of asking for donations for the continuing maintenance of this plugin, we ask that you
						support
						our by effort by considering the purchase of one of our other plugins. Check our web site for
						the most
						current offerings. Below are some popular options.
					</td>
				</tr>

				<tr>
					<td></td>
				</tr>

				<tr class="pbci-widefat-header-row">
					<th colspan="2"><a href="http://www.pyebrook.com">stamps.com for WP-eCommerce</a></th>
				</tr>

				<tr>
					<td>
						<img
							src="<?php echo plugins_url( 'images/pye-brook-logo-pbci-stamps-com-min-128.png', __FILE__ ); ?>"/>
					</td>
					<td>
						Use stamps.com to generate WP-eCommerce shipping quotes and print shipping labels from your
						store dashbaord.
						Shipping quotes using stamps.com, all USPS shipping options are available. Ship packages from
						within
						WP-eCommerce, including paid shipping labels.
					</td>
				</tr>

				<tr>
					<td></td>
				</tr>

				<tr class="pbci-widefat-header-row">
					<th colspan="2"><a href="http://www.pyebrook.com">Shopper Rewards for WP-eCommerce</a></th>
				</tr>

				<tr>
					<td>
						<img
							src="<?php echo plugins_url( 'images/pye-brook-logo-wpec-shopper-rewards-128.png', __FILE__ ); ?>"/>
					</td>
					<td>
						Let your shoppers earn points for purchasing from your WP-e-Commerce store. Give shoppers a
						reason
						to come back and make additional purchases.

						<h3>Feature Highlights</h3>
						<ul>
							<li>Shoppers Earning points based on amount spent</li>
							<li>Import historical purchases</li>
							<li>Points history available to shoppers on their WP-eCommerce account page</li>
							<li>Works with the WP e-Commerce Coupon System</li>
							<li>Let customers change points into coupons</li>
							<li>Customer point redemption self-service</li>
							<li>Customers can easily redeem points on their WP-eCommerce account page</li>
						</ul>

					</td>
				</tr>

				<tr>
					<td></td>
				</tr>

				<tr class="pbci-widefat-header-row">
					<th colspan="2"><a href="http://www.pyebrook.com">Free Shipping Pro for WP-eCommerce</a></th>
				</tr>

				<tr>
					<td>
						<img
							src="<?php echo plugins_url( 'images/pye-brook-logo-free-shipping-pro-128.png', __FILE__ ); ?>"/>
					</td>
					<td>
						Enhanced free shipping based on number of items in cart, total cart value. You can
						exclude products based on product tags or product categories. Limit free shipping to
						specific countries, or exclude specific countries.
					</td>
				</tr>

				<tr>
					<td></td>
				</tr>

				<tr class="pbci-widefat-header-row">
					<th colspan="2"><a href="http://www.pyebrook.com">Store Admin eMail for WP-eCommerce</a></th>
				</tr>

				<tr>
					<td>
						<img
							src="<?php echo plugins_url( 'images/pye-brook-logo-email-wpec-customer-128.png', __FILE__ ); ?>"/>
					</td>
					<td>
						Send emails to customers from the WP-e-Commerce purchase log. Configure each individual store
						administrator
						with a custom professional looking signature. Automatically sends copy of email communications
						to store
						administrator email.

						No need to copy emails to your personal email program, or expose your personal email account
						when communicating store business.
					</td>
				</tr>

				<tr>
					<td></td>
				</tr>
				<tr>
					<td></td>
				</tr>
				<tr class="pbci-widefat-header-row">
					<th colspan="2">News from <a href="http://www.pyebrook.com">www.pyebrook.com</a></th>
				</tr>
				<tr>
					<td colspan="2">
						<?php pbci_news(); ?>
					</td>
				</tr>

			</table>


		</div>
	<?php
	}
}