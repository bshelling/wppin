<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
Plugin Name: WP Custom Pinterest Plugin
Plugin URI: https://github.com/bshelling/wppin
Description: Allows users to pin posts or pages properly with pininterest crop ratios
Version: 1.0
Author: Brandon Shelling
Author URI: https://github.com/bshelling
License: GPL2
*/
add_image_size('pinImgXLarge',900, 1600,array('left','top'));
add_image_size('pinImgLarge',700, 1300,array('left','top'));
add_image_size('pinImgMedium',500, 900,array('left','top'));
add_image_size('pinImgSmall',350, 600,array('left','top'));
add_image_size('pinImgHorzSmall',700, 400,array('left','top'));
add_image_size('pinImgHorzMedium',1080,600,array('left','top'));
add_image_size('pinImgHorzLarge',1280,700,array('left','top'));
add_image_size('pinImgHorzXLarge',1920,1000,array('left','top'));


/**
* Choose image sizes
**/
add_filter('image_size_names_choose','pinSizes');
function pinSizes($sizes){
  $customSizes = array(
    'pinImgXLarge'=>__('Pinterest Vertical XLarge Image'),
    'pinImgLarge'=>__('Pinterest Vertical Large Image'),
    'pinImgMedium'=>__('Pinterest Vertical Medium Image'),
    'pinImgSmall'=>__('Pinterest Vertical Small Image'),
    'pinImgHorzXLarge'=>__('Pinterest Horizontal XLarge Image'),
    'pinImgHorzLarge'=>__('Pinterest Horizontal Large Image'),
    'pinImgHorzMedium'=>__('Pinterest Horizontal Medium Image'),
    'pinImgHorzSmall'=>__('Pinterest Horizontal Small Image'),
  );
  $size = array_merge($sizes,$customSizes);
  return $size;
}

/**
* Pinterest Meta Tags
**/
function pinMeta(){
  $post = get_post();
  add_post_type_support('page','excerpt');
?>
<meta property="og:type" content="article" />
<meta property="og:title" content="<?php the_title(); ?>" />
<meta property="og:description" content="<?php echo $post->post_excerpt; ?>" />
<meta property="og:url" content="<?php the_permalink(); ?>" />
<meta property="og:site_name" content="<?php echo bloginfo('name'); ?>" />
<meta property="article:published_time" content="<?php echo the_time('F jS, Y'); ?>" />
<meta property="article:author" content="<?php echo $post->post_author; ?>" />
<?php
}
add_action('wp_head','pinMeta');


/**
* Add PinIt Button to Page
**/
function addPinIt($content){
  global $post;
  $custom = $content;
  if(get_post_meta($post->ID,'pinsize',true) == 'yes'){
      $custom = $content.'<a href="https://www.pinterest.com/pin/create/button/"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /></a>';

      return $custom;
  }
  else{
    return $custom;
  }

}
add_filter('the_content','addPinIt');

/**
* Embed Pininterest Script
**/
function pinScript(){?>
  <script
    type="text/javascript"
    async defer
    src="//assets.pinterest.com/js/pinit.js"
    ></script>

<?php
}
add_action('wp_footer','pinScript');

function pinCustomSupports() {
	add_post_type_support( 'page','excerpt');
}
add_action( 'init', 'pinCustomSupports' );

/**
* Custom meta box
**/
function customPinFields(){
  function selectOption(){

      global $post;
      $size = get_post_custom($post->ID);
      $pinSize = isset($size['pinsize']) ? $size['pinsize'][0] : 'No Value';

      wp_nonce_field( 'my_meta_pinterest_nonce', 'meta_pinterest_nonce' );
    ?>
    <select name="size" id="my_meta_box_select">
      <option value="yes" <?php selected( $pinSize, 'yes' ); ?>>Yes</option>
      <option value="no" <?php selected( $pinSize, 'no' ); ?>>No</option>
    </select>

    <?php
  }
  add_meta_box('pin-post-crop','Add PinIt Button','selectOption',array('page','post'),'side','high');
}
add_action('add_meta_boxes','customPinFields');

/**
* Save Pin visibility
**/
function savePinImgSz($post_id){
  // Bail if we're doing an auto save
   if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
   // if our nonce isn't there, or we can't verify it, bail
   if( !isset( $_POST['meta_pinterest_nonce'] ) || !wp_verify_nonce( $_POST['meta_pinterest_nonce'], 'my_meta_pinterest_nonce' ) ) return;
   // if our current user can't edit this post, bail
   if( !current_user_can( 'edit_post' ) ) return;
   update_post_meta( $post_id, 'pinsize', $_POST['size']);
}
add_action('save_post','savePinImgSz');
