<?php
/**
 * Footer Template
 * @package Flohmarkt_Blog
 */
?>

<!-- SITE FOOTER -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- About Column -->
            <div class="footer-col">
                <h4>Über Uns</h4>
                <p>Ihr Portal für Flohmärkte und Trödelmärkte in ganz Deutschland. Entdecken Sie die besten Veranstaltungen, Tipps zum Handeln und spannende Schätze in Ihrer Nähe.</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h4>Schnelllinks</h4>
                <?php
                if ( has_nav_menu( 'footer' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'footer',
                        'container'      => false,
                        'items_wrap'     => '<ul>%3$s</ul>',
                        'fallback_cb'    => false,
                    ));
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . home_url('/') . '">Startseite</a></li>';
                    echo '<li><a href="' . home_url('/blog/') . '">Blog</a></li>';
                    echo '<li><a href="' . home_url('/veranstaltungen/') . '">Veranstaltungen</a></li>';
                    echo '<li><a href="' . home_url('/ueber-uns/') . '">Über uns</a></li>';
                    echo '<li><a href="' . home_url('/kontakt/') . '">Kontakt</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>

            <!-- Legal Links -->
            <div class="footer-col">
                <h4>Rechtliches</h4>
                <ul>
                    <li><a href="<?php echo home_url('/impressum/'); ?>">Impressum</a></li>
                    <li><a href="<?php echo home_url('/datenschutz/'); ?>">Datenschutz</a></li>
                    <li><a href="<?php echo home_url('/haftungsausschluss/'); ?>">Haftungsausschluss</a></li>
                    <li><a href="<?php echo home_url('/nutzungsbedingungen/'); ?>">Nutzungsbedingungen</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>. Alle Rechte vorbehalten.</span>
            <span>Entworfen mit ❤️ für Flohmarkt-Liebhaber</span>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
