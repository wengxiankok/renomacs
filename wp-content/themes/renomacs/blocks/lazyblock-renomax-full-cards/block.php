<section class="fullcard-section fullcard" <?php if ($attributes['custom-id']) : echo 'id="'. $attributes['custom-id'] .'"' ; endif;  ?>>
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
        <?php 
            $cards = $attributes['cards'];
            $total = count($cards);

            $chunkSize = ($total > 4) ? 3 : $total;

            $rows = array_chunk($cards, $chunkSize);
        ?>
        <div class="row gap-lg-4 justify-content-center">
            <div class="fullcard__container">
                <?php foreach ($rows as $i => $rowCards) : ?>
                    <?php
                        $rowCount = count($rowCards);
                        $minWidth = "calc(100% / {$rowCount})";
                    ?>
                    <div class="row d-flex fullcard__row <?php if ($i > 0) : echo 'mt-lg-4' ; endif; ?>">
                        <?php foreach ($rowCards as $card) : ?>
                            <div class="fullcard__wrapper px-0" style="min-width: <?php echo esc_attr($minWidth) ?>;">
                                <?php if (!empty($card['image'])) :
                                    $lbPic = wp_get_attachment_metadata($card['image']['id']);
                                    echo '<img
                                        loading="lazy"
                                        src="' . esc_url($card['image']['url']) . '"
                                        alt="' . esc_attr($card['image']['alt']) . '"
                                        width="' . ($lbPic['width'] ?: '100') . '"
                                        height="' . ($lbPic['height'] ?: '100') . '"
                                        class="fullcard__wrapper-img img-fluid"
                                        "
                                    >';
                                endif; ?>

                                <div class="fullcard__wrapper-content p-4" style="position: absolute; bottom: 0;">
                                    <?php if ($card['card-title']) : ?>
                                        <p class="h5 card-title"><?php echo esc_html($card['card-title']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($card['card-description']) : ?>
                                        <div class="card-text"><?php echo ($card['card-description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php
                    endforeach;
                ?>
                
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const cards = document.querySelectorAll(".fullcard__wrapper");
        const container = document.querySelector(".fullcard__container");

        cards.forEach(card => {
            card.addEventListener("mouseenter", () => {
                // Remove any existing hover state
                cards.forEach(c => c.classList.remove("is-hovered", "is-dimmed"));
                // Mark hovered card
                card.classList.add("is-hovered");
                // Dim all others
                cards.forEach(c => {
                    if (c !== card) c.classList.add("is-dimmed");
                });
            });

            card.addEventListener("mouseleave", () => {
                // Reset everything when hover ends
                cards.forEach(c => c.classList.remove("is-hovered", "is-dimmed"));
            });
        });
    });
</script>