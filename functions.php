<?php
/*================================================
#Load the Parent theme style.css file
================================================*/
function dt_enqueue_styles() {
  $parenthandle = 'divi-style'; 
  $theme = wp_get_theme();
  wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css', 
    array(),  // if the parent theme code has a dependency, copy it to here
    $theme->parent()->get('Version')
  );
  wp_enqueue_style( 'child-style', get_stylesheet_uri(),
    array( $parenthandle ),
    $theme->get('Version') 
  );
}
add_action( 'wp_enqueue_scripts', 'dt_enqueue_styles' );

// WooCommerce Overrides
// disable add to cart
add_filter( 'woocommerce_is_purchasable', '__return_false');

function argo_var_dump( $var ) {
  echo '<pre>';
  var_dump($var);
  echo '</pre>';
}

// custom projects module filtered by project category
function argo_generate_custom_projects_module( $query_args ) {
  $query = new WP_Query($query_args);

  $html = '';
  if ( $query->have_posts() ) : 
    $html .= '<div class="argo-projects">';
    while ( $query->have_posts() ) : 
      $query->the_post();
      $id = get_the_ID();
      $html .= '<article class="post-' . $id .' project has-post-thumbnail hentry">';

      if ( has_post_thumbnail( $id ) ) :
        $html .= '<div class="et_pb_image_container">';
        $html .= '<a href="' . get_permalink($id) . '" title="'. esc_attr( get_the_title() ) .'" class="entry-featured-image-url">';
        $html .= get_the_post_thumbnail($id);
        $html .= '</a>';
        $html .= '</div>';
      endif;

      $html .= '<a href="' . get_permalink($id) . '" title="'. esc_attr( get_the_title() ) .'">';
        $html .= '<h3 class="entry-title">' . get_the_title($id) . '</h3>';
      $html .= '</a>';

      if ( has_excerpt( $id) ) :
        $html .= '<div class="post-content">';
        $html .= '<p>' . get_the_excerpt($id) . '</p>';
        $html .= '</div>';
      endif;
      $html .= '</article>';
    endwhile;
    $html .= '</div>'; // end .argo-projects
  endif;
  wp_reset_query();
  wp_reset_postdata();

  return $html;
}

function argo_create_custom_project_feed( $args ) {
    /**
   * @var array $args
   */
  $args = wp_parse_args( $args );
  $project_cat = isset($args['project_category']) ? esc_attr( $args['project_category'] ) : 'fabrication';

  $query_args = array(
    'post_type' => 'project',
    'orderby' => 'date',
    'order' => 'DESC',
    'posts_per_page' => 3,
    'tax_query' => array(
      array(
        'taxonomy' => 'project_category',
        'field' => 'slug',
        'terms' => $project_cat,
      ),
    ),
  );

  $html = argo_generate_custom_projects_module( $query_args );
  return $html;
}

function argo_custom_projects_module_shortcode( $atts ) {
  $html = argo_create_custom_project_feed( $atts );
  return $html;
}
add_shortcode('argo_custom_projects_module', 'argo_custom_projects_module_shortcode');