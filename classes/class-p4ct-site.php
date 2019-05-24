<?php
/**
 * P4 Site Class
 *
 * @package P4CT
 */

/**
 * Class P4CT_Site.
 * The main class that handles Planet4 Child Theme.
 */
class P4CT_Site {

	/**
	 * Services
	 *
	 * @var array $services
	 */
	protected $services;

	/**
	 * P4CT_Site constructor.
	 *
	 * @param array $services The dependencies to inject.
	 */
	public function __construct( $services = [] ) {

		$this->load();
		$this->settings();
		$this->hooks();
		$this->services( $services );

	}

	/**
	 * Load required files.
	 */
	protected function load() {
		/**
		 * Class names need to be prefixed with P4CT and should use capitalized words separated by underscores.
		 * Any acronyms should be all upper case.
		 */
		spl_autoload_register(
			function ( $class_name ) {
				if ( strpos( $class_name, 'P4CT_' ) !== false ) {
					$file_name = 'class-' . str_ireplace( [ 'P4CT\\', '_' ], [ '', '-' ], strtolower( $class_name ) );
					require_once __DIR__ . '/' . $file_name . '.php';
				}
			}
		);
	}

	/**
	 * Define settings for the Planet4 Child Theme.
	 */
	protected function settings() {
	}

	/**
	 * Hooks the theme.
	 */
	protected function hooks() {
		add_filter( 'timber_context', [ $this, 'add_to_context' ] );
		add_filter( 'get_twig', [ $this, 'add_to_twig' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ], 2 );
		// add_action( 'pre_get_posts', [ $this, 'add_search_options' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
		add_action( 'after_setup_theme', [ $this, 'add_oembed_filter' ] );
		// add_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10, 2 );
		register_nav_menus(
			[
				'navigation-bar-menu' => __( 'Navigation Bar Menu', 'planet4-child-theme-backend' ),
			]
		);

	}

	/**
	 * Filters the oEmbed process to run the responsive_embed() function
	 */
	public function add_oembed_filter(	) {
		add_filter('embed_oembed_html', [ $this, 'responsive_embed' ], 10, 3);
	}

	/**
	 * Adds a responsive embed wrapper around oEmbed content
	 * @param  string $html The oEmbed markup
	 * @param  string $url	The URL being embedded
	 * @param  array  $attr An array of attributes
	 * @return string		Updated embed markup
	 */
	public function responsive_embed($html, $url, $attr) {
		return $html!=='' ? '<div class="embed-container">'.$html.'</div>' : '';
	}

	/**
	 * Inject dependencies.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function services( $services = [] ) {
		if ( $services ) {
			foreach ( $services as $service ) {
				$this->services[ $service ] = new $service();
			}
		}
	}

	/**
	 * Gets the loaded services.
	 *
	 * @return array The loaded services.
	 */
	public function get_services() : array {
		return $this->services;
	}

	/**
	 * Adds more data to the context variable that will be passed to the main template.
	 *
	 * @param array $context The associative array with data to be passed to the main template.
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		global $wp;
		// $context['antani'] = 'scappellamento';
		// $options = get_option( 'planet4_tarapia' );
		// $context['sbiriguda'] = $options['brematurata'] ?? '';
		return $context;
	}

	/**
	 * Add your own functions to Twig.
	 *
	 * @param Twig_ExtensionInterface $twig The Twig object that implements the Twig_ExtensionInterface.
	 *
	 * @return mixed
	 */
	public function add_to_twig( $twig ) {
		// $twig->addExtension( new Twig_Scappella_Destra() );
		// $twig->addFilter( new Twig_Filtra_Scappella( 'svgicon', [ $this, 'svgicon' ] ) );
		return $twig;
	}

	/**
	 * Dequeue parent theme assets for subsequent override.
	 * Uncomment as needed.
	 */
	public function dequeue_parent_assets() {
		wp_dequeue_style( 'parent-style' );
		wp_deregister_style( 'parent-style' );
		wp_dequeue_style( 'bootstrap' );
		wp_dequeue_style( 'slick' );
		// wp_deregister_script( 'jquery' );
		wp_dequeue_script( 'popperjs' );
		wp_dequeue_script( 'bootstrapjs' );
		wp_dequeue_script( 'main' );
		wp_dequeue_script( 'slick' );
		wp_dequeue_script( 'hammer' );
	}

	/**
	 * Load styling and behaviour on admin pages.
	 *
	 * @param string $hook Hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// TODO check that this one still works if not hooked on 'init'
		add_editor_style( get_stylesheet_directory_uri().'/admin/css/admin_editor_style.css' );

		$css_creation = filectime(get_stylesheet_directory() . '/admin/css/admin_style.css');
		wp_enqueue_style( 'admin-child-style', get_stylesheet_directory_uri() . '/admin/css/admin_style.css', [], $css_creation );
	}

	/**
	 * Load styling and behaviour on website pages.
	 *
	 * @param string $hook Hook.
	 */
	public function enqueue_public_assets( $hook ) {
		$css_creation = filectime(get_stylesheet_directory() . '/static/css/style.css');
		$js_creation = filectime(get_stylesheet_directory() . '/static/js/script.js');

		wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/static/css/style.css', [], $css_creation );
		wp_enqueue_script( 'child-script', get_stylesheet_directory_uri() . '/static/js/script.js', array(), $js_creation, true );
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {

		$labels = array(
			'name'				=> _x( 'Special attributes', 'taxonomy general name', 'planet4-child-theme-backend'),
			'singular_name'		=> _x( 'Special attribute', 'taxonomy singular name', 'planet4-child-theme-backend' ),
			'search_items'		=> __( 'Search attributes', 'planet4-child-theme-backend' ),
			'all_items'			=> __( 'All attributes', 'planet4-child-theme-backend' ),
			'edit_item'			=> __( 'Edit attribute', 'planet4-child-theme-backend' ),
			'update_item'		=> __( 'Update attribute', 'planet4-child-theme-backend' ),
			'add_new_item'		=> __( 'Add New attribute', 'planet4-child-theme-backend' ),
			'new_item_name'		=> __( 'New attribute Name', 'planet4-child-theme-backend' ),
			'menu_name'			=> __( 'Attribute', 'planet4-child-theme-backend' ),
		);
		$args = array(
			'hierarchical'		=> true,
			'labels'			=> $labels,
			'show_ui'			=> true,
			'show_admin_column' => true,
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'attribute' ),
		);
		register_taxonomy( 'p4_post_attribute', array( 'post', 'page' ), $args );

	}

	/**
	 * Add custom options to the main WP_Query.
	 *
	 * @param WP_Query $wp The WP Query to customize.
	 */
	public function add_search_options( WP_Query $wp ) {
	}

	/**
	 * Auto generate excerpt for post.
	 *
	 * @param int	  $post_id Id of the saved post.
	 * @param WP_Post $post Post object.
	 */
	public function p4_auto_generate_excerpt( $post_id, $post ) {
	}

}
