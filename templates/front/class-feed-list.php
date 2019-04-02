<?php

  namespace Wc_Insta_Feed\Templates\Front;

  use Wc_Insta_Feed\Helper;
  use Wc_Insta_Feed\Inc\WC_Insta_Errors;



  if (!defined('ABSPATH')) {
      exit;
  }

  if (! class_exists('Feed_List')) {
      /**
       *
       */
      class Feed_List extends Helper\Tag\Wc_Tag
      {

        public $status = '';

        public $linked_products = '';

        public $tag_data = '';

        public $insta_hashtag = '';

        public $hashtag_priority = '';

        public $hashtag_visiblity = '';

        public $error_obj = '';

        public $view = '';

        public $linked_products_list = array();



        public function __construct( $view = '') {

          global $wpdb;

          $this->wpdb = $wpdb;

          $this->error_obj = new WC_Insta_Errors();

          $this->view = $view;

          $this->lightbox_container();

        }

        public function validate_tag_results( $results ) {

            if( empty($results ) ) {

                $message = __( 'Instagrams tags are not available', 'insta_feed' );

                $this->error_obj->set_error_code(1);
                $this->error_obj->insta_print_notification($message);

                return false;

            } else {

                return true;

            }
        }

        public function setup_class_tag_data( $results ) {

            $data = array();

            foreach ($results as $key => $value) {

                $this->tag_data = $this->insta_get_tag_data_by_tag_id( $value['id']);

                if( !empty( $this->tag_data ) ) {

                    $this->insta_hashtag = $this->tag_data['hashtag'];
                    $this->linked_products = $this->tag_data['linked_products'];

                    $this->hashtag_priority = $this->tag_data['priority'];
                    $this->status = $this->tag_data['status'];
                    $this->hashtag_visiblity = $this->tag_data['visiblity'];

                    if( $this->status != '1' )
                        continue;

                    $check = $this->insta_check_visiblity();

                    if( !$check )
                        continue;

                    $media_obj = new Helper\Tag\Wc_Tag_Data( $value['id'] );

                    $tag_media = $media_obj->insta_get_only_one_tag_media();

                    if ( ! empty( $tag_media ) ) {

                        $count = $media_obj->insta_tag_media_count();

                        $image_id  = $tag_media['image_id'];

                        $author   = $tag_media['insta_username'];

                        $images   	= !empty( $tag_media['images'] ) ? maybe_unserialize( $tag_media['images'] ) : '';

                        $image = isset( $images ) ? $images : '';

                        $preview = !empty( $image ) ? '<img src="'.$image.'" alt="'.$author.'" >' : 'N/A';

                        array_push( $data, array(

                            'image_id' => $image_id,

                            'preview'  => $preview,

                            'author'   => $author,

                            'count'   => $count['count'],

                            'tag_id'   =>  $value['id'],

                            'tag_name' => $this->insta_hashtag,

                        ) );

                    }

                }
            }


            if( !empty( $data ) ) {

                $shop_view = get_option('_insta_shop_view');

                if( $shop_view === '0' && !empty( $this->view) && $this->view == 'shop') {

                    echo "<div class='slider-wrapper owl-carousel ".$this->view."'>";

                        foreach ($data as $pdata) {

                            $this->generate_media_list_html( $pdata );

                        }

                    echo "</div>";

                } else {

                    foreach ($data as $pdata) {

                        $this->generate_media_list_html( $pdata );

                    }
                }


            }
        }

        function get_insta_tag_template()
        {

          ?>

            <div id="wrapper">

                <div id="dashboard_right_now" class="instagram instagram-feeds <?php echo $this->view; ?>">

                    <div class="inside  row wonka-row">

                        <div class="instagram-wrap col-12">

                            <?php

                            $results = $this->insta_get_tag_data();

                            $bool = $this->validate_tag_results( $results );

                            if( $bool) {

                                $this->setup_class_tag_data( $results);

                            } else {

                                $message = __( 'Instagrams tags are not available', 'insta_feed' );

                                $this->error_obj->set_error_code(1);
                                $this->error_obj->insta_print_notification($message);
                            }

                            ?>

                        </div>

                    </div>


                </div>

            </div>

           <?php


        }

        public function generate_media_list_html( $data ) {

            ?>

            <div class="insta-box" id="<?php echo $data['tag_id']; ?>" data-image-id="<?php echo $data['image_id']; ?>">

                <?php echo $data['preview']; ?>

                <div class="box-head">

                    <span class="count-pic"><?php echo $data['count']; ?></span>
                    <button class="popup-open"><?php echo __('View Products', 'insta_feed'); ?></button>
                    <span class="pic-author" title="hashtag"><?php echo $data['tag_name']; ?></span>

                </div>
            </div>

            <?php

        }

        public function insta_check_visiblity( $visiblity = '' ) {

            if( !empty( $this->view ) && $this->view == 'shop' )
                $viewer = 0;
            else
                $viewer = 1;

            if( !empty( $visiblity ) ) {

                if( !empty( $visiblity ) && in_array( $viewer, $visiblity ) ) {
                    return true;
                } else {
                    return false;
                }

            } else {

                if( !empty( $this->hashtag_visiblity ) && in_array( $viewer, $this->hashtag_visiblity ) ) {

                    return true;
                } else {
                    return false;
                }
            }

        }

        public function lightbox_container(){

            ?>

            <template id="screenSliderTemplate">
                <div class="screens-template">
                    <div class="screen-template-wrap" active-client="{{id}}">
                        <span class="close-icon"></span>
                        <div class="content">
                            <div class="wkgrid-squeezy">
                                <div class="wk-loader"></div>
                                <div class="inner-content">

                                    <div class="info-part">
                                        <h2><?php echo __('Products', 'insta_feed'); ?></h2>
                                        <div class="insta-tag-products">{{tagProducts}}</div>
                                    </div>

                                    <div class="slider-part">
                                        <div class="insta-modal slider-wrapper owl-carousel">{{sliderItems}}</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div id="sliderHolder"></div>

            <?php
        }



  }

}