<?php
/**
 * Template Name: Career
 *
 * iOS 27 Liquid Glass Career & Open Positions page for gayanes.am.
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
				<span aria-current="page">Կարիերա</span>
			</nav>

			<div class="gv2-info-hero__inner">
				<span class="gv2-eyebrow">Թափուր աշխատատեղեր</span>
				<h1 class="gv2-info-hero__title">Կարիերա «Գայանեի Խոհանոց»-ում</h1>
				<p class="gv2-info-hero__lead">
					Միացեք մեր ջերմ, պատասխանատու և ստեղծագործ թիմին Երևանում։ Համագործակցության և թափուր հաստիքների համար ուղարկեք Ձեր ինքնակենսագրականը (CV) <a href="mailto:info@gayanes.am">info@gayanes.am</a> էլ. հասցեին։
				</p>
				<div class="gv2-info-hero__actions">
					<a href="mailto:info@gayanes.am?subject=CV%20%E2%80%94%20Gayane's%20Kitchen" class="gv2-btn gv2-btn--primary gv2-btn--lg">Ուղարկել CV՝ info@gayanes.am</a>
					<a href="tel:+37455600655" class="gv2-btn gv2-btn--glass gv2-btn--lg">055 60 06 55</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Open Positions -->
	<section class="gv2-section gv2-container" aria-labelledby="gv2-career-jobs-heading">
		<div class="gv2-section__head">
			<h2 class="gv2-section__title" id="gv2-career-jobs-heading">Թափուր աշխատատեղեր</h2>
		</div>

		<div class="gv2-info-grid-3">
			<article class="gv2-job-card gv2-glass-card">
				<div>
					<span class="gv2-info-card__badge">Լոգիստիկա • Երևան</span>
					<h3>Վարորդ-առաքիչ</h3>
					<p>Փնտրում ենք վարորդ-առաքիչի, ով մեր պատրաստած համեղ կիսաֆաբրիկատները ժամանակին ու ժպիտով՝ առանց մունաթ-զունաթի, կհասցնի տեղ։</p>
				</div>
				<div>
					<a href="mailto:info@gayanes.am?subject=%D5%8E%D5%A1%D6%80%D5%B8%D6%80%D5%A4-%D5%A1%D5%BC%D5%A1%D6%84%D5%AB%D5%B9" class="gv2-btn gv2-btn--glass">Ուղարկել CV &rarr;</a>
				</div>
			</article>

			<article class="gv2-job-card gv2-glass-card">
				<div>
					<span class="gv2-info-card__badge">Արտադրամաս • Դավթաշեն</span>
					<h3>Խոհարարներ</h3>
					<p>Աշխատանքի ենք հրավիրում ձեռքի շնորքով խոհարարների՝ պելմենի, կոտլետ, բլինչիկ, իշլի քյուֆթա և այլ մսային ու բուսական կիսաֆաբրիկատներ պատրաստելու համար։</p>
				</div>
				<div>
					<a href="mailto:info@gayanes.am?subject=%D4%BD%D5%B8%D5%B0%D5%A1%D6%80%D5%A1%D6%80%D5%B6%D5%A5%D6%80" class="gv2-btn gv2-btn--glass">Ուղարկել CV &rarr;</a>
				</div>
			</article>

			<article class="gv2-job-card gv2-glass-card">
				<div>
					<span class="gv2-info-card__badge">Բիզնեսի զարգացում</span>
					<h3>Վաճառքի մենեջեր</h3>
					<p>Աշխատանքի ենք հրավիրում վաճառքի մենեջերների՝ շուկայի զարգացման, կորպորատիվ համագործակցության և նոր հաճախորդների ձեռքբերման համար։</p>
				</div>
				<div>
					<a href="mailto:info@gayanes.am?subject=%D5%8E%D5%A1%D5%B3%D5%A1%D5%BC%D6%84%D5%AB%20%D5%B4%D5%A5%D5%B6%D5%A5%D5%BB%D5%A5%D6%80" class="gv2-btn gv2-btn--glass">Ուղարկել CV &rarr;</a>
				</div>
			</article>
		</div>
	</section>

	<!-- Why Join Us -->
	<section class="gv2-section gv2-container">
		<div class="gv2-info-grid-3">
			<div class="gv2-info-card gv2-glass-card">
				<h3>Կայուն և ժամանակին վարձատրություն</h3>
				<p>Մրցունակ աշխատավարձ և աճի հնարավորություն՝ կախված Ձեր փորձից և նվիրվածությունից։</p>
			</div>
			<div class="gv2-info-card gv2-glass-card">
				<h3>Մաքուր և հարմարավետ խոհանոց</h3>
				<p>Ժամանակակից սարքավորումներով և հիգիենայի բոլոր նորմերով ապահովված արտադրամաս Դավթաշենում։</p>
			</div>
			<div class="gv2-info-card gv2-glass-card">
				<h3>Ընկերական միջավայր</h3>
				<p>Աշխատանք մի թիմում, որտեղ գնահատվում է յուրաքանչյուրի ձեռքի շնորքն ու բարեհամբույր վերաբերմունքը։</p>
			</div>
		</div>
	</section>

	<!-- CTA -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2>Պատրա՞ստ եք միանալ մեր թիմին</h2>
				<p>Ուղարկեք Ձեր CV-ն էլ. փոստով կամ զանգահարեք մեզ՝ հարցազրույց նշանակելու համար։</p>
				<div class="gv2-about-cta__meta">
					<a href="mailto:info@gayanes.am" class="gv2-about-cta__pill">info@gayanes.am</a>
					<a href="tel:+37455600655" class="gv2-about-cta__pill">055 60 06 55</a>
				</div>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="mailto:info@gayanes.am?subject=CV%20%E2%80%94%20Gayane's%20Kitchen" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">Ուղարկել CV</a>
				<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-outline">Մեր մասին</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
