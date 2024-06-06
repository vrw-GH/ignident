<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<title><?= ! empty( $this->title ) ? $this->title : __( 'Page not found' ) ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="<?= LS_ROOT_URL.'/static/public/blank-template.css'?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> style="background: <?= ! empty( $this->background ) ? $this->background : 'transparent' ?> !important;">

<?php
if( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
} else {
	do_action( 'wp_body_open' );
}
?>

<div id="ls-template-outer-wrapper">
	<div id="ls-template-projects-wrapper">
		<?php layerslider( (int) $this->project ); ?>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>