<?php
/**
 * Trilingual (Armenian, Russian, English) UI translation layer.
 *
 * Bridges Polylang / Gayanes_Core\Multilingual when present and provides
 * complete built-in HY / RU / EN translations for all GayanesV2 UI strings.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get current active language code ('hy', 'ru', or 'en').
 */
function gv2_current_lang(): string {
	if ( class_exists( 'Gayanes_Core\Multilingual' ) ) {
		return Gayanes_Core\Multilingual::current_lang();
	}

	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( is_string( $lang ) && in_array( $lang, array( 'hy', 'ru', 'en' ), true ) ) {
			return $lang;
		}
	}

	$locale = determine_locale();
	if ( str_starts_with( $locale, 'ru' ) ) {
		return 'ru';
	}
	if ( str_starts_with( $locale, 'en' ) ) {
		return 'en';
	}

	return 'hy';
}

/**
 * Built-in trilingual dictionary for GayanesV2 UI strings.
 *
 * @return array<string, array{hy: string, ru: string}>
 */
function gv2_translations_map(): array {
	return array(
		'Skip to content' => array(
			'hy' => 'Անցնել բովանդակությանը',
			'ru' => 'Перейти к содержимому',
		),
		'Free delivery in Yerevan on orders over 15,000 AMD' => array(
			'hy' => 'Անվճար առաքում Երևանում՝ 15,000 ֏-ից սկսած պատվերների համար',
			'ru' => 'Бесплатная доставка по Еревану при заказе от 15 000 AMD',
		),
		'Home' => array(
			'hy' => 'Գլխավոր',
			'ru' => 'Главная',
		),
		'Shop' => array(
			'hy' => 'Մենյու',
			'ru' => 'Меню',
		),
		'Shop All' => array(
			'hy' => 'Ամբողջ մենյուն',
			'ru' => 'Всё меню',
		),
		'Explore Menu' => array(
			'hy' => 'Դիտել մենյուն',
			'ru' => 'Смотреть меню',
		),
		'Categories' => array(
			'hy' => 'Կատեգորիաներ',
			'ru' => 'Категории',
		),
		'All' => array(
			'hy' => 'Բոլորը',
			'ru' => 'Все',
		),
		'About Us' => array(
			'hy' => 'Մեր մասին',
			'ru' => 'О нас',
		),
		'Our Story' => array(
			'hy' => 'Մեր պատմությունը',
			'ru' => 'Наша история',
		),
		'Delivery & Payment' => array(
			'hy' => 'Առաքում և վճարում',
			'ru' => 'Доставка и оплата',
		),
		'Contact' => array(
			'hy' => 'Կապ',
			'ru' => 'Контакты',
		),
		'Search' => array(
			'hy' => 'Որոնել',
			'ru' => 'Поиск',
		),
		'Search products...' => array(
			'hy' => 'Որոնել ուտեստներ...',
			'ru' => 'Поиск блюд...',
		),
		'My Account' => array(
			'hy' => 'Իմ հաշիվը',
			'ru' => 'Мой аккаунт',
		),
		'Account' => array(
			'hy' => 'Հաշիվ',
			'ru' => 'Аккаунт',
		),
		'Your Cart' => array(
			'hy' => 'Զամբյուղ',
			'ru' => 'Корзина',
		),
		'Cart' => array(
			'hy' => 'Զամբյուղ',
			'ru' => 'Корзина',
		),
		'Open menu' => array(
			'hy' => 'Բացել մենյուն',
			'ru' => 'Открыть меню',
		),
		'Close menu' => array(
			'hy' => 'Փակել մենյուն',
			'ru' => 'Закрыть меню',
		),
		'Close' => array(
			'hy' => 'Փակել',
			'ru' => 'Закрыть',
		),
		'Homemade Armenian Semi-Finished Food' => array(
			'hy' => 'Տնական հայկական կիսաֆաբրիկատներ',
			'ru' => 'Домашние армянские полуфабрикаты',
		),
		'Authentic Armenian Dishes, Ready in Minutes' => array(
			'hy' => 'Ավանդական հայկական խոհանոց՝ պատրաստ րոպեների ընթացքում',
			'ru' => 'Аутентичная армянская кухня, готовая за минуты',
		),
		'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' => array(
			'hy' => 'Պատրաստված է ամեն օր 100% բնական բաղադրիչներից և առաքվում է թարմ վիճակում՝ Ձեր դռան մոտ։',
			'ru' => 'Готовится ежедневно из 100% натуральных продуктов с охлаждённой доставкой до вашей двери.',
		),
		'100% Natural' => array(
			'hy' => '100% Բնական',
			'ru' => '100% Натурально',
		),
		'No Preservatives' => array(
			'hy' => 'Առանց կոնսերվանտների',
			'ru' => 'Без консервантов',
		),
		'Chilled Delivery' => array(
			'hy' => 'Սառեցված առաքում',
			'ru' => 'Охлаждённая доставка',
		),
		'Best Sellers' => array(
			'hy' => 'Ամենավաճառվողները',
			'ru' => 'Хиты продаж',
		),
		'Featured Products' => array(
			'hy' => 'Առաջարկվող ուտեստներ',
			'ru' => 'Рекомендуемые блюда',
		),
		'New Arrivals' => array(
			'hy' => 'Նորույթներ',
			'ru' => 'Новинки',
		),
		'View All' => array(
			'hy' => 'Տեսնել բոլորը',
			'ru' => 'Смотреть все',
		),
		'Add' => array(
			'hy' => 'Ավելացնել',
			'ru' => 'Добавить',
		),
		'Add to Cart' => array(
			'hy' => 'Ավելացնել զամբյուղ',
			'ru' => 'В корзину',
		),
		'Select Options' => array(
			'hy' => 'Ընտրել տարբերակ',
			'ru' => 'Выбрать вариант',
		),
		'In Stock' => array(
			'hy' => 'Առկա է',
			'ru' => 'В наличии',
		),
		'Out of Stock' => array(
			'hy' => 'Առկա չէ',
			'ru' => 'Нет в наличии',
		),
		'Sale' => array(
			'hy' => 'Զեղչ',
			'ru' => 'Скидка',
		),
		'Crafted Like Home' => array(
			'hy' => 'Պատրաստված ինչպես տանը',
			'ru' => 'Сделано как дома',
		),
		'Gayane’s Kitchen brings homemade Armenian dolma, pelmeni, khinkali, blini, and seasonal delicacies straight from our kitchen to your family table.' => array(
			'hy' => 'Gayane’s Kitchen-ը Ձեր ընտանեկան սեղանին է հասցնում տնական տոլմա, պելմենի, խինկալի, բլինչիկ և սեզոնային համեղ ուտեստներ՝ պատրաստված սիրով և ավանդական բաղադրատոմսերով։',
			'ru' => 'Gayane’s Kitchen приносит домашнюю армянскую толму, пельмени, хинкали, блинчики и сезонные деликатесы прямо с нашей кухни на ваш семейный стол.',
		),
		'Fresh Local Ingredients' => array(
			'hy' => 'Թարմ տեղական բաղադրիչներ',
			'ru' => 'Свежие местные продукты',
		),
		'Family Recipes' => array(
			'hy' => 'Ընտանեկան բաղադրատոմսեր',
			'ru' => 'Семейные рецепты',
		),
		'Same-Day Yerevan Delivery' => array(
			'hy' => 'Նույն օրվա առաքում Երևանում',
			'ru' => 'Доставка в день заказа по Еревану',
		),
		'Learn More' => array(
			'hy' => 'Իմանալ ավելին',
			'ru' => 'Подробнее',
		),
		'Our Partners' => array(
			'hy' => 'Մեր գործընկերները',
			'ru' => 'Наши партнёры',
		),
		'Trusted by leading supermarkets, restaurants, and local producers across Armenia.' => array(
			'hy' => 'Մեզ վստահում են Հայաստանի առաջատար սուպերմարկետները, ռեստորանները և տեղական արտադրողները։',
			'ru' => 'Нам доверяют ведущие супермаркеты, рестораны и местные производители Армении.',
		),
		'Blog & Recipes' => array(
			'hy' => 'Բլոգ և բաղադրատոմսեր',
			'ru' => 'Блог и рецепты',
		),
		'Read Recipe' => array(
			'hy' => 'Կարդալ ավելին',
			'ru' => 'Читать далее',
		),
		'Customer Reviews' => array(
			'hy' => 'Հաճախորդների կարծիքները',
			'ru' => 'Отзывы клиентов',
		),
		'Seasonal Menu & Offers' => array(
			'hy' => 'Սեզոնային մենյու և հատուկ առաջարկներ',
			'ru' => 'Сезонное меню и предложения',
		),
		'Receive updates on new homemade dishes and seasonal specials.' => array(
			'hy' => 'Ստացեք տեղեկություններ նոր տնական ուտեստների և սեզոնային առաջարկների մասին։',
			'ru' => 'Получайте новости о новых домашних блюдах и сезонных новинках.',
		),
		'Email address' => array(
			'hy' => 'Էլ. հասցե',
			'ru' => 'Электронная почта',
		),
		'Subscribe' => array(
			'hy' => 'Բաժանորդագրվել',
			'ru' => 'Подписаться',
		),
		'Subtotal' => array(
			'hy' => 'Ենթագումար',
			'ru' => 'Подытог',
		),
		'Checkout' => array(
			'hy' => 'Ձևակերպել պատվերը',
			'ru' => 'Оформить заказ',
		),
		'View Cart' => array(
			'hy' => 'Դիտել զամբյուղը',
			'ru' => 'Открыть корзину',
		),
		'Your cart is empty' => array(
			'hy' => 'Ձեր զամբյուղը դատարկ է',
			'ru' => 'Ваша корзина пуста',
		),
		'Add some homemade Armenian dishes to get started.' => array(
			'hy' => 'Ավելացրեք տնական հայկական ուտեստներ՝ պատվերը սկսելու համար։',
			'ru' => 'Добавьте домашние армянские блюда, чтобы начать заказ.',
		),
		'Free delivery unlocked!' => array(
			'hy' => 'Անվճար առաքումը ակտիվացված է',
			'ru' => 'Бесплатная доставка активирована!',
		),
		'Add %s more for free delivery' => array(
			'hy' => 'Ավելացրեք ևս %s անվճար առաքման համար',
			'ru' => 'Добавьте ещё %s для бесплатной доставки',
		),
		'Loading more dishes...' => array(
			'hy' => 'Բեռնվում են այլ ուտեստներ...',
			'ru' => 'Загружаем ещё блюда...',
		),
		'All dishes loaded' => array(
			'hy' => 'Բոլոր ուտեստները բեռնված են',
			'ru' => 'Все блюда загружены',
		),
		'No dishes found.' => array(
			'hy' => 'Ուտեստներ չեն գտնվել։',
			'ru' => 'Блюда не найдены.',
		),
		'Customer Care' => array(
			'hy' => 'Հաճախորդների սպասարկում',
			'ru' => 'Поддержка клиентов',
		),
		'FAQ' => array(
			'hy' => 'Հաճախ տրվող հարցեր',
			'ru' => 'Частые вопросы',
		),
		'Legal' => array(
			'hy' => 'Իրավական',
			'ru' => 'Правовая информация',
		),
		'Privacy Policy' => array(
			'hy' => 'Գաղտնիության քաղաքականություն',
			'ru' => 'Политика конфиденциальности',
		),
		'Terms & Conditions' => array(
			'hy' => 'Օգտագործման պայմաններ',
			'ru' => 'Условия использования',
		),
		'Language selector' => array(
			'hy' => 'Լեզվի ընտրություն',
			'ru' => 'Выбор языка',
		),
		'Breadcrumb' => array(
			'hy' => 'Նավիգացիա',
			'ru' => 'Навигация',
		),
		'Remove item' => array(
			'hy' => 'Հեռացնել',
			'ru' => 'Удалить',
		),
		'Decrease quantity' => array(
			'hy' => 'Նվազեցնել քանակը',
			'ru' => 'Уменьшить количество',
		),
		'Increase quantity' => array(
			'hy' => 'Ավելացնել քանակը',
			'ru' => 'Увеличить количество',
		),
		'Added to cart' => array(
			'hy' => 'Ավելացվեց զամբյուղ',
			'ru' => 'Добавлено в корзину',
		),
		'Updated quantity' => array(
			'hy' => 'Քանակը թարմացվեց',
			'ru' => 'Количество обновлено',
		),
		'Could not update cart' => array(
			'hy' => 'Չհաջողվեց թարմացնել զամբյուղը',
			'ru' => 'Не удалось обновить корзину',
		),
		'Sort by' => array(
			'hy' => 'Դասավորել',
			'ru' => 'Сортировать',
		),
		'Default' => array(
			'hy' => 'Հիմնական',
			'ru' => 'По умолчанию',
		),
		'Popularity' => array(
			'hy' => 'Հայտնիությամբ',
			'ru' => 'По популярности',
		),
		'Newest' => array(
			'hy' => 'Նորույթներ',
			'ru' => 'Новинки',
		),
		'Price: Low to High' => array(
			'hy' => 'Գինը՝ ցածրից բարձր',
			'ru' => 'Цена: по возрастанию',
		),
		'Price: High to Low' => array(
			'hy' => 'Գինը՝ բարձրից ցածր',
			'ru' => 'Цена: по убыванию',
		),
		'Please enter a valid phone number (e.g. +374 99 12 34 56).' => array(
			'hy' => 'Խնդրում ենք մուտքագրել ճիշտ հեռախոսահամար (օր.՝ +374 99 12 34 56)։',
			'ru' => 'Пожалуйста, введите корректный номер телефона (например, +374 99 12 34 56).',
		),
		'Deal of the Day' => array(
			'hy' => 'Օրվա առաջարկ',
			'ru' => 'Товар дня',
		),
		'Ends in' => array(
			'hy' => 'Ակցիան ավարտվում է',
			'ru' => 'До конца акции',
		),
		'View Dish' => array(
			'hy' => 'Դիտել ուտեստը',
			'ru' => 'Подробнее',
		),
		'Crafted with Love, Just Like Home' => array(
			'hy' => 'Սիրով պատրաստված՝ ինչպես տանը',
			'ru' => 'Приготовлено с любовью, как дома',
		),
		'From our family kitchen in Yerevan to your table — handcrafted Armenian semi-finished dishes made daily from 100% natural local ingredients.' => array(
			'hy' => 'Մեր ընտանեկան խոհանոցից՝ Ձեր սեղանին. ամեն օր ձեռքով պատրաստվող տնական հայկական կիսաֆաբրիկատներ՝ բացառապես 100% բնական և տեղական մթերքներից։',
			'ru' => 'Из нашей семейной кухни на ваш стол — домашние армянские полуфабрикаты ручной работы, приготовленные ежедневно из 100% натуральных местных продуктов.',
		),
		'Natural Ingredients' => array(
			'hy' => 'Բնական բաղադրիչներ',
			'ru' => 'Натуральные продукты',
		),
		'Preservatives & Additives' => array(
			'hy' => 'Կոնսերվանտներ և հավելումներ',
			'ru' => 'Консервантов и добавок',
		),
		'Handcrafted Daily' => array(
			'hy' => 'Ամենօրյա թարմ պատրաստում',
			'ru' => 'Ежедневное приготовление',
		),
		'Free Yerevan Delivery' => array(
			'hy' => 'Անվճար առաքում Երևանում',
			'ru' => 'Бесплатная доставка',
		),
		'Why Gayane’s Kitchen' => array(
			'hy' => 'Ինչո՞ւ Գայանեի Խոհանոց',
			'ru' => 'Почему Кухня Гаяне',
		),
		'Tradition Meets Modern Convenience' => array(
			'hy' => 'Ավանդույթի համը՝ ժամանակակից հարմարավետությամբ',
			'ru' => 'Вкус традиций и современное удобство',
		),
		'We started Gayane’s Kitchen with a simple belief: busy days should never mean compromising on real, wholesome homemade food. Every dolma, pelmeni, khinkali, and blini is shaped by hand following time-honored family recipes.' => array(
			'hy' => 'Գայանեի Խոհանոցը ստեղծվել է մեկ պարզ գաղափարի շուրջ. զբաղված առօրյան չպետք է զրկի Ձեր ընտանիքը իրական, առողջ և տնական սննդից։ Յուրաքանչյուր տոլմա, պելմենի, խինկալի և բլինչիկ պատրաստվում է ձեռքով՝ պահպանելով ընտանեկան ավանդական բաղադրատոմսերը։',
			'ru' => 'Мы создали «Кухню Гаяне» с простой верой: насыщенный ритм жизни не должен лишать семью настоящей домашней еды. Каждая долма, пельмени, хинкали и блинчики лепятся вручную по проверенным семейным рецептам.',
		),
		'We carefully select fresh farm meats, aromatic Armenian herbs, and natural dairy every morning, then gently chill our dishes so you can serve a warm, homemade dinner in just minutes.' => array(
			'hy' => 'Ամեն առավոտ մենք ընտրում ենք թարմ տեղական միս, անուշաբույր հայկական կանաչիներ և բնական կաթնամթերք, այնուհետև խնամքով սառեցնում պատրաստի ուտեստները, որպեսզի Դուք կարողանաք րոպեների ընթացքում մատուցել տաք և համեղ ընթրիք։',
			'ru' => 'Каждое утро мы отбираем свежее фермерское мясо, ароматную армянскую зелень и натуральные молочные продукты, а затем бережно охлаждаем блюда, чтобы вы могли подать горячий домашний ужин за считанные минуты.',
		),
		'In our kitchen, every dish is prepared as if we are cooking for our own family.' => array(
			'hy' => '«Մեր խոհանոցում յուրաքանչյուր ուտեստ պատրաստվում է այնպես, կարծես պատրաստում ենք մեր սեփական ընտանիքի համար»։',
			'ru' => '«На нашей кухне каждое блюдо готовится так, словно мы готовим для собственной семьи».',
		),
		'Gayane, Founder & Chef' => array(
			'hy' => 'Գայանե՝ հիմնադիր և խոհարար',
			'ru' => 'Гаяне, основатель и шеф-повар',
		),
		'Our Kitchen Standards' => array(
			'hy' => 'Մեր խոհանոցի սկզբունքները',
			'ru' => 'Стандарты нашей кухни',
		),
		'Selected Farm Produce' => array(
			'hy' => 'Ընտրված տեղական մթերք',
			'ru' => 'Отборные фермерские продукты',
		),
		'Only fresh meat, seasonal vegetables, and aromatic greens from trusted Armenian suppliers.' => array(
			'hy' => 'Միայն թարմ միս, սեզոնային բանջարեղեն և բուրավետ կանաչիներ՝ վստահելի հայկական մատակարարներից։',
			'ru' => 'Только свежее мясо, сезонные овощи и ароматная зелень от проверенных армянских поставщиков.',
		),
		'Authentic Family Recipes' => array(
			'hy' => 'Ընտանեկան բաղադրատոմսեր',
			'ru' => 'Аутентичные семейные рецепты',
		),
		'Hand-rolled dough and balanced spices passed down through generations without shortcuts.' => array(
			'hy' => 'Ձեռքով բացված խմոր և հավասարակշռված համեմունքներ՝ սերնդեսերունդ փոխանցված բաղադրատոմսերով։',
			'ru' => 'Тесто ручной раскатки и сбалансированные специи по рецептам, передаваемым из поколения в поколение.',
		),
		'Clean & Certified Kitchen' => array(
			'hy' => 'Մաքուր և վերահսկվող խոհանոց',
			'ru' => 'Чистота и контроль качества',
		),
		'Strict hygiene standards at our Davtashen kitchen ensure safety and consistency in every batch.' => array(
			'hy' => 'Դավթաշենում գտնվող մեր խոհանոցում պահպանվում են հիգիենայի և որակի վերահսկման ամենաբարձր չափանիշները։',
			'ru' => 'Строгие стандарты гигиены и контроля качества на нашей кухне в Давташене гарантируют безупречность каждой партии.',
		),
		'Vegan & Fasting Friendly' => array(
			'hy' => 'Պահքի և վեգան ընտրանի',
			'ru' => 'Постные и веганские блюда',
		),
		'Delicious plant-based falafel, potato and spinach vareniks, and veggie spring rolls crafted with care.' => array(
			'hy' => 'Հատուկ սիրով պատրաստված բուսական ուտեստներ՝ ֆալաֆել, կարտոֆիլով ու սպանախով վարենիկներ, բանջարեղենով սփրինգ ռոլեր։',
			'ru' => 'Вкусные растительные блюда: фалафель, вареники с картофелем и шпинатом, овощные спринг-роллы.',
		),
		'From Our Kitchen to Your Table' => array(
			'hy' => 'Մեր խոհանոցից՝ Ձեր սեղանին',
			'ru' => 'С нашей кухни — к вашему столу',
		),
		'Every morning our chefs prepare fresh fillings and hand-fold each piece with care.' => array(
			'hy' => 'Ամեն առավոտ մեր խոհարարները պատրաստում են թարմ միջուկը և խնամքով ձևավորում յուրաքանչյուր կտորը։',
			'ru' => 'Каждое утро наши повара готовят свежую начинку и бережно лепят каждое изделие.',
		),
		'Gentle Shock Chilling' => array(
			'hy' => 'Խնամքով սառեցում',
			'ru' => 'Бережное охлаждение',
		),
		'Rapid chilling locks in natural taste, juiciness, and nutrients without any preservatives.' => array(
			'hy' => 'Արագ և ճիշտ սառեցումը պահպանում է ուտեստի բնական համը, հյութեղությունն ու օգտակարությունը՝ առանց հավելումների։',
			'ru' => 'Быстрое и правильное охлаждение сохраняет натуральный вкус, сочность и пользу без единого консерванта.',
		),
		'Chilled Delivery in Yerevan' => array(
			'hy' => 'Արագ առաքում Երևանում',
			'ru' => 'Быстрая доставка по Еревану',
		),
		'Delivered straight to your home in temperature-controlled packaging — ready to cook in minutes.' => array(
			'hy' => 'Առաքում ենք Ձեր տուն հատուկ ջերմապաշտպան փաթեթավորմամբ՝ պատրաստ եփելու ընդամենը րոպեների ընթացքում։',
			'ru' => 'Доставляем к вашей двери в термоупаковке — достаточно нескольких минут, чтобы подать к столу.',
		),
		'Ready for a Homemade Dinner in Minutes?' => array(
			'hy' => 'Պատրաստե՞ք տնական ընթրիք րոպեների ընթացքում',
			'ru' => 'Готовы к домашнему ужину за минуты?',
		),
		'Visit us in Davtashen or order online with same-day delivery across Yerevan.' => array(
			'hy' => 'Այցելեք մեզ Դավթաշենում կամ պատվիրեք առցանց՝ Երևանի ողջ տարածքում նույն օրվա առաքմամբ։',
			'ru' => 'Загляните к нам в Давташен или закажите онлайн с доставкой по всему Еревану.',
		),
		'Yerevan, Davtashen 2nd dist., 37' => array(
			'hy' => 'ք․ Երևան, Դավթաշեն 2-րդ թաղ., 37',
			'ru' => 'г. Ереван, Давташен 2-й кв., 37',
		),
	);
}

