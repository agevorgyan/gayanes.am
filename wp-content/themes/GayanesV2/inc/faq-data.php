<?php
/**
 * Structured Trilingual FAQ Data & FAQPage Schema Helper (SEO + AI/GEO Optimized).
 *
 * Single source of truth for:
 * - /faq/ full categorized page
 * - Homepage featured FAQ section
 * - Schema.org FAQPage JSON-LD structured data
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return FAQ categories with localized labels.
 *
 * @return array<string, array{hy: string, ru: string, en: string}>
 */
function gv2_get_faq_categories(): array {
	return array(
		'all'      => array(
			'hy' => 'Բոլորը',
			'ru' => 'Все вопросы',
			'en' => 'All Questions',
		),
		'quality'  => array(
			'hy' => 'Որակ և բաղադրություն',
			'ru' => 'Качество и состав',
			'en' => 'Quality & Ingredients',
		),
		'delivery' => array(
			'hy' => 'Առաքում և վճարում',
			'ru' => 'Доставка и оплата',
			'en' => 'Delivery & Payment',
		),
		'cooking'  => array(
			'hy' => 'Պատրաստում և պահպանում',
			'ru' => 'Приготовление и хранение',
			'en' => 'Cooking & Storage',
		),
		'ordering' => array(
			'hy' => 'Պատվերներ և հատուկ մենյու',
			'ru' => 'Заказы и особое меню',
			'en' => 'Ordering & Special Menu',
		),
	);
}

/**
 * Return all structured FAQs (or only featured ones for the homepage).
 *
 * @param bool $featured_only Whether to return only homepage-featured FAQs.
 * @return array<int, array<string, mixed>>
 */
