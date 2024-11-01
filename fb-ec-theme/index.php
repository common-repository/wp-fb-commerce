<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" > 
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
//make sure relative urls referenced via https

fb_ec_limit_scripts_styles_filters();

global $wp_query;
global $fb_ec_opt;
if($wp_query->query_vars["pagename"])
	$is_product_page = true;

ob_start();
wp_print_styles();
wp_print_head_scripts();

$style =(strpos(__FILE__,'plugins')) ? plugins_url('/style.css',__FILE__) : get_bloginfo( 'stylesheet_url' );
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $style ?>" />
<?php echo $custom_fields["_inchead"][0]; 

if($fb_ec_opt["css"] )
	echo '<style type="text/css">
<![CDATA[
'.$fb_ec_opt["css"].']]>
</style>';

do_action('fb_wpsc_head');
do_action('fb_pwsc_open_graph');
?>
</head>

<body>
<div id="fb-root"></div><script src="https://connect.facebook.net/en_US/all.js#appId=192764714115444&amp;xfbml=1"></script>
	<div id="fb_content">
<div id="fbheader"><?php echo $fb_ec_opt["header"]; ?></div>
<?php
fb_ec_navlinks('top');

	if(is_single())
		include (dirname(__FILE__) . '/fb-wpsc-single_product.php');
	elseif($_GET["fbx"] || ($_GET["fb"] == 'commerce') || ($wp_query->query_vars["pagename"] == 'products-page'))
		include(dirname(__FILE__) . '/fb-wpsc-products_page.php');
	elseif($wp_query->query_vars["pagename"] == 'checkout')
		include(dirname(__FILE__) . '/fb-wpsc-shopping_cart_page.php');
	else
		include(dirname(__FILE__) . '/basic-loop.php');

	$carturl = get_option('shopping_cart_url');
	$prodpage = get_option("product_list_url");
?>
<div style="clear: both;"></div>
<?php

fb_ec_navlinks('bottom');

if(isset($cart_messages) && count($cart_messages) > 0) { ?>
	<?php foreach((array)$cart_messages as $cart_message) { ?>
	  <span class="cart_message"><?php echo $cart_message; ?></span>
	<?php } ?>
<?php } ?>

<?php if(wpsc_cart_item_count() > 0): ?>
    <div class="shoppingcart">
	<table>
		<thead>
			<tr>
				<th id="product" colspan='2'><?php _e('Product', 'wpsc'); ?></th>
				<th id="quantity"><?php _e('Qty', 'wpsc'); ?></th>
				<th id="price"><?php _e('Price', 'wpsc'); ?></th>
	            <th id="remove">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php while(wpsc_have_cart_items()): wpsc_the_cart_item(); ?>
			<tr>
					<td colspan='2' class='product-name'><a href="<?php echo wpsc_cart_item_url(); ?>"><?php echo wpsc_cart_item_name(); ?></a></td>
					<td><?php echo wpsc_cart_item_quantity(); ?></td>
					<td><?php echo wpsc_cart_item_price(); ?></td>
                    <td class="cart-widget-remove"><form action="" method="post" class="adjustform">
					<input type="hidden" name="quantity" value="0" />
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
					<input type="hidden" name="wpsc_update_quantity" value="true" />
					<input class="remove_button" type="submit" />
				</form></td>
			</tr>	
		<?php endwhile; ?>
		</tbody>
		<tfoot>
			<tr class="cart-widget-total">
				<td class="cart-widget-count">
					<?php printf( _n('%d item', '%d items', wpsc_cart_item_count(), 'wpsc'), wpsc_cart_item_count() ); ?>
				</td>
				<td class="pricedisplay checkout-total" colspan='4'>
					<?php _e('Total', 'wpsc'); ?>: <?php echo wpsc_cart_total_widget( false, false ,false ); ?><br />
					<small><?php _e( 'excluding shipping and tax' ); ?></small>
				</td>
			</tr>
			<tr>
				<td id='cart-widget-links' colspan="5">
					<a target="_parent" href="<?php echo get_option('shopping_cart_url'); ?>" title="<?php _e('Checkout', 'wpsc'); ?>" class="gocheckout"><?php _e('Checkout', 'wpsc'); ?></a>
					<form action="" method="post" class="wpsc_empty_the_cart">
						<input type="hidden" name="wpsc_ajax_action" value="empty_cart" />
							<a target="_parent" href="<?php echo htmlentities(add_query_arg('wpsc_ajax_action', 'empty_cart', remove_query_arg('ajax')), ENT_QUOTES, 'UTF-8'); ?>" class="emptycart" title="<?php _e('Empty Your Cart', 'wpsc'); ?>"><?php _e('Clear cart', 'wpsc'); ?></a>                                                                                    
					</form>
				</td>
			</tr>
		</tfoot>
	</table>
	</div><!--close shoppingcart-->		
<?php endif; ?>

<div id="fbfooter"><?php echo $fb_ec_opt["footer"]; ?></div>

</div><!-- end content -->
<?php
if(!$_SESSION["fb_test"])
{
?>
<script type="text/javascript" charset="utf-8">
if (window==window.top) { /* I'm not in an iframe anymore! */
var mySplitResult = document.location.href.split("?");
window.location = mySplitResult[0] + '?fbc_end=1'; 
}
</script>

<?php
}
?>

<script type="text/javascript" charset="utf-8">
    /* Resizing code will be here. */
	FB.Canvas.setSize();
	FB.Canvas.setAutoResize();
</script>
<?php

$content = ob_get_clean();
if($_SERVER['HTTPS'] == 'on')
	{
	$content = str_replace("http://".$_SERVER["SERVER_NAME"],"https://".$_SERVER["SERVER_NAME"],$content);
	//$content = str_replace("src=\"/","src=\"https://".$_SERVER["SERVER_NAME"].'/',$content);
	}
echo $content;

?>
</body>
</html>