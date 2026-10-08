<?php
/**
 * Global footer — the Stitch design's five-column footer.
 *
 * The export renders the bottom-row items as plain <span>s (dead text) and
 * hard-codes "© 2025"; both are corrected here, and the outcomes disclosure is
 * linked so the figures shown on the marketing pages are labelled.
 *
 * @package SkillsOnline
 */
?>
<footer class="w-full bg-surface-container-low text-on-surface shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
	<div class="max-w-7xl mx-auto px-margin-mobile md:px-margin py-space-xl">
		<div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-space-lg mb-space-xl">
			<div class="lg:col-span-2 space-y-space-md">
				<a class="inline-flex items-center" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Skills<span class="text-primary-container">Online</span></span>
				</a>
				<p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm"><?php esc_html_e( 'Empowering modern learners and engineering enterprise workforce resilience through production-grade curriculums, 1-on-1 industry mentorship, and verified credentials.', 'skills-online' ); ?></p>
				<div class="flex flex-wrap items-center gap-space-sm pt-space-xs">
					<span class="font-label-caps text-label-caps text-on-tertiary-container bg-tertiary-container px-space-sm py-0.5 rounded-full"><?php esc_html_e( 'Accredited Curriculum', 'skills-online' ); ?></span>
					<span class="font-label-caps text-label-caps text-on-primary bg-primary-container px-space-sm py-0.5 rounded-full"><?php esc_html_e( 'Global Cohorts', 'skills-online' ); ?></span>
				</div>
			</div>

			<div>
				<h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md"><?php esc_html_e( 'Curriculum', 'skills-online' ); ?></h2>
				<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/software-engineering/' ) ); ?>"><?php esc_html_e( 'Software Engineering', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/cloud-and-devops/' ) ); ?>"><?php esc_html_e( 'Cloud &amp; DevOps', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/applied-ai-and-machine-learning/' ) ); ?>"><?php esc_html_e( 'Applied AI &amp; Machine Learning', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/tech-leadership/' ) ); ?>"><?php esc_html_e( 'Tech Leadership', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/courses-and-tracks/' ) ); ?>"><?php esc_html_e( 'All Tracks', 'skills-online' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md"><?php esc_html_e( 'Institute', 'skills-online' ); ?></h2>
				<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/pillars-and-outcomes/' ) ); ?>"><?php esc_html_e( 'Methodology &amp; Outcomes', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/enterprise/' ) ); ?>"><?php esc_html_e( 'Enterprise Training', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>"><?php esc_html_e( 'Alumni Stories', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/blog-and-insights/' ) ); ?>"><?php esc_html_e( 'Research &amp; Insights', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/results-and-disclosures/' ) ); ?>"><?php esc_html_e( 'Results &amp; Disclosures', 'skills-online' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md"><?php esc_html_e( 'Admissions', 'skills-online' ); ?></h2>
				<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Tuition &amp; Financing', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/pricing/#scholarships' ) ); ?>"><?php esc_html_e( 'Scholarship Fund', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/enterprise/' ) ); ?>"><?php esc_html_e( 'Corporate Sponsorship', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><?php esc_html_e( 'Student Portal', 'skills-online' ); ?></a></li>
					<li><a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Admissions', 'skills-online' ); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="pt-space-lg border-t border-outline-variant/60 flex flex-col sm:flex-row items-center justify-between gap-space-md text-body-sm font-body-sm text-on-surface-variant">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'Skills Online Education Institute. All rights reserved.', 'skills-online' ); ?></p>
			<div class="flex flex-wrap items-center justify-center gap-space-lg">
				<a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'skills-online' ); ?></a>
				<a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'skills-online' ); ?></a>
				<a class="hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/campus-safety/' ) ); ?>"><?php esc_html_e( 'Campus Safety', 'skills-online' ); ?></a>
			</div>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/video-modal' ); ?>
<?php wp_footer(); ?>
</body>
</html>
