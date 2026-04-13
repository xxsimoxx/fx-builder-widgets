<?php

namespace xxsimoxx\FXBuilderWidgets;

class FXBuilderWidgets extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fx_builder_widgets',
			esc_html__('FX Builder content', 'fx-builder-widgets'),
			[
				'description' => esc_html__('Page widget', 'fx-builder-widgets'),
			]
		);
	}

	public function widget($args, $instance) {
		$content = \fx_builder\builder\Functions::content($instance['post_id']);
		$content = do_shortcode($content);
		echo wp_kses_post($args['before_widget']);
		if ($instance['show_title'] === 'on') {
			echo wp_kses_post($args['before_title'].apply_filters('widget_title', get_the_title($instance['post_id'])).$args['after_title']);
		}
		echo wp_kses_post($content);
		echo wp_kses_post($args['after_widget']);
	}

	public function form($instance) {
		$post_types = get_option('fx-builder_post_types');
		if (!is_array($post_types) || empty($post_types)) {
			$post_types = ['page'];
		}
		$posts = get_posts([
			'post_type'   => $post_types,
			'post_status' => 'publish',
			'numberposts' => -1,
			'meta_query'  => [ //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'     => '_fxb_active',
					'value'   => true,
					'compare' => 'LIKE',
				],
			],
		]);
		?>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('post_id')); ?>"><?php esc_html_e('Select content:', 'fx-builder-widgets'); ?></label>
		<select id="<?php echo esc_attr($this->get_field_id('post_id')); ?>" name="<?php echo esc_attr($this->get_field_name('post_id')); ?>" >
		<option value="0" disabled selected><?php esc_html_e('--Please choose an option--', 'fx-builder-widgets'); ?></option>
		<?php
		$current_id = (int) ($instance['post_id'] ?? '');
		$show_title = $instance['show_title'] ?? '';
		foreach ($posts as $post) {
			$title      = $post->post_title;
			$post_id    = $post->ID;
			$selected = ($post_id === $current_id) ? ' selected' : '';
			echo '<option value="'.esc_attr($post_id).'"'.esc_attr($selected).'>'.esc_html($title).'</option>';
		}
		?>
		</select>
		</p>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('show_title')); ?>"><?php esc_html_e('Show title', 'fx-builder-widgets'); ?></label>
		<input type="checkbox" <?php checked('on', $show_title); ?> id="<?php echo esc_attr($this->get_field_id('show_title')); ?>" name="<?php echo esc_attr($this->get_field_name('show_title')); ?>" >
		</p>
		<?php
	}

	public function update($new_instance, $old_instance) {
		$instance = [];
		$instance['post_id']    = (!empty($new_instance['post_id'])) ? wp_strip_all_tags($new_instance['post_id']) : '';
		$instance['show_title'] = (!empty($new_instance['show_title'])) ? wp_strip_all_tags($new_instance['show_title']) : '';
		return $instance;
	}
}


