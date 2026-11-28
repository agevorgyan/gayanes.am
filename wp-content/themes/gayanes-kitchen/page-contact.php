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
?>
<main id="primary" class="gv2-main gv2-info-page gv2-contact-page">
	<!-- 1. Editorial Hero -->
	<section class="gv2-info-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="<?php echo esc_attr( gv2_t( 'Breadcrumb' ) ); ?>">
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
				<span class="gv2-contact-channel__label"><?php echo esc_html( gv2_t( 'Mobile / WhatsApp' ) ); ?></span>
				<strong class="gv2-contact-channel__val">+374 (55) 600655</strong>
			</a>

			<a href="tel:+37411600111" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<span class="gv2-contact-channel__label"><?php echo esc_html( gv2_t( 'Call Center' ) ); ?></span>
				<strong class="gv2-contact-channel__val">+374 (11) 600111</strong>
			</a>

			<a href="mailto:info@gayanes.am" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<span class="gv2-contact-channel__label"><?php echo esc_html( gv2_t( 'Email' ) ); ?></span>
				<strong class="gv2-contact-channel__val">info@gayanes.am</strong>
			</a>

			<a href="https://yandex.com/maps/-/CXSIyGZo" target="_blank" rel="noopener noreferrer" class="gv2-info-card gv2-glass-card gv2-contact-channel">
				<span class="gv2-contact-channel__label"><?php echo esc_html( gv2_t( 'Kitchen' ) ); ?></span>
				<strong class="gv2-contact-channel__val"><?php echo esc_html( gv2_t( 'Yerevan, Davtashen 2nd dist., 37' ) ); ?></strong>
			</a>
		</div>
	</section>

	<!-- 3. Feedback Form + Kitchen Info & Socials -->
	<section class="gv2-section gv2-container">
		<div class="gv2-contact-grid">
			<div class="gv2-contact-form-card gv2-glass-card">
				<h2><?php echo esc_html( gv2_t( 'Send Us a Message' ) ); ?></h2>

				<form id="gv2-contact-form" class="gv2-contact-form" method="post" novalidate>
					<?php echo do_shortcode( '[gv2_contact_nonce]' ); ?>
					<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true" />

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-cf-name"><?php echo esc_html( gv2_t( 'Your Name' ) ); ?> *</label>
							<input id="gv2-cf-name" type="text" name="name" required />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-cf-contact"><?php echo esc_html( gv2_t( 'Phone or Email' ) ); ?> *</label>
							<input id="gv2-cf-contact" type="text" name="contact" required />
						</div>
					</div>

					<div class="gv2-contact-field">
						<label for="gv2-cf-topic"><?php echo esc_html( gv2_t( 'Topic' ) ); ?></label>
						<select id="gv2-cf-topic" name="topic">
							<option value="order"><?php echo esc_html( gv2_t( 'Order & Delivery' ) ); ?></option>
							<option value="corporate"><?php echo esc_html( gv2_t( 'Corporate Partnership' ) ); ?></option>
							<option value="feedback"><?php echo esc_html( gv2_t( 'Feedback & Suggestions' ) ); ?></option>
							<option value="other"><?php echo esc_html( gv2_t( 'Other Question' ) ); ?></option>
						</select>
					</div>

					<div class="gv2-contact-field">
						<label for="gv2-cf-message"><?php echo esc_html( gv2_t( 'Message' ) ); ?> *</label>
						<textarea id="gv2-cf-message" name="message" rows="5" required></textarea>
					</div>

					<div id="gv2-contact-feedback" class="gv2-contact-feedback" role="status" aria-live="polite" hidden></div>

					<?php
					if ( function_exists( 'gv2_render_turnstile_widget' ) ) {
						gv2_render_turnstile_widget( 'contact', 'gv2-turnstile-wrap--contact' );
					}
					?>

					<button type="submit" class="gv2-btn gv2-btn--primary gv2-btn--lg gv2-contact-submit">
						<?php echo esc_html( gv2_t( 'Send Message' ) ); ?> &rarr;
					</button>
				</form>
			</div>

			<aside class="gv2-contact-aside">
				<div class="gv2-info-card gv2-glass-card">
					<span class="gv2-info-card__badge"><?php echo esc_html( gv2_t( 'Working Hours' ) ); ?></span>
					<h3><?php echo esc_html( gv2_t( 'Daily: 09:00 – 21:00' ) ); ?></h3>
					<p><?php echo esc_html( gv2_t( 'Yerevan, 0054, Davtashen 2nd dist., 37' ) ); ?></p>
					<p><?php echo esc_html( function_exists( 'gv2_get_free_delivery_banner_text' ) ? gv2_get_free_delivery_banner_text() : gv2_t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></p>
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
