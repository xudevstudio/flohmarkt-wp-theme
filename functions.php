<?php
/**
 * Flohmarkt Blog Theme Functions
 *
 * @package Flohmarkt_Blog
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Theme Auto-Import System
require_once get_stylesheet_directory() . '/inc/theme-importer.php';

/* ───────────── IMAGE SIDELOAD REST API (for N8N) ───────────── */
add_action('rest_api_init', function () {
    register_rest_route('flohmarkt/v1', '/sideload-image', [
        'methods'  => 'POST',
        'callback' => 'flohmarkt_sideload_image',
        'permission_callback' => function ($request) {
            return current_user_can('upload_files');
        },
    ]);
});

function flohmarkt_sideload_image($request) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $image_url = $request->get_param('image_url');
    $filename  = $request->get_param('filename') ?: 'flohmarkt-' . time() . '.webp';
    $alt_text  = $request->get_param('alt_text') ?: '';

    if (empty($image_url)) {
        return new WP_Error('no_url', 'image_url is required', ['status' => 400]);
    }

    // Download the image
    $tmp = download_url($image_url, 30);
    if (is_wp_error($tmp)) {
        return new WP_Error('download_failed', $tmp->get_error_message(), ['status' => 500]);
    }

    $file_array = [
        'name'     => sanitize_file_name($filename),
        'tmp_name' => $tmp,
    ];

    $media_id = media_handle_sideload($file_array, 0, $alt_text);
    @unlink($tmp);

    if (is_wp_error($media_id)) {
        return new WP_Error('upload_failed', $media_id->get_error_message(), ['status' => 500]);
    }

    if ($alt_text) update_post_meta($media_id, '_wp_attachment_image_alt', $alt_text);

    return rest_ensure_response([
        'id'         => $media_id,
        'source_url' => wp_get_attachment_url($media_id),
    ]);
}

/* ───────────────────── THEME SETUP ───────────────────── */
function flohmarkt_setup() {
    // German language
    load_theme_textdomain( 'flohmarkt-blog', get_template_directory() . '/languages' );

    // Theme support
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 250,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    add_theme_support( 'automatic-feed-links' );
    
    // Gutenberg Content Formatting Support
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'responsive-embeds' );

    // Thumbnail sizes
    add_image_size( 'flohmarkt-featured', 1200, 630, true );
    add_image_size( 'flohmarkt-card', 600, 400, true );
    add_image_size( 'flohmarkt-thumb', 300, 200, true );

    // Navigation menus
    register_nav_menus( array(
        'primary'   => __( 'Hauptmenü', 'flohmarkt-blog' ),
        'footer'    => __( 'Footer Menü', 'flohmarkt-blog' ),
    ));
}
add_action( 'after_setup_theme', 'flohmarkt_setup' );

/* ───────────────────── ENQUEUE STYLES & SCRIPTS ───────────────────── */
function flohmarkt_scripts() {
    // Google Fonts
    wp_enqueue_style( 'flohmarkt-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap',
        array(), null
    );

    // Theme stylesheet
    wp_enqueue_style( 'flohmarkt-style', get_stylesheet_uri(), array(), '1.0.0' );

    // Theme JS
    wp_enqueue_script( 'flohmarkt-main', get_template_directory_uri() . '/js/main.js', array(), '1.0.0', true );

    // Localize script for AJAX
    wp_localize_script( 'flohmarkt-main', 'flohmarktAjax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'flohmarkt_nonce' ),
        'siteurl' => get_site_url(),
    ));
}
add_action( 'wp_enqueue_scripts', 'flohmarkt_scripts' );

