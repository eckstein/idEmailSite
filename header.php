<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
	
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="wrapper" class="hfeed">
<header id="header" role="banner">
<div id="branding">
	<div id="site-logo">
	<a href="<?php echo get_bloginfo('url'); ?>"><img src="https://idemailwiz.com/wp-content/uploads/2023/10/id-tech-icon.png"></a>
	</div>
	<h1 id="site-title"><a href="<?php echo get_bloginfo('url'); ?>">Email Wizard</a></h1>
	<div id="site-nav">
		<?php //wp_nav_menu('main-menu'); ?>
		<?php wp_nav_menu( array( 'theme_location' => 'primary-menu' ) ); ?>
	</div>
	<div id="headerUserInfo">
	<?php
	// Check if a user is logged in
	if ( is_user_logged_in() ) {
	  // Get the current user's info
	  $current_user = wp_get_current_user();
	  // Display the username and logout link
	  echo 'Logged in as ' . esc_html( $current_user->user_login ) . '&nbsp;&nbsp;<a href="' . esc_url( wp_logout_url() ) . '">logout</a>';
	  echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="access-user-settings"><i class="fa fa-gear"></i></a>';
	}
	?>

	</div>
</div>

</header>
<div id="container">
<main id="content" role="main">