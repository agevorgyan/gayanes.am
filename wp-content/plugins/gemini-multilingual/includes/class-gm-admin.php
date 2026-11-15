<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Admin {

	private GM_Languages $languages;
	private GM_Translations $translations;
	private GM_Gemini_Client $gemini;
	private GM_Translator $translator;
	private GM_String_Translator $strings;

	public function __construct(
		GM_Languages $languages,
		GM_Translations $translations,
		GM_Gemini_Client $gemini,
		GM_Translator $translator,
		GM_String_Translator $strings
	) {
		$this->languages    = $languages;
		$this->translations = $translations;
		$this->gemini       = $gemini;
		$this->translator   = $translator;
		$this->strings      = $strings;

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_post_actions' ) );

		// Metabox on post/page/product edit screens.
		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_post_language_meta' ), 10, 2 );

		// List table language columns.
		add_action( 'admin_init', array( $this, 'register_list_table_columns' ) );

		// Taxonomy term language fields.
		add_action( 'admin_init', array( $this, 'register_taxonomy_hooks' ) );

		// AJAX endpoints for 1-click AI translation, connection test, model refresh, string scan & batch translate.
		add_action( 'wp_ajax_gm_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_gm_refresh_models', array( $this, 'ajax_refresh_models' ) );
		add_action( 'wp_ajax_gm_translate_post', array( $this, 'ajax_translate_post' ) );
		add_action( 'wp_ajax_gm_fix_post_language', array( $this, 'ajax_fix_post_language' ) );
		add_action( 'wp_ajax_gm_translate_term', array( $this, 'ajax_translate_term' ) );
		add_action( 'wp_ajax_gm_scan_package', array( $this, 'ajax_scan_package' ) );
		add_action( 'wp_ajax_gm_save_string', array( $this, 'ajax_save_string' ) );
		add_action( 'wp_ajax_gm_ai_translate_strings_batch', array( $this, 'ajax_ai_translate_strings_batch' ) );
		add_action( 'wp_ajax_gm_get_untranslated_posts', array( $this, 'ajax_get_untranslated_posts' ) );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'Gemini Multilingual', 'gemini-multilingual' ),
			__( 'Gemini Multilingual', 'gemini-multilingual' ),
			'manage_options',
			'gemini-multilingual',
			array( $this, 'render_admin_page' ),
			'dashicons-translation',
			58
		);
	}

	public function enqueue_assets( string $hook ): void {
		$screen = get_current_screen();
		$is_gm  = str_contains( $hook, 'gemini-multilingual' ) || ( $screen && in_array( $screen->base, array( 'post', 'edit', 'term', 'edit-tags' ), true ) );
		if ( ! $is_gm ) {
			return;
		}

		wp_enqueue_style( 'gm-admin', GM_PLUGIN_URL . 'assets/admin.css', array(), GM_VERSION );
		wp_enqueue_script( 'gm-admin', GM_PLUGIN_URL . 'assets/admin.js', array( 'jquery' ), GM_VERSION, true );
		wp_localize_script(
			'gm-admin',
			'GM_Admin_Vars',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gm_admin_nonce' ),
				'i18n'    => array(
					'translating' => __( 'Translating with Gemini AI...', 'gemini-multilingual' ),
					'done'        => __( 'Done!', 'gemini-multilingual' ),
					'error'       => __( 'Error:', 'gemini-multilingual' ),
					'testing'     => __( 'Testing connection...', 'gemini-multilingual' ),
					'scanning'    => __( 'Scanning files...', 'gemini-multilingual' ),
				),
			)
		);
	}

	public function handle_post_actions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['gm_action'] ) && check_admin_referer( 'gm_settings_action', 'gm_nonce' ) ) {
			$action = sanitize_key( wp_unslash( $_POST['gm_action'] ) );

			if ( 'save_settings' === $action ) {
				$raw      = wp_unslash( $_POST['gm_settings'] ?? array() );
				$settings = GM_Plugin::get_settings();

				$settings['gemini_api_key']        = sanitize_text_field( $raw['gemini_api_key'] ?? '' );
				$settings['gemini_model']          = sanitize_text_field( $raw['gemini_model'] ?? 'gemini-3.8-flash' );
				$settings['gemini_custom_model']   = sanitize_text_field( $raw['gemini_custom_model'] ?? '' );
				$settings['url_mode']              = in_array( $raw['url_mode'] ?? 'prefix', array( 'prefix', 'query' ), true ) ? $raw['url_mode'] : 'prefix';
				$settings['hide_default_lang_url'] = ! empty( $raw['hide_default_lang_url'] );
				$settings['translate_slugs']       = ! empty( $raw['translate_slugs'] );
				$settings['auto_translate_terms']  = ! empty( $raw['auto_translate_terms'] );
				$settings['wc_sync_stock_price']   = ! empty( $raw['wc_sync_stock_price'] );
				$settings['wc_sync_media']         = ! empty( $raw['wc_sync_media'] );
				$settings['wc_sync_attributes']    = ! empty( $raw['wc_sync_attributes'] );
				$settings['custom_prompt']         = sanitize_textarea_field( $raw['custom_prompt'] ?? '' );

				if ( ! empty( $raw['post_types'] ) && is_array( $raw['post_types'] ) ) {
					$settings['post_types'] = array_map( 'sanitize_key', $raw['post_types'] );
				}

				update_option( 'gm_settings', $settings );
				flush_rewrite_rules();
				wp_safe_redirect( add_query_arg( array( 'page' => 'gemini-multilingual', 'tab' => 'settings', 'gm_msg' => 'saved' ), admin_url( 'admin.php' ) ) );
				exit;
			}

			if ( 'add_language' === $action ) {
				$catalog_slug = sanitize_key( wp_unslash( $_POST['catalog_lang'] ?? '' ) );
				$catalog      = GM_Languages::get_catalog();

				if ( '' !== $catalog_slug && isset( $catalog[ $catalog_slug ] ) ) {
					$data = $catalog[ $catalog_slug ];
				} else {
					$data = array(
						'slug'   => sanitize_key( wp_unslash( $_POST['lang_slug'] ?? '' ) ),
						'name'   => sanitize_text_field( wp_unslash( $_POST['lang_name'] ?? '' ) ),
						'locale' => sanitize_text_field( wp_unslash( $_POST['lang_locale'] ?? '' ) ),
						'flag'   => sanitize_text_field( wp_unslash( $_POST['lang_flag'] ?? '🌐' ) ),
						'rtl'    => ! empty( $_POST['lang_rtl'] ),
					);
				}

				$this->languages->save_language( $data );
				flush_rewrite_rules();
				wp_safe_redirect( add_query_arg( array( 'page' => 'gemini-multilingual', 'tab' => 'languages', 'gm_msg' => 'lang_added' ), admin_url( 'admin.php' ) ) );
				exit;
			}
		}

		// Quick GET language actions.
		if ( isset( $_GET['page'], $_GET['gm_lang_action'], $_GET['slug'] ) && 'gemini-multilingual' === $_GET['page'] && check_admin_referer( 'gm_lang_action_nonce' ) ) {
			$act  = sanitize_key( wp_unslash( $_GET['gm_lang_action'] ) );
			$slug = sanitize_key( wp_unslash( $_GET['slug'] ) );
			if ( 'set_default' === $act ) {
				$this->languages->set_default_language( $slug );
			} elseif ( 'delete' === $act ) {
				$this->languages->delete_language( $slug );
			}
			flush_rewrite_rules();
			wp_safe_redirect( add_query_arg( array( 'page' => 'gemini-multilingual', 'tab' => 'languages' ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	public function render_admin_page(): void {
		$tab = sanitize_key( $_GET['tab'] ?? 'languages' );
		?>
		<div class="wrap gm-wrap">
			<h1>
				<span class="dashicons dashicons-translation"></span>
				<?php esc_html_e( 'Gemini AI Multilingual', 'gemini-multilingual' ); ?>
				<span class="gm-badge">v<?php echo esc_html( GM_VERSION ); ?> &middot; <?php echo esc_html( GM_Plugin::get_active_model() ); ?></span>
			</h1>

			<?php if ( isset( $_GET['gm_msg'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Changes saved successfully.', 'gemini-multilingual' ); ?></p></div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper gm-tabs">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=gemini-multilingual&tab=languages' ) ); ?>" class="nav-tab <?php echo 'languages' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Languages', 'gemini-multilingual' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=gemini-multilingual&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'AI & Settings', 'gemini-multilingual' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=gemini-multilingual&tab=strings' ) ); ?>" class="nav-tab <?php echo 'strings' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Themes & Plugins Translator', 'gemini-multilingual' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=gemini-multilingual&tab=bulk' ) ); ?>" class="nav-tab <?php echo 'bulk' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Bulk AI Translator', 'gemini-multilingual' ); ?></a>
			</nav>

			<div class="gm-tab-content">
				<?php
				if ( 'settings' === $tab ) {
					$this->render_settings_tab();
				} elseif ( 'strings' === $tab ) {
					$this->render_strings_tab();
				} elseif ( 'bulk' === $tab ) {
					$this->render_bulk_tab();
				} else {
					$this->render_languages_tab();
				}
				?>
			</div>
		</div>
		<?php
	}

	private function render_languages_tab(): void {
		$langs   = $this->languages->get_languages( false );
		$catalog = GM_Languages::get_catalog();
		?>
		<div class="gm-grid-2">
			<div class="gm-card">
				<h2><?php esc_html_e( 'Configured Languages', 'gemini-multilingual' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Flag', 'gemini-multilingual' ); ?></th>
							<th><?php esc_html_e( 'Name', 'gemini-multilingual' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'gemini-multilingual' ); ?></th>
							<th><?php esc_html_e( 'Locale', 'gemini-multilingual' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'gemini-multilingual' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $langs as $lang ) : ?>
							<tr>
								<td><?php echo esc_html( $lang['flag'] ); ?></td>
								<td>
									<strong><?php echo esc_html( $lang['name'] ); ?></strong>
									<?php if ( ! empty( $lang['is_default'] ) ) : ?>
										<span class="gm-pill gm-pill-primary"><?php esc_html_e( 'Default', 'gemini-multilingual' ); ?></span>
									<?php endif; ?>
								</td>
								<td><code><?php echo esc_html( $lang['slug'] ); ?></code></td>
								<td><code><?php echo esc_html( $lang['locale'] ); ?></code></td>
								<td>
									<?php if ( empty( $lang['is_default'] ) ) : ?>
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=gemini-multilingual&gm_lang_action=set_default&slug=' . $lang['slug'] ), 'gm_lang_action_nonce' ) ); ?>" class="button button-small"><?php esc_html_e( 'Make Default', 'gemini-multilingual' ); ?></a>
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=gemini-multilingual&gm_lang_action=delete&slug=' . $lang['slug'] ), 'gm_lang_action_nonce' ) ); ?>" class="button button-small gm-danger" onclick="return confirm('Delete this language?');"><?php esc_html_e( 'Delete', 'gemini-multilingual' ); ?></a>
									<?php else : ?>
										<span class="description"><?php esc_html_e( 'Primary', 'gemini-multilingual' ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="description" style="margin-top:12px;">
					<?php esc_html_e( 'Language Switcher Shortcode:', 'gemini-multilingual' ); ?> <code>[gm_language_switcher]</code>
				</p>
			</div>

			<div class="gm-card">
				<h2><?php esc_html_e( 'Add Language', 'gemini-multilingual' ); ?></h2>
				<form method="post">
					<?php wp_nonce_field( 'gm_settings_action', 'gm_nonce' ); ?>
					<input type="hidden" name="gm_action" value="add_language" />
					<table class="form-table">
						<tr>
							<th><label for="catalog_lang"><?php esc_html_e( 'Quick Select', 'gemini-multilingual' ); ?></label></th>
							<td>
								<select name="catalog_lang" id="catalog_lang" class="regular-text">
									<option value=""><?php esc_html_e( '-- Choose a language or enter custom below --', 'gemini-multilingual' ); ?></option>
									<?php foreach ( $catalog as $c ) : ?>
										<option value="<?php echo esc_attr( $c['slug'] ); ?>"><?php echo esc_html( $c['flag'] . ' ' . $c['name'] . ' (' . $c['locale'] . ')' ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="lang_name"><?php esc_html_e( 'Custom Name', 'gemini-multilingual' ); ?></label></th>
							<td><input type="text" name="lang_name" id="lang_name" class="regular-text" placeholder="e.g. Հայերեն" /></td>
						</tr>
						<tr>
							<th><label for="lang_slug"><?php esc_html_e( 'Custom Slug', 'gemini-multilingual' ); ?></label></th>
							<td><input type="text" name="lang_slug" id="lang_slug" class="small-text" placeholder="hy" /></td>
						</tr>
						<tr>
							<th><label for="lang_locale"><?php esc_html_e( 'Custom Locale', 'gemini-multilingual' ); ?></label></th>
							<td><input type="text" name="lang_locale" id="lang_locale" class="regular-text" placeholder="hy" /></td>
						</tr>
						<tr>
							<th><label for="lang_flag"><?php esc_html_e( 'Flag Emoji', 'gemini-multilingual' ); ?></label></th>
							<td><input type="text" name="lang_flag" id="lang_flag" class="small-text" placeholder="🇦🇲" /></td>
						</tr>
					</table>
					<?php submit_button( __( 'Add Language', 'gemini-multilingual' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	private function render_settings_tab(): void {
		$settings      = GM_Plugin::get_settings();
		$models        = GM_Gemini_Client::get_default_models();
		$cached_models = get_option( 'gm_cached_models', array() );
		if ( is_array( $cached_models ) && ! empty( $cached_models ) ) {
			// Merge live-fetched models while keeping curated ones at top.
			$models = array_merge( $models, $cached_models );
			unset( $models['custom'] );
			$models['custom'] = 'Custom Model ID...';
		}
		?>
		<form method="post" class="gm-card">
			<?php wp_nonce_field( 'gm_settings_action', 'gm_nonce' ); ?>
			<input type="hidden" name="gm_action" value="save_settings" />

			<h2><?php esc_html_e( 'Google Gemini AI Configuration', 'gemini-multilingual' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="gm_api_key"><?php esc_html_e( 'Gemini API Key', 'gemini-multilingual' ); ?></label></th>
					<td>
						<input type="password" id="gm_api_key" name="gm_settings[gemini_api_key]" value="<?php echo esc_attr( $settings['gemini_api_key'] ); ?>" class="regular-text" autocomplete="off" />
						<button type="button" class="button" id="gm-test-connection"><?php esc_html_e( 'Test Connection', 'gemini-multilingual' ); ?></button>
						<span id="gm-test-result" class="gm-inline-status"></span>
						<p class="description"><?php esc_html_e( 'Get your API key from Google AI Studio (aistudio.google.com).', 'gemini-multilingual' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="gm_model"><?php esc_html_e( 'Gemini Model', 'gemini-multilingual' ); ?></label></th>
					<td>
						<select id="gm_model" name="gm_settings[gemini_model]" class="regular-text">
							<?php foreach ( $models as $id => $label ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $settings['gemini_model'], $id ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button" id="gm-refresh-models"><?php esc_html_e( 'Refresh Models from API', 'gemini-multilingual' ); ?></button>
						<span id="gm-refresh-result" class="gm-inline-status"></span>
						<div id="gm-custom-model-wrap" style="margin-top:8px; <?php echo 'custom' === $settings['gemini_model'] ? '' : 'display:none;'; ?>">
							<input type="text" name="gm_settings[gemini_custom_model]" value="<?php echo esc_attr( $settings['gemini_custom_model'] ); ?>" class="regular-text" placeholder="e.g. gemini-4-argon" />
						</div>
					</td>
				</tr>
				<tr>
					<th><label for="gm_custom_prompt"><?php esc_html_e( 'Translation Style & Glossary Rules', 'gemini-multilingual' ); ?></label></th>
					<td>
						<textarea id="gm_custom_prompt" name="gm_settings[custom_prompt]" rows="3" class="large-text"><?php echo esc_textarea( $settings['custom_prompt'] ); ?></textarea>
					</td>
				</tr>
			</table>

			<hr />
			<h2><?php esc_html_e( 'URL & Content Routing', 'gemini-multilingual' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'URL Format', 'gemini-multilingual' ); ?></th>
					<td>
						<label><input type="radio" name="gm_settings[url_mode]" value="prefix" <?php checked( $settings['url_mode'], 'prefix' ); ?> /> <?php esc_html_e( 'Directory prefix (/en/shop/, /ru/shop/)', 'gemini-multilingual' ); ?></label><br />
						<label><input type="radio" name="gm_settings[url_mode]" value="query" <?php checked( $settings['url_mode'], 'query' ); ?> /> <?php esc_html_e( 'Query parameter (?lang=en)', 'gemini-multilingual' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Options', 'gemini-multilingual' ); ?></th>
					<td>
						<label><input type="checkbox" name="gm_settings[hide_default_lang_url]" value="1" <?php checked( ! empty( $settings['hide_default_lang_url'] ) ); ?> /> <?php esc_html_e( 'Hide default language code from URL', 'gemini-multilingual' ); ?></label><br />
						<label><input type="checkbox" name="gm_settings[translate_slugs]" value="1" <?php checked( ! empty( $settings['translate_slugs'] ) ); ?> /> <?php esc_html_e( 'Translate post/product URL slugs with AI', 'gemini-multilingual' ); ?></label><br />
						<label><input type="checkbox" name="gm_settings[auto_translate_terms]" value="1" <?php checked( ! empty( $settings['auto_translate_terms'] ) ); ?> /> <?php esc_html_e( 'Auto-translate linked categories, tags & attributes when translating posts/products', 'gemini-multilingual' ); ?></label>
					</td>
				</tr>
			</table>

			<hr />
			<h2><?php esc_html_e( 'WooCommerce Synchronization', 'gemini-multilingual' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Product Sync', 'gemini-multilingual' ); ?></th>
					<td>
						<label><input type="checkbox" name="gm_settings[wc_sync_stock_price]" value="1" <?php checked( ! empty( $settings['wc_sync_stock_price'] ) ); ?> /> <?php esc_html_e( 'Sync SKU, Prices, Stock, Dimensions & Tax across product translations', 'gemini-multilingual' ); ?></label><br />
						<label><input type="checkbox" name="gm_settings[wc_sync_media]" value="1" <?php checked( ! empty( $settings['wc_sync_media'] ) ); ?> /> <?php esc_html_e( 'Sync Product Gallery images', 'gemini-multilingual' ); ?></label><br />
						<label><input type="checkbox" name="gm_settings[wc_sync_attributes]" value="1" <?php checked( ! empty( $settings['wc_sync_attributes'] ) ); ?> /> <?php esc_html_e( 'Sync Product Attributes & Variations', 'gemini-multilingual' ); ?></label>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save Settings', 'gemini-multilingual' ) ); ?>
		</form>
		<?php
	}

	private function render_strings_tab(): void {
		$packages        = $this->strings->get_scannable_packages();
		$langs           = $this->languages->get_languages( true );
		$selected_domain = sanitize_text_field( $_GET['domain'] ?? '' );
		$selected_lang   = sanitize_key( $_GET['lang'] ?? $this->languages->get_default_slug() );
		$selected_status = sanitize_key( $_GET['status'] ?? '' );
		$search          = sanitize_text_field( $_GET['s'] ?? '' );
		$paged           = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$limit           = 40;
		$offset          = ( $paged - 1 ) * $limit;

		$result = $this->strings->query_strings(
			array(
				'domain' => $selected_domain,
				'lang'   => $selected_lang,
				'status' => $selected_status,
				'search' => $search,
				'limit'  => $limit,
				'offset' => $offset,
			)
		);
		?>
		<div class="gm-card">
			<h2><?php esc_html_e( 'Scan Theme or Plugin for Translatable Strings', 'gemini-multilingual' ); ?></h2>
			<div class="gm-toolbar">
				<select id="gm-package-select" class="regular-text">
					<?php foreach ( $packages as $pkg ) : ?>
						<option value="<?php echo esc_attr( $pkg['id'] ); ?>" data-domain="<?php echo esc_attr( $pkg['domain'] ); ?>">
							[<?php echo esc_html( strtoupper( $pkg['type'] ) ); ?>] <?php echo esc_html( $pkg['name'] ); ?> (<?php echo esc_html( $pkg['domain'] ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
				<button type="button" class="button button-primary" id="gm-scan-package-btn"><?php esc_html_e( 'Scan Strings', 'gemini-multilingual' ); ?></button>
				<span id="gm-scan-status" class="gm-inline-status"></span>
			</div>
		</div>

		<div class="gm-card" style="margin-top:16px;">
			<h2><?php esc_html_e( 'Translate Theme & Plugin Strings with Gemini AI', 'gemini-multilingual' ); ?></h2>

			<form method="get" class="gm-toolbar" style="margin-bottom:12px;">
				<input type="hidden" name="page" value="gemini-multilingual" />
				<input type="hidden" name="tab" value="strings" />
				<select name="domain" id="gm-filter-domain">
					<option value=""><?php esc_html_e( 'All Domains', 'gemini-multilingual' ); ?></option>
					<?php foreach ( $packages as $pkg ) : ?>
						<option value="<?php echo esc_attr( $pkg['domain'] ); ?>" <?php selected( $selected_domain, $pkg['domain'] ); ?>><?php echo esc_html( $pkg['name'] ); ?> (<?php echo esc_html( $pkg['domain'] ); ?>)</option>
					<?php endforeach; ?>
				</select>
				<select name="lang" id="gm-filter-lang">
					<?php foreach ( $langs as $l ) : ?>
						<option value="<?php echo esc_attr( $l['slug'] ); ?>" <?php selected( $selected_lang, $l['slug'] ); ?>><?php echo esc_html( $l['flag'] . ' ' . $l['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="status">
					<option value=""><?php esc_html_e( 'All Statuses', 'gemini-multilingual' ); ?></option>
					<option value="untranslated" <?php selected( $selected_status, 'untranslated' ); ?>><?php esc_html_e( 'Untranslated', 'gemini-multilingual' ); ?></option>
					<option value="ai" <?php selected( $selected_status, 'ai' ); ?>><?php esc_html_e( 'AI Translated', 'gemini-multilingual' ); ?></option>
					<option value="manual" <?php selected( $selected_status, 'manual' ); ?>><?php esc_html_e( 'Manual', 'gemini-multilingual' ); ?></option>
				</select>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search strings...', 'gemini-multilingual' ); ?>" />
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'gemini-multilingual' ); ?></button>

				<?php if ( '' !== $selected_domain ) : ?>
					<button type="button" class="button button-primary" id="gm-ai-translate-domain-btn" data-domain="<?php echo esc_attr( $selected_domain ); ?>" data-lang="<?php echo esc_attr( $selected_lang ); ?>">
						✨ <?php esc_html_e( 'AI Translate All Untranslated in Domain', 'gemini-multilingual' ); ?>
					</button>
				<?php endif; ?>
			</form>

			<div id="gm-string-progress" class="gm-progress-wrap" style="display:none;">
				<div class="gm-progress-bar"><div class="gm-progress-fill" style="width:0%"></div></div>
				<p class="gm-progress-text"></p>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:14%;"><?php esc_html_e( 'Domain', 'gemini-multilingual' ); ?></th>
						<th style="width:36%;"><?php esc_html_e( 'Original Text', 'gemini-multilingual' ); ?></th>
						<th style="width:38%;"><?php esc_html_e( 'Translation', 'gemini-multilingual' ); ?></th>
						<th style="width:12%;"><?php esc_html_e( 'Action', 'gemini-multilingual' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $result['rows'] ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'No strings found. Select a theme or plugin above and click "Scan Strings".', 'gemini-multilingual' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $result['rows'] as $row ) : ?>
							<tr data-string-id="<?php echo (int) $row['id']; ?>">
								<td><code><?php echo esc_html( $row['domain'] ); ?></code><br /><small class="gm-status-tag gm-status-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></small></td>
								<td><?php echo esc_html( $row['original'] ); ?></td>
								<td><textarea class="large-text gm-string-input" rows="2"><?php echo esc_textarea( $row['translation'] ); ?></textarea></td>
								<td><button type="button" class="button gm-save-string-btn"><?php esc_html_e( 'Save', 'gemini-multilingual' ); ?></button></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php
			$total_pages = (int) ceil( $result['total'] / $limit );
			if ( $total_pages > 1 ) {
				echo '<div class="tablenav"><div class="tablenav-pages">';
				echo paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'current' => $paged,
						'total'   => $total_pages,
					)
				);
				echo '</div></div>';
			}
			?>
		</div>
		<?php
	}

	private function render_bulk_tab(): void {
		$langs      = $this->languages->get_languages( true );
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<div class="gm-card">
			<h2><?php esc_html_e( 'Bulk One-Click AI Translator (Posts, Pages, WooCommerce Products)', 'gemini-multilingual' ); ?></h2>
			<div class="gm-toolbar">
				<select id="gm-bulk-post-type">
					<?php foreach ( $post_types as $pt ) : ?>
						<option value="<?php echo esc_attr( $pt->name ); ?>"><?php echo esc_html( $pt->labels->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<select id="gm-bulk-target-lang">
					<?php foreach ( $langs as $l ) : ?>
						<?php if ( empty( $l['is_default'] ) ) : ?>
							<option value="<?php echo esc_attr( $l['slug'] ); ?>"><?php echo esc_html( $l['flag'] . ' ' . $l['name'] ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<button type="button" class="button" id="gm-load-untranslated-btn"><?php esc_html_e( 'Find Untranslated Items', 'gemini-multilingual' ); ?></button>
				<button type="button" class="button button-primary" id="gm-run-bulk-btn" style="display:none;">✨ <?php esc_html_e( 'Translate All with Gemini AI', 'gemini-multilingual' ); ?></button>
			</div>

			<div id="gm-bulk-progress" class="gm-progress-wrap" style="display:none;">
				<div class="gm-progress-bar"><div class="gm-progress-fill" style="width:0%"></div></div>
				<p class="gm-progress-text"></p>
			</div>

			<div id="gm-bulk-list" style="margin-top:12px;"></div>
		</div>
		<?php
	}

	public function register_metaboxes(): void {
		$settings   = GM_Plugin::get_settings();
		$post_types = (array) ( $settings['post_types'] ?? array( 'post', 'page', 'product' ) );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'gm_language_metabox',
				__( 'Gemini AI Multilingual', 'gemini-multilingual' ),
				array( $this, 'render_post_metabox' ),
				$pt,
				'side',
				'high'
			);
		}
	}

	public function render_post_metabox( WP_Post $post ): void {
		wp_nonce_field( 'gm_post_lang_meta', 'gm_post_lang_nonce' );
		$langs         = $this->languages->get_languages( true );
		$current_lang  = $this->translations->get_object_language( $post->ID, 'post' ) ?: $this->languages->get_default_slug();
		$translations  = $this->translations->get_translations( $post->ID, 'post' );
		$detected_lang = $this->translator->detect_post_language( $post );
		$detected_obj  = $detected_lang ? $this->languages->get_language( $detected_lang ) : null;
		?>
		<p>
			<label for="gm_post_lang"><strong><?php esc_html_e( 'Language:', 'gemini-multilingual' ); ?></strong></label><br />
			<select name="gm_post_lang" id="gm_post_lang" style="width:100%;margin-top:4px;">
				<?php foreach ( $langs as $l ) : ?>
					<option value="<?php echo esc_attr( $l['slug'] ); ?>" <?php selected( $current_lang, $l['slug'] ); ?>>
						<?php echo esc_html( $l['flag'] . ' ' . $l['name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<?php if ( $detected_obj && $detected_lang !== $current_lang ) : ?>
			<div class="notice notice-warning inline" style="margin:8px 0;padding:8px 10px;">
				<p style="margin:0 0 6px;font-size:12px;">
					<?php
					printf(
						/* translators: %s: detected language name */
						esc_html__( 'Content appears to be written in %s.', 'gemini-multilingual' ),
						'<strong>' . esc_html( $detected_obj['flag'] . ' ' . $detected_obj['name'] ) . '</strong>'
					);
					?>
				</p>
				<button type="button" class="button button-small gm-fix-post-lang" data-post-id="<?php echo (int) $post->ID; ?>" data-lang="<?php echo esc_attr( $detected_lang ); ?>" style="width:100%;">
					<?php
					printf(
						/* translators: %s: detected language name */
						esc_html__( 'Fix Language → %s', 'gemini-multilingual' ),
						esc_html( $detected_obj['flag'] . ' ' . $detected_obj['name'] )
					);
					?>
				</button>
			</div>
		<?php endif; ?>

		<div class="gm-metabox-translations">
			<strong><?php esc_html_e( 'Translations:', 'gemini-multilingual' ); ?></strong>
			<ul>
				<?php foreach ( $langs as $l ) : ?>
					<?php if ( $l['slug'] === $current_lang ) : ?>
						<li class="gm-tr-row">
							<span><?php echo esc_html( $l['flag'] . ' ' . $l['name'] ); ?></span>
							<span class="gm-tr-actions">
								<button type="button" class="button button-small gm-one-click-translate" data-post-id="<?php echo (int) $post->ID; ?>" data-lang="<?php echo esc_attr( $l['slug'] ); ?>" data-is-current="1" title="<?php esc_attr_e( 'Check grammar and translate any untranslated parts into this language', 'gemini-multilingual' ); ?>">
									✨ <?php esc_html_e( 'Proofread & Complete', 'gemini-multilingual' ); ?>
								</button>
							</span>
						</li>
						<?php continue; ?>
					<?php endif; ?>
					<?php $tr_id = $translations[ $l['slug'] ] ?? 0; ?>
					<li class="gm-tr-row">
						<span><?php echo esc_html( $l['flag'] . ' ' . $l['name'] ); ?></span>
						<span class="gm-tr-actions">
							<?php if ( $tr_id > 0 && get_post( $tr_id ) ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $tr_id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'gemini-multilingual' ); ?></a>
							<?php endif; ?>
							<button type="button" class="button button-small button-primary gm-one-click-translate" data-post-id="<?php echo (int) $post->ID; ?>" data-lang="<?php echo esc_attr( $l['slug'] ); ?>">
								✨ <?php echo $tr_id > 0 ? esc_html__( 'Re-translate', 'gemini-multilingual' ) : esc_html__( 'AI Translate', 'gemini-multilingual' ); ?>
							</button>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( count( $langs ) > 1 ) : ?>
				<button type="button" class="button button-primary gm-translate-all-btn" data-post-id="<?php echo (int) $post->ID; ?>" style="width:100%;margin-top:8px;">
					✨ <?php esc_html_e( 'Translate to All Languages (1-Click)', 'gemini-multilingual' ); ?>
				</button>
			<?php endif; ?>
			<div class="gm-metabox-status" style="margin-top:8px;"></div>
		</div>
		<?php
	}

	public function save_post_language_meta( int $post_id, WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['gm_post_lang_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gm_post_lang_nonce'] ) ), 'gm_post_lang_meta' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['gm_post_lang'] ) ) {
			$lang = sanitize_key( wp_unslash( $_POST['gm_post_lang'] ) );
			$this->translations->set_object_language( $post_id, $lang, 'post' );
		}
	}

	public function register_list_table_columns(): void {
		$settings   = GM_Plugin::get_settings();
		$post_types = (array) ( $settings['post_types'] ?? array( 'post', 'page', 'product' ) );
		foreach ( $post_types as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( $this, 'add_lang_column' ) );
			add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_lang_column' ), 10, 2 );
		}
	}

	public function add_lang_column( array $columns ): array {
		$columns['gm_languages'] = __( 'Languages (AI)', 'gemini-multilingual' );
		return $columns;
	}

	public function render_lang_column( string $column, int $post_id ): void {
		if ( 'gm_languages' !== $column ) {
			return;
		}
		$langs         = $this->languages->get_languages( true );
		$post_lang     = $this->translations->get_object_language( $post_id, 'post' ) ?: $this->languages->get_default_slug();
		$translations  = $this->translations->get_translations( $post_id, 'post' );
		$detected_lang = $this->translator->detect_post_language( $post_id );

		echo '<div class="gm-col-langs">';
		foreach ( $langs as $l ) {
			if ( $l['slug'] === $post_lang ) {
				$mismatch_badge = ( $detected_lang && $detected_lang !== $post_lang ) ? ' ⚠️' : '';
				$title          = $mismatch_badge
					? sprintf( '%s (content looks like %s)', $l['name'], $detected_lang )
					: $l['name'];
				echo '<span class="gm-col-flag gm-col-current" title="' . esc_attr( $title ) . '">' . esc_html( $l['flag'] . $mismatch_badge ) . '</span>';
			} elseif ( ! empty( $translations[ $l['slug'] ] ) ) {
				$edit_url = get_edit_post_link( (int) $translations[ $l['slug'] ] );
				echo '<a href="' . esc_url( $edit_url ) . '" class="gm-col-flag gm-col-done" title="' . esc_attr( 'Edit ' . $l['name'] ) . '">' . esc_html( $l['flag'] ) . '✓</a>';
			} else {
				echo '<button type="button" class="gm-col-flag gm-col-add gm-one-click-translate" data-post-id="' . (int) $post_id . '" data-lang="' . esc_attr( $l['slug'] ) . '" title="' . esc_attr( 'AI Translate to ' . $l['name'] ) . '">' . esc_html( $l['flag'] ) . '+</button>';
			}
		}
		echo '</div>';
	}

	public function register_taxonomy_hooks(): void {
		$settings   = GM_Plugin::get_settings();
		$taxonomies = (array) ( $settings['taxonomies'] ?? array( 'category', 'post_tag', 'product_cat', 'product_tag' ) );
		foreach ( $taxonomies as $tax ) {
			add_action( "{$tax}_edit_form_fields", array( $this, 'render_term_edit_fields' ), 10, 2 );
		}
	}

	public function render_term_edit_fields( WP_Term $term, string $taxonomy ): void {
		$langs        = $this->languages->get_languages( true );
		$current_lang = $this->translations->get_object_language( $term->term_id, 'term' ) ?: $this->languages->get_default_slug();
		?>
		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'Gemini AI Translations', 'gemini-multilingual' ); ?></label></th>
			<td>
				<?php foreach ( $langs as $l ) : ?>
					<?php if ( $l['slug'] !== $current_lang ) : ?>
						<button type="button" class="button gm-one-click-translate-term" data-term-id="<?php echo (int) $term->term_id; ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>" data-lang="<?php echo esc_attr( $l['slug'] ); ?>">
							✨ <?php echo esc_html( $l['flag'] . ' ' . __( 'AI Translate to', 'gemini-multilingual' ) . ' ' . $l['name'] ); ?>
						</button>
					<?php endif; ?>
				<?php endforeach; ?>
				<span class="gm-term-status gm-inline-status"></span>
			</td>
		</tr>
		<?php
	}

	/* ================= AJAX HANDLERS ================= */

	public function ajax_test_connection(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		$res = $this->gemini->test_connection();
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( $res->get_error_message() );
		}
		wp_send_json_success( $res );
	}

	public function ajax_refresh_models(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		$models = $this->gemini->fetch_remote_models();
		if ( is_wp_error( $models ) ) {
			wp_send_json_error( $models->get_error_message() );
		}
		wp_send_json_success( array( 'models' => $models ) );
	}

	public function ajax_translate_post(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		$lang    = sanitize_key( wp_unslash( $_POST['lang'] ?? '' ) );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$res = $this->translator->translate_post( $post_id, $lang, true );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( $res->get_error_message() );
		}

		wp_send_json_success(
			array(
				'target_id' => $res,
				'same_post' => ( (int) $res === $post_id ),
				'edit_url'  => get_edit_post_link( $res, 'raw' ),
			)
		);
	}

	public function ajax_fix_post_language(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		$lang    = sanitize_key( wp_unslash( $_POST['lang'] ?? '' ) );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$res = $this->translator->fix_post_language( $post_id, '' !== $lang ? $lang : null );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( $res->get_error_message() );
		}

		wp_send_json_success( array( 'lang' => $res ) );
	}

	public function ajax_translate_term(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$term_id  = (int) ( $_POST['term_id'] ?? 0 );
		$taxonomy = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$lang     = sanitize_key( wp_unslash( $_POST['lang'] ?? '' ) );

		$res = $this->translator->translate_term( $term_id, $taxonomy, $lang, true );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( $res->get_error_message() );
		}

		wp_send_json_success( array( 'target_term_id' => $res ) );
	}

	public function ajax_scan_package(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		$package_id = sanitize_text_field( wp_unslash( $_POST['package_id'] ?? '' ) );
		$res        = $this->strings->scan_package( $package_id );
		wp_send_json_success( $res );
	}

	public function ajax_save_string(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		$id          = (int) ( $_POST['id'] ?? 0 );
		$translation = wp_kses_post( wp_unslash( $_POST['translation'] ?? '' ) );
		$ok          = $this->strings->save_translation( $id, $translation, 'manual' );
		wp_send_json_success( array( 'saved' => $ok ) );
	}

	public function ajax_ai_translate_strings_batch(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		$domain = sanitize_text_field( wp_unslash( $_POST['domain'] ?? '' ) );
		$lang   = sanitize_key( wp_unslash( $_POST['lang'] ?? '' ) );

		$res = $this->strings->ai_translate_domain_batch( $domain, $lang, 35 );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( $res->get_error_message() );
		}
		wp_send_json_success( $res );
	}

	public function ajax_get_untranslated_posts(): void {
		check_ajax_referer( 'gm_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$post_type    = sanitize_key( wp_unslash( $_POST['post_type'] ?? 'post' ) );
		$target_lang  = sanitize_key( wp_unslash( $_POST['lang'] ?? '' ) );
		$default_slug = $this->languages->get_default_slug();

		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => 200,
				'post_status'    => array( 'publish', 'draft' ),
			)
		);

		$missing = array();
		foreach ( $posts as $p ) {
			$p_lang = $this->translations->get_object_language( $p->ID, 'post' ) ?: $default_slug;
			if ( $p_lang !== $default_slug ) {
				continue;
			}
			$tr_id = $this->translations->get_translation( $p->ID, $target_lang, 'post' );
			if ( $tr_id <= 0 ) {
				$missing[] = array(
					'id'    => $p->ID,
					'title' => $p->post_title ?: '(No Title #' . $p->ID . ')',
				);
			}
		}

		wp_send_json_success( array( 'items' => $missing ) );
	}
}
