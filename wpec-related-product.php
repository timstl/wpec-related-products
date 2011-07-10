<?php
/**
  * Plugin Name: WP e-Commerce Related Product
  * Description: WPEC Related Products for WP e-Commerce uses information available within the Single Product template to display related Products that belong to the same Product Category.
  * Version: 1.1
  * Author: Onnay Okheng
  * Author URI: http://onnayokheng.com/

    Copyright (C) 2010-2010, Onnay Okheng
    All rights reserved.

    Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    Neither the name of Alex Moss or pleer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

  **/


// Checking page if is admin page
if(is_admin()){
    
    // call function for admin menu
    add_action('admin_menu', 'on_wpec_options');
    
    // function for google +1 options
    function on_wpec_options(){
        
        // add a new setting submenu
        add_options_page('WPEC Related Products', 'WPEC Related Products', 'manage_options', 'wpec-related-products', 'on_wpec_panel');        
        
    }
       
}

/**
 * Function for displaying the related products
 *
 * @global type $post 
 */
function on_wpec_related(){
    global $post;
    
        // get related from produt category.
        $product_cat = wp_get_object_terms(wpsc_the_product_id(), 'wpsc_product_category');
        
        foreach ($product_cat as $cat_item) {
            $cat_array_name_list[] = $cat_item->slug;
        }
        
        $number = (get_option('on_wpec_number') == '')? 4: get_option('on_wpec_number');
        $title  = (get_option('on_wpec_title') == '')? 'Related Products': get_option('on_wpec_title');

        if (empty($related_product)) {
             $query = array (
                'showposts' => $number,
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
                    'showposts' => $number,
                    'orderby'   => 'rand',
                    'post_type' => 'wpsc-product',
                    'post__not_in' => array ($post->ID),
                );
                $related_product = new WP_Query($query);
            }

            if($related_product->have_posts()):
                
                echo "<div class='wpec-related-wrap'>";
            
                echo "<h2>".$title."</h2>";
                
                while($related_product->have_posts()) : $related_product->the_post();
            ?>

                    <div class="wpec-related-product product-<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?>">

                        <?php if(get_option('on_wpec_image') == 'on') : ?>
                            <div class="wpec-related-image" id="related-pro-<?php echo wpsc_the_product_id(); ?>">
                                    <a href="<?php echo wpsc_the_product_permalink(); ?>">

                                        <?php if(wpsc_the_product_thumbnail()) : ?>
                                                    <img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(100, 100); ?>"/>
                                        <?php else: ?>
                                                    <img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/noimage.png" width="100" height="100" />	
                                        <?php endif; ?>
                                    </a>
                            </div><!--close imagecol-->
                        <?php endif; ?>

                            <h3 class="wpec-related-title">
                                    <?php if(get_option('hide_name_link') == 1) : ?>
                                            <?php echo wpsc_the_product_title(); ?>
                                    <?php else: ?> 
                                            <a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
                                    <?php endif; ?>
                            </h3>


                        <?php if(get_option('on_wpec_price') == 'on') : ?>
                            <div class="product-info">
                                    <div class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>: <span id='product_price_<?php echo wpsc_the_product_id(); ?>' class="currentprice pricedisplay"><?php echo wpsc_the_product_price(); ?></span></div>
                            </div>
                        <?php endif; ?>

                    </div><!-- close default_product_display -->

<?php
                endwhile;
                
                echo "</div><div class='clear'></div>";
                
            endif;
            wp_reset_query();
        }
        
}

function on_wpec_related_style(){
?>
        <style>
            .wpec-related-wrap{margin: 20px 0; padding: 0; display: inline-block;}
            .wpec-related-product{float: left; padding: 0 3px; width: 110px;}
            .wpec-related-title{margin:0 !important;}
        </style>
                    
<?php
}

add_action('wpsc_product_addon_after_descr', 'on_wpec_related');
add_action('wp_head','on_wpec_related_style');


/**
 * Function for display the Plugin Panel Options.
 */
function on_wpec_panel() { ?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2>WPEC Related Products Options</h2>

    <form method="post" action="options.php" id="options">
    <?php wp_nonce_field('update-options') ?>
                
        <table class="form-table">
            <tbody>

                <tr valign="top">
                    <th scope="row">Title</th>
                    <td>
                            <input type="text" name="on_wpec_title" value="<?php echo get_option('on_wpec_title'); ?>" />
                            <br/>Default is "Related Products".
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Number of related products</th>
                    <td>
                            <input type="text" name="on_wpec_number" value="<?php echo get_option('on_wpec_number'); ?>" />
                            <br/>Default is 4.
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Show image</th>
                    <td>                        
                            <?php $checked_image = (get_option('on_wpec_image') == 'on') ? ' checked="yes"' : ''; ?>                    
                            <label id="on_wpec_image" ><input type="checkbox" id="on_wpec_image" name="on_wpec_image"<?php echo $checked_image; ?> /> Enabled / Disabled</label>                                    
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Show price</th>
                    <td>                        
                            <?php $checked_price = (get_option('on_wpec_price') == 'on') ? ' checked="yes"' : ''; ?>                    
                            <label id="on_wpec_price" ><input type="checkbox" id="on_wpec_price" name="on_wpec_price"<?php echo $checked_price; ?> /> Enabled / Disabled</label>                                    
                    </td>
                </tr>

            </tbody>
        </table>
        
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="on_wpec_image, on_wpec_number, on_wpec_title, on_wpec_price" />
        <div class="submit"><input type="submit" class="button-primary" name="submit" value="Save Settings"></div>


    </form>

</div>

<?php } ?>