<?php
/**
 * Dependency-free rendering regression tests using small dependency doubles.
 * Run in separate processes to cover FX Shortcodes present and absent:
 * php tests/widget-rendering.php
 * php tests/widget-rendering.php --with-fx-shortcodes
 */

namespace fx_builder\builder {
	class Functions {
		public static $content = '';

		public static function content($id) {
			return self::$content;
		}
	}
}

namespace {
	class WP_Widget {
		public function __construct($id, $name, $options) {}
	}

	class WP_Post {
		public $post_status = 'publish';
		public $post_password = '';
	}

	function esc_html__($text, $domain) {
		return $text;
	}

	function get_post($id) {
		return $GLOBALS['test_post'];
	}

	function get_the_title($id) {
		return 'Test title';
	}

	function apply_filters($hook, $value) {
		if ($hook === 'the_content') {
			throw new \RuntimeException('The widget must not apply the_content.');
		}
		return $value;
	}

	function do_shortcode($content) {
		$GLOBALS['trace'][] = 'shortcodes';
		return str_replace('[test-shortcode]', '<span>Standard shortcode</span>', (string) $content);
	}

	// Record the sanitization boundary; this double does not implement KSES.
	function wp_kses_post($content) {
		$GLOBALS['trace'][] = 'sanitize';
		return $content;
	}

	$with_parser = in_array('--with-fx-shortcodes', $argv, true);
	if ($with_parser) {
		// A parser double verifies integration and ordering, not FX parser internals.
		function fx_shortcodes_parse($content) {
			$GLOBALS['trace'][] = 'elements';
			return str_replace(
				['[element type="group"]', '[/element]'],
				['<div class="fx-group">', '</div>'],
				(string) $content
			);
		}
	}

	require dirname(__DIR__).'/classes/fx-bulider-widgets.class.php';

	function check($condition, $message) {
		if (!$condition) {
			throw new \RuntimeException($message);
		}
	}

	$GLOBALS['test_post'] = new WP_Post();
	$widget = new \xxsimoxx\FXBuilderWidgets\FXBuilderWidgets();
	$args = [
		'before_widget' => '<section>',
		'after_widget' => '</section>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	];
	$render = static function ($content, $show_title = '') use ($widget, $args) {
		\fx_builder\builder\Functions::$content = $content;
		$GLOBALS['trace'] = [];
		ob_start();
		$widget->widget($args, ['post_id' => 1, 'show_title' => $show_title]);
		return ob_get_clean();
	};

	check($render('<p>Plain HTML</p>') === '<section><p>Plain HTML</p></section>', 'Plain content changed.');
	check($render('[test-shortcode]') === '<section><span>Standard shortcode</span></section>', 'Ordinary shortcodes stopped working.');
	check($render('<p>Body</p>', 'on') === '<section><h2>Test title</h2><p>Body</p></section>', 'Widget title or wrappers changed.');

	$content = '[element type="group"][element type="group"][test-shortcode][/element][/element]';
	$actual = $render($content);
	if ($with_parser) {
		check($actual === '<section><div class="fx-group"><div class="fx-group"><span>Standard shortcode</span></div></div></section>', 'Nested elements were not processed before ordinary shortcodes.');
		check($GLOBALS['trace'] === ['elements', 'shortcodes', 'sanitize', 'sanitize', 'sanitize'], 'Rendering or sanitization order changed.');
	} else {
		check($actual === '<section>'.str_replace('[test-shortcode]', '<span>Standard shortcode</span>', $content).'</section>', 'Missing optional parser changed content or broke rendering.');
		check($GLOBALS['trace'] === ['shortcodes', 'sanitize', 'sanitize', 'sanitize'], 'Fallback rendering order changed.');
	}

	check($render(false) === '<section></section>', 'Empty Builder content broke rendering.');
	$GLOBALS['test_post']->post_status = 'draft';
	check($render('<p>Private</p>') === '', 'Unpublished content became visible.');
	check($GLOBALS['trace'] === [], 'Hidden content was processed.');
	$GLOBALS['test_post']->post_status = 'publish';
	$GLOBALS['test_post']->post_password = 'secret';
	check($render('<p>Protected</p>') === '', 'Password-protected content became visible.');

	echo 'PASS: widget rendering with FX Shortcodes '.($with_parser ? 'present' : 'absent').PHP_EOL;
}
