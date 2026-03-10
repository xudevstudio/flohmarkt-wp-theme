<?php
/**
 * Search Results Template
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<div class="container">
    <?php flohmarkt_breadcrumbs(); ?>
</div>

<section class="section">
    <div class="container">
        <div class="search-header fade-in">
            <h1>Suchergebnisse für: <span class="search-query-text">&ldquo;<?php echo get_search_query(); ?>&rdquo;</span></h1>
            <p><?php printf( _n( '%d Ergebnis gefunden', '%d Ergebnisse gefunden', $wp_query->found_posts, 'flohmarkt-blog' ), $wp_query->found_posts ); ?></p>
            <div class="accent-line"></div>
        </div>

        <div class="content-wrap">
            <div class="main-content">
                <?php if ( have_posts() ) : ?>
                <div class="posts-grid">
                    <?php while ( have_posts() ) : the_post();
                        echo flohmarkt_render_post_card( $post );
                    endwhile; ?>
                </div>

                <div class="pagination">
                    <?php echo paginate_links( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
                </div>
                <?php else : ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <h2>Keine Ergebnisse gefunden</h2>
                    <p style="color: var(--color-text-light); margin-bottom: 24px;">Versuchen Sie andere Suchbegriffe oder durchsuchen Sie unsere Kategorien.</p>
                    <form class="hero-search" role="search" method="get" action="<?php echo esc_url( home_url('/') ); ?>" style="max-width: 500px; margin: 0 auto;">
                        <input type="search" name="s" placeholder="Erneut suchen..." aria-label="Suche">
                        <button type="submit">🔍</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php get_sidebar(); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
