<?php
/**
 * Global header — the Stitch design's fixed announcement bar + navigation bar.
 *
 * Two deviations from the export, both deliberate:
 *  - the "ENDS IN 48H" pill shows a live countdown to the real cohort deadline
 *    (option `so_cohort_deadline`), because a fixed urgency label that resets on
 *    every page load is a fabricated claim;
 *  - a mobile navigation panel is added, because the export's navigation is
 *    `hidden xl:flex` and offers a phone visitor nothing at all.
 *
 * @package SkillsOnline
 */

$so_nav = so_primary_nav();
$so_deadline = so_cohort_deadline();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#fbf8ff">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php // The two display faces are preloaded so the design's type never swaps late. ?>
	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() . '/assets/fonts/plus-jakarta-sans-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() . '/assets/fonts/inter-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="so-skip so-visually-hidden" href="#so-main"><?php esc_html_e( 'Skip to content', 'skills-online' ); ?></a>

<header class="fixed top-0 w-full z-50">
	<?php // ---------------------------------------------------- announcement bar -- ?>
	<div class="bg-surface-container-high text-on-surface px-margin-mobile md:px-margin shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
		<div class="max-w-7xl mx-auto h-10 flex items-center justify-between text-body-sm">
			<div class="flex items-center gap-space-sm overflow-hidden text-ellipsis whitespace-nowrap">
				<span class="flex items-center gap-space-xs text-on-secondary-container bg-secondary-fixed px-space-sm py-0.5 rounded-full font-label-caps text-label-caps shrink-0"
					data-so-countdown="<?php echo esc_attr( $so_deadline ); ?>">
					<span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse shrink-0"></span>
					<span class="so-countdown-label"><?php echo esc_html( so_cohort_countdown_text( $so_deadline ) ); ?></span>
				</span>
				<span class="font-body-sm text-body-sm font-medium text-on-surface truncate">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: promo code. */
							__( '🚀 %1$s Enrollment Open: Save 30%% on tech &amp; leadership tracks plus 1-on-1 career mentorship. Use code %2$s', 'skills-online' ),
							esc_html( so_cohort_label() ),
							'<span class="font-label-md text-label-md font-bold text-primary tracking-wide">SKILLS30</span>'
						),
						array( 'span' => array( 'class' => array() ) )
					);
					?>
				</span>
			</div>
			<div class="hidden sm:flex items-center gap-space-md shrink-0">
				<a class="font-label-md text-label-md text-primary hover:text-on-primary-fixed-variant transition-colors flex items-center gap-space-xs font-semibold"
					href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Claim Offer', 'skills-online' ); ?>
					<span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
				</a>
			</div>
		</div>
	</div>

	<?php // --------------------------------------------------------- navigation -- ?>
	<div class="bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
		<div class="max-w-7xl mx-auto h-16 px-margin-mobile md:px-margin flex items-center justify-between gap-space-md">
			<div class="flex items-center gap-space-lg">
				<a class="flex items-center gap-space-sm" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Skills Online — home', 'skills-online' ); ?>">
					<span class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Skills<span class="text-primary-container">Online</span></span>
				</a>
				<nav class="hidden xl:flex items-center gap-space-md" aria-label="<?php esc_attr_e( 'Primary', 'skills-online' ); ?>">
					<?php foreach ( $so_nav as $so_item ) : ?>
						<a class="font-label-lg text-label-lg transition-colors py-space-xs <?php echo $so_item['current'] ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface'; ?>"
							href="<?php echo esc_url( $so_item['url'] ); ?>"<?php echo $so_item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $so_item['label'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
			<div class="flex items-center gap-space-md">
				<a class="hidden sm:inline-flex font-label-lg text-label-lg text-on-surface-variant hover:text-on-surface transition-colors px-space-md py-space-xs"
					href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><?php esc_html_e( 'Sign In', 'skills-online' ); ?></a>
				<a class="hidden sm:inline-flex items-center justify-center bg-primary-container text-on-primary font-label-lg text-label-lg px-space-lg py-space-sm rounded-lg shadow-[0_1px_8px_rgba(0,0,0,0.04)] hover:bg-primary hover:text-on-primary transition-all duration-200"
					href="<?php echo esc_url( home_url( '/courses-and-tracks/' ) ); ?>"><?php esc_html_e( 'Explore Courses', 'skills-online' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a class="flex items-center pl-space-xs" href="<?php echo esc_url( so_portal_url() ); ?>" aria-label="<?php esc_attr_e( 'Your learning portal', 'skills-online' ); ?>">
						<?php echo get_avatar( get_current_user_id(), 32, '', '', array( 'class' => 'w-8 h-8 rounded-full object-cover' ) ); ?>
					</a>
				<?php else : ?>
					<a class="flex items-center pl-space-xs" href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>" aria-label="<?php esc_attr_e( 'Sign in to your learning portal', 'skills-online' ); ?>">
						<span class="w-8 h-8 rounded-full bg-surface-container-high border border-outline-variant/60 flex items-center justify-center text-on-surface-variant">
							<span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
						</span>
					</a>
				<?php endif; ?>
				<button type="button" class="xl:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg bg-surface-container-high text-on-surface hover:bg-surface-container-highest transition-colors"
					data-so-menu-open aria-controls="so-mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open navigation menu', 'skills-online' ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">menu</span>
				</button>
			</div>
		</div>
	</div>
</header>

<?php // ----------------------------------------------------- mobile navigation -- ?>
<div id="so-mobile-menu" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site navigation', 'skills-online' ); ?>" data-so-menu-panel>
	<div class="absolute inset-0 bg-inverse-surface/50 backdrop-blur-sm" data-so-menu-close></div>
	<div class="relative h-full w-full max-w-sm ml-auto bg-surface border-l border-outline-variant flex flex-col">
		<div class="flex items-center justify-between h-16 px-margin shrink-0 border-b border-outline-variant">
			<span class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Skills<span class="text-primary-container">Online</span></span>
			<button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors"
				data-so-menu-close aria-label="<?php esc_attr_e( 'Close navigation menu', 'skills-online' ); ?>">
				<span class="material-symbols-outlined" aria-hidden="true">close</span>
			</button>
		</div>
		<nav class="flex-1 overflow-y-auto px-margin py-space-lg flex flex-col gap-space-xs" aria-label="<?php esc_attr_e( 'Mobile', 'skills-online' ); ?>">
			<?php foreach ( $so_nav as $so_item ) : ?>
				<a class="py-3 px-3 rounded-lg font-headline-sm text-headline-sm transition-colors <?php echo $so_item['current'] ? 'text-primary bg-surface-container-high' : 'text-on-surface hover:bg-surface-container-high'; ?>"
					href="<?php echo esc_url( $so_item['url'] ); ?>"<?php echo $so_item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $so_item['label'] ); ?></a>
			<?php endforeach; ?>
			<a class="py-3 px-3 rounded-lg font-headline-sm text-headline-sm text-on-surface hover:bg-surface-container-high transition-colors" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'skills-online' ); ?></a>
			<a class="py-3 px-3 rounded-lg font-headline-sm text-headline-sm text-on-surface hover:bg-surface-container-high transition-colors" href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><?php esc_html_e( 'Sign In', 'skills-online' ); ?></a>
		</nav>
		<div class="p-margin flex flex-col gap-space-sm shrink-0 border-t border-outline-variant">
			<a class="w-full inline-flex items-center justify-center py-3.5 rounded-lg bg-primary-container text-on-primary font-label-lg text-label-lg hover:bg-primary transition-all"
				href="<?php echo esc_url( home_url( '/courses-and-tracks/' ) ); ?>"><?php esc_html_e( 'Explore Courses', 'skills-online' ); ?></a>
			<p class="font-body-sm text-body-sm text-on-surface-variant text-center"><?php esc_html_e( 'Applications reviewed by staff engineers.', 'skills-online' ); ?></p>
		</div>
	</div>
</div>
