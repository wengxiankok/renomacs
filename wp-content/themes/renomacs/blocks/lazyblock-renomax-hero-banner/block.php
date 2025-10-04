<section class="hero-banner-section">
    <div class="container">
        <div class="hero-banner" <?php if ($attributes['banner-image']) : echo 'style="background-image: url(' . $attributes['banner-image'] .');"' ; endif; ?>>
            <div class="hero-content col-12 col-lg-8">
                <h1><?php echo $attributes['header']; ?></h1>
                <?php echo $attributes['description'] ?>
                <div class="d-flex flex-column <?php if ($attributes['secondary-cta-link']) : echo 'flex-lg-row gap-4' ; endif; ?>">
                    <?php if ($attributes['primary-cta-link'] ): ?>
                        <a href="<?php echo $attributes['primary-cta-link']; ?>" class="btn btn-primary"><?php echo $attributes['primary-cta-text']; ?></a>
                    <?php endif; ?>

                    <?php if ($attributes['secondary-cta-link'] ): ?>
                        <a href="<?php echo $attributes['secondary-cta-link']; ?>" class="btn btn-secondary"><?php echo $attributes['secondary-cta-text']; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>