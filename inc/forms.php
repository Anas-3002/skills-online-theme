<?php
/**
 * Native, dependency-free forms: design-styled markup, nonce-checked handler,
 * server-side validation, spam honeypot, an audit log (private CPT) and wp_mail.
 *
 * Keeping this in the theme avoids a form plugin and keeps the design's markup
 * exact — Elementor Free has no form widget.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form definitions.
 *
 * @return array<string,array{title:string,submit:string,note:string,fields:array}>
 */
function so_form_definitions() {
	return array(
		'apply'      => array(
			'title'  => __( 'Apply for the next cohort', 'skills-online' ),
			'note'   => __( 'A senior engineer reads every application. Expect a reply within five business days.', 'skills-online' ),
			'submit' => __( 'Submit application', 'skills-online' ),
			'fields' => array(
				array( 'name' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true ),
				array( 'name' => 'country', 'label' => 'Country / timezone', 'type' => 'text', 'required' => true ),
				array( 'name' => 'experience', 'label' => 'Years of professional software experience', 'type' => 'select', 'required' => true, 'options' => array( 'Less than 1 year', '1–3 years', '3–6 years', '6–10 years', '10+ years' ) ),
				array( 'name' => 'track', 'label' => 'Track of interest', 'type' => 'select', 'required' => true, 'options' => array( 'Software Engineering', 'Cloud & DevOps', 'Applied AI & Machine Learning', 'Tech Leadership', 'Not sure yet — advise me' ) ),
				array( 'name' => 'goal', 'label' => 'What do you want to be able to do in twelve months?', 'type' => 'textarea', 'required' => true ),
				array( 'name' => 'proof', 'label' => 'Link to something you built (repository, article, product)', 'type' => 'url', 'required' => false ),
			),
		),
		'contact'    => array(
			'title'  => __( 'Send us a message', 'skills-online' ),
			'note'   => __( 'Admissions answers within one business day; enterprise proposals within two.', 'skills-online' ),
			'submit' => __( 'Send message', 'skills-online' ),
			'fields' => array(
				array( 'name' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true ),
				array( 'name' => 'topic', 'label' => 'What is this about?', 'type' => 'select', 'required' => true, 'options' => array( 'Curriculum or track question', 'Cohort dates and availability', 'Tuition, financing or scholarships', 'Enterprise / private cohorts', 'Press or partnership', 'Safety report', 'Something else' ) ),
				array( 'name' => 'role', 'label' => 'Current role (optional)', 'type' => 'text', 'required' => false ),
				array( 'name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true ),
			),
		),
		'enterprise' => array(
			'title'  => __( 'Book a programme call', 'skills-online' ),
			'note'   => __( 'Tell us the capability gap and the timeline. A programme lead replies with a proposed outline.', 'skills-online' ),
			'submit' => __( 'Request a programme call', 'skills-online' ),
			'fields' => array(
				array( 'name' => 'full_name', 'label' => 'Your name', 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => 'Work email', 'type' => 'email', 'required' => true ),
				array( 'name' => 'company', 'label' => 'Company', 'type' => 'text', 'required' => true ),
				array( 'name' => 'team_size', 'label' => 'Engineers to train', 'type' => 'select', 'required' => true, 'options' => array( 'Fewer than 12', '12–25', '26–40', 'More than 40' ) ),
				array( 'name' => 'focus', 'label' => 'Primary focus', 'type' => 'select', 'required' => true, 'options' => array( 'Cloud migration / platform engineering', 'Applied AI & ML capability', 'Backend & distributed systems depth', 'Engineering leadership development', 'Mixed / not sure yet' ) ),
				array( 'name' => 'message', 'label' => 'Context (current stack, deadlines, constraints)', 'type' => 'textarea', 'required' => false ),
			),
		),
		'enroll'     => array(
			'title'  => __( 'Get the syllabus and sandbox preview', 'skills-online' ),
			'note'   => __( 'One email: the curriculum outline for the track you pick, plus access to the sample sandbox.', 'skills-online' ),
			'submit' => __( 'Claim 30% off', 'skills-online' ),
			'fields' => array(
				array( 'name' => 'email', 'label' => 'Work or personal email', 'type' => 'email', 'required' => true ),
				array( 'name' => 'track', 'label' => 'Track', 'type' => 'select', 'required' => false, 'options' => array( 'Software Engineering', 'Cloud & DevOps', 'Applied AI & Machine Learning', 'Tech Leadership', 'Not sure yet' ) ),
			),
		),
		'newsletter' => array(
			'title'  => __( 'The weekly teardown', 'skills-online' ),
			'note'   => __( 'One email each Tuesday: a production post-mortem, a salary-metric note and the week’s new playbook. Unsubscribe in one click.', 'skills-online' ),
			'submit' => __( 'Subscribe', 'skills-online' ),
			'fields' => array(
				array( 'name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true ),
				array( 'name' => 'role', 'label' => 'Your discipline (optional)', 'type' => 'text', 'required' => false ),
			),
		),
	);
}

/**
 * Register the private submissions store.
 */
function so_register_submission_cpt() {
	register_post_type(
		'so_submission',
		array(
			'labels'          => array(
				'name'          => __( 'Form submissions', 'skills-online' ),
				'singular_name' => __( 'Form submission', 'skills-online' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-email-alt',
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor' ),
		)
	);
}
add_action( 'init', 'so_register_submission_cpt' );

/**
 * Render a design-styled form.
 *
 * @param string $type Form key.
 * @return string
 */
function so_form( $type ) {
	$defs = so_form_definitions();
	if ( ! isset( $defs[ $type ] ) ) {
		return '';
	}
	$def   = $defs[ $type ];
	$state = isset( $_GET['so_form'] ) ? sanitize_key( wp_unslash( $_GET['so_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$compact = in_array( $type, array( 'enroll', 'newsletter' ), true );

	if ( $compact ) {
		return so_compact_form( $type, $def, $state );
	}

	$out  = '<div class="so-form-shell" id="so-form">';
	$out .= '<div class="mb-space-md"><h2 class="font-headline-md text-headline-md text-on-surface font-bold">' . esc_html( $def['title'] ) . '</h2>';
	$out .= '<p class="font-body-md text-body-md text-on-surface-variant mt-1">' . esc_html( $def['note'] ) . '</p></div>';

	if ( 'ok' === $state ) {
		$out .= '<div class="p-space-md rounded-lg bg-tertiary-fixed/30 border border-tertiary-container/30 flex items-start gap-space-sm mb-space-md" role="status">'
			. so_icon( 'check_circle', 'text-tertiary-container text-xl' )
			. '<div><span class="font-label-lg text-label-lg text-on-surface font-bold block">' . esc_html__( 'Received.', 'skills-online' ) . '</span>'
			. '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html__( 'Check your inbox for a confirmation. If you do not hear from us within one business day, reply to that email and it reaches a human.', 'skills-online' ) . '</p></div></div>';
	} elseif ( 'error' === $state ) {
		$out .= '<div class="p-space-md rounded-lg bg-error-container/40 border border-error/30 flex items-start gap-space-sm mb-space-md" role="alert">'
			. so_icon( 'error', 'text-error text-xl' )
			. '<div><span class="font-label-lg text-label-lg text-on-surface font-bold block">' . esc_html__( 'That did not send.', 'skills-online' ) . '</span>'
			. '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html__( 'Please complete every required field with a valid email address and try again.', 'skills-online' ) . '</p></div></div>';
	}

	$out .= '<form class="so-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate>';
	$out .= '<input type="hidden" name="action" value="so_form">';
	$out .= '<input type="hidden" name="so_type" value="' . esc_attr( $type ) . '">';
	$out .= wp_nonce_field( 'so_form_' . $type, 'so_nonce', true, false );
	$out .= '<div class="hidden" aria-hidden="true"><label>Leave this field empty<input type="text" name="so_hp" value="" tabindex="-1" autocomplete="off"></label></div>';
	$out .= '<input type="hidden" name="so_ts" value="' . esc_attr( time() ) . '">';

	foreach ( $def['fields'] as $f ) {
		$id   = 'so-' . $type . '-' . $f['name'];
		$req  = ! empty( $f['required'] );
		$full = in_array( $f['type'], array( 'textarea' ), true ) ? ' sm:col-span-2' : '';
		$out .= '<div class="flex flex-col gap-1.5' . $full . '">';
		$out .= '<label class="font-label-caps text-label-caps uppercase tracking-wider text-on-surface-variant" for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . ( $req ? ' <span class="text-primary">*</span>' : ' <span class="text-outline">(' . esc_html__( 'optional', 'skills-online' ) . ')</span>' ) . '</label>';

		if ( 'textarea' === $f['type'] ) {
			$out .= '<textarea class="so-input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '" rows="5"' . ( $req ? ' required' : '' ) . '></textarea>';
		} elseif ( 'select' === $f['type'] ) {
			$out .= '<select class="so-input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '"' . ( $req ? ' required' : '' ) . '>';
			$out .= '<option value="">' . esc_html__( 'Select…', 'skills-online' ) . '</option>';
			foreach ( $f['options'] as $opt ) {
				$out .= '<option value="' . esc_attr( $opt ) . '">' . esc_html( $opt ) . '</option>';
			}
			$out .= '</select>';
		} else {
			$out .= '<input class="so-input" type="' . esc_attr( $f['type'] ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '"' . ( $req ? ' required' : '' ) . ' autocomplete="' . esc_attr( 'email' === $f['name'] ? 'email' : ( 'full_name' === $f['name'] ? 'name' : ( 'company' === $f['name'] ? 'organization' : 'on' ) ) ) . '">';
		}
		$out .= '</div>';
	}

	$out .= '<div class="flex flex-col sm:flex-row items-center gap-space-md pt-space-xs sm:col-span-2">';
	$out .= '<button class="so-submit w-full sm:w-auto inline-flex items-center justify-center gap-space-sm px-space-lg py-3.5 rounded-lg bg-primary-container text-on-primary font-label-lg text-label-lg font-semibold hover:bg-primary transition-colors" type="submit">'
		. '<span class="so-submit-label" data-busy="' . esc_attr__( 'Sending…', 'skills-online' ) . '">' . esc_html( $def['submit'] ) . '</span>'
		. so_icon( 'arrow_forward', 'text-lg' )
		. '</button>';
	$out .= '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html__( 'We reply from a monitored inbox. Your details are never sold or shared.', 'skills-online' ) . '</p>';
	$out .= '</div>';
	$out .= '</form></div>';
	return $out;
}

/**
 * The design's inline capture forms (hero/enrollment band, newsletter strip).
 *
 * @param string $type   Form key.
 * @param array  $def    Definition.
 * @param string $state  ok|error|''.
 * @return string
 */
function so_compact_form( $type, $def, $state ) {
	$out = '';
	if ( 'ok' === $state ) {
		$out .= '<p class="p-space-sm rounded-lg bg-tertiary-fixed/40 text-on-surface font-body-sm text-body-sm" role="status">'
			. esc_html__( 'Received — check your inbox for the syllabus link.', 'skills-online' ) . '</p>';
	} elseif ( 'error' === $state ) {
		$out .= '<p class="p-space-sm rounded-lg bg-error-container/50 text-on-surface font-body-sm text-body-sm" role="alert">'
			. esc_html__( 'That email did not look right. Please try again.', 'skills-online' ) . '</p>';
	}
	$out .= '<form class="so-form flex flex-col sm:flex-row gap-space-sm" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate>';
	$out .= '<input type="hidden" name="action" value="so_form">';
	$out .= '<input type="hidden" name="so_type" value="' . esc_attr( $type ) . '">';
	$out .= wp_nonce_field( 'so_form_' . $type, 'so_nonce', true, false );
	$out .= '<input type="hidden" name="so_ts" value="' . esc_attr( time() ) . '">';
	$out .= '<div class="hidden" aria-hidden="true"><label>Leave this field empty<input type="text" name="so_hp" value="" tabindex="-1" autocomplete="off"></label></div>';
	$out .= '<label class="so-visually-hidden" for="so-' . esc_attr( $type ) . '-email">' . esc_html__( 'Email address', 'skills-online' ) . '</label>';
	$out .= '<input class="so-input flex-1" id="so-' . esc_attr( $type ) . '-email" name="email" type="email" required placeholder="'
		. esc_attr__( 'Enter your work or personal email', 'skills-online' ) . '" autocomplete="email">';
	$out .= '<button class="so-submit bg-primary-container text-on-primary px-space-lg py-3 rounded-lg font-label-lg text-label-lg font-bold hover:bg-primary transition-all duration-200 shrink-0" type="submit">'
		. '<span class="so-submit-label" data-busy="' . esc_attr__( 'Sending…', 'skills-online' ) . '">' . esc_html( $def['submit'] ) . '</span></button>';
	$out .= '</form>';
	return $out;
}

/**
 * Handle submissions.
 */
function so_handle_form() {
	$type = isset( $_POST['so_type'] ) ? sanitize_key( wp_unslash( $_POST['so_type'] ) ) : '';
	$defs = so_form_definitions();
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'so_form' ), $back );

	if ( ! isset( $defs[ $type ] ) ) {
		wp_safe_redirect( add_query_arg( 'so_form', 'error', $back ) );
		exit;
	}
	if ( ! isset( $_POST['so_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['so_nonce'] ) ), 'so_form_' . $type ) ) {
		wp_safe_redirect( add_query_arg( 'so_form', 'error', $back ) );
		exit;
	}
	// Honeypot + time trap.
	$hp = isset( $_POST['so_hp'] ) ? trim( (string) wp_unslash( $_POST['so_hp'] ) ) : '';
	$ts = isset( $_POST['so_ts'] ) ? (int) $_POST['so_ts'] : 0;
	if ( '' !== $hp || ( $ts && ( time() - $ts ) < 2 ) ) {
		wp_safe_redirect( add_query_arg( 'so_form', 'ok', $back ) . '#so-form' );
		exit;
	}

	$clean = array();
	foreach ( $defs[ $type ]['fields'] as $f ) {
		$name = $f['name'];
		$val  = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : '';
		$val  = is_string( $val ) ? trim( sanitize_textarea_field( $val ) ) : '';
		if ( ! empty( $f['required'] ) && '' === $val ) {
			wp_safe_redirect( add_query_arg( 'so_form', 'error', $back ) );
			exit;
		}
		if ( 'email' === $f['type'] && '' !== $val && ! is_email( $val ) ) {
			wp_safe_redirect( add_query_arg( 'so_form', 'error', $back ) );
			exit;
		}
		if ( 'url' === $f['type'] && '' !== $val && ! wp_http_validate_url( $val ) ) {
			$val = '';
		}
		if ( 'select' === $f['type'] && '' !== $val && ! in_array( $val, $f['options'], true ) ) {
			$val = '';
		}
		$clean[ $name ] = $val;
	}

	$who   = isset( $clean['full_name'] ) && '' !== $clean['full_name'] ? $clean['full_name'] : ( isset( $clean['email'] ) ? $clean['email'] : __( 'Unknown', 'skills-online' ) );
	$title = sprintf( '%s — %s', $defs[ $type ]['title'], $who );
	$body  = '';
	foreach ( $clean as $k => $v ) {
		$body .= ucwords( str_replace( '_', ' ', $k ) ) . ': ' . ( '' === $v ? '—' : $v ) . "\n";
	}
	$body .= "\n---\nSubmitted: " . gmdate( 'c' ) . "\nSource: " . esc_url_raw( $back ) . "\nIP: " . so_client_ip() . "\n";

	$id = wp_insert_post(
		array(
			'post_type'    => 'so_submission',
			'post_status'  => 'private',
			'post_title'   => $title,
			'post_content' => $body,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		wp_safe_redirect( add_query_arg( 'so_form', 'error', $back ) );
		exit;
	}
	foreach ( $clean as $k => $v ) {
		update_post_meta( $id, '_so_' . $k, $v );
	}
	update_post_meta( $id, '_so_form_type', $type );

	$to      = so_notify_email();
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( ! empty( $clean['email'] ) ) {
		$headers[] = 'Reply-To: ' . $clean['email'];
	}
	$sent = wp_mail( $to, sprintf( '[Skills Online] %s', $title ), $body, $headers );
	update_post_meta( $id, '_so_mail', $sent ? 'sent' : 'failed' );

	/**
	 * Fires after a submission is stored.
	 *
	 * @param int    $id    Submission post id.
	 * @param string $type  Form key.
	 * @param array  $clean Sanitised values.
	 */
	do_action( 'so_form_submitted', $id, $type, $clean );

	wp_safe_redirect( add_query_arg( 'so_form', 'ok', $back ) . '#so-form' );
	exit;
}
add_action( 'admin_post_so_form', 'so_handle_form' );
add_action( 'admin_post_nopriv_so_form', 'so_handle_form' );

/**
 * Where submissions are emailed.
 *
 * @return string
 */
function so_notify_email() {
	$to = apply_filters( 'so_notify_email', get_option( 'admin_email' ) );
	return is_email( $to ) ? $to : get_option( 'admin_email' );
}

/**
 * Best-effort client IP for the audit log.
 *
 * @return string
 */
function so_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
			$ip = trim( $ip[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return 'unknown';
}

/**
 * Show the stored fields in the admin list.
 *
 * @param array $columns Columns.
 * @return array
 */
function so_submission_columns( $columns ) {
	return array(
		'cb'      => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'   => __( 'Submission', 'skills-online' ),
		'so_type' => __( 'Form', 'skills-online' ),
		'so_email' => __( 'Email', 'skills-online' ),
		'date'    => __( 'Received', 'skills-online' ),
	);
}
add_filter( 'manage_so_submission_posts_columns', 'so_submission_columns' );

/**
 * Render the extra columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post id.
 */
function so_submission_column_content( $column, $post_id ) {
	if ( 'so_type' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_so_form_type', true ) );
	}
	if ( 'so_email' === $column ) {
		$email = (string) get_post_meta( $post_id, '_so_email', true );
		echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '&mdash;';
	}
}
add_action( 'manage_so_submission_posts_custom_column', 'so_submission_column_content', 10, 2 );
