<?php

/**
 * Media Buttons
 *
 * Add a media button & frame
 * 
 * @author Pieter-Jan Volders
 */
 
// Adding media buttuns
add_action('media_buttons', 'fflow_addMediaButton', 99);

// Adding action for the iframe
add_action('media_upload_fflow', 'fflow_create_iframe');


function fflow_addMediaButton() {

	global $post_ID, $temp_ID;
	$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
	$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";

	$fflow_upload_iframe_src = apply_filters('media_fflow_iframe_src', "$media_upload_iframe_src&amp;type=fflow");
	
	$fflow_title = 'Add Google Street View';
	
	$logo = get_option('siteurl') . "/wp-content/plugins/" . dirname(plugin_basename(__FILE__)) . "/media_icon.png";
	
	$link_markup = "<a href='{$fflow_upload_iframe_src}&amp;tab=fflow&amp;TB_iframe=true&amp;height=400&amp;width=640' class='thickbox' title='$fflow_title'><img src='$logo' /></a>\n";

	echo $link_markup;
}

function fflow_create_iframe() {
	wp_iframe('fflow_inner_custom_box');
}

/* Prints the inner fields for the custom post/page section */
function fflow_inner_custom_box() {
	//media_upload_header();
	
	?>
	<div style="padding: 15px;">
		<h3 class="media-title"><? _e('Add FlickrFlow shortcode to the post/page'); ?></h3>
		<p class="howto"><? _e('Insert your username, email address or Flickr ID to generate the shortcode for the plugin.'); ?></p>
		
		<table class="form-table"><tbody><tr valign="top"><th scope="row">
			<? _e('Username, email or ID'); ?>
		</th><td><input id="fflow_id" name="fflow_id" size="40" type="text" value=""></td></tr></tbody></table>
		<br/>
		<input class="button" style="font-weight: bold;" value="Add Shortcode" type="button" onclick="javascript:fflow_check();">
	</div>
	<script type="text/javascript">
	
		function fflow_check() {
		
			var $ = jQuery.noConflict();
			var something = document.getElementById('fflow_id').value;
			
			// Ajax magic
			var data = {
				action: 'flickr_flow_getnsid',
				some: something
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				if (response != 0)
				{
					var embedcode = '[flickrflow fid="' + response + '"]';
					top.send_to_editor(embedcode);
				} else {
					alert('<? _e("Could not find corresponding Flickr user."); ?>');
				}
			});
			
		}
	    
    </script>
    
    <?php
	
}

?>