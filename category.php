<?php get_header(); ?>

<div class="templateFolder">
<div class="folderList">

  <?php 
  // Get the current category ID
  $current_category_id = get_queried_object_id();
  get_folder_list($current_category_id); 
  ?>
</div>

<div class="templateTable">
<select id="bulkActionsSelect" name="bulkActionsSelect" disabled=true>
	<option disabled selected="true">Bulk Actions</option>
	<option value="move">Move</option>
	<option value="delete">Delete</option>
</select>
<table>
  <thead class="folder-header">
	
    <tr>
		<?php 
	if (is_user_favorite($current_category_id, 'Folder')) {
	  $favItemClass = 'fa-solid';
	  $folderStarClass = 'fa-solid';
	} else {
	  $favItemClass = 'fa-regular';
	  $folderStarClass = 'fa-regular';
	} 
	  ?>
      <th><center><i class="fa-solid fa-folder-open"></i></center></th>
      <th class="single-folder-title" colspan="3"><?php echo display_category_hierarchy(); ?></th>
      <th><?php if ($current_category_id !== 1) { 
	echo '<center><a title="Favorite Folder"> <i class="'.$folderStarClass.' fa-star addRemoveFavorite favItem" data-objecttype="Folder" data-objectid="'.$current_category_id.'"></i></a>&nbsp;&nbsp;<span class="moveFolder" title="Move Folder" data-folderid="'.$current_category_id.'"><i class="fa-solid fa-folder-tree"></i></span>&nbsp;&nbsp;&nbsp;<span class="deleteFolder" title="Delete Folder" data-folderid="'.$current_category_id.'"><i class="fa fa-trash"></i>&nbsp;&nbsp;</span></center>';
	} ?></th>
	<th></th>

    </tr>
  </thead>

  <tbody>
  <?php
  //Add the sub-folders to the top.
	// Get the direct children terms of the parent term
	$child_terms = get_terms( array(
		'taxonomy' => 'category',
		'parent' => $current_category_id,
		'hide_empty' => false,
	) );

	// Loop through the child terms/folders
	if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
		$faves = array();
		$nonFaves = array();
		foreach ( $child_terms as $term ) {
			$term->is_favorite = is_user_favorite( $term->term_id, 'Folder');  // Add a property to store the favorite status
			if ( $term->is_favorite ) {
				$faves[] = $term;
			} else {
				$nonFaves[] = $term;
			}
		}
		if (!empty($faves)) {
		foreach ( $faves as $faveTerm ) {
				$folderStarClass = $faveTerm->is_favorite ? 'fa-solid' : 'fa-regular';  // Use the stored favorite status

			echo '<tr data-foldertitle="'.$faveTerm->name.'" class="favorite" data-objectid="'.$faveTerm->term_id.'">
				<td style="text-align: center;"><span class="clickToSelect folder"><i class="fa-solid fa-folder"></i><i class="fa-solid fa-square-check selected"></i></span></td>
				<td colspan="3"><a href="' . get_term_link( $faveTerm ) . '">' . $faveTerm->name . '</a></td>
				<td class="templateActions"><center><a title="Favorite Folder"> <i class="'.$folderStarClass.' fa-star addRemoveFavorite favItem" data-objecttype="Folder" data-objectid="'.$faveTerm->term_id.'"></i></a>&nbsp;&nbsp;<span title="Move Folder" class="moveFolder" data-folderid="'.$faveTerm->term_id.'"><i class="fa-solid fa-folder-tree"></i></span>&nbsp;&nbsp;&nbsp;<span class="deleteFolder" title="Delete Folder" data-folderid="'.$faveTerm->term_id.'"><i class="fa fa-trash"></i>&nbsp;&nbsp;</span></center></td>
				<td></td>
			</tr>';
			}
		}
		if (!empty($nonFaves)) {
			foreach ($nonFaves as $nonFaveTerm) {
			$folderStarClass = $nonFaveTerm->is_favorite ? 'fa-solid' : 'fa-regular';  // Use the stored favorite status
			echo '
			<tr data-foldertitle="'.$nonFaveTerm->name.'" data-objectid="'.$nonFaveTerm->term_id.'">
				<td style="text-align: center;" ><span class="clickToSelect folder"><i class="fa-solid fa-folder"></i><i class="fa-solid fa-square-check selected"></i></span></td>
				<td colspan="3"><a href="' . get_term_link( $nonFaveTerm ) . '">' . $nonFaveTerm->name . '</a></td>
				<td class="templateActions"><center><a title="Favorite Folder"> <i class="'.$folderStarClass.' fa-star addRemoveFavorite favItem" data-objecttype="Folder" data-objectid="'.$nonFaveTerm->term_id.'"></i></a>&nbsp;&nbsp;<span class="moveFolder" title="Move Folder" data-folderid="'.$nonFaveTerm->term_id.'"><i class="fa-solid fa-folder-tree"></i></span>&nbsp;&nbsp;&nbsp;<span class="deleteFolder" title="Delete Folder" data-folderid="'.$nonFaveTerm->term_id.'"><i class="fa fa-trash"></i>&nbsp;&nbsp;</span></center></td>
				<td></td>
			</tr>';
			}
		}
		
	}	
	?>
 <?php if ( have_posts() ) {
	 if (!empty($child_terms)) {
	 echo '<tr class="table-separator"><td colspan="6" style="background-color: #fff; padding: 40px 0 0 0; border: 0;"></td></tr>';
		 }
 ?>
	<thead>
    <tr>

      <th></th>
      <th>Template Name</th>
      <th>Created</th>
      <th>Last Updated</th>
      <th><center>Actions</center></th>
      <th><?php if (get_post_status(get_the_ID()) != 'trash') { ?><center>Sync</center><?php } ?></th>
    </tr>

  </thead>
<?php while ( have_posts() ) { the_post(); 
 // Get the post data
    $post_title = get_the_title();
	$post_link = get_permalink();
	$post_date = date( 'm/d/Y', strtotime( get_the_date() ) );
	$post_author = get_the_author();
	$post_modified = date( 'm/d/Y', strtotime( get_the_modified_date() ) );
	$post_modified_by_id = get_post_field( 'post_modified_by', get_the_ID() );
	$post_modified_by = get_userdata( $post_modified_by_id );
	$post_modified_author = ! empty( $post_modified_by ) ? $post_modified_by->display_name : $post_author;
		$trashedClass = '';
	if (get_post_status(get_the_ID()) == 'trash') {
		$trashedClass = 'trashed';
	}
	
	  ?>
    <tr id="template-<?php echo get_the_ID(); ?>" class="<?php if (is_user_favorite(get_the_ID(), 'Template')) { echo 'favorite';} ?> <?php echo $trashedClass; ?>" data-foldertitle="<?php echo get_the_title(); ?>" data-objectid="<?php echo get_the_ID(); ?>">
	  
	  <?php if (is_user_favorite(get_the_ID(),'Template')) {
		  $starClass = 'fa-solid';
	  } else {
		  $starClass = 'fa-regular';
	  }
	  ?>
      <td style="vertical-align:middle;" align="center"><span class="clickToSelect template"><i class="fa-regular fa-file"></i><i class="fa-solid fa-square-check selected"></i></span></td>
      <td style="vertical-align:middle;"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $post_title ); ?></a></td>
      <td style="vertical-align:middle;"><?php echo esc_html( $post_date ); ?> by <?php echo esc_html( $post_author ); ?></td>
      <td style="vertical-align:middle;"><?php echo esc_html( $post_modified ); ?> by <?php echo esc_html( $post_modified_author ); ?></td>
      <td class="templateActions" style="vertical-align:middle;" align="center">
	  <?php echo '<a title="Favorite Template"> <i class="'.$starClass.' fa-star addRemoveFavorite favItem" data-objecttype="Template" data-objectid="'.get_the_ID().'"></i></a>&nbsp;&nbsp;'; ?>
	  <?php echo '<span class="moveTemplate" title="Move Template" data-postid="'.get_the_ID().'"><i class="fa-solid fa-folder-tree"></i></span>&nbsp;&nbsp;'; ?>
		<?php if (get_post_status(get_the_ID()) != 'trash') { ?>
        <i class="fa fa-copy duplicate-template" title="Duplicate Template" data-postid="<?php echo get_the_ID(); ?>"></i>&nbsp;&nbsp;&nbsp;<i class="fa fa-trash delete-template" title="Delete Template" data-postid="<?php echo get_the_ID(); ?>"></i>
		<?php } else { ?>
		<i title="Restore from trash" class="fa fa-trash-arrow-up restore-template" data-postid="<?php echo get_the_ID(); ?>"></i>
		<?php } ?>
      </td>
	  <td style="vertical-align:middle;" align="center">
	  <?php
	  if (get_post_status(get_the_ID()) != 'trash') {
		$itTemplateId = get_post_meta(get_the_ID(),'itTemplateId',true);
		
		if ($itTemplateId == true) {
			echo '<a href="https://app.iterable.com/templates/editor?templateId='.$itTemplateId.'"><strong>'.$itTemplateId.'</strong></a>';
		} else {
			echo ' — ';
		}
	  }
	 ?>
	  </td>
    </tr>
<?php } ?>
  </tbody>
</table>
 <?php
  // Display pagination links
  global $wp_query;

  $total_pages = $wp_query->max_num_pages;
  $current_page = max( 1, get_query_var( 'paged' ) );

  echo '<div class="pagination">';
  echo paginate_links( array(
    'base' => get_pagenum_link( 1 ) . '%_%',
    'format' => 'page/%#%',
    'current' => $current_page,
    'total' => $total_pages,
  ) );
  echo '</div>';
  ?>
<?php } else if (!empty($child_terms)){
	echo '</tbody></table><br/><em>No templates in this folder...</em>';
	} else {
		echo '</tbody></table><br/><em>This folder is empty....</em>';
	} ?>
</div>
</div>

<?php get_footer(); ?>