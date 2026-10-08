<?php
/**
 * Blog archive (the posts page) — the design's Knowledge Base screen around a
 * real WordPress loop.
 *
 * The static chrome (editorial hero, topic hubs, residency CTA) is the exported
 * design markup, stored under inc/content/blog/. The post cards are rendered
 * from the loop with the design's own card classes.
 *
 * @package SkillsOnline
 */

get_header();

$so_paged    = max( 1, (int) get_query_var( 'paged' ) );
$so_featured = null;
if ( 1 === $so_paged ) {
	$so_sticky = get_option( 'sticky_posts' );
	$so_q      = new WP_Query(
		array(
			'post__in'            => $so_sticky ? $so_sticky : array(),
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
		)
	);
	if ( $so_q->have_posts() ) {
		$so_featured = $so_q->posts[0];
	} else {
		$so_q = new WP_Query( array( 'posts_per_page' => 1, 'ignore_sticky_posts' => true ) );
		if ( $so_q->have_posts() ) {
			$so_featured = $so_q->posts[0];
		}
	}
	wp_reset_postdata();
}

/**
 * One post card in the design's article-card style.
 *
 * @param int  $post_id Post id.
 * @param bool $compact Compact variant (no thumbnail).
 * @return string
 */
function so_post_card( $post_id, $compact = false ) {
	$cats    = get_the_category( $post_id );
	$cat     = $cats ? $cats[0] : null;
	$thumb   = get_the_post_thumbnail_url( $post_id, 'large' );
	$media   = '';
	if ( $thumb && ! $compact ) {
		$media = '<div class="relative w-full h-48 rounded-lg overflow-hidden bg-surface-container-high">'
			. '<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="' . esc_url( $thumb )
			. '" alt="' . esc_attr( get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true ) ?: get_the_title( $post_id ) ) . '" loading="lazy" decoding="async">'
			. ( $cat ? '<div class="absolute top-3 left-3 flex gap-2"><span class="px-2.5 py-1 rounded-md bg-inverse-surface/80 text-surface-container-lowest backdrop-blur-md font-label-caps text-label-caps">' . esc_html( strtoupper( $cat->name ) ) . '</span></div>' : '' )
			. '</div>';
	}
	$meta = so_read_time( $post_id ) . ( $cat ? ' • ' . esc_html( $cat->name ) : '' );
	$byline = so_article_byline( $post_id );

	return '<article class="bg-surface-container-lowest rounded-xl p-space-lg shadow-md flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all group">'
		. '<div class="space-y-space-md">' . $media
		. '<div class="space-y-2">'
		. '<span class="font-label-md text-label-md text-on-surface-variant">' . esc_html( $meta ) . '</span>'
		. '<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold group-hover:text-primary transition-colors leading-snug">'
		. '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>'
		. '<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-3">' . esc_html( so_excerpt_for( $post_id ) ) . '</p>'
		. '</div></div>'
		. '<div class="pt-space-md mt-space-md flex items-center justify-between border-t border-surface-container">'
		. '<div class="flex items-center gap-2">' . $byline['avatar']
		. '<div><p class="font-label-md text-label-md text-on-surface font-semibold">' . esc_html( $byline['name'] ) . '</p>'
		. '<p class="text-[11px] text-on-surface-variant">' . esc_html( $byline['role'] ) . '</p></div></div>'
		. '<span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors" aria-hidden="true">arrow_outward</span>'
		. '</div></article>';
}
?>
<main id="so-main" class="w-full pt-[6.5rem] bg-surface min-h-screen">
	<?php
	// ---------------------------------------------------------------- hero --
	echo so_blog_fragment( 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shipped design markup.
	?>

	<?php if ( $so_featured ) : ?>
		<?php // --------------------------------------------------- featured -- ?>
		<section class="w-full px-margin-mobile md:px-margin -mt-4 pb-space-xl">
			<div class="max-w-7xl mx-auto">
				<a class="block bg-surface-container-lowest rounded-xl p-space-lg md:p-space-xl shadow-lg hover:shadow-xl transition-all group" href="<?php echo esc_url( get_permalink( $so_featured ) ); ?>">
					<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-center">
						<div class="lg:col-span-7 space-y-space-md">
							<div class="flex flex-wrap items-center gap-space-sm">
								<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-secondary-container/30 text-on-secondary-container font-label-caps text-label-caps uppercase"><?php echo so_icon( 'local_fire_department', 'text-[16px]' ); ?> Featured playbook</span>
								<span class="font-label-md text-label-md text-on-surface-variant"><?php echo esc_html( so_read_time( $so_featured->ID ) ); ?></span>
							</div>
							<h2 class="font-headline-lg text-headline-lg text-on-surface font-extrabold tracking-tight group-hover:text-primary transition-colors"><?php echo esc_html( get_the_title( $so_featured ) ); ?></h2>
							<p class="font-body-lg text-body-lg text-on-surface-variant"><?php echo esc_html( so_excerpt_for( $so_featured->ID, 42 ) ); ?></p>
							<span class="inline-flex items-center gap-space-xs text-primary font-label-lg text-label-lg font-semibold">Read the playbook <?php echo so_icon( 'arrow_forward', 'text-lg' ); ?></span>
						</div>
						<?php if ( get_the_post_thumbnail_url( $so_featured->ID, 'large' ) ) : ?>
							<div class="lg:col-span-5">
								<img class="w-full h-64 object-cover rounded-xl" src="<?php echo esc_url( get_the_post_thumbnail_url( $so_featured->ID, 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $so_featured ) ); ?>" loading="lazy" decoding="async">
							</div>
						<?php endif; ?>
					</div>
				</a>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php // -------------------------------------------------- post grid -- ?>
		<section class="w-full px-margin-mobile md:px-margin py-space-lg">
			<div class="max-w-7xl mx-auto space-y-space-lg">
				<div class="flex items-end justify-between gap-space-md flex-wrap">
					<div>
						<span class="font-label-caps text-label-caps text-primary bg-primary/10 px-space-sm py-1 rounded-full"><?php echo 1 === $so_paged ? esc_html__( 'TOP READS', 'skills-online' ) : esc_html__( 'ARCHIVE', 'skills-online' ); ?></span>
						<h2 class="font-headline-lg text-headline-lg text-on-surface font-extrabold mt-space-sm">
							<?php
							if ( is_category() || is_tag() ) {
								printf( /* translators: %s: term name. */ esc_html__( 'Articles in %s', 'skills-online' ), esc_html( single_term_title( '', false ) ) );
							} else {
								esc_html_e( 'High-velocity engineering guides', 'skills-online' );
							}
							?>
						</h2>
					</div>
					<p class="font-body-sm text-body-sm text-on-surface-variant"><?php echo esc_html( sprintf( /* translators: %d: post count. */ _n( '%d article', '%d articles', (int) $GLOBALS['wp_query']->found_posts, 'skills-online' ), (int) $GLOBALS['wp_query']->found_posts ) ); ?></p>
				</div>
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
					<?php
					$so_i = 0;
					while ( have_posts() ) :
						the_post();
						if ( 1 === $so_paged && $so_featured && get_the_ID() === $so_featured->ID && 0 === $so_i ) {
							// the featured post is already rendered above; still show it in the grid
						}
						echo so_post_card( get_the_ID(), 0 === $so_i % 3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
						++$so_i;
					endwhile;
					?>
				</div>
				<nav class="so-pagination" aria-label="<?php esc_attr_e( 'Posts', 'skills-online' ); ?>">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'prev_text' => so_icon( 'chevron_right', 'text-base rotate-180' ),
								'next_text' => so_icon( 'chevron_right', 'text-base' ),
							)
						)
					);
					?>
				</nav>
			</div>
		</section>
	<?php else : ?>
		<section class="w-full px-margin-mobile md:px-margin py-space-xl">
			<div class="max-w-3xl mx-auto text-center space-y-space-md">
				<h2 class="font-headline-lg text-headline-lg text-on-surface font-extrabold"><?php esc_html_e( 'Nothing published under that topic yet', 'skills-online' ); ?></h2>
				<p class="font-body-lg text-body-lg text-on-surface-variant"><?php esc_html_e( 'Try a different topic, or browse everything in the knowledge base.', 'skills-online' ); ?></p>
				<a class="inline-flex items-center gap-space-xs bg-primary-container text-on-primary py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary transition-colors" href="<?php echo esc_url( home_url( '/blog-and-insights/' ) ); ?>"><?php esc_html_e( 'All articles', 'skills-online' ); ?></a>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// ------------------------------------------- topic hubs + residency CTA --
	if ( 1 === $so_paged ) {
		echo so_blog_fragment( 'hubs' );      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shipped design markup.
		echo so_blog_fragment( 'residency' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shipped design markup.
	}
	?>
</main>
<?php
get_footer();
