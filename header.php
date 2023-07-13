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
	<a href="<?php echo get_bloginfo('url'); ?>"><img src="http://localhost/wp-content/uploads/2023/03/id-tech-icon.png"></a>
	</div>
	<h1 id="site-title"><a href="<?php echo get_bloginfo('url'); ?>">Email Template Wizard</a></h1>
	<div id="site-nav">
		<ul>
			<li>
			<a class="button" href="<?php echo get_bloginfo('url'); ?>"><i class="fa fa-plus"></i>&nbsp;&nbsp;New Template</a>
			</li>
			<li>
			<a class="button" href="<?php echo get_bloginfo('url'); ?>/templates/all-templates"><i class="fa fa-table-list"></i>&nbsp;&nbsp;Template List</a>
			</li>
			<li>
			<a class="button" href="<?php echo get_page_link(366); ?>"><i class="fa fa-solid fa-code"></i>&nbsp;&nbsp;Code Repo</a>
			</li>
		</ul>
	</div>
	<div id="search">
	<?php get_search_form(); ?>
	</div>
</div>

</header>
<div id="container">
<main id="content" role="main">