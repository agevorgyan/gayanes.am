<?php
/**
 * AI Translation & Model Selector (HY / RU / EN).
 *
 * Allows administrators to select an AI provider & model (Google Gemini,
 * OpenAI, Anthropic Claude, or Custom OpenAI-Compatible endpoint) and
 * translate Products, Posts, and Pages between Armenian, Russian, and English.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Supported AI models grouped by provider.
 *
 * @return array<string, array{label: string, provider: string}>
 */
function gv2_get_available_ai_models(): array {
	return array(
		'gemini-2.5-flash'           => array(
			'label'    => 'Google Gemini 2.5 Flash (Fast & Recommended)',
			'provider' => 'gemini',
		),
		'gemini-2.5-pro'             => array(
			'label'    => 'Google Gemini 2.5 Pro (Highest Quality)',
			'provider' => 'gemini',
		),
		'gemini-3-flash-preview'     => array(
			'label'    => 'Google Gemini 3 Flash Preview',
			'provider' => 'gemini',
		),
		'gpt-4o-mini'                => array(
			'label'    => 'OpenAI GPT-4o Mini',
			'provider' => 'openai',
		),
		'gpt-4.1'                    => array(
			'label'    => 'OpenAI GPT-4.1',
			'provider' => 'openai',
		),
		'gpt-4o'                     => array(
			'label'    => 'OpenAI GPT-4o',
			'provider' => 'openai',
		),
		'claude-3-5-sonnet-latest'   => array(
			'label'    => 'Anthropic Claude 3.5 Sonnet',
			'provider' => 'anthropic',
		),
		'claude-3-5-haiku-latest'    => array(
			'label'    => 'Anthropic Claude 3.5 Haiku',
			'provider' => 'anthropic',
		),
		'custom'                     => array(
			'label'    => 'Custom OpenAI-Compatible Endpoint',
			'provider' => 'custom',
		),
	);
}

/**
 * Retrieve saved AI translation settings.
 *
 * @return array<string, string>
 */
function gv2_get_ai_settings(): array {
	$defaults = array(
		'model'           => 'gemini-2.5-flash',
		'api_key'         => '',
		'custom_endpoint' => '',
		'custom_model'    => '',
	);
	$saved = get_option( 'gv2_ai_settings', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

/**
 * Register AI Translator admin page under Appearance.
 */
function gv2_register_ai_admin_page(): void {
	add_theme_page(
		__( 'GayanesV2 AI Translator', 'gayanes-v2' ),
		__( 'AI Translator (HY/RU/EN)', 'gayanes-v2' ),
		'manage_options',
		'gv2-ai-translator',
		'gv2_render_ai_settings_page'
	);
}
add_action( 'admin_menu', 'gv2_register_ai_admin_page' );

/**
 * Handle saving AI settings.
 */
function gv2_register_ai_settings(): void {
	register_setting(
		'gv2_ai_settings_group',
		'gv2_ai_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => static function ( $input ): array {
				$models = gv2_get_available_ai_models();
				$model  = isset( $input['model'] ) && isset( $models[ $input['model'] ] ) ? $input['model'] : 'gemini-2.5-flash';
				return array(
					'model'           => $model,
					'api_key'         => isset( $input['api_key'] ) ? sanitize_text_field( (string) $input['api_key'] ) : '',
					'custom_endpoint' => isset( $input['custom_endpoint'] ) ? esc_url_raw( (string) $input['custom_endpoint'] ) : '',
					'custom_model'    => isset( $input['custom_model'] ) ? sanitize_text_field( (string) $input['custom_model'] ) : '',
				);
			},
		)
	);
}
add_action( 'admin_init', 'gv2_register_ai_settings' );

/**
 * Render the AI Translator settings page.
 */
