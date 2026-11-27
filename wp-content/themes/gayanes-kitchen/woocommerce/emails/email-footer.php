<?php
/**
 * Email Footer — Gayane's Kitchen
 * Compact, trustworthy footer with clickable phone CTA, social icons, website link, and copyright.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email      = $email ?? null;
$lang       = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $email ) : 'hy';
$store_name = get_bloginfo( 'name', 'display' );
$icons_uri  = defined( 'GV2_URI' ) ? GV2_URI . '/assets/images/email' : '';
?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

					<!-- Compact Brand Footer -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer" role="presentation">
						<tr>
							<td align="center" valign="top">
								<?php if ( function_exists( 'gv2_email_t' ) ) : ?>
									<div class="gv2-phone-box">
										<div class="gv2-phone-label"><?php echo esc_html( gv2_email_t( 'questions_line', $lang ) ); ?></div>
										<a href="tel:+37455600655" class="gv2-phone-cta">
											<?php if ( $icons_uri ) : ?>
												<img src="<?php echo esc_url( $icons_uri . '/phone.png' ); ?>" alt="" width="22" height="22" style="width: 22px; height: 22px; vertical-align: middle; margin-right: 8px; border-radius: 999px;" />
											<?php endif; ?>
											<span style="vertical-align: middle;">+374 55 600 655</span>
										</a>
									</div>
								<?php endif; ?>

								<table border="0" cellpadding="0" cellspacing="0" align="center" class="gv2-social-table" role="presentation">
									<tr>
										<td class="gv2-social-cell">
											<a href="https://www.instagram.com/gayanes_kitchen" class="gv2-social-icon-link" target="_blank" aria-label="Instagram" title="Instagram">
												<img src="<?php echo esc_url( $icons_uri . '/instagram.png' ); ?>" alt="Instagram" width="38" height="38" />
											</a>
										</td>
										<td class="gv2-social-cell">
											<a href="https://www.facebook.com/gayanes.kitchen" class="gv2-social-icon-link" target="_blank" aria-label="Facebook" title="Facebook">
												<img src="<?php echo esc_url( $icons_uri . '/facebook.png' ); ?>" alt="Facebook" width="38" height="38" />
											</a>
										</td>
										<td class="gv2-social-cell">
											<a href="https://wa.me/37455600655" class="gv2-social-icon-link" target="_blank" aria-label="WhatsApp" title="WhatsApp">
												<img src="<?php echo esc_url( $icons_uri . '/whatsapp.png' ); ?>" alt="WhatsApp" width="38" height="38" />
											</a>
										</td>
										<td class="gv2-social-cell">
											<a href="https://t.me/+37455600655" class="gv2-social-icon-link" target="_blank" aria-label="Telegram" title="Telegram">
												<img src="<?php echo esc_url( $icons_uri . '/telegram.png' ); ?>" alt="Telegram" width="38" height="38" />
											</a>
										</td>
									</tr>
								</table>

								<div class="gv2-footer-text">
									<?php if ( function_exists( 'gv2_email_t' ) ) : ?>
										&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $store_name ); ?> &middot; <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">gayanes.am</a><br />
										<?php echo esc_html( gv2_email_t( 'all_rights', $lang ) ); ?>
									<?php else : ?>
										&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $store_name ); ?> (gayanes.am)
									<?php endif; ?>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
</body>
</html>
