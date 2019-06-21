<?php

// --- in functions.php ---
// require $this->CLASSES . 'post-ajax/post-ajax.php';

// --- in blog page template (page-blog.php) ---
// $Post_Ajax = Post_Ajax::initialize_class();
// $Post_Ajax->render_post_ajax_section();

class Post_Ajax {
  const PREFIX = 'pa_';
  const ACTION = self::PREFIX.'ajax';
  const VERSION = '1';

  private static $instance = null;

  public static function initialize_class() {
    if (null == self::$instance)
      self::$instance = new self;
    return self::$instance;
  }

  private function __construct() {
    $this->PATH = get_template_directory_uri().'/classes/post-ajax/';
    $this->post_type = 'post';
    $this->posts_per_page = 3;
    $this->DOM = array(
      'load_more' => self::PREFIX.'load_more',
      'section_content' => self::PREFIX.'content'
    );

    add_action('wp_enqueue_scripts', array($this, self::PREFIX.'scripts'));
		add_action('wp_enqueue_scripts', array($this, self::PREFIX.'styles'));

    add_action('wp_ajax_nopriv_'.self::ACTION, array($this, self::ACTION));
    add_action('wp_ajax_'.self::ACTION, array($this, self::ACTION));
  }

  function pa_scripts() {
    wp_enqueue_script( self::PREFIX.'script', $this->PATH.'script.js', array('jquery'), self::VERSION, true );
    wp_localize_script( self::PREFIX.'script', self::PREFIX, array(
      'url' => admin_url('admin-ajax.php'),
      'action' => self::ACTION,
      'security' => wp_create_nonce(self::ACTION),
      'DOM' => $this->DOM,
      'error_message' => 'Error Loading Posts'
    ) );
  }

  function pa_styles() {
    wp_enqueue_style(self::PREFIX.'style', $this->PATH.'style.css');
  }

  function pa_ajax() {
    if (!wp_verify_nonce($_REQUEST['security'], self::ACTION))
      exit("Rejected!");

    $html = '';
    $paged = isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : 1;
    $html .= $this->get_post_loop_html($paged);

    echo json_encode(array(
      'paged' => $paged,
      'html' => $html
    ));

    die;
  }

  function handle_post_query( $paged ) {
    $query = array(
      'post_type' => $this->post_type,
      'post_status' => 'publish',
      'posts_per_page' => $this->posts_per_page,
      'paged' => $paged
    );
    $posts = new WP_Query($query);
    return $posts;
  }

  function get_load_more_button() {
    return '<i
      id="'.$this->DOM[ 'load_more' ].'"
      class="fas fa-chevron-down"
      data-posts-per-page="'.$this->posts_per_page.'"
      data-post-type="'.$this->post_type.'"
    ></i>';
  }

  function build_post_template() {
    $output = '';
    $output .= '<article id="post-'.get_the_ID().'">';
    $output .= '<h1 class="entry-title">'.get_the_title().'</h1>';
    if (get_the_post_thumbnail_url())
      $output .= '<img src="'.get_the_post_thumbnail_url().'" alt="Featured Image For '.get_the_title().'" />';
    $output .= '<div class="entry-content">'.get_the_content().'</div>';
    $output .= '</article>';
    return $output;
  }

  function get_post_loop_html($paged = 1) {
    $html = '';
    $posts = $this->handle_post_query($paged);

    if ($posts->have_posts()) :
      $html .= '<input type="hidden" name="max_num_pages" value="'.$posts->max_num_pages.'" />';
      while ($posts->have_posts()) :
        $posts->the_post();
        $html .= $this->build_post_template();
      endwhile;
    endif;

    return $html;
  }

  function render_post_ajax_section() {
    echo '<div class="'.self::PREFIX.'wrapper">';
    echo '<div class="'.$this->DOM['section_content'].'">';
    echo $this->get_post_loop_html(1);
    echo '</div>';
    echo $this->get_load_more_button();
    echo '</div>';
  }
}

Post_Ajax::initialize_class();
