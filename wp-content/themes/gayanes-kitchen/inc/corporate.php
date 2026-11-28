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
			'cb'             => $columns['cb'] ?? '<input type="checkbox" />',
			'title'          => 'Կազմակերպություն',
			'gv2_contact'    => 'Կոնտակտային անձ',
			'gv2_phone'      => 'Հեռախոս',
			'gv2_email'      => 'Էլ. փոստ',
			'gv2_sector'     => 'Ոլորտ',
			'date'           => 'Ամսաթիվ',
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
		'_gv2_corp_company'   => 'Կազմակերպության անվանում',
		'_gv2_corp_full_name' => 'Անուն, ազգանուն',
		'_gv2_corp_position'  => 'Պաշտոն',
		'_gv2_corp_phone'     => 'Հեռախոսահամար',
		'_gv2_corp_email'     => 'Էլեկտրոնային փոստ',
		'_gv2_corp_sector'    => 'Գործունեության ոլորտ',
		'_gv2_corp_products'  => 'Հետաքրքրող ապրանքատեսակներ',
		'_gv2_corp_frequency' => 'Մոտավոր պատվերի հաճախականություն',
		'_gv2_corp_comment'   => 'Լրացուցիչ մեկնաբանություն',
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
 * SEO Title & Meta Description for Corporate Partnership Page
 * -------------------------------------------------------------------------- */

add_filter(
	'wpseo_title',
	static function ( string $title ): string {
		if ( ! gv2_is_corporate_page() ) {
			return $title;
		}
		$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
		if ( 'ru' === $lang ) {
			return 'Корпоративное сотрудничество | Gayane’s Kitchen';
		}
		if ( 'en' === $lang ) {
			return 'Corporate Partnership | Gayane’s Kitchen';
		}
		return 'Կորպորատիվ համագործակցություն | Gayane’s Kitchen';
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
			$parts['title'] = 'Корпоративное сотрудничество | Gayane’s Kitchen';
		} elseif ( 'en' === $lang ) {
			$parts['title'] = 'Corporate Partnership | Gayane’s Kitchen';
		} else {
			$parts['title'] = 'Կորպորատիվ համագործակցություն | Gayane’s Kitchen';
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
			return 'Корпоративные поставки питания для организаций: бесплатная доставка, скидка 5%, гибкие условия оплаты и персональный менеджер.';
		}
		if ( 'en' === $lang ) {
			return 'Corporate food supply for businesses and organizations: free delivery, 5% discount, flexible payment terms, and dedicated personal service.';
		}
		return 'Կորպորատիվ սննդի մատակարարում կազմակերպություններին։ Անվճար առաքում, 5% զեղչ, ճկուն վճարման պայմաններ և անհատական սպասարկում։';
	},
	25
);

/* --------------------------------------------------------------------------
 * Trilingual Copy (HY / RU / EN)
 * -------------------------------------------------------------------------- */

/**
 * Get localized strings for the Corporate Partnership page.
 *
 * @return array<string, mixed>
 */
