<?php

if( $carousel == 'no' ){
    $grid_class = (int)$params['column'] > 0 ? 'grid_' . (int)$params['column'] : 'grid_1';
}else{
    $grid_class= $related_tour_count <= $num_of_tour?'grid_'.$num_of_tour:'';
}

 foreach ( $terms_data as $key => $term ){
     $term_id    = $term->term_id;
     $term_name  = esc_html( $term->name );
     $term_slug  = esc_html( $term->slug );
     $description = esc_html( $term->description );
     $get_activities_icon = get_term_meta( $term_id, 'ttbm_activities_icon', true );

     $term_link = get_term_link( (int) $term_id, 'ttbm_tour_activities' );

     $img_url = '';

    ?>
    <div class="ttbm_activity_item ttbm_term_display_shortcode <?php echo esc_attr( $grid_class ).' '; echo esc_attr( $load_more_class ); ?>" id="<?php echo esc_attr( $term_id );?>" style="display: <?php echo esc_attr( $display );?>">
        <h3 class="ttbm-title"><i class="<?php echo esc_attr( $get_activities_icon )?> "></i></h3>
        <div>
            <h4><a href="<?php echo esc_attr( $term_link )?>"><?php echo esc_attr( $term_name );?></a></h4>
            <p><?php echo esc_attr( count( $activity_term_ids[$term_id] ) );?> Tours and Activities</p>
        </div>
    </div>
    <?php

}
