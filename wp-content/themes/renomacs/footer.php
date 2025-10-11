
            <footer>
                <div class="container d-flex flex-column flex-lg-row justify-content-lg-between pt-5 pb-4">
                    <div class="d-flex flex-column contact-information">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="h3 text-white">Renomax</p> <!-- Logo can go here -->
                            <div class="btn btn-primary d-block d-lg-none">Get a Quote</div>
                        </div>
                        <div class="pt-2">
                            <ul>
                                <li>Address goes here</li>
                                <li>Phone: (123) 456-7890</li>
                                <li>Email:</li>
                            </ul>
                        </div>
                    </div>
                    <div class="pt-4 pt-lg-0">
                        <?php
                            $vals = array(
                                'theme_location' => 'footer-menu',
                                'container' => '',
                                'menu_class' => 'rm-footer d-flex flex-row flex-lg-column',
                                'depth' => 0,
                                'echo' => true,
                            ); wp_nav_menu( $vals );
                        ?>
                    </div>
                </div>
                <div>
                    <div class="container text-lg-end pt-3 pt-lg-0 pb-3 pb-lg-5">
                        <p class="text-white mb-0">© <?php echo date("Y"); ?> Renomax. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </main>

        <?php wp_footer(); ?>
        <script src="<?= bloginfo('template_url') ?>/assets/js/min/app.js" charset="utf-8"></script>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    </body>

</html>