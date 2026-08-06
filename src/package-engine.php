<?php
function enqueue_package_slider_assets() {
    wp_enqueue_script('jquery');
    
    // Slick Carousel CSS & JS
    wp_enqueue_style('slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
    wp_enqueue_style('slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css');
    wp_enqueue_script('slick-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.8.1', true);
}
add_action('wp_enqueue_scripts', 'enqueue_package_slider_assets');

function enqueue_package_custom_fonts() {
    wp_enqueue_style('google-font-great-vibes', 'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap', array(), null);
}
add_action('wp_enqueue_scripts', 'enqueue_package_custom_fonts');

/**
 * 1. REGISTER CUSTOM POST TYPE & TAXONOMY
 */
function register_custom_event_packages_cpt() {
    register_post_type('custom_package', array(
        'labels' => array(
            'name'               => __('Event Packages'),
            'singular_name'      => __('Event Package'),
            'add_new'            => __('Add New Package'),
            'add_new_item'       => __('Add New Event Package'),
            'edit_item'          => __('Edit Event Package'),
            'all_items'          => __('All Event Packages'),
            'menu_name'          => __('Event Packages')
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'package-item'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'editor', 'thumbnail'),
        'menu_icon'          => 'dashicons-archive',
    ));

    register_taxonomy('event_package_cat', 'custom_package', array(
        'labels' => array(
            'name'              => __('Package Categories'),
            'singular_name'     => __('Package Category'),
            'search_items'      => __('Search Package Categories'),
            'all_items'         => __('All Package Categories'),
            'parent_item'       => __('Parent Category'),
            'parent_item_colon' => __('Parent Category:'),
            'edit_item'         => __('Edit Package Category'),
            'update_item'       => __('Update Package Category'),
            'add_new_item'      => __('Add New Package Category'),
            'new_item_name'     => __('New Package Category Name'),
            'menu_name'         => __('Package Categories'),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'package-category'),
    ));
}
add_action('init', 'register_custom_event_packages_cpt', 0);


/**
 * 2. SHORTCODE TO RENDER INDIVIDUAL PACKAGE CARDS
 * Usage: [display_packages category="engagement"]
 */
function render_packages_by_category($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts, 'display_packages');

    // Enqueue Slick Slider assets
    wp_enqueue_script('slick-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.8.1', true);
    wp_enqueue_style('slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
    wp_enqueue_style('slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css');

    $args = array(
        'post_type'      => 'custom_package',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'event_package_cat',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ),
        ),
    );

    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '<p class="no-packages-found">No packages found in this category.</p>';
    }

    ob_start();
    while ($query->have_posts()) : $query->the_post();
        $post_id        = get_the_ID();
        $title          = get_the_title();
        $subtitle       = get_field('package_subtitle', $post_id);
        $short_desc     = get_field('package_short_description', $post_id);
        $price          = get_field('package_starting_price', $post_id);
        $min_book       = get_field('package_min_booking', $post_id);
        $includes       = get_field('package_includes', $post_id);
        $addons         = get_field('package_addons', $post_id);
        $gallery_images = get_field('package_gallery', $post_id);
        $bonus          = get_field('bonus_offer', $post_id);

        // --- CONSTRUCT ENQUIRY PAGE URL WITH QUERY PARAMETERS ---
        // Change '/package-enquiry/' to your actual enquiry page slug in WP
        $enquiry_url = add_query_arg(array(
            'pkg_title' => rawurlencode($title),
            'pkg_sub'   => rawurlencode($subtitle),
            'pkg_price' => rawurlencode($price),
            'pkg_min'   => rawurlencode($min_book),
        ), site_url('/package-enquiry/'));
        ?>

        <div class="package-card-item">
            <!-- Single Image Slider with Arrows & Dots -->
            <?php if ( $gallery_images ): ?>
                <div class="package-gallery-slider">
                    <?php foreach ( $gallery_images as $image ): ?>
                        <div class="slide-item">
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Package Header Info -->
            <div class="package-header-info">
                <h1 class="package-title"><?php the_title(); ?></h1>
                
                <?php if ($subtitle): ?>
                    <h2 class="package-subtitle"><?php echo esc_html($subtitle); ?></h2>
                <?php endif; ?>
                
                <?php if ($short_desc): ?>
                    <p class="package-short-desc"><?php echo esc_html($short_desc); ?></p>
                <?php endif; ?>

                <div class="package-pricing-badge">
                    <span class="price-label">FROM</span>
                    <span class="price-amount"><?php echo esc_html($price); ?></span>
                    <span class="price-sub">PER GUEST</span>
                </div>

                <?php if ($min_book): ?>
                    <div class="package-min-booking">
                        <span>
                          <svg class="guest-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                          </svg> MINIMUM BOOKING: <?php echo esc_html($min_book); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- UPDATED BUTTON 1: Converted to Link -->
                <a href="<?php echo esc_url($enquiry_url); ?>" class="elementor-button enquire-now-btn">
                    ENQUIRE NOW <span>&rarr;</span>
                </a>
            </div>

            <!-- Details Grid: Includes & Add-ons -->
            <div class="package-details-grid">
                <div class="package-includes-column">
                    <h3 class="column-title">PACKAGE INCLUDES</h3>
                    <?php echo $includes; ?>
                </div>
                <div class="package-addons-column">
                    <h3 class="column-title">ENHANCE YOUR EVENT</h3>
                    <?php echo $addons; ?>
                </div>
            </div>

            <!-- Free Offers Section -->
            <div class="bonus-offer-card">
              <div class="bonus-header">
                <h2 class="bonus-title">
                  <?php echo esc_html( $bonus['main_bonus_heading'] ?? 'BOOK ANY PACKAGE AND RECEIVE' ); ?>
                </h2>
              </div>
              
              <div class="bonus-grid">
                <!-- Offer 1 -->
                <div class="bonus-item">
                  <div class="bonus-icon-circle">
                    <svg viewBox="0 0 24 24" class="gift-icon" aria-hidden="true">
                      <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                    </svg>
                  </div>
                  <div class="bonus-content">
                    <span class="bonus-tag">FREE</span>
                    <h5><?php echo esc_html( $bonus['offer_1_title'] ?? 'DESSERT TABLE STYLING' ); ?></h5>
                    <p><?php echo esc_html( $bonus['offer_1_desc'] ?? 'Beautifully styled to complement your theme.' ); ?></p>
                  </div>
                </div>

                <!-- Offer 2 -->
                <div class="bonus-item">
                  <div class="bonus-icon-circle">
                    <svg viewBox="0 0 24 24" class="gift-icon" aria-hidden="true">
                      <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                    </svg>
                  </div>
                  <div class="bonus-content">
                    <span class="bonus-tag">FREE</span>
                    <h5><?php echo esc_html( $bonus['offer_2_title'] ?? 'THEMED COCKTAIL STATION STYLING' ); ?></h5>
                    <p><?php echo esc_html( $bonus['offer_2_desc'] ?? 'Colour-matched glasses to elevate your celebration.' ); ?></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom CTA Card -->
            <div class="package-bottom-cta">
                <div class="cta-text">
                    <div class="calendar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                            <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                  d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                        </svg>
                    </div>
                    
                    <div class="cta-inner">
                        <strong>Let's bring your vision to life.</strong>
                        <p>Our team is here to create an unforgettable experience.</p>
                    </div>
                </div>
                
                <!-- UPDATED BUTTON 2: Converted to Link -->
                <a href="<?php echo esc_url($enquiry_url); ?>" class="elementor-button secondary-enquire-btn">
                    ENQUIRE NOW <span>&rarr;</span>
                </a>
            </div>
        </div>

    <?php 
    endwhile;
    wp_reset_postdata();

    // Inline JS for initialization & recalculation on tab switch
    ?>
    <script>
    jQuery(document).ready(function($) {
        function initPackageSliders() {
            $('.package-gallery-slider').each(function() {
                if (!$(this).hasClass('slick-initialized')) {
                    $(this).slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: true,
                        adaptiveHeight: true,
                        infinite: true,
                        prevArrow: '<button type="button" class="slick-prev">&#10094;</button>',
                        nextArrow: '<button type="button" class="slick-next">&#10095;</button>'
                    });
                }
            });
        }

        initPackageSliders();

        $('.category-tab-item').on('click', function() {
            setTimeout(function() {
                $('.package-gallery-slider').slick('setPosition');
            }, 100);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('display_packages', 'render_packages_by_category');

/**
 * 3. MASTER INTERACTIVE PAGE SHORTCODE (CATEGORY TABS + CONTENT PANELS)
 * Usage on Elementor page: [event_packages_interactive_page]
 */
function render_full_packages_interactive_page() {
    $categories = get_terms(array(
        'taxonomy'   => 'event_package_cat',
        'hide_empty' => true,
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return '<p class="no-categories-found">No packages found.</p>';
    }

    ob_start();
    ?>
    <div class="packages-master-wrapper">
        
        <!-- Top Category Horizontal Scroll Navigation -->
        <div class="category-scroll-container">
            <div class="category-tab-wrapper" id="categoryTabWrapper">
                <?php foreach ($categories as $index => $cat): 
                    $active_class = ($index === 0) ? 'active' : '';
                    
                    // Retrieve ACF Icon Field
                    $cat_icon = get_field('category_icon', 'event_package_cat_' . $cat->term_id); 
                    $icon_url = is_array($cat_icon) ? $cat_icon['url'] : $cat_icon;
                ?>
                    <button class="category-tab-item <?php echo $active_class; ?>" data-target="cat-tab-<?php echo esc_attr($cat->term_id); ?>">
                        <div class="cat-icon-box">
                            <?php if ($icon_url): ?>
                                <img src="<?php echo esc_url($icon_url); ?>" class="cat-icon" alt="<?php echo esc_attr($cat->name); ?>" />
                            <?php endif; ?>
                        </div>
                        <span class="cat-name"><?php echo esc_html($cat->name); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="swipe-hint"><span>&longleftarrow;</span> SWIPE LEFT OR RIGHT TO EXPLORE ALL PACKAGES <span>&longrightarrow;</span></div>

        <!-- Dynamic Category Content Panels -->
        <div class="category-content-panels">
            <?php foreach ($categories as $index => $cat): 
                $active_class = ($index === 0) ? 'active' : '';
            ?>
                <div class="category-panel-item <?php echo $active_class; ?>" id="cat-tab-<?php echo esc_attr($cat->term_id); ?>">
                    <?php echo render_packages_by_category(array('category' => $cat->slug)); ?>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('event_packages_interactive_page', 'render_full_packages_interactive_page');
