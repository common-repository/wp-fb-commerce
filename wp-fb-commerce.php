<?php

/*
Plugin Name: WP FB-Commerce
Plugin URI: http://wordpress.org/#
Description:  WP FB Commerce is a companion plugin to GetShopped's famous WP e-Commerce. Market featured products or your whole catalog on Facebook.
Author: David F. Carr
Version: 0.3
Author URI: http://carrcommunications.com/
*/

function wp_fb_commerce_setup() {
global $fb_ec_opt;

if(!$_SESSION)
	session_start();

if($_GET["fbc_end"])
	{
	$_SESSION["fbcommerce"] = NULL;
	$_SESSION["fbx"] = NULL;
	$_SESSION["fb_test"] = NULL;
	return;
	}
if($_GET["test"])
	$_SESSION["fb_test"] = 1;

if(isset($_REQUEST['signed_request']))
    {
	$encoded_sig = null;
	$payload = null;
	list($encoded_sig, $payload) = explode('.', $_REQUEST['signed_request'], 2);
	$sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
	$data = json_decode(base64_decode(strtr($payload, '-_', '+/'), true));
	$_SESSION["like"] = $data->page->liked;
	$_SESSION["fbcommerce"] = 1;
	}

if($_GET["fb"] == 'commerce')
	$_SESSION["fbcommerce"] = 1;

if($_GET["fbx"] && !$_SESSION["fbx"])
	{
	$_SESSION["fbx"] = $_SERVER['REQUEST_URI'];
	$_SESSION["fbcommerce"] = 1;
	}

if(!$_SESSION["fbcommerce"])
	return;

	wp_enqueue_script( 'fb-wpsc-thickbox',				WP_PLUGIN_URL . '/wp-fb-commerce/js/thickbox.js',                      array( 'jquery' ), 'Instinct_e-commerce' );
	wp_enqueue_style( 'fb-wpsc-thickbox-style',				WP_PLUGIN_URL . '/wp-fb-commerce/js/thickbox.css',						false, WPSC_VERSION . "." . WPSC_MINOR_VERSION, 'all' );

//$fb_ec_opt = get_option('wp_fb_ec_template');
$fb_ec_opt = get_fb_ec_options();

if(!$fb_ec_opt["filters"])
	$fb_ec_opt["filters"] = "'convert_chars','wpautop','wptexturize'";
if(!$fb_ec_opt["scripts_styles"])
	$fb_ec_opt["scripts_styles"] = "'sharethis', 'jQuery', 'wp-e-commerce','infieldlabel','wp-e-commerce-ajax-legacy','wp-e-commerce-dynamic','livequery','jquery-rating','wp-e-commerce-legacy','colorbox-min','wpsc_colorbox','wpsc-colorbox-css','wpsc-theme-css','wpsc-theme-css-compatibility',  'wpsc-product-rater',  'wp-e-commerce-dynamic'";
if($fb_ec_opt["fb_theme"])
	{
	add_filter('template', 'fb_ec_get_template',99);
	add_filter('stylesheet', 'fb_ec_get_stylesheet',99);
	}
}

function fb_ec_get_template($t) {
global $fb_ec_opt;
return ($fb_ec_opt["fb_theme"]) ? $fb_ec_opt["fb_theme"] : $t;
}
function fb_ec_get_stylesheet($t) {
global $fb_ec_opt;
return ($fb_ec_opt["fb_theme"]) ? $fb_ec_opt["fb_theme"] : $t;
}

function fb_ec_request_filter( $sql ) {
global $wpdb;
global $wp_query;
if(strpos($sql,'wpsc') && $_GET["fbx"])
	{
	$sql = str_replace("WHERE"," JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->postmeta.meta_key = '_fbx' AND ",$sql);
	//echo "<h3>$sql ".$wp_query->query_vars["posts_per_page"]."</h3>";
	//$sql = preg_replace("/ORDER BY.+/"," ORDER BY CAST($wpdb->postmeta.meta_value AS SIGNED) LIMIT 0, 4 ",$sql);
	}
return $sql;
}

add_filter( 'posts_request', 'fb_ec_request_filter' );

function fbx_order($order){
global $wpdb;
global $wp_query;

if(!$_GET["fbx"])
	return $order; // if does not apply
return " CAST($wpdb->postmeta.meta_value AS SIGNED) ";
}
add_filter('posts_orderby', 'fbx_order' );

