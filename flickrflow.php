<?php
/*
Plugin Name: Flickr Flow
Description: With FlickrFlow you can create pages with an infinite stream of Flickr photos. As soon as the user scrolls down, more photos get added to the page.
Version: 0.6
Author: PJ Volders
Author URI: http://www.pjvolders.be/
License: GPL2
*/

/*  Copyright 2010  Pieter-Jan Volders  (email : support@pjvolders.be)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once 'flickrflow-mediabutton.php';
require_once 'flickrflow-settings.php';
require_once 'Quickr.php';

add_filter( 'the_content', 'flickrflow_filter' );
add_action( 'wp_head', 'flickrflow_add_js', 1 );
//add_action( 'get_header', 'tb_inject', 10);


function flickrflow_add_js() {
	wp_enqueue_script( 'jquery' );
	
	wp_register_script( 'flickrflow', get_settings('siteurl').'/wp-content/plugins/'.str_replace('.php', '.js', plugin_basename(__FILE__)));
	wp_enqueue_script( 'flickrflow' );
	
	//wp_enqueue_script( 'thickbox' );
}

function flickrflow_html($photos, $fid)
{
	$admin_url = admin_url("admin-ajax.php");
	
	return "<div id='flickrflow_frame'>
		{$photos}
	</div>
	<script type='text/javascript'>
		var ff = new Flickrflow('$fid');
		ff.ajaxurl = '{$admin_url}';
		ff.init();
	</script>";
}


function flickrflow_filter($content) {

	if ( preg_match_all('/\[flickrflow fid="(\d*@N\d*)"\]/', $content, $matches) )
	{
		$search	 = array();
		$replace = array();
		
		foreach ($matches[0] as $id => $match)
		{
			$search[]  = $matches[0][$id];
			$replace[] = flickrflow_html(flickrflow_getphotos(1, $matches[1][$id]), $matches[1][$id]);
		}
		
		return str_replace($search, $replace, $content);
	
	} else {
		return $content;
	}
}

function flickrflow_getphotos($page = 1, $fid = '13477885@N00') {
	$html;
	
	$options = get_option('fflow_options');
	$quickr	= new Quickr($options['api_key']);
	
	$photos = $quickr->getPublicPhotos($fid, 10, $page);
		
	foreach ($photos as $photo)
	{
		
		$medium_url	= $photo['url_m'];
		$page_url  	= "http://www.flickr.com/photos/$fid/{$photo['id']}";
		//?KeepThis=true&TB_iframe=true&height=400&width=600";
		//$d			= explode(" ", $photo['datetaken']);
		//$date		= explode("-", $d[0]);
		//$time		= explode(":", $d[1]);
		//$timestamp	= mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		$timestamp	= strtotime($photo['datetaken']);
		$date_taken	= ucfirst(strtolower(date("l, j F Y", $timestamp)));
		
		$html.= "<p><img src='$medium_url'/><br/><a href='$page_url' class='flickrflow_title'>{$photo['title']}</a> on $date_taken<br/><br/></p>";
	}
	
	return $html;
}

/* TODO maak een mooie lightbox */
function  tb_inject() {
    ?>

    <link rel="stylesheet" href="<?= get_option('siteurl'); ?>/<?= WPINC; ?>/js/thickbox/thickbox.css" type="text/css" media="screen" />

    <script type="text/javascript">
    var tb_pathToImage = "<?= get_option('siteurl'); ?>/<?= WPINC; ?>/js/thickbox/loadingAnimation.gif";
    var tb_closeImage = "<?= get_option('siteurl'); ?>/<?= WPINC; ?>/js/thickbox/tb-close.png"
    </script>

    <?php
}

add_action('wp_ajax_flickr_flow_action', 'flickr_flow_action_callback');
add_action('wp_ajax_nopriv_flickr_flow_action', 'flickr_flow_action_callback');

function flickr_flow_action_callback() {

	$page	= ( isset($_POST['ff_p']) ) ? $_POST['ff_p'] : 1;
	$fid 	= ( isset($_POST['fid']) )  ? $_POST['fid']  : '13477885@N00';
	
	echo flickrflow_getphotos($page, $fid);
	die();
}

add_action('wp_ajax_flickr_flow_getnsid', 'flickr_flow_getnsid_callback');

function flickr_flow_getnsid_callback() {

	if ( isset($_POST['some']) )
	{
		$something = trim($_POST['some']);
	} else {
		die('0');
	}
	
	$options = get_option('fflow_options');
	$quickr	= new Quickr($options['api_key']);
	
	if ( $fid = $quickr->findUser($something) )
	{
		echo $fid;
		die();
	} else {
		die('0');
	}
}

?>