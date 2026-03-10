<?php
/**
 * Archive / Blog Listing Template
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<div class="container">
    <?php flohmarkt_breadcrumbs(); ?>
</div>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h1><?php
            if ( is_category() ) {
                single_cat_title( 'Kategorie: ' );
            } elseif ( is_tag() ) {
                single_tag_title( 'Tag: ' );
            } elseif ( is_author() ) {
                echo 'Artikel von ' . get_the_author();
            } elseif ( is_date() ) {
                echo 'Archiv: ' . get_the_date( 'F Y' );
            } else {
                echo 'Blog';
            }
            ?></h1>
            <?php if ( category_description() ) : ?>
                <p><?php echo category_description(); ?></p>
            <?php else : ?>
                <p>Alle Artikel über Flohmärkte, Trödelmärkte und mehr</p>
            <?php endif; ?>
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

                <!-- Pagination -->
                <div class="pagination">
                    <?php
                    echo paginate_links( array(
                        'prev_text' => '←',
                        'next_text' => '→',
                        'type'      => 'list',
                    ));
                    ?>
                </div>
                <?php else : ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <h2>Keine Artikel gefunden</h2>
                    <p style="color: var(--color-text-light);">Es wurden keine Artikel in dieser Kategorie gefunden. Versuchen Sie eine andere Suche.</p>
                    <a href="<?php echo home_url('/'); ?>" class="btn btn-primary" style="margin-top: 20px;">Zurück zur Startseite</a>
                </div>
                <?php endif; ?>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
