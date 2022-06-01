<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * functions.php
 * Add PHP snippets here
 */

// remvoe hidden category
add_filter( 'get_terms', 'ts_get_subcategory_terms', 10, 3 );
function ts_get_subcategory_terms( $terms, $taxonomies, $args ) {
$new_terms = array();
// if it is a product category and on the shop page
if ( in_array( 'product_cat', $taxonomies ) && ! is_admin() ) {
foreach( $terms as $key => $term ) {
if ( !in_array( $term->slug, array( 'additional-shipping-charge' ) ) ) { //pass the slug name here
$new_terms[] = $term;
}}
$terms = $new_terms;
}
return $terms;
}

//remove wise logo
add_filter( 'woocommerce_gateway_icon', 'my_gateway_icon_wise', 10, 2 );
function my_gateway_icon_wise( $icon_html, $gateway_id ) {
    if( $gateway_id == 'ew_wise' ) { 
        $icon_html = '';
    }
    return $icon_html;
}


//remove account
function WOO_login_redirect( $redirect, $user ) {

    $redirect_page_id = url_to_postid( $redirect );
    $checkout_page_id = wc_get_page_id( 'checkout' );

    if ($redirect_page_id == $checkout_page_id) {
        return $redirect;
    }

    return get_permalink(get_option('woocommerce_myaccount_page_id')) . 'orders/';

}

add_action('woocommerce_login_redirect', 'WOO_login_redirect', 10, 2);

//remove acount
function WOO_account_menu_items($items) {
    unset($items['dashboard']);
    return $items;            
}

add_filter ('woocommerce_account_menu_items', 'WOO_account_menu_items');


// Remove the product description Title
add_filter( 'woocommerce_product_description_heading', '__return_null' );


// Change the product description title
add_filter('woocommerce_product_description_heading', 'change_product_description_heading');
function change_product_description_heading() {
 return __('', 'woocommerce');
}

/* Remove Categories from Single Products */
remove_action( 'woocommerce_single_product_summary',
'woocommerce_template_single_meta', 40 );

add_action( 'woocommerce_single_product_summary', 'bbloomer_show_sku_again_single_product', 40 );
 
function bbloomer_show_sku_again_single_product() {
   global $product;
   ?>
   <div class="product_meta">
   <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
      <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>
   <?php endif; ?>
   </div>
   <?php
}

/* remove additioanl information */
add_filter( 'woocommerce_product_tabs', 'bbloomer_remove_product_tabs', 9999 );
  
function bbloomer_remove_product_tabs( $tabs ) {
    unset( $tabs['additional_information'] ); 
    return $tabs;
}

/* buy now */
function add_content_after_addtocart() {
// get the current post/product ID
$current_product_id = get_the_ID();
// get the product based on the ID
$product = wc_get_product( $current_product_id );
// get the "Checkout Page" URL
$checkout_url = wc_get_checkout_url();
// run only on simple products
if( $product->is_type( 'simple' ) ){
echo '<a href="'.$checkout_url.'?add-to-cart='.$current_product_id.'" class="buy-now button">Buy Now</a>';
//echo '<a href="'.$checkout_url.'" class="buy-now button">Buy Now</a>';
}
}
add_action( 'woocommerce_after_add_to_cart_button', 'add_content_after_addtocart' );



add_action( 'init', 'jk_remove_storefront_handheld_footer_bar' );

function jk_remove_storefront_handheld_footer_bar() {
  remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
}


/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function my_hide_shipping_when_free_is_available( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
			break;
		}
	}
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );




/* replace billing and shipping address */

