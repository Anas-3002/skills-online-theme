<?php
/**
 * Shortcodes the theme's own templates use to place design sections.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The design's dark enrollment band, for use inside templates.
 *
 * @return string
 */
function so_cta_shortcode() {
	return '<section class="w-full bg-inverse-surface py-space-xl text-inverse-on-surface relative overflow-hidden">'
		. '<div class="absolute -top-24 right-10 w-96 h-96 rounded-full bg-primary-container/20 blur-3xl pointer-events-none"></div>'
		. '<div class="absolute bottom-0 -left-10 w-80 h-80 rounded-full bg-secondary-container/20 blur-3xl pointer-events-none"></div>'
		. '<div class="max-w-5xl mx-auto px-margin-mobile md:px-margin relative z-10 space-y-space-lg text-center">'
		. '<div class="space-y-space-xs max-w-3xl mx-auto">'
		. '<span class="font-label-caps text-label-caps text-secondary-fixed bg-secondary-fixed/20 px-space-sm py-1 rounded-full uppercase tracking-wider font-bold">'
		. esc_html__( 'NEXT COHORT — ENROLLMENT OPEN', 'skills-online' ) . '</span>'
		. '<h2 class="font-display-xl text-display-xl text-surface font-extrabold tracking-tight">'
		. esc_html__( 'Ready to elevate your career? Your next chapter starts Monday.', 'skills-online' ) . '</h2>'
		. '<p class="font-body-lg text-body-lg text-surface-variant">'
		. sprintf(
			/* translators: %s: promo code. */
			esc_html__( 'Enrol with code %s for 30%% off tech tracks plus personalised 1-on-1 onboarding.', 'skills-online' ),
			'<strong class="text-secondary-fixed">SKILLS30</strong>'
		) . '</p></div>'
		. '<div class="max-w-xl mx-auto bg-surface-container-lowest rounded-xl p-space-md shadow-2xl">'
		. '<!--SO_FORM:enroll-->'
		. '</div>'
		. '<div class="flex flex-wrap items-center justify-center gap-space-lg pt-space-xs text-surface-variant text-label-md font-label-md">'
		. '<span class="flex items-center gap-1">' . so_icon( 'check_circle', 'text-tertiary-fixed text-base' ) . esc_html__( '14-day refund window', 'skills-online' ) . '</span>'
		. '<span class="flex items-center gap-1">' . so_icon( 'check_circle', 'text-tertiary-fixed text-base' ) . esc_html__( 'Accredited diplomas', 'skills-online' ) . '</span>'
		. '<span class="flex items-center gap-1">' . so_icon( 'check_circle', 'text-tertiary-fixed text-base' ) . esc_html__( 'Interest-free financing', 'skills-online' ) . '</span>'
		. '</div></div></section>';
}
add_shortcode( 'so_cta', 'so_cta_shortcode' );

/**
 * Editorial standards block.
 *
 * @return string
 */
function so_standards_shortcode() {
	return so_editorial_standards();
}
add_shortcode( 'so_editorial_standards', 'so_standards_shortcode' );

/**
 * A form placed inside content, for pages that need a second form.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function so_form_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'type' => 'contact' ), $atts, 'so_form' );
	return so_form( sanitize_key( $atts['type'] ) );
}
add_shortcode( 'so_form', 'so_form_shortcode' );
