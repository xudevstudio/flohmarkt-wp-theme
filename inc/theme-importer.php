<?php
/**
 * Flohmarkt Theme Auto-Import
 * Automatically imports ALL bundled content when theme is activated:
 * - Navigation menus (header + footer)
 * - Custom logo + favicon
 * - Categories
 * - Pages (Kontakt, Impressum, Datenschutz, etc.)
 * - Blog posts with images
 * - Events with meta data
 * - Site settings
 * 
 * @package Flohmarkt_Blog
 */

if (!defined('ABSPATH')) exit;

class Flohmarkt_Theme_Importer {
    
    private $import_dir;
    private $media_dir;
    private $theme_dir;
    private $data;
    
    public function __construct() {
        $this->theme_dir  = get_stylesheet_directory();
        $this->import_dir = $this->theme_dir . '/import-data';
        $this->media_dir  = $this->import_dir . '/media';
    }
    
    public function needs_import() {
        return !get_option('flohmarkt_theme_imported_v2', false);
    }
    
    /**
     * Run the COMPLETE import
     */
    public function run_import() {
        if (!$this->needs_import()) {
            return ['status' => 'skipped', 'message' => 'Import already completed.'];
        }
        
        // Load import data
        $json_file = $this->import_dir . '/content.json';
        if (file_exists($json_file)) {
            $this->data = json_decode(file_get_contents($json_file), true);
        }
        if (!$this->data) $this->data = [];
        
        // Require WP admin functions for media uploads
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $results = [];
        
        // 1. Site settings first
        $results['settings'] = $this->import_settings();
        
        // 2. Logo + Favicon
        $results['logo'] = $this->import_logo();
        $results['favicon'] = $this->import_favicon();
        
        // 3. Categories
        $results['categories'] = $this->import_categories();
        
        // 4. Pages
        $results['pages'] = $this->import_pages();
        
        // 5. Posts
        $results['posts'] = $this->import_posts();
        
        // 6. Events
        $results['events'] = $this->import_events();
        
        // 7. Navigation Menus (after pages are created so we can link to them)
        $results['menus'] = $this->import_menus();
        
        // 8. Reading settings & permalinks
        $this->configure_reading_settings();
        
        // 9. Flush rewrite rules
        flush_rewrite_rules();
        
        // Mark as done
        update_option('flohmarkt_theme_imported_v2', true);
        update_option('flohmarkt_theme_import_date', current_time('mysql'));
        
        return ['status' => 'success', 'results' => $results];
    }

    // =============================================
    // SITE SETTINGS
    // =============================================
    private function import_settings() {
        $settings = $this->data['site_settings'] ?? [];
        $count = 0;
        
        // Core settings
        $defaults = [
            'blogname'           => 'Flohmarkt & Trödelmarkt',
            'blogdescription'    => 'Deutschlands Portal für Flohmärkte, Trödelmärkte und Antiquitäten',
            'posts_per_page'     => 10,
            'permalink_structure'=> '/%postname%/',
            'timezone_string'    => 'Europe/Berlin',
            'date_format'        => 'j. F Y',
            'time_format'        => 'H:i',
            'WPLANG'             => 'de_DE',
        ];
        
        foreach ($defaults as $key => $fallback) {
            $value = $settings[$key] ?? $fallback;
            if (!empty($value)) {
                update_option($key, $value);
                $count++;
            }
        }
        
        return $count;
    }

    // =============================================
    // LOGO
    // =============================================
    private function import_logo() {
        // Check if logo already set
        if (get_theme_mod('custom_logo')) return 'already set';
        
        // Try bundled media first, then theme assets
        $logo_sources = [
            $this->media_dir . '/logo.png',
            $this->theme_dir . '/assets/images/logo.png',
            $this->theme_dir . '/assets/images/logo-dark.png',
        ];
        
        foreach ($logo_sources as $logo_file) {
            if (file_exists($logo_file)) {
                $logo_id = $this->upload_file_to_media($logo_file, 'Flohmarkt Logo');
                if ($logo_id) {
                    set_theme_mod('custom_logo', $logo_id);
                    return 'imported (ID: ' . $logo_id . ')';
                }
            }
        }
        
        return 'no logo file found';
    }

