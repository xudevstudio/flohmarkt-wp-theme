<?php
/**
 * Homepage / Main Index Template
 * @package Flohmarkt_Blog
 */
get_header(); ?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Flohmärkte & Trödelmärkte in Deutschland</h1>
            <p>Entdecken Sie die besten Flohmärkte, Tipps zum Handeln, Schatzsuche und aktuelle Veranstaltungstermine in Ihrer Nähe.</p>

            <form class="hero-search fade-in" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="search" name="s" placeholder="Flohmärkte suchen..." aria-label="Suche" value="<?php echo get_search_query(); ?>">
                <button type="submit" aria-label="Suchen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>

            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-number"><?php echo wp_count_posts()->publish; ?></span>
                    <span class="stat-label">Artikel</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-number"><?php echo flohmarkt_upcoming_events_count(); ?></span>
                    <span class="stat-label">Veranstaltungen</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-number"><?php echo count( get_categories( array( 'hide_empty' => false ) ) ); ?></span>
                    <span class="stat-label">Kategorien</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TRUST BADGES / FEATURES -->
<section class="section" style="padding-top: 40px; padding-bottom: 20px;">
    <div class="container">
        <div class="trust-badges-grid" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; align-items: stretch;">
            <!-- Badge 1 -->
            <div class="trust-badge" style="flex: 1; min-width: 200px; background: var(--color-surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: transform 0.3s ease; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <span style="font-size: 2.5rem; margin-bottom: 12px; display: block;">🇩🇪</span>
                <h3 style="font-size: 1.1rem; margin-bottom: 8px;">Ganz Deutschland</h3>
                <p style="font-size: 0.9rem; color: var(--color-text-light); margin: 0;">Termine aus allen Bundesländern</p>
            </div>
            <!-- Badge 2 -->
            <div class="trust-badge" style="flex: 1; min-width: 200px; background: var(--color-surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: transform 0.3s ease; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <span style="font-size: 2.5rem; margin-bottom: 12px; display: block;">📅</span>
                <h3 style="font-size: 1.1rem; margin-bottom: 8px;">Täglich Aktuell</h3>
                <p style="font-size: 0.9rem; color: var(--color-text-light); margin: 0;">Immer die neuesten Flohmarkt-Termine</p>
            </div>
            <!-- Badge 3 -->
            <div class="trust-badge" style="flex: 1; min-width: 200px; background: var(--color-surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: transform 0.3s ease; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <span style="font-size: 2.5rem; margin-bottom: 12px; display: block;">💯</span>
                <h3 style="font-size: 1.1rem; margin-bottom: 8px;">100% Kostenlos</h3>
                <p style="font-size: 0.9rem; color: var(--color-text-light); margin: 0;">Keine versteckten Gebühren für Besucher</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED POST -->
<?php
$featured = get_posts( array( 'posts_per_page' => 1, 'post_status' => 'publish', 'meta_key' => '_thumbnail_id' ) );
if ( $featured ) :
    $fp = $featured[0];
    $thumb = get_the_post_thumbnail_url( $fp->ID, 'flohmarkt-featured' );
    $cats = get_the_category( $fp->ID );
?>
<section class="section">
    <div class="container">
        <div class="featured-post fade-in">
            <div class="featured-post-image">
                <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $fp->post_title ); ?>" loading="lazy">
            </div>
            <div class="featured-post-content">
                <?php if ( ! empty($cats) ) : ?>
                    <span class="post-card-category"><?php echo esc_html( $cats[0]->name ); ?></span>
                <?php endif; ?>
                <h2><?php echo esc_html( $fp->post_title ); ?></h2>
                <p><?php echo wp_trim_words( $fp->post_excerpt ?: $fp->post_content, 40, '...' ); ?></p>
                <a href="<?php echo get_permalink( $fp->ID ); ?>" class="btn btn-primary">Weiterlesen →</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- LATEST ARTICLES -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Neueste Artikel</h2>
            <p>Aktuelle Tipps, Guides und Geschichten rund um Flohmärkte und Trödelmärkte</p>
            <div class="accent-line"></div>
        </div>

        <div class="posts-grid">
            <?php
            $latest = new WP_Query( array(
                'posts_per_page' => 6,
                'post_status'    => 'publish',
                'offset'         => $featured ? 1 : 0,
            ));

            if ( $latest->have_posts() ) :
                while ( $latest->have_posts() ) : $latest->the_post();
                    echo flohmarkt_render_post_card( $post );
                endwhile;
                wp_reset_postdata();
            else :
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <h3>Willkommen beim Flohmarkt Blog!</h3>
                    <p style="color: var(--color-text-light);">Bald erscheinen hier spannende Artikel über Flohmärkte und Trödelmärkte in ganz Deutschland. Bleiben Sie dran!</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $latest->found_posts > 6 ) : ?>
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>" class="btn btn-outline">Alle Artikel ansehen →</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CATEGORIES SECTION -->
<?php
$categories = get_categories( array( 'hide_empty' => false, 'number' => 8 ) );
if ( ! empty( $categories ) ) :
    $cat_icons = array( '🏪', '💰', '🎨', '📦', '🛍️', '🗺️', '📸', '🧸', '🔧', '👗', '📚', '🎵' );
