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

<article class="single-post" id="post-<?php the_ID(); ?>">
    <!-- Content Layout -->
    <div class="container">
        <div class="content-wrap">
            <div class="main-content">
                <!-- Post Header -->
                <div class="single-post-header">
                    <?php
                    $cats = get_the_category();
                    if ( $cats ) : ?>
                        <div style="margin-bottom: 20px;">
                            <a href="<?php echo get_category_link( $cats[0]->term_id ); ?>" class="post-category"><?php echo esc_html( $cats[0]->name ); ?></a>
                        </div>
                    <?php endif; ?>

                    <h1><?php the_title(); ?></h1>

                    <div class="single-post-meta" style="justify-content: flex-start; margin-bottom: 20px;">
                        <span>📅 <?php echo get_the_date( 'j. F Y' ); ?></span>
                        <span>✍️ <?php the_author(); ?></span>
                        <span>⏱ <?php echo flohmarkt_reading_time( $post ); ?> Min. Lesezeit</span>
                        <span>💬 <?php comments_number( '0 Kommentare', '1 Kommentar', '% Kommentare' ); ?></span>
                    </div>
                </div>

                <!-- Post Content -->
                <div class="single-post-content">
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
