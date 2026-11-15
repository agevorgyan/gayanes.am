<?php
/**
 * Template Name: Privacy Policy
 *
 * iOS 27 Liquid Glass Privacy Policy page for gayanes.am.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="gv2-main gv2-info-page">

	<!-- Hero -->
	<section class="gv2-info-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page">Գաղտնիության քաղաքականություն</span>
			</nav>

			<div class="gv2-info-hero__inner">
				<span class="gv2-eyebrow">Անձնական տվյալների պաշտպանություն</span>
				<h1 class="gv2-info-hero__title">Գաղտնիության քաղաքականություն</h1>
				<p class="gv2-info-hero__lead">
					Սույն «Գաղտնիության քաղաքականություն»-ը նկարագրում է, թե ինչպես է «Գայանեի Խոհանոց»-ը (gayanes.am) հավաքագրում, օգտագործում և պաշտպանում Ձեր անձնական տեղեկությունները։
				</p>
			</div>
		</div>
	</section>

	<section class="gv2-section gv2-container">
		<!-- Quick TOC Pills -->
		<nav class="gv2-legal-toc" aria-label="Բաժիններ">
			<a href="#privacy-collect" class="gv2-legal-toc__pill">01. Տեղեկությունների հավաքագրում</a>
			<a href="#privacy-use" class="gv2-legal-toc__pill">02. Տեղեկությունների օգտագործում</a>
			<a href="#privacy-nondisclosure" class="gv2-legal-toc__pill">03. Տվյալների չբացահայտում</a>
			<a href="#privacy-security" class="gv2-legal-toc__pill">04. Հաշվի պաշտպանվածություն</a>
		</nav>

		<div class="gv2-legal-stack">
			<article id="privacy-collect" class="gv2-legal-card gv2-glass-card">
				<div class="gv2-legal-card__head">
					<span class="gv2-legal-card__num">01</span>
					<h2>Տեղեկությունների հավաքագրումը</h2>
				</div>
				<p>Մենք կարող ենք հավաքագրել և պահել հետևյալ անձնական տեղեկությունները.</p>
				<ul class="gv2-info-list">
					<li>Էլ. փոստի հասցեն, անուն-ազգանունը, հեռախոսահամար(ներ)ը</li>
					<li>Բանկային ռեկվիզիտները, եթե պատվերի համար վճարումը կատարել եք բանկային փոխանցման միջոցով։ <strong>Մենք ՉԵՆՔ տիրապետում Ձեր վճարային (կրեդիտային կամ դեբետային) քարտի որևէ տվյալի</strong>, քանի որ առցանց վճարումները մեր կայքում իրականացվում են բացառապես Arca և PayPal վճարային համակարգերի միջոցով</li>
					<li>Կայքում Ձեր կատարած գնումների պատմությունը, նախընտրած ապրանքների ցանկը և այլն</li>
					<li>Առաքման հասցեն(ները), առաքման համար տրամադրված լրացուցիչ ինֆորմացիան</li>
					<li>Մեզ հետ կատարված նամակագրությունը (ներառյալ չաթը և էլեկտրոնային նամակները)</li>
				</ul>
			</article>

			<article id="privacy-use" class="gv2-legal-card gv2-glass-card">
				<div class="gv2-legal-card__head">
					<span class="gv2-legal-card__num">02</span>
					<h2>Տեղեկությունների օգտագործումը</h2>
				</div>
				<p>Մեզ համար անձնական տեղեկությունների հավաքագրման առաջնային նպատակը Ձեզ ապահով, հարմարավետ, արդյունավետ և անհատական ծառայություններ մատուցելն է։ Սույնով Դուք համաձայնվում եք, որ մենք կարող ենք օգտագործել Ձեր անձնական տեղեկությունները հետևյալի համար.</p>
				<ul class="gv2-info-list">
					<li>Ձեր հայցած ծառայությունների և օգնության ապահովում</li>
					<li>Մեր ծառայությունների, բովանդակության և գովազդի որակի չափում և բարելավում</li>
					<li>Ոչ ստանդարտ իրավիճակներում զանգի կամ էլ. փոստի միջոցով հավելյալ տեղեկությունների ճշտում կամ հաղորդում</li>
					<li>Պատվերը հաստատող, սխալ լրացված դաշտերի մասին տեղեկացնող, ի պատասխան Ձեր հարցի ուղարկված և այլ տիպի էլեկտրոնային նամակների ուղարկում (օրինակ՝ պրոմո առաջարկներ, եթե բաժանորդագրված եք մեր փոստառաքման ծառայությանը)</li>
				</ul>
			</article>

			<article id="privacy-nondisclosure" class="gv2-legal-card gv2-glass-card">
				<div class="gv2-legal-card__head">
					<span class="gv2-legal-card__num">03</span>
					<h2>Տեղեկությունների չբացահայտումը</h2>
				</div>
				<p>Ձեր կողմից մեզ տրամադրված տեղեկությունները համարվում են միանգամայն գաղտնի և չեն կարող տրամադրվել որևէ երրորդ կողմի։</p>
			</article>

			<article id="privacy-security" class="gv2-legal-card gv2-glass-card">
				<div class="gv2-legal-card__head">
					<span class="gv2-legal-card__num">04</span>
					<h2>Հաշվի պաշտպանվածություն</h2>
				</div>
				<p>Ձեր գաղտնաբառը Ձեր հաշիվ մուտք գործելու բանալին է։ Գաղտնաբառ ընտրելիս օգտագործեք թվերի, տառերի և հատուկ նիշերի չկրկնող համադրություն։ Մի՛ տվեք gayanes.am-ի Ձեր գաղտնաբառը մեկ ուրիշին. հիշե՛ք, որ Դուք պատասխանատու եք Ձեր հաշվի անունից կատարված բոլոր գործողությունների համար։</p>
				<p>Գաղտնաբառի կորստի դեպքում այն կարող եք վերականգնել կայքի օգնությամբ՝ սեղմելով «Մուտք» կոճակի հարևանությամբ գտնվող «Մոռացել եք գաղտնաբառը» հրամանը (<a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>">Իմ հաշիվը</a>)։</p>
			</article>
		</div>
	</section>

</main>

<?php
get_footer();
