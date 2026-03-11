<?php
/**
 * Single Post Template
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<div class="container">
    <?php flohmarkt_breadcrumbs(); ?>
</div>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<?php
// Auto-set first content image as featured if no featured image exists
if ( !has_post_thumbnail() ) {
    $content = get_the_content();
    preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $match);
    if ( !empty($match[1]) ) {
        // Try to find this image in the media library
        $image_url = $match[1];
        global $wpdb;
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE guid = %s LIMIT 1", $image_url
        ));
        if ( $attachment_id ) {
            set_post_thumbnail( get_the_ID(), $attachment_id );
        } else {
            // Sideload the image and set as featured
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $tmp = download_url( $image_url, 15 );
            if ( !is_wp_error($tmp) ) {
                $ext = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (!$ext) $ext = 'jpg';
                $file_array = [
                    'name'     => 'featured-' . get_the_ID() . '.' . $ext,
                    'tmp_name' => $tmp,
                ];
                $media_id = media_handle_sideload($file_array, get_the_ID());
                @unlink($tmp);
                if ( !is_wp_error($media_id) ) {
                    set_post_thumbnail( get_the_ID(), $media_id );
                }
            }
        }
    }
}
?>

<!-- Hero Featured Image Banner -->
<style>
    .single-hero-banner {
        position: relative;
        width: 100%;
        min-height: 420px;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .single-hero-banner .hero-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-size: cover;
        background-position: center;
        z-index: 1;
    }
    .single-hero-banner .hero-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.15) 100%);
        z-index: 2;
    }
    .single-hero-banner .hero-content {
        position: relative;
        z-index: 3;
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        padding: 3rem 2rem 2.5rem;
        color: #fff;
    }
    .single-hero-banner .hero-category {
        display: inline-block;
        padding: 5px 14px;
        background: var(--color-primary, #e8983a);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
    }
    .single-hero-banner .hero-title {
        font-size: clamp(1.6rem, 4vw, 2.6rem);
        font-weight: 800;
        line-height: 1.25;
        margin: 0 0 1rem;
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    }
    .single-hero-banner .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.2rem;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .single-hero-banner .hero-meta span {
        color: #fff;
    }
    .single-post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem auto;
        display: block;
        object-fit: contain;
    }
    @media (max-width: 768px) {
        .single-hero-banner { min-height: 320px; }
        .single-hero-banner .hero-content { padding: 2rem 1rem 1.5rem; }
    }
</style>

<?php
$hero_bg_style = 'background: linear-gradient(135deg, #1a6b8a 0%, #2b9bb5 50%, #e8983a 100%);';
if ( has_post_thumbnail() ) {
    $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
    if ( $thumb_url ) {
        $hero_bg_style = 'background-image: url(' . esc_url($thumb_url) . ');';
    }
}
$cats = get_the_category();
?>

<div class="single-hero-banner">
    <div class="hero-bg" style="<?php echo $hero_bg_style; ?>"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <?php if ( $cats ) : ?>
            <a href="<?php echo get_category_link( $cats[0]->term_id ); ?>" class="hero-category"><?php echo esc_html( $cats[0]->name ); ?></a>
        <?php endif; ?>
        <h1 class="hero-title"><?php the_title(); ?></h1>
        <div class="hero-meta">
            <span>📅 <?php echo get_the_date( 'j. F Y' ); ?></span>
            <span>✍️ <?php the_author(); ?></span>
            <span>⏱ <?php echo flohmarkt_reading_time( $post ); ?> Min. Lesezeit</span>
            <span>💬 <?php comments_number( '0 Kommentare', '1 Kommentar', '% Kommentare' ); ?></span>
        </div>
    </div>
</div>

<article class="single-post" id="post-<?php the_ID(); ?>">
    <div class="container">
        <div class="content-wrap">
            <div class="main-content">
                <!-- Post Content -->
                <div class="single-post-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php
                $tags = get_the_tags();
                if ( $tags ) : ?>
                <div style="margin: 32px 0;">
                    <?php foreach ( $tags as $tag ) : ?>
                        <a href="<?php echo get_tag_link( $tag->term_id ); ?>" style="display: inline-block; padding: 6px 16px; background: var(--color-bg-alt); border-radius: 20px; font-size: 0.85rem; margin: 4px; color: var(--color-text-light);">#<?php echo esc_html( $tag->name ); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Share Buttons -->
                <div class="share-buttons">
                    <button class="share-btn" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>','_blank')">📘 Facebook</button>
                    <button class="share-btn" onclick="window.open('https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>','_blank')">🐦 Twitter</button>
                    <button class="share-btn" onclick="window.open('https://www.linkedin.com/shareArticle?url=<?php echo urlencode( get_permalink() ); ?>','_blank')">💼 LinkedIn</button>
                    <button class="share-btn" onclick="window.open('https://wa.me/?text=<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>','_blank')">💬 WhatsApp</button>
                    <button class="share-btn" onclick="navigator.clipboard.writeText('<?php echo esc_url( get_permalink() ); ?>').then(()=>alert('Link kopiert!'))">🔗 Link kopieren</button>
                </div>

                <!-- Author Box -->
                <div class="author-box">
                    <?php echo get_avatar( get_the_author_meta('ID'), 72 ); ?>
                    <div>
                        <div class="author-name"><?php the_author(); ?></div>
                        <div class="author-bio"><?php echo get_the_author_meta('description') ?: 'Autor bei ' . get_bloginfo('name'); ?></div>
                    </div>
                </div>

                <!-- Related Posts -->
                <?php
                $related = get_posts( array(
                    'posts_per_page' => 3,
                    'post__not_in'   => array( get_the_ID() ),
                    'category__in'   => wp_get_post_categories( get_the_ID() ),
                    'orderby'        => 'rand',
                ));
                if ( $related ) : ?>
                <div class="related-posts">
                    <h3>Ähnliche Artikel</h3>
                    <div class="posts-grid">
                        <?php foreach ( $related as $rp ) {
                            echo flohmarkt_render_post_card( $rp );
                        } ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Comments -->
                <?php if ( comments_open() || get_comments_number() ) : ?>
                    <?php comments_template(); ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <?php get_sidebar(); ?>
        </div>
    </div>
</article>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
