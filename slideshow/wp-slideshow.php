<?php
/**
 * @package Common
 * @author Faaiq Ahmed
 * @version 1.5.1
 */
/*
Plugin Name: Smooth Slideshow
Description: Smooth Slideshow for wordpress.
Author: Faaiq Ahmed, Sr Web Developer at Arcgate,faaiqsj@gmail.com
Version: 1.5.1
Author URI: http://arcgate.com/#
*/


function slideshow_plugin_menu() {
	 add_options_page('Slideshow Management', 'Slideshow', 'administrator', 'slideshow', 'slideshow_mgt');
}

add_action('admin_menu', 'slideshow_plugin_menu');

function slideshow_mgt() {

print '<div class="wrap">
	<h2>Slide Show Settings</h2>
	<form method="post" action="options.php">';
	wp_nonce_field('update-options');
	$checked= "";
	if(get_option('slideshow_enable')==1) {
		$checked = 'checked';
	}
	
	if(get_option('slideshow_only_images')==1) {
		$onlyimagechecked = 'checked';
	}	
	
	print '<table class="form-table">
		<tr valign="top">
		<th scope="row">Duration:</th>
		<td><input type="text" name="slideshow_duration" value="'.get_option('slideshow_duration').'" size="10" /></td>
		</tr>
		<tr valign="top">
		<th scope="row">SlideShow Width:</th>
		<td><input type="text" name="slideshow_width" value="'.get_option('slideshow_width').'"  size="10" /></td>
		</tr>
		<tr valign="top">
		<th scope="row">Image Height:</th>
		<td><input type="text" name="slideshow_image_height" value="'.get_option('slideshow_image_height').'"  size="10" /></td>
		</tr>
		<tr valign="top">
		<th scope="row">Image Width:</th>
		<td><input type="text" name="slideshow_image_width" value="'.get_option('slideshow_image_width').'"  size="10" /></td>
		</tr>
		<tr valign="top">
		<th scope="row">Only Image:</th>
		<td><input type="checkbox" name="slideshow_only_images" value="1" '.$onlyimagechecked.'   /></td>
		</tr>
		<tr valign="top">
		<th scope="row">Enable Slideshow:</th>
		<td><input type="checkbox" name="slideshow_enable" value="1" '.$checked.'   /></td>
		</tr>
		<tr valign="top"><td>
		<input type="submit" class="button" value="Save Changes" />
		</td></tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="slideshow_duration,slideshow_width,slideshow_image_height,slideshow_image_width,slideshow_only_images,slideshow_enable" />
		</form>
		</div>';
}	

add_action('wp_head', 'add_slideshowjs');
function add_slideshowjs() {
	$ss = get_option('slideshow_enable');
	if($ss) {
		$url = get_bloginfo("url").'/wp-content/plugins/slideshow';
		print '<script language="javascript" src="'.$url.'/js/jquery-1.4.2.min.js"></script>';
		print '<script language="javascript" src="'.$url.'/js/yahoo-dom-event.js"></script>';
		print '<script language="javascript" src="'.$url.'/js/slideshow.js"></script>';
		print '<link rel="stylesheet" href="'.$url.'/css/slideshow.css" type="text/css" media="screen" />';
		print '<script language="javascript">';
		$duration = get_option('slideshow_duration');
		if(!$duration) $duration = 2000;
		print "duration = ".$duration.";";
		$width = get_option('slideshow_width');
		if(!$width) $width = 400;
		print "width = ".$width.";";
		$height = get_option('slideshow_image_height');
		if(!$height) $height = 160;
		print "imgheight=".$height.";";
		$imgwidth  = get_option('slideshow_image_width');
		if(!$imgwidth) $imgwidth = 240;
		print "imgwidth=".$imgwidth.";";
		$onlyimages  = get_option('slideshow_only_images');
		print "onlyimages='".$onlyimages."';";
		print "content_url='".get_bloginfo("url")."?slideshow_content=1'";
		
		print '</script>';
	}
}


add_filter('the_content', 'contentss');

function contentss($content) {
	$ss = get_option('slideshow_enable');
	if($ss) {
		return str_replace('[SLIDESHOW]','<div id="slideshow"><div id="seg1"></div><div id="seg2"></div></div><br style="clear:both;">',$content);
	}else {
		return str_replace('[SLIDESHOW]','',$content);
	}
}

function slideshow_content() {
	if($_GET['slideshow_content']==1) {
		$template_name = 'content';
	}

	if (isset($template_name)) {
		if (file_exists(ABSPATH."wp-content/plugins/slideshow/{$template_name}.php")) require_once (ABSPATH."wp-content/plugins/slideshow/{$template_name}.php");
		exit;
	} //isset template name
}

add_action('template_redirect', 'slideshow_content');
