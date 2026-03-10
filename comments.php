<?php
/**
 * Comments Template
 * @package Flohmarkt_Blog
 */

if ( post_password_required() ) return;
?>

<div id="comments" class="comments-area" style="margin-top: 40px;">
    <?php if ( have_comments() ) : ?>
    <h3 style="margin-bottom: 24px;">
        <?php comments_number( 'Keine Kommentare', '1 Kommentar', '% Kommentare' ); ?>
    </h3>

    <ol class="comment-list" style="list-style: none; padding: 0;">
        <?php wp_list_comments( array(
            'style'       => 'ol',
            'short_ping'  => true,
            'avatar_size' => 48,
        )); ?>
    </ol>

    <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php if ( comments_open() ) : ?>
    <div style="background: var(--color-surface); padding: 32px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); margin-top: 24px;">
        <?php comment_form( array(
            'title_reply'          => 'Kommentar hinterlassen',
            'title_reply_to'       => 'Antwort an %s',
            'cancel_reply_link'    => 'Antwort abbrechen',
            'label_submit'         => 'Kommentar absenden',
            'comment_notes_before' => '<p style="color: var(--color-text-muted); font-size: 0.9rem;">Ihre E-Mail-Adresse wird nicht veröffentlicht.</p>',
        )); ?>
    </div>
    <?php endif; ?>
</div>
