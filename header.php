<?php
/**
 * Header Template
 * @package Flohmarkt_Blog
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>">
    <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon.png' ); ?>">
    <?php wp_head(); ?>

    <!-- Google AdSense Auto Ads -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3132081669702592"
         crossorigin="anonymous"></script>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({
              google_ad_client: "ca-pub-3132081669702592",
              enable_page_level_ads: true
         });
    </script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( is_front_page() || is_home() ) : ?>
    <?php
    $next_events = get_posts( array(
        'post_type'      => 'event',
        'posts_per_page' => 5,
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
    if ( ! empty( $next_events ) ) : 
    ?>
    <div class="top-notification-bar">
        <span class="highlight-tag">Events</span>
        
        <div id="ticker-container">
            <?php foreach ( $next_events as $index => $ev ) : 
                $ev_date = get_post_meta( $ev->ID, '_event_date', true );
                $ev_city = get_post_meta( $ev->ID, '_event_city', true );
                $active_class = ($index === 0) ? 'active' : '';
            ?>
            <a href="<?php echo get_permalink( $ev->ID ); ?>" class="ticker-item <?php echo $active_class; ?>">
                <span>
                    <?php echo esc_html( $ev->post_title ); ?> 
                    <?php if ( $ev_city || $ev_date ) : ?>
                        <span class="meta-info">
                            <?php 
                            if ($ev_city) echo esc_html( $ev_city );
                            if ($ev_city && $ev_date) echo ' • ';
                            if ($ev_date) echo date_i18n( 'j. M', strtotime( $ev_date ) ); 
                            ?>
                        </span>
                    <?php endif; ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tickers = document.querySelectorAll('.ticker-item');
        if(tickers.length <= 1) return;
        
        let currentIndex = 0;
        
        setInterval(() => {
            const currentItem = tickers[currentIndex];
            currentIndex = (currentIndex + 1) % tickers.length;
            const nextItem = tickers[currentIndex];
            
            // Start slide out
            currentItem.classList.remove('active');
            currentItem.classList.add('exit');
            
            // Start slide in
            nextItem.classList.add('active');
            
            // Clean up previous item after animation
            setTimeout(() => {
                currentItem.classList.remove('exit');
            }, 600);
        }, 5000);
    });
    </script>
    <?php endif; ?>
<?php endif; ?>

<!-- SITE HEADER -->
<header class="site-header" id="site-header">
    <div class="header-inner">
        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php bloginfo( 'name' ); ?>">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="Flohmarkt" class="custom-logo logo-light">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-dark.png' ); ?>" alt="Flohmarkt" class="custom-logo logo-dark" style="display:none;">
            <span class="logo-text">Flohmarkt</span>
        </a>

        <!-- Navigation -->
        <nav class="nav-main" id="nav-main" aria-label="Hauptnavigation">
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'items_wrap'     => '<ul>%3$s</ul>',
                    'fallback_cb'    => false,
                ));
            } else {
                echo '<ul>';
                echo '<li class="current-menu-item"><a href="' . home_url('/') . '">Startseite</a></li>';
                echo '<li><a href="' . home_url('/blog/') . '">Blog</a></li>';
                echo '<li><a href="' . home_url('/veranstaltungen/') . '">Veranstaltungen</a></li>';
                echo '<li><a href="' . home_url('/ueber-uns/') . '">Über uns</a></li>';
                echo '<li><a href="' . home_url('/kontakt/') . '">Kontakt</a></li>';
                echo '</ul>';
            }
            ?>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">
            <button class="btn-search" id="btn-search" aria-label="Suche">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
            <button class="btn-dark-mode" id="btn-dark-mode" aria-label="Dunkelmodus umschalten">
                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Menü öffnen">
                <div class="hamburger-box">
                    <div class="hamburger-inner"></div>
                </div>
            </button>
        </div>
    </div>
</header>
<div class="menu-overlay" id="menu-overlay"></div>
