<?php
require_once(dirname(__FILE__).'/wp-cartoon.php');

function widget_cartoon( $args, $widget_args = 1 ) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
	$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_cartoon');
	if ( !isset($options[$number]) )
	return;

	echo $before_widget;

	$wpc_width = intval($options[$number]['width']);
	if (!$wpc_width) $wpc_width = 500;
	$wpc_border = intval($options[$number]['border']);
	if (!$wpc_border) $wpc_border = 2;

	echo wp_cartoon_html(array('wpc_width'=>$wpc_width, 'wpc_border'=>$wpc_border));

	echo $after_widget;
}


function widget_cartoon_control( $widget_args = 1 ) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
	$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_cartoon');
	if ( !is_array($options) )
	$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
		$this_sidebar =& $sidebars_widgets[$sidebar];
		else
		$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'widget_cartoon' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "cartoon-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "cartoon-$widget_number" is "{id_base}-{widget_number}
				unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-cartoon'] as $widget_number => $widget_cartoon_instance ) {
			// compile data from $widget_cartoon_instance
			if ( !isset($widget_cartoon_instance['width']) && isset($options[$widget_number]) ) // user clicked cancel
			continue;
			$width = intval( $widget_cartoon_instance['width'] );
			$border = intval( $widget_cartoon_instance['border'] );
			$options[$widget_number] = array( 'width' => $width, 'border' => $border );  // Even simple widgets should store stuff in array, rather than in scalar
		}

		update_option('widget_cartoon', $options);

		$updated = true; // So that we don't go through this more than once
	}


	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$width = 500;
		$border = 2;
		$number = '%i%';
	} else {
		$width = attribute_escape($options[$number]['width']);
		$border = attribute_escape($options[$number]['border']);
	}

	// The form has inputs with names like widget-cartoon[$number][width] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['widget-cartoon'][$number]
?>
		<p>
		<label for="widget-cartoon-width-<?php echo $number; ?>" >Cartoon Width</label>
			<input class="widefat" id="widget-cartoon-width-<?php echo $number; ?>" name="widget-cartoon[<?php echo $number; ?>][width]" type="text" value="<?php echo $width; ?>" />
			</p>
			<p>
			<label for="">Border Width</label>
			<input class="widefat" id="widget-cartoon-border-<?php echo $number; ?>" name="widget-cartoon[<?php echo $number; ?>][border]" type="text" value="<?php echo $border; ?>" />
			</p>
			<p>
			<input type="hidden" id="widget-cartoon-submit-<?php echo $number; ?>" name="widget-cartoon[<?php echo $number; ?>][submit]" value="1" />
		</p>
<?php
}


function widget_cartoon_register() {
	if ( !$options = get_option('widget_cartoon') )
	$options = array();

	$widget_ops = array('classname' => 'widget_cartoon', 'description' => 'Web cartoon widget');
	$control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'cartoon');
	$name = 'Cartoon';

	$registered = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['width']) )
		continue;

		// $id should look like {$id_base}-{$o}
		$id = "cartoon-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget( $id, $name, 'widget_cartoon', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'widget_cartoon_control', $control_ops, array( 'number' => $o ) );
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'cartoon-1', $name, 'widget_cartoon', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'cartoon-1', $name, 'widget_cartoon_control', $control_ops, array( 'number' => -1 ) );
	}
}

add_action( 'widgets_init', 'widget_cartoon_register' );

