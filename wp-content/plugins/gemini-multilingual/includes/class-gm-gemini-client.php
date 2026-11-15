<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Gemini_Client {

	private const API_BASE     = 'https://generativelanguage.googleapis.com/v1beta';
	private const API_REVISION = '2026-05-20';

	/**
	 * Curated list of latest Gemini models (2026).
	 */
	public static function get_default_models(): array {
		return array(
			'gemini-3.8-flash'        => 'Gemini 3.8 Flash (Recommended — Latest Smart & Fast)',
			'gemini-4-argon'          => 'Gemini 4 Argon (Flagship Reasoning Model)',
			'gemini-3.6-flash'        => 'Gemini 3.6 Flash (Stable)',
			'gemini-3.5-flash-lite'   => 'Gemini 3.5 Flash-Lite (Fastest / Low Cost)',
			'gemini-3.1-flash-lite'   => 'Gemini 3.1 Flash-Lite (Stable)',
			'gemini-3.1-pro-preview'  => 'Gemini 3.1 Pro (Preview)',
			'gemini-3-flash-preview'  => 'Gemini 3 Flash (Preview)',
			'custom'                  => 'Custom Model ID...',
		);
	}

	public function get_api_key(): string {
		$settings = GM_Plugin::get_settings();
		return trim( (string) ( $settings['gemini_api_key'] ?? '' ) );
	}

	/**
	 * Fetches available text-generation models from Gemini API.
	 *
	 * @return array|WP_Error
	 */
	public function fetch_remote_models() {
		$api_key = $this->get_api_key();
		if ( '' === $api_key ) {
			return new WP_Error( 'gm_no_key', __( 'Please enter your Gemini API Key first.', 'gemini-multilingual' ) );
		}

		$response = wp_remote_get(
			self::API_BASE . '/models?pageSize=100',
			array(
				'timeout' => 20,
				'headers' => array(
					'x-goog-api-key' => $api_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$msg = $body['error']['message'] ?? ( 'HTTP ' . $code );
			return new WP_Error( 'gm_api_error', $msg );
		}

		$models = array();
		foreach ( $body['models'] ?? array() as $m ) {
			$name    = str_replace( 'models/', '', (string) ( $m['name'] ?? '' ) );
			$methods = $m['supportedGenerationMethods'] ?? array();
			if ( '' === $name || ! str_contains( $name, 'gemini' ) ) {
				continue;
			}
			// Exclude pure TTS/image/embedding/live-only models when possible.
			if ( preg_match( '/(embedding|tts|image|transcribe|robotics|live)/i', $name ) ) {
				continue;
			}
			if ( ! empty( $methods ) && ! in_array( 'generateContent', $methods, true ) ) {
				continue;
			}
			$display         = ! empty( $m['displayName'] ) ? $m['displayName'] . " ({$name})" : $name;
			$models[ $name ] = $display;
		}

		if ( ! empty( $models ) ) {
			update_option( 'gm_cached_models', $models );
		}

		return $models;
	}

	/**
	 * Tests connection with the configured API key and active model.
	 *
	 * @return array|WP_Error
	 */
	public function test_connection() {
		$model  = GM_Plugin::get_active_model();
		$result = $this->translate_structured(
			array( 'greeting' => 'Hello, world!' ),
			'English',
			'Armenian',
			array( 'type' => 'connection_test' ),
			$model
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'model'  => $model,
			'sample' => $result['greeting'] ?? '',
		);
	}

	/**
	 * Translates an associative array of fields into target language, returning the exact same keys.
	 *
	 * @param array  $fields      Key => string value pairs to translate.
	 * @param string $source_lang Human-readable or slug source language.
	 * @param string $target_lang Human-readable or slug target language.
	 * @param array  $context     Optional metadata (post_type, domain, etc.).
	 * @param string $model       Optional override model.
	 * @return array|WP_Error
	 */
	public function translate_structured( array $fields, string $source_lang, string $target_lang, array $context = array(), string $model = '' ) {
		$api_key = $this->get_api_key();
		if ( '' === $api_key ) {
			return new WP_Error( 'gm_no_api_key', __( 'Gemini API key is not configured in Gemini Multilingual Settings.', 'gemini-multilingual' ) );
		}

		// Filter out empty fields so we don't waste tokens.
		$non_empty = array();
		foreach ( $fields as $k => $v ) {
			if ( is_string( $v ) && '' !== trim( $v ) ) {
				$non_empty[ $k ] = $v;
			}
		}

		if ( empty( $non_empty ) ) {
			return $fields;
		}

		$model    = '' !== $model ? $model : GM_Plugin::get_active_model();
		$settings = GM_Plugin::get_settings();
		$rules    = trim( (string) ( $settings['custom_prompt'] ?? '' ) );

		$properties = array();
		$required   = array();
		foreach ( array_keys( $non_empty ) as $key ) {
			$properties[ $key ] = array( 'type' => 'string' );
			$required[]         = $key;
		}

		$schema = array(
			'type'       => 'object',
			'properties' => $properties,
			'required'   => $required,
		);

		$context_note = ! empty( $context ) ? 'Context: ' . wp_json_encode( $context, JSON_UNESCAPED_UNICODE ) . "\n" : '';
		$prompt       = "You are an expert WordPress and WooCommerce localization translator.\n"
			. "Translate every string value in the JSON object below from {$source_lang} to {$target_lang}.\n"
			. "Rules:\n"
			. "- Return ONLY valid JSON matching the requested schema with the exact same keys.\n"
			. "- Preserve all HTML tags, attributes, URLs, WordPress Gutenberg block comments (<!-- wp:... -->), shortcodes ([...]), and printf placeholders (%s, %d, %1\$s, {{var}}, {name}).\n"
			. "- Do not translate CSS classes, inline style attributes, or JSON keys.\n"
			. "- Use natural, idiomatic, e-commerce-appropriate phrasing in {$target_lang}.\n"
			. ( '' !== $rules ? "- Additional instructions: {$rules}\n" : '' )
			. $context_note
			. "\nInput JSON:\n"
			. wp_json_encode( $non_empty, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 120 );
		}

		$decoded = $this->call_interactions_api( $api_key, $model, $prompt, $schema );

		// Fallback to classic generateContent if Interactions API rejects an older/custom model.
		if ( is_wp_error( $decoded ) && 'gm_try_generate_content' === $decoded->get_error_code() ) {
			$decoded = $this->call_generate_content_api( $api_key, $model, $prompt, $schema );
		}

		if ( is_wp_error( $decoded ) ) {
			return $decoded;
		}

		// Merge back empty fields unchanged.
		$output = $fields;
		foreach ( $non_empty as $k => $orig ) {
			if ( isset( $decoded[ $k ] ) && is_string( $decoded[ $k ] ) ) {
				$output[ $k ] = $decoded[ $k ];
			}
		}

		return $output;
	}

	/**
	 * Batch-translates an indexed list of UI strings (for themes & plugins).
	 *
	 * @param array  $items       Array of [ 'id' => string|int, 'text' => string, 'context' => string ].
	 * @param string $source_lang
	 * @param string $target_lang
	 * @param string $domain
	 * @return array|WP_Error Map of id => translated text.
	 */
	public function translate_batch_strings( array $items, string $source_lang, string $target_lang, string $domain = '' ) {
		if ( empty( $items ) ) {
			return array();
		}

		$fields = array();
		foreach ( $items as $idx => $item ) {
			$key            = 's_' . $idx;
			$fields[ $key ] = (string) $item['text'];
		}

		$translated = $this->translate_structured(
			$fields,
			$source_lang,
			$target_lang,
			array(
				'type'   => 'wordpress_theme_plugin_strings',
				'domain' => $domain,
			)
		);

		if ( is_wp_error( $translated ) ) {
			return $translated;
		}

		$result = array();
		foreach ( $items as $idx => $item ) {
			$key                      = 's_' . $idx;
			$result[ $item['id'] ]    = $translated[ $key ] ?? $item['text'];
		}

		return $result;
	}

	/**
	 * Calls the modern Gemini Interactions API (/v1beta/interactions).
	 *
	 * @return array|WP_Error
	 */
	private function call_interactions_api( string $api_key, string $model, string $prompt, array $schema ) {
		$payload = array(
			'model'           => $model,
			'input'           => $prompt,
			'response_format' => array(
				'type'      => 'text',
				'mime_type' => 'application/json',
				'schema'    => $schema,
			),
		);

		$response = wp_remote_post(
			self::API_BASE . '/interactions',
			array(
				'timeout' => 90,
				'headers' => array(
					'Content-Type'   => 'application/json',
					'x-goog-api-key' => $api_key,
					'Api-Revision'   => self::API_REVISION,
				),
				'body'    => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 400 === $code || 404 === $code ) {
			return new WP_Error( 'gm_try_generate_content', $body['error']['message'] ?? 'Fallback to generateContent' );
		}

		if ( $code < 200 || $code >= 300 ) {
			$msg = $body['error']['message'] ?? ( 'Gemini API HTTP ' . $code );
			return new WP_Error( 'gm_api_error', $msg );
		}

		$text = '';
		if ( ! empty( $body['steps'] ) && is_array( $body['steps'] ) ) {
			foreach ( $body['steps'] as $step ) {
				if ( 'model_output' === ( $step['type'] ?? '' ) && ! empty( $step['content'] ) ) {
					foreach ( $step['content'] as $part ) {
						if ( 'text' === ( $part['type'] ?? '' ) && isset( $part['text'] ) ) {
							$text .= $part['text'];
						}
					}
				}
			}
		} elseif ( isset( $body['output_text'] ) ) {
			$text = (string) $body['output_text'];
		}

		$parsed = json_decode( trim( $text ), true );
		if ( ! is_array( $parsed ) ) {
			return new WP_Error( 'gm_parse_error', __( 'Gemini returned an unexpected JSON response.', 'gemini-multilingual' ) );
		}

		return $parsed;
	}

	/**
	 * Fallback: Calls /v1beta/models/{model}:generateContent with responseMimeType=application/json.
	 *
	 * @return array|WP_Error
	 */
	private function call_generate_content_api( string $api_key, string $model, string $prompt, array $schema ) {
		$payload = array(
			'contents'         => array(
				array(
					'role'  => 'user',
					'parts' => array(
						array( 'text' => $prompt ),
					),
				),
			),
			'generationConfig' => array(
				'responseMimeType' => 'application/json',
				'responseSchema'   => $schema,
				'temperature'      => 0.2,
			),
		);

		$url = self::API_BASE . '/models/' . rawurlencode( $model ) . ':generateContent';

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 90,
				'headers' => array(
					'Content-Type'   => 'application/json',
					'x-goog-api-key' => $api_key,
				),
				'body'    => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$msg = $body['error']['message'] ?? ( 'Gemini API HTTP ' . $code );
			return new WP_Error( 'gm_api_error', $msg );
		}

		$text   = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
		$parsed = json_decode( trim( (string) $text ), true );

		if ( ! is_array( $parsed ) ) {
			return new WP_Error( 'gm_parse_error', __( 'Gemini returned an unexpected JSON response.', 'gemini-multilingual' ) );
		}

		return $parsed;
	}
}
