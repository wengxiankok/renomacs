<?php
    get_header();

    /* 
        Template Name: T&C / Privacy Policy Template
    */

?>
    <section class="tnc-privacy">
        <div class="container contact-wrapper pb-5">
            <h1 class="mb-4"><?php echo get_the_title(); ?></h1>
            <div class="text-left">
                <?php
                    while (have_posts()) : the_post();
                        the_content();
                    endwhile;
                ?>
            </div>
        </div>
    </section>

    <a href="https://wa.link/vw98jd" target="blank_"><div id="whatsapp-icon"></div></a>
    <div id="back-to-top"></div>
<?php
    get_footer();
?>
