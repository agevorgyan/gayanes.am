<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Translations {

	private string $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'gm_translations';

		add_action( 'delete_post', array( $this, 'on_delete_post' ) );
		add_action( 'delete_term', array( $this, 'on_delete_term' ), 10, 4 );
	}

	public function get_object_language( int $object_id, string $object_type = 'post' ): ?string {
		global $wpdb;
		$lang = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT lang FROM {$this->table} WHERE object_type = %s AND object_id = %d LIMIT 1",
				$object_type,
				$object_id
			)
		);
		return $lang ? (string) $lang : null;
	}

	public function get_group_id( int $object_id, string $object_type = 'post' ): ?string {
		global $wpdb;
		$group = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT group_id FROM {$this->table} WHERE object_type = %s AND object_id = %d LIMIT 1",
				$object_type,
				$object_id
			)
		);
		return $group ? (string) $group : null;
	}

	/**
	 * Returns [ 'hy' => 12, 'en' => 45 ] for all translations in the same group.
	 */
	public function get_translations( int $object_id, string $object_type = 'post' ): array {
		global $wpdb;
		$group_id = $this->get_group_id( $object_id, $object_type );
		if ( ! $group_id ) {
			return array();
		}

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT lang, object_id FROM {$this->table} WHERE group_id = %s AND object_type = %s",
				$group_id,
				$object_type
			),
			ARRAY_A
		);

		$map = array();
		foreach ( $rows as $row ) {
			$map[ $row['lang'] ] = (int) $row['object_id'];
		}
		return $map;
	}

	public function get_translation( int $object_id, string $lang, string $object_type = 'post' ): int {
		$translations = $this->get_translations( $object_id, $object_type );
		return isset( $translations[ $lang ] ) ? (int) $translations[ $lang ] : 0;
	}

	/**
	 * Assigns a language to an object (creates a new group if it doesn't belong to one).
	 */
	public function set_object_language( int $object_id, string $lang, string $object_type = 'post', ?string $group_id = null ): string {
		global $wpdb;

		$existing_group = $this->get_group_id( $object_id, $object_type );
		$target_group   = $group_id ?: ( $existing_group ?: 'gm_' . wp_generate_uuid4() );

		$wpdb->replace(
			$this->table,
			array(
				'group_id'    => $target_group,
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'lang'        => sanitize_key( $lang ),
			),
			array( '%s', '%s', '%d', '%s' )
		);

		return $target_group;
	}

	/**
	 * Links a target object into the same translation group as a source object.
	 */
	public function link_translation( int $source_id, int $target_id, string $source_lang, string $target_lang, string $object_type = 'post' ): string {
		$group_id = $this->get_group_id( $source_id, $object_type );
		if ( ! $group_id ) {
			$group_id = $this->set_object_language( $source_id, $source_lang, $object_type );
		}
		$this->set_object_language( $target_id, $target_lang, $object_type, $group_id );
		return $group_id;
	}

	/**
	 * Returns all object IDs belonging to a specific language and object_type.
	 */
	public function get_object_ids_for_language( string $lang, string $object_type = 'post' ): array {
		global $wpdb;
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT object_id FROM {$this->table} WHERE object_type = %s AND lang = %s",
				$object_type,
				$lang
			)
		);
		return array_map( 'intval', $ids );
	}

	/**
	 * Returns object IDs that belong to OTHER languages (used for excluding from queries).
	 */
	public function get_object_ids_not_in_language( string $lang, string $object_type = 'post' ): array {
		global $wpdb;
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT object_id FROM {$this->table} WHERE object_type = %s AND lang != %s",
				$object_type,
				$lang
			)
		);
		return array_map( 'intval', $ids );
	}

	public function unlink_object( int $object_id, string $object_type = 'post' ): void {
		global $wpdb;
		$wpdb->delete(
			$this->table,
			array(
				'object_type' => $object_type,
				'object_id'   => $object_id,
			),
			array( '%s', '%d' )
		);
	}

	public function on_delete_post( int $post_id ): void {
		$this->unlink_object( $post_id, 'post' );
	}

	public function on_delete_term( int $term_id ): void {
		$this->unlink_object( $term_id, 'term' );
	}
}
