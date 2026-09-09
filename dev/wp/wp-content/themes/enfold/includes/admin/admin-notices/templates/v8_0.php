<?php
/**
 * Contains the Welcome Notice for 8.0 including the Omnalingo beta campaign
 *
 * @since 8.0
 */
if (!defined('ABSPATH')) {
	exit;
}    // Exit if accessed directly

/**
 * Campaign link - the parameters allow to track the signups of this notice on the landing page
 */
$beta_url = 'https://omnalingo.com/omnalingo-and-enfold?utm_source=enfold&utm_medium=admin-notice&utm_campaign=beta-8-0';

?>
<style>
	/* ============================================================
   Omnalingo beta banner - gradient card, two columns

   Gradient and animation are the ones used on omnalingo.com:
   --om-gradient-brand + .om-gradient--animate (om-gradient-shift 20s ease infinite)

   All rules are prefixed with #wpwrap: WP core styles notice content with
   ".notice p" and "div.notice a" (0,1,1), which beats a plain class selector.
   ============================================================ */
	#wpwrap .av-omna-banner-scope {
		--av-omna-info: #2271b1;
		--av-omna-brand: #673ab7;
		--av-omna-error: #d63638;
		--av-omna-warning: #dba617;
		--av-omna-serif: Georgia, "Times New Roman", serif;
		--av-omna-sans: -apple-system, "Segoe UI", "Helvetica Neue", Arial, sans-serif;
		--av-omna-radius: 16px;
		--av-omna-gradient: linear-gradient(135deg, var(--av-omna-info), var(--av-omna-brand), var(--av-omna-error), var(--av-omna-warning));
		--av-omna-on-dark: rgba(255, 255, 255, .88);
		--av-omna-on-dark-soft: rgba(255, 255, 255, .72);
		--av-omna-hairline: rgba(255, 255, 255, .24);
		font-family: var(--av-omna-sans);
		box-sizing: border-box;
		margin: 18px 0 6px;
	}

	#wpwrap .av-omna-banner-scope *,
	#wpwrap .av-omna-banner-scope *::before,
	#wpwrap .av-omna-banner-scope *::after {
		box-sizing: border-box;
	}

	#wpwrap .av-omna-banner-scope .av-omna-overline {
		font-size: 11px;
		font-weight: 700;
		letter-spacing: .1em;
		text-transform: uppercase;
		color: var(--av-omna-on-dark-soft);
		margin: 0 0 14px;
	}

	#wpwrap .av-omna-banner-scope .av-omna-overline::before {
		content: "// ";
		font-size: 1.5em;
		line-height: 0;
		vertical-align: -.05em;
		opacity: .55;
	}

	#wpwrap .av-omna-banner-scope .av-omna-title {
		font-family: var(--av-omna-serif);
		font-weight: 400;
		line-height: 1.15;
		color: #fff;
		margin: 0 0 12px;
		letter-spacing: -.005em;
		font-size: 24px;
	}

	#wpwrap .av-omna-banner-scope .av-omna-title em {
		font-style: italic;
	}

	/*	p.  -> beats ".notice p"	*/
	#wpwrap .av-omna-banner-scope p.av-omna-text {
		font-size: 15px;
		line-height: 1.65;
		color: var(--av-omna-on-dark);
		margin: 0 0 22px;
		max-width: 64ch;
		text-wrap: pretty;
	}

	#wpwrap .av-omna-banner-scope .av-omna-actions {
		display: flex;
		align-items: center;
		gap: 18px;
		flex-wrap: wrap;
	}

	/*	a.  -> beats "div.notice a"	*/
	#wpwrap .av-omna-banner-scope a.av-omna-btn,
	#wpwrap .av-omna-banner-scope a.av-omna-btn:visited {
		display: inline-flex;
		align-items: center;
		gap: .5em;
		padding: 12px 24px;
		border-radius: 999px;
		text-decoration: none;
		box-shadow: none;
		font-size: 14px;
		font-weight: 700;
		line-height: 1;
		background: rgba(255, 255, 255, .18);
		color: #fff;
		border: 1px solid rgba(255, 255, 255, .5);
		transition: background .2s ease, border-color .2s ease;
	}

	#wpwrap .av-omna-banner-scope a.av-omna-btn:hover,
	#wpwrap .av-omna-banner-scope a.av-omna-btn:focus,
	#wpwrap .av-omna-banner-scope a.av-omna-btn:active {
		background: rgba(255, 255, 255, .3);
		border-color: #fff;
		color: #fff;
		text-decoration: none;
	}

	#wpwrap .av-omna-banner-scope a.av-omna-btn:focus {
		outline: 2px solid #fff;
		outline-offset: 2px;
		box-shadow: none;
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2 {
		position: relative;
		display: grid;
		grid-template-columns: 3fr 2fr;
		overflow: hidden;
		border-radius: var(--av-omna-radius);
		color: #fff;
		background-image: var(--av-omna-gradient);
		background-size: 250% 250%;
		animation: av-omna-gradient-shift 20s ease infinite;
	}

	/*	keeps the white type readable while the gradient sweeps	*/
	#wpwrap .av-omna-banner-scope .av-omna-beta2::before {
		content: "";
		position: absolute;
		inset: 0;
		z-index: 0;
		pointer-events: none;
		background: linear-gradient(100deg, rgba(0, 0, 0, .32) 0%, rgba(0, 0, 0, .14) 100%);
	}

	@keyframes av-omna-gradient-shift {
		0% {
			background-position: 0% 50%;
		}

		50% {
			background-position: 100% 50%;
		}

		100% {
			background-position: 0% 50%;
		}
	}

	/*	main column - content centered vertically	*/
	#wpwrap .av-omna-banner-scope .av-omna-beta2__cell {
		position: relative;
		z-index: 1;
		min-width: 0;
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__cell--main {
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: 30px 34px 30px 40px;
	}

	/*	aside - two halves of exactly the same height, each centered	*/
	#wpwrap .av-omna-banner-scope .av-omna-beta2__aside {
		display: grid;
		grid-template-rows: 1fr 1fr;
		border-left: 1px solid var(--av-omna-hairline);
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__aside-half {
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: 24px 34px;
		min-width: 0;
	}

	/*	padding lives on the halves, so the divider spans the full column width	*/
	#wpwrap .av-omna-banner-scope .av-omna-beta2__aside-half+.av-omna-beta2__aside-half {
		border-top: 1px solid var(--av-omna-hairline);
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__stats {
		display: grid;
		grid-template-columns: repeat(3, auto);
		gap: 16px;
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__stat {
		line-height: 1.1;
		min-width: 0;
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__num {
		font-family: var(--av-omna-serif);
		font-size: 24px;
		color: #fff;
		display: block;
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__cap {
		display: block;
		margin-top: 4px;
		font-size: 11px;
		letter-spacing: .08em;
		text-transform: uppercase;
		color: var(--av-omna-on-dark-soft);
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__ai .av-omna-beta2__num em {
		font-style: italic;
	}

	#wpwrap .av-omna-banner-scope .av-omna-beta2__ai p {
		margin: 6px 0 0;
		font-size: 13px;
		line-height: 1.55;
		color: var(--av-omna-on-dark);
	}

	@media only screen and (max-width: 1023px) {
		#wpwrap .av-omna-banner-scope .av-omna-beta2 {
			grid-template-columns: 1fr;
		}

		#wpwrap .av-omna-banner-scope .av-omna-beta2__aside {
			border-left: 0;
			border-top: 1px solid var(--av-omna-hairline);
			grid-template-rows: auto auto;
		}
	}

	@media only screen and (max-width: 782px) {
		#wpwrap .av-omna-banner-scope .av-omna-title {
			font-size: 21px;
		}

		#wpwrap .av-omna-banner-scope .av-omna-beta2__cell--main {
			padding: 24px 22px;
		}

		#wpwrap .av-omna-banner-scope .av-omna-beta2__aside-half {
			padding: 22px;
		}

		#wpwrap .av-omna-banner-scope .av-omna-beta2__stats {
			gap: 12px;
		}

		#wpwrap .av-omna-banner-scope .av-omna-actions {
			gap: 12px;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		#wpwrap .av-omna-banner-scope * {
			animation: none !important;
		}
	}
