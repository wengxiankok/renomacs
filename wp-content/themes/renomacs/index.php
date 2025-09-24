<?php
    get_header();

    while (have_posts()) : the_post();
        the_content();
    endwhile;
?>
    <div id="back-to-top"></div>
<?php
    get_footer();
?>