?>
<section class="section" style="background: var(--color-bg-alt);">
    <div class="container">
        <div class="section-title">
            <h2>Kategorien entdecken</h2>
            <p>Finden Sie Artikel zu Ihrem Lieblingsthema</p>
            <div class="accent-line"></div>
        </div>

        <div class="categories-grid">
            <?php foreach ( $categories as $i => $cat ) : ?>
            <a href="<?php echo get_category_link( $cat->term_id ); ?>" class="category-card fade-in">
                <span class="cat-icon"><?php echo $cat_icons[ $i % count($cat_icons) ]; ?></span>
                <span class="cat-name"><?php echo esc_html( $cat->name ); ?></span>
                <span class="cat-count"><?php echo $cat->count; ?> Artikel</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- HOW IT WORKS SECTION -->
<section class="section" style="background: var(--color-bg);">
    <div class="container">
        <div class="section-title">
            <h2>Wie es funktioniert</h2>
            <p>In wenigen Schritten zum perfekten Flohmarkt-Erlebnis</p>
            <div class="accent-line"></div>
        </div>

        <div class="how-it-works-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; position: relative;">
            <!-- Step 1 -->
            <div style="background: var(--color-surface); border-radius: var(--radius-lg); padding: 40px 30px; text-align: center; box-shadow: var(--shadow-md); border: 1px solid var(--color-border); position: relative; z-index: 2; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark)); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 20px; box-shadow: var(--shadow-glow);">1</div>
                <h3 style="margin-bottom: 12px;">Suchen</h3>
                <p style="color: var(--color-text-light); font-size: 0.95rem;">Nutzen Sie unsere Suchfunktion oder stöbern Sie nach Flohmärkten in Ihrer Nähe.</p>
            </div>
            
            <!-- Step 2 -->
            <div style="background: var(--color-surface); border-radius: var(--radius-lg); padding: 40px 30px; text-align: center; box-shadow: var(--shadow-md); border: 1px solid var(--color-border); position: relative; z-index: 2; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light)); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 20px; box-shadow: 0 0 24px rgba(32, 178, 170, 0.4);">2</div>
                <h3 style="margin-bottom: 12px;">Entdecken</h3>
                <p style="color: var(--color-text-light); font-size: 0.95rem;">Finden Sie alle wichtigen Informationen wie Öffnungszeiten, Adressen und Tipps.</p>
            </div>
            
            <!-- Step 3 -->
            <div style="background: var(--color-surface); border-radius: var(--radius-lg); padding: 40px 30px; text-align: center; box-shadow: var(--shadow-md); border: 1px solid var(--color-border); position: relative; z-index: 2; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--color-accent), var(--color-accent-light)); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 20px; box-shadow: 0 0 24px rgba(255, 183, 77, 0.4);">3</div>
                <h3 style="margin-bottom: 12px;">Erleben</h3>
                <p style="color: var(--color-text-light); font-size: 0.95rem;">Besuchen Sie den Markt, handeln Sie wie ein Profi und finden Sie einzigartige Schätze!</p>
            </div>
        </div>
    </div>
</section>



