<?php
/**
 * Page Template
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<div class="container">
    <?php flohmarkt_breadcrumbs(); ?>
</div>

<section class="section" <?php if ( in_array( get_post_field( 'post_name', get_post() ), ['ueber-uns', 'kontakt', 'datenschutz', 'agb', 'impressum'] ) ) echo 'style="padding-top: 0; padding-bottom: 40px;"'; ?>>
    <div class="container">
        <div class="content-wrap full-width" style="padding-top: 0;">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article class="single-post-content" style="max-width: 900px; margin: 0 auto;">
                <h1 style="text-align: center; margin-bottom: 32px;"><?php the_title(); ?></h1>
                <?php if ( has_post_thumbnail() ) : ?>
                <div class="single-post-image" style="margin-bottom: 32px;">
                    <?php the_post_thumbnail( 'flohmarkt-featured' ); ?>
                </div>
                <?php endif; ?>
                <?php the_content(); ?>
            </article>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
