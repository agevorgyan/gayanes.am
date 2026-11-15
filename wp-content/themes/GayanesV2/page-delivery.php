<?php
/**
 * Template Name: Delivery & Payment
 *
 * iOS 27 Liquid Glass Delivery & Payment conditions page for gayanes.am.
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
				<span aria-current="page">Առաքում և վճարում</span>
			</nav>

			<div class="gv2-info-hero__inner">
				<span class="gv2-eyebrow">Առաքման և վճարման պայմաններ</span>
				<h1 class="gv2-info-hero__title">Առաքում և վճարում</h1>
				<p class="gv2-info-hero__lead">
					«Գայանեի Խոհանոց»-ի առաքման ծառայությունն ընդունում է պատվերներ ինչպես կայքի միջոցով, այնպես էլ <a href="tel:+37411600111">011 600 111</a> հեռախոսահամարով՝ ապահովելով արագ, հարմարավետ և սառեցված առաքում Երևան քաղաքում։
				</p>
			</div>
		</div>
	</section>

	<!-- Key Delivery Metrics -->
	<section class="gv2-section gv2-container" aria-label="Առաքման հիմնական պայմաններ">
		<div class="gv2-about-stats">
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">ԱՆՎՃԱՐ</span>
				<span class="gv2-about-stat__label">15,000 ֏ և ավելի պատվերների համար</span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">1,500 ֏</span>
				<span class="gv2-about-stat__label">15,000 ֏-ից ցածր պատվերների դեպքում</span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">1–2 օր</span>
				<span class="gv2-about-stat__label">Առաքում Երևան քաղաքում</span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">011 600 111</span>
				<span class="gv2-about-stat__label">Հեռախոսային պատվերների կենտրոն</span>
			</div>
		</div>
	</section>

	<!-- Delivery Details -->
	<section class="gv2-section gv2-container">
		<div class="gv2-section__head">
			<h2 class="gv2-section__title">Առաքման պայմաններ</h2>
		</div>

		<div class="gv2-info-grid-2">
			<article class="gv2-info-card gv2-glass-card">
				<div class="gv2-info-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
				</div>
				<h3>Ինչպես է իրականացվում առաքումը</h3>
				<p>Կայքում ընտրում եք Ձեր նախընտրած ուտեստները, նշում առաքման հասցեն, հեռախոսահամարը, նախընտրելի օրն ու ժամանակահատվածը, ինչպես նաև հավելյալ ցուցումներ վարորդի համար (օրինակ՝ շենքի մուտքը կամ դուռը թակելու մասին)։</p>
				<p>Մեր առաքիչը կապ կհաստատի Ձեզ հետ նշված հեռախոսահամարով՝ ճշտումներ կատարելու համար։</p>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<div class="gv2-info-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
				</div>
				<h3>Պատվերի կարգավիճակ և փոփոխություն</h3>
				<p>Կայք մուտք գործելուց հետո Ձեր անձնական էջում կարող եք հետևել պատվերների պատմությանը և կարգավիճակին՝ «Պատվիրված», «Հաստատված», «Առաքված», «Կատարված»։</p>
				<ul class="gv2-info-list">
					<li>Մեկ պատվերի շրջանակներում ապրանքն առաքվում է մեկ հասցեով։</li>
					<li>Տարբեր հասցեների համար խնդրում ենք ձևակերպել առանձին պատվերներ։</li>
					<li>Պատվերին լրացուցիչ ապրանք ավելացնելու համար զանգահարեք <a href="tel:+37411600111">011 600 111</a>։</li>
				</ul>
			</article>
		</div>
	</section>

	<!-- Payment Methods -->
	<section class="gv2-section gv2-container">
		<div class="gv2-section__head">
			<h2 class="gv2-section__title">Վճարման եղանակներ</h2>
		</div>

		<div class="gv2-info-grid-3">
			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Առաքման պահին</span>
				<h3>Կանխիկ վճարում</h3>
				<p>Վճարում եք պատվերը ստանալիս՝ կանխիկ ՀՀ դրամով։ Առաքիչը տրամադրում է ՀԴՄ կտրոն (անհրաժեշտության դեպքում՝ նաև հաշիվ-ապրանքագիր)։ Պատվիրելիս կարող եք նշել մանրի անհրաժեշտությունը։</p>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Առցանց</span>
				<h3>Բանկային քարտ (ArCa, Visa, MasterCard)</h3>
				<p>Անվտանգ առցանց վճարում անմիջապես կայքում՝ ArCa, Visa և MasterCard բանկային քարտերով՝ ԱրՔա վճարային համակարգի միջոցով։</p>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Էլեկտրոնային դրամապանակ</span>
				<h3>Idram</h3>
				<p>Պատվերի ձևակերպման «Վճարում» քայլում ընտրեք ԻԴՐԱՄ-ը և կատարեք արագ վճարում ՀՀ դրամով անմիջապես Ձեր ԻԴՐԱՄ հաշվից։</p>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Բջջային վճարում</span>
				<h3>Telcell Wallet</h3>
				<p>Ընտրեք Telcell-ը վճարման քայլում և վճարեք Telcell Wallet-ի միջոցով՝ իրական ժամանակում և ցանկացած վայրից։</p>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">Իրավաբանական և ֆիզ. անձանց</span>
				<h3>Բանկային փոխանցում</h3>
				<p>Նախապես պայմանագիր կնքած կորպորատիվ հաճախորդները պատվերը ստանում են անմիջապես։ Առանց պայմանագրի դեպքում առաքումն իրականացվում է գումարը հաշվին հայտնվելուց հետո՝ հաշիվ-ապրանքագրով։</p>
			</article>

			<article class="gv2-info-card gv2-glass-card">
				<span class="gv2-info-card__badge">ՀՀ Օրենսդրություն</span>
				<h3>Արժույթ և հարկեր</h3>
				<p>Կայքում ներկայացված ապրանքների գները վերջնական են և ներառում են բոլոր հարկերը։ Դոլարով կամ այլ արտարժույթով կանխիկ վճարումներ չեն ընդունվում։</p>
			</article>
		</div>
	</section>

	<!-- CTA -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2>Կորպորատի՞վ պատվեր կամ լրացուցիչ հարցեր</h2>
				<p>Կապվեք մեզ հետ կամ ուսումնասիրեք մեր կորպորատիվ հաճախորդների հատուկ պայմանները։</p>
				<div class="gv2-about-cta__meta">
					<a href="tel:+37411600111" class="gv2-about-cta__pill">011 600 111</a>
					<a href="tel:+37455600655" class="gv2-about-cta__pill">055 60 06 55</a>
				</div>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">
					<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/corporate-clients/' ) ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-outline">
					Կորպորատիվ հաճախորդներին
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
