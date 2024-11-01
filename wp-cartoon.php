<?php
/*
Plugin Name: WP-Cartoon
Plugin URI: http://www.seo-traffic-guide.de/wp-cartoon
Description: Integrate the daily web cartoon by Dan Rosendich on your WordPress Blog. <a href="options-general.php?page=page=wp-cartoon.php">Options configuration panel</a>
Author: Michael Busch
Version: 1.6
*/

/*
To install:  Put the whole WP-Cartoon folder from this package in your WordPress plugin folder and activate it

License: GPL
*/
$wp_cartoon = '1.6';

require_once (dirname(__FILE__) . '/cartoon_tinymce.php');
require_once(dirname(__FILE__).'/cartoon_widget.php');

#TODO: Make options for captions 
# Make function to update options on install
# Make function to remotely alter captions options from backend

add_option( 'wpc_width', 500 );
add_option( 'wpc_border', 2 );
add_option( 'wpc_license', '' );
add_option( 'wpc_refresh', '0' );
add_option( 'piclinkcaption', '0' );
add_option( 'poweredby', '0' );

function wpc_cartoon_options_setup()
{
	if( function_exists( 'add_options_page' ) ){
		add_options_page( 'WP-Cartoon', 'WP-Cartoon', 8,basename(__FILE__), 'wpc_cartoon_options_page');
	}

}

