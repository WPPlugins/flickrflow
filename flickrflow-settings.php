<?php

// add the admin options page
function fflow_admin_add_page() {
	add_options_page('FickrFlow settings', 'FickrFlow', 'manage_options', 'fickrflow-settings', 'fflow_options_page');
}
add_action('admin_menu', 'fflow_admin_add_page');

// display the admin options page
function fflow_options_page() {
	?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br/></div>
	<h2>Flickrflow settings</h2>
	<form action="options.php" method="post">
	<?php settings_fields('fickrflow-settings'); ?>
	<?php do_settings_sections('fickrflow-settings'); ?>
	<br />
	
	<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form></div>

	<?php
}

// Add the sections, fields and settings during admin_init
function fflow_settings_api_init() {
	// Check for the api_key and display a warning if not set 
	$options = get_option('fflow_options');
	if ( (strlen($options['api_key'])<10) && (!isset($_POST['fflow_options'])) && (function_exists('add_settings_error')))
		add_settings_error( 'general', 'fflow_api_key_warning', __('A Flickr API key has not been set for FlickrFlow!') );
	
	// Add the section to the page so we can add our fields to it
	add_settings_section('fflow_setting_section', '', 'fflow_setting_section_callback_function', 'fickrflow-settings');
	
	// Voeg het api-key veld toe
	add_settings_field('fflow_api_key', 'API Key', 'fflow_api_key_callback', 'fickrflow-settings', 'fflow_setting_section');
	
	// Register our setting so that $_POST handling is done for us and our callback function just has to echo the <input>
	register_setting( 'fickrflow-settings','fflow_options', 'fflow_options_validate' );
	
}// fflow_settings_api_init()

add_action('admin_init', 'fflow_settings_api_init');


// Settings section callback function
function fflow_setting_section_callback_function() {
	echo '<p>FlickFlow uses the Flickr API to acces photos, if you don&rsquo;t have an API key yet, request one <a href="http://www.flickr.com/services/api/misc.api_keys.html">here</a>.</p>';
}

// TEXTBOX - Name: fflow_options[api_key]
function fflow_api_key_callback() {
	$options = get_option('fflow_options');
	echo "<input id='fflow_api_key' name='fflow_options[api_key]' size='40' type='text' value='{$options['api_key']}' />";
}

function fflow_options_validate($input) {
	
	$newinput['api_key'] = addslashes(trim($input['api_key']));
	$quickr = new Quickr($newinput['api_key']);
	
	if( !$quickr->check() ) {
		$newinput['api_key'] = '';
		add_settings_error( 'general', 'fflow_api_key_error', __('The API-key you provided is not valid!'), 'error' );
	}
	return $newinput;
}

?>