function wc_custom_addresses_labels( $translated_text, $text, $domain )
{
    switch ( $translated_text )
    {
        case 'Billing Address' : /* Front-end */
            $translated_text = __( 'Shipping address', 'woocommerce' );
            break;
            
        case 'Shipping Address' : /* Front-end */
            $translated_text = __( 'Billing address', 'woocommerce' );
            break;

        case 'Billing details' : // Back-end
            $translated_text = __( 'Shipping Info', 'woocommerce' );
            break;

        case 'Ship to a different address?' :
            $translated_text = __( 'Bill to a different address?', 'woocommerce' );
            break;

        case 'Deliver to a different address?' :
            $translated_text = __( 'Bill to a different address?', 'woocommerce' );
            break;

        case 'Shipping details' : // Back-end
            $translated_text = __( 'Billing Info', 'woocommerce' );
            break;

        case 'Ship to' : // Back-end
            $translated_text = __( 'Bill to', 'woocommerce' );
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'wc_custom_addresses_labels', 20, 3 );


function customize_wc_errors( $error ) {
 if ( strpos( $error, 'Billing ' ) !== false ) {
 $error = str_replace("Billing ", "", $error);
 } elseif ( strpos( $error, 'Shipping ' ) !== false ) {
 $error = str_replace("Shipping ", "Billing ", $error);
 }
 return $error;
 }
 add_filter( 'woocommerce_add_error', 'customize_wc_errors' );




/* Add Show All Products to Woocommerce Shortcode */
function woocommerce_shortcode_display_all_products($args)
{
 if(strtolower(@$args['post__in'][0])=='all')
 {
  global $wpdb;
  $args['post__in'] = array();
  $products = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE `post_type`='product'",ARRAY_A);
  foreach($products as $k => $v) { $args['post__in'][] = $products[$k]['ID']; }
 }
 return $args;
}
add_filter('woocommerce_shortcode_products_query', 'woocommerce_shortcode_display_all_products');


//revemoe price 
//remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

//revemoe add to cart
add_action('wp', 'QL_remove_add_to_cart_from_category' );   
function QL_remove_add_to_cart_from_category(){ 
  if( is_product_category( )) { 
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart'); 
    remove_action( 'woocommerce_shop_loop_item_title','woocommerce_template_loop_product_title', 10 );
  } 
}

//revemoe add to cart - related product
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );


// Change WooCommerce "Related Products" default text

add_filter('gettext', 'woocommerce_related_text', 10, 3);
add_filter('ngettext', 'woocommerce_related_text', 10, 3);
function woocommerce_related_text($translated, $text, $domain)
{
     if ($text === 'Related products' && $domain === 'woocommerce') {
         $translated = esc_html__('You may also like', $domain);
     }
     return $translated;
}

add_filter('woocommerce_sale_flash', 'lw_hide_sale_flash');
function lw_hide_sale_flash()
{
return false;
}

/**
 * Change number of related products output
 */ 
