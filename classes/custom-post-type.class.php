<?php

namespace xxsimoxx\FXBuilderWidgets;

class CustomPostType {
	const CPT = 'fx-builder-content';

	public function __construct() {
		add_action('init', [$this, 'register_cpt']);
		add_action('wp_insert_post', [$this, 'set_post_meta'], 10, 3);
		add_action('admin_enqueue_scripts', [$this, 'remove_editor'], 10, 1);
		add_action('admin_enqueue_scripts', [$this, 'change_fx_settings'], 10, 1);
		$this->maybe_change_fxb_settings();
	}

	private function maybe_change_fxb_settings() {
		$options = get_option('fx-builder_post_types', ['page']);
		if (in_array(self::CPT, $options)) {
			return;
		}
		$options[] = self::CPT;
		update_option('fx-builder_post_types', $options);
	}

	public function register_cpt() {
		$labels = [
			'name'               => esc_html__('FX Builder widget contents', 'fx-builder-widgets'),
			'singular_name'      => esc_html__('FX Builder widget content', 'fx-builder-widgets'),
			'add_new'            => esc_html__('Add new widget content', 'fx-builder-widgets'),
			'add_new_item'       => esc_html__('Add new widget content', 'fx-builder-widgets'),
			'edit_item'          => esc_html__('Edit widget content', 'fx-builder-widgets'),
			'new_item'           => esc_html__('New widget content', 'fx-builder-widgets'),
			'all_items'          => esc_html__('All widget content', 'fx-builder-widgets'),
			'view_item'          => esc_html__('View widget content', 'fx-builder-widgets'),
			'search_items'       => esc_html__('Search widget content', 'fx-builder-widgets'),
		];
		$args = [
			'labels'              => $labels,
			'description'         => esc_html__('Holds FX Builder content for widgets.', 'fx-builder-widgets'),
			'public'              => true,
			'menu_icon'           => 'dashicons-welcome-widgets-menus',
			'menu_position'       => 100,
			'supports'            => ['title', 'editor', 'thumbnail', 'custom-fields', 'fx-builder'],
			'has_archive'         => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'query_var'           => true,
			'exclude_from_search' => true,
		];
		register_post_type(self::CPT, $args);
	}

	public function set_post_meta($post_id, $post, $update) {
		if ($post->post_type !== self::CPT || wp_is_post_revision($post_id) || $update) {
			return;
		}
		update_post_meta($post_id, '_fxb_active', 1);
	}

	public function remove_editor($hook) {
		if ($hook !== 'post-new.php' && $hook !== 'post.php') {
			return;
		}
		global $post;
		if ($post->post_type !== self::CPT) {
			return;
		}
		wp_enqueue_script(self::CPT.'-hide-editor', plugin_dir_url(__DIR__).'/js/editor.js');
	}

	public function change_fx_settings($hook) {
		if ($hook !== 'toplevel_page_fx_builder') {
			return;
		}
		wp_enqueue_script(self::CPT.'-change-fx', plugin_dir_url(__DIR__).'/js/fx.js');
	}
}




