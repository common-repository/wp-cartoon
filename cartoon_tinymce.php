<?php

$wc_ct = new Cartoon_Tinymce();
add_action('init', array($wc_ct, 'cartoon_addbuttons'));

class Cartoon_Tinymce {

	function cartoon_addbuttons() {
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;
		
		if (get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array($this, 'add_cartoon_tinymce_plugin'));
			add_filter('mce_buttons', array($this, 'register_cartoon_button'));
		}
	}

	function register_cartoon_button($buttons) {
		array_push($buttons, "separator", "cartoon");
		return $buttons;
	}

	
	function add_cartoon_tinymce_plugin($plugin_array) {
		
		$plugin_array['cartoon'] = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)). '/tinymce/editor_plugin.js';
		return $plugin_array;
	}

}
