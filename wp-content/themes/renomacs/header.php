<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<?php $keyword = get_field("keywords"); ?>
	<meta content="<?php echo $keyword ? $keyword : ""; ?>" name="keywords" />
<?php
	// $f2x_favicon = assetLink.'/img/logo.svg';
	// echo '
	// 	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	// 	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	// 	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	// 	<link rel="manifest" href="/site.webmanifest">
	// ';
	wp_head();
?>

</head>

<body <?php body_class(); ?>>
    <header>

    </header>
    
    <main>
