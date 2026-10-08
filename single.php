<?php
/**
 * Single article — the design's editorial layout.
 *
 * The Stitch export has no article screen, so this is composed from the design's
 * own vocabulary (eyebrow pill, headline scale, card and prose treatments) and
 * driven by the WordPress loop.
 *
 * @package SkillsOnline
 */

get_header();

while ( have_posts() ) :
	the_post();

	$so_cats   = get_the_category();
	$so_cat    = $so_cats ? $so_cats[0] : null;
	$so_byline = so_article_byline( get_the_ID() );
	$so_src    = json_decode( (string) get_post_meta( get_the_ID(), '_so_sources', true ), true );
	$so_thumb  = get_the_post_thumbnail_url( get_the_ID(), 'full' );
	?>
	<main id="so-main" class="w-full pt-[6.5rem] bg-surface min-h-screen">
		<section class="w-full bg-background pt-space-lg pb-space-md">
			<div class="max-w-7xl mx-auto px-margin-mobile md:px-margin">
				<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'skills-online' ); ?>" class="flex items-center gap-space-xs flex-wrap font-label-md text-label-md text-on-surface-variant">
					<a class="hover:text-primary transition-colors" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'skills-online' ); ?></a>
					<?php echo so_icon( 'chevron_right', 'text-[16px] text-outline' ); ?>
					<a class="hover:text-primary transition-colors" href="<?php echo esc_url( home_url( '/blog-and-insights/' ) ); ?>"><?php esc_html_e( 'Blog &amp; Insights', 'skills-online' ); ?></a>
					<?php echo so_icon( 'chevron_right', 'text-[16px] text-outline' ); ?>
					<span class="text-on-surface font-medium"><?php echo esc_html( wp_trim_words( get_the_title(), 8, '…' ) ); ?></span>
				</nav>
			</div>
		</section>

		<article class="w-full bg-background pb-space-xl">
			<div class="max-w-7xl mx-auto px-margin-mobile md:px-margin">
				<header class="max-w-3xl space-y-space-md">
					<?php if ( $so_cat ) : ?>
						<a class="inline-flex items-center rounded-full px-space-sm py-1 font-label-caps text-label-caps uppercase bg-primary/10 text-primary" href="<?php echo esc_url( get_category_link( $so_cat ) ); ?>"><?php echo esc_html( $so_cat->name ); ?></a>
					<?php endif; ?>
					<h1 class="font-display-xl text-display-xl text-on-surface tracking-tight font-extrabold leading-[1.1]"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="font-body-lg text-body-lg text-on-surface-variant"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<div class="flex flex-wrap items-center gap-space-md pt-space-xs">
						<div class="flex items-center gap-2">
							<?php echo $so_byline['avatar']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
							<div>
								<p class="font-label-md text-label-md text-on-surface font-semibold"><?php echo esc_html( $so_byline['name'] ); ?></p>
								<p class="text-[11px] text-on-surface-variant"><?php echo esc_html( $so_byline['role'] ); ?></p>
							</div>
						</div>
						<span class="w-px h-8 bg-outline-variant hidden sm:block"></span>
						<div class="font-label-md text-label-md text-on-surface-variant">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<span class="px-1.5">•</span><?php echo esc_html( so_read_time() ); ?>
						</div>
					</div>
				</header>

				<?php if ( $so_thumb ) : ?>
					<figure class="mt-space-lg">
						<img class="w-full h-64 md:h-96 object-cover rounded-xl" src="<?php echo esc_url( $so_thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" decoding="async">
					</figure>
				<?php endif; ?>

				<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter mt-space-lg">
					<div class="lg:col-span-8">
						<div class="so-prose" id="so-article-body">
							<?php the_content(); ?>
						</div>

						<?php if ( is_array( $so_src ) && $so_src ) : ?>
							<div class="so-prose">
								<div class="so-sources">
									<h2><?php esc_html_e( 'Sources', 'skills-online' ); ?></h2>
									<ul>
										<?php foreach ( $so_src as $so_source ) : ?>
											<li><a href="<?php echo esc_url( $so_source['url'] ); ?>" rel="noopener nofollow" target="_blank"><?php echo esc_html( $so_source['title'] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
									<p><?php esc_html_e( 'Links are provided for verification. We are not affiliated with the projects or vendors referenced.', 'skills-online' ); ?></p>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<aside class="lg:col-span-4 space-y-space-md lg:sticky lg:top-[7.5rem] lg:self-start">
						<div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-md">
							<span class="font-label-caps text-label-caps text-primary bg-primary/10 px-space-sm py-1 rounded-full"><?php esc_html_e( 'LEARN THIS PROPERLY', 'skills-online' ); ?></span>
							<h2 class="font-headline-sm text-headline-sm text-on-surface font-bold mt-space-sm mb-space-xs"><?php esc_html_e( 'The track behind this article', 'skills-online' ); ?></h2>
							<p class="font-body-sm text-body-sm text-on-surface-variant mb-space-md"><?php esc_html_e( 'Cohort-based, reviewed by working staff engineers, built around deployed systems rather than videos.', 'skills-online' ); ?></p>
							<a class="inline-flex items-center justify-center gap-space-xs bg-primary-container text-on-primary py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary transition-colors w-full" href="<?php echo esc_url( home_url( '/courses-and-tracks/' ) ); ?>"><?php esc_html_e( 'Browse the tracks', 'skills-online' ); ?><?php echo so_icon( 'arrow_forward', 'text-lg' ); ?></a>
							<a class="inline-flex items-center justify-center gap-space-xs bg-surface-container-lowest border border-outline-variant text-on-surface py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:border-primary hover:text-primary transition-colors w-full mt-space-sm" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See tuition', 'skills-online' ); ?></a>
						</div>
						<div class="bg-surface-container-low rounded-xl p-space-lg">
							<h2 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-space-sm"><?php esc_html_e( 'More in this topic', 'skills-online' ); ?></h2>
							<ul class="space-y-space-sm">
								<?php
								$so_related = new WP_Query(
									array(
										'post__not_in'        => array( get_the_ID() ),
										'posts_per_page'      => 4,
										'ignore_sticky_posts' => true,
										'category__in'        => $so_cat ? array( $so_cat->term_id ) : array(),
									)
								);
								while ( $so_related->have_posts() ) :
									$so_related->the_post();
									?>
									<li><a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
									<?php
								endwhile;
								wp_reset_postdata();
								?>
							</ul>
						</div>
					</aside>
				</div>
			</div>
		</article>

		<?php
		$so_cta = do_shortcode( '[so_cta]' );
		echo $so_cta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered by the theme shortcode.
		?>
	</main>
	<?php
endwhile;

get_footer();
