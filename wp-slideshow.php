<?php
/**
 * @package Common
 * @author Faaiq Ahmed
 * @version 1.5.2
 */
/*
Plugin Name: Smooth Slideshow
Description: Smooth Slideshow for wordpress.
Author: Faaiq, Technical Architect Php
Version: 1.5.2
*/

class wp_slideshow {
 
 function __construct() {
   add_action('init', array($this,'init'));
   add_action('post_edit_form_tag', array($this,'post_edit_form_tag'));
			add_action( 'add_meta_boxes', array($this,'meta_box'));
   add_action('save_post', array($this,'save_post'),1,2);
   add_action('admin_menu',  array($this, 'adminmenu'));
   add_action('wp_head',  array($this, 'head'));
			add_action('admin_head',  array($this, 'admin_head'));
			register_activation_hook(__FILE__, array(&$this,'install'));
   register_deactivation_hook(__FILE__, array(&$this,'uninstall'));
 }
	
 
function slideshow( ) {
	global $wpdb;
			$axc_slideshow_width = get_option('axc_slideshow_width');
				$axc_slideshow_height = get_option('axc_slideshow_height');
				$axc_slideshow_border_color = get_option('axc_slideshow_border_color');
				$border = '';
				if(trim($axc_slideshow_border_color) != '') {
						$border = 'border:5px solid '.$axc_slideshow_border_color.';';
				}
				
 $slides_row = $wpdb->get_results("select * from ".$wpdb->prefix."posts where post_type = 'slides' and post_status = 'publish'");
	print '<div id="slideshowcontainer" class="slideshowcontainer" style="height:'.$axc_slideshow_height.'px;width:'.$axc_slideshow_width.'px">';
 
	$zindex = 1000;
	$text_data = array();
 for($i = 0; $i< count($slides_row); ++$i) {
  $attach_id = get_post_meta($slides_row[$i]->ID,'slide',true);
  $arr_existing_image = wp_get_attachment_image_src($attach_id);
		$image_url = $arr_existing_image[0];
		$text_data[] = '<div class="text_data" id="text_'.$i.'" style="opacity:0;">'.$slides_row[$i]->post_content.'</div>';
  if($i > 0) {
   print '<div class="slideshow_data" id="slide_'.$i.'" style="opacity:0;z-index:'.$zindex.';'.$border.'height:'.$axc_slideshow_height.'px;width:'.$axc_slideshow_width.'px">';
  }
  else {
   print '<div class="slideshow_data" id="slide_'.$i.'" style="opacity:1;z-index:'.$zindex.';'.$border.'height:'.$axc_slideshow_height.'px;width:'.$axc_slideshow_width.'px">';
  }
  print '<img src="'.$image_url.'"></div>';
  
		--$zindex;
 }
 
	print '</div>';
	print implode("",$text_data);
 ?>
 <script>
 var total_slide = <?php print $i;?>;
 </script>
 <?php
 
}

 
 function meta_box() {
		add_meta_box('res_solprodid',  __( 'Slide Image', 'product_textdomain' ), array($this,'slide_image_box'), 'slides');
	}
 
 function save_post($post_id, $post) {
  if (!wp_is_post_revision($post_id)) {
   if(!empty($_FILES['slide_image'])) {
    $file   = $_FILES['slide_image'];
    $upload = wp_handle_upload($file, array('test_form' => false));
    if(!isset($upload['error']) && isset($upload['file'])) {
     if($_POST['slide_image_attach_id'] > 0) {
      wp_delete_attachment( $_POST['slide_image_attach_id'], true);
     }
     $wp_filetype = wp_check_filetype(basename($upload['file']), null );
     $wp_upload_dir = wp_upload_dir();
     $attachment = array(
      'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ), 
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
      'post_content' => 'slide_image',
      'post_status' => 'inherit'
     );
     $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
     update_post_meta($post_id,'slide',$attach_id);
     require_once(ABSPATH . 'wp-admin/includes/image.php');
    }
   }
  }
 }
 