<!-- TOP CITIES GRID -->
<section class="section top-cities" style="background: var(--color-bg); padding-top: 20px;">
    <div class="container">
        <div class="section-title">
            <h2>Beliebte Städte</h2>
            <p>Finden Sie Flohmärkte schnell in den größten Metropolen Deutschlands</p>
            <div class="accent-line"></div>
        </div>
        
        <div class="cities-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px;">
            <?php
            $top_cities = [
                ['name' => 'Berlin', 'icon' => '🏛️'],
                ['name' => 'Hamburg', 'icon' => '⚓'],
                ['name' => 'München', 'icon' => '🥨'],
                ['name' => 'Köln', 'icon' => '⛪'],
            ];
            foreach ($top_cities as $city) :
                $search_url = esc_url( add_query_arg( 's', $city['name'], home_url( '/' ) ) );
            ?>
                <a href="<?php echo $search_url; ?>" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 30px; display: flex; align-items: center; gap: 20px; box-shadow: var(--shadow-sm); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease; text-decoration: none;" onmouseover="this.style.transform='translateY(-6px) scale(1.02)'; this.style.boxShadow='var(--shadow-md)'; this.style.borderColor='var(--color-primary)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='var(--shadow-sm)'; this.style.borderColor='var(--color-border)';">
                    <span style="font-size: 2.5rem; background: rgba(var(--color-primary-rgb), 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;"><?php echo $city['icon']; ?></span>
                    <div>
                        <h3 style="margin: 0; font-size: 1.25rem; color: var(--color-text); font-family: var(--font-heading);"><?php echo $city['name']; ?></h3>
                        <span style="font-size: 0.85rem; color: var(--color-primary); font-weight: 500;">Märkte ansehen →</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- LATEST EVENTS LIST -->
<section class="section latest-events-section" style="background: var(--color-bg);">
    <div class="container">
        <div class="section-title">
            <h2>Neueste Veranstaltungen</h2>
            <p>Die aktuellsten Termine aus ganz Deutschland</p>
            <div class="accent-line"></div>
        </div>

        <?php
        $latest_events = new WP_Query( array(
            'post_type'      => 'event',
            'posts_per_page' => 5,
            'orderby'        => 'meta_value',
            'meta_key'       => '_event_date',
            'order'          => 'ASC',
            'meta_query'     => array( array(
                'key'     => '_event_date',
                'value'   => date('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE',
            )),
        ) );

        if ( $latest_events->have_posts() ) :
            echo '<div style="display: flex; flex-direction: column; gap: 16px;">';
            while ( $latest_events->have_posts() ) : $latest_events->the_post();
                $date = get_post_meta( get_the_ID(), '_event_date', true );
                $time = get_post_meta( get_the_ID(), '_event_time', true );
                $city = get_post_meta( get_the_ID(), '_event_city', true );
                ?>
                <a href="<?php the_permalink(); ?>" style="display: block; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 20px; text-decoration: none; color: inherit; transition: all 0.3s ease; box-shadow: var(--shadow-sm);" onmouseover="this.style.borderColor='var(--color-primary)'; this.style.transform='translateX(8px)';" onmouseout="this.style.borderColor='var(--color-border)'; this.style.transform='translateX(0)';">
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
                    </div>
                </a>
                <?php
            endwhile;
            echo '</div>';
            echo '<div style="text-align: center; margin-top: 32px;"><a href="' . get_post_type_archive_link( 'event' ) . '" class="btn btn-primary">Alle Veranstaltungen ansehen →</a></div>';
            wp_reset_postdata();
        else :
            echo '<p style="text-align:center;">Derzeit sind keine Veranstaltungen verfügbar.</p>';
        endif;
        ?>
    </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="section testimonials" style="background: linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.03) 0%, rgba(var(--color-accent-rgb), 0.05) 100%);">
    <div class="container">
        <div class="section-title">
            <h2>Die Flohmarkt-Community</h2>
            <p>Das sagen unsere Nutzer über erfolgreiche Schatzsuchen</p>
            <div class="accent-line"></div>
        </div>

        <div class="testimonials-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <!-- Review 1 -->
            <div style="background: var(--color-surface-glass); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding: 32px; border-radius: var(--radius-xl); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: var(--shadow-md); position: relative;">
                <div style="color: var(--color-accent); font-size: 2rem; position: absolute; top: 20px; right: 30px; opacity: 0.3;">"</div>
                <div style="display: flex; gap: 4px; color: var(--color-accent); margin-bottom: 16px;">
                    ★★★★★
                </div>
                <p style="font-size: 1.05rem; font-style: italic; color: var(--color-text); margin-bottom: 24px; line-height: 1.6;">"Dank dieser Plattform habe ich den perfekten Vintage-Sessel für mein Wohnzimmer gefunden! Die Termine sind immer super aktuell."</p>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-family: var(--font-heading);">S</div>
                    <div>
                        <div style="font-weight: 700; color: var(--color-text); font-family: var(--font-heading);">Sarah K.</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-light);">Trödel-Liebhaberin</div>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div style="background: var(--color-surface-glass); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding: 32px; border-radius: var(--radius-xl); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: var(--shadow-md); position: relative;">
                <div style="color: var(--color-accent); font-size: 2rem; position: absolute; top: 20px; right: 30px; opacity: 0.3;">"</div>
                <div style="display: flex; gap: 4px; color: var(--color-accent); margin-bottom: 16px;">
                    ★★★★★
                </div>
                <p style="font-size: 1.05rem; font-style: italic; color: var(--color-text); margin-bottom: 24px; line-height: 1.6;">"Endlich eine mobile-freundliche Seite. Wenn ich am Wochenende spontan los will, schaue ich nur kurz aufs Handy - perfekt!"</p>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--color-secondary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-family: var(--font-heading);">M</div>
                    <div>
                        <div style="font-weight: 700; color: var(--color-text); font-family: var(--font-heading);">Markus T.</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-light);">Sammler & Verkäufer</div>
                    </div>
                </div>
            </div>
            
            <!-- Review 3 -->
            <div style="background: var(--color-surface-glass); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding: 32px; border-radius: var(--radius-xl); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: var(--shadow-md); position: relative;">
                <div style="color: var(--color-accent); font-size: 2rem; position: absolute; top: 20px; right: 30px; opacity: 0.3;">"</div>
                <div style="display: flex; gap: 4px; color: var(--color-accent); margin-bottom: 16px;">
                    ★★★★★
                </div>
                <p style="font-size: 1.05rem; font-style: italic; color: var(--color-text); margin-bottom: 24px; line-height: 1.6;">"Die automatische Import-Funktion sorgt dafür, dass wirklich ALLES drin steht. Besser als jede App, die ich bisher getestet habe."</p>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--color-primary-dark); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-family: var(--font-heading);">J</div>
                    <div>
                        <div style="font-weight: 700; color: var(--color-text); font-family: var(--font-heading);">Julia W.</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-light);">Schnäppchenjägerin</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT US / TRUST SECTION -->
