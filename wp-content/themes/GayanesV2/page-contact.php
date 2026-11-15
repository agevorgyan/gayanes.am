<?php
/**
 * Template Name: Contact
 *
 * iOS 27 Liquid Glass Contact & Feedback page for gayanes.am.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$contact_nonce = wp_create_nonce( 'gv2_contact_nonce' );
?>

<main id="primary" class="gv2-main gv2-info-page gv2-contact-page">

	<!-- 1. Editorial Hero -->
	<section class="gv2-info-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php echo esc_html( gv2_t( 'Contact' ) ); ?></span>
			</nav>

			<div class="gv2-info-hero__inner">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Contact' ) ); ?></span>
				<h1 class="gv2-info-hero__title"><?php echo esc_html( gv2_t( 'Get in Touch' ) ); ?></h1>
				<p class="gv2-info-hero__lead">
					<?php echo esc_html( gv2_t( 'Questions about our menu, delivery, or corporate orders? Our team in Yerevan is always happy to hear from you.' ) ); ?>
				</p>
			</div>
		</div>
	</section>

	<!-- 2. Quick Contact Channels -->
	<section class="gv2-section gv2-container" aria-label="<?php echo esc_attr( gv2_t( 'Contact' ) ); ?>">
		<div class="gv2-info-grid-4 gv2-contact-channels">
			<a href="tel:+37455600655" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<div class="gv2-info-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				</div>
				<span class="gv2-contact-channel__label">Բջջային / WhatsApp</span>
				<strong class="gv2-contact-channel__val">+374 (55) 600655</strong>
			</a>

			<a href="tel:+37411600111" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<div class="gv2-info-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				</div>
				<span class="gv2-contact-channel__label">Զանգերի կենտրոն</span>
				<strong class="gv2-contact-channel__val">+374 (11) 600111</strong>
			</a>

			<a href="mailto:info@gayanes.am" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<div class="gv2-info-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
				</div>
				<span class="gv2-contact-channel__label">Էլ. փոստ</span>
				<strong class="gv2-contact-channel__val">info@gayanes.am</strong>
			</a>

			<a href="https://yandex.com/maps/-/CXSIyGZo" target="_blank" rel="noopener noreferrer" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<div class="gv2-info-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				</div>
				<span class="gv2-contact-channel__label">Խոհանոց</span>
				<strong class="gv2-contact-channel__val"><?php echo esc_html( gv2_t( 'Yerevan, Davtashen 2nd dist., 37' ) ); ?></strong>
			</a>
		</div>
	</section>

	<!-- 3. Feedback Form + Kitchen Info & Socials -->
	<section class="gv2-section gv2-container">
		<div class="gv2-contact-grid">
			<div class="gv2-contact-form-card gv2-glass-card">
				<h2><?php echo esc_html( gv2_t( 'Send Us a Message' ) ); ?></h2>

				<form id="gv2-contact-form" class="gv2-contact-form" novalidate>
					<input type="hidden" name="nonce" value="<?php echo esc_attr( $contact_nonce ); ?>" />
					<input type="text" name="website" class="gv2-sr-only" tabindex="-1" autocomplete="off" aria-hidden="true" />

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-cf-name"><?php echo esc_html( gv2_t( 'Your Name' ) ); ?> *</label>
							<input id="gv2-cf-name" type="text" name="name" required autocomplete="name" />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-cf-contact"><?php echo esc_html( gv2_t( 'Phone or Email' ) ); ?> *</label>
							<input id="gv2-cf-contact" type="text" name="contact" required placeholder="+374 55 00 00 00" />
						</div>
					</div>

					<div class="gv2-contact-field">
						<label for="gv2-cf-topic"><?php echo esc_html( gv2_t( 'Topic' ) ); ?></label>
						<select id="gv2-cf-topic" name="topic">
							<option value="<?php echo esc_attr( gv2_t( 'Order & Delivery' ) ); ?>"><?php echo esc_html( gv2_t( 'Order & Delivery' ) ); ?></option>
							<option value="<?php echo esc_attr( gv2_t( 'Corporate Partnership' ) ); ?>"><?php echo esc_html( gv2_t( 'Corporate Partnership' ) ); ?></option>
							<option value="<?php echo esc_attr( gv2_t( 'Feedback & Suggestions' ) ); ?>"><?php echo esc_html( gv2_t( 'Feedback & Suggestions' ) ); ?></option>
							<option value="<?php echo esc_attr( gv2_t( 'Other Question' ) ); ?>"><?php echo esc_html( gv2_t( 'Other Question' ) ); ?></option>
						</select>
					</div>

					<div class="gv2-contact-field">
						<label for="gv2-cf-message"><?php echo esc_html( gv2_t( 'Message' ) ); ?> *</label>
						<textarea id="gv2-cf-message" name="message" rows="5" required></textarea>
					</div>

					<div id="gv2-contact-feedback" class="gv2-contact-feedback" role="status" aria-live="polite" hidden></div>

					<button type="submit" class="gv2-btn gv2-btn--primary gv2-btn--lg gv2-contact-submit">
						<?php echo esc_html( gv2_t( 'Send Message' ) ); ?> &rarr;
					</button>
				</form>
			</div>

			<aside class="gv2-contact-aside">
				<div class="gv2-info-card gv2-glass-card">
					<span class="gv2-info-card__badge"><?php echo esc_html( gv2_t( 'Working Hours' ) ); ?></span>
					<h3><?php echo esc_html( gv2_t( 'Daily: 09:00 – 21:00' ) ); ?></h3>
					<p>ք․ Երևան, 0054, Դավթաշեն 2-րդ թաղամաս, 37</p>
					<p><?php echo esc_html( gv2_t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></p>
					<div class="gv2-contact-aside__map">
						<a href="https://yandex.com/maps/-/CXSIyGZo" target="_blank" rel="noopener noreferrer" class="gv2-btn gv2-btn--glass">
							<?php echo esc_html( gv2_t( 'Open on Map' ) ); ?> &rarr;
						</a>
					</div>
				</div>

				<div class="gv2-info-card gv2-glass-card">
					<span class="gv2-info-card__badge"><?php echo esc_html( gv2_t( 'Messengers & Socials' ) ); ?></span>
					<h3><?php echo esc_html( gv2_t( 'Messengers & Socials' ) ); ?></h3>
					<div class="gv2-contact-socials">
						<a href="https://wa.me/37455600655" target="_blank" rel="noopener noreferrer" class="gv2-contact-pill">WhatsApp</a>
						<a href="https://t.me/+37455600655" target="_blank" rel="noopener noreferrer" class="gv2-contact-pill">Telegram</a>
						<a href="viber://chat?number=+37455600655" class="gv2-contact-pill">Viber</a>
						<a href="https://www.instagram.com/gayanes_kitchen" target="_blank" rel="noopener noreferrer" class="gv2-contact-pill">Instagram</a>
						<a href="https://www.facebook.com/gayanes.kitchen" target="_blank" rel="noopener noreferrer" class="gv2-contact-pill">Facebook</a>
					</div>
				</div>
			</aside>
		</div>
	</section>

</main>

<?php
get_footer();
