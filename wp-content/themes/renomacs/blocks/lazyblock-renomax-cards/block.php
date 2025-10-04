<section class="card-section">
    <div class="container py-20">
        <?php if ($attributes['section-header'] || $attributes['section-subheader'] || $attributes['section-description']) : ?>
            <div class="text-center mb-5">
                <?php if ($attributes['section-header']) : ?>
                    <h2><?php echo $attributes['section-header'] ?></h2>
                <?php endif; ?>
                <?php if ($attributes['section-subheader']) : ?>
                    <h3><?php echo $attributes['section-subheader'] ?></h3>
                <?php endif; ?>
                <?php if ($attributes['section-description']) : ?>
                    <?php echo $attributes['section-description'] ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="row gap-4 justify-content-center">
            <?php foreach ($attributes['cards'] as $card) : ?>
                <div class="col-12 <?php if (count($attributes['cards']) > 3) : echo 'col-md-4' ; elseif (count($attributes['cards']) === 3) : echo 'col-md-3' ; elseif (count($attributes['cards']) > 1) : echo 'col-md-5' ; else : echo 'col-md-6' ; endif; ?>">
                    <div class="card h-auto">
                        <?php
                            if ($card['image']) :
                                $lbPic = wp_get_attachment_metadata($card['image']['id']);
                                echo '<img
                                    loading="lazy"
                                    src="'.esc_url( $card['image']['url'] ).'"
                                    alt="'.esc_attr( $card['image']['alt'] ).'"
                                    width="'.($lbPic['width'] ?: '100').'"
                                    height="'.($lbPic['height'] ?: '100').'"
                                    class="card-img-top img-fluid mh-50 h-100 object-fit-cover aspect-ratio-16-9"
                                >';
                            endif;
                        ?>
                        <div class="card-body d-flex flex-column flex-grow-1 flex-shrink-0">
                            <?php if ($card['card-title']) : ?>
                                <p class="h5 card-title"><?php echo esc_html($card['card-title']); ?></p>
                            <?php endif; ?>
                            <?php if ($card['card-description']) : ?>
                                <div class="card-text"><?php echo ($card['card-description']); ?></div>
                            <?php endif; ?>
                            <?php if ($card['cta-link'] ): ?>
                                <div class="mt-2">
                                    <a href="<?php echo $card['cta-link']; ?>" class="btn btn-primary"><?php echo $card['cta-text']; ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>