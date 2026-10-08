<?php
/**
 * Class-preview modal.
 *
 * The design's hero has a "Watch 3-Min Class Preview" control that, in the
 * export, only calls an inline `alert()`. Rather than ship a decorative button —
 * or embed a video we do not have — it opens a real sample-lesson preview built
 * from the published curriculum, with a route to request the recorded session.
 *
 * @package SkillsOnline
 */
?>
<div id="so-preview-modal" class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true" aria-labelledby="so-preview-title" data-so-modal>
	<div class="absolute inset-0 bg-inverse-surface/60 backdrop-blur-sm" data-so-modal-close></div>
	<div class="relative h-full w-full overflow-y-auto p-margin-mobile md:p-margin flex items-start justify-center">
		<div class="w-full max-w-3xl bg-surface rounded-xl shadow-2xl overflow-hidden">
			<div class="flex items-center justify-between gap-space-md p-space-md border-b border-outline-variant">
				<div class="flex items-center gap-space-sm">
					<span class="inline-flex items-center rounded-full px-space-sm py-1 font-label-caps text-label-caps uppercase bg-primary/10 text-primary"><?php esc_html_e( 'Sample lesson', 'skills-online' ); ?></span>
					<span class="font-label-md text-label-md text-on-surface-variant"><?php esc_html_e( 'Module 2 — Retrieval that works', 'skills-online' ); ?></span>
				</div>
				<button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors" data-so-modal-close aria-label="<?php esc_attr_e( 'Close preview', 'skills-online' ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">close</span>
				</button>
			</div>
			<div class="p-space-lg space-y-space-md">
				<h2 id="so-preview-title" class="font-headline-lg text-headline-lg text-on-surface font-extrabold"><?php esc_html_e( 'What a class actually looks like', 'skills-online' ); ?></h2>
				<p class="font-body-md text-body-md text-on-surface-variant"><?php esc_html_e( 'A cohort session is not a lecture. You arrive with code that does not work yet, the mentor walks through the failure, and the session ends with a change pushed and reviewed. This is the shape of the retrieval module in the applied-AI track.', 'skills-online' ); ?></p>
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter">
					<div class="bg-surface-container-low rounded-xl p-space-md">
						<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-space-xs"><?php esc_html_e( 'Objectives', 'skills-online' ); ?></h3>
						<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
							<li><?php esc_html_e( 'Measure retrieval quality instead of guessing at it.', 'skills-online' ); ?></li>
							<li><?php esc_html_e( 'Explain when hybrid search beats pure vector search.', 'skills-online' ); ?></li>
							<li><?php esc_html_e( 'Tune an index for a latency budget you can defend.', 'skills-online' ); ?></li>
						</ul>
					</div>
					<div class="bg-surface-container-low rounded-xl p-space-md">
						<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-space-xs"><?php esc_html_e( 'Session format', 'skills-online' ); ?></h3>
						<ul class="space-y-space-xs font-body-sm text-body-sm text-on-surface-variant">
							<li><?php esc_html_e( '90 minutes live, recorded for the cohort.', 'skills-online' ); ?></li>
							<li><?php esc_html_e( 'One worked failure, not a happy path.', 'skills-online' ); ?></li>
							<li><?php esc_html_e( 'Ends with a reviewed pull request.', 'skills-online' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="rounded-xl bg-inverse-surface p-space-md overflow-x-auto so-scroll-x">
					<pre class="font-mono text-[13px] leading-relaxed text-[#dfe0ff] whitespace-pre"># the evaluation harness every module ends with
$ python -m eval.run --set queries.jsonl --k 10
recall@10  0.71  (baseline, vector only)
recall@10  0.86  (hybrid: keyword + vector, rrf fusion)
recall@10  0.89  (hybrid + cross-encoder re-rank, p95 +38ms)
> decision: ship hybrid + re-rank, budget 250ms, revisit at 10k qps</pre>
				</div>
				<p class="font-body-sm text-body-sm text-on-surface-variant"><?php esc_html_e( 'The recorded preview of a full session is available on request during an admissions call.', 'skills-online' ); ?></p>
				<div class="flex flex-wrap gap-space-md pt-space-xs">
					<a class="inline-flex items-center gap-2 bg-primary-container text-on-primary py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary transition-colors" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Request the recorded session', 'skills-online' ); ?></a>
					<a class="inline-flex items-center gap-2 bg-surface-container-lowest border border-outline-variant text-on-surface py-2.5 px-space-lg rounded-lg font-label-lg text-label-lg font-semibold hover:border-primary hover:text-primary transition-colors" href="<?php echo esc_url( home_url( '/courses-and-tracks/' ) ); ?>"><?php esc_html_e( 'See the curriculum', 'skills-online' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
