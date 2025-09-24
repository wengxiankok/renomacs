
            <footer>
                <?php
                    $vals = array(
                        'theme_location' => 'footer-menu',
                        'container' => '',
                        'menu_class' => 'rm-footer',
                        'depth' => 0,
                        'echo' => true,
                    ); wp_nav_menu( $vals );
                ?>
            </footer>
        </main>

        <script src="<?= bloginfo('template_url') ?>/assets/js/min/app.js" charset="utf-8"></script>
        <?php wp_footer(); ?>

    </body>

</html>