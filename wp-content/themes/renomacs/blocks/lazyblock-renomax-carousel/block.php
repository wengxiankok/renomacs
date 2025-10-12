<style>
    .autoplay-progress {
      position: absolute;
      right: 16px;
      bottom: 16px;
      z-index: 10;
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: var(--swiper-theme-color);
    }
    
    .autoplay-progress svg {
      --progress: 0;
      position: absolute;
      left: 0;
      top: 0px;
      z-index: 10;
      width: 100%;
      height: 100%;
      stroke-width: 4px;
      stroke: var(--swiper-theme-color);
      fill: none;
      stroke-dashoffset: calc(125.6px * (1 - var(--progress)));
      stroke-dasharray: 125.6;
      transform: rotate(-90deg);
    }
</style>
<section class="carousel-section" <?php if ($attributes['custom-id']) : echo 'id="'. $attributes['custom-id'] .'"' ; endif;  ?>>
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
        <div class="swiper">
            <div class="swiper-wrapper align-items-center">
                <?php foreach ($attributes['carousel'] as $carousel) : ?>
                    <div class="swiper-slide">
                        <?php
                            if ($carousel['image']) :
                                $lbPic = wp_get_attachment_metadata($carousel['image']['id']);
                                echo '<img
                                    loading="lazy"
                                    src="'.esc_url( $carousel['image']['url'] ).'"
                                    alt="'.esc_attr( $carousel['image']['alt'] ).'"
                                    width="'.($lbPic['width'] ?: '100').'"
                                    height="'.($lbPic['height'] ?: '100').'"
                                    class="mb-2"
                                >';
                            endif;
                        ?>
                        <?php if ($carousel['title']) : ?>
                            <p class="text-center"><strong><?php echo esc_html($carousel['title']); ?></strong></p>
                        <?php endif; ?>
                        <?php if ($carousel['description']) : ?>
                            <?php echo ($carousel['description']); ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="swiper-navigation">
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </div>
</section>