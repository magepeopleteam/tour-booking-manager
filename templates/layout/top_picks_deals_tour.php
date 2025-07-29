<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}


$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
//$related_tours = array( 163, 164, 165, 94, 96, 100, 102 );
$related_tours = TTBM_Function::get_top_deals_post_ids($type_tour);
$related_tour_count=sizeof( $related_tours );
$num_of_tour=$num_of_tour??'';

$carousel = $params['carousel'];
$show = $params['show'];

if ( $related_tour_count > 0 && (TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_top_picks_deals', 'on' ) != 'off' || $num_of_tour>0)) {
    $num_of_tour=$num_of_tour>0?$num_of_tour:4;
    $num_of_tour=min($num_of_tour,$related_tour_count);
    if( $carousel == 'yes' ){
        $grid_class=$related_tour_count <= $num_of_tour?'grid_'.$num_of_tour:'';
        $load_class = '';
        $display = '';
    }else{
        $grid_class = (int)$params['column'] > 0 ? 'grid_' . (int)$params['column'] : 'grid_1';
        $load_class =  'ttbm_'.$type_tour.'_shortcode_load_tour';
        $display = 'none';
    }
//    $div_class=$related_tour_count==1?'flexWrap modern':'flexWrap grid';
    $div_class='flexWrap grid';
    ?>
    <div class='ttbm_style ttbm_wraper' id="ttbm_<?php echo esc_attr( $type_tour );?>_tour">
        <?php if( $carousel == 'no' ){ ?>
            <input type="hidden" id="ttbm_<?php echo esc_attr( $type_tour );?>_load_more_tour_shortcode" value="<?php echo esc_attr( $show )?>">
        <?php }?>
        <div class="ttbm_container">
            <div class=''>
<!--                --><?php //do_action( 'ttbm_section_title', 'ttbm_string_related_tour', esc_html__( ' '.ucfirst( $type_tour ).' Tour ', 'tour-booking-manager' ) ); ?>
                <div class="ttbm_carousel_holder">
                <?php
                if ( $carousel == 'yes' && sizeof( $related_tours ) > $num_of_tour ) {
                    include( TTBM_Function::template_path( 'layout/carousel_indicator.php' ) );
                } ?>
                </div>
               <?php
                if( $carousel == 'yes'){ ?>
                    <div class="ttbm_widget_content _mZero  <?php echo esc_attr($related_tour_count >$num_of_tour?'owl-theme owl-carousel':$div_class); ?>" data-show="<?php echo esc_attr($num_of_tour); ?>">
                    <?php }else{  ?>
                    <div class="placeholder_area flexWrap">
                    <?php }?>
                    <?php foreach ( $related_tours as $ttbm_post_id ) { ?>
                        <div class="filter_item <?php echo esc_attr($grid_class).' ' ; echo esc_attr( $load_class ); ?> " id="<?php echo esc_attr( $ttbm_post_id )?>" style="display: <?php echo esc_attr( $display );?>">
                            <?php include( TTBM_Function::template_path( 'list/shortcode_list_grid.php' ) ); ?>
                        </div>
                    <?php } ?>

                </div>
                <?php  if( $carousel == 'no' &&  $related_tour_count > $show ){?>
                    <div class="ttbm_shortcode_load_more_holder"><div class="ttbm_load_more_text" id="ttbm_<?php echo esc_attr( $type_tour );?>_load_more_text">Load More...</div></div>
                <?php }?>
            </div>
        </div>
    </div>
<?php } ?>