function gv2_get_faqs( bool $featured_only = false ): array {
	$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';

	$items = array(
		array(
			'id'       => 'what-is-gayanes-kitchen',
			'category' => 'quality',
			'featured' => true,
			'q'        => array(
				'hy' => 'Ի՞նչ է առաջարկում Գայանեի Խոհանոցը (gayanes.am) և ի՞նչ ուտեստներ կարելի է պատվիրել։',
				'ru' => 'Что предлагает «Кухня Гаяне» (gayanes.am) и какие блюда можно заказать?',
				'en' => 'What does Gayane’s Kitchen (gayanes.am) offer and what dishes can I order?',
			),
			'a'        => array(
				'hy' => 'Գայանեի Խոհանոցը (gayanes.am) Երևանում գործող արտադրամաս է, որը պատրաստում է 100% բնական, ձեռքով ձևավորված տնական հայկական կիսաֆաբրիկատներ։ Մենյուում առկա են տնական տոլմա (սուտ տոլմա), խինկալի, պելմենի, բլինչիկ, կարտոֆիլով և սպանախով վարենիկներ, ֆալաֆել և սփրինգ ռոլեր՝ առանց կոնսերվանտների և արհեստական հավելումների։',
				'ru' => '«Кухня Гаяне» (gayanes.am) — это домашнее производство в Ереване, которое ежедневно вручную готовит 100% натуральные армянские полуфабрикаты. В меню представлены домашняя долма, хинкали, пельмени, блинчики, вареники с картофелем и шпинатом, фалафель и спринг-роллы без консервантов и искусственных добавок.',
				'en' => 'Gayane’s Kitchen (gayanes.am) is a Yerevan-based artisanal kitchen crafting 100% natural, handmade Armenian semi-finished dishes daily. Our menu includes homemade dolma, khinkali, pelmeni, blini, potato and spinach vareniks, falafel, and spring rolls with zero preservatives.',
			),
		),
		array(
			'id'       => 'yerevan-delivery-cost',
			'category' => 'delivery',
			'featured' => true,
			'q'        => array(
				'hy' => 'Ինչպե՞ս է իրականացվում առաքումը Երևանում և որքա՞ն արժե այն։',
				'ru' => 'Как осуществляется доставка по Еревану и сколько она стоит?',
				'en' => 'How does delivery work in Yerevan and how much does it cost?',
			),
			'a'        => array(
				'hy' => 'Գայանեի Խոհանոցը իրականացնում է նույն օրվա արագ և սառեցված առաքում Երևանի ողջ տարածքում՝ հատուկ ջերմապաշտպան փաթեթավորմամբ։ 15,000 ֏ և ավելի պատվերների դեպքում առաքումը Երևանում ԱՆՎՃԱՐ է։ Պատվերը ձևակերպելիս կարող եք ընտրել Ձեզ հարմար առաքման օրն ու ժամային միջակայքը։',
				'ru' => '«Кухня Гаяне» выполняет быструю охлаждённую доставку по всему Еревану в день заказа в специальной термоупаковке. При заказе от 15 000 AMD доставка по Еревану БЕСПЛАТНАЯ. При оформлении заказа можно выбрать удобную дату и временной интервал.',
				'en' => 'Gayane’s Kitchen provides same-day chilled delivery across all of Yerevan in temperature-controlled packaging. Delivery in Yerevan is FREE for orders of 15,000 AMD or more. You can select your preferred delivery date and time slot directly at checkout.',
			),
		),
		array(
			'id'       => 'preservatives-natural',
			'category' => 'quality',
			'featured' => true,
			'q'        => array(
				'hy' => 'Արդյո՞ք ուտեստները պարունակում են կոնսերվանտներ, սոյա կամ արհեստական հավելումներ։',
				'ru' => 'Содержат ли блюда консерванты, сою или искусственные добавки?',
				'en' => 'Do your dishes contain preservatives, soy fillers, or artificial additives?',
			),
			'a'        => array(
				'hy' => 'Ոչ, բացարձակապես ոչ։ Մեր բոլոր ուտեստները պատրաստվում են 100% բնական, թարմ տեղական մթերքներից՝ ընտրված հայկական մսից, սեզոնային բանջարեղենից, բուրավետ կանաչիներից և բնական կաթնամթերքից։ Մենք չենք օգտագործում կոնսերվանտներ, համի ուժեղացուցիչներ կամ սոյա. թարմությունը պահպանվում է բացառապես ճիշտ և արագ սառեցման շնորհիվ։',
				'ru' => 'Нет, абсолютно никаких добавок. Все блюда готовятся из 100% натуральных свежих местных продуктов: отборного армянского мяса, сезонных овощей, ароматной зелени и натуральных молочных продуктов. Мы не используем консерванты, усилители вкуса или сою — свежесть сохраняется за счёт бережного охлаждения.',
				'en' => 'No, never. Every dish is made from 100% natural, fresh local ingredients — selected Armenian farm meat, seasonal vegetables, fresh herbs, and natural dairy. We use zero preservatives, flavor enhancers, or soy fillers; freshness is locked in solely through gentle rapid chilling.',
			),
		),
		array(
			'id'       => 'cooking-time-storage',
			'category' => 'cooking',
			'featured' => true,
			'q'        => array(
				'hy' => 'Որքա՞ն ժամանակում են պատրաստվում ուտեստները և ինչպե՞ս ճիշտ պահել դրանք։',
				'ru' => 'Сколько времени занимает приготовление и как правильно хранить полуфабрикаты?',
				'en' => 'How long do the dishes take to cook and how should they be stored?',
			),
			'a'        => array(
				'hy' => 'Գայանեի Խոհանոցի կիսաֆաբրիկատները պատրաստվում են ընդամենը 5–12 րոպեում և նախապես հալեցման կարիք չունեն։ Պահեք փակ փաթեթավորմամբ սառցարանում (-18°C ջերմաստիճանում) մինչև 90 օր, իսկ պատրաստելիս տեղադրեք անմիջապես եռացող ջրի մեջ կամ տաք թավայի վրա։',
				'ru' => 'Полуфабрикаты «Кухни Гаяне» готовятся всего за 5–12 минут и не требуют предварительной разморозки. Храните их в закрытой упаковке в морозильной камере при температуре -18°C до 90 дней, а при готовке сразу опускайте в кипящую воду или выкладывайте на разогретую сковороду.',
				'en' => 'Gayane’s Kitchen dishes cook in just 5–12 minutes straight from the freezer — no thawing needed. Store them sealed in your freezer at -18°C for up to 90 days, and cook directly in boiling water or a preheated pan.',
			),
		),
		array(
			'id'       => 'vegan-fasting-options',
			'category' => 'ordering',
			'featured' => true,
			'q'        => array(
				'hy' => 'Ունե՞ք պահքի, բուսական կամ վեգան ուտեստներ։',
				'ru' => 'Есть ли у вас постные, вегетарианские или веганские блюда?',
				'en' => 'Do you offer vegan, vegetarian, or Armenian fasting options?',
			),
			'a'        => array(
				'hy' => 'Այո՛։ Մենք ունենք հատուկ բուսական և պահքի ընտրանի՝ ներառյալ կարտոֆիլով վարենիկներ, սպանախով վարենիկներ, տնական ֆալաֆել, բանջարեղենով սփրինգ ռոլեր, սնկով սփրինգ ռոլեր և սուտ տոլմա։ Բոլոր բուսական ուտեստները պատրաստվում են մաքուր մակերեսների վրա՝ առանց կենդանական ծագման բաղադրիչների։',
				'ru' => 'Да! У нас есть специальное постное и растительное меню: вареники с картофелем, вареники со шпинатом, домашний фалафель, овощные и грибные спринг-роллы, а также постная долма. Все растительные блюда готовятся отдельно без продуктов животного происхождения.',
				'en' => 'Yes! We offer a dedicated vegan and fasting-friendly selection, including potato vareniks, spinach vareniks, homemade falafel, vegetable and mushroom spring rolls, and meatless dolma — all prepared on clean surfaces without animal products.',
			),
		),
		array(
			'id'       => 'kitchen-address-pickup',
			'category' => 'delivery',
			'featured' => false,
			'q'        => array(
				'hy' => 'Որտե՞ղ է գտնվում Գայանեի Խոհանոցը և կարո՞ղ եմ անձամբ վերցնել պատվերը։',
				'ru' => 'Где находится «Кухня Гаяне» и можно ли забрать заказ самовывозом?',
				'en' => 'Where is Gayane’s Kitchen located and can I pick up my order in person?',
			),
			'a'        => array(
				'hy' => 'Մեր խոհանոցը գտնվում է Երևանում՝ Դավթաշեն 2-րդ թաղամաս, 37 հասցեում (0054)։ Այո, դուք կարող եք կատարել առցանց պատվեր և նախապես համաձայնեցնելով՝ վերցնել այն անձամբ կամ զանգահարել +374 (55) 600655 և +374 (11) 600111 հեռախոսահամարներով։',
				'ru' => 'Наша кухня расположена в Ереване по адресу: Давташен, 2-й квартал, 37 (0054). Вы можете оформить заказ онлайн и забрать его самовывозом по предварительному согласованию или позвонить по номерам +374 (55) 600655 и +374 (11) 600111.',
				'en' => 'Our kitchen is located in Yerevan at Davtashen 2nd District, 37 (0054). Yes, you can place your order online and arrange self-pickup, or call us at +374 (55) 600655 or +374 (11) 600111.',
			),
		),
		array(
			'id'       => 'payment-methods',
			'category' => 'delivery',
			'featured' => false,
			'q'        => array(
				'hy' => 'Ի՞նչ վճարման եղանակներ են գործում gayanes.am կայքում։',
				'ru' => 'Какие способы оплаты принимаются на сайте gayanes.am?',
				'en' => 'What payment methods are accepted on gayanes.am?',
			),
			'a'        => array(
				'hy' => 'Կարող եք վճարել կանխիկ առաքիչին՝ պատվերը ստանալու պահին, կամ անկանխիկ եղանակով՝ առցանց վճարմամբ և բանկային քարտերով։ Բոլոր գները ներկայացված են ՀՀ դրամով (AMD)։',
				'ru' => 'Вы можете оплатить заказ наличными курьеру при получении или безналичным способом онлайн банковской картой. Все цены на сайте указаны в армянских драмах (AMD).',
				'en' => 'You can pay cash on delivery or via online bank card / cashless payment at checkout. All prices are listed in Armenian Drams (AMD).',
			),
		),
		array(
			'id'       => 'how-to-boil-pelmeni-khinkali',
			'category' => 'cooking',
			'featured' => false,
			'q'        => array(
				'hy' => 'Ինչպե՞ս ճիշտ եփել պելմենին, խինկալին և վարենիկները, որպեսզի չկպչեն կամ չճաքեն։',
				'ru' => 'Как правильно варить пельмени, хинкали и вареники, чтобы они не слипались и не рвались?',
				'en' => 'How should I boil pelmeni, khinkali, and vareniks so they don’t stick or tear?',
			),
			'a'        => array(
				'hy' => 'Եռացրեք առատ աղած ջուր, գցեք սառեցված կիսաֆաբրիկատները առանց հալեցնելու և զգուշորեն խառնեք փայտե կամ սիլիկոնե գդալով առաջին 30 վայրկյանում։ Եփեք միջին կրակի վրա՝ պելմենին և վարենիկները 6–8 րոպե, իսկ խինկալին՝ 10–12 րոպե, մինչև ջրի երես բարձրանալը և խմորի պատրաստ լինելը։',
				'ru' => 'Вскипятите достаточное количество подсоленной воды, опустите замороженные изделия без разморозки и аккуратно перемешайте в первые 30 секунд. Варите на среднем огне: пельмени и вареники 6–8 минут, хинкали 10–12 минут до готовности теста.',
				'en' => 'Bring a generous pot of salted water to a boil, drop in the frozen pieces without thawing, and gently stir during the first 30 seconds. Simmer on medium heat for 6–8 minutes for pelmeni and vareniks, and 10–12 minutes for khinkali.',
			),
		),
		array(
			'id'       => 'how-to-cook-dolma-springrolls',
			'category' => 'cooking',
			'featured' => false,
			'q'        => array(
				'hy' => 'Ինչպե՞ս պատրաստել սուտ տոլման, բլինչիկը, ֆալաֆելը կամ սփրինգ ռոլերը։',
				'ru' => 'Как приготовить постную долму, блинчики, фалафель и спринг-роллы?',
				'en' => 'How do I cook dolma, blini, falafel, and spring rolls?',
			),
			'a'        => array(
				'hy' => 'Սուտ տոլման դրեք կաթսայի մեջ, ավելացրեք տաք ջուր կամ տոմատի թեթև սոուս՝ մինչև կեսը, և շոգեխաշեք փակ կափարիչով 15–20 րոպե։ Բլինչիկները, ֆալաֆելը և սփրինգ ռոլերը տապակեք նախապես տաքացված բուսայուղի մեջ կամ պատրաստեք ջեռոցում / Air Fryer-ում (190°C, 12–15 րոպե)՝ խրթխրթան արդյունքի համար։',
				'ru' => 'Постную долму уложите в кастрюлю, залейте тёплой водой или лёгким томатным соусом до половины и тушите под крышкой 15–20 минут. Блинчики, фалафель и спринг-роллы обжарьте на разогретой сковороде или запеките в духовке / аэрогриле (190°C, 12–15 минут) до золотистой корочки.',
				'en' => 'Simmer dolma covered with warm water or light tomato broth for 15–20 minutes. Pan-fry blini, falafel, and spring rolls in preheated oil, or bake/air-fry at 190°C for 12–15 minutes until golden and crisp.',
			),
		),
		array(
			'id'       => 'package-sizes-servings',
			'category' => 'ordering',
			'featured' => false,
			'q'        => array(
				'hy' => 'Ի՞նչ քաշով են փաթեթավորվում ուտեստները և քանի՞ հոգու համար է բավական 1 տուփը։',
				'ru' => 'Каков вес упаковки и на сколько порций рассчитана одна пачка?',
				'en' => 'What are the package weights and how many servings are in one pack?',
			),
			'a'        => array(
				'hy' => 'Ուտեստների մեծ մասը փաթեթավորվում է 400 գրամից մինչև 1 կգ հարմար ընտանեկան տուփերով (կամ հատով՝ օրինակ խինկալի և բլինչիկ)։ Մեկ տուփը միջինում բավարար է 2–4 հոգու լիարժեք ընթրիքի համար։ Յուրաքանչյուր ապրանքի քաշն ու գինը հստակ նշված է կատալոգում։',
				'ru' => 'Большинство блюд фасуются в удобные семейные упаковки от 400 г до 1 кг (или поштучно — например, хинкали и блинчики). Одной упаковки в среднем хватает на полноценный ужин для 2–4 человек. Точный вес указан на карточке каждого товара.',
				'en' => 'Most dishes come in convenient 400g to 1kg family packs (or by piece for khinkali and blini). One pack typically serves 2–4 people for a full homemade meal. Exact weights are clearly shown on every product card.',
			),
		),
		array(
			'id'       => 'bulk-events-orders',
			'category' => 'ordering',
			'featured' => false,
			'q'        => array(
				'hy' => 'Կարո՞ղ եմ պատվիրել մեծ քանակությամբ՝ ընտանեկան միջոցառումների կամ գրասենյակի համար։',
				'ru' => 'Можно ли сделать крупный заказ для семейного мероприятия или офиса?',
				'en' => 'Can I place bulk orders for family events, gatherings, or offices?',
			),
			'a'        => array(
				'hy' => 'Իհարկե։ Մեծաքանակ կամ կորպորատիվ պատվերների համար կարող եք կապվել մեզ հետ առնվազն 1 օր առաջ՝ զանգահարելով +374 (55) 600655 կամ գրելով WhatsApp / Telegram-ով։ Մենք կպատրաստենք թարմ խմբաքանակ հատուկ Ձեր միջոցառման համար։',
				'ru' => 'Конечно! Для крупных или корпоративных заказов свяжитесь с нами минимум за 1 день по телефону +374 (55) 600655 или через WhatsApp / Telegram — мы приготовим свежую партию специально для вашего мероприятия.',
				'en' => 'Yes! For bulk or event orders, please reach out at least 1 day in advance via +374 (55) 600655 or WhatsApp / Telegram, and we will prepare a fresh batch tailored to your gathering.',
			),
		),
		array(
			'id'       => 'difference-from-supermarket',
			'category' => 'quality',
			'featured' => false,
			'q'        => array(
				'hy' => 'Ինչո՞վ է Գայանեի Խոհանոցը տարբերվում խանութային սովորական կիսաֆաբրիկատներից։',
				'ru' => 'Чем продукция «Кухни Гаяне» отличается от обычных магазинных полуфабрикатов?',
				'en' => 'How is Gayane’s Kitchen different from mass-produced supermarket frozen foods?',
			),
			'a'        => array(
				'hy' => 'Ի տարբերություն արդյունաբերական արտադրության՝ Գայանեի Խոհանոցում ամեն ինչ պատրաստվում է փոքր խմբաքանակներով և ձեռքով՝ բարակ տնական խմորով, առատ միջուկով և առանց էժան փոխարինիչների։ Համը, բույրն ու կառուցվածքը ճիշտ նույնն են, ինչ տանը պատրաստված ուտեստինը։',
				'ru' => 'В отличие от фабричного производства, на «Кухне Гаяне» всё готовится небольшими партиями и вручную — с тонким домашним тестом, щедрой начинкой и без дешёвых заменителей. Вкус и аромат получаются точно такими же, как дома.',
				'en' => 'Unlike factory-made frozen foods, everything at Gayane’s Kitchen is handcrafted daily in small batches with thin homemade dough, generous fillings, and zero cheap fillers — giving you true home-cooked taste and texture.',
			),
		),
	);

	if ( $featured_only ) {
		$items = array_values(
			array_filter(
				$items,
				static fn( array $item ): bool => ! empty( $item['featured'] )
			)
		);
	}

	$result = array();
	foreach ( $items as $item ) {
		$result[] = array(
			'id'       => $item['id'],
			'category' => $item['category'],
			'featured' => (bool) $item['featured'],
			'question' => $item['q'][ $lang ] ?? $item['q']['hy'],
			'answer'   => $item['a'][ $lang ] ?? $item['a']['hy'],
		);
	}

	return $result;
}

/**
 * Build a Schema.org FAQPage node from a list of FAQs.
 *
 * @param array<int, array<string, mixed>> $faqs FAQ items from gv2_get_faqs().
 * @return array<string, mixed>
 */
function gv2_build_faq_schema( array $faqs ): array {
	$entities = array();
	foreach ( $faqs as $faq ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $faq['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['answer'],
			),
		);
	}

	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
}
