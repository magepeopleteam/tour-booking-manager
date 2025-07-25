<?php
if ($places_query->have_posts()) {

    while ($places_query->have_posts()) {
        $places_query->the_post();
        $post_id = get_the_ID();
        $places_name = get_the_title();
        $description = get_the_excerpt();
        $view_link = get_permalink($post_id);

        $img_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
        if (!$img_url) {
            $img_url = 'https://i.imgur.com/GD3zKtz.png';
        }

        $view_link = get_permalink($post_id);

        ?>
        <div class="ttbm_attraction_item <?php echo esc_attr($grid_class).' '; echo esc_attr( $load_more_class ); ?>" id="<?php echo esc_attr( $post_id );?>" style="display: <?php echo esc_attr( $display );?>">
            <img src="<?php echo esc_url( $img_url );?>" alt="<?php echo esc_attr( $places_name );?>">
            <div>
                <h4><a href="<?php echo esc_attr( $view_link );?>" target="_blank"><?php echo esc_attr( $places_name );?></a></h4>
                <p><?php echo esc_attr( count( $place_tour[$post_id] ) );?> <?php esc_attr_e( 'Tours and Activities', 'tour-booking-manager' )?></p>
            </div>
        </div>
        <?php

    }

    wp_reset_postdata();
} else {
    echo '<p>No attractions found.</p>';
}