    // =============================================
    // FAVICON
    // =============================================
    private function import_favicon() {
        // Check if already set
        if (get_option('site_icon')) return 'already set';
        
        $favicon_sources = [
            $this->media_dir . '/favicon.png',
            $this->theme_dir . '/assets/images/favicon.png',
        ];
        
        foreach ($favicon_sources as $favicon_file) {
            if (file_exists($favicon_file)) {
                $favicon_id = $this->upload_file_to_media($favicon_file, 'Site Favicon');
                if ($favicon_id) {
                    update_option('site_icon', $favicon_id);
                    return 'imported (ID: ' . $favicon_id . ')';
                }
            }
        }
        
        return 'no favicon file found';
    }

    // =============================================
    // NAVIGATION MENUS
    // =============================================
    private function import_menus() {
        $created = 0;
        
        // ---- PRIMARY MENU (Header) ----
        $primary_menu_name = 'Hauptmenü';
        $primary_menu = wp_get_nav_menu_object($primary_menu_name);
        
        if (!$primary_menu) {
            $primary_menu_id = wp_create_nav_menu($primary_menu_name);
            
            if (!is_wp_error($primary_menu_id)) {
                // Menu items for header
                $primary_items = [
                    ['title' => 'Startseite',       'url' => home_url('/'),              'order' => 1],
                    ['title' => 'Blog',             'url' => home_url('/blog/'),          'order' => 2],
                    ['title' => 'Veranstaltungen',  'url' => home_url('/veranstaltungen/'), 'order' => 3],
                    ['title' => 'Über uns',         'url' => home_url('/ueber-uns/'),     'order' => 4],
                    ['title' => 'Kontakt',          'url' => home_url('/kontakt/'),       'order' => 5],
                ];
                
                foreach ($primary_items as $item) {
                    // Try to find corresponding page
                    $page_slug = trim(parse_url($item['url'], PHP_URL_PATH), '/');
                    $page = get_page_by_path($page_slug);
                    
                    if ($page) {
                        // Link to actual page
                        wp_update_nav_menu_item($primary_menu_id, 0, [
                            'menu-item-title'     => $item['title'],
                            'menu-item-object-id' => $page->ID,
                            'menu-item-object'    => 'page',
                            'menu-item-type'      => 'post_type',
                            'menu-item-status'    => 'publish',
                            'menu-item-position'  => $item['order'],
                        ]);
                    } else {
                        // Custom link
                        wp_update_nav_menu_item($primary_menu_id, 0, [
                            'menu-item-title'    => $item['title'],
                            'menu-item-url'      => $item['url'],
                            'menu-item-type'     => 'custom',
                            'menu-item-status'   => 'publish',
                            'menu-item-position' => $item['order'],
                        ]);
                    }
                }
                
                // Assign to primary location
                $locations = get_theme_mod('nav_menu_locations', []);
                $locations['primary'] = $primary_menu_id;
                set_theme_mod('nav_menu_locations', $locations);
                $created++;
            }
        } else {
            // Menu exists, just assign location
            $locations = get_theme_mod('nav_menu_locations', []);
            $locations['primary'] = $primary_menu->term_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
        
        // ---- FOOTER MENU ----
        $footer_menu_name = 'Footer Menü';
        $footer_menu = wp_get_nav_menu_object($footer_menu_name);
        
        if (!$footer_menu) {
            $footer_menu_id = wp_create_nav_menu($footer_menu_name);
            
            if (!is_wp_error($footer_menu_id)) {
                $footer_items = [
                    ['title' => 'Startseite',       'url' => home_url('/'),              'order' => 1],
                    ['title' => 'Blog',             'url' => home_url('/blog/'),          'order' => 2],
                    ['title' => 'Veranstaltungen',  'url' => home_url('/veranstaltungen/'), 'order' => 3],
                    ['title' => 'Über uns',         'url' => home_url('/ueber-uns/'),     'order' => 4],
                    ['title' => 'Kontakt',          'url' => home_url('/kontakt/'),       'order' => 5],
                    ['title' => 'Impressum',        'url' => home_url('/impressum/'),     'order' => 6],
                    ['title' => 'Datenschutz',      'url' => home_url('/datenschutz/'),   'order' => 7],
                ];
                
                foreach ($footer_items as $item) {
                    $page_slug = trim(parse_url($item['url'], PHP_URL_PATH), '/');
                    $page = get_page_by_path($page_slug);
                    
                    if ($page) {
                        wp_update_nav_menu_item($footer_menu_id, 0, [
                            'menu-item-title'     => $item['title'],
                            'menu-item-object-id' => $page->ID,
                            'menu-item-object'    => 'page',
                            'menu-item-type'      => 'post_type',
                            'menu-item-status'    => 'publish',
                            'menu-item-position'  => $item['order'],
                        ]);
                    } else {
                        wp_update_nav_menu_item($footer_menu_id, 0, [
                            'menu-item-title'    => $item['title'],
                            'menu-item-url'      => $item['url'],
                            'menu-item-type'     => 'custom',
                            'menu-item-status'   => 'publish',
                            'menu-item-position' => $item['order'],
                        ]);
                    }
                }
                
                // Assign to footer location
                $locations = get_theme_mod('nav_menu_locations', []);
                $locations['footer'] = $footer_menu_id;
                set_theme_mod('nav_menu_locations', $locations);
                $created++;
            }
        } else {
            $locations = get_theme_mod('nav_menu_locations', []);
            $locations['footer'] = $footer_menu->term_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
        
        return $created;
    }

    // =============================================
    // CATEGORIES
    // =============================================
    private function import_categories() {
        $categories = $this->data['categories'] ?? [];
        $count = 0;
        
        foreach ($categories as $cat) {
            $existing = get_category_by_slug($cat['slug']);
            if ($existing) continue;
            
            $result = wp_insert_category([
                'cat_name'             => $cat['name'],
                'category_nicename'    => $cat['slug'],
                'category_description' => $cat['description'] ?? '',
            ]);
            
            if ($result && !is_wp_error($result)) $count++;
        }
        
        return $count;
    }

    // =============================================
    // PAGES
    // =============================================
    private function import_pages() {
        $pages = $this->data['pages'] ?? [];
        $count = 0;
        
        foreach ($pages as $page) {
            $existing = get_page_by_path($page['slug']);
            if ($existing) continue;
            
            $content = $this->process_content_images($page['content'], $page['inline_images'] ?? []);
            
            $post_id = wp_insert_post([
                'post_title'    => $page['title'],
                'post_name'     => $page['slug'],
                'post_content'  => $content,
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'page_template' => $page['template'] ?? '',
                'menu_order'    => $page['menu_order'] ?? 0,
            ]);
            
            if ($post_id && !is_wp_error($post_id)) {
                if (!empty($page['featured_image'])) {
                    $this->set_featured_image($post_id, $page['featured_image'], $page['title']);
                }
                $count++;
            }
        }
        
        return $count;
    }

    // =============================================
    // POSTS
    // =============================================
    private function import_posts() {
        $posts = $this->data['posts'] ?? [];
        $count = 0;
        
        foreach ($posts as $post) {
            $existing = get_page_by_path($post['slug'], OBJECT, 'post');
            if ($existing) continue;
            
            $content = $this->process_content_images($post['content'], $post['inline_images'] ?? []);
            
            // Resolve categories by slug
            $cat_ids = [];
            foreach ($post['categories'] ?? [] as $cat_slug) {
                $cat = get_category_by_slug($cat_slug);
                if ($cat) $cat_ids[] = $cat->term_id;
            }
            if (empty($cat_ids)) $cat_ids[] = 1;
            
            $post_id = wp_insert_post([
                'post_title'    => $post['title'],
                'post_name'     => $post['slug'],
                'post_content'  => $content,
                'post_excerpt'  => $post['excerpt'] ?? '',
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_date'     => $post['date'],
                'post_category' => $cat_ids,
            ]);
            
            if ($post_id && !is_wp_error($post_id)) {
                if (!empty($post['featured_image'])) {
                    $this->set_featured_image($post_id, $post['featured_image'], $post['title']);
                }
                $count++;
            }
        }
        
        return $count;
    }

    // =============================================
    // EVENTS
    // =============================================
    private function import_events() {
        $events = $this->data['events'] ?? [];
        $count = 0;
        
        foreach ($events as $event) {
            $existing = get_page_by_path($event['slug'], OBJECT, 'event');
            if ($existing) continue;
            
            $post_id = wp_insert_post([
                'post_title'   => $event['title'],
                'post_name'    => $event['slug'],
                'post_content' => $event['content'],
                'post_status'  => 'publish',
                'post_type'    => 'event',
                'post_date'    => $event['date'],
            ]);
            
            if ($post_id && !is_wp_error($post_id)) {
                foreach ($event['meta'] ?? [] as $key => $value) {
                    if (!empty($value)) update_post_meta($post_id, $key, $value);
                }
                $count++;
            }
        }
        
        return $count;
    }

    // =============================================
    // READING SETTINGS
    // =============================================
    private function configure_reading_settings() {
        update_option('show_on_front', 'posts');
        update_option('page_on_front', 0);
        update_option('page_for_posts', 0);
        update_option('permalink_structure', '/%postname%/');
        
        // Set blog page if it exists
        $blog_page = get_page_by_path('blog');
        if ($blog_page) {
            update_option('page_for_posts', $blog_page->ID);
        }
    }

    // =============================================
    // HELPER: Upload file to WP Media Library
    // =============================================
    private function upload_file_to_media($file_path, $title = '') {
        if (!file_exists($file_path)) return false;
        
        $filename = basename($file_path);
        $upload_dir = wp_upload_dir();
        $target_path = $upload_dir['path'] . '/' . $filename;
        
        // Avoid overwriting
        if (file_exists($target_path)) {
            $info = pathinfo($filename);
            $filename = $info['filename'] . '-' . time() . '.' . $info['extension'];
            $target_path = $upload_dir['path'] . '/' . $filename;
        }
        
        copy($file_path, $target_path);
        
        $mime = wp_check_filetype($filename);
        
        $attachment = [
            'post_mime_type' => $mime['type'] ?: 'image/png',
            'post_title'     => $title ?: sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];
        
        $attach_id = wp_insert_attachment($attachment, $target_path);
        
        if ($attach_id && !is_wp_error($attach_id)) {
            $attach_data = wp_generate_attachment_metadata($attach_id, $target_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            return $attach_id;
        }
        
        return false;
    }

    // =============================================
    // HELPER: Set featured image for a post
    // =============================================
    private function set_featured_image($post_id, $filename, $title = '') {
        $file_path = $this->media_dir . '/' . $filename;
        if (!file_exists($file_path)) return;
        $media_id = $this->upload_file_to_media($file_path, $title);
        if ($media_id) set_post_thumbnail($post_id, $media_id);
    }

    // =============================================
    // HELPER: Replace old image URLs in content
    // =============================================
    private function process_content_images($content, $inline_images = []) {
        if (empty($inline_images)) return $content;
        
        foreach ($inline_images as $img) {
            $file_path = $this->media_dir . '/' . $img['filename'];
            if (file_exists($file_path)) {
                $media_id = $this->upload_file_to_media($file_path, '');
                if ($media_id) {
                    $new_url = wp_get_attachment_url($media_id);
                    $content = str_replace($img['original_url'], $new_url, $content);
                }
            }
        }
        
        return $content;
    }

    // =============================================
    // RESET (for re-import)
    // =============================================
    public function reset_import() {
        delete_option('flohmarkt_theme_imported_v2');
        delete_option('flohmarkt_theme_import_date');
    }
}

// ============================================
// HOOKS
// ============================================

// Auto-import on theme activation
function flohmarkt_theme_activation_import() {
    $importer = new Flohmarkt_Theme_Importer();
    if ($importer->needs_import()) {
        $result = $importer->run_import();
        update_option('flohmarkt_import_result', $result);
    }
}
// add_action('after_switch_theme', 'flohmarkt_theme_activation_import');

// Admin notice after import
function flohmarkt_import_admin_notice() {
    $result = get_option('flohmarkt_import_result');
    if (!$result) return;
    
    if ($result['status'] === 'success') {
        $r = $result['results'];
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>🎉 Flohmarkt Theme — Alle Inhalte erfolgreich importiert!</strong></p>';
        echo '<ul style="list-style:disc;padding-left:20px;">';
        echo '<li>⚙️ ' . ($r['settings'] ?? 0) . ' Einstellungen konfiguriert</li>';
        echo '<li>🖼️ Logo: ' . ($r['logo'] ?? 'n/a') . '</li>';
        echo '<li>🔖 Favicon: ' . ($r['favicon'] ?? 'n/a') . '</li>';
        echo '<li>📁 ' . ($r['categories'] ?? 0) . ' Kategorien</li>';
        echo '<li>📄 ' . ($r['pages'] ?? 0) . ' Seiten importiert</li>';
        echo '<li>📝 ' . ($r['posts'] ?? 0) . ' Blog-Beiträge importiert</li>';
        echo '<li>📅 ' . ($r['events'] ?? 0) . ' Events importiert</li>';
        echo '<li>🔗 ' . ($r['menus'] ?? 0) . ' Navigationsmenüs erstellt</li>';
        echo '</ul>';
        echo '</div>';
    } elseif ($result['status'] === 'error') {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>❌ Import Fehler:</strong> ' . esc_html($result['message']) . '</p>';
        echo '</div>';
    }
    
    delete_option('flohmarkt_import_result');
}
add_action('admin_notices', 'flohmarkt_import_admin_notice');

// Admin page for manual import
function flohmarkt_import_admin_menu() {
    add_theme_page('Flohmarkt Import', '🔄 Theme Import', 'manage_options', 'flohmarkt-import', 'flohmarkt_import_admin_page');
}
add_action('admin_menu', 'flohmarkt_import_admin_menu');

function flohmarkt_import_admin_page() {
    $importer = new Flohmarkt_Theme_Importer();
    
    if (isset($_POST['flohmarkt_run_import']) && wp_verify_nonce($_POST['_wpnonce'], 'flohmarkt_import')) {
        $importer->reset_import();
        $result = $importer->run_import();
        echo '<div class="notice notice-success"><p><strong>✅ Import erfolgreich!</strong></p></div>';
        if ($result['status'] === 'success') {
            echo '<pre>' . print_r($result['results'], true) . '</pre>';
        }
    }
    
    if (isset($_POST['flohmarkt_reset_import']) && wp_verify_nonce($_POST['_wpnonce'], 'flohmarkt_import')) {
        $importer->reset_import();
        echo '<div class="notice notice-info"><p>Import zurückgesetzt. Sie können jetzt erneut importieren.</p></div>';
    }
    
    $imported = get_option('flohmarkt_theme_imported_v2', false);
    $import_date = get_option('flohmarkt_theme_import_date', '');
    ?>
    <div class="wrap">
        <h1>🏪 Flohmarkt Theme Import</h1>
        <div class="card" style="max-width: 700px; padding: 24px;">
            <h2 style="margin-top:0;">Was wird importiert?</h2>
            <table class="widefat" style="margin-bottom: 20px;">
                <tr><td>📝</td><td>Blog-Beiträge mit Bildern</td></tr>
                <tr><td>📄</td><td>Seiten (Kontakt, Impressum, Datenschutz, etc.)</td></tr>
                <tr><td>📅</td><td>Events mit Datum, Ort und Details</td></tr>
                <tr><td>📁</td><td>Alle Kategorien</td></tr>
                <tr><td>🖼️</td><td>Logo + Favicon</td></tr>
                <tr><td>🔗</td><td>Header- und Footer-Navigation</td></tr>
                <tr><td>⚙️</td><td>Seiteneinstellungen (Permalink, Sprache, etc.)</td></tr>
            </table>
            
            <?php if ($imported) : ?>
                <p><strong>Status:</strong> ✅ Importiert am <?php echo esc_html($import_date); ?></p>
                <form method="post">
                    <?php wp_nonce_field('flohmarkt_import'); ?>
                    <p>
                        <button type="submit" name="flohmarkt_reset_import" class="button">Zurücksetzen</button>
                        <button type="submit" name="flohmarkt_run_import" class="button button-primary">🔄 Erneut importieren</button>
                    </p>
                </form>
            <?php else : ?>
                <p><strong>Status:</strong> ⏳ Noch nicht importiert.</p>
                <form method="post">
                    <?php wp_nonce_field('flohmarkt_import'); ?>
                    <p><button type="submit" name="flohmarkt_run_import" class="button button-primary button-hero">🚀 Alles jetzt importieren</button></p>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
