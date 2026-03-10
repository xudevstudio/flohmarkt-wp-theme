<?php
/**
 * Template Name: Blog Page
 * Description: Custom page template to display all latest blog articles.
 */
get_header(); ?>

<section class="hero" style="padding: 60px 0; text-align: center;">
    <div class="container">
        <h1 style="color: #fff;">Flohmarkt & Trödelmarkt Blog</h1>
        <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem; max-width: 600px; margin: 0 auto;">Entdecken Sie alle Tipps, Ratgeber und spannenden Geschichten rund um den Trödel.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php flohmarkt_breadcrumbs(); ?>
        
        <div class="posts-grid" style="margin-top: 40px;">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
            $blog_query = new WP_Query( array(
                'post_type'      => 'post',
                'posts_per_page' => 12,
                'post_status'    => 'publish',
                'paged'          => $paged
            ));

            if ( $blog_query->have_posts() ) :
                while ( $blog_query->have_posts() ) : $blog_query->the_post();
                    global $post;
                    echo flohmarkt_render_post_card( $post );
                endwhile;
            else :
                echo '<p>Keine Artikel gefunden.</p>';
            endif;
            ?>
        </div>

        <?php if ( $blog_query->max_num_pages > 1 ) : ?>
            <div class="pagination" style="margin-top: 40px; display: flex; justify-content: center; gap: 10px;">
                <?php
                echo paginate_links( array(
                    'base'      => home_url('/blog/page/%#%/'),
                    'format'    => '?paged=%#%',
                    'current'   => max( 1, $paged ),
                    'total'     => $blog_query->max_num_pages,
                    'prev_text' => '&laquo; Zurück',
                    'next_text' => 'Weiter &raquo;',
                    'type'      => 'list'
                ) );
                ?>
            </div>
            <style>
                .pagination ul { list-style: none; display: flex; gap: 8px; padding: 0; }
                .pagination a, .pagination span { padding: 10px 16px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); background: var(--color-surface); color: var(--color-text); text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
                .pagination a:hover { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
                .pagination .current { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
            </style>
        <?php endif; 
        wp_reset_postdata(); ?>
    </div>
</section>



<section class="section" style="padding-bottom: 80px;"></section>

<?php get_footer(); ?>
