<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<?php
		wp_head();
	?>
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
	<link rel="stylesheet" href="<?= bloginfo('template_url') ?>/assets/css/main.css">

</head>

<body <?php body_class(); ?>>
    <header>
		<div class="d-flex flex-row justify-content-between container align-items-center">
			<div>
				<img class="nav-logo" src="<?php echo get_template_directory_uri() ?>/assets/img/renomacs-white.svg" />
			</div>
			<div class="d-none d-lg-block">
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

			<div class="d-block d-lg-none">
				<!-- Hamburger toggle (visually a button, functionally a checkbox) -->
				<input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true" />
				<label for="nav-toggle" class="hamburger" aria-label="Toggle navigation" aria-controls="primary-nav" aria-expanded="false">
					<span class="bar"></span>
					<span class="bar"></span>
					<span class="bar"></span>
				</label>
	
				<nav id="primary-nav" class="site-nav container px-4" role="navigation" aria-label="Primary">
					<label for="nav-toggle" class="close-btn" aria-label="Close navigation">✕</label>
					<div class="pt-4">
						Logo
					</div>
					<?php
						$vals = array(
							'theme_location' => 'primary-menu',
							'container' => '',
							'menu_class' => 'nav-list',
							'depth' => 0,
							'echo' => true,
						); wp_nav_menu( $vals );
					?>
				</nav>
			</div>
		</div>
    </header>
    
    <main>
