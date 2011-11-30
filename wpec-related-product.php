<?php
/**
  * Plugin Name: WP e-Commerce Related Product
  * Description: WPEC Related Products for WP e-Commerce uses information available within the Single Product template to display related Products that belong to the same Product Category.
  * Version: 1.2
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

function on_wpec_related_add_settings_page($page_hooks, $base_page) {
	$page_hooks[] = add_submenu_page($base_page,__('- Related Products'), __('- Related Products'), 9, 'wpec-related-products', 'on_wpec_panel');
	return $page_hooks;
}

add_filter('wpsc_additional_pages', 'on_wpec_related_add_settings_page', 10, 2);

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

$place_related  = get_option('on_wpec_place', 'wpsc_product_addon_after_descr');
$display_on     = get_option('on_wpec_display', 'All');
if($display_on != "All" AND on_is_single_product() OR $display_on == "All")
    add_action($place_related, 'on_wpec_related');

add_action('wp_head','on_wpec_related_style');


    
/**
 * Check is on single product or not.
 *
 * @global  $wp_query
 * @return TRUE/FALSE
 */
function on_is_single_product(){
    global $wp_query;

    if(@$wp_query->query_vars['post_type'] == 'wpsc-product'){
            return true;
    }

    return false;
}

/**
 * Function for display the Plugin Panel Options.
 */
function on_wpec_panel() { ?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2>WPEC Related Products Options</h2>
    
    <div style="float: right; width: 300px; padding: 5px; background-color: #FFFBCC; border: 1px solid #E6DB55; color: #555;">
        <h3>Thanks a lot</h3>
        <p>Thanks for using my plugin, you can contact me for say hello <a href="http://onnayokheng.com">Onnay Okheng</a> or buy me a cup of chocolate :)</p>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHVwYJKoZIhvcNAQcEoIIHSDCCB0QCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBfogXKUGqOEURIb66Iqd1chY6JsFyLtYwM2P2hjVyaQOyN7HkebS+w+9eBNjMeosB7ArSwv50QRUfCdNN4YpdFPwzjzgGjQvkRLX9RLYB69HQXXgfBupwQ3YtEmA9fvOeP5q0jr4lqsaIg7OMwUInw0j1LdMfJCBF4LFhcbWTxHDELMAkGBSsOAwIaBQAwgdQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQI1MH4PI09MJyAgbCnnS8yZ97kIo08fX2lgv/6ErmuEWXj+pF4e/9DHeT2OQS1uFheMeVGrmoOKdHv7GoHeCuCPwvy9NFBYTMr7Pkhme5NmT7k2sbjvUsB14KLhsHLK6b0tQffNgkeOl+j0k1N/Du5sCKi/mWLbEngPPsnOKVKtpjdKgfCPkvYiJ98/YLsTjBjUphNDdtWbsRwK1euOefyeKqzRK4yXDkbQeTajNt+7bb5guYj5i04qBmaZ6CCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTExMTEzMDAzNDAyOFowIwYJKoZIhvcNAQkEMRYEFEj/R/oHFWWjfwSWwsOICUp2LkiZMA0GCSqGSIb3DQEBAQUABIGAE36vf7PY/CTJQsZ9dtSgrFFVk3RRXBLIvMps9N88DPEIEB277+cbpML8PFWVNDP16X4FOZl9lW2CBkdqNy4JTXY00lOcCw9mp6Xvb3/HB9NgPrG+VzawZWtLdxx3HlF1P2aiFGciwoMMlRw1GPWQCBZWHL3Lki/q1D86+XYGUM8=-----END PKCS7-----
        ">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    </div>

    <form method="post" action="options.php" id="options" style="float: left;">
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

                <tr valign="top">
                    <th scope="row">Related by</th>
                    <td>
                        <?php $related_array  = array('wpsc_product_category', 'product_tag'); ?>
                        <?php $related        = get_option('on_wpec_related_by', 'wpsc_product_category'); ?>
                        <select name="on_wpec_related_by">
                        <?php 
                            foreach($related_array as $item):
                                $selected = ($related == $item)? ' selected="selected"':'';
                                echo '<option'.$selected.'>'.$item.'</option>';
                            endforeach;
                        ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Placement products</th>
                    <td>
                        <?php $place_array  = array('wpsc_product_before_description', 'wpsc_product_addons', 'wpsc_product_addon_after_descr'); ?>
                        <?php $place        = get_option('on_wpec_place', 'wpsc_product_addon_after_descr'); ?>
                        <select name="on_wpec_place">
                        <?php 
                            foreach($place_array as $item):
                                $selected = ($place == $item)? ' selected="selected"':'';
                                echo '<option'.$selected.'>'.$item.'</option>';
                            endforeach;
                        ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Display on</th>
                    <td>
                        <?php $display_array  = array('All', 'Single Product'); ?>
                        <?php $display        = get_option('on_wpec_display', 'All'); ?>
                        <select name="on_wpec_display">
                        <?php 
                            foreach($display_array as $item):
                                $selected = ($display == $item)? ' selected="selected"':'';
                                echo '<option'.$selected.'>'.$item.'</option>';
                            endforeach;
                        ?>
                        </select>
                    </td>
                </tr>

            </tbody>
        </table>
        
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="on_wpec_image, on_wpec_number, on_wpec_title, on_wpec_price, on_wpec_related_by, on_wpec_place, on_wpec_display" />
        <div class="submit"><input type="submit" class="button-primary" name="submit" value="Save Settings"/></div>

    </form>

</div>

<?php } ?>