<style>
.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
}
.about-heading-mobile {
    display: none;
}
@media (max-width: 768px) {
    .about-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }
    .about-heading-desktop {
        display: none;
    }
    .about-heading-mobile {
        display: block;
        font-size: clamp(2.2rem, 6vw, 2.8rem);
        margin-bottom: 20px;
        color: var(--color-text-dark);
        font-weight: 800;
        order: 1; /* First */
    }
    .about-image {
        order: 2; /* Second */
        max-width: 400px;
        margin: 0 auto;
    }
    .about-content {
        order: 3; /* Third */
    }
}
</style>
<section class="section about-snippet" style="background: var(--color-bg-alt);">
    <div class="container">
        <div class="about-grid">
            <h2 class="about-heading-mobile">Wir lieben Flohmärkte!</h2>
            <div class="about-image" style="width: 100%; position: relative; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg);">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about_section_flea_market_1772894828539.png" alt="Flohmarkt in Deutschland" style="width: 100%; height: auto; object-fit: cover; transition: transform 0.5s ease; border-radius: inherit;" loading="lazy" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            </div>
            <div class="about-content">
                <h2 class="about-heading-desktop" style="font-size: clamp(2.5rem, 5vw, 3rem); margin-bottom: 24px; color: var(--color-text-dark); font-weight: 800;">Wir lieben Flohmärkte!</h2>
                <p style="font-size: 1.15rem; line-height: 1.8; margin-bottom: 16px; color: var(--color-text);">Willkommen bei Deutschlands größtem Verzeichnis für Flohmärkte und Trödelmärkte. Unser leidenschaftliches Team recherchiert täglich die besten Geheimtipps, von seltenen Antikmärkten bis hin zu gemütlichen Hinterhofflohmärkten in Ihrer Nachbarschaft.</p>
                <p style="font-size: 1.15rem; line-height: 1.8; margin-bottom: 30px; color: var(--color-text);">Unser Ziel ist es, einzigartige Schätze und nachhaltigen Konsum für alle zugänglich zu machen – mit ehrlichen Ratgebern, aktuellen Terminen und einer starken Community.</p>
                <a href="<?php echo home_url('/ueber-uns/'); ?>" class="btn btn-outline" style="min-width: 200px; margin-bottom: 20px;">Mehr über uns erfahren →</a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ SECTION -->
