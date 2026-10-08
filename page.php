<?php
/**
 * Page template — the design's <main> shell around the page content.
 *
 * Managed design pages render either their Elementor document or, when that
 * document is empty, the version-controlled fragment (see inc/managed-pages.php).
 *
 * @package SkillsOnline
 */

get_header();

$so_managed = get_post_meta( get_the_ID(), '_so_managed', true );
?>
<main id="so-main" class="w-full pt-[6.5rem] bg-surface min-h-screen">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
