<?php
/**
 * Automatically insert dummy events for the Flohmarkt theme
 * Run this from the WordPress root using `wp eval-file populate-events.php` or temporarily include in functions.php
 */

require_once( dirname(__FILE__) . '/wp-load.php' );

$flea_markets = [
    "Großer Antik- & Trödelmarkt",
    "Flohmarkt am Rheinufer",
    "Vintage Nachtflohmarkt",
    "Familien- & Babyflohmarkt",
    "Kunst- & Handwerkermarkt",
    "Kiezflohmarkt am Sonntag",
    "Bücher- und Schallplattenbörse",
    "Hallenflohmarkt Spezial",
    "Riesenflohmarkt am Messegelände",
    "Studenten-Flohmarkt"
];

$cities = ["Berlin", "München", "Hamburg", "Köln", "Frankfurt", "Stuttgart", "Düsseldorf", "Leipzig", "Dortmund", "Essen"];
$times = ["08:00 - 15:00", "09:00 - 16:00", "15:00 - 22:00", "10:00 - 17:00", "07:00 - 14:00"];

$current_month = date('n');
$current_year = date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

$events_created = 0;

// Create 25 random events spread across the month
for ($i = 0; $i < 25; $i++) {
    $title = $flea_markets[array_rand($flea_markets)] . " in " . $cities[array_rand($cities)];
    $day = rand(1, $days_in_month);
    $time = $times[array_rand($times)];
    $location = $cities[array_rand($cities)] . " Zentrum";
    
    // Format YYYY-MM-DD
    $date_str = sprintf("%04d-%02d-%02d", $current_year, $current_month, $day);

    $post_data = array(
        'post_title'    => $title,
        'post_content'  => 'Ein wunderbarer Flohmarkt mit vielen Ständen und tollen Angeboten. Kommen Sie vorbei und finden Sie einzigartige Schätze.',
        'post_status'   => 'publish',
        'post_type'     => 'event',
    );
    
    // Check if post already exists to prevent crazy duplicates if run multiple times
    $existing = get_page_by_title($title, OBJECT, 'event');
    if (!$existing) {
        $post_id = wp_insert_post($post_data);
        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_event_date', $date_str);
            update_post_meta($post_id, '_event_time', $time);
            update_post_meta($post_id, '_event_location', $location);
            $events_created++;
        }
    }
}

echo "Done! Created $events_created new dummy events for $current_year-$current_month.\n";
