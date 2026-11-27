<?php
/**
 * Email Footer — Gayane's Kitchen (Option 3: Bold Culinary & Marketing-Driven)
 * Includes Loyalty / Repeat-Order Card, Social Links, and Quick Support.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email       = $email ?? null;
$lang        = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $email ) : 'hy';
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$promo_code  = (string) apply_filters( 'gv2_email_loyalty_code', '', $email, $lang );
$store_name  = get_bloginfo( 'name', 'display' );
?>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<?php if ( function_exists( 'gv2_email_t' ) ) : ?>
						<!-- Option 3 Loyalty / Retention Card -->
						<tr>
							<td class="gv2-loyalty-wrap">
								<div class="gv2-loyalty-card">
									<p class="gv2-loyalty-title"><?php echo esc_html( gv2_email_t( 'loyalty_title', $lang ) ); ?></p>
									<p class="gv2-loyalty-sub"><?php echo esc_html( gv2_email_t( 'loyalty_subtitle', $lang ) ); ?></p>
									<?php if ( '' !== $promo_code ) : ?>
										<div>
											<span class="gv2-loyalty-code">
												<?php echo esc_html( sprintf( gv2_email_t( 'loyalty_code_label', $lang ), $promo_code ) ); ?>
											</span>
										</div>
									<?php endif; ?>
									<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-loyalty-link" target="_blank">
										<?php echo esc_html( gv2_email_t( 'loyalty_cta', $lang ) ); ?> &rarr;
									</a>
								</div>
							</td>
						</tr>
						<?php endif; ?>
					</table>

					<!-- Footer & Socials -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer" role="presentation">
						<tr>
							<td align="center" valign="top">
								<div class="gv2-social-row">
									<a href="https://www.instagram.com/gayanes_kitchen" class="gv2-social-pill" target="_blank">Instagram</a>
									<a href="https://www.facebook.com/gayanes.kitchen" class="gv2-social-pill" target="_blank">Facebook</a>
									<a href="https://wa.me/37455600655" class="gv2-social-pill" target="_blank">WhatsApp</a>
									<a href="https://t.me/+37455600655" class="gv2-social-pill" target="_blank">Telegram</a>
								</div>
								<div class="gv2-footer-text">
									<?php if ( function_exists( 'gv2_email_t' ) ) : ?>
										<?php echo esc_html( gv2_email_t( 'questions_line', $lang ) ); ?>
										<a href="tel:+37455600655">+374 55 600 655</a><br />
										&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $store_name ); ?> (gayanes.am). <?php echo esc_html( gv2_email_t( 'all_rights', $lang ) ); ?>
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
