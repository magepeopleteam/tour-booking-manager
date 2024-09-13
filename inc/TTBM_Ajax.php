<?php

class TTBM_Ajax
{
    function __construct()
    {
        add_action('wp_ajax_ttbm_get_location_based_activities', array($this, 'ttbm_get_location_based_activities'));
        add_action('wp_ajax_nopriv_ttbm_get_location_based_activities', array($this, 'ttbm_get_location_based_activities'));
    }

    public static function ttbm_get_location_based_activities(){

        $location_details = get_term_by('id', $_POST['location'], 'ttbm_tour_location');
        $location_name = $location_details->name;

        $activities = MP_Global_Function::location_based_activities($location_name);
        $html_activities = '';
        foreach ($activities as $activity) {
            $term_id = get_term_by('name', $activity, 'ttbm_tour_activities')->term_id;
            $html_activities .= '<option value="'.$term_id.'">'.$activity.'</option>';
        }
        echo json_encode(['activities' => $html_activities,'status'=>200]);
        wp_die();
    }
}

new TTBM_Ajax();