function gv2_get_corporate_copy(): array {
	$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';

	$copy = array(
		'hy' => array(
			'h1'                 => 'Կորպորատիվ համագործակցություն «Գայանեի Խոհանոցի» հետ',
			'eyebrow'            => 'B2B և HoReCa մատակարարում',
			'hero_title'         => 'Համագործակցություն, որը խնայում է ձեր ժամանակն ու ռեսուրսները',
			'hero_subtitle'      => 'Կորպորատիվ սննդի մատակարարում՝ հատուկ պայմաններով, ճկուն վճարումներով և անհատական սպասարկմամբ',
			'cta_primary'        => 'Դառնալ կորպորատիվ գործընկեր',
			'cta_call'           => 'Զանգահարել՝ 055 60 06 55',
			'cta_manager'        => 'Կապ հաստատել մենեջերի հետ',
			'sectors'            => array( 'Գրասենյակներ', 'Հյուրանոցներ', 'Ռեստորաններ և սրճարաններ', 'Կրթական և բժշկական հաստատություններ' ),
			'benefits_eyebrow'   => 'Առավելություններ',
			'benefits_title'     => 'Ինչու՞ ընտրել մեզ',
			'benefits_subtitle'  => 'Ստեղծել ենք հարմարավետ ու թափանցիկ համակարգ՝ կազմակերպությունների ամենօրյա կամ պարբերական մատակարարման համար։',
			'benefits'           => array(
				array(
					'badge' => '5% զեղչ',
					'title' => '5% զեղչի կտրոն',
					'items' => array( 'Կորպորատիվ հաճախորդներին տրամադրվում է 5% զեղչի կտրոն։' ),
					'icon'  => 'percent',
					'accent' => true,
				),
				array(
					'badge' => 'Անվճար',
					'title' => 'Անվճար առաքում',
					'items' => array( 'Կորպորատիվ պայմանագրով սպասարկվող հաճախորդների համար։' ),
					'icon'  => 'truck',
					'accent' => true,
				),
				array(
					'badge' => 'Ճկունություն',
					'title' => 'Ճկուն վճարման պայմաններ',
					'items' => array(
						'Վճարում հաշիվ-ապրանքագրի հիման վրա։',
						'Վճարում յուրաքանչյուր պատվերից հետո կամ ամսվա վերջում՝ պայմանագրի համաձայն։',
					),
					'icon'  => 'invoice',
					'accent' => false,
				),
				array(
					'badge' => 'Անհատական',
					'title' => 'Անհատական սպասարկում',
					'items' => array(
						'Անձնական կորպորատիվ մենեջեր։',
						'Մասնագիտական խորհրդատվություն։',
						'Պատվերներ հեռախոսով և էլեկտրոնային փոստով։',
					),
					'icon'  => 'headset',
					'accent' => false,
				),
				array(
					'badge' => 'Օնլայն կատալոգ',
					'title' => 'Հարմարավետ առցանց տեսականի',
					'items' => array(
						'Ապրանքների լուսանկարներ, անվանումներ և գներ։',
						'Մանրամասն նկարագրություններ և նմանատիպ ապրանքների համեմատություն։',
					),
					'icon'  => 'grid',
					'accent' => false,
				),
			),
			'steps_eyebrow'      => 'Գործընթացը',
			'steps_title'        => 'Ինչպե՞ս է աշխատում համագործակցությունը',
			'steps'              => array(
				array( 'num' => '01', 'title' => 'Ուղարկում եք համագործակցության հայտ', 'desc' => 'Լրացրեք կարճ հայտը էջի ներքևում կամ զանգահարեք 055 60 06 55։' ),
				array( 'num' => '02', 'title' => 'Կորպորատիվ մենեջերը կապ է հաստատում ձեզ հետ', 'desc' => 'Քննարկում ենք ձեր կազմակերպության պահանջները, ծավալներն ու նախընտրելի տեսականին։' ),
				array( 'num' => '03', 'title' => 'Համաձայնեցնում եք պայմաններն ու կնքում պայմանագիր', 'desc' => 'Ֆիքսում ենք վճարման կարգը, առաքման գրաֆիկը և կորպորատիվ 5% զեղչը։' ),
				array( 'num' => '04', 'title' => 'Կատարում եք պատվերներ և ստանում ձեր արտադրանքը', 'desc' => 'Պատվիրեք կայքով, հեռախոսով կամ էլ. փոստով և ստացեք թարմ ու սառեցված արտադրանքը։' ),
			),
			'products_eyebrow'   => 'Արտադրատեսականի',
			'products_title'     => 'Մեր արտադրատեսականին',
			'products_subtitle'  => 'Հայաստանում արտադրվող տնական սառեցված և կիսապատրաստի ուտեստներ՝ պատրաստ կորպորատիվ մատակարարման համար։',
			'products_cta'       => 'Դիտել ամբողջ տեսականին',
			'form_eyebrow'       => 'Հայտի ձև',
			'form_title'         => 'Կորպորատիվ համագործակցության հայտ',
			'form_subtitle'      => 'Լրացրեք ձեր կազմակերպության տվյալները, և մեր կորպորատիվ մենեջերը կապ կհաստատի ձեզ հետ։',
			'form_aside_title'   => 'Ինչ կստանաք հայտ ուղարկելուց հետո',
			'form_aside_points'  => array(
				'Անհատական կորպորատիվ մենեջերի արագ արձագանք',
				'5% կորպորատիվ զեղչի կտրոն և անվճար առաքում պայմանագրով',
				'Հաշիվ-ապրանքագրով վճարման հնարավորություն',
			),
			'f_company'          => 'Կազմակերպության անվանում',
			'f_full_name'        => 'Անուն, ազգանուն',
			'f_position'         => 'Պաշտոն',
			'f_phone'            => 'Հեռախոսահամար',
			'f_email'            => 'Էլեկտրոնային փոստ',
			'f_sector'           => 'Գործունեության ոլորտ',
			'f_products'         => 'Հետաքրքրող ապրանքատեսակներ',
			'f_frequency'        => 'Մոտավոր պատվերի հաճախականություն',
			'f_comment'          => 'Լրացուցիչ մեկնաբանություն',
			'f_submit'           => 'Ուղարկել համագործակցության հայտ',
			'sector_options'     => array(
				''           => 'Ընտրեք ոլորտը',
				'office'     => 'Գրասենյակ / ընկերություն',
				'hotel'      => 'Հյուրանոց / հյուրատուն',
				'horeca'     => 'Ռեստորան / սրճարան',
				'edu_med'    => 'Կրթական / բժշկական հաստատություն',
				'other'      => 'Այլ կազմակերպություն',
			),
			'freq_options'       => array(
				''           => 'Ընտրեք հաճախականությունը',
				'daily'      => 'Ամեն օր / շաբաթը մի քանի անգամ',
				'weekly'     => 'Շաբաթական',
				'biweekly'   => 'Երկու շաբաթը մեկ',
				'monthly'    => 'Ամսական',
				'onetime'    => 'Մեկանգամյա / ըստ անհրաժեշտության',
			),
			'faq_eyebrow'        => 'Հարց ու պատասխան',
			'faq_title'          => 'Հաճախ տրվող հարցեր',
			'faqs'               => array(
				array(
					'q' => 'Ո՞վ կարող է դառնալ կորպորատիվ հաճախորդ։',
					'a' => 'Կորպորատիվ հաճախորդ կարող են դառնալ իրավաբանական անձինք և կազմակերպությունները՝ գրասենյակներ, ընկերություններ, հյուրանոցներ, հյուրատներ, ռեստորաններ, սրճարաններ, ինչպես նաև կրթական ու բժշկական հաստատություններ։',
				),
				array(
					'q' => 'Ինչպե՞ս է իրականացվում վճարումը։',
					'a' => 'Վճարումն իրականացվում է հաշիվ-ապրանքագրի հիման վրա՝ անկանխիկ փոխանցմամբ, կամ պայմանագրով սահմանված կարգով։',
				),
				array(
					'q' => 'Հնարավո՞ր է վճարել ամսվա վերջում։',
					'a' => 'Այո՛։ Կորպորատիվ պայմանագրի համաձայն հնարավոր է վճարել ինչպես յուրաքանչյուր պատվերից հետո, այնպես էլ ամսվա վերջում՝ ամփոփիչ հաշիվ-ապրանքագրով։',
				),
				array(
					'q' => 'Առաքումն իսկապե՞ս անվճար է։',
					'a' => 'Այո՛, կորպորատիվ պայմանագրով սպասարկվող հաճախորդների համար առաքումն անվճար է։',
				),
				array(
					'q' => 'Ինչպե՞ս է գործում 5% զեղչը։',
					'a' => 'Համագործակցության հաստատումից հետո կորպորատիվ հաճախորդին տրամադրվում է 5% զեղչի կտրոն, որը կիրառվում է պատվերների ձևակերպման ժամանակ։',
				),
				array(
					'q' => 'Ինչպե՞ս կարելի է պատվեր ձևակերպել։',
					'a' => 'Պատվեր կարող եք ձևակերպել ինչպես կայքի առցանց տեսականուց, այնպես էլ անմիջապես ձեր անձնական կորպորատիվ մենեջերի միջոցով՝ հեռախոսով (055 60 06 55) կամ էլեկտրոնային փոստով։',
				),
				array(
					'q' => 'Հնարավո՞ր է համագործակցել առանց երկարաժամկետ պայմանագրի։',
					'a' => 'Փորձնական կամ մեկանգամյա պատվերների համար կարող եք կապ հաստատել մեր մենեջերի հետ՝ քննարկելու ձեր կազմակերպության համար հարմար ձևաչափը, իսկ անվճար առաքման և ամսական վճարման հատուկ պայմանները գործում են կորպորատիվ պայմանագրով։',
				),
			),
			'final_title'        => 'Դարձեք մեր կորպորատիվ գործընկերը',
			'final_text'         => 'Թող ձեր գնումների կազմակերպումն ավելի հեշտ և արդյունավետ դառնա։ Մեր մասնագետները պատրաստ են քննարկելու ձեր կազմակերպության կարիքներն ու առաջարկելու համագործակցության հարմար պայմաններ։',
			'final_cta'          => 'Սկսել համագործակցությունը',
			'err_required'       => 'Խնդրում ենք լրացնել բոլոր պարտադիր դաշտերը (կազմակերպություն, անուն, հեռախոս, էլ. փոստ)։',
			'err_email'          => 'Խնդրում ենք մուտքագրել վավեր էլեկտրոնային փոստի հասցե։',
			'msg_success'        => 'Շնորհակալություն։ Ձեր կորպորատիվ համագործակցության հայտը հաջողությամբ ուղարկվեց։ Մեր մենեջերը շուտով կապ կհաստատի ձեզ հետ։',
		),
		'ru' => array(
			'h1'                 => 'Корпоративное сотрудничество с «Gayane’s Kitchen»',
			'eyebrow'            => 'B2B и HoReCa поставки',
			'hero_title'         => 'Сотрудничество, которое экономит ваше время и ресурсы',
			'hero_subtitle'      => 'Корпоративные поставки питания на специальных условиях, с гибкой оплатой и персональным сервисом',
			'cta_primary'        => 'Стать корпоративным партнёром',
			'cta_call'           => 'Позвонить: 055 60 06 55',
			'cta_manager'        => 'Связаться с менеджером',
			'sectors'            => array( 'Офисы и компании', 'Отели и гостевые дома', 'Рестораны и кафе', 'Образовательные и медицинские учреждения' ),
			'benefits_eyebrow'   => 'Преимущества',
			'benefits_title'     => 'Почему выбирают нас',
			'benefits_subtitle'  => 'Удобная и прозрачная система корпоративных поставок для вашей организации.',
			'benefits'           => array(
				array(
					'badge' => 'Скидка 5%',
					'title' => 'Купон на скидку 5%',
					'items' => array( 'Корпоративным клиентам предоставляется купон на скидку 5%.' ),
					'icon'  => 'percent',
					'accent' => true,
				),
				array(
					'badge' => 'Бесплатно',
					'title' => 'Бесплатная доставка',
					'items' => array( 'Для клиентов, обслуживаемых по корпоративному договору.' ),
					'icon'  => 'truck',
					'accent' => true,
				),
				array(
					'badge' => 'Гибкость',
					'title' => 'Гибкие условия оплаты',
					'items' => array(
						'Оплата по счёту-фактуре.',
						'Оплата после каждого заказа или в конце месяца — согласно договору.',
					),
					'icon'  => 'invoice',
					'accent' => false,
				),
				array(
					'badge' => 'Персонально',
					'title' => 'Индивидуальный сервис',
					'items' => array(
						'Персональный корпоративный менеджер.',
						'Профессиональная консультация.',
						'Заказы по телефону и электронной почте.',
					),
					'icon'  => 'headset',
					'accent' => false,
				),
				array(
					'badge' => 'Онлайн-каталог',
					'title' => 'Удобный онлайн-ассортимент',
					'items' => array(
						'Фотографии блюд, названия и цены.',
						'Подробные описания и сравнение похожих позиций.',
					),
					'icon'  => 'grid',
					'accent' => false,
				),
			),
			'steps_eyebrow'      => 'Процесс',
			'steps_title'        => 'Как работает сотрудничество',
			'steps'              => array(
				array( 'num' => '01', 'title' => 'Отправляете заявку на сотрудничество', 'desc' => 'Заполните короткую форму ниже или позвоните по номеру 055 60 06 55.' ),
				array( 'num' => '02', 'title' => 'Корпоративный менеджер связывается с вами', 'desc' => 'Обсуждаем потребности вашей компании, объёмы и ассортимент.' ),
				array( 'num' => '03', 'title' => 'Согласовываете условия и заключаете договор', 'desc' => 'Фиксируем порядок оплаты, график доставки и корпоративную скидку 5%.' ),
				array( 'num' => '04', 'title' => 'Оформляете заказы и получаете продукцию', 'desc' => 'Заказывайте онлайн, по телефону или e-mail и получайте свежую продукцию.' ),
			),
			'products_eyebrow'   => 'Ассортимент',
			'products_title'     => 'Наша продукция',
			'products_subtitle'  => 'Замороженные и полуфабрикатные блюда домашнего качества производства Армении.',
			'products_cta'       => 'Смотреть весь ассортимент',
			'form_eyebrow'       => 'Заявка',
			'form_title'         => 'Заявка на корпоративное сотрудничество',
			'form_subtitle'      => 'Заполните данные вашей организации, и наш менеджер свяжется с вами в ближайшее время.',
			'form_aside_title'   => 'Что вы получаете после отправки заявки',
			'form_aside_points'  => array(
				'Быстрый отклик персонального корпоративного менеджера',
				'Корпоративная скидка 5% и бесплатная доставка по договору',
				'Возможность безналичной оплаты по счёту-фактуре',
			),
			'f_company'          => 'Название организации',
			'f_full_name'        => 'Имя, фамилия',
			'f_position'         => 'Должность',
			'f_phone'            => 'Телефон',
			'f_email'            => 'Электронная почта',
			'f_sector'           => 'Сфера деятельности',
			'f_products'         => 'Интересующие позиции',
			'f_frequency'        => 'Примерная частота заказов',
			'f_comment'          => 'Дополнительный комментарий',
			'f_submit'           => 'Отправить заявку на сотрудничество',
			'sector_options'     => array(
				''           => 'Выберите сферу',
				'office'     => 'Офис / компания',
				'hotel'      => 'Отель / гостевой дом',
				'horeca'     => 'Ресторан / кафе',
				'edu_med'    => 'Образовательное / медицинское учреждение',
				'other'      => 'Другая организация',
			),
			'freq_options'       => array(
				''           => 'Выберите частоту',
				'daily'      => 'Ежедневно / несколько раз в неделю',
				'weekly'     => 'Еженедельно',
				'biweekly'   => 'Раз в две недели',
				'monthly'    => 'Ежемесячно',
				'onetime'    => 'Разово / по мере необходимости',
			),
			'faq_eyebrow'        => 'Вопросы и ответы',
			'faq_title'          => 'Часто задаваемые вопросы',
			'faqs'               => array(
				array(
					'q' => 'Кто может стать корпоративным клиентом?',
					'a' => 'Корпоративными клиентами могут стать юридические лица и организации: офисы, компании, отели, гостевые дома, рестораны, кафе, а также образовательные и медицинские учреждения.',
				),
				array(
					'q' => 'Как осуществляется оплата?',
					'a' => 'Оплата производится по счёту-фактуре безналичным переводом в соответствии с условиями договора.',
				),
				array(
					'q' => 'Возможна ли оплата в конце месяца?',
					'a' => 'Да. По условиям корпоративного договора оплата возможна как после каждого заказа, так и в конце месяца.',
				),
				array(
					'q' => 'Доставка действительно бесплатная?',
					'a' => 'Да, для клиентов, обслуживаемых по корпоративному договору, доставка бесплатная.',
				),
				array(
					'q' => 'Как работает скидка 5%?',
					'a' => 'После подтверждения сотрудничества корпоративному клиенту предоставляется купон на скидку 5%.',
				),
				array(
					'q' => 'Как можно оформить заказ?',
					'a' => 'Вы можете оформить заказ через онлайн-каталог на сайте или напрямую через вашего корпоративного менеджера по телефону (055 60 06 55) и электронной почте.',
				),
				array(
					'q' => 'Можно ли сотрудничать без долгосрочного договора?',
					'a' => 'Для пробных или разовых заказов свяжитесь с нашим менеджером для согласования удобного формата, а специальные условия бесплатной доставки и постоплаты действуют по корпоративному договору.',
				),
			),
			'final_title'        => 'Станьте нашим корпоративным партнёром',
			'final_text'         => 'Сделайте организацию закупок проще и эффективнее. Наши специалисты готовы обсудить задачи вашей компании и предложить удобные условия сотрудничества.',
			'final_cta'          => 'Начать сотрудничество',
			'err_required'       => 'Пожалуйста, заполните все обязательные поля (организация, имя, телефон, e-mail).',
			'err_email'          => 'Пожалуйста, введите корректный адрес электронной почты.',
			'msg_success'        => 'Спасибо! Ваша заявка на корпоративное сотрудничество успешно отправлена. Наш менеджер свяжется с вами в ближайшее время.',
		),
		'en' => array(
			'h1'                 => 'Corporate Partnership with Gayane’s Kitchen',
			'eyebrow'            => 'B2B & HoReCa Supply',
			'hero_title'         => 'A partnership that saves your time and resources',
			'hero_subtitle'      => 'Corporate food supply with special terms, flexible payments, and dedicated personal service',
			'cta_primary'        => 'Become a Corporate Partner',
			'cta_call'           => 'Call: 055 60 06 55',
			'cta_manager'        => 'Contact a Manager',
			'sectors'            => array( 'Offices & Companies', 'Hotels & Guesthouses', 'Restaurants & Cafes', 'Educational & Medical Institutions' ),
			'benefits_eyebrow'   => 'Benefits',
			'benefits_title'     => 'Why Choose Us',
			'benefits_subtitle'  => 'A transparent, reliable supply system tailored for daily and recurring corporate orders.',
			'benefits'           => array(
				array(
					'badge' => '5% Off',
					'title' => '5% Discount Coupon',
					'items' => array( 'Corporate clients receive a dedicated 5% discount coupon.' ),
					'icon'  => 'percent',
					'accent' => true,
				),
				array(
					'badge' => 'Free',
					'title' => 'Free Delivery',
					'items' => array( 'For clients served under a corporate supply agreement.' ),
					'icon'  => 'truck',
					'accent' => true,
				),
				array(
					'badge' => 'Flexible',
					'title' => 'Flexible Payment Terms',
					'items' => array(
						'Payment based on official invoice.',
						'Pay after each order or at the end of the month, as agreed in the contract.',
					),
					'icon'  => 'invoice',
					'accent' => false,
				),
				array(
					'badge' => 'Dedicated',
					'title' => 'Personal Service',
					'items' => array(
						'Dedicated corporate account manager.',
						'Professional consultation.',
						'Order by phone or email.',
					),
					'icon'  => 'headset',
					'accent' => false,
				),
				array(
					'badge' => 'Online Catalog',
					'title' => 'Convenient Online Catalog',
					'items' => array(
						'Product photos, names, and transparent pricing.',
						'Detailed descriptions and easy comparison of similar items.',
					),
					'icon'  => 'grid',
					'accent' => false,
				),
			),
			'steps_eyebrow'      => 'How It Works',
			'steps_title'        => 'How the Partnership Works',
			'steps'              => array(
				array( 'num' => '01', 'title' => 'Submit a partnership request', 'desc' => 'Fill out the short application form below or call 055 60 06 55.' ),
				array( 'num' => '02', 'title' => 'A corporate manager contacts you', 'desc' => 'We discuss your organization’s needs, order volume, and preferred dishes.' ),
				array( 'num' => '03', 'title' => 'Agree on terms and sign the contract', 'desc' => 'We finalize payment terms, delivery schedule, and your 5% corporate discount.' ),
				array( 'num' => '04', 'title' => 'Place orders and receive your products', 'desc' => 'Order online, by phone, or by email and receive fresh frozen products.' ),
			),
			'products_eyebrow'   => 'Catalog',
			'products_title'     => 'Our Product Range',
			'products_subtitle'  => 'Armenian-made frozen and semi-finished homemade dishes ready for corporate supply.',
			'products_cta'       => 'View Full Catalog',
			'form_eyebrow'       => 'Application',
			'form_title'         => 'Corporate Partnership Application',
			'form_subtitle'      => 'Share your organization’s details and our corporate manager will get in touch with you.',
			'form_aside_title'   => 'What happens after you apply',
			'form_aside_points'  => array(
				'Prompt follow-up from a dedicated corporate manager',
				'5% corporate discount coupon and free delivery under contract',
				'Invoice-based payment options',
			),
			'f_company'          => 'Organization Name',
			'f_full_name'        => 'Full Name',
			'f_position'         => 'Position / Role',
			'f_phone'            => 'Phone Number',
			'f_email'            => 'Email Address',
			'f_sector'           => 'Business Sector',
			'f_products'         => 'Products of Interest',
			'f_frequency'        => 'Estimated Order Frequency',
			'f_comment'          => 'Additional Comments',
			'f_submit'           => 'Submit Partnership Request',
			'sector_options'     => array(
				''           => 'Select sector',
				'office'     => 'Office / Company',
				'hotel'      => 'Hotel / Guesthouse',
				'horeca'     => 'Restaurant / Cafe',
				'edu_med'    => 'Educational / Medical Institution',
				'other'      => 'Other Organization',
			),
			'freq_options'       => array(
				''           => 'Select frequency',
				'daily'      => 'Daily / several times a week',
				'weekly'     => 'Weekly',
				'biweekly'   => 'Bi-weekly',
				'monthly'    => 'Monthly',
				'onetime'    => 'One-time / as needed',
			),
			'faq_eyebrow'        => 'FAQ',
			'faq_title'          => 'Frequently Asked Questions',
			'faqs'               => array(
				array(
					'q' => 'Who can become a corporate client?',
					'a' => 'Legal entities and organizations including offices, companies, hotels, guesthouses, restaurants, cafes, as well as educational and medical institutions.',
				),
				array(
					'q' => 'How is payment handled?',
					'a' => 'Payment is made via bank transfer based on an official invoice according to the contract terms.',
				),
				array(
					'q' => 'Is it possible to pay at the end of the month?',
					'a' => 'Yes. Depending on the corporate agreement, payment can be made after each order or at the end of the month.',
				),
				array(
					'q' => 'Is delivery really free?',
					'a' => 'Yes, delivery is free for clients served under a corporate agreement.',
				),
				array(
					'q' => 'How does the 5% discount work?',
					'a' => 'Once your corporate partnership is confirmed, you receive a 5% discount coupon applicable to your orders.',
				),
				array(
					'q' => 'How can orders be placed?',
					'a' => 'You can place orders through our online catalog or directly via your corporate manager by phone (055 60 06 55) or email.',
				),
				array(
					'q' => 'Is it possible to cooperate without a long-term contract?',
					'a' => 'For trial or one-time orders, please contact our manager to discuss a suitable arrangement; free delivery and monthly invoicing apply under a corporate agreement.',
				),
			),
			'final_title'        => 'Become Our Corporate Partner',
			'final_text'         => 'Make your organization’s procurement simpler and more efficient. Our specialists are ready to discuss your needs and offer tailored terms.',
			'final_cta'          => 'Start Partnership',
			'err_required'       => 'Please fill in all required fields (organization, name, phone, email).',
			'err_email'          => 'Please enter a valid email address.',
			'msg_success'        => 'Thank you! Your corporate partnership request has been submitted. Our manager will contact you shortly.',
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
	$company   = isset( $_POST['company'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['company'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$full_name = isset( $_POST['full_name'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['full_name'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$position  = isset( $_POST['position'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['position'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$phone     = isset( $_POST['phone'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['phone'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$email     = isset( $_POST['email'] ) ? trim( sanitize_email( (string) wp_unslash( $_POST['email'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$sector    = isset( $_POST['sector'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['sector'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$products  = isset( $_POST['products'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['products'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$frequency = isset( $_POST['frequency'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['frequency'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$comment   = isset( $_POST['comment'] ) ? trim( sanitize_textarea_field( (string) wp_unslash( $_POST['comment'] ) ) ) : '';

	if ( '' === $company || '' === $full_name || '' === $phone || '' === $email ) {
		wp_send_json_error( array( 'message' => $c['err_required'] ), 400 );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => $c['err_email'] ), 400 );
	}

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
		update_post_meta( $post_id, '_gv2_corp_company', $company );
		update_post_meta( $post_id, '_gv2_corp_full_name', $full_name );
		update_post_meta( $post_id, '_gv2_corp_position', $position );
		update_post_meta( $post_id, '_gv2_corp_phone', $phone );
		update_post_meta( $post_id, '_gv2_corp_email', $email );
		update_post_meta( $post_id, '_gv2_corp_sector', $sector_label );
		update_post_meta( $post_id, '_gv2_corp_products', $products );
		update_post_meta( $post_id, '_gv2_corp_frequency', $frequency_label );
		update_post_meta( $post_id, '_gv2_corp_comment', $comment );
		update_post_meta( $post_id, '_gv2_corp_lang', $lang );
	}

	$to      = (string) get_option( 'admin_email', 'info@gayanes.am' );
	$subject = sprintf( '[gayanes.am] Կորպորատիվ համագործակցության հայտ — %s', $company );
	$body    = sprintf(
		"Կազմակերպություն: %s\nԱնուն, ազգանուն: %s\nՊաշտոն: %s\nՀեռախոս: %s\nԷլ. փոստ: %s\nՈլորտ: %s\nՀետաքրքրող ապրանքներ: %s\nՀաճախականություն: %s\nԼեզու: %s\n\nՄեկնաբանություն:\n%s\n",
		$company,
		$full_name,
		$position ?: '—',
		$phone,
		$email,
		$sector_label ?: '—',
		$products ?: '—',
		$frequency_label ?: '—',
		$lang,
		$comment ?: '—'
	);
	$headers = array( 'Reply-To: ' . $full_name . ' <' . $email . '>' );

	@wp_mail( $to, $subject, $body, $headers ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	wp_send_json_success( array( 'message' => $c['msg_success'] ) );
}
