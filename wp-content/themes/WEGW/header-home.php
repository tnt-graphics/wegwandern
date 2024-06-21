<?php
/**
 * The header for our home page
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Wegwandern
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri().'/img/favicon/apple-touch-icon.png';?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri().'/img/favicon/favicon-32x32.png';?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri().'/img/favicon/favicon-16x16.png';?>">
	<link rel="manifest" href="<?php echo get_template_directory_uri().'/img/favicon/site.webmanifest';?>">
	<link rel="mask-icon" href="<?php echo get_template_directory_uri().'/img/favicon/safari-pinned-tab.svg';?>" color="#ff0000">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">

	
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
	<link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png" />
	
	<!-- config scripts:  these scripts are required for loading all slot dependencies -->
	<script type="text/javascript">
	var gbucket = gbucket || {}; var setgbpartnertag1280 = true; var setgbpartnertag1281 = true; var setgbpartnertag4440 = true; if(typeof(setgbtargetingobj) == 'undefined') {var setgbtargetingobj = {};} 
	</script>
	<script type="text/javascript" id="gbconfigscript" src="//gbucket.ch/CH/ch_config_desktop.js"></script>
	<script type="text/javascript" id="gbadtag" src="//gbucket.ch/CH/wegwandern/Desktop/DE_wegwandern_ROS_BTF_AllAdFormats.js"></script>
	<script type="text/javascript" id="gbadtag" src="//gbucket.ch/CH/wegwandern/Smartphone/DE_wegwandern_ROS_BTF_Smartphone_AllAdFormats.js"></script>
	<script type="text/javascript" id="gbadtag" src="//gbucket.ch/CH/wegwandern/Tablet/D_ROS_Banner.js"></script>

	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-QN6SKXNB8P"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-QN6SKXNB8P');
	</script>

	<?php wp_head(); ?>
	
</head>



<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$idname = '';
global $post;
if ( ! is_page( 'b2b-portal' ) ) {
	include 'inc/ads/header-ad.php';
}
?>

<header id="<?php echo $idname; ?>" >
	<div class="container">
		<div class="header_main_wrapper">
			<div>
			<?php
				$size              = 'thumbnail';
				$header_logo       = get_field( 'header_logo', 'option' );
				$header_logo_thumb = $header_logo['sizes'][ $size ];
				$header_logo_alt   = $header_logo['alt'];

				$transparent_logo       = get_field( 'transparent_logo', 'option' );
				$transparent_logo_thumb = $transparent_logo['sizes'][ $size ];
				$transparent_logo_alt   = $transparent_logo['alt'];
			?>
			 <div class="logo">
				<a class="" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php if ( ! empty( $header_logo ) ) : ?>
					<img class="normal-logo" src="<?php echo esc_url( $header_logo_thumb ); ?>" alt="<?php echo esc_attr( $header_logo_alt ); ?>" />
					<?php endif; ?>
					<?php if ( ! empty( $transparent_logo ) ) : ?>
					<img class="transparent-logo" src="<?php echo esc_url( $transparent_logo_thumb ); ?>" alt="<?php echo esc_attr( $transparent_logo_alt ); ?>" />
					<?php endif; ?>
					</a>
			</div>
		  </div>
		  <div class="header_menu">
			 <?php
				if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
					display_user_avatar_header();
				} else {
					?>
					<div class="login" onclick=""></div>
					<?php
				}
				// if ( is_plugin_active( 'wegw-b2b/wegw-b2b.php' ) ) {
					// do_action( 'b2b_user_avatar' );
				// } else {
				?>
			 
			 <!-- <div class="login" onclick=""></div> -->
			 <?php // } ?>
			 <div class="menu">
				 <img class="menu_white" src="<?php echo get_template_directory_uri() . '/img/menu_white.svg'; ?>" onclick="openMainMenu()">
				 <img class="menu_grey" src="<?php echo get_template_directory_uri() . '/img/menu.svg'; ?>" onclick="openMainMenu()">
				</div>
		  </div>
		</div>
	 
	   
   </div>
</header>
<?php
if ( is_plugin_active( 'wegw-b2b/wegw-b2b.php' ) ) {
	 do_action( 'b2b_login_reg_init' );
}

if ( function_exists('wegw_filter_html') ) {
	echo wegw_filter_html();
}

if ( function_exists('wegw_main_menu_display') ) {
	echo wegw_main_menu_display();
}
?>