<style>
.custom-faq-details > summary { list-style: none; }
.custom-faq-details > summary::-webkit-details-marker { display: none; }
.custom-faq-details[open] summary .faq-icon { transform: rotate(45deg); color: var(--color-accent); }
.faq-icon { transition: transform 0.3s ease, color 0.3s ease; }
</style>
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Häufige Fragen (FAQ)</h2>
            <p>Alles, was Sie über Flohmärkte und unsere Plattform wissen müssen</p>
            <div class="accent-line"></div>
        </div>

        <div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px;">
            <details class="custom-faq-details" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0; box-shadow: var(--shadow-sm); cursor: pointer; transition: all 0.3s ease; overflow: hidden;">
                <summary style="font-family: var(--font-heading); font-weight: 600; font-size: 1.1rem; color: var(--color-text); outline: none; display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                    Sind die Termine auf der Webseite aktuell? 
                    <span class="faq-icon" style="color: var(--color-primary); font-size: 1.4rem; font-weight: bold;">+</span>
                </summary>
                <div style="padding: 0 20px 20px 20px; color: var(--color-text-light); line-height: 1.6; border-top: 1px solid var(--color-border); padding-top: 16px;">
                    Ja, wir aktualisieren unsere Datenbank täglich, um Ihnen die neuesten und genauesten Informationen zu gewährleisten. Es wird jedoch empfohlen, vor der Anreise die offiziellen Veranstalter-Websites zu prüfen.
                </div>
            </details>
            
            <details class="custom-faq-details" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0; box-shadow: var(--shadow-sm); cursor: pointer; transition: all 0.3s ease; overflow: hidden;">
                <summary style="font-family: var(--font-heading); font-weight: 600; font-size: 1.1rem; color: var(--color-text); outline: none; display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                    Wie kann ich selbst einen Flohmarkt eintragen? 
                    <span class="faq-icon" style="color: var(--color-primary); font-size: 1.4rem; font-weight: bold;">+</span>
                </summary>
                <div style="padding: 0 20px 20px 20px; color: var(--color-text-light); line-height: 1.6; border-top: 1px solid var(--color-border); padding-top: 16px;">
                    Aktuell importieren wir Termine automatisch über unsere Partner-Netzwerke. In Kürze werden wir ein Formular anbieten, über das Veranstalter ihre Termine kostenlos einreichen können.
                </div>
            </details>
            
            <details class="custom-faq-details" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0; box-shadow: var(--shadow-sm); cursor: pointer; transition: all 0.3s ease; overflow: hidden;">
                <summary style="font-family: var(--font-heading); font-weight: 600; font-size: 1.1rem; color: var(--color-text); outline: none; display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                    Kostet die Nutzung der Webseite etwas? 
                    <span class="faq-icon" style="color: var(--color-primary); font-size: 1.4rem; font-weight: bold;">+</span>
                </summary>
                <div style="padding: 0 20px 20px 20px; color: var(--color-text-light); line-height: 1.6; border-top: 1px solid var(--color-border); padding-top: 16px;">
                    Nein, unser Portal ist für alle Besucher zu 100% kostenlos. Wir finanzieren uns rein durch Werbeeinblendungen und Partnerprogramme.
                </div>
            </details>
        </div>
    </div>
</section>

<!-- NEWSLETTER CTA -->
<section class="section">
    <div class="container">
        <div class="newsletter-cta-box">
            <h2>Verpassen Sie keine Veranstaltung!</h2>
            <p>Abonnieren Sie unseren Newsletter und erhalten Sie die neuesten Flohmarkt-Termine und Tipps.</p>
            <div class="newsletter-cta-form">
                <input type="email" placeholder="Ihre E-Mail-Adresse">
                <button class="btn btn-accent">Abonnieren</button>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