add_action("template_redirect", 'fb_commerce_template_redirect');

add_action('plugins_loaded','wp_fb_commerce_setup');

function fb_commerce_template_redirect() {
global $fb_ec_opt;
if($_SESSION["fbcommerce"] && !$fb_ec_opt["fb_theme"])
	{	
	include(WP_PLUGIN_DIR . '/wp-fb-commerce/fb-ec-theme/index.php');
	die();
	}
}

function fb_ec_limit_scripts_styles_filters() {
	global $wp_scripts;
	global $wp_styles;
	global $fb_ec_opt;

	$wpsc_keep = split("[, ]+",$fb_ec_opt["scripts_styles"]);
	//'wpsc-thickbox',
	$wp_scripts->queue = array_intersect($wp_scripts->queue,$wpsc_keep);
	$wp_styles->queue = array_intersect($wp_styles->queue,$wpsc_keep);

//prevent blog filters from interfering
global $wp_filter;
$corefilters = split("[, ]+",$fb_ec_opt["filters"]);
foreach($wp_filter["the_content"] as $priority => $filters)
	foreach($filters as $name => $details)
		{
		//keep only core text processing or shortcode
		if(!in_array($name,$corefilters) && !strpos($name,'hortcode') && !strpos($name,'psc_'))
			{
			$r = remove_filter( 'the_excerpt', $name, $priority );
			$r = remove_filter( 'the_content', $name, $priority );
			//echo "remove $name <br />";
			}
/*		else
			echo "keep $name <br />";
*/
		}	
//print_r($wpsc_keep);
//print_r($wp_scripts->queue);

/*
print_r($wp_styles->queue);
print_r($corefilters);
*/

}

function fb_commerce_menu() {

add_submenu_page("edit.php?post_type=wpsc-product", "Facebook Featured Products", "FB Featured", "edit_posts", "fbfeatured", "fbfeatured");

add_submenu_page('options-general.php','WP FB e-Commerce', 'WP FB e-Commerce', 'manage_options', 'wp_ec_fb_settings_page', 'wp_ec_fb_settings_page');

}

add_action('admin_menu', 'fb_commerce_menu');

function fbfeatured() {
global $wpdb;
$wpdb->show_errors();

if($_POST["featured"])
{
foreach ($_POST["featured"] as $index => $post_id)
	{
	if($post_id)
		update_post_meta($post_id, '_fbx', $index);
	}
}

$sql = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value FROM `$wpdb->posts` LEFT JOIN `$wpdb->postmeta` on $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key LIKE '_fbx' WHERE `$wpdb->posts`.`post_type` = 'wpsc-product'";
$results = $wpdb->get_results($sql);
?>
<div id="wrap" class="wrap">
<h2>Facebook Featured Products</h2>
<p>Use this utility to select featured products to be shown on your Facebook page tab. See below for installation instructions.</p>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php

if(!$results)
	die("No products found");
foreach ($results as $row)
	{
	$option = sprintf('<option value="%d">%s</option>',$row->ID,$row->post_title);
	if($row->meta_value)
		$featured[$row->meta_value] = $option;
	$options .= $option;
	}

for($i=1; $i <= 20; $i++)
	{
	echo '<p>Featured #'.$i.': <select name="featured['.$i.']">';
	if($featured[$i])
		echo $featured[$i];
	else
		echo '<option value="">Not Set</option>';
	echo $options . '</select></p>';
	}
?>
  <input type="submit" name="Submit" id="Submit" value="Submit" />
</form>

<?php
fb_ec_install_doc();
?>
</div>
<?php
}

//call register settings function
add_action( 'admin_init', 'register_wp_fb_ec_settings' );

function register_wp_fb_ec_settings() {
	//register our settings
	register_setting( 'wp_fb_ec-settings-group', 'wp_fb_ec_template' );
}

function get_fb_ec_options() {
$fb_ec_opt = get_option('wp_fb_ec_template');
if(!$fb_ec_opt["filters"])
	$fb_ec_opt["filters"] = "convert_chars,wpautop,wptexturize";
if(!$fb_ec_opt["scripts_styles"])
	$fb_ec_opt["scripts_styles"] = "sharethis, jQuery, wp-e-commerce,infieldlabel,wp-e-commerce-ajax-legacy,wp-e-commerce-dynamic,livequery,jquery-rating,wp-e-commerce-legacy,colorbox-min,wpsc_colorbox,wpsc-colorbox-css,wpsc-theme-css,wpsc-theme-css-compatibility,  wpsc-product-rater,  wp-e-commerce-dynamic, wp-e-commerce-taxes-functions, fb-wpsc-thickbox, fb-wpsc-thickbox-style";
if(!isset($fb_ec_opt["full_catalog"]) )
	$fb_ec_opt["full_catalog"] = 1;
if(!isset($fb_ec_opt["header"]) )
	$fb_ec_opt["header"] = '<div id="logo"><a href="'.get_bloginfo('url').'">'.get_bloginfo('name').' Store</a></div>';	

return $fb_ec_opt;
}

