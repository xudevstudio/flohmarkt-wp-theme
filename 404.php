<?php
/**
 * 404 Error Page
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<section class="section">
    <div class="container">
        <div class="page-404">
            <div class="error-code">404</div>
            <h2>Seite nicht gefunden</h2>
            <p>Die gesuchte Seite existiert leider nicht oder wurde verschoben. Vielleicht finden Sie hier etwas Interessantes:</p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">🏠 Zur Startseite</a>
                <a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-outline">📅 Veranstaltungen</a>
            </div>

            <div style="max-width: 500px; margin: 40px auto 0;">
                <form class="hero-search" role="search" method="get" action="<?php echo esc_url( home_url('/') ); ?>">
                    <input type="search" name="s" placeholder="Suche starten..." aria-label="Suche">
                    <button type="submit">🔍</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