/* ───────────────────── WIDGET AREAS ───────────────────── */
function flohmarkt_widgets() {
    register_sidebar( array(
        'name'          => __( 'Blog Sidebar', 'flohmarkt-blog' ),
        'id'            => 'blog-sidebar',
        'description'   => __( 'Widgets in der Blog-Seitenleiste', 'flohmarkt-blog' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar( array(
        'name'          => __( 'Footer Spalte 1', 'flohmarkt-blog' ),
        'id'            => 'footer-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));

    register_sidebar( array(
        'name'          => __( 'Footer Spalte 2', 'flohmarkt-blog' ),
        'id'            => 'footer-2',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}
add_action( 'widgets_init', 'flohmarkt_widgets' );

/* ───────────────────── CUSTOM POST TYPE: EVENTS ───────────────────── */
function flohmarkt_register_events() {
    $labels = array(
        'name'               => 'Veranstaltungen',
        'singular_name'      => 'Veranstaltung',
        'menu_name'          => 'Veranstaltungen',
        'add_new'            => 'Neue Veranstaltung',
        'add_new_item'       => 'Neue Veranstaltung hinzufügen',
        'edit_item'          => 'Veranstaltung bearbeiten',
        'view_item'          => 'Veranstaltung ansehen',
        'all_items'          => 'Alle Veranstaltungen',
        'search_items'       => 'Veranstaltungen suchen',
        'not_found'          => 'Keine Veranstaltungen gefunden',
    );

    register_post_type( 'event', array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'veranstaltungen' ),
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'show_in_rest'       => true,  // for REST API / N8N
        'rest_base'          => 'events',
        'taxonomies'         => array( 'event_location' ),
    ));

    // Event Location Taxonomy
    register_taxonomy( 'event_location', 'event', array(
        'labels'       => array(
            'name'          => 'Standorte',
            'singular_name' => 'Standort',
            'add_new_item'  => 'Neuen Standort hinzufügen',
        ),
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => array( 'slug' => 'standort' ),
        'show_in_rest' => true,
    ));
}
add_action( 'init', 'flohmarkt_register_events' );

/* ───────────────────── EVENT META BOXES ───────────────────── */
function flohmarkt_event_meta_boxes() {
    add_meta_box(
        'event_details',
        'Veranstaltungsdetails',
        'flohmarkt_event_meta_callback',
        'event',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'flohmarkt_event_meta_boxes' );

function flohmarkt_event_meta_callback( $post ) {
    wp_nonce_field( 'flohmarkt_event_meta', 'flohmarkt_event_nonce' );

    $date  = get_post_meta( $post->ID, '_event_date', true );
    $time  = get_post_meta( $post->ID, '_event_time', true );
    $end_time = get_post_meta( $post->ID, '_event_end_time', true );
    $city  = get_post_meta( $post->ID, '_event_city', true );
    $address = get_post_meta( $post->ID, '_event_address', true );
    $organizer = get_post_meta( $post->ID, '_event_organizer', true );
    $website = get_post_meta( $post->ID, '_event_website', true );

    echo '<table class="form-table">';
    echo '<tr><th><label>Datum</label></th><td><input type="date" name="_event_date" value="' . esc_attr($date) . '" class="regular-text"></td></tr>';
    echo '<tr><th><label>Startzeit</label></th><td><input type="time" name="_event_time" value="' . esc_attr($time) . '" class="regular-text"></td></tr>';
    echo '<tr><th><label>Endzeit</label></th><td><input type="time" name="_event_end_time" value="' . esc_attr($end_time) . '" class="regular-text"></td></tr>';
    echo '<tr><th><label>Stadt</label></th><td><input type="text" name="_event_city" value="' . esc_attr($city) . '" class="regular-text" placeholder="z.B. Berlin, München"></td></tr>';
    echo '<tr><th><label>Adresse</label></th><td><input type="text" name="_event_address" value="' . esc_attr($address) . '" class="regular-text" placeholder="Straße und Hausnummer"></td></tr>';
    echo '<tr><th><label>Veranstalter</label></th><td><input type="text" name="_event_organizer" value="' . esc_attr($organizer) . '" class="regular-text"></td></tr>';
    echo '<tr><th><label>Website</label></th><td><input type="url" name="_event_website" value="' . esc_attr($website) . '" class="regular-text" placeholder="https://"></td></tr>';
    echo '</table>';
}

function flohmarkt_save_event_meta( $post_id ) {
    if ( ! isset( $_POST['flohmarkt_event_nonce'] ) || ! wp_verify_nonce( $_POST['flohmarkt_event_nonce'], 'flohmarkt_event_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $fields = array( '_event_date', '_event_time', '_event_end_time', '_event_city', '_event_address', '_event_organizer', '_event_website' );
    foreach ( $fields as $field ) {
        if ( isset( $_POST[$field] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
    }
}
add_action( 'save_post_event', 'flohmarkt_save_event_meta' );

/* ── Register event meta for REST API (N8N access) ── */
function flohmarkt_register_event_meta_rest() {
    $meta_fields = array( '_event_date', '_event_time', '_event_end_time', '_event_city', '_event_address', '_event_organizer', '_event_website' );
    foreach ( $meta_fields as $field ) {
        register_post_meta( 'event', $field, array(
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
            'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
        ));
    }
}
add_action( 'init', 'flohmarkt_register_event_meta_rest' );

/* ───────────────────── SHORTCODES ───────────────────── */

// [flohmarkt_calendar] - Advanced Monthly Grid Calendar
function flohmarkt_calendar_shortcode( $atts ) {
    // Determine month and year to show (defaults to current)
    $month = isset( $_GET['cal_month'] ) ? intval( $_GET['cal_month'] ) : date('n');
    $year  = isset( $_GET['cal_year'] ) ? intval( $_GET['cal_year'] ) : date('Y');

    // Basic validation
    if ( $month < 1 || $month > 12 ) $month = date('n');
    if ( $year < 2000 || $year > 2100 ) $year = date('Y');

    // Create a date object for the 1st of the selected month
    $first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day_of_month);
    
    // Day of the week for the 1st (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
    // Convert so Monday = 1, Sunday = 7 for German week standard
    $day_of_week = date('w', $first_day_of_month);
    $day_of_week = ( $day_of_week == 0 ) ? 7 : $day_of_week;

    // Previous and Next month calculations for navigation
    $prev_month = ( $month == 1 ) ? 12 : $month - 1;
    $prev_year  = ( $month == 1 ) ? $year - 1 : $year;
    $next_month = ( $month == 12 ) ? 1 : $month + 1;
    $next_year  = ( $month == 12 ) ? $year + 1 : $year;

    // Fetch Events for this month
    $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end_date   = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . $days_in_month;

    $events = get_posts( array(
        'post_type'      => 'event',
        'posts_per_page' => -1,
        'meta_key'       => '_event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_event_date',
                'value'   => array($start_date, $end_date),
                'compare' => 'BETWEEN',
                'type'    => 'DATE',
            ),
        ),
    ));

    // Organize events by day for easy lookup
    $events_by_day = array();
    foreach ( $events as $event ) {
        $event_date = get_post_meta( $event->ID, '_event_date', true );
        $day = intval( date('j', strtotime($event_date)) );
        if ( ! isset( $events_by_day[$day] ) ) {
            $events_by_day[$day] = array();
        }
        $events_by_day[$day][] = $event;
    }

    $current_month_name = date_i18n('F Y', $first_day_of_month);
    
    ob_start();
    ?>
    <div class="flohmarkt-calendar-wrapper fade-in">
        
        <!-- Calendar Header / Navigation -->
        <div class="calendar-header">
            <a href="?cal_month=<?php echo $prev_month; ?>&cal_year=<?php echo $prev_year; ?>" class="cal-nav-btn" aria-label="Vorheriger Monat">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            
            <h2 class="calendar-title"><?php echo esc_html( $current_month_name ); ?></h2>
            
            <a href="?cal_month=<?php echo $next_month; ?>&cal_year=<?php echo $next_year; ?>" class="cal-nav-btn" aria-label="Nächster Monat">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <!-- Weekdays -->
            <div class="cal-weekdays">
                <div>Mo</div><div>Di</div><div>Mi</div><div>Do</div><div>Fr</div><div>Sa</div><div>So</div>
            </div>
            
            <!-- Days -->
            <div class="cal-days">
                <?php
                // Pad start of month
                $empty_cells_start = $day_of_week - 1;
                for ( $i = 0; $i < $empty_cells_start; $i++ ) {
                    echo '<div class="cal-day empty"></div>';
                }

                // Actual days
                $today_day = date('j');
                $today_month = date('n');
                $today_year = date('Y');

                for ( $day = 1; $day <= $days_in_month; $day++ ) {
                    $is_today = ($day == $today_day && $month == $today_month && $year == $today_year) ? ' is-today' : '';
                    $has_events = isset( $events_by_day[$day] ) ? ' has-events' : '';
                    
                    echo '<div class="cal-day' . $is_today . $has_events . '">';
                    echo '<span class="day-number">' . $day . '</span>';
                    
                    if ( isset( $events_by_day[$day] ) ) {
                        echo '<div class="day-events">';
                        foreach ( $events_by_day[$day] as $event ) {
                            $time = get_post_meta( $event->ID, '_event_time', true );
                            echo '<a href="' . get_permalink( $event->ID ) . '" class="cal-event-pill" title="' . esc_attr($event->post_title) . '">';
                            if ($time) echo '<strong>' . esc_html($time) . '</strong> ';
                            echo esc_html($event->post_title);
                            echo '</a>';
                        }
                        echo '</div>';
                        
                        // Mobile dot indicator
                        echo '<div class="day-dot-indicator"></div>';
                    }
                    echo '</div>';
                }

                // Pad end of month
                $total_cells_used = $empty_cells_start + $days_in_month;
                $empty_cells_end =  (7 - ($total_cells_used % 7)) % 7;
                for ( $i = 0; $i < $empty_cells_end; $i++ ) {
                    echo '<div class="cal-day empty"></div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Mobile Events Drawer -->
        <div class="mobile-events-container" id="mobile-events-container" style="display:none;">
            <div class="mobile-events-header">
                <h3 id="mobile-events-date-title">Termine</h3>
                <button id="close-mobile-events">&times;</button>
            </div>
            <div id="mobile-events-list"></div>
        </div>
    </div>

    <!-- Calendar Javascript for Mobile Interactions -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if(window.innerWidth > 768) return; // Desktop doesn't need this logic mostly, pills are visible
        
        const days = document.querySelectorAll('.cal-day.has-events');
        const container = document.getElementById('mobile-events-container');
        const list = document.getElementById('mobile-events-list');
        const title = document.getElementById('mobile-events-date-title');
        const closeBtn = document.getElementById('close-mobile-events');
        
        days.forEach(day => {
            day.addEventListener('click', function() {
                // Ignore clicks if we are on desktop sizing
                if(window.innerWidth > 768) return;
                
                // Clear active states
                days.forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                
                const num = this.querySelector('.day-number').textContent;
                const events = this.querySelectorAll('.cal-event-pill');
                
                title.textContent = "Termine am " + num + ".";
                list.innerHTML = "";
                
                if(events.length > 0) {
                    events.forEach(ev => {
                        const clone = ev.cloneNode(true);
                        // Ensure it's visible inside the list even if the CSS class tries to hide it
                        clone.style.display = 'block'; 
                        list.appendChild(clone);
                    });
                    container.style.display = 'block';
                    // Scroll to it smoothly
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                container.style.display = 'none';
                days.forEach(d => d.classList.remove('active'));
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode( 'flohmarkt_calendar', 'flohmarkt_calendar_shortcode' );

// [flohmarkt_latest_posts] - Latest posts grid
function flohmarkt_latest_posts_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'count' => 6 ), $atts );

    $posts = get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => intval( $atts['count'] ),
        'post_status'    => 'publish',
    ));

    if ( empty( $posts ) ) return '<p>Keine Beiträge gefunden.</p>';

    $output = '<div class="posts-grid">';
    foreach ( $posts as $post ) {
        $output .= flohmarkt_render_post_card( $post );
    }
    $output .= '</div>';

    return $output;
}
add_shortcode( 'flohmarkt_latest_posts', 'flohmarkt_latest_posts_shortcode' );

/* ───────────────────── HELPER FUNCTIONS ───────────────────── */

// Render a post card
function flohmarkt_render_post_card( $post ) {
    $thumb = get_the_post_thumbnail_url( $post->ID, 'flohmarkt-card' );
    $cats  = get_the_category( $post->ID );
    $cat_name = ! empty( $cats ) ? $cats[0]->name : '';

    $output = '<article class="post-card fade-in">';
    $output .= '<div class="post-card-image">';
    if ( $thumb ) {
        $output .= '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $post->post_title ) . '" loading="lazy">';
    }
    if ( $cat_name ) {
        $output .= '<span class="post-card-category">' . esc_html( $cat_name ) . '</span>';
    }
    $output .= '</div>';
    $output .= '<div class="post-card-body">';
    $output .= '<div class="post-card-meta">';
    $output .= '<span>📅 ' . get_the_date( 'j. M Y', $post->ID ) . '</span>';
    $output .= '<span>⏱ ' . flohmarkt_reading_time( $post ) . ' Min. Lesezeit</span>';
    $output .= '</div>';
    $output .= '<h3 class="post-card-title"><a href="' . get_permalink( $post->ID ) . '">' . esc_html( $post->post_title ) . '</a></h3>';
    $output .= '<p class="post-card-excerpt">' . wp_trim_words( $post->post_excerpt ?: $post->post_content, 25, '...' ) . '</p>';
    $output .= '<div class="post-card-footer">';
    $output .= '<a href="' . get_permalink( $post->ID ) . '" class="read-more">Weiterlesen →</a>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</article>';

    return $output;
}

// Calculate reading time
function flohmarkt_reading_time( $post ) {
    $content = is_object($post) ? $post->post_content : get_post_field( 'post_content', $post );
    $word_count = str_word_count( strip_tags( $content ) );
    return max( 1, ceil( $word_count / 200 ) );
}

// Breadcrumbs
function flohmarkt_breadcrumbs() {
    if ( is_front_page() ) return;

    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<a href="' . home_url() . '">Startseite</a>';
    echo '<span class="separator">›</span>';

    if ( is_single() ) {
        $cats = get_the_category();
        if ( $cats ) {
            echo '<a href="' . get_category_link( $cats[0]->term_id ) . '">' . esc_html( $cats[0]->name ) . '</a>';
            echo '<span class="separator">›</span>';
        }
        the_title();
    } elseif ( is_category() ) {
        single_cat_title();
    } elseif ( is_tag() ) {
        single_tag_title( 'Tag: ' );
    } elseif ( is_search() ) {
        echo 'Suche: ' . get_search_query();
    } elseif ( is_archive() ) {
        if ( is_post_type_archive( 'event' ) ) {
            echo 'Veranstaltungen';
        } else {
            the_archive_title();
        }
    } elseif ( is_page() ) {
        the_title();
    } elseif ( is_404() ) {
        echo 'Seite nicht gefunden';
    }

    echo '</nav>';
}

// Get upcoming events count
function flohmarkt_upcoming_events_count() {
    $count = get_posts( array(
        'post_type'   => 'event',
        'post_status' => 'publish',
        'meta_query'  => array( array(
            'key'     => '_event_date',
            'value'   => date('Y-m-d'),
            'compare' => '>=',
            'type'    => 'DATE',
        )),
        'fields'       => 'ids',
        'numberposts'  => -1,
    ));
    return count( $count );
}

/* ───────────────────── SEO HELPERS ───────────────────── */
function flohmarkt_seo_meta() {
    if ( is_singular() ) {
        global $post;
        $desc = wp_trim_words( $post->post_excerpt ?: $post->post_content, 30, '' );
        $thumb = get_the_post_thumbnail_url( $post->ID, 'flohmarkt-featured' );

        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '">' . "\n";
        if ( $thumb ) {
            echo '<meta property="og:image" content="' . esc_url( $thumb ) . '">' . "\n";
        }
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

        // Article Schema
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode( array(
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'description'   => $desc,
            'image'         => $thumb ?: '',
            'datePublished' => get_the_date( 'c' ),
            'dateModified'  => get_the_modified_date( 'c' ),
            'author'        => array( '@type' => 'Person', 'name' => get_the_author() ),
            'publisher'     => array(
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
            ),
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        echo '</script>' . "\n";
    } elseif ( is_front_page() ) {
        echo '<meta name="description" content="' . esc_attr( get_bloginfo( 'description' ) ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( get_bloginfo( 'description' ) ) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";

        // Website Schema
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode( array(
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url(),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        echo '</script>' . "\n";
    }
}
add_action( 'wp_head', 'flohmarkt_seo_meta' );


/* ───────────────────── EXCERPT LENGTH ───────────────────── */
function flohmarkt_excerpt_length( $length ) {
    return 25;
}
add_filter( 'excerpt_length', 'flohmarkt_excerpt_length' );

function flohmarkt_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'flohmarkt_excerpt_more' );

/* ───────────────────── FLUSH REWRITE ON ACTIVATION ───────────────────── */
function flohmarkt_rewrite_flush() {
    flohmarkt_register_events();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'flohmarkt_rewrite_flush' );

/* ───────────────────── FIX FOR /BLOG/ 404 WITHOUT DB ACCESS ───────────────────── */
function flohmarkt_custom_blog_redirect() {
    // If the URL matches /blog/ or /blog/page/X and it's throwing a 404
    if ( is_404() && strpos( $_SERVER['REQUEST_URI'], '/blog' ) === 0 ) {
        // Force status to 200 OK
        status_header( 200 );
        
        // Extract pagination if present
        $paged = 1;
        if ( preg_match( '#/blog/page/([0-9]+)/?#', $_SERVER['REQUEST_URI'], $matches ) ) {
            $paged = intval( $matches[1] );
        }
        set_query_var( 'paged', $paged );
        
        // Load our custom blog template
        $template = locate_template( 'page-blog.php' );
        if ( $template ) {
            include( $template );
            exit;
        }
    }
}
add_action( 'template_redirect', 'flohmarkt_custom_blog_redirect', 1 );

/* ───────────────────── AJAX LIVE SEARCH ───────────────────── */
function flohmarkt_live_search() {
    check_ajax_referer( 'flohmarkt_nonce', 'nonce' );

    $s = isset($_POST['query']) ? sanitize_text_field( $_POST['query'] ) : '';
    if ( empty( $s ) || strlen($s) < 2 ) {
        wp_send_json_success( array() );
    }

    $args = array(
        'post_type'      => array( 'post', 'event' ),
        'posts_per_page' => 8,
        's'              => $s,
        'post_status'    => 'publish',
    );

    $query = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $type = get_post_type();
            $icon = ( $type === 'event' ) ? '📅' : '📝';
            $meta = '';
            
            if ( $type === 'event' ) {
                $date = get_post_meta( get_the_ID(), '_event_date', true );
                $city = get_post_meta( get_the_ID(), '_event_city', true );
                if ( $city ) $meta = $city;
                if ( $date ) $meta .= ( $meta ? ' • ' : '' ) . date_i18n( 'j. M', strtotime( $date ) );
            } else {
                $meta = get_the_date( 'j. M Y' );
            }

            $results[] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'type'  => $type,
                'icon'  => $icon,
                'meta'  => $meta,
                'thumb' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_live_search', 'flohmarkt_live_search' );
add_action( 'wp_ajax_nopriv_live_search', 'flohmarkt_live_search' );

/* ───────────────────── UPDATE LEGAL PAGES SCRIPT ───────────────────── */
function flohmarkt_update_legal_pages_once() {
    if ( ! get_option('flohmarkt_legal_pages_updated_v1') ) {
        
        $pages_data = array(
            'ueber-uns' => array(
                'title' => 'Über Uns',
                'content' => '
                    <div class="legal-page-content fade-in">
                        <h2>Willkommen bei Flohmarkt-Trödelmarkt!</h2>
                        <p>Wir sind Ihr führendes Portal für Flohmärkte, Trödelmärkte, Antikmärkte und Basare in ganz Deutschland. Unsere Leidenschaft ist es, Menschen zusammenzubringen, nachhaltigen Konsum zu fördern und die Suche nach versteckten Schätzen so einfach und angenehm wie möglich zu gestalten.</p>
                        
                        <h3>Unsere Mission</h3>
                        <p>Unsere Mission ist es, die größte und aktuellste Übersicht aller Flohmarkt-Veranstaltungen im deutschsprachigen Raum zu bieten. Wir möchten Käufern und Verkäufern eine Plattform bieten, auf der sie schnell und zuverlässig alle Informationen zu kommenden Märkten finden können – von Öffnungszeiten über Standgebühren bis hin zu Anfahrtsbeschreibungen.</p>
        
                        <h3>Warum Flohmarkt-Trödelmarkt?</h3>
                        <ul>
                            <li><strong>Aktualität:</strong> Unser Team und unsere Community arbeiten ständig daran, die Veranstaltungsdatenbank auf dem neuesten Stand zu halten.</li>
                            <li><strong>Vielfalt:</strong> Ob Babymarkt, Antikmarkt, Nachtflohmarkt oder klassischer Trödelmarkt – bei uns finden Sie alles.</li>
                            <li><strong>Nachhaltigkeit:</strong> Durch den Kauf und Verkauf von gebrauchten Gegenständen leisten wir gemeinsam einen wichtigen Beitrag zum Umweltschutz. Gebraucht kaufen bedeutet Ressourcen schonen.</li>
                        </ul>
        
                        <h3>Unser Team</h3>
                        <p>Hinter Flohmarkt-Trödelmarkt steht ein kleines, aber engagiertes Team von Flohmarkt-Liebhabern, Technik-Enthusiasten und Redakteuren. Wir verbringen unsere Wochenenden selbst gerne auf den Märkten der Region und wissen genau, worauf es bei einer guten Flohmarkt-Planung ankommt.</p>
        
                        <div class="contact-cta" style="margin-top: 40px; text-align: center; padding: 30px; background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                            <h3 style="margin-top:0;">Haben Sie Fragen oder Anregungen?</h3>
                            <p>Wir freuen uns immer über Feedback aus unserer Community!</p>
                            <a href="' . home_url('/kontakt') . '" class="btn btn-primary">Kontaktieren Sie uns</a>
                        </div>
                    </div>
                '
            ),
            'kontakt' => array(
                'title' => 'Kontakt',
                'content' => '
                    <div class="legal-page-content fade-in">
                        <div class="contact-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
                            <div class="contact-info">
                                <h2>Nehmen Sie Kontakt mit uns auf</h2>
                                <p>Haben Sie Fragen zu einer Veranstaltung, möchten Sie Feedback geben oder haben Sie ein technisches Problem? Zögern Sie nicht, uns zu kontaktieren. Unser Team hilft Ihnen gerne weiter!</p>
                                
                                <div class="info-block" style="margin-top: 30px;">
                                    <h4 style="margin-bottom: 5px;">📧 E-Mail</h4>
                                    <p><a href="mailto:info@flohmarkt-troedelmarkt.de" style="color: var(--color-primary); text-decoration: none;">info@flohmarkt-troedelmarkt.de</a><br>
                                    Wir bemühen uns, alle Anfragen innerhalb von 24-48 Stunden zu beantworten.</p>
                                </div>
        
                                <div class="info-block" style="margin-top: 20px;">
                                    <h4 style="margin-bottom: 5px;">📍 Postanschrift</h4>
                                    <p>Flohmarkt-Trödelmarkt Redaktion<br>
                                    Musterstraße 123<br>
                                    10115 Berlin<br>
                                    Deutschland</p>
                                </div>
                            </div>
                            
                            <div class="contact-form-wrapper" style="background: var(--color-surface); padding: 30px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
                                <h3 style="margin-top: 0; margin-bottom: 20px;">Ihre Nachricht an uns</h3>
                                <form id="contact-form" action="#" method="POST">
                                    <div class="form-group" style="margin-bottom: 15px;">
                                        <label for="name" style="display:block; margin-bottom: 5px; font-weight: 500;">Name *</label>
                                        <input type="text" id="name" name="name" required style="width: 100%; padding: 12px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); outline: none; transition: border-color 0.3s; background: var(--color-bg);">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 15px;">
                                        <label for="email" style="display:block; margin-bottom: 5px; font-weight: 500;">E-Mail *</label>
                                        <input type="email" id="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); outline: none; transition: border-color 0.3s; background: var(--color-bg);">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 20px;">
                                        <label for="message" style="display:block; margin-bottom: 5px; font-weight: 500;">Nachricht *</label>
                                        <textarea id="message" name="message" rows="5" required style="width: 100%; padding: 12px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); outline: none; transition: border-color 0.3s; background: var(--color-bg); resize: vertical;"></textarea>
                                    </div>
                                    <button type="button" onclick="alert(\'Vielen Dank für Ihre Nachricht. Wir werden uns in Kürze bei Ihnen melden.\')" class="btn btn-primary" style="width: 100%; padding: 14px;">Nachricht senden</button>
                                </form>
                            </div>
                        </div>
                    </div>
                '
            ),
            'datenschutz' => array(
                'title' => 'Datenschutzerklärung',
                'content' => '
                    <div class="legal-page-content fade-in">
                        <h2>1. Datenschutz auf einen Blick</h2>
                        <h3>Allgemeine Hinweise</h3>
                        <p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie unsere Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können. Ausführliche Informationen zum Thema Datenschutz entnehmen Sie unserer unter diesem Text aufgeführten Datenschutzerklärung.</p>
        
                        <h3>Datenerfassung auf unserer Website</h3>
                        <p><strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong><br>
                        Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten können Sie dem Impressum dieser Website entnehmen.</p>
                        <p><strong>Wie erfassen wir Ihre Daten?</strong><br>
                        Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen. Hierbei kann es sich z.B. um Daten handeln, die Sie in ein Kontaktformular eingeben. Andere Daten werden automatisch beim Besuch der Website durch unsere IT-Systeme erfasst. Das sind vor allem technische Daten (z.B. Internetbrowser, Betriebssystem oder Uhrzeit des Seitenaufrufs).</p>
        
                        <h2>2. Allgemeine Hinweise und Pflichtinformationen</h2>
                        <h3>Datenschutz</h3>
                        <p>Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend der gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>
                        <p>Wenn Sie diese Website benutzen, werden verschiedene personenbezogene Daten erhoben. Personenbezogene Daten sind Daten, mit denen Sie persönlich identifiziert werden können. Die vorliegende Datenschutzerklärung erläutert, welche Daten wir erheben und wofür wir sie nutzen. Sie erläutert auch, wie und zu welchem Zweck das geschieht.</p>
                        <p>Wir weisen darauf hin, dass die Datenübertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. Ein lückenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht möglich.</p>
        
                        <h3>Hinweis zur verantwortlichen Stelle</h3>
                        <p>Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:<br>
                        Flohmarkt-Trödelmarkt Redaktion<br>
                        Musterstraße 123<br>
                        10115 Berlin<br>
                        E-Mail: info@flohmarkt-troedelmarkt.de</p>
        
                        <h2>3. Datenerfassung auf unserer Website</h2>
                        <h3>Cookies</h3>
                        <p>Die Internetseiten verwenden teilweise so genannte Cookies. Cookies richten auf Ihrem Rechner keinen Schaden an und enthalten keine Viren. Cookies dienen dazu, unser Angebot nutzerfreundlicher, effektiver und sicherer zu machen. Cookies sind kleine Textdateien, die auf Ihrem Rechner abgelegt werden und die Ihr Browser speichert.</p>
        
                        <h3>Server-Log-Dateien</h3>
                        <p>Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, die Ihr Browser automatisch an uns übermittelt. Dies sind:</p>
                        <ul>
                            <li>Browsertyp und Browserversion</li>
                            <li>verwendetes Betriebssystem</li>
                            <li>Referrer URL</li>
                            <li>Hostname des zugreifenden Rechners</li>
                            <li>Uhrzeit der Serveranfrage</li>
                            <li>IP-Adresse</li>
                        </ul>
                        <p>Eine Zusammenführung dieser Daten mit anderen Datenquellen wird nicht vorgenommen.</p>
                    </div>
                '
            ),
            'agb' => array(
                'title' => 'Allgemeine Geschäftsbedingungen',
                'content' => '
                    <div class="legal-page-content fade-in">
                        <h2>1. Geltungsbereich</h2>
                        <p>Diese Allgemeinen Geschäftsbedingungen (AGB) gelten für alle Nutzer der Plattform Flohmarkt-Trödelmarkt.de (im Folgenden "Plattform" genannt). Mit der Nutzung unserer Webseite erklären Sie sich mit diesen Bedingungen einverstanden. Bitte lesen Sie diese sorgfältig durch.</p>
        
                        <h2>2. Leistungen der Plattform</h2>
                        <p>Die Plattform bietet Informationen zu Flohmärkten, Trödelmärkten und ähnlichen Veranstaltungen in Deutschland. Die bereitgestellten Informationen basieren auf Angaben von Marktveranstaltern, Nutzern der Plattform und öffentlich zugänglichen Quellen.</p>
                        <p>Wir bemühen uns stets um höchste Aktualität und Richtigkeit der bereitgestellten Daten. Jedoch übernehmen wir keine Garantie für die Richtigkeit, Vollständigkeit und Aktualität der bereitgestellten Informationen, wie z.B. zu Terminen, Öffnungszeiten, Veranstaltungsorten oder Standgebühren.</p>
        
                        <h2>3. Haftungsausschluss</h2>
                        <p>Flohmarkt-Trödelmarkt.de tritt ausschließlich als Vermittler von Informationen auf und ist nicht Veranstalter der gelisteten Märkte. Für die Durchführung der Märkte, eventuelle Absagen oder Änderungen sind ausschließlich die jeweiligen Marktveranstalter verantwortlich.</p>
                        <p>Wir haften nicht für Schäden materieller oder immaterieller Art, die durch die Nutzung oder Nichtnutzung der dargebotenen Informationen bzw. durch die Nutzung fehlerhafter und unvollständiger Informationen verursacht wurden, sofern seitens der Plattform kein nachweislich vorsätzliches oder grob fahrlässiges Verschulden vorliegt.</p>
        
                        <h2>4. Urheberrecht</h2>
                        <p>Die auf dieser Webseite veröffentlichten Inhalte, Texte, Bilder und Layouts unterliegen dem deutschen Urheberrecht. Jede Art der Verwertung außerhalb der Grenzen des Urheberrechts bedarf der vorherigen schriftlichen Zustimmung des jeweiligen Rechteinhabers. Das Setzen von Links auf unsere Plattform ist erlaubt und erwünscht.</p>
        
                        <h2>5. Änderungen der AGB</h2>
                        <p>Wir behalten uns das Recht vor, diese Allgemeinen Geschäftsbedingungen jederzeit und ohne Nennung von Gründen zu ändern. Die geänderten Bedingungen werden den Nutzern auf der Webseite bekannt gegeben. Durch die fortgesetzte Nutzung der Webseite nach einer Änderung erklären sich die Nutzer mit den geänderten AGB einverstanden.</p>
        
                        <h2>6. Schlussbestimmungen</h2>
                        <p>Sollten einzelne Bestimmungen dieser AGB unwirksam oder undurchführbar sein oder nach Vertragsschluss unwirksam oder undurchführbar werden, bleibt davon die Wirksamkeit der übrigen Bestimmungen unberührt. An die Stelle der unwirksamen oder undurchführbaren Bestimmung soll diejenige wirksame und durchführbare Regelung treten, deren Wirkungen der wirtschaftlichen Zielsetzung am nächsten kommen, die die Vertragsparteien mit der unwirksamen bzw. undurchführbaren Bestimmung verfolgt haben.</p>
                    </div>
                '
            ),
            'impressum' => array(
                'title' => 'Impressum',
                'content' => '
                    <div class="legal-page-content fade-in">
                        <h2>Angaben gemäß § 5 TMG</h2>
                        <p>Flohmarkt-Trödelmarkt.de<br>
                        Musterstraße 123<br>
                        10115 Berlin</p>
        
                        <h2>Vertreten durch:</h2>
                        <p>Max Mustermann</p>
        
                        <h2>Kontakt:</h2>
                        <p>Telefon: +49 (0) 123 44 55 66<br>
                        E-Mail: info@flohmarkt-troedelmarkt.de</p>
        
                        <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV:</h2>
                        <p>Max Mustermann<br>
                        Musterstraße 123<br>
                        10115 Berlin</p>
        
                        <h2>Streitschlichtung</h2>
                        <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener noreferrer">https://ec.europa.eu/consumers/odr</a>.<br>
                        Unsere E-Mail-Adresse finden Sie oben im Impressum.</p>
                        <p>Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
        
                        <h2>Haftung für Inhalte</h2>
                        <p>Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>
                    </div>
                '
            )
        );

        foreach ($pages_data as $slug => $data) {
            $page = get_page_by_path($slug);
            $post_arr = array(
                'post_type'    => 'page',
                'post_title'   => $data['title'],
                'post_content' => $data['content'],
                'post_status'  => 'publish',
                'post_name'    => $slug,
                'post_author'  => 1
            );

            if ($page) {
                $post_arr['ID'] = $page->ID;
                wp_update_post( $post_arr );
            } else {
                wp_insert_post( $post_arr );
            }
        }

        update_option('flohmarkt_legal_pages_updated_v1', true);
    }
}
add_action( 'init', 'flohmarkt_update_legal_pages_once' );

/* ───────────────────── AJAX LOAD MORE EVENTS ───────────────────── */
function flohmarkt_load_more_events() {
    check_ajax_referer( 'flohmarkt_nonce', 'nonce' );

    $paged = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

    $args = array(
        'post_type'      => 'event',
        'posts_per_page' => 10,
        'paged'          => $paged,
        'orderby'        => 'meta_value',
        'meta_key'       => '_event_date',
        'order'          => 'DESC'
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        ob_start();
        while ( $query->have_posts() ) {
            $query->the_post();
            $date = get_post_meta( get_the_ID(), '_event_date', true );
            $time = get_post_meta( get_the_ID(), '_event_time', true );
            $city = get_post_meta( get_the_ID(), '_event_city', true );
            ?>
            <a href="<?php the_permalink(); ?>" class="event-list-item" style="display: block; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 20px; text-decoration: none; color: inherit; transition: all 0.3s ease; box-shadow: var(--shadow-sm);" onmouseover="this.style.borderColor='var(--color-primary)'; this.style.transform='translateX(8px)';" onmouseout="this.style.borderColor='var(--color-border)'; this.style.transform='translateX(0)';">
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
        }
        $html = ob_get_clean();
        wp_send_json_success( $html );
    } else {
        wp_send_json_error( 'no_more_posts' );
    }
    wp_die();
}
add_action( 'wp_ajax_load_more_events', 'flohmarkt_load_more_events' );
add_action( 'wp_ajax_nopriv_load_more_events', 'flohmarkt_load_more_events' );
