<?php
require_once( dirname(dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

ob_start();
acf_form(array(
	'post_id' => 'options',
	'fields' => array('field_640559a70cf51'), // Set the field(s) to be displayed in the form
	'submit_value' => 'Select Folder', // Set the label for the submit button
));
$acfCatsForm = ob_get_clean();

$id_ajax_settings = array(
    'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
    'currentPost' => get_post( get_the_ID() ),
    'nonce' => wp_create_nonce( 'id-nonce' ),
    'stylesheet' => get_stylesheet_directory_uri(),
	'acfCatList' => $acfCatsForm,
);
?>

<script type="text/javascript">
var idAjax = <?php echo json_encode( $id_ajax_settings ); ?>;
</script>
