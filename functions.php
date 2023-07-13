<?php
/**
 * Add HTML5 theme support.
 */
function wpdocs_after_setup_theme() {
	add_theme_support( 'html5', array( 'search-form' ) );
}
add_action( 'after_setup_theme', 'wpdocs_after_setup_theme' );


add_action( 'wp_enqueue_scripts', 'id_template_generator_theme_enqueue_styles' );
function id_template_generator_theme_enqueue_styles() {
    wp_enqueue_style( 'id-style',
        get_stylesheet_directory_uri() . '/style.css', array()
    );
	//require our ajax settings file to establish global variables for our js files
	require_once( dirname( __FILE__ ) ) . '/idAjax-settings.php';
	
	//enqueue jquery
	wp_enqueue_script( 'jquery' );
	//custom swal pop-ups
	wp_enqueue_script( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11.0', true );
	
   
	
	//general js stuff
	wp_enqueue_script( 'id-general', get_stylesheet_directory_uri() . '/js/id-general.js', array( 'jquery' ), '1.0.0', false );

	//folder actions
	wp_enqueue_script( 'folder-actions', get_stylesheet_directory_uri() . '/js/folder-actions.js', array( 'jquery', 'id-general' ), '1.0.0', true );

	//template editor
	wp_enqueue_script( 'template-editor', get_stylesheet_directory_uri() . '/js/template-editor.js', array( 'jquery','id-general' ), '1.0.0', true );

	//template functions
	wp_enqueue_script( 'template-actions', get_stylesheet_directory_uri() . '/js/template-actions.js', array( 'jquery','id-general' ), '1.0.0', true );

	//bulk actions functions
	wp_enqueue_script( 'bulk-actions', get_stylesheet_directory_uri() . '/js/bulk-actions.js', array( 'jquery','id-general','folder-actions', 'template-actions' ), '1.0.0', true );

	//iterable ajax
	wp_enqueue_script( 'iterable-actions', get_stylesheet_directory_uri() . '/js/iterable-actions.js', array( 'jquery','id-general','bulk-actions'), '1.0.0', true );

	
	
	//code highlighter
	wp_enqueue_script( 'highlighterjs', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js', array('jquery'), '11.7.0', true );
	//code highlighter theme
	wp_enqueue_style( 'highlighter-agate', get_stylesheet_directory_uri() . '/styles/agate.css', array(), '11.7.0' );

}

//Add ACF options pages
if( function_exists('acf_add_options_page') ) {
    
    acf_add_options_page(array(
        'page_title'    => 'Site Settings',
        'menu_title'    => 'Site Settings',
        'menu_slug'     => 'site-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
	 acf_add_options_page(array(
        'page_title'    => 'Code Repo',
        'menu_title'    => 'Code Repo',
        'menu_slug'     => 'code-repo',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}




//Gets template parts for each chunk
function iD_generate_chunk($chunk) {
    $template_parts = [
        'plain_text' => 'plain-text',
        'full_width_image' => 'full-width-image',
        'contained_image' => 'contained-image',
        'html' => 'custom-html',
        'two-column' => 'two-column',
        'two-column-contained' => 'two-column-contained',
        'three-column' => 'three-column',
        'button' => 'button',
        'spacer' => 'spacer',
        'external_send_footer' => 'external-send-footer',
    ];

    // Verify that the acf_fc_layout value is in the array of template parts
    if (!isset($template_parts[$chunk['acf_fc_layout']])) {
        return '';
    }

    // Include the template part and capture the output
    ob_start();
    get_template_part('template-parts/chunks/' . $template_parts[$chunk['acf_fc_layout']]);
    $return = ob_get_clean();

    return $return;
}





function duplicate_email_template( $post_id, $returnPHP=false ) {
    // Get the current post that we're duplicating
    $post = get_post( $post_id );
	
    // Duplicate the post
    $duplicate = array(
        'post_title'     => $post->post_title.' (copy)',
        'post_content'   => $post->post_content,
        'post_status'    => 'publish',
        'post_type'      => $post->post_type,
        'post_author'    => $post->post_author,
    );
    $dupedID = wp_insert_post( $duplicate );
	
	
	
    // Duplicate the post's custom fields
    $meta_keys = get_post_custom_keys( $post_id );
    if ( ! empty( $meta_keys ) ) {
        foreach ( $meta_keys as $key ) {
            //don't duplicate the iterable template ID value
            if ($key != 'itTemplateId') {
                $meta_values = get_post_custom_values( $key, $post_id );
                foreach ( $meta_values as $value ) {
                    add_post_meta( $dupedID, $key, maybe_unserialize( $value ) );
                }
            }
        }
    }

    // Duplicate the post's ACF fields
    if ( function_exists( 'acf_get_field_groups' ) ) {
        $field_groups = acf_get_field_groups( array( 'post_id' => $post_id ) );
        if ( ! empty( $field_groups ) ) {
            foreach ( $field_groups as $field_group ) {
                $fields = acf_get_fields( $field_group );
                if ( ! empty( $fields ) ) {
                    foreach ( $fields as $field ) {
                        $value = get_field( $field['name'], $post_id );
                        update_field( $field['key'], $value, $dupedID );
                    }
                }
            }
        }
    }
	
	
    
	//Set the copied templates categories (folders) to the duped ones
	//IMPORTANT: we're doing this later because it doesn't seem to work earlier in the code

	$categories = wp_get_object_terms( $post_id, 'category');

	// If no categories, set category to 1 as fallback
	if ( empty( $categories ) ) {
			$categoryIDs = array();
		foreach ($categories as $category) {
			$categoryIDs[] = $category->term_id;
		}
	}
	if (empty($categoryIDs)) {
		$categoryIDs = array(1);
	}
	wp_set_object_terms( $dupedID, $categoryIDs, 'category' );
	
	
    $return = array();
    if ($dupedID) {
        $return['success'] = true;
        $return['newTemplate'] = $dupedID;
        $return['newURL'] = get_the_permalink($dupedID);
    }
    
	if (!$returnPHP) {
		wp_send_json($return);
	} else {
		return $return;
	}
}

function my_acf_remove_curly_quotes() {
    remove_filter ('acf_the_content', 'wptexturize');
}
add_action('acf/init', 'my_acf_remove_curly_quotes');


/**
 * Restore a post by ID from the trash.
 *
 * @param int $post_id The ID of the post to restore from the trash.
 * @return bool Whether the post was successfully restored.
 */
function id_restore_template( $post_id ) {
    $post = get_post( $post_id );

    if ( ! $post ) {
        return false;
    }

    if ( 'trash' !== $post->post_status ) {
        return false;
    }
	
    $return =  wp_untrash_post( $post_id );
	wp_publish_post($post_id);
	
	return $return;
}


// Modify the default font family for all ACF WYSIWYG fields
add_filter('tiny_mce_before_init', function($init) {
  $init['content_style'] = "body { font-family: 'Poppins', sans-serif; }";
  return $init;
});

//ajax requests for template actions
function id_ajax_template_actions() {
    // handle the Ajax request here
    // use $_POST to retrieve any data sent in the request
	
	$action = $_POST['template_action'];
	$post_id = $_POST['post_id'];
	if (!$post_id || !$action) {
		wp_send_json(false);
	}
	$doAction = false;
	$actionResponse = '';
	switch ($action) {
        case 'delete':
			$trashPost = wp_trash_post($post_id);

			//Give this post a "trashed" category
			$currentFolder = wp_get_post_terms($post_id,'category',array('fields'=>'ids'));
			wp_set_post_terms($post_id, array_merge(array( 12 ),$currentFolder), 'category', false );

			//deleted iterable template id if present
			delete_post_meta($post_id, 'itTemplateId');

			// Get all users
			$users = get_users();

			// Loop through the users
			foreach ($users as $user) {
				$favorites = get_user_meta($user->ID, 'favorite_templates', true);

				// If the template is in the user's favorites, remove it
				if (is_array($favorites) && in_array($post_id, $favorites)) {
					$key = array_search($post_id, $favorites);
					unset($favorites[$key]);
					update_user_meta($user->ID, 'favorite_templates', $favorites);
				}
			}

			$doAction = true;
			$actionResponse = $post_id;
			break;

		case 'restore':
			
			$restorePost = id_restore_template($post_id);
			//Give this post a "trashed" category
			wp_remove_object_terms($post_id, 12, 'category');
			//wp_set_post_terms( $post_id, array( 1 ), 'category', false );
			$doAction = true;
			$actionResponse = $post_id;
            break;
        case 'create_from_template':
            $template_title = $_POST['template_title'];
			$actionResponse = duplicate_email_template($post_id, true);//we set the 2nd parameter to true to return a php-friendly result
			$dID = $actionResponse['newTemplate'];
			
			// Update the post title and slug
			$slug = sanitize_title($template_title); // Generate a slug from the post title
			$unique_slug = wp_unique_post_slug($slug, $post_ID, get_post_status($post_ID), get_post_type($post_ID), 0); // Generate a unique slug
			
			$post_data = array(
				'ID' => $dID,
				'post_title' => $template_title,
				'post_name' => $unique_slug,
			);
			wp_update_post($post_data);
			
			//get our new permalink
			$actionResponse['newURL'] = get_the_permalink($dID);
			
			//update the category to the root
			// Get the current categories for the post
			$current_categories = wp_get_post_categories($dID);

			// Set the post categories to the new category
			wp_set_post_categories($dID, array(1));

			// Remove the post from all other categories
			foreach ($current_categories as $category_id) {
				if ($category_id != 1) {
					wp_remove_object_terms($dID, $category_id, 'category');
				}
			}
			
			if (!empty($actionResponse)) {
				$doAction = true;
			}
            break;
		case 'duplicate':
			$actionResponse = duplicate_email_template($post_id);//returns new post id
			if (!empty($actionResponse)) {
				$doAction = true;
			}
			break;
        default:
            // code to handle any other actions
    }
	
    // return a response
	//wp_send_json automatically calls die();
    wp_send_json(array('success'=>$doAction, 'actionResponse'=>$actionResponse));
}
add_action('wp_ajax_id_ajax_template_actions', 'id_ajax_template_actions');
add_action('wp_ajax_nopriv_id_ajax_template_actions', 'id_ajax_template_actions');

//change <b> to <strong> in tinymce
function custom_acf_wysiwyg_toolbars( $toolbars ) {
  // Check if the 'items' index exists in the expected location
  if ( isset( $toolbars['Basic'][1]['items'] ) ) {
    // Modify the B button to wrap text with <strong> tags
    $toolbars['Basic'][1]['items'] .= ',strong';
  }
  return $toolbars;
}
add_filter( 'acf/fields/wysiwyg/toolbars', 'custom_acf_wysiwyg_toolbars' );

//custom editor styles
add_editor_style('custom-editor.css');


// Custom wpautop for ACF WYSIWYG fields
function my_acf_wysiwyg_custom_wpautop($content) {
    $content = wpautop($content, false);

    // Replace </p> with two <br/> tags
    $content = str_replace("</p>", "<br/>", $content);

    // Remove <p> tags
    $content = str_replace("<p>", "", $content);

    return $content;
}

// Override wpautop filter for ACF WYSIWYG fields
function my_acf_override_wpautop() {
    remove_filter('acf_the_content', 'wpautop');
    add_filter('acf_the_content', 'my_acf_wysiwyg_custom_wpautop');
}
add_action('init', 'my_acf_override_wpautop');


function id_mce_merge_tags_button($buttons) {
    array_push($buttons, 'id_merge_tags');
    return $buttons;
}
add_filter('mce_buttons', 'id_mce_merge_tags_button');

//Setup up custom tinyMCE buttons and menu
add_action( 'after_setup_theme', 'shortcodes_button_setup', 99);

function shortcodes_button_setup() {
    add_action( 'init', 'merge_tags_button' );
}

function merge_tags_button() {
    add_filter( 'mce_external_plugins', 'add_shortcodes_button' );
    add_filter( 'mce_buttons', 'register_shortcodes_button' );
}

function add_shortcodes_button( $plugin_array ) {
    $plugin_array['merge_tags_button'] = get_stylesheet_directory_uri().'/js/mergeTags.js';
    return $plugin_array;
}

function register_shortcodes_button( $buttons ) {
    array_push( $buttons, 'merge_tags_button' );
    return $buttons;
}





/**
 * Removes buttons from the first row of the tiny mce editor
 *
 * @link     http://thestizmedia.com/remove-buttons-items-wordpress-tinymce-editor/
 *
 * @param    array    $buttons    The default array of buttons
 * @return   array                The updated array of buttons that exludes some items
 */
add_filter( 'mce_buttons', 'id_remove_tiny_mce_buttons_from_editor');
function id_remove_tiny_mce_buttons_from_editor( $buttons ) {

    $remove_buttons = array(
        'formatselect', // format dropdown menu for <p>, headings, etc
		'blockquote',
        'wp_more', // read more link
        'spellchecker',
        'fontselect',
        'fullscreen',
		'alignleft',
		'aligncenter',
		'alignright',
        'dfw', // distraction free writing mode
        'wp_adv', // kitchen sink toggle (if removed, kitchen sink will always display)
    );
    foreach ( $buttons as $button_key => $button_value ) {
        if ( in_array( $button_value, $remove_buttons ) ) {
            unset( $buttons[ $button_key ] );
        }
    }
    return $buttons;
}

/**
 * Removes buttons from the second row (kitchen sink) of the tiny mce editor
 *
 * @link     http://thestizmedia.com/remove-buttons-items-wordpress-tinymce-editor/
 *
 * @param    array    $buttons    The default array of buttons in the kitchen sink
 * @return   array                The updated array of buttons that exludes some items
 */
add_filter( 'mce_buttons_2', 'id_remove_tiny_mce_buttons_from_kitchen_sink');
function id_remove_tiny_mce_buttons_from_kitchen_sink( $buttons ) {

    $remove_buttons = array(
        
        'alignjustify',
        'charmap', // special characters
        'outdent',
        'indent',
		'hr',
        'wp_help', // keyboard shortcuts
    );
    foreach ( $buttons as $button_key => $button_value ) {
        if ( in_array( $button_value, $remove_buttons ) ) {
            unset( $buttons[ $button_key ] );
        }
    }
    return $buttons;
}






//Get all the postdata needed to create or update a template in Iterable
function get_template_data_for_iterable() {
	
	 // Check that post_id is defined
    if (!isset($_POST['post_id'])) {
        wp_send_json(array(
            'status' => 'error',
            'message' => 'Post ID is missing',
        ));
    }
	
	$post_id = $_POST['post_id'];
	$emailSettings = get_field('email_settings', $post_id);
	$current_user = wp_get_current_user();
	
	$templateFields = array (
		'preheader' => $emailSettings['preview_text'],
		'fromName' => $emailSettings['from_name'],
		'utmTerm' => $emailSettings['utm_term'],
	);
	$reqTemplateFields = array (
		'templateName' => get_the_title($post_id),
		'emailSubject' => $emailSettings['subject_line'],
		'messageType' => $emailSettings['email_type'],
		'fromEmail' => 'info@idtechonline.com',
		'replyToEmail' => 'info@idtechonline.com',
		'createdBy' => $current_user->user_email,
		'postId' => $post_id,
	);
	
	$missing = array();
	$present = array();
	foreach ($reqTemplateFields as $key=>$field) {
		if (!$field) {
			$missing[] = $key;
			
		}
	}
	if (empty($missing)) {
		$templateFields = array_merge($reqTemplateFields, $templateFields);
		$response = array(
			'status' => 'success',
			'fields' => $templateFields,
		);
	} else {	
		$response = array(
			'status' => 'error',
			'message' => 'Required fields are missing: '.implode(',', $missing),
		);
	}
	
	
	
	wp_send_json($response);
}
add_action('wp_ajax_get_template_data_for_iterable', 'get_template_data_for_iterable');
add_action('wp_ajax_nopriv_get_template_data_for_iterable', 'get_template_data_for_iterable');


//Update the template after it syncs to Iterable
function update_template_after_sync() {
	$post_id = $_POST['post_id'];
	$template_id = $_POST['template_id'];
	//check for existing itTemplateId
	if (!get_post_meta($post_id,'itTemplateId', true)) {
		//add template_id to post meta if not existent yet
		delete_post_meta($post_id,'itTemplateId');
		update_post_meta($post_id,'itTemplateId',$template_id);
		$message = 'itTemplateId added to post meta!';
	} else {
		$message = 'itTemplateId already exists, moving on...';
	}
	$response = array(
		'status' => 'success',
		'message' => $message,
	);
	wp_send_json($response);
}
add_action('wp_ajax_update_template_after_sync', 'update_template_after_sync');
add_action('wp_ajax_nopriv_update_template_after_sync', 'update_template_after_sync');


//Add chunk elements to the acf chunk title area for easy IDing of content
function id_filter_acf_chunk_title($title, $field, $layout, $i) {
    // Only modify title for specific layout
    if ($layout['name'] === 'full_width_image' || $layout['name'] === 'contained_image') {
        $image_url = get_sub_field('desktop_image_url', $layout['key']);
		if ($image_url) {
			if (!empty($image_url)) {
				$title = $title.'&nbsp;&nbsp;<img style="max-width: 60px;" src="'.$image_url.'"/>';
			}
		}
    } else if ($layout['name'] === 'two-column' || $layout['name'] === 'two-column-contained') {
		$twoColSettings = get_sub_field('chunk_settings');
		if ($twoColSettings) {//make sure this field has saved settings		
			$twoColLayout = $twoColSettings['layout'];
			$leftImage = get_sub_field('left_image', $layout['key']);
				$leftImageURL = $leftImage['left_image_url'];
			$leftContent = get_sub_field('left_text', $layout['key']);
				$leftText = $leftContent['text_content'] ?? '';
				$firstFiveLeft = strip_tags(implode(" ", array_slice(explode(" ", html_entity_decode($leftText)), 0, 10))).'...';
			$rightImage = get_sub_field('right_image', $layout['key']);
				$rightImageUrl = $rightImage['right_image_url'];
			$rightContent = get_sub_field('right_text', $layout['key']);
				$rightText = $rightContent['text_content'] ?? '';
				$firstFiveRight = strip_tags(implode(" ", array_slice(explode(" ", html_entity_decode($rightText)), 0, 10))).'...';
			if ($twoColLayout == 'ltr') {
				$title = $title.'&nbsp;&nbsp;<img style="max-width: 60px;" src="'.$leftImageURL.'"/>';
				$title = $title.'  <span style="font-weight:300; font-size: 12px;color: #666;">'.$firstFiveRight.'</span>';
			} else if ($twoColLayout == 'rtl') {
				$title = $title.'&nbsp;&nbsp;<span style="font-weight:300; font-size: 12px;color: #666;">'.$firstFiveLeft.'</span>';
				$title = $title.'  <img style="max-width: 60px;" src="'.$rightImageUrl.'"/>';
			} else if ($twoColLayout == 'txt') {
				$title = $title.'&nbsp;&nbsp;<span style="font-weight:300; font-size: 12px;color: #666;">'.$firstFiveLeft.'</span>';
				$title = $title.'  <span style="font-weight:300; font-size: 12px;color: #666;">'.$firstFiveRight.'</span>';
			} else if ($twoColLayout == 'img') {
				$title = $title.'&nbsp;&nbsp;<img style="max-width: 60px;" src="'.$leftImageURL.'"/>';
				$title = $title.'  <img style="max-width: 60px;" src="'.$rightImageUrl.'"/>';
			}
		}
	} else if ($layout['name'] === 'three-column') {
		$threeColSettings = get_sub_field('chunk_settings');
		if ($threeColSettings) {//make sure this field has saved settings	
			$leftContent = get_sub_field('left_content');
				$Ltype = $leftContent['content_type'];
				if ($Ltype == 'text') {
				$Ltext = $leftContent['left_text'];
					$LtextAlign = $Ltext['align'];
					$LtextColor = $Ltext['text_color'];
					$LtextContent = $Ltext['text_content'];
					$title = $title.'&nbsp;&nbsp;<span style="font-weight:300; font-size: 12px;color: #666;">'.strip_tags(implode(" ", array_slice(explode(" ", html_entity_decode($LtextContent)), 0, 5))).'...</span>';
				} else {
				$Limage = $leftContent['left_image'];
					$LimageSrc = $Limage['left_image_url'];
					$LimageLink = $Limage['left_image_link'];
					$LimageAlt = $Limage['left_image_alt'];
					$title = $title.'&nbsp;&nbsp;<img style="max-width: 60px;" src="'.$LimageSrc.'"/>';
				}
			$middleContent = get_sub_field('middle_content');
				$Mtype = $middleContent['content_type'];
				if ($Mtype == 'text') {
				$Mtext = $middleContent['middle_text'];
					$MtextAlign = $Mtext['align'];
					$MtextColor = $Mtext['text_color'];
					$MtextContent = $Mtext['text_content'];
					$title = $title.'&nbsp;&nbsp;<span style="font-weight:300; font-size: 12px;color: #666;">'.strip_tags(implode(" ", array_slice(explode(" ", html_entity_decode($MtextContent)), 0, 5))).'...</span>';
				} else {
				$Mimage = $middleContent['middle_image'];
					$MimageSrc = $Mimage['middle_image_url'];
					$MimageMink = $Mimage['middle_image_link'];
					$MimageAlt = $Mimage['middle_image_alt'];
					$title = $title.'&nbsp;&nbsp;<img style="max-width: 60px;" src="'.$MimageSrc.'"/>';
				}
			$rightContent = get_sub_field('right_content');
				$Rtype = $rightContent['content_type'];
				if ($Rtype == 'text') {
				$Rtext = $rightContent['right_text'];
					$RtextAlign = $Rtext['align'];
					$RtextColor = $Rtext['text_color'];
					$RtextContent = $Rtext['text_content'];
					$title = $title.'&nbsp;&nbsp;<span style="font-weight:300; font-size: 12px;color: #666;">'.strip_tags(implode(" ", array_slice(explode(" ", html_entity_decode($RtextContent)), 0, 5))).'...</span>';
				} else {
				$Rimage = $rightContent['right_image'];
					$RimageSrc = $Rimage['right_image_url'];
					$RimageRink = $Rimage['right_image_link'];
					$RimageAlt = $Rimage['right_image_alt'];
					$title = $title.'&nbsp;&nbsp;<img style="max-width: 60px;" src="'.$RimageSrc.'"/>';
				}
		}
	} else if ($layout['name'] === 'plain_text') {
		$plainText = get_sub_field('plain_text_content', $layout['key']);
		if (isset($plainText)) {
			$textContent = strip_tags(implode(" ", array_slice(explode(" ", html_entity_decode($plainText)), 0, 10))).'...';
			$title = $title.'&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:300; font-size: 12px;color: #666;">'.$textContent.'</span>';
		}
	} else if ($layout['name'] === 'button') {
		$buttonCTA = get_sub_field('cta_text', $layout['key']);
		if ($buttonCTA) {
			$title = $title.'&nbsp;&nbsp;<button>'.$buttonCTA.'</button>';
		}
	}

    return $title;
}

add_filter('acf/fields/flexible_content/layout_title', 'id_filter_acf_chunk_title', 10, 4);



//Chunk Functions

//set up standard cols
function fillImage($imgUrl,$imgLink,$imgAlt,$mobileImgs,$mobImgUrl,$imageWidth) {
	if (!$imgUrl) {
		return false;
	}
	$dtClass='';
	$htmlComment = $imageWidth.' Image -->';
	if ($mobileImgs == 'alt' || $mobileImgs == 'hide') {
		$dtClass = 'hide-mobile';
		$htmlComment = $imageWidth.' Image Desktop-->';
	}
$colImage = '
                                          <!-- '.$htmlComment.'
                                          <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="width:100%;max-width:100%;" class="'.$dtClass.'">
                                            <tr>
                                              <td align="center" valign="top" class="img-responsive">
                                                <a href="'.$imgLink.'">
                                                  <img style="display:block;width:100%;max-width:'.$imageWidth.'px;display:block;border:0px;" width="'.$imageWidth.'" src="'.$imgUrl.'" border="0" alt="'.$imgAlt.'" />
                                                </a>
                                              </td>
                                            </tr>
                                          </table>
                                          <!-- / End '.$htmlComment;
if ($mobileImgs == 'alt') {
$colImage .= '
                                          
                                          <!-- '.$imageWidth.' Image Mobile Start -->
                                          <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="width:100%;max-width:100%;" class="hide-desktop">
                                              <tr>
                                                <td align="center" valign="top" class="img-responsive">
                                                  <a href="'.$imgLink.'">
                                                   <img style="display:block;width:100%;max-width:'.$imageWidth.'px;display:block;border:0px;" width="'.$imageWidth.'" src="'.$mobImgUrl.'" border="0" alt="'.$imgAlt.'" />
                                                  </a>
                                                </td>
                                              </tr>
                                            </table>
                                          <!-- /End '.$imageWidth.' Image Mobile -->
                                          
';
	}
	return $colImage;
}

function fillText($textContent,$align,$fontColor,$bgColor, $centerOnMobile,$spacing=array('top','bottom'), $padText = false) {
	if (!$textContent) {
		return false;
	}
$centerMobile = '';
if ($centerOnMobile == true) {
	$centerMobile = 'center-on-mobile';
}
$topSpacing = false;
$btmSpacing = false;
if (in_array('top',$spacing)) {
$topSpacing = true;
}
if (in_array('bottom',$spacing)) {
$btmSpacing = true;
}
if ($padText) {
	$textPadding = 'padding: 20px;';
} else {
	$textPadding = '';
}

$colText = '';
if($topSpacing) {
                              $colText .= '
                                          <!-- Optional Top Space -->
                                          <table role="presentation" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;background-color:'.$bgColor.'">
                                            <tr>
                                              <td class="space-control" valign="middle" align="center" height="20"></td>
                                            </tr>
                                          </table>
                                          <!-- / End Optional Top Space -->
                                          ';
}
$colText .= '
                                          <!-- Text Start -->
                                          <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="width:100%;max-width:100%;">
                                            <tr>
                                              <td class="text responsive-text '.$align.'-text '.$centerMobile.'" valign="middle" align="'.$align.'" style="'.$textPadding.' font-family:Poppins, sans-serif;color:'.$fontColor.' !important;text-decoration:none;">
                                                '.$textContent.'
                                              </td>
                                            </tr>
                                          </table>
                                          <!-- /End Text -->
                                          
';
if($btmSpacing) {
                              $colText .= '
                                          <!-- Optional Top Space -->
                                          <table role="presentation" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;background-color:'.$bgColor.'">
                                            <tr>
                                              <td class="space-control" valign="middle" align="center" height="20"></td>
                                            </tr>
                                          </table>
                                          <!-- / End Optional Top Space -->
                                          ';
}
	return $colText;
}


function inline_button($inlineButton=false) {
	if (!$inlineButton) {
		return;
	}
	
	$buttonText = $inlineButton['button_text'];
	$buttonUrl = $inlineButton['button_url'];
	$buttonSettings = $inlineButton['button_settings'];
		$bgColor = $buttonSettings['button_background_color'] ?? '#94d500';
		$chunkBgColor = $buttonSettings['chunk_background_color'] ?? '#FFFFFF';
		$textColor = $buttonSettings['text_color'] ?? '#FFFFFF';
		$borderColor = $buttonSettings['border_color'] ?? '#94d500';
		$borderSize = $buttonSettings['border_size'] ?? '1px';
		$borderRad = $buttonSettings['border_radius'] ?? '3px';
		$mobileVis = $buttonSettings['mobile_visibility'] ?? true;
		$spacing = $buttonSettings['spacing'] ?? array('top','bottom');
		$hideMobile = '';
		if ($mobileVis == false) {
			$hideMobile = 'hide-mobile';
		}
		$topSpacing = false;
		$btmSpacing = false;
		if (in_array('top',$spacing)) {
			$topSpacing = true;
		}
		if (in_array('bottom',$spacing)) {
		$btmSpacing = true;
		}
		
	ob_start();
	 if($topSpacing) {?>
                                               <!-- Optional Top Space -->
                                               <table class="<?php echo $hideMobile; ?>" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;background-color:<?php echo $chunkBgColor; ?>;">
                                                  <tbody>
                                                    <tr>
                                                      <td class="space-control" valign="middle" align="center" height="20">
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                               <!-- / End Optional Top Space -->
<?php } ?>
                                               
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="<?php echo $hideMobile; ?>" >
                                                  <tbody>
                                                    <tr>
                                                      <td>
                                                        <table border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0 auto;">
                                                          <tbody>
                                                            <tr style="color:<?php echo $textColor; ?>;">
                                                            
                                                            <!--Button Content Start-->
                                                              <td align="center" bgcolor="<?php echo $bgColor; ?>" style="border-radius:<?php echo $borderRad; ?>; color:<?php echo $textColor; ?>;">
                                                              <a target="_blank"
                                                                class="button-link" 
                                                                style="
                                                                  font-size:19px;font-family:Poppins, sans-serif;line-height:24px;font-weight: bold;text-decoration:none;
                                                                  display:inline-block;
																  margin: 0 auto;
																  padding:14px 30px;
                                                                  color:<?php echo $textColor; ?>;
                                                                  border-radius:<?php echo $borderRad; ?>;
                                                                  border:<?php echo $borderSize; ?> solid <?php echo $borderColor; ?>;
                                                                "
                                                                href="<?php echo $buttonUrl; ?>">
                                                                <span style="color:<?php echo $textColor; ?>;"><?php echo $buttonText; ?></span>
                                                              </a>
                                                              </td>
                                                            <!--/End Button Content-->
                                                            
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
<?php if($btmSpacing) {?>
                                               <!-- Optional Bottom Space -->
                                               <table class="<?php echo $hideMobile; ?>" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;background-color:<?php echo $chunkBgColor; ?>;">
                                                  <tbody>
                                                    <tr>
                                                      <td class="space-control" valign="middle" align="center" height="20">
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                               <!-- / End Optional Bottom Space -->
<?php }
 
return ob_get_clean();
}

function load_mobile_css() {
  $css_file = get_stylesheet_directory_uri().$_POST['css_file'];
  $output = '<link rel="stylesheet" href="' . $css_file . '" type="text/css" />';
  echo $output;
  die();
}
add_action('wp_ajax_load_mobile_css', 'load_mobile_css');
add_action('wp_ajax_nopriv_load_mobile_css', 'load_mobile_css');


//Post breadcrumb
function display_post_category_hierarchy($post_id) {
  $categories = get_the_category($post_id);
  $assigned_category = $categories[0];
  $category_links = array();

  while ($assigned_category) {
    if (!is_wp_error($assigned_category)) {
      $category_links[] = '<a href="' . get_category_link($assigned_category->term_id) . '">' . $assigned_category->name . '</a>';
      $assigned_category = get_category($assigned_category->parent);
    } else {
      break;
    }
  }

  $category_links = array_reverse($category_links);
  echo implode(' > ', $category_links);
}


// Alter the main query on the category archive page
function id_pre_get_posts( $query ) {
    if (!$query->get_queried_object()) {
        return;
    }
    if ( $query->is_category() && $query->is_main_query()) {

        // Add pagination
        // Set the number of posts to display per page
        $posts_per_page = get_option( 'posts_per_page' );

        // Get the current page number
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

        // Set the number of posts to display per page and the current page for the query
        $query->set( 'posts_per_page', $posts_per_page );
        $query->set( 'paged', $paged );

        // Get the current category
        $category = $query->get_queried_object();
		
        // Exclude posts in child categories
        $children = get_categories( array( 'parent' => $category->term_id ) );
        $children_ids = array();
        foreach ( $children as $child ) {
            $children_ids[] = $child->term_id;
        }
        $query->set( 'category__not_in', $children_ids );

        // Include the trashed posts if in the trashed category
        if ( $category->term_id == 12 ) {
            $query->set( 'post_status', 'trash' );
        }

		// Get the current user ID
		$current_user_id = get_current_user_id();
		
		// Get an array of post IDs that the current user has marked as favorite templates
		$favorite_templates = get_user_meta( $current_user_id, 'favorite_templates', true );

        $favorite_templates = is_array($favorite_templates) ? $favorite_templates : [];

        // Filter the favorite templates array to contain only those post IDs 
        // which are associated exactly with the current category.
        $favorite_templates = array_filter($favorite_templates, function($post_id) use ($category) {
            $post_categories = wp_get_post_categories($post_id); // Get IDs of categories of the post
            return in_array($category->term_id, $post_categories); // Check if current category id is in the post's categories
        });

        $category_posts = get_posts( array(
            'posts_per_page' => -1,
            'category' => $category->term_id,
            'post__not_in' => $favorite_templates,
            'post_status' => array('publish','trash'),
        ) );

        // Sort the category posts by modified date
        usort( $category_posts, function( $a, $b ) {
            return strtotime( $b->post_modified ) - strtotime( $a->post_modified );
        } );

        // Merge the filtered favorite templates array with the reversed category posts array
        $all_posts = array_merge( wp_list_pluck( $category_posts, 'ID' ), array_values($favorite_templates) );

        // Modify the orderby and order parameters to sort by the modified post__in parameter
        $query->set( 'post__in', $all_posts );
        $query->set( 'orderby', 'modified' );
		$query->set( 'order', 'DESC' );


    }
}
add_action( 'pre_get_posts', 'id_pre_get_posts' );


//Category page breadcrumb
function display_category_hierarchy() {
  $categories = get_queried_object();
  $category_links = array();

  while ($categories) {
    if (!is_wp_error($categories)) {
      if ($categories->term_id == get_queried_object_id()) {
        $category_links[] = '<span>' . $categories->name . '</span>';
      } else {
        $category_links[] = '<a href="' . get_category_link($categories->term_id) . '">' . $categories->name . '</a>';
      }
      $categories = get_category($categories->parent);
    } else {
      break;
    }
  }

  $category_links = array_reverse($category_links);
  echo implode(' > ', $category_links);
}

//delete_user_meta(1,'favorites');

// Delete a folder and move its contents to where the user specified
function id_delete_folder() {
    $category_ids = $_POST['this_folder']; // Can be an array
    $new_category_id = $_POST['move_into'];

    // Loop through categories
    foreach ($category_ids as $category_id) {
        // Get the category object for the category to be deleted
        $category = get_category($category_id);

        // Check if the category exists
        if (!$category) {
            $error_message = 'Category does not exist.';
            wp_send_json_error(array('error' => $error_message));
            return;
        }

        // Get the posts that are assigned to the category
        $posts = get_posts(array(
            'category' => $category_id,
            'numberposts' => -1
        ));

        // Loop through the posts and move them to the new category
        foreach ($posts as $post) {
            wp_set_post_categories($post->ID, array($new_category_id), true);
        }

        // Get child categories
        $child_categories = get_categories(array(
            'child_of' => $category_id,
        ));

        // Loop through child categories and move them to the new category
		foreach ($child_categories as $child_category) {
			$child_category_id = $child_category->term_id;  // Get the term_id property of the WP_Term object
			$args = array(
				'ID' => $child_category_id,
				'parent' => $new_category_id
			);
			wp_update_term($child_category_id, 'category', $args);
		}

        // Get all users
        $users = get_users();

        // Loop through the users
        foreach ($users as $user) {
            $favorites = get_user_meta($user->ID, 'favorite_folders', true);

            // If the category is in the user's favorites, remove it
            if (is_array($favorites) && in_array($category_id, $favorites)) {
                $key = array_search($category_id, $favorites);
                unset($favorites[$key]);
                update_user_meta($user->ID, 'favorite_folders', $favorites);
            }
        }

        // Delete the category
        $result = wp_delete_category($category_id);

        if ($result instanceof WP_Error) {
            $error_message = $result->get_error_message();
            wp_send_json_error(array('error' => $error_message));
            return;
        }
    }

    // Send success response with additional data
    wp_send_json_success(array(
        'newFolderLink' => get_category_link($new_category_id),
    ));
}

add_action('wp_ajax_id_delete_folder', 'id_delete_folder');
add_action('wp_ajax_nopriv_id_delete_folder', 'id_delete_folder');





// Determine if a post or category is in the current user's favorites
function is_user_favorite( $object_id, $object_type ) {
  // Determine the meta key based on the object_type
  $meta_key = 'favorite_' . strtolower($object_type) . 's'; // either 'favorite_templates' or 'favorite_folders'

  $favorites = get_user_meta( get_current_user_id(), $meta_key, true );

  if ( ! is_array( $favorites ) ) {
    $favorites = array();
  }

  // Cast IDs in favorites to integers for consistent comparison
  $favorites = array_map( 'intval', $favorites );
  
  $object_id = intval( $object_id );  // Ensure object_id is an integer

  // Check if $object_id is in favorites
  if ( in_array( $object_id, $favorites ) ) {
    return true;
  }
  
  return false;
}

// Add or remove a favorite template or folder from a user's profile
function add_remove_user_favorite() {
  // Ensure object_id and object_type are set
  $object_id = isset( $_POST['object_id'] ) ? intval( $_POST['object_id'] ) : 0;
  $object_type = isset( $_POST['object_type'] ) ? sanitize_text_field( $_POST['object_type'] ) : '';

  if ( $object_id <= 0 || empty($object_type) ) {
    wp_send_json( array(
      'success' => false,
      'message' => 'Invalid object id or object type was sent!',
      'action' => null,
      'objectid' => $object_id,
    ) );
  }

  // Determine the meta key based on the object_type
  $meta_key = 'favorite_' . strtolower($object_type) . 's'; // either 'favorite_templates' or 'favorite_folders'

  $favorites = get_user_meta( get_current_user_id(), $meta_key, true );

  if ( ! is_array( $favorites ) ) {
    $favorites = array();
  }

  $success = false;
  $message = '';
  $action = '';

  $key = array_search( $object_id, $favorites );
  if ( false !== $key ) {
    unset( $favorites[ $key ] );
    $message = 'Favorite ' . $object_type . ' removed.';
    $action = 'removed';
  } else {
    $favorites[] = intval( $object_id );  // Ensure object_id is an integer
    $message = 'Favorite ' . $object_type . ' added.';
    $action = 'added';
  }
  $success = true;

  if ( $success ) {
    $update_status = update_user_meta( get_current_user_id(), $meta_key, $favorites );
    if ( $update_status === false ) {
      $success = false;
      $message = 'Failed to update user meta.';
    } else {
      $updated_favorites = get_user_meta( get_current_user_id(), $meta_key, true );
      if ( ! is_array( $updated_favorites ) ) {
        $success = false;
        $message = 'User meta was updated but the structure is incorrect.';
      } else {
		// Check if the object_id was correctly added or removed
		if ( $action === 'added' && ! in_array( $object_id, $updated_favorites ) ) {
			$success = false;
			$message = 'Object id was not added correctly to ' . $object_type . '.';
		} elseif ( $action === 'removed' && in_array( $object_id, $updated_favorites ) ) {
			$success = false;
			$message = 'Object id was not removed correctly from ' . $object_type . '.';
		}
      }
    }
  }

  wp_send_json( array(
    'success' => $success,
    'message' => $message,
    'action' => $action,
    'objectid' => $object_id,
  ) );
}

add_action('wp_ajax_add_remove_user_favorite', 'add_remove_user_favorite');
add_action('wp_ajax_nopriv_add_remove_user_favorite', 'add_remove_user_favorite');


// Generates the list of folders on the template table sidebar
function get_folder_list($current_category_id=null) {
    $args = array(
        'orderby' => 'name',
        'hide_empty' => false,
        'hierarchical' => false,
        'depth' => 0,
        'child_of' => 0,
        'taxonomy' => 'category',
    );

    $favorite_folders = get_user_meta(get_current_user_id(), 'favorite_folders', true);
    $favorite_folder_cat_ids = !empty($favorite_folders) ? $favorite_folders : array();
	
	$favorite_templates = get_user_meta(get_current_user_id(), 'favorite_templates', true);
    $favorite_template_cat_ids = !empty($favorite_templates) ? $favorite_templates : array();

    $faves_list = '';
        
	if (!empty($favorite_folders)) {
		$faves_list .= '<h5>Favorite Folders</h5>';
		$faves_list .= '<ul>';
		foreach ($favorite_folder_cat_ids as $fav_folder_cat_id) {
			$term = get_term($fav_folder_cat_id, 'category');
			if ($term && !is_wp_error($term)) {
				$faves_list .= '<li class="favItem"><a href="' . get_term_link($term) . '"><i class="fa-solid fa-folder"></i>&nbsp;&nbsp;' . $term->name . '</a>&nbsp;&nbsp;<i title="Remove Favorite" class="fa-solid fa-circle-minus addRemoveFavorite" data-objecttype="Folder" data-objectid="'.$fav_folder_cat_id.'"></i></li>';
			}
		}
		$faves_list .= '</ul>';
	}
	if (!empty($favorite_templates)) {
			$faves_list .= '<h5>Favorite Templates</h5>';
			$faves_list .= '<ul>';
			foreach ($favorite_template_cat_ids as $fav_template_id) {
				$template = get_post($fav_template_id);
				if ($template && !is_wp_error($template)) {
					$faves_list .= '<li class="favItem"><a href="' . get_the_permalink($fav_template_id) . '"><i class="fa-solid fa-file"></i>&nbsp;&nbsp;' . get_the_title($fav_template_id) . '</a>&nbsp;&nbsp;<i title="Remove Favorite" class="fa-solid fa-circle-minus addRemoveFavorite" data-objecttype="Template" data-objectid="'.$fav_template_id.'"></i></li>';
				}
			}
		$faves_list .= '</ul>';
	}
	
	
	

    $categories = get_terms($args);

    $categories_list = '';
    if (!empty($categories)) {
        $categories_list .= '<h5>All Folders<div id="addNewFolder"><i title="Add new folder" class="fa-solid fa-circle-plus"></i></div></h5>';
		
		$categories_list .= '<ul>';
        foreach ($categories as $category) {
            if ($category->parent == 0) {
                $categories_list .= get_category_tree($category, $current_category_id);
            }
        }

        $categories_list = str_replace('<i class="fa-solid fa-folder"></i>&nbsp;&nbsp;Trashed', '<i class="fa fa-trash"></i>&nbsp;&nbsp;Trashed', $categories_list);
		$categories_list .= '</ul>';
        
		
		$folder_list = $categories_list . $faves_list;
        
    }

    echo $folder_list;
}

// Helper function to check if a category is an ancestor of the current category
function is_parent($parent, $child) {
	if (!$parent || !$child) {
		return false;
	}
    // Check if the parent is the direct parent of the child
	if (isset($child->parent)) {
		if ($child->parent == $parent->term_id) {
			return true;
		}
	} else {
		return false;
	}

    // If the child has a parent, and it's not the parent we're checking for,
    // Get the parent of the child and check again
    if ($child->parent != 0) {
        return is_parent($parent, get_category($child->parent));
    }

    // If no ancestors were the parent we're checking for, return false
    return false;
}


//Generate the category (folder) tree
function get_category_tree($category, $current_category_id = null) {
    $link = get_term_link($category);
    $name = $category->name;
    $current_class = ($current_category_id == $category->term_id) ? ' current-cat' : '';
    $has_sub_cats_class = '';
    $sub_cats_collapse_toggle = '';

    $sub_categories = get_categories(array('parent' => $category->term_id, 'hide_empty' => false));
    if ($sub_categories) {
        $has_sub_cats_class = ' has-sub-cats';
        $sub_cats_collapse_toggle = '<i class="fa-solid fa-angle-up showHideSubs"></i>';
    }

    $html = '<li class="cat-item cat-item-' . $category->term_id . $current_class . $has_sub_cats_class . '"><a href="' . $link . '">';
    $html .= ($current_category_id == $category->term_id || is_parent($category, get_category($current_category_id)) || $category->term_id == 1) ? '<i class="fa-regular fa-folder-open"></i>&nbsp;&nbsp;' : '<i class="fa-solid fa-folder"></i>&nbsp;&nbsp;';
    $html .= $name . '</a>' . $sub_cats_collapse_toggle;

    if ($sub_categories) {
        $html .= '<ul class="sub-categories">';
        foreach ($sub_categories as $sub_category) {
            $html .= get_category_tree($sub_category, $current_category_id);
        }
        $html .= '</ul>';
    }

    $html .= '</li>';

    return $html;
}




//Add a new folder to the folder tree
function id_add_new_folder() {
	$category_name = sanitize_text_field( $_POST['folder_name'] );
	$parent_folder = $_POST['parent_folder'];
	
    $response = wp_insert_term( $category_name, 'category', array('parent'=>$parent_folder)); 
	
    wp_send_json_success( $response );
    wp_die();
}
add_action('wp_ajax_id_add_new_folder', 'id_add_new_folder');
add_action('wp_ajax_nopriv_id_add_new_folder', 'id_add_new_folder');

//Move a template to another folder
function id_move_template() {
	// Set the post ID and the new category ID
	$thisTemplate = $_POST['this_template'];
	
	foreach ($thisTemplate as $template) {
		$moveInto = $_POST['move_into'];

		// Assign the post a new category and remove previous ones
		$setCategories = wp_set_post_categories($template, array($moveInto));
	}

	// Get the new category's link
	$newCategoryLink = get_category_link($moveInto);

	$return = array(
		'moveTemplate' => $setCategories,
		'newFolderLink' => $newCategoryLink,
	);
	
	
	wp_send_json_success( $return ); // respond is an array including term id and term taxonomy id
	wp_die();
}

add_action('wp_ajax_id_move_template', 'id_move_template');
add_action('wp_ajax_nopriv_id_move_template', 'id_move_template');


//Move a folder to another folder
function id_move_folder() {
    $thisFolder = $_POST['this_folder'];
    $moveInto = $_POST['move_into'];
	
    foreach ($thisFolder as $folder) {
        $moveFolder = wp_update_term($folder, 'category', array(
            'parent' => $moveInto,
        ));
        // Check for any errors
        if (is_wp_error($moveFolder)) {
            $return = array(
                'error' => $moveFolder->get_error_message(), // Get the error message
            );
            wp_send_json_error($return); // Send the error message in the response
            wp_die();
        }
    }

    // Get the new parent category's link
    $newParentLink = get_category_link($moveInto);

    // Use the new parent's link instead of the moved category's link
    $return = array(
        'moveCat' => $moveFolder,
        'newFolderLink' => $newParentLink,
    );
    wp_send_json_success($return); // respond is an array including term id and term taxonomy id
    wp_die();
}


add_action('wp_ajax_id_move_folder', 'id_move_folder');
add_action('wp_ajax_nopriv_id_move_folder', 'id_move_folder');

// Generate a drop-down list of categories
function id_generate_cats_select($parent_id = 0, $prefix = '') {
    $options = '';

    $categories = get_categories(array('parent' => $parent_id, 'hide_empty' => false));
    //$options .= '<option value="test">Test Options</option>';
    
    foreach ($categories as $category) {
		if ($category->term_id == 12) {
			//skips the trash category
			continue;
		}
        $name = $category->name;
        $options .= '<option value="' . $category->term_id . '">' . $prefix . $name . '</option>';
        $options .= id_generate_cats_select($category->term_id, '&nbsp;&nbsp;'.$prefix . '-&nbsp;&nbsp;');
    }

    return $options;
}

function id_generate_cats_select_ajax() {
    $options = id_generate_cats_select();
    wp_send_json_success(array('options' => $options));
    wp_die();
}

add_action('wp_ajax_id_generate_cats_select_ajax', 'id_generate_cats_select_ajax');
add_action('wp_ajax_nopriv_id_generate_cats_select_ajax', 'id_generate_cats_select_ajax');



