<section class="content-image-section" <?php if ($attributes['custom-id']) : echo 'id="'. $attributes['custom-id'] .'"' ; endif;  ?>>
    <div class="container py-20">
        <div class="row align-items-center gap-10">
            <?php if ($attributes['image']): ?>
                <div class="col-md-6 <?php echo $attributes['image-position'] ?>">
                    <?php
                        $lbPic = wp_get_attachment_metadata($attributes['image']['id']);
                        echo '<img
                            loading="lazy"
                            src="'.esc_url( $attributes['image']['url'] ).'"
                            alt="'.esc_attr( $attributes['image']['alt'] ).'"
                            width="'.($lbPic['width'] ?: '100').'"
                            height="'.($lbPic['height'] ?: '100').'"
                            class="img-fluid p-lg-4 rounded"
                        >';
                    ?>
                </div>
            <?php endif; ?>
            <div class="<?php echo ($attributes['image']) ? 'col-md-6' : 'col-12'; ?> <?php if ($attributes['image-position'] === 'order-1') : echo 'order-2' ; else : echo 'order-1'; endif; ?>">
                <?php if ($attributes['eyebrow'] || $attributes['header'] || $attributes['description']) : ?>
                    <div class="pb-2 pt-3 pt-lg-0">
                        <?php if ($attributes['eyebrow']) : ?>
                            <p class="text-eyebrow text-uppercase mb-2"><?php echo $attributes['eyebrow']; ?></p>
                        <?php endif; ?>
                        <?php if ($attributes['header']) : ?>
                            <h2><?php echo $attributes['header']; ?></h2>
                        <?php endif; ?>
                        <?php if ($attributes['description']) : ?>
                            <?php echo $attributes['description']; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($attributes['primary-cta-link'] || $attributes['secondary-cta-link']) : ?>
                    <div class="d-flex flex-column pt-3 <?php if ($attributes['secondary-cta-link']) : echo 'flex-lg-row gap-4' ; endif; ?>">
                        <?php if ($attributes['primary-cta-link'] ): ?>
                            <a href="<?php echo $attributes['primary-cta-link']; ?>" class="btn btn-primary"><?php echo $attributes['primary-cta-text']; ?></a>
                        <?php endif; ?>

                        <?php if ($attributes['secondary-cta-link'] ): ?>
                            <a href="<?php echo $attributes['secondary-cta-link']; ?>" class="btn btn-secondary"><?php echo $attributes['secondary-cta-text']; ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>