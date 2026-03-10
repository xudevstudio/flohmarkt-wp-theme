<?php
/**
 * Event Archive / Calendar Page
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<div class="container">
    <?php flohmarkt_breadcrumbs(); ?>
</div>

<!-- Hero -->
<section class="hero" style="padding: 50px 0; text-align: center;">
    <div class="container">
        <h1 style="color: #fff; margin-bottom: 8px;">📅 Flohmarkt-Veranstaltungen</h1>
        <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem; max-width: 600px; margin: 0 auto;">Flohmärkte und Trödelmärkte in ganz Deutschland</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="content-wrap">
            <div class="main-content">
                <?php
                // Display the Advanced Monthly Calendar
                echo do_shortcode('[flohmarkt_calendar]');
                ?>

                <!-- List View of Latest Events -->
                <div class="latest-events-list" style="margin-top: 50px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 24px;">
                        <h2 style="font-size: 1.8rem;">Neueste Veranstaltungen</h2>
                        <div class="accent-line" style="margin: 8px 0 0 0; width: 40px;"></div>
                    </div>

                    <?php
                    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                    $events_query = new WP_Query( array(
                        'post_type'      => 'event',
                        'posts_per_page' => 10,
                        'paged'          => $paged,
                        'orderby'        => 'meta_value',
                        'meta_key'       => '_event_date',
                        'order'          => 'DESC'
                    ) );

                    if ( $events_query->have_posts() ) :
                        echo '<div id="events-list-container" style="display: flex; flex-direction: column; gap: 16px;">';
                        while ( $events_query->have_posts() ) : $events_query->the_post();
                            $date = get_post_meta( get_the_ID(), '_event_date', true );
                            $time = get_post_meta( get_the_ID(), '_event_time', true );
                            $city = get_post_meta( get_the_ID(), '_event_city', true );
                            ?>
                            <a href="<?php the_permalink(); ?>" style="display: block; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 20px; text-decoration: none; color: inherit; transition: all 0.3s ease; box-shadow: var(--shadow-sm);" onmouseover="this.style.borderColor='var(--color-primary)'; this.style.transform='translateX(8px)';" onmouseout="this.style.borderColor='var(--color-border)'; this.style.transform='translateX(0)';">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <h3 style="margin: 0; font-size: 1.25rem; color: var(--color-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php the_title(); ?></h3>
                                    <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.9rem; color: var(--color-text-light);">
                                        <?php if ( $date ) : ?>
                                            <span>📅 <?php echo date_i18n( 'j. F Y', strtotime( $date ) ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $time ) : ?>
                                            <span>⏰ <?php echo esc_html( $time ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $city ) : ?>
                                            <span>📍 <?php echo esc_html( $city ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin: 8px 0 0 0; font-size: 0.95rem; line-height: 1.5; color: var(--color-text);"><?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?></p>
                                </div>
                            </a>
                            <?php
                        endwhile;
                        echo '</div>'; // End #events-list-container

                        // Load More Button
                        if ( $events_query->max_num_pages > 1 ) :
                            echo '<div class="load-more-wrapper" style="text-align: center; margin-top: 40px;">';
                            echo '<button id="btn-load-more-events" class="btn btn-primary" data-page="1" data-max="' . $events_query->max_num_pages . '">Mehr laden ↓</button>';
                            echo '</div>';
                        endif;

                        wp_reset_postdata();
                    else :
                        echo '<p>Derzeit sind keine Veranstaltungen für diese Ansicht verfügbar.</p>';
                    endif;
                    ?>
                </div>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