function fb_ec_navlinks($postion = '') {
global $fb_ec_opt;
$carturl = get_option('shopping_cart_url');

if(strpos($carturl,$_SERVER['REQUEST_URI']))
	$is_cart = true;

if(!is_single() && !$is_cart)
	{
	if(wpsc_cart_item_count() > 0)
	{
		$nav = '<a href="'.$carturl.'">'. __('Checkout','fb_ec').'</a>';
		echo '<div id="fb_ec_nav'.$position.'" class="navlinks">'.$nav.'</div>';
	}
	return;
	}

$prodpage = get_option("product_list_url");

if($fb_ec_opt["nav"] == 'both')
	$nav = '<a class="facebook_featured" href="'.$prodpage.'?fbx=1">'. __('Facebook Specials','fb_ec').'</a> | <a class="main_catalog" href="'.$prodpage.'?fb=commerce">'. __('Main Catalog','fb_ec').'</a>';
elseif($fb_ec_opt["nav"] == 'fbx')
	$nav = '<a class="facebook_featured" href="'.$prodpage.'?fbx=1">'. __('Facebook Specials','fb_ec').'</a> ';
elseif($fb_ec_opt["nav"] == 'main')
	$nav = '<a class="main_catalog" href="'.$prodpage.'?fb=commerce">'. __('Keep Shopping','fb_ec').'</a>';
else
	{
	if($_SESSION["fbx"])
		$nav = '<a class="facebook_featured" href="'.$_SESSION["fbx"].'">'. __('Facebook Specials','fb_ec').'</a>';
	else
		$nav = '<a class="main_catalog" href="'.$prodpage.'?fb=commerce">'. __('Keep Shopping','fb_ec').'</a>';
	}

if(!$is_cart)
	$nav .= ' | <a href="'.$carturl.'">'. __('Checkout','fb_ec').'</a>';

echo '<div id="fb_ec_nav'.$position.'" class="navlinks">'.$nav.'</div>';
}

