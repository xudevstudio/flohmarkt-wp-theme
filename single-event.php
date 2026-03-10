<?php
/**
 * Single Event Template
 * @package Flohmarkt_Blog
 */
get_header();

$event_date = get_post_meta( get_the_ID(), '_event_date', true );
$event_time = get_post_meta( get_the_ID(), '_event_time', true );
$end_time   = get_post_meta( get_the_ID(), '_event_end_time', true );
$city       = get_post_meta( get_the_ID(), '_event_city', true );
$address    = get_post_meta( get_the_ID(), '_event_address', true );
$organizer  = get_post_meta( get_the_ID(), '_event_organizer', true );
$website    = get_post_meta( get_the_ID(), '_event_website', true );
?>

<div class="container">
    <?php flohmarkt_breadcrumbs(); ?>
</div>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<!-- Event Schema.org Markup -->
<script type="application/ld+json">
<?php echo json_encode( array(
    '@context'  => 'https://schema.org',
    '@type'     => 'Event',
    'name'      => get_the_title(),
    'startDate' => $event_date . ($event_time ? 'T' . $event_time : ''),
    'endDate'   => $event_date . ($end_time ? 'T' . $end_time : ''),
    'location'  => array(
        '@type'   => 'Place',
        'name'    => $city ?: 'Deutschland',
        'address' => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $address,
            'addressLocality' => $city,
            'addressCountry'  => 'DE',
        ),
    ),
    'description' => wp_trim_words( get_the_excerpt() ?: get_the_content(), 30 ),
    'organizer'   => array( '@type' => 'Organization', 'name' => $organizer ?: get_bloginfo('name') ),
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>

<section class="section">
    <div class="container">
        <div class="content-wrap">
            <div class="main-content">
                <article style="background: var(--color-surface); border-radius: var(--radius-xl); overflow: hidden; border: 1px solid var(--color-border);">
                    <?php if ( has_post_thumbnail() ) : ?>
                    <div style="height: 350px; overflow: hidden;">
                        <?php the_post_thumbnail( 'flohmarkt-featured', array( 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
                    </div>
                    <?php endif; ?>

                    <div style="padding: 32px;">
                        <!-- Event Date Badge -->
                        <?php if ( $event_date ) : $ts = strtotime($event_date); ?>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                            <div class="event-date-badge" style="font-size: 1.2em;">
                                <span class="day"><?php echo date_i18n('d', $ts); ?></span>
                                <span class="month"><?php echo date_i18n('M', $ts); ?></span>
                            </div>
                            <div>
                                <h1 style="font-size: 1.8rem; margin: 0;"><?php the_title(); ?></h1>
                                <span style="color: var(--color-text-light); font-size: 0.9rem;"><?php echo date_i18n('l, j. F Y', $ts); ?></span>
                            </div>
                        </div>
                        <?php else : ?>
                        <h1 style="margin-bottom: 24px;"><?php the_title(); ?></h1>
                        <?php endif; ?>

                        <!-- Event Details Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
                            <?php if ( $event_time ) : ?>
                            <div style="background: var(--color-bg-alt); padding: 16px; border-radius: var(--radius-md);">
                                <strong style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 4px;">🕐 Uhrzeit</strong>
                                <?php echo esc_html($event_time); ?> Uhr<?php if ($end_time) echo ' – ' . esc_html($end_time) . ' Uhr'; ?>
                            </div>
                            <?php endif; ?>

                            <?php if ( $city ) : ?>
                            <div style="background: var(--color-bg-alt); padding: 16px; border-radius: var(--radius-md);">
                                <strong style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 4px;">📍 Ort</strong>
                                <?php echo esc_html($city); ?><?php if ($address) echo '<br><small>' . esc_html($address) . '</small>'; ?>
                            </div>
                            <?php endif; ?>

                            <?php if ( $organizer ) : ?>
                            <div style="background: var(--color-bg-alt); padding: 16px; border-radius: var(--radius-md);">
                                <strong style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 4px;">👤 Veranstalter</strong>
                                <?php echo esc_html($organizer); ?>
                            </div>
                            <?php endif; ?>

                            <?php if ( $website ) : ?>
                            <div style="background: var(--color-bg-alt); padding: 16px; border-radius: var(--radius-md);">
                                <strong style="display: block; font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 4px;">🌐 Website</strong>
                                <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener">Webseite besuchen</a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Event Description -->
                        <div class="single-post-content">
                            <?php the_content(); ?>
                        </div>

                        <!-- Share -->
                        <div class="share-buttons" style="margin-top: 24px;">
                            <button class="share-btn" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>','_blank')">📘 Teilen</button>
                            <button class="share-btn" onclick="window.open('https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>','_blank')">💬 WhatsApp</button>
                            <button class="share-btn" onclick="navigator.clipboard.writeText('<?php echo esc_url(get_permalink()); ?>').then(()=>alert('Link kopiert!'))">🔗 Link kopieren</button>
                        </div>
                    </div>
                </article>

                <!-- Description -->
                <?php
                $more_events = get_posts( array(
                    'post_type'      => 'event',
                    'posts_per_page' => 4,
                    'post__not_in'   => array( get_the_ID() ),
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
                if ( $more_events ) : ?>
                <div style="margin-top: 40px;">
                    <h3 style="margin-bottom: 20px;">Weitere Veranstaltungen</h3>
                    <div class="events-list">
                        <?php foreach ( $more_events as $ev ) :
                            $d = get_post_meta($ev->ID, '_event_date', true);
                            $t = get_post_meta($ev->ID, '_event_time', true);
                            $c = get_post_meta($ev->ID, '_event_city', true);
                        ?>
                        <a href="<?php echo get_permalink($ev->ID); ?>" class="event-card">
                            <div class="event-date-badge">
                                <span class="day"><?php echo date_i18n('d', strtotime($d)); ?></span>
                                <span class="month"><?php echo date_i18n('M', strtotime($d)); ?></span>
                            </div>
                            <div class="event-info">
                                <h3><?php echo esc_html($ev->post_title); ?></h3>
                                <?php if ($c) : ?><span class="event-location">📍 <?php echo esc_html($c); ?></span><?php endif; ?>
                                <?php if ($t) : ?><span class="event-time">🕐 <?php echo esc_html($t); ?> Uhr</span><?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</section>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