 function slide_image_box($post) {
			$args = array(
				'numberposts'     => -1,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'post_parent' => $post->ID,
				'orderby'    => 'post_title',
				'order'           => 'ASC'
	  );
	
		$attachments = get_posts( $args );
		//print '<pre>';
		//print_r($attachments);
		//print '</pre>';
		//getting all images attached to post
		$image_arr = array();
		$image_attach_id_arr = array();
		for($i =0 ; $i<count($attachments);++$i) {
				$attach_id = $attachments[$i]->ID;
				$attach_ids_arr[] = $attach_id;//$arr_existing_image[0];
				$arr_existing_image = wp_get_attachment_image_src($attach_id);
				$image_arr[$attachments[$i]->post_content] = $arr_existing_image[0];
				$image_attach_id_arr[$attachments[$i]->post_content] = $attach_id;
		}
	$rec = 0;
	$html = '<div class="wrap">';
	$html .= '<img src="'.$image_arr['slide_image'].'">';
	$html .= '<p><label for="document_file">Upload Slide Image:</label><br />';
	$html .= '<input type="hidden" name="slide_image_attach_id" id="slide_image_attach_id" value="'.$image_attach_id_arr['slide_image'].'"/></p>';
	$html .= '<input type="file" name="slide_image" id="slide_image" /></p>';
	$html .= '</p>';
	
	print $html;
 
}
 // [bartag foo="foo-value"]

 function adminmenu() {
  add_submenu_page( 'edit.php?post_type=slides', 'Settings', 'Slideshow Settings',  'administrator','s-set', array($this,'settings'));
 }
 function settings() {
		
		if(isset($_POST['axc_slideshow_height'])) {
				$axc_slideshow_width = $_POST['axc_slideshow_width'];
				update_option('axc_slideshow_width',$axc_slideshow_width);
				
				$axc_slideshow_height = $_POST['axc_slideshow_height'];
				update_option('axc_slideshow_height',$axc_slideshow_height);
				
				$axc_slideshow_border_color = $_POST['axc_slideshow_border_color'];
				update_option('axc_slideshow_border_color',$axc_slideshow_border_color);
		}
		
		
		
				$axc_slideshow_width = get_option('axc_slideshow_width');
				$axc_slideshow_height = get_option('axc_slideshow_height');
				$axc_slideshow_border_color = get_option('axc_slideshow_border_color');
  
		?>
		<div id="wrap">
		<h1>Sideshow Settings</h1>
			
			<form method="post">
				<table width="100%" cellpadding="5" cellspacing="0" border="0">
					<tr>
						<td>Slideshow Width:</td>
						<td><input type="text" name="axc_slideshow_width" value="<?php print $axc_slideshow_width;?>"></td>
					</tr>
					<tr>
						<td>Slideshow Height:</td>
						<td><input type="text" name="axc_slideshow_height" value="<?php print $axc_slideshow_height;?>"></td>
					</tr>
					<tr>
						<td>Slideshow Border color:</td>
						<td><input type="text" name="axc_slideshow_border_color" value="<?php print $axc_slideshow_border_color;?>"></td>
					</tr>
				<tr>
						<td></td>
						<td></td>
					</tr>
				</table>
				<input type="submit" value="submit" class="button-primary">
			</form>
		</div>
		<p></p>
		Help Us, Like Us
		<div class="fb-like" data-href="http://www.facebook.com/pages/Wordpress-Expert/105504792973227" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>
		
		<?php
		
 }
	
 function init() {
		remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_excerpt', 'wpautop' );
   $this->content_type();
 }
 
 function content_type() {
  	 $labels = array(
    'name' => 'Slids',
    'singular_name' => 'Slides',
    'add_new' => 'Add New Slide',
    'add_new_item' => 'Add New Slide',
    'edit_item' => 'Edit Slide',
    'new_item' => 'New Slide',
    'all_items' => 'All Slides',
    'view_item' => 'View Slides',
    'search_items' => 'Search Slide',
    'not_found' =>  'No Slided found',
    'not_found_in_trash' => 'No Slide found in Trash', 
    'parent_item_colon' => '',
    'menu_name' => 'Slides'
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'slides' ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => 4,
    'supports' => array( 'title','editor')
  );
  register_post_type( 'slides', $args );
  
 }
	function post_edit_form_tag() {
					echo ' enctype="multipart/form-data"';
	}
 function head() {
  wp_enqueue_style("wp-slideshow.css",plugins_url('wp-slideshow.css',__FILE__));
  wp_enqueue_script("wp-slideshow.js",plugins_url('wp-slideshow.js',__FILE__));
		
 }
 function admin_head() {
		?>
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
				fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>
		<?php
 }	
	
	function install() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				update_option('axc_slideshow_width',800);
				update_option('axc_slideshow_height',330);
				update_option('axc_slideshow_border_color','#F6C344');
  
  }
  
  function uninstall() {
    global $wpdb;
    delete_option('axc_slideshow_width');
				delete_option('axc_slideshow_height',330);
				delete_option('axc_slideshow_border_color');
  }
}
new wp_slideshow();
