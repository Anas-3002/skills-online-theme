<?php
/**
 * 404 — same shell and design language as every other page, with real routes out.
 *
 * @package SkillsOnline
 */

get_header();
?>
<main id="so-main" class="w-full pt-[6.5rem] bg-surface min-h-screen">
	<section class="w-full bg-background py-space-xl">
		<div class="max-w-3xl mx-auto px-margin-mobile md:px-margin text-center space-y-space-lg">
			<span class="font-label-caps text-label-caps text-primary bg-primary/10 px-space-sm py-1 rounded-full"><?php esc_html_e( 'ERROR 404', 'skills-online' ); ?></span>
			<h1 class="font-display-xl text-display-xl text-on-surface tracking-tight font-extrabold"><?php esc_html_e( 'That page does not exist', 'skills-online' ); ?></h1>
			<p class="font-body-lg text-body-lg text-on-surface-variant"><?php esc_html_e( 'The link may be out of date, or the page may have moved. Everything below is a real destination.', 'skills-online' ); ?></p>
			<div class="flex flex-wrap items-center justify-center gap-space-md">
				<a class="inline-flex items-center gap-2 bg-primary-container text-on-primary py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary transition-colors" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'skills-online' ); ?></a>
				<a class="inline-flex items-center gap-2 bg-surface-container-lowest border border-outline-variant text-on-surface py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:border-primary hover:text-primary transition-colors" href="<?php echo esc_url( home_url( '/courses-and-tracks/' ) ); ?>"><?php esc_html_e( 'Browse the tracks', 'skills-online' ); ?></a>
			</div>
			<form class="max-w-md mx-auto flex gap-space-sm" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="so-visually-hidden" for="so-404-search"><?php esc_html_e( 'Search the site', 'skills-online' ); ?></label>
				<input class="so-input" id="so-404-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search articles and pages…', 'skills-online' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
				<button class="inline-flex items-center gap-2 bg-primary-container text-on-primary py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary transition-colors shrink-0" type="submit"><?php esc_html_e( 'Search', 'skills-online' ); ?></button>
			</form>
			<div class="grid grid-cols-1 sm:grid-cols-3 gap-gutter pt-space-md text-left">
				<div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
					<h2 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-space-xs"><?php esc_html_e( 'Curriculum', 'skills-online' ); ?></h2>
					<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/software-engineering/' ) ); ?>"><?php esc_html_e( 'Software Engineering', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/cloud-and-devops/' ) ); ?>"><?php esc_html_e( 'Cloud &amp; DevOps', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/applied-ai-and-machine-learning/' ) ); ?>"><?php esc_html_e( 'Applied AI &amp; ML', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/tech-leadership/' ) ); ?>"><?php esc_html_e( 'Tech Leadership', 'skills-online' ); ?></a></li>
					</ul>
				</div>
				<div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
					<h2 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-space-xs"><?php esc_html_e( 'Admissions', 'skills-online' ); ?></h2>
					<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Tuition &amp; financing', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/apply/' ) ); ?>"><?php esc_html_e( 'Apply', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact admissions', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><?php esc_html_e( 'Student portal', 'skills-online' ); ?></a></li>
					</ul>
				</div>
				<div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
					<h2 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-space-xs"><?php esc_html_e( 'Institute', 'skills-online' ); ?></h2>
					<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/pillars-and-outcomes/' ) ); ?>"><?php esc_html_e( 'Methodology &amp; outcomes', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>"><?php esc_html_e( 'Alumni stories', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/blog-and-insights/' ) ); ?>"><?php esc_html_e( 'Research &amp; insights', 'skills-online' ); ?></a></li>
						<li><a class="hover:text-primary" href="<?php echo esc_url( home_url( '/results-and-disclosures/' ) ); ?>"><?php esc_html_e( 'Results &amp; disclosures', 'skills-online' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