function gv2_render_ai_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$settings = gv2_get_ai_settings();
	$models   = gv2_get_available_ai_models();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'GayanesV2 — AI Trilingual Translator (HY / RU / EN)', 'gayanes-v2' ); ?></h1>
		<p><?php esc_html_e( 'Choose your preferred AI model to automatically translate products, recipes, and SEO metadata across Armenian, Russian, and English.', 'gayanes-v2' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'gv2_ai_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="gv2_ai_model"><?php esc_html_e( 'AI Model', 'gayanes-v2' ); ?></label></th>
					<td>
						<select id="gv2_ai_model" name="gv2_ai_settings[model]" class="regular-text">
							<?php foreach ( $models as $key => $info ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['model'], $key ); ?>>
									<?php echo esc_html( $info['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gv2_ai_api_key"><?php esc_html_e( 'API Key', 'gayanes-v2' ); ?></label></th>
					<td>
						<input id="gv2_ai_api_key" type="password" name="gv2_ai_settings[api_key]" value="<?php echo esc_attr( $settings['api_key'] ); ?>" class="regular-text" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'API key for the selected provider (Gemini, OpenAI, Anthropic, or Custom).', 'gayanes-v2' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gv2_custom_endpoint"><?php esc_html_e( 'Custom Endpoint (Optional)', 'gayanes-v2' ); ?></label></th>
					<td>
						<input id="gv2_custom_endpoint" type="url" name="gv2_ai_settings[custom_endpoint]" value="<?php echo esc_attr( $settings['custom_endpoint'] ); ?>" class="regular-text" placeholder="https://api.example.com/v1/chat/completions" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gv2_custom_model"><?php esc_html_e( 'Custom Model ID (Optional)', 'gayanes-v2' ); ?></label></th>
					<td>
						<input id="gv2_custom_model" type="text" name="gv2_ai_settings[custom_model]" value="<?php echo esc_attr( $settings['custom_model'] ); ?>" class="regular-text" placeholder="llama-3.3-70b-versatile" />
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Add AI Translation metabox on Product, Post, and Page edit screens.
 */
function gv2_add_ai_translator_metabox(): void {
	foreach ( array( 'product', 'post', 'page' ) as $post_type ) {
		add_meta_box(
			'gv2_ai_translator_box',
			__( 'GayanesV2 AI Translator (HY / RU / EN)', 'gayanes-v2' ),
			'gv2_render_ai_metabox',
			$post_type,
			'side',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'gv2_add_ai_translator_metabox' );

/**
 * Render AI translation metabox inside post/product editor.
 *
 * @param WP_Post $post Current post object.
 */
function gv2_render_ai_metabox( WP_Post $post ): void {
	$settings = gv2_get_ai_settings();
	$models   = gv2_get_available_ai_models();
	$nonce    = wp_create_nonce( 'gv2_ai_translate_nonce' );
	?>
	<div id="gv2-ai-metabox" data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
		<p>
			<label for="gv2-metabox-model"><strong><?php esc_html_e( 'AI Model:', 'gayanes-v2' ); ?></strong></label><br />
			<select id="gv2-metabox-model" style="width:100%;margin-top:4px;">
				<?php foreach ( $models as $key => $info ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['model'], $key ); ?>>
						<?php echo esc_html( $info['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p style="display:flex;gap:6px;flex-wrap:wrap;">
			<button type="button" class="button gv2-ai-btn" data-target-lang="hy">HY (Հայ)</button>
			<button type="button" class="button gv2-ai-btn" data-target-lang="ru">RU (Рус)</button>
			<button type="button" class="button gv2-ai-btn" data-target-lang="en">EN (Eng)</button>
		</p>
		<p id="gv2-ai-status" style="font-size:12px;color:#555;margin:0;"></p>
	</div>
	<script>
	(function(){
		const box = document.getElementById('gv2-ai-metabox');
		if (!box) return;
		const status = document.getElementById('gv2-ai-status');
		const modelEl = document.getElementById('gv2-metabox-model');
		box.querySelectorAll('.gv2-ai-btn').forEach(function(btn){
			btn.addEventListener('click', function(){
				const targetLang = btn.getAttribute('data-target-lang');
				status.textContent = 'Translating with ' + modelEl.value + '...';
				const body = new URLSearchParams({
					action: 'gv2_ai_translate_post',
					nonce: box.getAttribute('data-nonce'),
					post_id: box.getAttribute('data-post-id'),
					target_lang: targetLang,
					model: modelEl.value
				});
				fetch(ajaxurl, { method: 'POST', body: body })
					.then(r => r.json())
					.then(res => {
						if (res.success) {
							status.textContent = res.data.message || 'Translated & saved!';
						} else {
							status.textContent = (res.data && res.data.message) ? res.data.message : 'Translation failed.';
						}
					})
					.catch(() => { status.textContent = 'Network error.'; });
			});
		});
	})();
	</script>
	<?php
}

/**
 * Call the selected AI provider to translate structured JSON fields.
 *
 * @param array<string, string> $payload     Fields to translate (title, excerpt, content).
 * @param string                $target_lang Target language code (hy, ru, en).
 * @param string                $model       Selected model ID.
 * @return array<string, mixed>
 */
function gv2_call_ai_translation_api( array $payload, string $target_lang, string $model ): array {
	$settings = gv2_get_ai_settings();
	$api_key  = $settings['api_key'];
	if ( '' === $api_key ) {
		return array( 'error' => __( 'Please configure your AI API Key in Appearance -> AI Translator.', 'gayanes-v2' ) );
	}

	$lang_names = array(
		'hy' => 'Armenian (Հայերեն)',
		'ru' => 'Russian (Русский)',
		'en' => 'English',
	);
	$lang_label = $lang_names[ $target_lang ] ?? 'Armenian';
	$models     = gv2_get_available_ai_models();
	$provider   = $models[ $model ]['provider'] ?? 'gemini';

	$prompt = "Translate the following JSON fields into natural, appetizing culinary {$lang_label} for an Armenian homemade food e-commerce store (Gayane's Kitchen). Preserve any HTML tags. Return ONLY valid JSON with keys: title, excerpt, content.\n\n" . wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );

	if ( 'gemini' === $provider ) {
		$url      = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model ) . ':generateContent?key=' . rawurlencode( $api_key );
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 45,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'contents'         => array(
							array( 'parts' => array( array( 'text' => $prompt ) ) ),
						),
						'generationConfig' => array(
							'responseMimeType' => 'application/json',
						),
					)
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
		$json = json_decode( (string) $text, true );
		return is_array( $json ) ? $json : array( 'error' => __( 'Invalid JSON returned by Gemini.', 'gayanes-v2' ) );
	}

	if ( 'anthropic' === $provider ) {
		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 45,
				'headers' => array(
					'Content-Type'      => 'application/json',
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $model,
						'max_tokens' => 2048,
						'messages'   => array(
							array( 'role' => 'user', 'content' => $prompt ),
						),
					)
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$text = $data['content'][0]['text'] ?? '';
		$json = json_decode( (string) $text, true );
		return is_array( $json ) ? $json : array( 'error' => __( 'Invalid JSON returned by Claude.', 'gayanes-v2' ) );
	}

	// OpenAI or Custom OpenAI-compatible endpoint.
	$endpoint   = 'custom' === $provider && '' !== $settings['custom_endpoint']
		? $settings['custom_endpoint']
		: 'https://api.openai.com/v1/chat/completions';
	$model_name = 'custom' === $provider && '' !== $settings['custom_model']
		? $settings['custom_model']
		: $model;

	$response = wp_remote_post(
		$endpoint,
		array(
			'timeout' => 45,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'body'    => wp_json_encode(
				array(
					'model'           => $model_name,
					'response_format' => array( 'type' => 'json_object' ),
					'messages'        => array(
						array( 'role' => 'user', 'content' => $prompt ),
					),
				)
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'error' => $response->get_error_message() );
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	$text = $data['choices'][0]['message']['content'] ?? '';
	$json = json_decode( (string) $text, true );
	return is_array( $json ) ? $json : array( 'error' => __( 'Invalid JSON returned by AI provider.', 'gayanes-v2' ) );
}

/**
 * AJAX handler for translating a product/post/page using the chosen AI model.
 */
function gv2_handle_ai_translate_post(): void {
	check_ajax_referer( 'gv2_ai_translate_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gayanes-v2' ) ) );
	}

	$post_id     = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$target_lang = isset( $_POST['target_lang'] ) ? sanitize_key( (string) $_POST['target_lang'] ) : 'hy';
	$model       = isset( $_POST['model'] ) ? sanitize_text_field( (string) $_POST['model'] ) : 'gemini-2.5-flash';

	$post = get_post( $post_id );
	if ( ! $post ) {
		wp_send_json_error( array( 'message' => __( 'Post not found.', 'gayanes-v2' ) ) );
	}

	$translated = gv2_call_ai_translation_api(
		array(
			'title'   => $post->post_title,
			'excerpt' => $post->post_excerpt,
			'content' => $post->post_content,
		),
		$target_lang,
		$model
	);

	if ( isset( $translated['error'] ) ) {
		wp_send_json_error( array( 'message' => $translated['error'] ) );
	}

	update_post_meta( $post_id, "_gv2_ai_title_{$target_lang}", sanitize_text_field( (string) ( $translated['title'] ?? '' ) ) );
	update_post_meta( $post_id, "_gv2_ai_excerpt_{$target_lang}", wp_kses_post( (string) ( $translated['excerpt'] ?? '' ) ) );
	update_post_meta( $post_id, "_gv2_ai_content_{$target_lang}", wp_kses_post( (string) ( $translated['content'] ?? '' ) ) );

	wp_send_json_success(
		array(
			'message'    => sprintf( __( 'Translated to %s using %s and saved!', 'gayanes-v2' ), strtoupper( $target_lang ), $model ),
			'translated' => $translated,
		)
	);
}
add_action( 'wp_ajax_gv2_ai_translate_post', 'gv2_handle_ai_translate_post' );

