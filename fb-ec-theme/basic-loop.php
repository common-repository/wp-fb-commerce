<!-- Basic Loop -->
<?php
	 if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
                <?php
				if(!$is_product_page)
 				{
				?>           
                <h1 class="page_title"><?php the_title(); ?></h1>
				<?php
				}
                ?>
                <div class="entry">
					<?php 
						the_content();					
					?>
				</div>

			</div>

		<?php 
		endwhile;
		
		else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>

<?php endif; ?>
