
            <footer>
                <div class="container d-flex justify-content-between pt-5 pb-4">
                    <div class="d-flex flex-column contact-information">
                        <p class="h3 text-white pb-5">Renomax</p> <!-- Logo can go here -->
                        <p>Address goes here</p>
                        <p>Phone: (123) 456-7890</p>
                        <p>Email:</p>
                    </div>
                    <div>
                        <?php
                            $vals = array(
                                'theme_location' => 'footer-menu',
                                'container' => '',
                                'menu_class' => 'rm-footer',
                                'depth' => 0,
                                'echo' => true,
                            ); wp_nav_menu( $vals );
                        ?>
                    </div>
                </div>
                <div>
                    <div class="container text-end pb-5">
                        <p class="text-white mb-0">© <?php echo date("Y"); ?> Renomax. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </main>

        <?php wp_footer(); ?>
        <script src="<?= bloginfo('template_url') ?>/assets/js/min/app.js" charset="utf-8"></script>

    </body>

</html>