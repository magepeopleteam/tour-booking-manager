<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

    $popular_place_ids = array_values(array_keys( $place_tour ) );

    $args = array(
        'post_type' => 'ttbm_places',
        'post_status' => 'publish',
        'post__in' => $popular_place_ids,
    );

    $places_query = new WP_Query( $args);

    $related_tour_count = $places_query->found_posts;
    $num_of_tour = $params['show'];
    $num_of_column = $params['column'];
    $carousel = $params['carousel'];
    $load_more_button = $params['load-more-button'];


    $grid_class= $related_tour_count <= $num_of_tour?'grid_'.$num_of_column:'';
    $div_class='flexWrap grid';
    if( $carousel == 'no' ){
        if( $load_more_button == 'yes' ){
            $load_more_class = 'ttbm_load_top_attractive';
            $display = 'none';
        }else{
            $load_more_class = '';
            $display = '';
        }

    }else{
        $load_more_class = '';
        $display = '';
    }


    ?>
    <div class='ttbm_style ' id="ttbm_top_attraction">
        <div class="ttbm_container">
            <div class=''>
<!--                --><?php //do_action( 'ttbm_section_title', 'ttbm_string_related_tour', esc_html__( 'Top Attraction ', 'tour-booking-manager' ) ); ?>
                <div class="ttbm_carousel_holder">
                <?php
                if ( $carousel == 'yes' && $related_tour_count > $num_of_tour ) {
                    include( TTBM_Function::template_path( 'layout/carousel_indicator.php' ) );
                }
                ?>
                </div>
                <?php
                if( $carousel == 'no' ){
                ?>
                <div class="placeholder_area flexWrap" id="ttbm_attraction_placeholder_area">
                    <?php if ( $carousel == 'no' ) {?>
                    <input type="hidden" id="ttbm_load_more_attraction_number" value="<?php echo esc_attr( $num_of_tour )?>">
                    <?php }?>
                <?php } else {?>
                    <div class="ttbm_widget_content _mZero  <?php echo esc_attr( $related_tour_count >$num_of_tour?'owl-theme owl-carousel':$div_class); ?>" data-show ="<?php echo esc_attr($num_of_column); ?>">
                        <?php }?>
                        <?php
                        include(TTBM_Function::template_path('layout/display_top_attractions.php'));
                        ?>

                    </div>
                    <?php  if( $load_more_button == 'yes' && $carousel == 'no' &&  $related_tour_count > $num_of_tour ){?>
                        <div class="ttbm_attraction_load_more_holder"><div class="ttbm_load_more_text" id="ttbm_attraction_load_more_text">Load More...</div></div>
                    <?php }?>
                </div>

        </div>

    </div>
