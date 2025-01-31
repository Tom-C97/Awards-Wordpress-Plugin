<?php
    /**
     * Plugin Name: Awards Plugin
     * Plugin URI: https://github.com/Tom-C97
     * Description: A plugin to display awards in a carousel.
     * Version: 1.2
     * Author: Tom Cave
     * Author URI: https://github.com/Tom-C97
     */

    // Register Custom Post Type
    function awards_plugin_register_post_type() {
      $labels = array(
        'name' => _x('Awards', 'Post Type General Name', 'awards-plugin'),
        'singular_name' => _x('Award', 'Post Type Singular Name', 'awards-plugin'),
        'menu_name' => __('Awards', 'awards-plugin'),
        'name_admin_bar' => __('Award', 'awards-plugin'),
        'archives' => __('Award Archives', 'awards-plugin'),
        'attributes' => __('Award Attributes', 'awards-plugin'),
        'parent_item_colon' => __('Parent Award:', 'awards-plugin'),
        'all_items' => __('All Awards', 'awards-plugin'),
        'add_new_item' => __('Add New Award', 'awards-plugin'),
        'add_new' => __('Add New', 'awards-plugin'),
        'new_item' => __('New Award', 'awards-plugin'),
        'edit_item' => __('Edit Award', 'awards-plugin'),
        'update_item' => __('Update Award', 'awards-plugin'),
        'view_item' => __('View Award', 'awards-plugin'),
        'view_items' => __('View Awards', 'awards-plugin'),
        'search_items' => __('Search Award', 'awards-plugin'),
        'not_found' => __('Not found', 'awards-plugin'),
        'not_found_in_trash' => __('Not found in Trash', 'awards-plugin'),
        'featured_image' => __('Featured Image', 'awards-plugin'),
        'set_featured_image' => __('Set featured image', 'awards-plugin'),
        'remove_featured_image' => __('Remove featured image', 'awards-plugin'),
        'use_featured_image' => __('Use as featured image', 'awards-plugin'),
        'insert_into_item' => __('Insert into award', 'awards-plugin'),
        'uploaded_to_this_item' => __('Uploaded to this award', 'awards-plugin'),
        'items_list' => __('Awards list', 'awards-plugin'),
        'items_list_navigation' => __('Awards list navigation', 'awards-plugin'),
        'filter_items_list' => __('Filter awards list', 'awards-plugin'),
      );
      $args = array(
        'label' => __('Award', 'awards-plugin'),
        'description' => __('Awards', 'awards-plugin'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'capabilities' => array(
          'publish_posts' => 'publish_awards',
          'edit_posts' => 'edit_awards',
          'edit_others_posts' => 'edit_others_awards',
          'delete_posts' => 'delete_awards',
          'delete_others_posts' => 'delete_others_awards',
          'read_private_posts' => 'read_private_awards',
          'edit_post' => 'edit_award',
          'delete_post' => 'delete_award',
          'read_post' => 'read_award',
        ),
        'map_meta_cap' => true,
      );
      register_post_type('award', $args);
    }
    add_action('init', 'awards_plugin_register_post_type', 0);

    // Enqueue styles and scripts
    function awards_plugin_enqueue_scripts() {
      wp_enqueue_style('swiper-bundle', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/10.0.4/swiper-bundle.min.css');
      wp_enqueue_style('awards-plugin-styles', plugin_dir_url(__FILE__) . 'styles.css');
      wp_enqueue_script('swiper-bundle', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/10.0.4/swiper-bundle.min.js', [], null, true);
      wp_enqueue_script('awards-plugin-script', plugin_dir_url(__FILE__) . 'script.js', ['swiper-bundle'], null, true);
    }
    add_action('wp_enqueue_scripts', 'awards_plugin_enqueue_scripts');

    // Shortcode to display awards
    function awards_plugin_shortcode() {
      ob_start();
      ?>
      <div class="awards-container">
          <div class="swiper">
              <div class="swiper-wrapper">
                  <?php
                  $query = new WP_Query(array(
                    'post_type' => 'award',
                    'posts_per_page' => -1,
                    'meta_key' => 'award_year',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                  ));
                  $awards = array();
                  $current_slide = array();
                  $slide_index = 0;

                  if ($query->have_posts()) {
                    while ($query->have_posts()) {
                      $query->the_post();
                      $current_slide[] = array(
                        'title' => get_the_title(),
                        'content' => wp_kses_post(get_the_content()),
                        'year' => get_post_meta(get_the_ID(), 'award_year', true),
                      );

                      if (count($current_slide) === 3) {
                        $awards[$slide_index] = $current_slide;
                        $current_slide = array();
                        $slide_index++;
                      }
                    }

                    if (!empty($current_slide)) {
                      $awards[$slide_index] = $current_slide;
                    }

                    foreach ($awards as $slide) {
                      echo '<div class="swiper-slide">';
                      echo '<div class="award-grid">';
                      foreach ($slide as $award) {
                        echo '<div class="award">';
                        echo '<div>' . esc_html($award['title']) . '</div>';
                        echo '<div class="award-year">' . esc_html($award['year']) . '</div>';
                        echo '</div>';
                      }
                      echo '</div>';
                      echo '</div>';
                    }
                  }
                  wp_reset_postdata();
                  ?>
              </div>
              <!-- Add Navigation -->
              <div class="swiper-button-next"></div>
              <div class="swiper-button-prev"></div>
              <!-- Add Pagination -->
              <div class="swiper-pagination"></div>
          </div>
      </div>
      <?php
      return ob_get_clean();
    }
    add_shortcode('awards', 'awards_plugin_shortcode');

    // Add meta box for award year
    function awards_plugin_add_meta_box() {
      add_meta_box(
        'award_year',
        __('Award Year', 'awards-plugin'),
        'awards_plugin_meta_box_callback',
        'award',
        'side'
      );
    }
    add_action('add_meta_boxes', 'awards_plugin_add_meta_box');

    function awards_plugin_meta_box_callback($post) {
      wp_nonce_field('awards_plugin_save_meta_box_data', 'awards_plugin_meta_box_nonce');
      $value = get_post_meta($post->ID, 'award_year', true);
      echo '<label for="award_year">' . __('Year', 'awards-plugin') . '</label>';
      echo '<input type="text" id="award_year" name="award_year" value="' . esc_attr($value) . '" size="25" />';
    }

    function awards_plugin_save_meta_box_data($post_id) {
      if (!isset($_POST['awards_plugin_meta_box_nonce'])) {
        error_log('Nonce not set for awards_plugin_save_meta_box_data');
        return;
      }
      if (!wp_verify_nonce($_POST['awards_plugin_meta_box_nonce'], 'awards_plugin_save_meta_box_data')) {
        error_log('Nonce verification failed for awards_plugin_save_meta_box_data');
        return;
      }
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
      }
      if (isset($_POST['post_type']) && 'award' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
          return;
        }
      } else {
        return;
      }
      if (!isset($_POST['award_year'])) {
        return;
      }
      $year = sanitize_text_field($_POST['award_year']);
      if (!preg_match('/^\d{4}$/', $year)) {
        error_log('Invalid year format for awards_plugin_save_meta_box_data');
        return;
      }
      update_post_meta($post_id, 'award_year', $year);
    }
    add_action('save_post', 'awards_plugin_save_meta_box_data');
    ?>