</style>

<div class="container av-notice-8-0">
	<h2>Welcome to Enfold 8.0</h2>
	<h4>New: Two new demos to import</h4>
	<ul>
		<li><strong>Reef</strong> - a fashion shop built with the new gallery layouts. <a
				href="https://kriesi.at/themes/enfold-reef-shop/" target="_blank" rel="noopener noreferrer">View the demo</a>
		</li>
		<li><strong>Studio</strong> - a portfolio for a small brand or design studio. <a
				href="https://kriesi.at/themes/enfold-studio/" target="_blank" rel="noopener noreferrer">View the
				demo</a></li>
		<li>Other features and fixes - check the <a href="https://kriesi.at/documentation/enfold/changelog/#enfold-8-0"
				target="_blank" rel="noopener noreferrer">changelog</a> for all changes.</li>
	</ul>

	<div class="av-omna-banner-scope">
		<div class="av-omna-beta2">
			<div class="av-omna-beta2__cell av-omna-beta2__cell--main">
				<div class="av-omna-overline">Closed beta - We need you! </div>
				<h2 class="av-omna-title">Enfold goes <em>multilingual</em> - translate your site now</h2>
				<p class="av-omna-text">Omnalingo is our new AI translation plugin - built to work perfectly with
					Enfold. We are letting 50 Enfold users in first: free while the beta runs, in exchange for your
					feedback before the public release.</p>
				<div class="av-omna-actions">
					<a class="av-omna-btn" href="<?php echo esc_url($beta_url); ?>" target="_blank"
						rel="noopener noreferrer">Apply for a seat &rarr;</a>
				</div>
			</div>
			<div class="av-omna-beta2__cell av-omna-beta2__aside">
				<div class="av-omna-beta2__aside-half">
					<div class="av-omna-beta2__stats">
						<div class="av-omna-beta2__stat">
							<span class="av-omna-beta2__num">133</span>
							<span class="av-omna-beta2__cap">languages</span>
						</div>
						<div class="av-omna-beta2__stat">
							<span class="av-omna-beta2__num">Just minutes</span>
							<span class="av-omna-beta2__cap">to a translated site</span>
						</div>
						<div class="av-omna-beta2__stat">
							<span class="av-omna-beta2__num">50 seats</span>
							<span class="av-omna-beta2__cap">free for testers</span>
						</div>
					</div>
				</div>
				<div class="av-omna-beta2__aside-half">
					<div class="av-omna-beta2__stat av-omna-beta2__ai">
						<span class="av-omna-beta2__num">Premium <em>AI</em></span>
						<p>Page aware AI translation that reads the whole page, keeps your brand voice and your layout
							intact.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>