function wp_ec_fb_settings_page() {
?>
<div class="wrap">
<div id="wp_fb_ec_options" class="icon32"><br /></div>
<h2>WP e-Commerce Facebook Template</h2>
<form method="post" action="options.php">
<?php 
$fb_ec_opt = get_fb_ec_options();
settings_fields( 'wp_fb_ec-settings-group' );
?>
<p>
<label for="wp_fb_ec_template[header]">Logo / Header HTML</label><br />
<textarea name="wp_fb_ec_template[header]" id="wp_fb_ec_template[header]" cols="60" rows="5"><?php echo $fb_ec_opt["header"]; ?></textarea>
</p>
<p><label for="wp_fb_ec_template[footer]">Footer HTML</label><br />
<textarea name="wp_fb_ec_template[footer]" id="wp_fb_ec_template[footer]" cols="60" rows="5"><?php echo $fb_ec_opt["footer"]; ?></textarea>
</p>
<p>
<label for="wp_fb_ec_template[css]">Template CSS</label><br />
<textarea name="wp_fb_ec_template[css]" id="wp_fb_ec_template[css]" cols="60" rows="5"><?php echo $fb_ec_opt["css"]; ?></textarea>
<br />Use to override default values (see below)
</p>
<p>
<label for="wp_fb_ec_template[scripts_styles]">Allowed Scripts and Styles</label><br />
<textarea name="wp_fb_ec_template[scripts_styles]" id="wp_fb_ec_template[scripts_styles]" cols="60" rows="5"><?php echo $fb_ec_opt["scripts_styles"]; ?></textarea>
<br />Output only scripts and styles that make sense in a Facebook context. These are handle tags used by the enqueue function, separated by commas.
<?php
global $wp_scripts;
global $wp_styles;
	$wpsc_keep = split("[, ]",$fb_ec_opt["scripts_styles"]);
	$base = array_merge($wp_scripts->queue,$wp_styles->queue);
	foreach($base as $name)
		if(!in_array($name,$wpsc_keep))
			$excluded .= ($excluded) ? ", ".$name.' ' : $name;
	
	if($excluded)
		echo "<br /><strong>Excluded</strong>: ".$excluded; 
?>
</p>
<p>
<label for="wp_fb_ec_template[filters]">Allowed Filters on the_content</label><br />
<textarea name="wp_fb_ec_template[filters]" id="wp_fb_ec_template[filters]" cols="60" rows="5"><?php echo $fb_ec_opt["filters"]; ?></textarea>
<br />Use only filters that make sense in a Facebook context. These are filter function names. Must be separated by commas.
<?php
global $wp_filter;
$corefilters = split("[, ]",$fb_ec_opt["filters"]);
foreach($wp_filter["the_content"] as $priority => $filters)
	foreach($filters as $name => $details)
		{
		//keep only core text processing or shortcode
		if(!in_array($name,$corefilters) && !strpos($name,'hortcode') && !strpos($name,'psc_'))
			$excluded_filters .= ($excluded_filters) ? ", ".$name.' ' : $name;
		}
if($excluded_filters)
	echo "<br /><strong>Excluded</strong>: $excluded_filters";
?>
</p>
<p>

<label for="wp_fb_ec_template[posts_per_page]">Products Per Page</label>
<select name="wp_fb_ec_template[posts_per_page]" id="wp_fb_ec_template[posts_per_page]">
<?php 
$fb_ec_opt["posts_per_page"] = ($fb_ec_opt["posts_per_page"]) ? $fb_ec_opt["posts_per_page"] : 6;
echo '<option value="'.$fb_ec_opt["posts_per_page"].'" selected="selected" >'.$fb_ec_opt["posts_per_page"].'</option>'; ?>
    <option value="4">4</option>
    <option value="6">6</option>
    <option value="8">8</option>
    <option value="10">10</option>
    <option value="12">12</option>
    <option value="14">14</option>
    <option value="16">16</option>
    <option value="18">18</option>
    <option value="20">20</option>
  </select>
</p>
<p>
<label for="wp_fb_ec_template[nav]">Navigation</label>
<select name="wp_fb_ec_template[nav]" id="wp_fb_ec_template[nav]">
<?php
$navoptions = array('both' => 'Both Facebook Specials and Main Catalog','fbx' => 'Facebook Specials','main' => 'Main Catalog');
$nav = ($fb_ec_opt["nav"]) ? '<option value="">Default</option>' : '<option value="" selected="selected">Default</option>';
foreach($navoptions as $slug => $show)
	{
	$nav .= ($slug == $fb_ec_opt["nav"]) ? '<option selected="selected" ' : '<option ';
	$nav .= sprintf(' value ="%s">%s</option>',$slug,$show);
	}
echo $nav;	
?>
</select>
<br />By default, navigation links are shown for either featured selection or the main catalog, depending on what is accessed first. Or you can set it here.
</p>
<p><?php 

  // based on code from Vladimir Prelovac's Theme Test Drive
  $themes = get_themes();
 
  if (count($themes) > 1) {
	  $theme_names = array_keys($themes);
	  natcasesort($theme_names);
	  
	 
	  $ts = '<select name="wp_fb_ec_template[fb_theme]"><option value="">Plugin Built-In</option>' . "\n";
	  foreach ($theme_names as $theme_name) {
		  // Skip unpublished themes.
		  if (isset($themes[$theme_name]['Status']) && $themes[$theme_name]['Status'] != 'publish') {
			  continue;
		  }
		  if ($themes[$theme_name]["Stylesheet"] == $fb_ec_opt["fb_theme"]) {
			  $ts .= '        <option value="' . $themes[$theme_name]["Stylesheet"] . '" selected="selected">' . htmlspecialchars($theme_name) . ' ('.$themes[$theme_name]["Stylesheet"].')</option>' . "\n";
		  } else {
			  $ts .= '        <option value="' . $themes[$theme_name]["Stylesheet"] . '">' . htmlspecialchars($theme_name) . '</option>' . "\n";
		  }
	  }
	  $ts .= '    </select>' . "\n\n";
  }
  echo $ts;

?>
</p>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>

<h3>Default CSS</h3>
<?php

if($fb_ec_opt["fb_theme"])
	{
	$css = WP_CONTENT_DIR.'/themes/'. $fb_ec_opt["fb_theme"] .'/style.css';	
	}
else
	$css = WP_PLUGIN_DIR.'/wp-fb-commerce/fb-ec-theme/style.css';
echo "<p>File: <em>$css</em></p>";
?>
<pre>
<?php echo file_get_contents($css); ?>
</pre>

<?php
fb_ec_install_doc();
?>
</div>
<?php }

