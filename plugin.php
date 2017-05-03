<?php
/*
Plugin Name: Specific Post/Page Direction
Description: Select specific page direction to a specific post or page.
Author: Yehuda Hassine
Version: 1.0
License: GPL3
Text Domain: spr
*/

class spr {

	public function __construct() {
		add_action( 'wp', array( $this, 'check_post_direction' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	public function check_post_direction() {
		global $post, $wp_locale;

		$direction = get_post_meta( $post->ID, 'spr_post_direction', true );

		if ( ! empty( $direction )  ) {
			$wp_locale->text_direction = $direction;
		}

	}

	public function add_meta_box() {
		$args = array(
		   'public'   => true,
		);
		$post_types = get_post_types( $args );

		add_meta_box( __( 'Post/Page Direction', 'spr' ), __( 'Post/Page Direction', 'spr' ), array( $this, 'render_metabox' ), $post_types, 'side' );
	}

    public function render_metabox( $post ) {
        $direction = get_post_meta( $post->ID, 'spr_post_direction', true );
        wp_nonce_field( 'spr_action', 'spr_nonce' );
        ?>
        <label for="spr-direction"><?php echo __( 'Select Page Direction', 'spr' ); ?></label>
        <select id="spr-direction" name="spr_direction">
        	<?php 
        	$options = array(
        		'ltr' => __( 'LTR', 'spr' ),
        		'rtl' => __( 'RTL', 'spr' ),
        	);

        	foreach ( $options as $key => $value) {
        		$selected = selected( $key, $direction, false );
        		echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        	}
        	?>
        </select>
        <?php
    }
 
    public function save_metabox( $post_id, $post ) {

        $nonce_name   = isset( $_POST['spr_nonce'] ) ? $_POST['spr_nonce'] : '';
        $nonce_action = 'spr_action';
 
        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
 
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
 
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
 
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $direction = filter_input( INPUT_POST, 'spr_direction', FILTER_SANITIZE_STRING );

        update_post_meta( $post_id, 'spr_post_direction', $direction );
    }

}

new spr;