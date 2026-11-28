<?php
/**
 * Corporate Partnership (B2B / HoReCa) CPT, AJAX handler, SEO & trilingual copy.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check whether the current request is rendering the Corporate Clients page.
 */
function gv2_is_corporate_page(): bool {
	return is_page_template( 'page-corporate-clients.php' ) || is_page( array( 'corporate-clients', 'korporativnym-klientam' ) );
}

/* --------------------------------------------------------------------------
 * Custom Post Type: Corporate Partnership Applications (WP Admin)
 * -------------------------------------------------------------------------- */

add_action(
	'init',
	static function (): void {
		register_post_type(
			'gv2_corp_lead',
			array(
				'labels'              => array(
					'name'               => 'Կորպորատիվ հայտեր',
					'singular_name'      => 'Կորպորատիվ հայտ',
					'menu_name'          => 'Կորպորատիվ հայտեր',
					'all_items'          => 'Բոլոր հայտերը',
					'view_item'          => 'Դիտել հայտը',
					'search_items'       => 'Որոնել հայտեր',
					'not_found'          => 'Հայտեր չեն գտնվել',
					'not_found_in_trash' => 'Աղբարկղում հայտեր չկան',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 59,
				'menu_icon'           => 'dashicons-building',
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'        => true,
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
			)
		);
	}
);

add_filter(
	'manage_gv2_corp_lead_posts_columns',
	static function ( array $columns ): array {
		return array(
			'cb'          => $columns['cb'] ?? '<input type="checkbox" />',
			'title'       => 'Ընկերություն',
			'gv2_contact' => 'Կոնտակտային անձ',
			'gv2_phone'   => 'Հեռախոս',
			'gv2_email'   => 'Էլ. փոստ',
			'gv2_sector'  => 'Բիզնեսի տեսակ',
			'gv2_volume'  => 'Ծավալ',
			'date'        => 'Ամսաթիվ',
		);
	}
);

add_action(
	'manage_gv2_corp_lead_posts_custom_column',
	static function ( string $column, int $post_id ): void {
		$map = array(
			'gv2_contact' => '_gv2_corp_full_name',
			'gv2_phone'   => '_gv2_corp_phone',
			'gv2_email'   => '_gv2_corp_email',
			'gv2_sector'  => '_gv2_corp_sector',
			'gv2_volume'  => '_gv2_corp_volume',
		);
		if ( isset( $map[ $column ] ) ) {
			echo esc_html( (string) get_post_meta( $post_id, $map[ $column ], true ) );
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes',
	static function (): void {
		add_meta_box(
			'gv2_corp_lead_details',
			'Համագործակցության հայտի մանրամասներ',
			'gv2_render_corp_lead_metabox',
			'gv2_corp_lead',
			'normal',
			'high'
		);
	}
);

/**
 * Render the Corporate Lead details meta box in WP Admin.
 */
function gv2_render_corp_lead_metabox( WP_Post $post ): void {
	$fields = array(
		'_gv2_corp_full_name' => 'Անուն և ազգանուն',
		'_gv2_corp_company'   => 'Ընկերության / բիզնեսի անվանում',
		'_gv2_corp_sector'    => 'Բիզնեսի տեսակ',
		'_gv2_corp_phone'     => 'Հեռախոս',
		'_gv2_corp_email'     => 'Էլեկտրոնային փոստ',
		'_gv2_corp_products'  => 'Հետաքրքրող արտադրանք',
		'_gv2_corp_volume'    => 'Պատվերի մոտավոր ծավալ',
		'_gv2_corp_frequency' => 'Համագործակցության հաճախականություն',
		'_gv2_corp_comment'   => 'Լրացուցիչ հաղորդագրություն',
		'_gv2_corp_consent'   => 'Համաձայնություն',
		'_gv2_corp_lang'      => 'Լեզու',
	);

	echo '<table class="form-table" role="presentation">';
	foreach ( $fields as $meta_key => $label ) {
		$val = (string) get_post_meta( $post->ID, $meta_key, true );
		echo '<tr>';
		echo '<th scope="row">' . esc_html( $label ) . '</th>';
		echo '<td>' . nl2br( esc_html( '' !== $val ? $val : '—' ) ) . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}

/* --------------------------------------------------------------------------
 * SEO Title & Meta Description for B2B Landing Page
 * -------------------------------------------------------------------------- */

add_filter(
	'wpseo_title',
	static function ( string $title ): string {
		if ( ! gv2_is_corporate_page() ) {
			return $title;
		}
		$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
		if ( 'ru' === $lang ) {
			return 'Полуфабрикаты для ресторанов и кафе | Gayane’s Kitchen';
		}
		if ( 'en' === $lang ) {
			return 'Frozen Semi-Finished Products for Restaurants & Cafes | Gayane’s Kitchen';
		}
		return 'Կիսաֆաբրիկատներ ռեստորանների և սրճարանների համար | Գայանեի Խոհանոց';
	},
	25
);

add_filter(
	'document_title_parts',
	static function ( array $parts ): array {
		if ( ! gv2_is_corporate_page() ) {
			return $parts;
		}
		$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
		if ( 'ru' === $lang ) {
			$parts['title'] = 'Полуфабрикаты для ресторанов и кафе | Gayane’s Kitchen';
		} elseif ( 'en' === $lang ) {
			$parts['title'] = 'Frozen Semi-Finished Products for Restaurants & Cafes | Gayane’s Kitchen';
		} else {
			$parts['title'] = 'Կիսաֆաբրիկատներ ռեստորանների և սրճարանների համար | Գայանեի Խոհանոց';
		}
		unset( $parts['tagline'] );
		return $parts;
	},
	25
);

add_filter(
	'wpseo_metadesc',
	static function ( string $desc ): string {
		if ( ! gv2_is_corporate_page() ) {
			return $desc;
		}
		$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
		if ( 'ru' === $lang ) {
			return '«Gayane’s Kitchen» предлагает замороженные полуфабрикаты, индивидуальное производство и гибкие условия сотрудничества для ресторанов, кафе, столовых и бизнеса.';
		}
		if ( 'en' === $lang ) {
			return 'Gayane’s Kitchen offers frozen semi-finished products, custom food production, and tailored B2B partnership terms for restaurants, cafes, canteens, and hospitality.';
		}
		return '«Գայանեի Խոհանոց»-ը առաջարկում է կիսաֆաբրիկատներ, անհատական արտադրություն և համագործակցության պայմաններ ռեստորանների, սրճարանների, ճաշարանների ու սննդի ոլորտի բիզնեսների համար։';
	},
	25
);

/* --------------------------------------------------------------------------
 * Trilingual B2B Copy (HY / RU / EN)
 * -------------------------------------------------------------------------- */

/**
 * Get localized copy for the B2B Landing Page.
 *
 * @return array<string, mixed>
 */
function gv2_get_corporate_copy(): array {
	$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';

	$copy = array(
		'hy' => array(
			// Sub-nav
			'nav_products'       => 'Արտադրանք',
			'nav_partners'       => 'Մեր գործընկերները',
			'nav_custom'         => 'Անհատական արտադրություն',
			'nav_terms'          => 'Համագործակցության պայմաններ',
			'nav_faq'            => 'FAQ',
			'nav_cta'            => 'Դառնալ գործընկեր',

			// Section 1: Hero
			'eyebrow'            => 'B2B սննդի արտադրություն և մատակարարում',
			'h1'                 => 'Ձեր խոհանոցի վստահելի գործընկերը',
			'hero_subheadline'   => 'Կիսաֆաբրիկատներ և անհատական արտադրություն՝ ձեր բիզնեսի համար',
			'hero_text'          => '«Գայանեի Խոհանոց»-ը համագործակցում է ռեստորանների, սրճարանների, ճաշարանների, հյուրանոցների և սննդի ոլորտի բիզնեսների հետ՝ առաջարկելով որակյալ կիսաֆաբրիկատներ, անհատական արտադրություն և համագործակցության ճկուն պայմաններ։',
			'cta_primary'        => 'Դառնալ գործընկեր',
			'cta_products'       => 'Տեսնել արտադրանքը',
			'cta_contact'        => 'Կապ հաստատել',
			'cta_call'           => 'Զանգահարել',

			// Section 2: Who we work with
			'partners_eyebrow'   => 'Ոլորտներ',
			'partners_title'     => 'Մեր գործընկերներն են սննդի ոլորտի բիզնեսները',
			'partners_desc'      => 'Մենք առաջարկում ենք համագործակցության լուծումներ այն բիզնեսների համար, որոնք կարևորում են արտադրանքի որակը, աշխատանքի արդյունավետությունը և վստահելի մատակարարների հետ համագործակցությունը։',
			'partners'           => array(
				array(
					'icon'  => 'utensils',
					'title' => 'Ռեստորաններ',
					'desc'  => 'Ձեր մենյուի համար պատրաստի կիսաֆաբրիկատներ և հատուկ արտադրանք։',
				),
				array(
					'icon'  => 'coffee',
					'title' => 'Սրճարաններ',
					'desc'  => 'Հարմար լուծումներ՝ ձեր խոհանոցի աշխատանքի արդյունավետությունը բարձրացնելու համար։',
				),
				array(
					'icon'  => 'tray',
					'title' => 'Ճաշարաններ',
					'desc'  => 'Կայուն արտադրանք՝ մեծ և պարբերական պատվերների համար։',
				),
				array(
					'icon'  => 'hotel',
					'title' => 'Հյուրանոցներ',
					'desc'  => 'Սննդի պատրաստման գործընթացը հեշտացնող արտադրատեսակներ։',
				),
				array(
					'icon'  => 'sparkles',
					'title' => 'Catering ծառայություններ',
					'desc'  => 'Արտադրանք՝ միջոցառումների և սննդի սպասարկման կազմակերպման համար։',
				),
				array(
					'icon'  => 'building',
					'title' => 'Այլ սննդի բիզնեսներ',
					'desc'  => 'Անհատական լուծումներ՝ ձեր բիզնեսի պահանջներին համապատասխան։',
				),
			),

			// Section 3: Products for business
			'products_eyebrow'   => 'Տեսականի բիզնեսի համար',
			'products_title'     => 'Արտադրանք՝ ձեր խոհանոցի պահանջներին համապատասխան',
			'products_desc'      => '«Գայանեի Խոհանոց»-ն առաջարկում է սառեցված կիսաֆաբրիկատների և սննդամթերքի տեսականի, որը կարող է օգտագործվել ռեստորաններում, սրճարաններում, ճաշարաններում և սննդի սպասարկման այլ ոլորտներում։',
			'product_cta'        => 'Հարցնել գնի մասին',
			'products_cta_all'   => 'Ստանալ գնային առաջարկ',

			// Section 4: Custom product development
			'custom_eyebrow'     => 'Անհատական արտադրություն',
			'custom_title'       => 'Պատրաստում ենք նաև ձեր բիզնեսին անհրաժեշտ արտադրանքը',
			'custom_desc_1'      => 'Մեր համագործակցությունը սահմանափակված չէ միայն առկա տեսականու շրջանակում։',
			'custom_desc_2'      => 'Եթե ձեր ռեստորանին, սրճարանին կամ ճաշարանին անհրաժեշտ է հատուկ կիսաֆաբրիկատ, որոշակի չափաբաժին, բաղադրատոմս, քաշ կամ փաթեթավորում, մենք պատրաստ ենք քննարկել արտադրության հնարավորությունները։',
			'custom_points'      => array(
				'Անհատական բաղադրատոմսեր։',
				'Արտադրանքի ցանկ՝ ըստ ձեր մենյուի։',
				'Անհատական քաշ և չափաբաժին։',
				'Անհատական փաթեթավորում։',
				'Պատվերով կիսաֆաբրիկատների արտադրություն։',
				'Արտադրանքի քննարկում և տեխնիկական պայմանների համաձայնեցում։',
				'Պարբերական մատակարարում՝ ըստ համաձայնեցված պայմանների։',
			),
			'custom_cta'         => 'Քննարկել անհատական արտադրությունը',

			// Section 5: Why partner with us
			'why_eyebrow'        => 'Առավելություններ',
			'why_title'          => 'Ինչու՞ համագործակցել «Գայանեի Խոհանոց»-ի հետ',
			'benefits'           => array(
				array(
					'icon'  => 'clock',
					'title' => 'Ժամանակի խնայողություն',
					'desc'  => 'Նվազեցրեք խոհանոցում նախապատրաստական աշխատանքների ծավալը և կենտրոնացեք ձեր հիմնական գործունեության վրա։',
				),
				array(
					'icon'  => 'shield',
					'title' => 'Կայուն որակ',
					'desc'  => 'Համագործակցեք արտադրողի հետ, որը կարևորում է արտադրանքի որակը և պատրաստման գործընթացի վերահսկողությունը։',
				),
				array(
					'icon'  => 'sliders',
					'title' => 'Անհատական մոտեցում',
					'desc'  => 'Համագործակցության պայմանները և արտադրանքի տեսականին կարող են քննարկվել ձեր բիզնեսի պահանջներին համապատասխան։',
				),
				array(
					'icon'  => 'layers',
					'title' => 'Մենյուի ընդլայնում',
					'desc'  => 'Օգտագործեք մեր արտադրանքը ձեր մենյուի տարբեր ուտեստների պատրաստման համար։',
				),
				array(
					'icon'  => 'phone',
					'title' => 'Հարմար պատվերներ',
					'desc'  => 'Պատվերները կարող եք ձևակերպել հեռախոսով կամ էլեկտրոնային փոստով։',
				),
				array(
					'icon'  => 'handshake',
					'title' => 'Հարմար համագործակցության պայմաններ',
					'desc'  => 'Գնային առաջարկները, վճարման և առաքման պայմանները քննարկվում են անհատական։',
				),
			),

			// Section 6: How partnership works
			'steps_eyebrow'      => 'Գործընթաց',
			'steps_title'        => 'Համագործակցությունը՝ 4 պարզ քայլով',
			'steps'              => array(
				array( 'num' => '01', 'title' => 'Դիմում', 'desc' => 'Կապ հաստատեք մեզ հետ և ներկայացրեք ձեր բիզնեսի պահանջները։' ),
				array( 'num' => '02', 'title' => 'Քննարկում', 'desc' => 'Մեր կորպորատիվ վաճառքների մենեջերը կքննարկի ձեր պահանջները, անհրաժեշտ ապրանքատեսականին և համագործակցության հնարավորությունները։' ),
				array( 'num' => '03', 'title' => 'Անհատական առաջարկ', 'desc' => 'Կստանաք ձեր բիզնեսի պահանջներին համապատասխան ապրանքների և գնային պայմանների առաջարկ։' ),
				array( 'num' => '04', 'title' => 'Համագործակցություն', 'desc' => 'Համաձայնեցված պայմաններով սկսում ենք պատվերների և մատակարարումների կազմակերպումը։' ),
			),

			// Section 7: Individual pricing & terms
			'terms_eyebrow'      => 'Պայմաններ',
			'terms_title'        => 'Համագործակցության անհատական պայմաններ',
			'terms_desc'         => 'Յուրաքանչյուր գործընկերոջ համար համագործակցության պայմանները ձևավորվում են անհատական՝ հաշվի առնելով պատվերի ծավալը, արտադրանքի տեսականին, պատվերների պարբերականությունը և արտադրանքի առանձնահատկությունները։',
			'terms_lead'         => 'Մենք պատրաստ ենք քննարկել՝',
			'terms_items'        => array(
				'Անհատական գնային առաջարկ',
				'Պատվերի ծավալներ',
				'Պարբերական մատակարարումներ',
				'Վճարման պայմաններ',
				'Առաքման պայմաններ',
				'Երկարաժամկետ համագործակցության պայմանագրեր',
				'Անհատական արտադրության պահանջներ',
			),

			// Section 8: Corporate Partnership Form
			'form_eyebrow'       => 'Համագործակցության հայտ',
			'form_title'         => 'Սկսե՞նք համագործակցությունը',
			'form_subtitle'      => 'Լրացրեք հայտը, և մեր կորպորատիվ վաճառքների մենեջերը կապ կհաստատի ձեզ հետ՝ քննարկելու ձեր բիզնեսի պահանջներն ու համագործակցության հնարավորությունները։',
			'form_aside_title'   => 'Ուղիղ կապ կորպորատիվ բաժնի հետ',
			'form_aside_text'    => 'Արագ քննարկման կամ անհատական գնային առաջարկի համար կարող եք նաև կապ հաստատել հեռախոսով կամ մեսենջերներով։',
			'f_full_name'        => 'Անուն և ազգանուն',
			'f_company'          => 'Ընկերության / բիզնեսի անվանում',
			'f_sector'           => 'Բիզնեսի տեսակ',
			'f_phone'            => 'Հեռախոս',
			'f_email'            => 'Էլեկտրոնային փոստ',
			'f_products'         => 'Ինչպիսի՞ արտադրանք է ձեզ հետաքրքրում',
			'f_volume'           => 'Պատվերի մոտավոր ծավալ',
			'f_frequency'        => 'Համագործակցության հաճախականություն',
			'f_comment'          => 'Լրացուցիչ հաղորդագրություն',
			'f_consent'          => 'Համաձայն եմ տվյալների մշակմանը՝ համագործակցության հարցումը դիտարկելու նպատակով (Գաղտնիության քաղաքականություն)։',
			'f_submit'           => 'Ուղարկել համագործակցության հայտը',
			'sector_options'     => array(
				''           => 'Ընտրեք բիզնեսի տեսակը',
				'restaurant' => 'Ռեստորան',
				'cafe'       => 'Սրճարան',
				'canteen'    => 'Ճաշարան',
				'hotel'      => 'Հյուրանոց',
				'catering'   => 'Catering',
				'shop'       => 'Խանութ',
				'other'      => 'Այլ',
			),
			'freq_options'       => array(
				''          => 'Ընտրեք հաճախականությունը',
				'daily'     => 'Ամեն օր',
				'weekly'    => 'Շաբաթական',
				'monthly'   => 'Ամսական',
				'on_demand' => 'Ըստ պահանջի',
			),

			// Section 9: FAQ
			'faq_eyebrow'        => 'Հարց ու պատասխան',
			'faq_title'          => 'Հաճախ տրվող հարցեր',
			'faqs'               => array(
				array(
					'q' => 'Արդյո՞ք համագործակցում եք միայն ռեստորանների հետ։',
					'a' => 'Ոչ։ Մենք համագործակցում ենք ռեստորանների, սրճարանների, ճաշարանների, հյուրանոցների, catering ծառայությունների և սննդի ոլորտում գործող այլ բիզնեսների հետ։',
				),
				array(
					'q' => 'Կարո՞ղ եք պատրաստել մեր բիզնեսին անհրաժեշտ հատուկ արտադրանք։',
					'a' => 'Այո։ Հնարավոր է քննարկել անհատական արտադրատեսակների պատրաստման հնարավորությունը՝ ըստ բաղադրատոմսի, քաշի, փաթեթավորման և այլ պահանջների։',
				),
				array(
					'q' => 'Ինչպե՞ս են ձևավորվում գները։',
					'a' => 'Գները ձևավորվում են անհատական՝ կախված արտադրանքի տեսակից, պատվերի ծավալից, արտադրության առանձնահատկություններից և համագործակցության պայմաններից։',
				),
				array(
					'q' => 'Կարո՞ղ ենք ունենալ պարբերական մատակարարումներ։',
					'a' => 'Այո։ Պարբերական մատակարարումների հնարավորությունը և պայմանները քննարկվում են անհատական։',
				),
				array(
					'q' => 'Ինչպե՞ս է իրականացվում վճարումը։',
					'a' => 'Երկարաժամկետ համագործակցության դեպքում վճարման պայմանները կարող են սահմանվել պայմանագրով։ Վճարման տարբերակները համաձայնեցվում են գործընկերոջ հետ։',
				),
				array(
					'q' => 'Ինչպե՞ս կարող ենք սկսել համագործակցությունը։',
					'a' => 'Լրացրեք համագործակցության հայտը կամ կապ հաստատեք մեր կորպորատիվ վաճառքների բաժնի հետ։',
				),
			),

			// Section 10: Final CTA
			'final_title'        => 'Ձեր խոհանոցին անհրաժեշտ լուծումը՝ մեկ վստահելի գործընկերից',
			'final_text'         => 'Ներկայացրեք ձեր բիզնեսի պահանջները։ Մենք կքննարկենք, թե ինչպես կարող ենք օգնել ձեր խոհանոցին՝ մեր արտադրանքի և անհատական արտադրական լուծումների միջոցով։',
			'final_cta'          => 'Դառնալ գործընկեր',
			'final_call'         => 'Զանգահարել մեզ',

			// Validation & Status
			'err_required'       => 'Խնդրում ենք լրացնել բոլոր պարտադիր դաշտերը (անուն, ընկերություն, հեռախոս, էլ. փոստ)։',
			'err_phone'          => 'Խնդրում ենք մուտքագրել վավեր հեռախոսահամար (օրինակ՝ +374 55 60 06 55 կամ 055 60 06 55)։',
			'err_email'          => 'Խնդրում ենք մուտքագրել վավեր էլեկտրոնային փոստի հասցե։',
			'err_consent'        => 'Խնդրում ենք հաստատել տվյալների մշակման համաձայնությունը։',
			'err_duplicate'      => 'Ձեր հայտն արդեն ուղարկվել է։ Խնդրում ենք սպասել մեկ րոպե կամ զանգահարել 055 60 06 55։',
			'msg_success'        => 'Շնորհակալություն։ Ձեր համագործակցության հայտը հաջողությամբ ուղարկվեց։ Մեր կորպորատիվ վաճառքների մենեջերը շուտով կապ կհաստատի ձեզ հետ։',
		),
		'ru' => array(
			'nav_products'       => 'Продукция',
			'nav_partners'       => 'Партнёры',
			'nav_custom'         => 'Индивидуальное производство',
			'nav_terms'          => 'Условия',
			'nav_faq'            => 'FAQ',
			'nav_cta'            => 'Стать партнёром',
			'eyebrow'            => 'B2B производство и поставки',
			'h1'                 => 'Надёжный партнёр для вашей кухни',
			'hero_subheadline'   => 'Полуфабрикаты и индивидуальное производство для вашего бизнеса',
			'hero_text'          => '«Gayane’s Kitchen» сотрудничает с ресторанами, кафе, столовыми, отелями и предприятиями питания, предлагая качественные полуфабрикаты, индивидуальное производство и гибкие условия.',
			'cta_primary'        => 'Стать партнёром',
			'cta_products'       => 'Смотреть продукцию',
			'cta_contact'        => 'Связаться с нами',
			'cta_call'           => 'Позвонить',
			'partners_eyebrow'   => 'Отрасли',
			'partners_title'     => 'Наши партнёры — предприятия сферы питания',
			'partners_desc'      => 'Решения для бизнеса, который ценит стабильное качество продукции, эффективность кухни и надёжность поставщика.',
			'partners'           => array(
				array( 'icon' => 'utensils', 'title' => 'Рестораны', 'desc' => 'Готовые полуфабрикаты и спецпродукция для вашего меню.' ),
				array( 'icon' => 'coffee', 'title' => 'Кафе', 'desc' => 'Удобные решения для повышения эффективности вашей кухни.' ),
				array( 'icon' => 'tray', 'title' => 'Столовые', 'desc' => 'Стабильная продукция для крупных и регулярных заказов.' ),
				array( 'icon' => 'hotel', 'title' => 'Отели', 'desc' => 'Позиции, упрощающие процесс приготовления питания.' ),
				array( 'icon' => 'sparkles', 'title' => 'Кейтеринг', 'desc' => 'Продукция для организации мероприятий и выездного обслуживания.' ),
				array( 'icon' => 'building', 'title' => 'Другие фуд-бизнесы', 'desc' => 'Индивидуальные решения под требования вашего бизнеса.' ),
			),
			'products_eyebrow'   => 'Ассортимент для бизнеса',
			'products_title'     => 'Продукция под задачи вашей кухни',
			'products_desc'      => 'Замороженные полуфабрикаты домашнего качества производства Армении для ресторанов, кафе, столовых и фуд-сервиса.',
			'product_cta'        => 'Запросить цену',
			'products_cta_all'   => 'Получить ценовое предложение',
			'custom_eyebrow'     => 'Индивидуальное производство',
			'custom_title'       => 'Производим также продукцию под требования вашего бизнеса',
			'custom_desc_1'      => 'Наше сотрудничество не ограничивается только текущим ассортиментом.',
			'custom_desc_2'      => 'Если вашему ресторану, кафе или столовой нужен специальный полуфабрикат, определённая порция, рецептура, вес или упаковка — мы готовы обсудить производство.',
			'custom_points'      => array(
				'Индивидуальные рецептуры.',
				'Перечень продукции под ваше меню.',
				'Индивидуальный вес и порционирование.',
				'Индивидуальная упаковка.',
				'Производство полуфабрикатов под заказ.',
				'Согласование технических требований и образцов.',
				'Регулярные поставки по согласованному графику.',
			),
			'custom_cta'         => 'Обсудить индивидуальное производство',
			'why_eyebrow'        => 'Преимущества',
			'why_title'          => 'Почему стоит работать с «Gayane’s Kitchen»',
			'benefits'           => array(
				array( 'icon' => 'clock', 'title' => 'Экономия времени', 'desc' => 'Сократите объём заготовительных работ на кухне и сфокусируйтесь на основном бизнесе.' ),
				array( 'icon' => 'shield', 'title' => 'Стабильное качество', 'desc' => 'Работайте с производителем, который контролирует качество на каждом этапе.' ),
				array( 'icon' => 'sliders', 'title' => 'Индивидуальный подход', 'desc' => 'Условия и ассортимент обсуждаются с учётом задач вашего заведения.' ),
				array( 'icon' => 'layers', 'title' => 'Расширение меню', 'desc' => 'Используйте нашу продукцию для создания новых блюд в вашем меню.' ),
				array( 'icon' => 'phone', 'title' => 'Удобные заказы', 'desc' => 'Оформляйте заказы по телефону или электронной почте.' ),
				array( 'icon' => 'handshake', 'title' => 'Гибкие условия', 'desc' => 'Цены, оплата и условия доставки согласовываются индивидуально.' ),
			),
			'steps_eyebrow'      => 'Процесс',
			'steps_title'        => 'Сотрудничество в 4 простых шага',
			'steps'              => array(
				array( 'num' => '01', 'title' => 'Заявка', 'desc' => 'Свяжитесь с нами и опишите задачи вашего бизнеса.' ),
				array( 'num' => '02', 'title' => 'Обсуждение', 'desc' => 'Наш менеджер обсудит с вами требования, ассортимент и возможности производства.' ),
				array( 'num' => '03', 'title' => 'Индивидуальное предложение', 'desc' => 'Вы получите предложение по ассортименту и ценовым условиям.' ),
				array( 'num' => '04', 'title' => 'Сотрудничество', 'desc' => 'Начинаем регулярные заказы и поставки на согласованных условиях.' ),
			),
			'terms_eyebrow'      => 'Условия',
			'terms_title'        => 'Индивидуальные условия сотрудничества',
			'terms_desc'         => 'Для каждого партнёра условия формируются индивидуально с учётом объёма, ассортимента, периодичности заказов и особенностей продукции.',
			'terms_lead'         => 'Мы готовы обсудить:',
			'terms_items'        => array(
				'Индивидуальное ценовое предложение',
				'Объёмы заказов',
				'Регулярные поставки',
				'Условия оплаты',
				'Условия доставки',
				'Долгосрочные договоры сотрудничества',
				'Требования к индивидуальному производству',
			),
			'form_eyebrow'       => 'Заявка на партнёрство',
			'form_title'         => 'Начнём сотрудничество?',
			'form_subtitle'      => 'Заполните форму, и наш менеджер корпоративных продаж свяжется с вами в ближайшее время.',
			'form_aside_title'   => 'Прямая связь с отделом B2B',
			'form_aside_text'    => 'Для быстрого расчёта или консультации свяжитесь с нами по телефону или в мессенджерах.',
			'f_full_name'        => 'Имя и фамилия',
			'f_company'          => 'Название компании / бизнеса',
			'f_sector'           => 'Тип бизнеса',
			'f_phone'            => 'Телефон',
			'f_email'            => 'Электронная почта',
			'f_products'         => 'Какая продукция вас интересует?',
			'f_volume'           => 'Примерный объём заказа',
			'f_frequency'        => 'Частота сотрудничества',
			'f_comment'          => 'Дополнительное сообщение',
			'f_consent'          => 'Я согласен на обработку данных для рассмотрения заявки (Политика конфиденциальности).',
			'f_submit'           => 'Отправить заявку на сотрудничество',
			'sector_options'     => array(
				''           => 'Выберите тип бизнеса',
				'restaurant' => 'Ресторан',
				'cafe'       => 'Кафе',
				'canteen'    => 'Столовая',
				'hotel'      => 'Отель',
				'catering'   => 'Кейтеринг',
				'shop'       => 'Магазин',
				'other'      => 'Другое',
			),
			'freq_options'       => array(
				''          => 'Выберите частоту',
				'daily'     => 'Ежедневно',
				'weekly'    => 'Еженедельно',
				'monthly'   => 'Ежемесячно',
				'on_demand' => 'По запросу',
			),
			'faq_eyebrow'        => 'Вопросы и ответы',
			'faq_title'          => 'Часто задаваемые вопросы',
			'faqs'               => array(
				array( 'q' => 'Вы сотрудничаете только с ресторанами?', 'a' => 'Нет. Мы работаем с ресторанами, кафе, столовыми, отелями, кейтеринговыми компаниями и другими предприятиями питания.' ),
				array( 'q' => 'Можете ли вы производить спецпродукцию под наш бизнес?', 'a' => 'Да. Мы готовы обсудить изготовление индивидуальных полуфабрикатов по вашей рецептуре, весу, фасовке и другим параметрам.' ),
				array( 'q' => 'Как формируются цены?', 'a' => 'Цены формируются индивидуально в зависимости от вида продукции, объёма заказа, особенностей производства и условий сотрудничества.' ),
				array( 'q' => 'Возможны ли регулярные поставки?', 'a' => 'Да. График и условия регулярных поставок согласовываются индивидуально.' ),
				array( 'q' => 'Как осуществляется оплата?', 'a' => 'При долгосрочном сотрудничестве условия оплаты могут быть закреплены договором. Варианты оплаты согласовываются с партнёром.' ),
				array( 'q' => 'Как начать сотрудничество?', 'a' => 'Заполните заявку на этой странице или свяжитесь с нашим отделом корпоративных продаж.' ),
			),
			'final_title'        => 'Решение для вашей кухни от одного надёжного партнёра',
			'final_text'         => 'Расскажите о задачах вашего бизнеса — мы предложим оптимальный ассортимент и формат производства.',
			'final_cta'          => 'Стать партнёром',
			'final_call'         => 'Позвонить нам',
			'err_required'       => 'Пожалуйста, заполните все обязательные поля (имя, компания, телефон, e-mail).',
			'err_phone'          => 'Пожалуйста, введите корректный номер телефона (например, +374 55 60 06 55).',
			'err_email'          => 'Пожалуйста, введите корректный адрес электронной почты.',
			'err_consent'        => 'Пожалуйста, подтвердите согласие на обработку данных.',
			'err_duplicate'      => 'Ваша заявка уже отправлена. Пожалуйста, подождите минуту или позвоните 055 60 06 55.',
			'msg_success'        => 'Спасибо! Ваша заявка успешно отправлена. Наш менеджер корпоративных продаж скоро свяжется с вами.',
		),
		'en' => array(
			'nav_products'       => 'Products',
			'nav_partners'       => 'Partners',
			'nav_custom'         => 'Custom Production',
			'nav_terms'          => 'Terms',
			'nav_faq'            => 'FAQ',
			'nav_cta'            => 'Become a Partner',
			'eyebrow'            => 'B2B Food Production & Supply',
			'h1'                 => 'A Trusted Partner for Your Kitchen',
			'hero_subheadline'   => 'Semi-finished products and custom food production for your business',
			'hero_text'          => 'Gayane’s Kitchen partners with restaurants, cafes, canteens, hotels, and food businesses, offering quality semi-finished products, custom production, and flexible terms.',
			'cta_primary'        => 'Become a Partner',
			'cta_products'       => 'View Products',
			'cta_contact'        => 'Contact Us',
			'cta_call'           => 'Call Us',
			'partners_eyebrow'   => 'Industries',
			'partners_title'     => 'Our Partners Are Food Businesses',
			'partners_desc'      => 'Supply solutions for businesses that value consistent product quality, kitchen efficiency, and reliable local production.',
			'partners'           => array(
				array( 'icon' => 'utensils', 'title' => 'Restaurants', 'desc' => 'Ready-to-cook semi-finished items and custom products for your menu.' ),
				array( 'icon' => 'coffee', 'title' => 'Cafes', 'desc' => 'Convenient solutions to boost your kitchen’s efficiency.' ),
				array( 'icon' => 'tray', 'title' => 'Canteens', 'desc' => 'Consistent products for large and recurring orders.' ),
				array( 'icon' => 'hotel', 'title' => 'Hotels', 'desc' => 'Products that streamline food preparation workflows.' ),
				array( 'icon' => 'sparkles', 'title' => 'Catering Services', 'desc' => 'Reliable food items for events and catering operations.' ),
				array( 'icon' => 'building', 'title' => 'Other Food Businesses', 'desc' => 'Tailored solutions adapted to your business needs.' ),
			),
			'products_eyebrow'   => 'B2B Catalog',
			'products_title'     => 'Products Tailored to Your Kitchen Needs',
			'products_desc'      => 'Armenian-made frozen semi-finished products suitable for restaurants, cafes, canteens, and hospitality.',
			'product_cta'        => 'Inquire Price',
			'products_cta_all'   => 'Request a Price Quote',
			'custom_eyebrow'     => 'Custom Production',
			'custom_title'       => 'We Also Produce Custom Products for Your Business',
			'custom_desc_1'      => 'Our partnership is not limited to our existing retail range.',
			'custom_desc_2'      => 'If your restaurant, cafe, or canteen needs a custom semi-finished item, specific portion size, recipe, weight, or packaging, we are ready to discuss production.',
			'custom_points'      => array(
				'Custom recipes.',
				'Product lineup tailored to your menu.',
				'Custom weight and portioning.',
				'Custom packaging.',
				'Made-to-order semi-finished production.',
				'Technical specification alignment.',
				'Scheduled recurring deliveries under agreed terms.',
			),
			'custom_cta'         => 'Discuss Custom Production',
			'why_eyebrow'        => 'Benefits',
			'why_title'          => 'Why Partner with Gayane’s Kitchen',
			'benefits'           => array(
				array( 'icon' => 'clock', 'title' => 'Time Savings', 'desc' => 'Reduce prep workload in your kitchen and focus on your core service.' ),
				array( 'icon' => 'shield', 'title' => 'Consistent Quality', 'desc' => 'Partner with a producer committed to strict quality control.' ),
				array( 'icon' => 'sliders', 'title' => 'Individual Approach', 'desc' => 'Terms and product range tailored to your business requirements.' ),
				array( 'icon' => 'layers', 'title' => 'Menu Expansion', 'desc' => 'Use our products to expand and enrich your menu.' ),
				array( 'icon' => 'phone', 'title' => 'Convenient Ordering', 'desc' => 'Place orders easily by phone or email.' ),
				array( 'icon' => 'handshake', 'title' => 'Flexible Terms', 'desc' => 'Pricing, payment, and delivery terms are agreed individually.' ),
			),
			'steps_eyebrow'      => 'Process',
			'steps_title'        => 'Partnership in 4 Simple Steps',
			'steps'              => array(
				array( 'num' => '01', 'title' => 'Inquiry', 'desc' => 'Contact us and share your business requirements.' ),
				array( 'num' => '02', 'title' => 'Discussion', 'desc' => 'Our corporate sales manager discusses your needs, product range, and options.' ),
				array( 'num' => '03', 'title' => 'Custom Offer', 'desc' => 'Receive a tailored product and pricing proposal for your business.' ),
				array( 'num' => '04', 'title' => 'Partnership', 'desc' => 'We launch regular orders and deliveries under agreed terms.' ),
			),
			'terms_eyebrow'      => 'Terms',
			'terms_title'        => 'Individual Partnership Terms',
			'terms_desc'         => 'Partnership terms are shaped individually for each partner based on order volume, product selection, frequency, and production specifics.',
			'terms_lead'         => 'We are ready to discuss:',
			'terms_items'        => array(
				'Custom pricing quote',
				'Order volumes',
				'Recurring deliveries',
				'Payment terms',
				'Delivery terms',
				'Long-term supply agreements',
				'Custom production requirements',
			),
			'form_eyebrow'       => 'Partnership Inquiry',
			'form_title'         => 'Ready to Start a Partnership?',
			'form_subtitle'      => 'Fill out the form and our corporate sales manager will contact you to discuss your needs.',
			'form_aside_title'   => 'Direct B2B Contact',
			'form_aside_text'    => 'For a quick consultation or quote, reach us directly by phone or messenger.',
			'f_full_name'        => 'Full Name',
			'f_company'          => 'Company / Business Name',
			'f_sector'           => 'Business Type',
			'f_phone'            => 'Phone Number',
			'f_email'            => 'Email Address',
			'f_products'         => 'Products of Interest',
			'f_volume'           => 'Estimated Order Volume',
			'f_frequency'        => 'Partnership Frequency',
			'f_comment'          => 'Additional Message',
			'f_consent'          => 'I agree to the processing of my data to review this partnership inquiry (Privacy Policy).',
			'f_submit'           => 'Submit Partnership Inquiry',
			'sector_options'     => array(
				''           => 'Select business type',
				'restaurant' => 'Restaurant',
				'cafe'       => 'Cafe',
				'canteen'    => 'Canteen',
				'hotel'      => 'Hotel',
				'catering'   => 'Catering',
				'shop'       => 'Shop / Retailer',
				'other'      => 'Other',
			),
			'freq_options'       => array(
				''          => 'Select frequency',
				'daily'     => 'Daily',
				'weekly'    => 'Weekly',
				'monthly'   => 'Monthly',
				'on_demand' => 'On Demand',
			),
			'faq_eyebrow'        => 'FAQ',
			'faq_title'          => 'Frequently Asked Questions',
			'faqs'               => array(
				array( 'q' => 'Do you only partner with restaurants?', 'a' => 'No. We work with restaurants, cafes, canteens, hotels, catering companies, and other food service businesses.' ),
				array( 'q' => 'Can you produce custom products for our business?', 'a' => 'Yes. We can discuss producing custom semi-finished items according to your recipe, weight, packaging, and other requirements.' ),
				array( 'q' => 'How is pricing determined?', 'a' => 'Pricing is determined individually based on product type, order volume, production specifics, and partnership terms.' ),
				array( 'q' => 'Can we arrange recurring deliveries?', 'a' => 'Yes. Recurring delivery schedules and terms are discussed individually.' ),
				array( 'q' => 'How is payment handled?', 'a' => 'For long-term partnerships, payment terms can be defined in a contract. Payment options are agreed with each partner.' ),
				array( 'q' => 'How can we start a partnership?', 'a' => 'Submit the partnership inquiry form on this page or contact our corporate sales department.' ),
			),
			'final_title'        => 'The Solution Your Kitchen Needs from One Reliable Partner',
			'final_text'         => 'Share your business requirements and we will discuss how our products and custom production can support your kitchen.',
			'final_cta'          => 'Become a Partner',
			'final_call'         => 'Call Us',
			'err_required'       => 'Please fill in all required fields (name, company, phone, email).',
			'err_phone'          => 'Please enter a valid phone number (e.g. +374 55 60 06 55).',
			'err_email'          => 'Please enter a valid email address.',
			'err_consent'        => 'Please confirm your consent to data processing.',
			'err_duplicate'      => 'Your inquiry has already been submitted. Please wait a minute or call 055 60 06 55.',
			'msg_success'        => 'Thank you! Your partnership inquiry has been submitted. Our corporate sales manager will contact you shortly.',
		),
	);

	return $copy[ $lang ] ?? $copy['hy'];
}

/* --------------------------------------------------------------------------
 * AJAX Form Handler
 * -------------------------------------------------------------------------- */

add_action( 'wp_ajax_gv2_submit_corporate', 'gv2_handle_corporate_submission' );
add_action( 'wp_ajax_nopriv_gv2_submit_corporate', 'gv2_handle_corporate_submission' );

/**
 * Process Corporate Partnership AJAX form submission.
 */
function gv2_handle_corporate_submission(): void {
	$c = gv2_get_corporate_copy();

	if ( ! check_ajax_referer( 'gv2_corporate_nonce', 'nonce', false ) ) {
		wp_send_json_error(
			array( 'message' => gv2_t( 'Security check failed. Please refresh the page and try again.' ) ),
			403
		);
	}

	// Honeypot check.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$website = isset( $_POST['website'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['website'] ) ) ) : '';
	if ( '' !== $website ) {
		wp_send_json_success( array( 'message' => $c['msg_success'] ) );
	}

	// Cloudflare Turnstile verification.
	if ( function_exists( 'gv2_verify_turnstile_token' ) ) {
		$turnstile_check = gv2_verify_turnstile_token();
		if ( is_wp_error( $turnstile_check ) ) {
			wp_send_json_error( array( 'message' => $turnstile_check->get_error_message() ), 403 );
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$full_name = isset( $_POST['full_name'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['full_name'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$company   = isset( $_POST['company'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['company'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$sector    = isset( $_POST['sector'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['sector'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$phone     = isset( $_POST['phone'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['phone'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$email     = isset( $_POST['email'] ) ? trim( sanitize_email( (string) wp_unslash( $_POST['email'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$products  = isset( $_POST['products'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['products'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$volume    = isset( $_POST['volume'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['volume'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$frequency = isset( $_POST['frequency'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['frequency'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$comment   = isset( $_POST['comment'] ) ? trim( sanitize_textarea_field( (string) wp_unslash( $_POST['comment'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$consent   = ! empty( $_POST['consent'] );

	if ( '' === $full_name || '' === $company || '' === $phone || '' === $email ) {
		wp_send_json_error( array( 'message' => $c['err_required'] ), 400 );
	}

	$phone_digits = preg_replace( '/[^\d]/', '', $phone );
	if ( strlen( $phone_digits ) < 8 || strlen( $phone_digits ) > 15 ) {
		wp_send_json_error( array( 'message' => $c['err_phone'] ), 400 );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => $c['err_email'] ), 400 );
	}

	if ( ! $consent ) {
		wp_send_json_error( array( 'message' => $c['err_consent'] ), 400 );
	}

	// Duplicate submission guard (60-second window per email + phone).
	$dup_key = 'gv2_corp_dup_' . md5( strtolower( $email ) . '|' . $phone_digits );
	if ( get_transient( $dup_key ) ) {
		wp_send_json_error( array( 'message' => $c['err_duplicate'] ), 429 );
	}
	set_transient( $dup_key, 1, 60 );

	$sector_label    = $c['sector_options'][ $sector ] ?? $sector;
	$frequency_label = $c['freq_options'][ $frequency ] ?? $frequency;
	$lang            = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'gv2_corp_lead',
			'post_status' => 'publish',
			'post_title'  => $company . ' — ' . $full_name,
		)
	);

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_gv2_corp_full_name', $full_name );
		update_post_meta( $post_id, '_gv2_corp_company', $company );
		update_post_meta( $post_id, '_gv2_corp_sector', $sector_label );
		update_post_meta( $post_id, '_gv2_corp_phone', $phone );
		update_post_meta( $post_id, '_gv2_corp_email', $email );
		update_post_meta( $post_id, '_gv2_corp_products', $products );
		update_post_meta( $post_id, '_gv2_corp_volume', $volume );
		update_post_meta( $post_id, '_gv2_corp_frequency', $frequency_label );
		update_post_meta( $post_id, '_gv2_corp_comment', $comment );
		update_post_meta( $post_id, '_gv2_corp_consent', 'yes' );
		update_post_meta( $post_id, '_gv2_corp_lang', $lang );
	}

	$to      = (string) get_option( 'admin_email', 'info@gayanes.am' );
	$subject = sprintf( '[gayanes.am B2B] Համագործակցության հայտ — %s', $company );
	$body    = sprintf(
		"Անուն և ազգանուն: %s\nԸնկերություն: %s\nԲիզնեսի տեսակ: %s\nՀեռախոս: %s\nԷլ. փոստ: %s\nՀետաքրքրող արտադրանք: %s\nՄոտավոր ծավալ: %s\nՀաճախականություն: %s\nԼեզու: %s\n\nՀաղորդագրություն:\n%s\n",
		$full_name,
		$company,
		$sector_label ?: '—',
		$phone,
		$email,
		$products ?: '—',
		$volume ?: '—',
		$frequency_label ?: '—',
		$lang,
		$comment ?: '—'
	);
	$headers = array( 'Reply-To: ' . $full_name . ' <' . $email . '>' );

	@wp_mail( $to, $subject, $body, $headers ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	wp_send_json_success( array( 'message' => $c['msg_success'] ) );
}
