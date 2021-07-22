<?php

/**
* Plugin Name: Inve
* Description: A plugin to add inventory items and let users comment to add details
* Author: Enjeck
* Version: 1.0.0
* Author URI:
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

add_action('init', 'register_inventories');
function register_inventories() {
  $labels = array(
    'name' => __('Inventories', 'site_inventories'),
    'single_name' => __('Inventory', 'site_inventories'),
    'add_new' => __('Add New','site_inventories'),
    'add_new_item' => __('Add New Inventory', 'site_inventories'),
    'edit_item' => __('Edit Inventories', 'site_inventories'),
    'new_item' => __( 'New Inventory', 'site_inventories'),
    'view_item' => __( 'View inventory', 'site_inventories'),
    'search_items' => __( 'Search Inventories', 'site_inventories'),
    'not_found' => __( 'No Inventories found', 'site_inventories'),
    'not_found_in_trash' => __( 'No Inventory found in Trash', 'site_inventories'),
    'parent_item_colon' => __( 'Parent Inventory:', 'site_inventories'),
    'menu_name' => __( 'Inventories', 'site_inventories'),
  );
  $args = array(
    'label' => $labels,
    'hierarchical' => true,
    'description' => _('Inventories', 'site_inventories'),
    # these fileds act as the inventory title, inventory description, and more details
    'supports' => array('title', 'editor', 'comments', 'custom-fields'),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav' => true,
    'hide_from_search' => false,
    'has_archive' => true,
    'query_variable' => true,
    'can_export' => true,
    'capability_type' => 'post'
    /*'show_in_rest'  => true*/
  );

  register_post_type('site_inventory', $args);
}
# function to display comments list.

function inventory_comment_list( $comment, $args, $depth ) {
  global $post;
  $GLOBALS['comment'] = $comment;
  // Get current logged in user and creator of inventory
  $current_user = wp_get_current_user();
  $author_id = $post->post_author;
  $show_detail_status = false;
  # Show button to accept detail only to the creator of inventory
  if ( is_user_logged_in() && $current_user->ID == $author_id )
  {
    $show_detail_status = true;
  }
  // Get the approved status of the details
  $comment_id = get_comment_ID();
  $detail_status = get_comment_meta( $comment_id,
  "_inventory_details_status", true );
?>

<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
            <article id="comment-<?php comment_ID(); ?>">
                <header class="comment-meta comment-author vcard">
                        <?php echo get_avatar( $comment, $size = '48', $default = '<path_to_url>' ); ?>
                <?php printf(__('<cite class="fn">Details by %s</cite>'), get_comment_author_link() ) ?>

                </header>
                <?php if ( '0' == $comment->comment_approved ) : ?>
                <em><?php _e('Your detail is awaiting approval.') ?></em>
                <br />
                <?php endif; ?>


                <div class="comment-txt"><?php comment_text() ?></div>

                <div class="reply">
                <?php comment_reply_link( array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']) ) ) ?>
                </div>


                <div>
                  <?php
                       // Display the button for inventory creator to approve or disapprove a detail
                       if ( $show_detail_status ) {

                           $inve_status = '';
                           $inve_status_text = '';
                           if ( $detail_status ) {
                               $inve_status = 'invalid';
                               $inve_status_text = __('Dispprove','site_inventories');
                           } else {
                               $inve_status = 'valid';
                               $inve_status_text = __('Approve','site_inventories');
                           }

                   ?>
                   <input type="button" value="<?php echo $inve_status_text; ?>"  class="detail-status detail_status-<?php echo $comment_id; ?>"
                          data-inve-status="<?php echo $inve_status; ?>" />
                   <input type="hidden" value="<?php echo $comment_id; ?>" class="hcomment" />

                           <?php
                       }
               ?>
               </div>
           </article>
           </li>
       <?php
       }

       function frontend_scripts() {
         wp_enqueue_script( 'jquery' );
         wp_register_script( 'site-inventories', plugins_url('js/inventory.js', __FILE__ ), array('jquery'), '1.0', TRUE );
         wp_enqueue_script( 'site-inventories' );
         wp_register_style( 'site-inventories-css', plugins_url('css/inventory.css', __FILE__ ) );
         wp_enqueue_style( 'site-inventories-css' );
         $config_array = array('ajaxURL' => admin_url( 'admin-ajax.php' ),'ajaxNonce' => wp_create_nonce( 'inve-nonce' )
       );
       wp_localize_script( 'site-inventories', 'inveconf', $config_array
     );
   }

   add_action( 'wp_ajax_set_detail_status', 'inve_set_detail_status' );

   function wp_ajax_set_detail_status() {
     $data = isset( $_POST['data'] ) ? $_POST['data'] : array();
     $comment_id = isset( $data["comment_id"] ) ?
     absint($data["comment_id"]) : 0;
     $detail_status = isset( $data["status"] ) ? $data["status"] :
     0;
     // Set details as approved or disapproved
     if ("valid" == $detail_status) {
       update_comment_meta( $comment_id, "_inventory_details_status", 1 );
     } else {
       update_comment_meta( $comment_id, "_inventory_details_status", 0 );
     }
     echo json_encode( array("status" => "success") );
     exit;
   }

   function inve_get_approved_details( $post_id ) {
     $args = array(
       'post_id' => $post_id,
       'status' => 'approve',
       'meta_key' => '_inventory_details_status',
       'meta_value'=> 1,
     );
     // Get approved details for given inventory
     $comments = get_comments( $args );
     printf(__('<cite class="fn">%s</cite> approved details'),
count( $comments ) );
}

?>
