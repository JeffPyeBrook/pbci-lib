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

if ( ! class_exists( 'PBCI_MetaBox' ) ) {
	abstract class PBCI_MetaBox {

		protected $post_type;
		protected $meta_box_title;

		function __construct( $title = '', $post_type = '' ) {
			$this->post_type      = $post_type;
			$this->meta_box_title = $title;

			add_action( 'save_post', array( &$this, 'mb_save' ) );
			add_action( 'admin_menu', array( &$this, 'mb_init' ), 11 );

		}

		protected function meta_key_name() {
			return '_mb_' . get_class( $this );
		}

		protected function option_element_name( $option_name, $echo = true ) {
			if ( $echo ) {
				echo $this->meta_key_name() . '[' . $option_name . ']';
			}

			return $this->meta_key_name() . '[' . $option_name . ']';
		}

		function get_option( $id, $option_name ) {
			$options = $this->get_options( $id );

			return isset( $options[ $option_name ] ) ? $options[ $option_name ] : '';
		}

		protected function get_options( $id ) {
			$options = get_post_meta( $id, $this->meta_key_name(), true );
			if ( ! is_array( $options ) ) {
				$options = array();
			}

			return $options;
		}

		protected function save_options( $id, $options ) {
			if ( ! is_array( $options ) ) {
				$options = array();
			}

			if ( empty( $options ) ) {
				delete_post_meta( $id, $this->meta_key_name() );
			} else {
				$options = update_post_meta( $id, $this->meta_key_name(), $options );
			}

			return $options;
		}

		protected function nonce_name() {
			return get_class( $this ) . '_nonce';
		}

		private function nonce_action() {
			return get_class( $this );
		}

		function do_metabox( $post ) {
			// Add an nonce field so we can check for it later.
			wp_nonce_field( $this->nonce_action(), $this->nonce_name() );
		}

		function mb_init() {
			add_meta_box(
				get_class( $this ),
				$this->meta_box_title,
				array( &$this, 'do_metabox' ),
				$this->post_type,
				'normal',
				'high'
			);
		}

		function mb_save( $post_id ) {

			if ( ! $this->mb_check_save_auth( $post_id ) ) {
				return false;
			}

			if ( isset( $_POST[ $this->meta_key_name() ] ) ) {
				if ( is_array( $_POST[ $this->meta_key_name() ] ) ) {
					$this->save_options( $post_id, $_POST[ $this->meta_key_name() ] );
				}
			}

			return true;
		}

		function mb_check_save_auth( $post_id ) {
			/*
			* We need to verify this came from our screen and with proper authorization,
			* because the save_post action can be triggered at other times.
			*/

			// Check if our nonce is set.
			if ( ! isset( $_POST[ $this->nonce_name() ] ) ) {
				return false;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST[ $this->nonce_name() ], $this->nonce_action() ) ) {
				return false;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return false;
			}

			// Check to be sure this save routine applies to this post type
			if ( isset( $_POST['post_type'] ) && ! empty( $this->post_type ) && $this->post_type != $_POST['post_type'] ) {
				return false;
			}

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return false;
				}

			} else {

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return false;
				}
			}

			return true;
		}
	}
}