<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */
acf_form_head();
get_header(); ?>

<?php if ( is_home() && ! is_front_page() && ! empty( single_post_title( '', false ) ) ) : ?>
<?php endif; ?>
<div id="startUI">
	<div class="startOptions">
		<div id="recentTemplates">
		<h3>Your recent templates</h3>
		<?php
		// Get the current user ID
		$current_user_id = get_current_user_id();

		// Get an array of post IDs that the current user has marked as favorite templates
		$favorites = get_user_meta( $current_user_id, 'favorite_templates', true );

		// Get the 10 most recent posts from the current user
		$args = array(
			'post_type' => 'post',
			'author' => $current_user_id,
			'posts_per_page' => 10,
			'orderby' => 'date',
			'order' => 'DESC'
		);

		// Add all posts in the category to the post__in parameter, so that they are included in the query results
		$category = get_queried_object();
		$category_posts = get_posts( array(
			'posts_per_page' => -1,
			'category' => $category->term_id,
			'post__not_in' => $favorites
		) );

		// Sort the category posts by modified date
		  usort( $category_posts, function( $a, $b ) {
			return strtotime( $b->post_modified ) - strtotime( $a->post_modified );
		  } );


		// Merge the favorite posts array with the reversed category posts array
		$all_posts = array_merge( $favorites, wp_list_pluck( $category_posts, 'ID' ) );

		// Modify the query to include the favorite posts first and the 10 most recent posts from the current user
		$args['post__in'] = $all_posts;
		$args['orderby'] = 'post__in';

		// Run the new query
		$new_query = new WP_Query( $args );

		// Loop through the results of the new query
		if ( $new_query->have_posts() ) {
			echo '<ul>';
			while ( $new_query->have_posts() ) {
				$new_query->the_post();
				if (is_user_favorite(get_the_ID(),'Template')) {
					echo '<li><i class="fa-solid fa-star"></i>&nbsp;&nbsp;<a href="'.get_the_permalink().'">'.get_the_title().'</a><br/><span style="font-size: 12px; color: #666;">'.get_the_modified_date('n/j/y').'</span></li>';
				} else {
					echo '<li><a href="'.get_the_permalink().'">'.get_the_title().'</a> <br/><span style="font-size: 12px; color: #666;">'.get_the_modified_date('n/j/y').'</span></li>';
				}
			}
			echo '</ul>';
			wp_reset_postdata();
		}

		?>
		</div>
		<div id="templateSelect">
		<h3 class="startHeader">Start from a layout</h3>
		<p>Choose a preset layout as a starting point. You'll be able to add/move/remove layout chunks as you edit.</p>
			<div class="templateSelectWrap">
				<div class="startTemplate" data-postid="608">
					<h4>Plain Text</h4>
					<span>Non-Letter</span>
					<a href="<?php echo get_bloginfo('wpurl')?>/plain-text/?action=duplicate&post=282"></a>
				</div>
				<div class="startTemplate" data-postid="678">
					<h4>Plain Letter</h4>
					<span>Plain text with Sig</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="682">
					<h4>Full Graphic</h4>
					<span>Desktop Only Assets</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="685">
					<h4>Full Graphic Resp.</h4>
					<span>With Mobile-Alt Assets</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="687">
					<h4>Two-Col with Header</h4>
					<span>Header | Text | 2-Col (x3)</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="1806">
					<h4>Two-Col Contained with Header</h4>
					<span>Header | Text | 2-Col (x3)</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="691">
					<h4>Zig-Zag with Header</h4>
					<span>Header | Text | ZZ-Cols (x1)</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="1798">
					<h4>Zig-Zag Contained with Header</h4>
					<span>Header | Text | ZZ-Cols (x1)</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="695">
					<h4>Three-Col with Header</h4>
					<span>Header | Text | 3-Col (x1)</span>
					<a href=""></a>
				</div>
				<div class="startTemplate" data-postid="699">
					<h4>(blank)</h4>
					<span>Start from scratch!</span>
					<a href=""></a>
				</div>
			</div>
		</div>
	</div>
	
</div>

<?php
get_footer();
