<?php

acf_form_head();
get_header(); 


?>
	<h1 class="page-title"><?php single_post_title(); ?></h1>


<div id="codeRepository">

	<?php
	// Check rows exist
	if( have_rows('repo_block', 'options') ):

		// Loop through rows.
		while( have_rows('repo_block', 'options') ) : the_row();
?>
		<div class="two-col-wrap">
		
		<div class="left">
			<h2 class="repo-block-title"><?php echo get_sub_field('block_title'); ?></h2>
			<?php echo get_sub_field('block_info'); ?>
		</div>
		<div class="right">
			<?php ob_start();
			get_template_part('template-parts/chunks/'.get_sub_field('block_slug'));
			$plainTextHTML = ob_get_clean(); ?>
			<pre style="white-space: pre; " tabsize="1" wrap="soft">
			<code class="language-html">
			<?php echo htmlspecialchars($plainTextHTML); ?>
			</code>
			</pre>
		</div>
	</div>
<?php
		// End loop.
		endwhile;
	// No value.
	else :
		// Do something...
	endif;
	?>
	
</div>



<?php
get_footer();
?>
<div id="iDoverlay"></div>
<div id="iDspinner" class="loader"></div>
