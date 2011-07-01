<?php
/**
  * Plugin Name: WP e-Commerce Related Products
  * Description: WPEC Related Products for WP e-Commerce uses information available within the Single Product template to display related Products that belong to the same Product Category.
  * Version: 1.0
  * Author: Onnay Okheng
  * Author URI: http://onnayokheng.com/
  **/

/**
 * Function for displaying the related products
 *
 * @global type $post 
 */
function wpec_related(){
    global $post;
    
        $product_cat = wp_get_object_terms(wpsc_the_product_id(), 'wpsc_product_category');
        
        foreach ($product_cat as $cat_item) {
            $cat_array_name_list[] = $cat_item->slug;
        }

        if (empty($related_product)) {
             $query = array (
                'showposts' => 4,
                'orderby'   => 'rand',
                'post_type' => 'wpsc-product',
                'tax_query' => array(
                        array(
                                'taxonomy'  => 'wpsc_product_category',
                                'field'     => 'slug',
                                'terms'     => $cat_array_name_list
                        )
                ),
                'post__not_in' => array ($post->ID),
            );
            $related_product = new WP_Query($query);

            if(!$related_product->have_posts()){

                 $query = array (
                    'showposts' => 4,
                    'orderby'   => 'rand',
                    'post_type' => 'wpsc-product',
                    'post__not_in' => array ($post->ID),
                );
                $related_product = new WP_Query($query);
            }

            if($related_product->have_posts()):
                
                echo "<div class='wpec-related-wrap'>";
            
                echo "<h2>".__('Related Products')."</h2>";
                
                while($related_product->have_posts()) : $related_product->the_post();
            ?>

                    <div class="wpec-related-product product-<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?>">

                            <div class="wpec-related-image" id="related-pro-<?php echo wpsc_the_product_id(); ?>">
                                    <a href="<?php echo wpsc_the_product_permalink(); ?>">

                                        <?php if(wpsc_the_product_thumbnail()) : ?>
                                                    <img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(100, 100); ?>"/>
                                        <?php else: ?>
                                                    <img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/noimage.png" width="100" height="100" />	
                                        <?php endif; ?>
                                    </a>
                            </div><!--close imagecol-->

                            <h3 class="wpec-related-title">
                                    <?php if(get_option('hide_name_link') == 1) : ?>
                                            <?php echo wpsc_the_product_title(); ?>
                                    <?php else: ?> 
                                            <a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
                                    <?php endif; ?>
                            </h3>


                            <div class="product-info">
                                    <div class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>: <span id='product_price_<?php echo wpsc_the_product_id(); ?>' class="currentprice pricedisplay"><?php echo wpsc_the_product_price(); ?></span></div>
                            </div>

                    </div><!-- close default_product_display -->

<?php
                endwhile;
                
                echo "</div><div class='clear'></div>";
                
            endif;
            wp_reset_query();
        }
        
}

function wpec_related_style(){
?>
        <style>
            .wpec-related-wrap{margin: 20px 0; padding: 0; display: inline-block;}
            .wpec-related-product{float: left; padding: 0 3px; width: 110px;}
            .wpec-related-title{margin:0 !important;}
        </style>
                    
<?php
}

add_action('wpsc_product_addon_after_descr', 'wpec_related');
add_action('wp_head','wpec_related_style');

?>