function fb_ec_install_doc () {
	$prodpage = get_option("product_list_url");
	$ssl = str_replace('http:','https:',$prodpage);
?>
<h3>Facebook Installation Instructions</h3>
<p>You can either display all products or a featured subset of products from your catalog, depending on the URL you register in the <a href="https://developers.facebook.com/apps">Facebook Developers</a> utility.</p>
<p>From the <a href="https://developers.facebook.com/apps">Facebook Developers</a> utility, click Create New App and follow the instructions. The critical information you will need to record is in the Facebook Integration section, under <strong>Page Tab</strong>. Enter a name for a tab and add both a <strong>Page Tab URL</strong> and a <strong>Secure Page Tab URL</strong>. <em>Note that you must have a an SSL security certificate installed on your web server for the https URLs to work properly</em>.</p>
<p><strong>Show Full Catalog</strong></p>
<p>Tab URL: <?php echo $prodpage; ?>?fb=commerce</p>
<p>Secure Tab URL: <?php echo $ssl; ?>?fb=commerce</p>
<p><strong>Facebook Featured Products</strong></p>
<p>Tab URL: <?php echo $prodpage; ?>?fbx=1</p>
<p>Secure Tab URL: <?php echo $ssl; ?>?fbx=1</p>
<?php
if($_GET["page"] == 'wp_ec_fb_settings_page')
	echo '<p><em>You can set your list of <a href="'.admin_url('edit.php?post_type=wpsc-product&page=fbfeatured',__FILE__).'">featured products here</a>.</em></p>';
else
	echo '<p><em>See the <a href="'.admin_url('options-general.php?page=wp_ec_fb_settings_page',__FILE__).'">settings page</a> to add a page header or footer, specify the number of products to be displayed in each listing, or add custom CSS.</em></p>';

if($_POST["appid"])
	{
	$addlinks = sprintf('<p><a target="_blank" href="https://www.facebook.com/dialog/pagetab?app_id=%s
&redirect_uri=%s">Add to Page - %s</a></p>'."\n",$_POST["appid"],urlencode($_POST["redirect"]), $_POST["title"] );//&display=popup
	$addlinks .= get_option('fbecaddtopagelinks');
	update_option('fbecaddtopagelinks',$addlinks);
	}
else
	$addlinks = get_option('fbecaddtopagelinks');	
	
//"_appid"][0]
?>
<h3>Add to Facebook Page</h3>

<p>Once you have registered your page tabs with the Facebook developer tool, you can record the AppID here together with a title to remind yourself which tab this refers to. The utility will generate an Add to Page link. Click that link, and you will be redirected to Facebook, which will display the dialog for choosing which of the pages under your control should display the page tab.</p>

<p>This feature was introduced at the end of 2011 to compensate for some changes Facebook has made, including the elimination of the profile pages that used to display an Add to Page button.</p>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<table>
<tr><td>App ID:</td><td><input name="appid"  /></td></tr> 
<tr><td>Redirect To:</td><td><input name="redirect" value="https://www.facebook.com"  size="50" /></td></tr>
<tr><td>Title:</td><td><input name="title" value="" size="50" /></td></tr>
</table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Create Add to Page Link') ?>" />
    </p>
</form>
<?php
echo $addlinks;

}

function fb_ec_open_graph() {
global $wp_query;

if(is_single() && ($wp_query->query_vars["post_type"] == 'wpsc-product') )
{
?>
   <meta property="og:title" content="<?php wp_title(); ?>"/>
   <meta property="og:type" content="article"/>
   <meta property="og:url" content="<?php echo wpsc_the_product_permalink(); ?>"/>
   <meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>
   <meta property="og:description" content="<?php ?>"/>
<?php if ( wpsc_the_product_thumbnail() ) : ?>
   <meta property="og:image" content="<?php echo wpsc_the_product_thumbnail(get_option('product_image_width'),get_option('product_image_height'),'','single'); ?>"/>
<?php
endif;

}

}

add_action('wp_head','fb_ec_open_graph');

?>