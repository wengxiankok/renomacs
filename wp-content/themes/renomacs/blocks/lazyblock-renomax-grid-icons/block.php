<section class="grid-icons">
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
        <div class="row">
            <?php foreach ($attributes['grid-items'] as $item) : ?>
                <div class="col-12 <?php if (count($attributes['grid-items']) === 3 ) : echo 'col-md-4' ; else : echo 'col-md-6' ; endif;  ?> text-center">
                    <?php
                        if ($item['icon']) :
                            $lbPic = wp_get_attachment_metadata($item['icon']['id']);
                            echo '<img
                                loading="lazy"
                                src="'.esc_url( $item['icon']['url'] ).'"
                                alt="'.esc_attr( $item['icon']['alt'] ).'"
                                width="'.($lbPic['width'] ?: '100').'"
                                height="'.($lbPic['height'] ?: '100').'"
                                class="icon mb-3"
                            >';
                        endif;
                    ?>
                    <?php if ($item['title']) : ?>
                        <p class="h5"><?php echo esc_html($item['title']); ?></p>
                    <?php endif; ?>
                    <?php if ($item['description']) : ?>
                        <?php echo ($item['description']); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($attributes['cta-link']) : ?>
            <div class="text-center mt-4">
                <a href="<?php echo $attributes['cta-link']; ?>" class="btn btn-primary"><?php echo $attributes['cta-text']; ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>