/**
 * Translate a UI string into the active language (hy, ru, en).
 *
 * @param string $text Source English string.
 */
function gv2_t( string $text ): string {
	$lang = gv2_current_lang();

	// Allow Polylang string translation override if registered.
	if ( function_exists( 'pll__' ) ) {
		$translated = pll__( $text );
		if ( is_string( $translated ) && '' !== $translated && $translated !== $text ) {
			return $translated;
		}
	}

	if ( 'en' !== $lang ) {
		$map = gv2_translations_map();
		if ( isset( $map[ $text ][ $lang ] ) ) {
			return $map[ $text ][ $lang ];
		}
	}

	if ( class_exists( 'Gayanes_Core\Multilingual' ) ) {
		return Gayanes_Core\Multilingual::t( $text );
	}

	return $text;
}

/**
 * Render trilingual language switcher (ՀԱՅ | РУС | ENG).
 */
function gv2_render_language_switcher(): void {
	if ( class_exists( 'Gayanes_Core\Multilingual' ) ) {
		$items = Gayanes_Core\Multilingual::get_switcher_items();
	} elseif ( function_exists( 'pll_the_languages' ) ) {
		$raw   = pll_the_languages( array( 'raw' => 1 ) );
		$items = array();
		if ( is_array( $raw ) ) {
			$labels = array( 'hy' => 'ՀԱՅ', 'ru' => 'РУС', 'en' => 'ENG' );
			foreach ( $raw as $slug => $data ) {
				$items[] = array(
					'code'    => (string) $slug,
					'label'   => $labels[ $slug ] ?? strtoupper( (string) $slug ),
					'name'    => (string) ( $data['name'] ?? $slug ),
					'url'     => (string) ( $data['url'] ?? home_url( '/' ) ),
					'current' => ! empty( $data['current_lang'] ),
				);
			}
		}
	} else {
		$cur   = gv2_current_lang();
		$items = array(
			array( 'code' => 'hy', 'label' => 'ՀԱՅ', 'name' => 'Հայերեն', 'url' => home_url( '/' ), 'current' => 'hy' === $cur ),
			array( 'code' => 'ru', 'label' => 'РУС', 'name' => 'Русский', 'url' => home_url( '/ru/' ), 'current' => 'ru' === $cur ),
			array( 'code' => 'en', 'label' => 'ENG', 'name' => 'English', 'url' => home_url( '/en/' ), 'current' => 'en' === $cur ),
		);
	}
	?>
	<nav class="gv2-lang-pill" aria-label="<?php echo esc_attr( gv2_t( 'Language selector' ) ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<a
				href="<?php echo esc_url( $item['url'] ); ?>"
				class="gv2-lang-pill__item<?php echo ! empty( $item['current'] ) ? ' is-active' : ''; ?>"
				hreflang="<?php echo esc_attr( $item['code'] ); ?>"
				lang="<?php echo esc_attr( $item['code'] ); ?>"
				<?php echo ! empty( $item['current'] ) ? 'aria-current="page"' : ''; ?>
				title="<?php echo esc_attr( $item['name'] ); ?>"
			>
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

