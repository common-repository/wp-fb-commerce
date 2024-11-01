<!-- Products Page -->
<?php

$paged = (get_query_var('page')) ? get_query_var('page') : 1;
$args["paged"] = $paged;
$arg["is_paged"] = 1;
$args['post_type'] = 'wpsc-product';
$args['posts_per_page'] = ($template_vars["posts_per_page"]) ? $template_vars["posts_per_page"] : 6;
query_posts($args);

global $wp_query;
/*
 * Most functions called in this page can be found in the wpsc_query.php file
 */
?>
<div id="fb-root"></div>

<div id="default_products_page_container" class="wrap wpsc_container">

<?php wpsc_output_breadcrumbs(); ?>
	
	<?php do_action('fb_wpsc_top_of_products_page'); // Plugin hook for adding things to the top of the products page, like the live search ?>
	<?php if(wpsc_display_categories()): ?>
	  <?php if(wpsc_category_grid_view()) :?>
			<div class="wpsc_categories wpsc_category_grid group">
				<?php wpsc_start_category_query(array('category_group'=> get_option('wpsc_default_category'), 'show_thumbnails'=> 1)); ?>
					<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_grid_item  <?php wpsc_print_category_classes_section(); ?>" title="<?php wpsc_print_category_name(); ?>">
						<?php wpsc_print_category_image(get_option('category_image_width'),get_option('category_image_height')); ?>
					</a>
					<?php wpsc_print_subcategory("", ""); ?>
				<?php wpsc_end_category_query(); ?>
				
			</div><!--close wpsc_categories-->
	  <?php else:?>
			<ul class="wpsc_categories">
			
				<?php wpsc_start_category_query(array('category_group'=>get_option('wpsc_default_category'), 'show_thumbnails'=> get_option('show_category_thumbnails'))); ?>
						<li>
							<?php wpsc_print_category_image(get_option('category_image_width'), get_option('category_image_height')); ?>
							
							<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link <?php wpsc_print_category_classes_section(); ?>" title="<?php wpsc_print_category_name(); ?>"><?php wpsc_print_category_name(); ?></a>
							<?php if(wpsc_show_category_description()) :?>
								<?php wpsc_print_category_description("<div class='wpsc_subcategory'>", "</div>"); ?>				
							<?php endif;?>
							
							<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
						</li>
				<?php wpsc_end_category_query(); ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>
