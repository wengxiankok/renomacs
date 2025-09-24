<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<link rel="stylesheet" href="<?= bloginfo('template_url') ?>/assets/css/main.css">
<?php
	wp_head();
?>

</head>

<body <?php body_class(); ?>>
    <header>
		<div class="d-flex flex-row justify-content-between container align-items-center">
			<div>
				Logo
			</div>
			<div>
				<?php
					$vals = array(
						'theme_location' => 'primary-menu',
						'container' => '',
						'menu_class' => 'rm-navigation',
						'depth' => 0,
						'echo' => true,
					); wp_nav_menu( $vals );
				?>
			</div>	
		</div>
    </header>
    
    <main>
