<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

$term_ids = array_keys( $activity_term_ids );
$terms_data = TTBM_Function::ttbm_get_term_data('ttbm_tour_activities', $term_ids );

$related_tour_count = count( $terms_data );
$num_of_tour = $params['column'];
$num_of_display = $params['show'];
$carousel = $params['carousal'];
$load_more_button = $params['load-more-button'];

$grid_class= $related_tour_count <= $num_of_tour?'grid_'.$num_of_tour:'';
$div_class='flexWrap grid';
if( $carousel == 'no' ){
    if( $load_more_button === 'yes' ){
        $load_more_class = 'ttbm_load_activity';
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
<div class='ttbm_style ' id="ttbm_browse_activities">
    <div class="ttbm_container">
        <div class='ttbm_default_widget'>
            <?php do_action( 'ttbm_section_title', 'ttbm_string_related_tour', esc_html__( 'Activity Browse ', 'tour-booking-manager' ) ); ?>

            <?php
            if ( $carousel == 'yes' && $related_tour_count > $num_of_tour ) {
                include( TTBM_Function::template_path( 'layout/carousel_indicator.php' ) );
            }
            if( $carousel == 'no' ){
            ?>
            <div class="placeholder_area flexWrap" id="ttbm_activities_placeholder_area">
                <?php if ( $carousel == 'no' ) {?>
                    <input type="hidden" id="ttbm_load_more_activities_number" value="<?php echo esc_attr( $num_of_display )?>">
                <?php }?>
                <?php } else {?>
                <div class="ttbm_widget_content _mZero  <?php echo esc_attr( $related_tour_count >$num_of_tour?'owl-theme owl-carousel':$div_class); ?>" data-show ="<?php echo esc_attr($num_of_tour); ?>">
                    <?php }?>
                    <?php
                    include(TTBM_Function::template_path('list/load_activity.php'));
                    ?>

                </div>
                <?php  if( $load_more_button == 'yes' && $carousel == 'no' &&  $related_tour_count > $num_of_display ){?>
                    <div class="ttbm_attraction_load_more_holder"><div class="ttbm_load_more_text" id="ttbm_activities_load_more_text">Load More...</div></div>
                <?php }?>
            </div>

        </div>

    </div>