function wpc_cartoon_options_page()
{
	global $wpc_width;
	global $wpc_border;

	$wpc_default_width = 400;
	$wpc_default_border = 2;

	if( isset( $_POST[ 'set_defaults' ] ) ){
		echo '<div id="message" class="updated fade"><p><strong>';
		update_option( 'wpc_width', $wpc_default_width );
		update_option( 'wpc_border', $wpc_default_border );

		echo 'Default WP-Cartoon options loaded!';
		echo '</strong></p></div>';
	}
	else if( isset( $_POST[ 'wpc_update_options' ] ) ){
		echo '<div id="message" class="updated fade"><p><strong>';

		$wpc_width = stripslashes( (string) $_POST[ 'wpc_width' ] );
		if ($wpc_width < 280)
		$wpc_width = 280;
		$wpc_border  = stripslashes( (string) $_POST[ 'wpc_border' ] );
		$wpc_license  = stripslashes( (string) $_POST[ 'wpc_license' ] );

		update_option( 'wpc_width', $wpc_width );
		update_option( 'wpc_border', $wpc_border );
		update_option( 'wpc_license', $wpc_license );

		echo "WP-Cartoon options updated!";
		echo '</strong></p></div>';

	}

?>
            <div class="wrap">
            <h2>WP-Cartoon</h2>
            <p>WP-Cartoon helps you to easily integrate the daily web cartoon by Dan Rosandich (<a href="http://www.danscartoons.com ">Dan's Cartoons</a>).</p>

            <p>To display the cartoon on a page or post, simply use the cartoon button in the editor or put the following tag somewhere in your HTML code:
            &lt;!-- danscartoon --&gt;
            <p>If you want you can change the default setting for a smaller picture or change the border</p>
			<p>Please note that the cartoon size is 500px, if you chose anything larger than that the pic will become blurred. A size smaller than 280px is not possible.</p>
            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <input type="hidden" name="info_update" id="info_update" value="true" />

            <fieldset style="border: 1px solid rgb(153, 153, 153); padding: 1em;">
            <legend>WP-Cartoon settings</legend>

            <table width="100%" border="0" cellspacing="0" cellpadding="6">

            <tr valign="top">
              <td align="right" valign="middle"><strong>Cartoon width</strong></td>
              <td align="left" valign="middle">
                 <input name="wpc_width" type="text" size="3"
                        value="<?php echo htmlspecialchars( get_option( 'wpc_width' ) ); ?>" />
              </td>
            </tr>

            <tr valign="top">
              <td align="right" valign="middle"><strong>border</strong></td>
              <td align="left" valign="middle">
                 <input name="wpc_border" type="text" size="1"
                        value="<?php echo htmlspecialchars( get_option( 'wpc_border' ) ); ?>" />
              </td>
            </tr>
            <tr valign="top">
              <td align="right" valign="middle"><strong>I confirm that I use Dan's cartoon in a noncommercial way as described on http://www.danscartoons.com</strong></td>
              <td align="left" valign="middle">
                 <input name="wpc_license" type="checkbox"
                        value="agreed"
                        <?php
                        if ( htmlspecialchars( get_option( 'wpc_license' ) ) == "agreed" )
                        		echo 'checked="checked"'; ?>" />
              </td>
            </tr>
            </table>

            <div class="submit">
              <input type="submit" name="wpc_update_options" value="Update Options" />
            </div>

            </form>

            </div>
    <?php

}

function wp_cartoon_replace($content)
{
	$tag = "[[danscartoon]]";

	if( strpos( $content, $tag ) == false )
	return $content;

	$wpc_width = get_option( 'wpc_width' );
	$wpc_border = get_option('wpc_border');

	return str_replace( $tag, wp_cartoon_html(array('wpc_width'=>$wpc_width, 'wpc_border'=>$wpc_border)), $content );
}

function wp_cartoon_html($args=array())
{
	global $wp_cartoon;
	extract($args, EXTR_SKIP);
	if ( htmlspecialchars( get_option( 'wpc_license' ) ) != "agreed" )
	return '<p style="color:red">You may only use Dan\'s cartoon for non-commercial sites, check the option page of the WP-Cartoon plugin</p>';
	
	$piclinkcaption = get_option('piclinkcaption');		
	if (! $piclinkcaption) {
		$piclinkcaption = wp_cartoon_html_piclink_caption($args);
		update_option('piclinkcaption', $piclinkcaption);
	} 
	
	$poweredby = get_option('poweredby');		
	if (! $poweredby) {
		$poweredby = wp_cartoon_html_poweredby($args);
		update_option('poweredby', $poweredby);
	}

	$piclinkcaption = preg_replace('/width="\d+"/', "width=\"${wpc_width}\"", $piclinkcaption);
	$piclinkcaption = preg_replace('/double \d+px black/', "double ${wpc_border}px black", $piclinkcaption);
		
	$addcartoon = "<div style=\"text-align:center\"><a href=\"http://wordpress.org/extend/plugins/wp-cartoon/\">Add this cartoon on your blog!</a></div>";
	$_WPC_result="<!-- WP-Cartoon V".$wp_cartoon." --><div style='width:${wpc_width}px'>" . $addcartoon . $piclinkcaption . $poweredby . "<div style=\"clear: both;\"></div></div>";

	return $_WPC_result;
}

function wp_cartoon_html_piclink_caption($args=array())
{
	global $wp_cartoon;
	extract($args, EXTR_SKIP);
	$_WPC_Domain=$_SERVER["HTTP_HOST"];

	if (function_exists("curl_init")) {
		$_WPC_ch = curl_init();

		curl_setopt($_WPC_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_WPC_ch, CURLOPT_ENCODING, "");
		curl_setopt($_WPC_ch, CURLOPT_HTTPGET, true);
		curl_setopt($_WPC_ch, CURLOPT_URL, "http://cartooncontrol.seo-traffic-guide.de/danscartoon_piclinkcaption.php?ver=$wp_cartoon&domain=$_WPC_Domain&border=$wpc_border&width=$wpc_width");

		$_WPC_result_piclink_caption = @curl_exec($_WPC_ch);

		curl_close($_WPC_ch);
	} else
		$_WPC_result_piclink_caption = @file_get_contents("http://cartooncontrol.seo-traffic-guide.de/danscartoon_piclinkcaption.php?ver=$wp_cartoon&domain=$_WPC_Domain&border=$wpc_border&width=$wpc_width");

	return $_WPC_result_piclink_caption;
}

function wp_cartoon_html_poweredby($args=array())
{
	global $wp_cartoon;
	extract($args, EXTR_SKIP);
	$_WPC_Domain=$_SERVER["HTTP_HOST"];

	if (function_exists("curl_init")) {
		$_WPC_ch = curl_init();

		curl_setopt($_WPC_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_WPC_ch, CURLOPT_ENCODING, "");
		curl_setopt($_WPC_ch, CURLOPT_HTTPGET, true);
		curl_setopt($_WPC_ch, CURLOPT_URL, "http://cartooncontrol.seo-traffic-guide.de/danscartoon_poweredby.php?ver=$wp_cartoon&domain=$_WPC_Domain&border=$wpc_border&width=$wpc_width");

		$_WPC_result_poweredby = @curl_exec($_WPC_ch);

		curl_close($_WPC_ch);
	} else
		$_WPC_result_poweredby = @file_get_contents("http://cartooncontrol.seo-traffic-guide.de/danscartoon_poweredby.php?ver=$wp_cartoon&domain=$_WPC_Domain&border=$wpc_border&width=$wpc_width");

	return $_WPC_result_poweredby;
}

function updatePiclinkCaption()
{
	if (wpc_checkRefreshDate(get_option('wpc_refresh'))) {
		update_option('poweredby', 0);
		update_option('piclinkcaption', 0);
		update_option('wpc_refresh', time());
	}

	if ( ! ($_GET['updatePiclinkCaption'] || $_GET['updatePoweredByCaption']))
	{
		return;
	}
	$resetpiclinkcaption = $_GET['updatePiclinkCaption'];
	
	if ($_SERVER['HTTP_REFERER'] || $_ENV['HTTP_REFERER'])
	{
		$Referrer = urldecode(($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $_ENV['HTTP_REFERER']));
	}
	if (  $Referrer == "http://cartooncontrol.seo-traffic-guide.de/" && $resetpiclinkcaption == get_bloginfo('url') ) {
		update_option('piclinkcaption', 0);
		update_option('poweredby', 0);
		echo "Link options successfully refreshed<br>";
		echo "Last Refresh: " .get_option('wpc_refresh');
		update_option('wpc_refresh', time());
	}
	else
		echo "Piclink caption reset not authorized from $Referrer for blog $resetpiclinkcaption";
		exit;
}

function wpc_checkRefreshDate($refreshdate)
{
	#returns true if the last refresh date has been longer ago than 3 weeks

	if($refreshdate < (time()-(60*60*24*21))) {
		return true;
	}
	else {
		return false;
	}
}


add_filter('the_content', 'wp_cartoon_replace');
add_action('admin_menu', 'wpc_cartoon_options_setup');
add_action('init', 'updatePiclinkCaption');
add_action('init', 'updatePoweredByCaption');

?>