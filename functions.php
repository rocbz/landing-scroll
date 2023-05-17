<?php

global $SITE;
$SITE = new SiteObject();
$SITE->init();

function SITE() {
	global $SITE;
	return $SITE;
}
// add css and js
wp_enqueue_style( 'main', get_template_directory_uri() . '/build/css/app.css',false,'1.1','all');
wp_enqueue_script( 'main', get_template_directory_uri() . '/build/js/app.js', array(), '1.0.0', true );

// disable for posts
add_filter('use_block_editor_for_post', '__return_false', 10);

// disable for post types
add_filter('use_block_editor_for_post_type', '__return_false', 10);

class SiteObject {

	public $meta_title = '';
	public $meta_desc = '';
	public $meta_img = '';

	function init() {
		add_action('init', function() {
			register_nav_menu('main-menu', __( 'Main Menu' ));
			add_image_size('1280', 1280);
			add_image_size('1920', 1920);
		});

		if (!is_admin()) {
			add_action('wp', [$this, 'setupMeta']);
		}
	}

	function setupMeta() {
		$post = get_post();

		if ($post) {
			$this->meta_title = trim(get_the_title($post->ID));
			$this->meta_desc = trim(get_the_excerpt($post));
			$this->meta_img = get_the_post_thumbnail_url($post->ID, 'large');
		}

		$home_post = get_post(url_to_postid('home'));

		if ($home_post) {
			if (empty($this->meta_title)) {
				$this->meta_title = trim(get_the_title($home_post->ID));
			}

			if (empty($this->meta_desc)) {
				$this->meta_desc = trim(get_the_excerpt($home_post));
			}

			if (empty($this->meta_img)) {
				$this->meta_img = get_the_post_thumbnail_url($home_post->ID, 'large');
			}
		}
	}

	function getMeta($type) {
		return esc_attr($this->$type);
	}

	function resource($file) {
		return bloginfo('template_url')."/src/{$file}";
	}

	function svg($file) {
		return file_get_contents(__DIR__."/src/{$file}");
	}

	function dir($parent = 0) {
		$pages = new WP_Query([
			'post_type' => 'page',
			'post_parent' => $parent,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'posts_per_page' => -1
		]);

		foreach($pages->posts as $page) {
			$template = get_page_template_slug($page->ID);
			$data = get_fields($page->ID);
			include $template;
		}
	}
}

function enqueue_scripts() {
    // Deregister the default jQuery included with WordPress
    wp_deregister_script('jquery');

    // Register and enqueue jQuery from a local copy
    wp_enqueue_script('jquery', get_template_directory_uri() . '/build/js/jquery-3.3.1.min.js', array(), '3.3.1', true);
    wp_enqueue_script('aos', get_template_directory_uri() . '/build/js/aos-2.3.1.js', array(), '2.3.1', true);
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');

?>