<?php // */ ?>
	
	<?php if(wpsc_display_products()): ?>
		
		<?php if(wpsc_is_in_category()) : ?>
			<div class="wpsc_category_details">
				<?php if(wpsc_show_category_thumbnails()) : ?>
					<img src="<?php echo wpsc_category_image(); ?>" alt="<?php echo wpsc_category_name(); ?>" />
				<?php endif; ?>
				
				<?php if(wpsc_show_category_description() &&  wpsc_category_description()) : ?>
					<?php echo wpsc_category_description(); ?>
				<?php endif; ?>
			</div><!--close wpsc_category_details-->
		<?php endif; ?>
		<?php if(wpsc_has_pages_top()) : ?>
			<div class="wpsc_page_numbers_top">
				<?php wpsc_pagination(); ?>
			</div><!--close wpsc_page_numbers_top-->
		<?php endif; ?>		
		
	
		<div class="wpsc_default_product_list">
		<?php /** start the product loop here */?>
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
					
			<div class="fb_product_display fb_product_view_<?php echo wpsc_the_product_id(); ?> ">   
				<h2 class="prodtitle entry-title">
							<?php if(get_option('hide_name_link') == 1) : ?>
								<?php echo wpsc_the_product_title(); ?>
							<?php else: ?> 
								<a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
							<?php endif; ?>
						</h2>   
				

				
				
				<?php if(wpsc_show_thumbnails()) :?>
					<div class="imagecol" id="imagecol_<?php echo wpsc_the_product_id(); ?>">
						<?php if(wpsc_the_product_thumbnail()) :
						?>
							<a rel="<?php echo wpsc_the_product_title(); ?>" class="<?php echo wpsc_the_product_image_link_classes(); ?>" href="<?php echo wpsc_the_product_image(); ?>">
								<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>

							</a>
						<?php else: ?>
								<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/noimage.png" width="<?php echo get_option('product_image_width'); ?>" height="<?php echo get_option('product_image_height'); ?>" />	
								</a>
						<?php endif; ?>
					</div><!--close imagecol-->
				<?php endif; ?>
					<div class="fb_productcol">

							<div class="wpsc_product_price">
								<?php if( wpsc_show_stock_availability() ): ?>
									<?php if(wpsc_product_has_stock()) : ?>
										<div id="stock_display_<?php echo wpsc_the_product_id(); ?>" class="in_stock"><?php _e('Product in stock', 'wpsc'); ?></div>
									<?php else: ?>
										<div id="stock_display_<?php echo wpsc_the_product_id(); ?>" class="out_of_stock"><?php _e('Product not in stock', 'wpsc'); ?></div>
									<?php endif; ?>
								<?php endif; ?>
								<?php if(wpsc_product_is_donation()) : ?>
									<label for="donation_price_<?php echo wpsc_the_product_id(); ?>"><?php _e('Donation', 'wpsc'); ?>: </label>
									<input type="text" id="donation_price_<?php echo wpsc_the_product_id(); ?>" name="donation_price" value="<?php echo wpsc_calculate_price(wpsc_the_product_id()); ?>" size="6" />

								<?php else : ?>
									<?php if(wpsc_product_on_special()) : ?>
										<div class="pricedisplay product_<?php echo wpsc_the_product_id(); ?>"><?php _e('Old Price', 'wpsc'); ?>: <span class="oldprice" id="old_product_price_<?php echo wpsc_the_product_id(); ?>"><?php echo wpsc_product_normal_price(); ?></span></div>
									<?php endif; ?>
									<div class="pricedisplay product_<?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>: <span id='product_price_<?php echo wpsc_the_product_id(); ?>' class="currentprice pricedisplay"><?php echo wpsc_the_product_price(); ?></span></div>
									<?php if(wpsc_product_on_special()) : ?>
										<div class="pricedisplay product_<?php echo wpsc_the_product_id(); ?>"><?php _e('You save', 'wpsc'); ?>: <span class="yousave" id="yousave_<?php echo wpsc_the_product_id(); ?>"><?php echo wpsc_currency_display(wpsc_you_save('type=amount'), array('html' => false)); ?>! (<?php echo wpsc_you_save(); ?>%)</span></div>
									<?php endif; ?>
									
								<?php endif; ?>
							</div><!--close wpsc_product_price-->
							
                            <p class="detailslink"><a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>">&gt;&gt; Details</a></p>
                <fb:like href="<?php echo wpsc_the_product_permalink(); ?>" send="true" width="200" show_faces="true" font=""></fb:like>                            
		</div>
        </div><!--close default_product_display-->

		<?php endwhile; ?>
		<?php /** end the product loop here */?>
		</div>
		
		<?php if(wpsc_product_count() == 0):?>
			<h3><?php  _e('There are no products in this group.', 'wpsc'); ?></h3>
		<?php endif ; ?>
	    <?php do_action( 'wpsc_theme_footer' ); ?> 	

		<?php if(wpsc_has_pages_bottom()) : ?>
			<div class="wpsc_page_numbers_bottom">
				<?php wpsc_pagination(); ?>
			</div><!--close wpsc_page_numbers_bottom-->
		<?php endif; ?>
	<?php endif; ?>

<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( 'More', 'fb_ec' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Back', 'fb_ec' ) ); ?></div>
				</div><!-- #nav-below -->

<?php endif; ?>    
    
</div><!--close default_products_page_container-->
