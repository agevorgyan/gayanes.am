<?php
/**
 * Template Name: Corporate Clients
 *
 * iOS 27 Liquid Glass B2B / Corporate Clients page for gayanes.am.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="primary" class="gv2-main gv2-info-page">

	<!-- Hero -->
	<section class="gv2-info-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page">Կորպորատիվ հաճախորդների համար</span>
			</nav>

			<div class="gv2-info-hero__inner">
				<span class="gv2-eyebrow">B2B համագործակցություն</span>
				<h1 class="gv2-info-hero__title">Կորպորատիվ հաճախորդներին</h1>
				<p class="gv2-info-hero__lead">
					«Գայանեի Խոհանոց»-ում գործում է հատուկ կորպորատիվ վաճառքների բաժին։ Իրավաբանական անձանց, գրասենյակների, սրճարանների և հաստատությունների սպասարկումն իրականացվում է կնքված պայմանագրի հիման վրա՝ արագ, հուսալի և շահավետ պայմաններով։
				</p>
				<div class="gv2-info-hero__actions">
					<a href="tel:+37455600655" class="gv2-btn gv2-btn--primary gv2-btn--lg">Զանգահարել՝ 055 60 06 55</a>
					<a href="mailto:info@gayanes.am?subject=%D4%BF%D5%B8%D6%80%D5%BA%D5%B8%D6%80%D5%A1%D5%BF%D5%AB%D5%BE%20%D5%B0%D5%A1%D5%B4%D5%A1%D5%A3%D5%B8%D6%80%D5%AE%D5%A1%D5%AF%D6%81%D5%B8%D6%82%D5%A9%D5%B5%D5%B8%D6%82%D5%B6" class="gv2-btn gv2-btn--glass gv2-btn--lg">Գրել էլ. փոստով</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Key Corporate Perks -->
	<section class="gv2-section gv2-container" aria-label="Կորպորատիվ առավելություններ">
		<div class="gv2-about-stats">
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">5%</span>
				<span class="gv2-about-stat__label">Զեղչի քարտ (կուպոն) կորպորատիվ գործընկերներին</span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">ԱՆՎՃԱՐ</span>
				<span class="gv2-about-stat__label">Առաքում Երևանի ողջ տարածքում</span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">Ամսական</span>
				<span class="gv2-about-stat__label">Վճարում հաշիվ-ապրանքագրով՝ ամսվա վերջում կամ պատվերից հետո</span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">Անհատական</span>
				<span class="gv2-about-stat__label">Կորպորատիվ վաճառքների մենեջերի աջակցություն</span>
			</div>
		</div>
	</section>

	<!-- Detailed Corporate Terms -->
	<section class="gv2-section gv2-container">
		<div class="gv2-info-grid-2">
			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Պայմանագրային սպասարկում</span>
				<h2>Առանձնահատուկ պայմաններ գործընկերների համար</h2>
				<ul class="gv2-info-list">
					<li><strong>Հաշիվ-ապրանքագրով վճարում.</strong> վճարումն իրականացվում է ամսվա վերջում կամ յուրաքանչյուր պատվերից հետո՝ առանց գումարը կանխիկացնելու և լրացուցիչ ժամանակ ծախսելու։</li>
					<li><strong>Հարմար պատվիրում.</strong> պատվերները ձևակերպվում են հեռախոսով, էլ. փոստով կամ անմիջապես կայքի միջոցով՝ նախապես տեսնելով ապրանքի լուսանկարը, գինը և նկարագրությունը։</li>
					<li><strong>Անվճար առաքում և 5% զեղչ.</strong> բոլոր պայմանագրային կորպորատիվ հաճախորդների համար առաքումն անվճար է, և տրամադրվում է 5% զեղչի կուպոն։</li>
					<li><strong>Համակողմանի խորհրդատվություն.</strong> մեր կորպորատիվ վաճառքների մենեջերը կօգնի ընտրել լավագույն տեսականին և ծավալները։</li>
				</ul>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Միանվագ փոխանցում</span>
				<h2>Բանկային փոխանցում առանց երկարատև պայմանագրի</h2>
				<p>Եթե չեք ցանկանում երկարատև համագործակցության պայմանագիր կնքել, սակայն պատրաստվում եք պատվերի դիմաց վճարել բանկային փոխանցման միջոցով, ապա՝</p>
				<ul class="gv2-info-list">
					<li>Առաքումն իրականացվում է գումարը մեր բանկային հաշվին հայտնվելուց հետո։</li>
					<li>Պատվերի հետ տրամադրվում է համապատասխան հաշիվ-ապրանքագիրը։</li>
					<li>Գործում են սովորական հաճախորդների համար նախատեսված առաքման պայմանները։</li>
				</ul>
			</article>
		</div>
	</section>

	<!-- CTA -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2>Հարցերի և համագործակցության համար</h2>
				<p>Զանգահարեք «Գայանեի Խոհանոց»-ի կորպորատիվ վաճառքների մենեջերին կամ ծանոթացեք մեր ամբողջ տեսականուն։</p>
				<div class="gv2-about-cta__meta">
					<a href="tel:+37455600655" class="gv2-about-cta__pill">055 60 06 55</a>
					<a href="mailto:info@gayanes.am" class="gv2-about-cta__pill">info@gayanes.am</a>
				</div>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">
					<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-outline">
					Առաքում և վճարում
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