function woo_related_products_limit() {
  global $product;
    
    $args['posts_per_page'] = 6;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args', 20 );
  function jk_related_products_args( $args ) {
    $args['posts_per_page'] = 6; // 6 related products
    $args['columns'] = 3; // arranged in 3 columns
    return $args;
}


//remove header sorting option
add_action( 'wp', 'bbloomer_remove_default_sorting_storefront' );
  
function bbloomer_remove_default_sorting_storefront() {
   remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
}

//remove header sorting popularity option
add_filter( 'woocommerce_catalog_orderby', 'bbloomer_remove_sorting_option_woocommerce_shop' );
  
function bbloomer_remove_sorting_option_woocommerce_shop( $options ) {
   unset( $options['popularity'] );
   return $options;
}



add_action( 'after_setup_theme', 'my_remove_product_result_count', 99 );
function my_remove_product_result_count() { 
    remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count', 20 );

}




//show sold
add_action( 'woocommerce_before_shop_loop_item_title', function() {
   global $product;
   if ( !$product->is_in_stock() ) {
       echo '<img class="now_sold" src="https://zeroaqua.com/wp-content/uploads/2021/10/sold-out.png">';
   }
});




//Adding the Open Graph in the Language Attributes
function add_opengraph_doctype( $output ) {
        return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
    }
add_filter('language_attributes', 'add_opengraph_doctype');
 
//Lets add Open Graph Meta Info
 
function insert_fb_in_head() {
    global $post;
    if ( !is_singular()) //if it is not a post or a page
        return;
        echo '<meta property="og:url" content="https://zeroaqua.com/" />';
        echo '<meta property="og:title" content="' . get_the_title() . '"/>';
        echo '<meta property="og:type" content="website"/>';
        echo '<meta property="og:url" content="' . get_permalink() . '"/>';
        echo '<meta property="og:site_name" content="Betta Aquarium Fish Online Store"/>';
        echo '<meta property="og:description" content="100% D.O.A. (death on arrival) Money Back Guarantee" />';
    if(!has_post_thumbnail( $post->ID )) { //the post does not have featured image, use a default image
        $default_image="https://zeroaqua.com/wp-content/uploads/2021/10/zeroaqua.jpg"; //replace this with a default image on your server or an image in your media library
        echo '<meta property="og:image" content="' . $default_image . '"/>';
    }
    else{
        $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
        echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';
    }
    echo "
";
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

// Display Fields using WooCommerce Action Hook
add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_general_product_data_custom_field' );
function woocommerce_general_product_data_custom_field() {
  global $woocommerce, $post;
  echo '<div class="options_group">';
  woocommerce_wp_text_input(
    array(
      'id' => 'ebay_us_start_price',
      'label' => __('eBay US start price', 'woocommerce'),
      'type' => 'number'
    )
  );
  woocommerce_wp_text_input(
    array(
      'id' => 'ebay_us_buy_price',
      'label' => __('eBay US buy now price', 'woocommerce'),
      'type' => 'number'
    )
  );
  echo '</div>';
  echo '<div class="options_group">';
  woocommerce_wp_text_input(
    array(
      'id' => 'yahoo_auction_start_price',
      'label' => __('Yahoo auction start price', 'woocommerce'),
      'type' => 'number'
    )
  );
  woocommerce_wp_text_input(
    array(
      'id' => 'yahoo_auction_buy_price',
      'label' => __('Yahoo auction buy now price', 'woocommerce'),
      'type' => 'number'
    )
  );
  echo '</div>';
  echo '<div class="options_group">';
  woocommerce_wp_text_input(
    array(
      'id' => 'yahoo_shopping_price',
      'label' => __('Yahoo shopping price', 'woocommerce'),
      'type' => 'number'
    )
  );
  echo '</div>';
  echo '<div class="options_group">';
  woocommerce_wp_text_input(
    array(
      'id' => 'shopee_price',
      'label' => __('Shopee price', 'woocommerce'),
      'type' => 'number'
    )
  );
  woocommerce_wp_select(
    array(
      'id' => 'post_to_shopee',
      'label' => __('Post to shopee', 'woocommerce'),
      'options' => array(
		'' => 'Not Post Yet',
        'posted' => 'Posted',
      )
    )
  );
  woocommerce_wp_select(
    array(
      'id' => 'post_to_aquashop',
      'label' => __('Post to aquashop', 'woocommerce'),
      'options' => array(
		'' => 'Not Post Yet',
        'posted' => 'Posted',
      )
    )
  );
  echo '</div>';
  echo '<div class="options_group">';
  woocommerce_wp_select(
    array(
      'id' => 'type',
      'label' => __('Type', 'woocommerce'),
      'options' => array(
        '' => '',
        'Plakat' => 'Plakat',
        'Half Moon' => 'Half Moon',
        'Crowntail' => 'Crowntail',
        'Dumbo' => 'Dumbo',
        'Wild Alien' => 'Wild Alien',
        'Dumbo Half Moon' => 'Dumbo Half Moon',
        'Double Tail' => 'Double Tail',
        'Isan' => 'Isan'
      )
    )
  );
  woocommerce_wp_select(
    array(
      'id' => 'nickname',
      'label' => __('Nickname', 'woocommerce'),
      'options' => array(
        '' => '',
        'Nemo' => 'Nemo',
        'Nemo Galaxy' => 'Nemo Galaxy',
		'Dragon' => 'Dragon',
        'Black Samurai' => 'Black Samurai',
        'Avatar' => 'Avatar',
        'Alien' => 'Alien'
      )
    )
  );
  woocommerce_wp_select(
    array(
      'id' => 'gender',
      'label' => __('Gender', 'woocommerce'),
      'options' => array(
        '' => '',
        'Male' => 'Male',
        'Female' => 'Female',
        'Pair' => 'Pair'
      )
    )
  );
  woocommerce_wp_text_input(
    array(
      'id' => 'age',
      'label' => __('Age', 'woocommerce'),
      'type' => 'number'
    )
  );
  woocommerce_wp_text_input(
    array(
      'id'          => 'birthday',
      'label'       => __( 'Birthday', 'woocommerce' ),
    )
  );
  woocommerce_wp_text_input(
    array(
      'id'          => 'color',
      'label'       => __( 'Color', 'woocommerce' ),
    )
  );
  echo '</div>';
}

// Save Fields using WooCommerce Action Hook
add_action( 'woocommerce_process_product_meta', 'woocommerce_process_product_meta_fields_save' );
function woocommerce_process_product_meta_fields_save( $post_id ){
  if(isset($_POST['ebay_us_start_price'])){
    update_post_meta($post_id, 'ebay_us_start_price', $_POST['ebay_us_start_price']);
  }
  if(isset($_POST['ebay_us_buy_price'])){
    update_post_meta($post_id, 'ebay_us_buy_price', $_POST['ebay_us_buy_price']);
  }
  if(isset($_POST['yahoo_auction_start_price'])){
    update_post_meta($post_id, 'yahoo_auction_start_price', $_POST['yahoo_auction_start_price']);
  }
  if(isset($_POST['yahoo_auction_buy_price'])){
    update_post_meta($post_id, 'yahoo_auction_buy_price', $_POST['yahoo_auction_buy_price']);
  }
  if(isset($_POST['yahoo_shopping_price'])){
    update_post_meta($post_id, 'yahoo_shopping_price', $_POST['yahoo_shopping_price']);
  }
  if(isset($_POST['shopee_price'])){
    update_post_meta($post_id, 'shopee_price', $_POST['shopee_price']);
  }
  if(isset($_POST['post_to_shopee'])){
    update_post_meta($post_id, 'post_to_shopee', $_POST['post_to_shopee']);
  }
  if(isset($_POST['post_to_aquashop'])){
    update_post_meta($post_id, 'post_to_aquashop', $_POST['post_to_aquashop']);
  }
  if(isset($_POST['type'])){
    update_post_meta($post_id, 'type', $_POST['type']);
  }
  if(isset($_POST['nickname'])){
    update_post_meta($post_id, 'nickname', $_POST['nickname']);
  }
  if(isset($_POST['gender'])){
    update_post_meta($post_id, 'gender', $_POST['gender']);
  }
  if(isset($_POST['age'])){
    update_post_meta($post_id, 'age', $_POST['age']);
  }
  if(isset($_POST['birthday'])){
    update_post_meta($post_id, 'birthday', $_POST['birthday']);
  }
  if(isset($_POST['color'])){
    update_post_meta($post_id, 'color', $_POST['color']);
  }
}

add_action( 'woocommerce_single_product_summary', 'show_custom_fields', 41 );
function show_custom_fields() {
  global $product;
  $product_id = $product->get_id();
  echo '<p>'. __( "Categories: ", "woocommerce" ).$product->get_categories().'</p>';
  $type = get_post_meta($product_id, 'type')[0];
  $nickname = get_post_meta($product_id, 'nickname')[0];
  $gender = get_post_meta($product_id, 'gender')[0];
  $age = get_post_meta($product_id, 'age')[0];
  //$color = get_post_meta($product_id, 'color')[0];
  echo '<p><span class="meta_wrapper">Type: <span class="meta_value">'.$type.' '.$nickname.'</span></span></p>';
  echo '<p><span class="meta_wrapper">Gender: <span class="meta_value">'.$gender.'</span></span></p>';
  echo '<p><span class="meta_wrapper">Age: <span class="meta_value">'.$age.' month</span></span></p>';
  //echo '<p><span class="meta_wrapper">Color: <span class="meta_value">'.$color.'</span></span></p>';
  $product_tags = get_the_term_list($product_id, 'product_tag', '', ',' );
  //echo '<p>'. __( "Color: ", "woocommerce" ).$product_tags.'</p>';
  $terms = wp_get_object_terms($product_id, 'product_tag');
  $box = '';
  foreach($terms as $term) {
    $term_link = get_term_link($term);
    $box = $box . '<a class="tooltip-box" href="'.$term_link.'"><span class="tooltip-text">'.$term->name.'</span>';
    if(strtolower($term->name) == 'white'){
    	$box = $box . '<div class="'.strtolower($term->name).'" style="width: 25px;height: 100%;padding: 0px;margin: 2px;border: 1px solid #DBDBDB;"></div>';
	}else{
		$box = $box . '<div class="'.strtolower($term->name).'" style="width: 25px;height: 100%;padding: 0px;margin: 2px;"></div>';
	}
    $box = $box . '</a>';
  }
  echo '<div class="row" style="margin-left: 1px;">'. __( "Color: ", "woocommerce" ).$box.'</div>';
  echo "<style>
.tooltip-box {
  position: relative;
  display: inline-block;
}
.tooltip-box .tooltip-text {
  visibility: hidden;
  width: fit-content;
  min-width: 29px;
  background-color: black;
  color: #fff;
  text-align: center;
  position: absolute;
  z-index: 1;
  font-size: xx-small;
  min-width: 29px;
  top: -15px;
}
.tooltip-box:hover .tooltip-text {
  visibility: visible;
}
</style>";
}

remove_filter( 'sanitize_title', 'sanitize_title_with_dashes' );
add_filter( 'sanitize_title', 'wpse5029_sanitize_title_with_dashes' );
function wpse5029_sanitize_title_with_dashes($title) {
    $title = strip_tags($title);
    // Preserve escaped octets.
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    // Remove percent signs that are not part of an octet.
    $title = str_replace('%', '', $title);
    // Restore octets.
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

    $title = remove_accents($title);
    if (seems_utf8($title)) {
        //if (function_exists('mb_strtolower')) {
        //    $title = mb_strtolower($title, 'UTF-8');
        //}
        $title = utf8_uri_encode($title, 200);
    }

    //$title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title); // kill entities
    $title = str_replace('.', '-', $title);
    // Keep upper-case chars too!
    $title = preg_replace('/[^%a-zA-Z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');

    return $title;
}

function shipping_js() {
?>
	<script>
		jQuery(function ($) {
      const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      const shippingDestinations = ["United States", "United Kingdom", "European Union", "Japan", "Singapore"];
      const shippingIds = {"United States": "#shipping-us", "United Kingdom": "#shipping-gb", "European Union": "#shipping-eu", "Japan": "#shipping-jp", "Singapore": "#shipping-sg"};
			
      $(document).ready(function(){
				$.ajax({
          url: 'https://cms.zeroaqua.com/departure/next',
          type: 'GET',
          beforeSend: function(xhr){
          },
          success: function(res){
            var shippingUpdates = {"United States": false, "United Kingdom": false, "European Union": false, "Japan": false, "Singapore": false};
            const rows = JSON.parse(res);
            $.each(rows, function(i, row){
              var departing_date = new Date(row['departing_date']);
              if($(shippingIds[row['destination']]).length){
                $(shippingIds[row['destination']]).html(departing_date.getDate()+'/'+monthNames[departing_date.getMonth()]+'/'+departing_date.getFullYear());
              }
              shippingUpdates[row['destination']] = true;
            });
            $.each(shippingDestinations, function(i, shippingDestination){
              if(shippingUpdates[shippingDestination] == false && $(shippingIds[shippingDestination]).length){
                $(shippingIds[shippingDestination]).html('');
              }
            });
          },
          error: function(err){
            console.log(err['responseText']);
          },
          complete: function(data){
          }
        });
			});
		});
	</script>
<?php
}
add_action('wp_head', 'shipping_js');

function tiktok_js() {
	if ( is_product() ) {
?>
		<script async src="https://www.tiktok.com/embed.js"></script>
<?php
	}
}
add_action('wp_head', 'tiktok_js');

?>