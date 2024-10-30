<?php
/**
 *	LifePress Dashboard header template
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> style='margin-top:0!important'>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=1100,user-scalable=yes" />	
	<!-- responsive -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">	

	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	
	<?php wp_head(); ?>
	<style type='text/css'>
	</style>
</head>
<body <?php body_class(); ?>>