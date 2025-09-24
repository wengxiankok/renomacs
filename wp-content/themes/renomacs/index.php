<?php
    get_header();

    while (have_posts()) : the_post();
        the_content();
    endwhile;
?>
<section style="height: 100vh">About Us</section>
<section style="height: 100vh">Why RenoMax?</section>
<section style="height: 100vh">Our Services</section>
<section style="height: 100vh">Coverage</section>
    <div id="back-to-top"></div>
<?php
    get_footer();
?>
