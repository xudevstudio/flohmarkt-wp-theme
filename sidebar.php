<?php
/**
 * Sidebar Template
 * @package Flohmarkt_Blog
 */
?>
<aside class="sidebar" role="complementary">
    <!-- Search Widget -->
    <div class="widget">
        <h3 class="widget-title">Suche</h3>
        <form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" name="s" placeholder="Artikel suchen..." value="<?php echo get_search_query(); ?>" aria-label="Suche">
            <button type="submit" aria-label="Suchen">🔍</button>
        </form>
    </div>

    <!-- Ad Zone -->
    <!-- No ad zone -->

    <!-- Categories Widget -->
    <?php $sidebar_cats = get_categories( array( 'hide_empty' => false, 'number' => 10 ) );
    if ( ! empty( $sidebar_cats ) ) : ?>
    <div class="widget">
        <h3 class="widget-title">Kategorien</h3>
        <ul>
            <?php foreach ( $sidebar_cats as $cat ) : ?>
            <li>
                <a href="<?php echo get_category_link( $cat->term_id ); ?>">
                    <?php echo esc_html( $cat->name ); ?>
                    <span class="count"><?php echo $cat->count; ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Recent Posts Widget -->
    <?php $recent = get_posts( array( 'posts_per_page' => 5, 'post_status' => 'publish' ) );
    if ( $recent ) : ?>
    <div class="widget">
        <h3 class="widget-title">Neueste Beiträge</h3>
        <ul>
            <?php foreach ( $recent as $rp ) : ?>
            <li>
                <a href="<?php echo get_permalink( $rp->ID ); ?>">
                    <?php echo esc_html( $rp->post_title ); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Upcoming Events Widget -->
    <?php
    $upcoming = get_posts( array(
        'post_type'      => 'event',
        'posts_per_page' => 3,
        'meta_key'       => '_event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array( array(
            'key'     => '_event_date',
            'value'   => date('Y-m-d'),
            'compare' => '>=',
            'type'    => 'DATE',
        )),
    ));
    if ( $upcoming ) : ?>
    <div class="widget">
        <h3 class="widget-title">Nächste Veranstaltungen</h3>
        <?php foreach ( $upcoming as $ev ) :
            $date = get_post_meta( $ev->ID, '_event_date', true );
            $city = get_post_meta( $ev->ID, '_event_city', true );
        ?>
        <div style="padding: 10px 0; border-bottom: 1px solid var(--color-border);">
            <a href="<?php echo get_permalink( $ev->ID ); ?>" style="color: var(--color-text); font-weight: 500; display: block;">
                <?php echo esc_html( $ev->post_title ); ?>
            </a>
            <span style="font-size: 0.8rem; color: var(--color-text-muted);">
                📅 <?php echo date_i18n( 'j. M Y', strtotime( $date ) ); ?>
                <?php if ( $city ) echo '· 📍 ' . esc_html( $city ); ?>
            </span>
        </div>
        <?php endforeach; ?>
        <a href="<?php echo get_post_type_archive_link('event'); ?>" style="display: block; margin-top: 12px; font-size: 0.9rem; font-weight: 600;">Alle Veranstaltungen →</a>
    </div>
    <?php endif; ?>

    <!-- Ad Zone -->
    <!-- No ad zone -->

</aside>
