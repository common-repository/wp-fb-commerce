=== WP FB Commerce ===
Contributors: davidfcarr, mufasa
Donate link: http://www.rsvpmaker.com
Tags: facebook, iframe, page tab, e-commerce, wp-e-commerce, shop, cart, paypal, authorize, stock control, ecommerce, shipping, tax
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 0.3

WP FB Commerce is a companion plugin to GetShopped's famous WP e-Commerce. Market featured products or your whole catalog on Facebook.

== Description ==

WP FB Commerce is a companion plugin to GetShopped's famous WP e-Commerce. Display featured products or your whole catalog on Facebook and allow customers to check out directly within your Facebook business page.

This early release includes code adapted from Facebook Tab Manager for WordPress, customized for the e-commerce application. Use the provided theme for displaying products on Facebook or provide your own.

WP FB Commerce was created for GetShopped by Carr Communications Inc. [http://www.carrcommunications.com](http://www.carrcommunications.com "http://www.carrcommunications.com")

For more information about WP e-Commerce visit [http://getshopped.org](http://getshopped.org "http://getshopped.org")

== Installation ==

1. Upload the folder `wp-fb-commerce` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
4. Optionally, copy the fb-ec-theme subfolder to `/wp-content/themes/` directory. This will allow you to modify the template files and style.css independently of the plugin code. It may also perform better.
5. Register the URL for your storefront in the Facebook Developers utility (see explanation under Frequently Asked Questions).

== Changelog ==

= 0.3 =

Modified instructions for installing on Facebook to compensate for recent changes to the Facebook platform.

= 0.2 =

Fixed bug on settings screen. Error displaying default CSS for catalog display.

= 0.1 =

This is a preliminary test release. Shopping and checkout functions should function much the same as they would on an independent website. However, the plugin needs further testing with WP e-Commerce options.

== Frequently Asked Questions ==

= How do I add my storefront to Facebook? =

WP FB Commerce displays your storefront as a tab on a Facebook business page. You will register a URL pointing to your web server in the Facebook Developers utility at [https://developers.facebook.com/apps](https://developers.facebook.com/apps "https://developers.facebook.com/apps")

Click 'Create a New App' and follow the instructions. Many of the fields on the app registration form are optional. The most critical fields to fill in for our purposes are in the Page Tab section where you will enter a Page Tab Name, Page Tab URL, and Secure Page Tab URL. You must be able to host SSL secured / https pages for this to work properly.

You can find the URLs to record on the `Products -> FB Featured` and `Settings -> WP FB Commerce` pages. You have a choice of either displaying products from the default view of your WP e-Commerce product catalog or a select list of featured products set on the `Products -> FB Featured` screen. The URLs include a query string parameter in the form of `?fbx=1` for the featured products list or `?fb=commerce` for the default listing.

You can also create a tab featuring a single selected product. Take the page URL for that product as it is normally displayed on your website and add the query string `?fb=commerce` to the end of the URL.

After saving the settings for your "app", click the link on the left hand sidebar to View App Profile Page.

From the profile page, click the link for `Add to My Page`. Facebook will display a listing of all the pages you own or have editing rights to. Click the Add to Page button next to the page or pages you want this storefront view to appear on. Then navigate to your business page, and you should see the new tab displayed on the menu on the left side of the page.

If you are logged in as an administrator of the page, an 'Edit' link will be displayed at the bottom of the list of tabs (navigation links within the content for your page). This allows you to delete or rearrange the tabs. If you have a long list of tabs, you may need to click 'More' before the 'Edit' link will be displayed.

You can edit or rearrange the tabs.


= How do I select Facebook featured products? =

Look for an additional menu item under Products titled FB Featured. You may select up to 20 products to be displayed as featured offers.

= How do I customize WP FB Commerce? =

* Visit the settings screen at Settings -> WP FB Commerce. You can add header (for example, including a logo) or footer, add custom CSS to be added dynamically, and specify navigation options.
* You can create your own themes specifically for use with WP FB Commerce. When you add a theme to the 'wp-content/themes' and select it from the Settings -> WP FB Commerce, it will be displayed instead of your default theme when your site is viewed from within Facebook.

= How does WordPress know to display the Facebook theme? =

When a visitor first accesses the Facebook Page Tab containing your storefront, the query string at the end of the URL tells WordPress to display the Facebook theme. As the visitor continues to navigate through the product catalog, a PHP session variable tells WordPress to continue to display content with the Facebook theme.

Note: If the visitor subsequently navigates to your external website, this session variable can cause the website to be displayed within this alternate theme. However, this should only be momentary -- a JavaScript routine detects that the site is no longer being viewed within the Facebook iframe and redirects with a parameter that terminates the session, causing your site to be viewed within its normal theme.

= What is the to-do list for this plugin? =

* Improve display of shopping cart contents after user clicks the Add to Cart button. Would be good to have something similar to the sliding cart effect used in the WP e-Commerce sidebar widget. However, the Facebook layout doesn't leave room for a sidebar.
* Better AJAX / JavaScript effects for product display and checkout. Need a better way of showing that items have been added to cart. JavaScript for 'fancy_notifications' doesn't seem to be working properly.
* Deeper integration with Facebook social graph. Currently using basic plugins such as the Like button. At what point does it make sense to prompt users to authorize the app? Not when they first enter the store probably. But at checkout? At Add to Cart? What are the advantages / trade-offs?
* A way of giving special discounts to users who come through the Facebook channel.
* The ability to add an additional discount, or display special offers, for people who have "Liked" the merchant's Facebook page. (Currently, this status is detected and stored in the session variable $_SESSION["like"] but the variable is not being used to affect product display or pricing.)
* A hacked version of the thickbox JavaScript is provided -- altered to address certain positioning issues when the lightbox is displayed within an iFrame. Would appreciate help from anyone who can come up with a better solution.

== Screenshots ==

1. A product listing embedded in a Facebook business page.
