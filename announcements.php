<?php

/*
Plugin Name: Announcements
Plugin URI: http://coloredcow.in
Description: An announcements plugin.
Version: 1.0.4
Author: Vaibhav Rathore
Author URI: http://rathorevaibhav.wordpress.com
*/

define('ANNOUNCEMENTS_PATH', plugin_dir_url( __FILE__ )); //creating plugin


/*
	*creating custom post types
*/
function cc_register_announcements() {
 
    $labels = array(
        'name' => _x( 'Announcements', 'post type general name' ),
        'singular_name' => _x( 'Announcement', 'post type singular name' ),
        'add_new' => _x( 'Add New', 'Announcement' ),
        'add_new_item' => __( 'Add New Announcement' ),
        'edit_item' => __( 'Edit Announcement' ),
        'new_item' => __( 'New Announcement' ),
        'view_item' => __( 'View Announcement' ),
        'search_items' => __( 'Search Announcements' ),
        'not_found' =>  __( 'No Announcements found' ),
        'not_found_in_trash' => __( 'No Announcements found in Trash' ),
    );
 
    $args = array(
        'labels' => $labels,
        'singular_label' => __('Announcement', 'announcements'),
        'public' => true,
        'capability_type' => 'post',
        'rewrite' => false,
        'supports' => array('title', 'editor'),
    );
    register_post_type('announcements', $args);
}
add_action('init', 'cc_register_announcements');


/*
	***adding metaboxes
*/
function cc_add_metabox() {
    add_meta_box( 'cc_metabox_id', 'Announcement Metadata', 'cc_metabox', 'announcements', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'cc_add_metabox' );

/*
	***creating metaboxes
*/
function cc_metabox( $post ) {
    $values = get_post_custom( $post->ID );
    $start_date = isset( $values['cc_start_date'] ) ? esc_attr( $values['cc_start_date'][0] ) : '';
    $end_date = isset( $values['cc_end_date'] ) ? esc_attr( $values['cc_end_date'][0] ) : '';
    $ann_link = isset($values['cc_ann_link'] ) ? esc_attr( $values['cc_ann_link'][0] ) : '';
    wp_nonce_field( 'cc_metabox_nonce', 'metabox_nonce' );
    ?>
    <p>
        <label for="start_date">Start date: YYYY-MM-DD</label>
        <input type="text" name="cc_start_date" id="cc_start_date" value="<?php echo $start_date; ?>" required="required" />
    </p>
    <p>
        <label for="end_date">End date: YYYY-MM-DD</label>
        <input type="text" name="cc_end_date" id="cc_end_date" value="<?php echo $end_date; ?>" required="required"/>
    </p>
    <p>
        <label for="ann_link">Announcement Link</label>
        <input type="url" name="cc_ann_link" id="cc_ann_link" value="<?php echo $ann_link; ?>" required="required"/>
    </p>
    <?php
}

/*
 ***javascript and css for datepicker
 */
function cc_backend_scripts($hook) {
    global $post;
 
    if( ( !isset($post) || $post->post_type != 'announcements' ))
        return;
 
    wp_enqueue_style( 'jquery-ui-fresh', ANNOUNCEMENTS_PATH . 'css/jquery-ui-fresh.css');
    wp_enqueue_script( 'announcements', ANNOUNCEMENTS_PATH . 'js/announcements.js', array( 'jquery', 'jquery-ui-datepicker' ) );
}
add_action( 'admin_enqueue_scripts', 'cc_backend_scripts' );

/*
    **********   saving meta box data   ************
*/
function cc_metabox_save( $post_id ) {
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;
 
    if( !isset( $_POST['metabox_nonce'] ) || !wp_verify_nonce( $_POST['metabox_nonce'], 'cc_metabox_nonce' ) )
        return $post_id;
 
    if( !current_user_can( 'edit_post' ) )
        return $post_id;
 
    // Make sure data is set
    if( isset( $_POST['cc_start_date'] ) && isset( $_POST['cc_end_date']) ) {
 
        $valid1 = 0;
        $valid2 = 0;
        $old_value1 = get_post_meta($post_id, 'cc_start_date', true);
        $old_value2 = get_post_meta($post_id, 'cc_end_date', true);

        if ( $_POST['cc_start_date'] != '' && $_POST['cc_end_date'] != '') {
 
            $date1 = $_POST['cc_start_date'];
            $date1 = explode( '-', (string) $date1 );
            $valid1 = checkdate($date1[1],$date1[2],$date1[0]); 
 
            $date2 = $_POST['cc_end_date'];
            $date2 = explode( '-', (string) $date2 );
            $valid2 = checkdate($date2[1],$date2[2],$date2[0]);
        }
         // if($valid1 && $valid2){
         //     if($date1>$date2){
         //         //echo '<script type="text/javascript">alert("end date smaler");</script>';
         //         return $post_id;
         //     }
         // }

        if ($valid1)
            update_post_meta( $post_id, 'cc_start_date', $_POST['cc_start_date'] );
        elseif (!$valid1 && $old_value1)
            update_post_meta( $post_id, 'cc_start_date', $old_value1 );

        if($valid2)
            update_post_meta( $post_id, 'cc_end_date', $_POST['cc_end_date'] );
        elseif (!$valid2 && $old_value2)
            update_post_meta( $post_id, 'cc_end_date', $old_value2 );
    }

    if ( isset( $_POST['cc_ann_link'] ) ) {
        if( $_POST['cc_start_date'] != '') {
            
            $oldvalue = get_post_meta($post_id, 'cc_ann_link', true);
            $link = $_POST['cc_ann_link'];
            if($link){
                update_post_meta( $post_id, 'cc_ann_link', $link);
            } 
        }
    }
}
add_action( 'save_post', 'cc_metabox_save' );

function cc_frontend_scripts() {
    wp_enqueue_style( 'announcements-style', ANNOUNCEMENTS_PATH . 'css/announcements.css');
    wp_enqueue_script( 'announcements', ANNOUNCEMENTS_PATH . 'js/announcements.js', array( 'jquery' ) );
    wp_enqueue_script( 'cookies', ANNOUNCEMENTS_PATH . 'js/jquery.cookie.js', array( 'jquery' ) );
    wp_enqueue_script( 'cycle', ANNOUNCEMENTS_PATH . 'js/jquery.cycle.lite.js', array( 'jquery' ) );

}
add_action('wp_enqueue_scripts', 'cc_frontend_scripts');
?>