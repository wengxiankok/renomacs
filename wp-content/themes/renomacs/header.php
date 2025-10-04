<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<?php
		wp_head();
	?>
	<link rel="stylesheet" href="<?= bloginfo('template_url') ?>/assets/css/main.css">

</head>

<body <?php body_class(); ?>>
    <header>
		<div class="d-flex flex-row justify-content-between container align-items-center">
			<div>
				Logo
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
