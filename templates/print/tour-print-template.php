<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * Tour Print Template - Magazine/Poster Style
 *
 * This template is used for printing tour details in a modern magazine/poster layout.
 */

$ttbm_post_id = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;
if (!$ttbm_post_id) {
    wp_die('Invalid tour ID');
}

$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
$tour_title = get_the_title($tour_id);
$thumbnail = MP_Global_Function::get_image_url($tour_id);
$gallery_images = MP_Global_Function::get_post_info($tour_id, 'ttbm_gallery_images', array());
$description = get_post_field('post_content', $tour_id);
$short_description = MP_Global_Function::get_post_info($tour_id, 'ttbm_short_description');
$location = TTBM_Function::get_full_location($tour_id);
$duration = MP_Global_Function::get_post_info($tour_id, 'ttbm_duration', '');
$start_price = TTBM_Function::get_tour_start_price($tour_id);
$features = MP_Global_Function::get_post_info($tour_id, 'ttbm_tour_features', array());
$activities = MP_Global_Function::get_post_info($tour_id, 'ttbm_tour_activities', array());
$guides = MP_Global_Function::get_post_info($tour_id, 'ttbm_tour_guide', array());
$why_choose_us = MP_Global_Function::get_post_info($tour_id, 'ttbm_why_choose_us', '');
$faq_arr = MP_Global_Function::get_post_info($tour_id, 'mep_event_faq', array());

// Get additional tour information
$group_size = MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_capacity', '');
$total_seat = TTBM_Function::get_total_seat($tour_id);
$available_seat = TTBM_Function::get_total_available($tour_id);
$tour_type = TTBM_Function::get_tour_type($tour_id);
$travel_type = TTBM_Function::get_travel_type($tour_id);
$country = MP_Global_Function::get_post_info($tour_id, 'ttbm_country_list', '');
$city = MP_Global_Function::get_post_info($tour_id, 'ttbm_city_list', '');
$places = MP_Global_Function::get_post_info($tour_id, 'ttbm_place_you_see', array());
$exclude_services = MP_Global_Function::get_post_info($tour_id, 'ttbm_exclude_service_arr', array());
$include_services = MP_Global_Function::get_post_info($tour_id, 'ttbm_include_service_arr', array());
$day_wise_details = MP_Global_Function::get_post_info($tour_id, 'ttbm_daywise_details', array());

// Get the first available date
$all_dates = TTBM_Function::get_date($tour_id);
$first_date = !empty($all_dates) ? current($all_dates) : '';
$formatted_date = $first_date ? MP_Global_Function::date_format($first_date) : '';

// Get categories and tags
$categories = get_the_terms($tour_id, 'ttbm_tour_cat');
$organizers = get_the_terms($tour_id, 'ttbm_tour_org');
$locations = get_the_terms($tour_id, 'ttbm_tour_location');

// Get rating information if available
$rating_count = get_post_meta($tour_id, '_wc_average_rating', true) ? get_post_meta($tour_id, '_wc_average_rating', true) : 0;
$rating_text = $rating_count > 0 ? sprintf('%s/5', $rating_count) : '';

// Get hotel information if available
$hotel_lists = TTBM_Function::get_hotel_list($tour_id);

// Get contact information
$contact_text = MP_Global_Function::get_post_info($tour_id, 'ttbm_contact_text', '');
$contact_phone = MP_Global_Function::get_post_info($tour_id, 'ttbm_contact_phone', '');
$contact_email = MP_Global_Function::get_post_info($tour_id, 'ttbm_contact_email', '');

// Get ticket information
$ticket_list = TTBM_Function::get_ticket_type($tour_id);

// Get additional details
$min_age = MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_min_age', '');
$max_people = MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_max_people_allow', '');
$duration_night = MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_night', '');
$duration_type = MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_type', 'day');

// Get extra services
$extra_services = MP_Global_Function::get_post_info($tour_id, 'ttbm_extra_service_data', array());

// Get permalink for booking
$tour_permalink = get_permalink($tour_id);

// Get site information
$site_name = get_bloginfo('name');
$site_url = get_bloginfo('url');
$site_description = get_bloginfo('description');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($tour_title); ?> - <?php esc_html_e('Print View', 'tour-booking-manager'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Print-specific styles */
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body {
                font-family: 'Montserrat', 'Arial', sans-serif;
                line-height: 1.5;
                color: #333;
                background: #fff;
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-before: always;
            }
            .screen-only {
                display: none !important;
            }
            a {
                text-decoration: none;
                color: inherit;
            }
        }

        /* General styles */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Montserrat', 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .print-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        /* Table of Contents */
        .print-toc {
            background: #f8f8f8;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-toc-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #f12971;
            text-align: center;
            text-transform: uppercase;
        }
        .print-toc-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            column-count: 2;
            column-gap: 30px;
        }
        .print-toc-list li {
            margin-bottom: 10px;
            break-inside: avoid;
        }
        .print-toc-list a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 5px 10px;
            border-left: 3px solid #f12971;
            transition: all 0.3s ease;
        }
        .print-toc-list a:hover {
            background: #f1f1f1;
            transform: translateX(5px);
        }

        /* Magazine Cover Style Header */
        .print-cover {
            position: relative;
            height: 100vh;
            overflow: hidden;
            color: #fff;
            margin-bottom: 30px;
        }
        .print-cover-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
            filter: brightness(0.9);
        }
        .print-cover-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 50%, rgba(241,41,113,0.6) 100%);
            z-index: 2;
        }
        .print-cover-content {
            position: relative;
            z-index: 3;
            padding: 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .print-cover-header {
            text-align: center;
            margin-top: 40px;
        }
        .print-cover-logo {
            max-width: 180px;
            margin: 0 auto 30px;
            filter: brightness(0) invert(1);
            border: 2px solid rgba(255,255,255,0.3);
            padding: 10px;
            border-radius: 5px;
        }
        .print-cover-title {
            font-size: 72px;
            font-weight: 800;
            margin: 0 0 20px;
            text-transform: uppercase;
            text-shadow: 2px 2px 15px rgba(0,0,0,0.7);
            line-height: 1.1;
            letter-spacing: -1px;
            position: relative;
            display: inline-block;
        }
        .print-cover-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background-color: #f12971;
            border-radius: 5px;
        }
        .print-cover-subtitle {
            font-size: 28px;
            margin: 30px 0 10px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .print-cover-meta {
            display: flex;
            justify-content: center;
            margin-top: auto;
            margin-bottom: 50px;
            gap: 30px;
        }
        .print-cover-meta-item {
            text-align: center;
            flex: 0 0 auto;
            background: rgba(241,41,113,0.8);
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            min-width: 150px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }
        .print-cover-meta-item:hover {
            transform: translateY(-5px);
        }
        .print-cover-meta-label {
            font-weight: 600;
            display: block;
            text-transform: uppercase;
            font-size: 12px;
            margin-bottom: 8px;
            letter-spacing: 1px;
            opacity: 0.9;
        }
        .print-cover-meta-value {
            display: block;
            font-size: 22px;
            font-weight: 700;
        }
        .print-cover-badge {
            position: absolute;
            top: 40px;
            right: 40px;
            background: #f12971;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: rotate(5deg);
        }

        /* Content Styles */
        .print-content {
            padding: 30px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 20px;
            border-bottom: 2px solid #f12971;
        }
        .print-title {
            font-size: 36px;
            font-weight: bold;
            margin: 0 0 10px;
            color: #f12971;
            text-transform: uppercase;
        }
        .print-subtitle {
            font-size: 20px;
            margin: 0 0 10px;
            color: #666;
            font-style: italic;
        }
        .print-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 30px;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-meta-item {
            margin-bottom: 15px;
            flex: 1 0 30%;
            text-align: center;
        }
        .print-meta-label {
            font-weight: bold;
            display: block;
            color: #f12971;
            text-transform: uppercase;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .print-meta-value {
            display: block;
            font-size: 18px;
        }

        /* Two-column layout */
        .print-two-column {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .print-column {
            flex: 1;
        }

        /* Featured image with caption */
        .print-featured-image-container {
            position: relative;
            margin-bottom: 30px;
        }
        .print-featured-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .print-image-caption {
            font-style: italic;
            text-align: center;
            color: #666;
            margin-top: 10px;
        }

        /* Description styles */
        .print-description {
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        .print-description p:first-of-type {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        /* Section styles */
        .print-section {
            margin-bottom: 40px;
            position: relative;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            page-break-inside: avoid;
        }
        .print-section-title {
            font-size: 28px;
            font-weight: 800;
            margin: -30px 0 25px 0;
            color: #333;
            padding: 12px 20px;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
            background: linear-gradient(135deg, #f12971 0%, #e91e63 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(241, 41, 113, 0.3);
            letter-spacing: 1px;
        }
        .print-section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 20px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid #e91e63;
        }

        /* Features and activities */
        .print-features, .print-activities {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .print-feature-item, .print-activity-item {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            flex: 1 0 30%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .print-feature-item:hover, .print-activity-item:hover {
            transform: translateY(-5px);
        }

        /* Services lists */
        .print-services {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .print-service-column {
            flex: 1;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-service-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #f12971;
            text-align: center;
            text-transform: uppercase;
        }
        .print-service-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .print-service-list li {
            padding: 10px 0;
            border-bottom: 1px dashed #ddd;
            position: relative;
            padding-left: 25px;
        }
        .print-service-list li:last-child {
            border-bottom: none;
        }
        .print-service-list.included li:before {
            content: '✓';
            color: #4CAF50;
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        .print-service-list.excluded li:before {
            content: '✕';
            color: #F44336;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        /* Gallery grid */
        .print-gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .print-gallery-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .print-gallery-image:hover {
            transform: scale(1.05);
        }

        /* Tour guides */
        .print-guides {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 30px;
        }
        .print-guide {
            flex: 1 0 30%;
            text-align: center;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-guide-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 5px solid #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        .print-guide-name {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 20px;
            color: #f12971;
        }

        /* Itinerary */
        .print-itinerary {
            margin-bottom: 30px;
        }
        .print-day {
            margin-bottom: 20px;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .print-day-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #f12971;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .print-day-content {
            padding-left: 20px;
            border-left: 3px solid #f12971;
        }

        /* Places to see */
        .print-places {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .print-place {
            flex: 1 0 30%;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            height: 200px;
        }
        .print-place-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .print-place-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .print-place-name {
            font-weight: bold;
            margin: 0;
            font-size: 16px;
        }

        /* FAQ */
        .print-faq {
            margin-bottom: 30px;
        }
        .faq-item {
            margin-bottom: 25px;
            background: linear-gradient(to right, #f8f8f8, #ffffff);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #f12971;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .faq-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .faq-item::after {
            content: '?';
            position: absolute;
            right: -15px;
            bottom: -15px;
            font-size: 120px;
            font-weight: 800;
            color: rgba(241, 41, 113, 0.05);
            line-height: 1;
            font-family: 'Playfair Display', serif;
        }
        .faq-question {
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 20px;
            color: #333;
            display: flex;
            align-items: center;
        }
        .faq-answer {
            margin-left: 35px;
            line-height: 1.7;
            color: #555;
            font-size: 16px;
            position: relative;
        }

        /* Footer */
        .print-footer {
            text-align: center;
            margin-top: 70px;
            padding: 40px 0;
            font-size: 15px;
            color: #666;
            background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
            border-top: 5px solid #f12971;
            position: relative;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.05);
        }
        .print-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(241, 41, 113, 0.3), transparent);
        }
        .print-footer-logo {
            max-width: 180px;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: inline-block;
        }
        .print-footer p {
            margin: 8px 0;
            line-height: 1.6;
        }
        .print-footer p:last-child {
            margin-top: 15px;
            font-style: italic;
            font-size: 13px;
            opacity: 0.8;
        }

        /* Print button */
        .print-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .print-button {
            background: linear-gradient(135deg, #f12971 0%, #e91e63 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(241, 41, 113, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .print-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s ease;
        }
        .print-button:hover {
            background: linear-gradient(135deg, #e91e63 0%, #f12971 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(241, 41, 113, 0.4);
        }
        .print-button:hover::before {
            left: 100%;
        }
        .print-button::after {
            content: '\f02f';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: 10px;
            font-size: 18px;
        }
        .screen-only {
            display: block;
        }
        @media print {
            .screen-only {
                display: none;
            }
        }

        /* Callout box */
        .print-callout {
            background: #f12971;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-callout-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        /* Hotels */
        .print-hotels {
            margin-bottom: 30px;
        }
        .print-hotel {
            display: flex;
            margin-bottom: 20px;
            background: #f8f8f8;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-hotel-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
        }
        .print-hotel-content {
            padding: 20px;
            flex: 1;
        }
        .print-hotel-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #f12971;
        }
        .print-hotel-rating {
            color: #FFC107;
            margin-bottom: 10px;
        }
        .print-hotel-price {
            font-weight: bold;
            font-size: 18px;
        }

        /* Categories and tags */
        .print-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 30px 0;
            justify-content: center;
        }
        .print-category {
            background: linear-gradient(135deg, #f12971 0%, #e91e63 100%);
            color: #fff;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 10px rgba(241, 41, 113, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
        }
        .print-category::before {
            content: '#';
            margin-right: 5px;
            font-weight: 800;
            opacity: 0.7;
        }
        .print-category:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(241, 41, 113, 0.3);
        }

        /* Pull quote */
        .print-pull-quote {
            font-size: 24px;
            font-style: italic;
            color: #f12971;
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border-top: 2px solid #f12971;
            border-bottom: 2px solid #f12971;
            line-height: 1.4;
        }

        /* Rating stars */
        .print-rating {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            justify-content: center;
        }
        .print-rating-stars {
            color: #FFC107;
            font-size: 24px;
            margin-right: 10px;
        }
        .print-rating-text {
            font-size: 18px;
            font-weight: bold;
        }

        /* Highlight box */
        .print-highlight {
            background: #f8f8f8;
            border-left: 5px solid #f12971;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .print-highlight-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #f12971;
        }

        /* QR code for booking */
        .print-qr-code {
            margin-top: 20px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        /* Magazine-style columns for text */
        .print-magazine-text {
            column-count: 2;
            column-gap: 30px;
            margin-bottom: 30px;
            text-align: justify;
        }
        .print-magazine-text p:first-of-type:first-letter {
            font-size: 3em;
            float: left;
            line-height: 0.8;
            margin-right: 8px;
            color: #f12971;
        }

        /* Decorative elements */
        .print-decorative-line {
            height: 3px;
            background: linear-gradient(to right, #f12971, transparent);
            margin: 30px 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .print-two-column,
            .print-services {
                flex-direction: column;
            }
            .print-gallery {
                grid-template-columns: repeat(2, 1fr);
            }
            .print-magazine-text {
                column-count: 1;
            }
            .print-cover-title {
                font-size: 40px;
            }
        }

        /* New sections styles */
        .print-subsection-title {
            font-size: 22px;
            font-weight: 700;
            margin: 25px 0 15px;
            color: #333;
            padding-bottom: 8px;
            position: relative;
            display: inline-block;
        }
        .print-subsection-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #f12971, transparent);
            border-radius: 3px;
        }

        /* Ticket table styles */
        .print-ticket-table, .print-services-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 10px;
            overflow: hidden;
        }
        .print-ticket-table th, .print-services-table th {
            background: linear-gradient(135deg, #f12971 0%, #e91e63 100%);
            color: white;
            text-align: left;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .print-ticket-table td, .print-services-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .print-ticket-table tr:last-child td, .print-services-table tr:last-child td {
            border-bottom: none;
        }
        .print-ticket-table tr:nth-child(even), .print-services-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .print-ticket-table tr:hover td, .print-services-table tr:hover td {
            background-color: #f1f1f1;
        }

        /* Travel tips styles */
        .print-travel-tips {
            background: linear-gradient(to right, #f8f8f8, #ffffff);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            position: relative;
            border-top: 5px solid #f12971;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .print-travel-tips::before {
            content: '\f5a1';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: -15px;
            top: -15px;
            font-size: 100px;
            color: rgba(241, 41, 113, 0.05);
            transform: rotate(15deg);
        }
        .print-tips-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .print-tips-list li {
            padding: 10px 0;
            border-bottom: 1px dashed #ddd;
            position: relative;
            padding-left: 30px;
            display: flex;
            align-items: flex-start;
        }
        .print-tips-list li:last-child {
            border-bottom: none;
        }
        .print-tips-list li::before {
            content: '\f0eb';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #f12971;
            position: absolute;
            left: 0;
            top: 15px;
            font-size: 18px;
        }
        .print-tips-list li strong {
            color: #333;
            margin-right: 8px;
            min-width: 150px;
            display: inline-block;
        }

        /* QR code container */
        .print-qr-code-container {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            border: 2px dashed #f12971;
            page-break-inside: avoid;
        }
        .print-qr-code-container::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(241, 41, 113, 0.1);
            border-radius: 50%;
            z-index: 0;
        }
        .print-qr-code img {
            max-width: 150px;
            margin-right: 20px;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }
        .print-qr-code img:hover {
            transform: scale(1.05);
        }
        .print-qr-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }
        .print-qr-code-text {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
            position: relative;
            display: inline-block;
        }
        .print-qr-code-text::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background: #f12971;
            border-radius: 3px;
        }
        .print-qr-url {
            font-size: 14px;
            color: #666;
            word-break: break-all;
            line-height: 1.4;
            margin-top: 8px;
            font-family: monospace;
            background: rgba(0,0,0,0.03);
            padding: 8px;
            border-radius: 5px;
            border-left: 3px solid #f12971;
        }

        /* Contact information */
        .print-contact {
            background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            border-bottom: 5px solid #f12971;
        }
        .print-contact::before {
            content: '\f2bb';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 150px;
            color: rgba(241, 41, 113, 0.05);
            transform: rotate(-10deg);
        }
        .print-contact-text {
            font-size: 18px;
            margin-bottom: 25px;
            line-height: 1.7;
            color: #444;
            position: relative;
            padding-left: 20px;
            border-left: 4px solid #f12971;
            font-style: italic;
        }
        .print-contact-details {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            position: relative;
            z-index: 1;
        }
        .print-contact-item {
            flex: 1 0 45%;
            display: flex;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        .print-contact-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-bottom: 3px solid #f12971;
        }
        .print-contact-icon {
            margin-right: 15px;
            color: #f12971;
            font-size: 24px;
            background: rgba(241, 41, 113, 0.1);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .print-contact-label {
            font-weight: 600;
            margin-right: 10px;
            color: #333;
            font-size: 16px;
        }
        .print-contact-value {
            color: #f12971;
            font-weight: 500;
        }

        /* Activity styles */
        .print-activity-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .print-activity-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 3px solid #f12971;
        }
        .print-activity-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #f12971;
        }
        .print-activity-desc {
            font-size: 14px;
            color: #666;
        }

        /* Gallery styles */
        .print-gallery-item {
            position: relative;
            margin-bottom: 15px;
        }
        .print-gallery-caption {
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 14px;
        }

        /* Place styles */
        .print-place-desc {
            font-size: 12px;
            margin-top: 5px;
        }

        /* Day styles */
        .print-day-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* Feature styles */
        .print-feature-icon {
            display: block;
            font-size: 24px;
            color: #f12971;
            margin-bottom: 10px;
        }
        .print-feature-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .print-feature-desc {
            font-size: 14px;
            color: #666;
        }

        /* FAQ styles */
        .faq-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #f12971;
            color: white;
            text-align: center;
            line-height: 1;
            border-radius: 50%;
            margin-right: 12px;
            font-weight: 700;
            box-shadow: 0 3px 8px rgba(241, 41, 113, 0.3);
            font-size: 16px;
        }

        /* Print-specific adjustments */
        @media print {
            .print-cover {
                height: auto;
                min-height: 100vh;
            }
            .print-featured-image {
                height: auto;
                max-height: 400px;
            }
            .print-gallery-image {
                height: auto;
            }
            .print-guide-image {
                height: auto;
            }
            .print-place {
                height: auto;
            }
            .print-toc-list {
                column-count: 2;
            }
            .print-ticket-table th {
                background-color: #f12971 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-section {
                page-break-inside: avoid;
                margin-bottom: 30px;
            }
            .print-day {
                page-break-inside: avoid;
            }
            .print-travel-tips,
            .print-qr-code,
            .print-qr-code-container {
                page-break-inside: avoid;
            }
            .print-section-title {
                margin-top: -25px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="screen-only print-button-container">
            <button class="print-button" onclick="window.print();"><?php esc_html_e('PRINT THIS TOUR', 'tour-booking-manager'); ?></button>
        </div>

        <!-- Magazine Cover Style Header -->
        <div class="print-cover">
            <?php if ($thumbnail) : ?>
                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($tour_title); ?>" class="print-cover-image">
            <?php endif; ?>
            <div class="print-cover-overlay"></div>
            <div class="print-cover-content">
                <div class="print-cover-header">
                    <?php
                    $logo_url = wp_get_attachment_url(get_theme_mod('custom_logo'));
                    if ($logo_url) :
                    ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="print-cover-logo">
                    <?php else : ?>
                        <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
                    <?php endif; ?>

                    <h1 class="print-cover-title"><?php echo esc_html($tour_title); ?></h1>

                    <?php if ($location) : ?>
                        <h2 class="print-cover-subtitle"><?php echo esc_html($location); ?></h2>
                    <?php endif; ?>
                </div>

                <div class="print-cover-meta">
                    <?php if ($duration) : ?>
                    <div class="print-cover-meta-item">
                        <span class="print-cover-meta-label"><?php esc_html_e('DURATION', 'tour-booking-manager'); ?></span>
                        <span class="print-cover-meta-value"><?php echo esc_html($duration); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($start_price) : ?>
                    <div class="print-cover-meta-item">
                        <span class="print-cover-meta-label"><?php esc_html_e('FROM', 'tour-booking-manager'); ?></span>
                        <span class="print-cover-meta-value"><?php echo wc_price($start_price); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($formatted_date) : ?>
                    <div class="print-cover-meta-item">
                        <span class="print-cover-meta-label"><?php esc_html_e('NEXT DATE', 'tour-booking-manager'); ?></span>
                        <span class="print-cover-meta-value"><?php echo esc_html($formatted_date); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Table of Contents -->
        <div class="print-toc">
            <h2 class="print-toc-title"><?php esc_html_e('CONTENTS', 'tour-booking-manager'); ?></h2>
            <ul class="print-toc-list">
                <li><a href="#overview"><?php esc_html_e('Overview', 'tour-booking-manager'); ?></a></li>
                <?php if (!empty($day_wise_details)) : ?>
                <li><a href="#itinerary"><?php esc_html_e('Itinerary', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($include_services) || !empty($exclude_services)) : ?>
                <li><a href="#inclusions"><?php esc_html_e('What\'s Included/Excluded', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($places)) : ?>
                <li><a href="#places"><?php esc_html_e('Places You\'ll See', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($activities)) : ?>
                <li><a href="#activities"><?php esc_html_e('Activities', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($hotel_lists)) : ?>
                <li><a href="#hotels"><?php esc_html_e('Hotels', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($guides)) : ?>
                <li><a href="#guides"><?php esc_html_e('Tour Guides', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($faq_arr)) : ?>
                <li><a href="#faq"><?php esc_html_e('FAQ', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
                <li><a href="#booking"><?php esc_html_e('Booking Information', 'tour-booking-manager'); ?></a></li>
                <?php if ($contact_phone || $contact_email || $contact_text) : ?>
                <li><a href="#contact"><?php esc_html_e('Contact Information', 'tour-booking-manager'); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="page-break"></div>

        <!-- Magazine Header -->
        <header class="print-header" id="overview">
            <?php
            if ($logo_url) :
            ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="print-logo">
            <?php else : ?>
                <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
            <?php endif; ?>

            <h1 class="print-title"><?php echo esc_html($tour_title); ?></h1>

            <div class="print-decorative-line"></div>
        </header>

        <!-- Key Information Box -->
        <div class="print-meta">
            <?php if ($start_price) : ?>
            <div class="print-meta-item">
                <span class="print-meta-label"><?php esc_html_e('PRICE FROM', 'tour-booking-manager'); ?></span>
                <span class="print-meta-value"><?php echo wc_price($start_price); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($formatted_date) : ?>
            <div class="print-meta-item">
                <span class="print-meta-label"><?php esc_html_e('NEXT AVAILABLE DATE', 'tour-booking-manager'); ?></span>
                <span class="print-meta-value"><?php echo esc_html($formatted_date); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Featured Image -->
        <?php if ($thumbnail) : ?>
            <div class="print-featured-image-container">
                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($tour_title); ?>" class="print-featured-image">
            </div>
        <?php endif; ?>

        <!-- Tour Description -->
        <?php if ($short_description || $description) : ?>
        <div class="print-description print-magazine-text">
            <?php if ($short_description) : ?>
                <p><strong><?php echo wp_kses_post($short_description); ?></strong></p>
            <?php endif; ?>

            <?php if ($description) : ?>
                <?php echo wp_kses_post($description); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- What's Included and Excluded -->
        <?php if (!empty($include_services) || !empty($exclude_services)) : ?>
        <section class="print-section" id="inclusions">
            <h2 class="print-section-title"><?php esc_html_e('What\'s Included & Excluded', 'tour-booking-manager'); ?></h2>
            <div class="print-services">
                <?php if (!empty($include_services)) : ?>
                <div class="print-service-column">
                    <h3 class="print-service-title"><?php esc_html_e('What\'s Included', 'tour-booking-manager'); ?></h3>
                    <ul class="print-service-list included">
                        <?php foreach ($include_services as $service) :
                            if (!empty($service['service_name'])) :
                        ?>
                            <li><?php echo esc_html($service['service_name']); ?></li>
                        <?php
                            endif;
                        endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($exclude_services)) : ?>
                <div class="print-service-column">
                    <h3 class="print-service-title"><?php esc_html_e('What\'s Excluded', 'tour-booking-manager'); ?></h3>
                    <ul class="print-service-list excluded">
                        <?php foreach ($exclude_services as $service) :
                            if (!empty($service['service_name'])) :
                        ?>
                            <li><?php echo esc_html($service['service_name']); ?></li>
                        <?php
                            endif;
                        endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Activities Section -->
        <?php if (!empty($activities)) : ?>
        <section class="print-section" id="activities">
            <h2 class="print-section-title"><?php esc_html_e('ACTIVITIES', 'tour-booking-manager'); ?></h2>
            <div class="print-activities">
                <?php foreach ($activities as $activity_id) :
                    $activity_title = get_the_title($activity_id);
                    $activity_desc = get_post_field('post_content', $activity_id);
                    $activity_image = MP_Global_Function::get_image_url($activity_id);
                    if ($activity_title) :
                ?>
                    <div class="print-activity-item">
                        <?php if ($activity_image) : ?>
                            <img src="<?php echo esc_url($activity_image); ?>" alt="<?php echo esc_attr($activity_title); ?>" class="print-activity-image">
                        <?php endif; ?>
                        <h3 class="print-activity-title"><?php echo esc_html($activity_title); ?></h3>
                        <?php if ($activity_desc) : ?>
                            <p class="print-activity-desc"><?php echo wp_trim_words($activity_desc, 20); ?></p>
                        <?php endif; ?>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Gallery Section -->
        <?php if (!empty($gallery_images)) : ?>
        <section class="print-section" id="gallery">
            <h2 class="print-section-title"><?php esc_html_e('PHOTO GALLERY', 'tour-booking-manager'); ?></h2>
            <div class="print-gallery">
                <?php
                $count = 0;
                foreach ($gallery_images as $image_id) :
                    if ($count >= 6) break; // Limit to 6 images for print
                    $image_url = wp_get_attachment_image_url($image_id, 'medium_large');
                    $image_caption = wp_get_attachment_caption($image_id);
                    if ($image_url) :
                        $count++;
                ?>
                    <div class="print-gallery-item">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($tour_title); ?> - <?php echo esc_attr__('Gallery Image', 'tour-booking-manager'); ?> <?php echo esc_attr($count); ?>" class="print-gallery-image">
                        <?php if ($image_caption) : ?>
                            <div class="print-gallery-caption"><?php echo esc_html($image_caption); ?></div>
                        <?php endif; ?>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Tour Details Box -->
        <div class="print-highlight">
            <div class="print-two-column">
                <div class="print-column">
                    <?php if ($duration) : ?>
                    <div class="print-meta-item">
                        <span class="print-meta-label"><?php esc_html_e('Duration', 'tour-booking-manager'); ?></span>
                        <span class="print-meta-value"><?php echo esc_html($duration); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($group_size) : ?>
                    <div class="print-meta-item">
                        <span class="print-meta-label"><?php esc_html_e('Group Size', 'tour-booking-manager'); ?></span>
                        <span class="print-meta-value"><?php echo esc_html($group_size); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($total_seat) : ?>
                    <div class="print-meta-item">
                        <span class="print-meta-label"><?php esc_html_e('Total Seats', 'tour-booking-manager'); ?></span>
                        <span class="print-meta-value"><?php echo esc_html($total_seat); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="print-column">
                    <?php if ($available_seat) : ?>
                    <div class="print-meta-item">
                        <span class="print-meta-label"><?php esc_html_e('Available Seats', 'tour-booking-manager'); ?></span>
                        <span class="print-meta-value"><?php echo esc_html($available_seat); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($location) : ?>
                    <div class="print-meta-item">
                        <span class="print-meta-label"><?php esc_html_e('Location', 'tour-booking-manager'); ?></span>
                        <span class="print-meta-value"><?php echo esc_html($location); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($tour_type) : ?>
                    <div class="print-meta-item">
                        <span class="print-meta-label"><?php esc_html_e('Tour Type', 'tour-booking-manager'); ?></span>
                        <span class="print-meta-value"><?php echo esc_html($tour_type); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Places You'll See -->
        <?php if (!empty($places)) : ?>
        <section class="print-section" id="places">
            <h2 class="print-section-title"><?php esc_html_e('Places You\'ll See', 'tour-booking-manager'); ?></h2>
            <div class="print-places">
                <?php foreach ($places as $place) :
                    if (!empty($place['ttbm_place_label']) && !empty($place['ttbm_city_place_id'])) :
                        $place_id = $place['ttbm_city_place_id'];
                        $place_name = $place['ttbm_place_label'];
                        $place_image = MP_Global_Function::get_image_url($place_id);
                        $place_desc = get_post_field('post_content', $place_id);
                ?>
                    <div class="print-place">
                        <?php if ($place_image) : ?>
                            <img src="<?php echo esc_url($place_image); ?>" alt="<?php echo esc_attr($place_name); ?>" class="print-place-image">
                        <?php endif; ?>
                        <div class="print-place-overlay">
                            <h4 class="print-place-name"><?php echo esc_html($place_name); ?></h4>
                            <?php if ($place_desc) : ?>
                                <div class="print-place-desc"><?php echo wp_trim_words($place_desc, 10); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Itinerary / Day-wise Details -->
        <?php if (!empty($day_wise_details)) : ?>
        <section class="print-section" id="itinerary">
            <h2 class="print-section-title"><?php esc_html_e('Detailed Itinerary', 'tour-booking-manager'); ?></h2>
            <div class="print-itinerary">
                <?php foreach ($day_wise_details as $index => $day) :
                    if (!empty($day['ttbm_day_title']) && !empty($day['ttbm_day_description'])) :
                        $day_image = !empty($day['ttbm_day_image']) ? wp_get_attachment_image_url($day['ttbm_day_image'], 'medium_large') : '';
                ?>
                    <div class="print-day">
                        <h3 class="print-day-title"><?php echo esc_html($day['ttbm_day_title']); ?></h3>
                        <?php if ($day_image) : ?>
                            <img src="<?php echo esc_url($day_image); ?>" alt="<?php echo esc_attr($day['ttbm_day_title']); ?>" class="print-day-image">
                        <?php endif; ?>
                        <div class="print-day-content">
                            <?php echo wp_kses_post($day['ttbm_day_description']); ?>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Tour Features -->
        <?php if (!empty($features)) : ?>
        <section class="print-section" id="features">
            <h2 class="print-section-title"><?php esc_html_e('Tour Features', 'tour-booking-manager'); ?></h2>
            <div class="print-features">
                <?php foreach ($features as $feature_id) :
                    $feature_title = get_the_title($feature_id);
                    $feature_desc = get_post_field('post_content', $feature_id);
                    $feature_icon = get_term_meta($feature_id, 'ttbm_feature_icon', true);
                    if ($feature_title) :
                ?>
                    <div class="print-feature-item">
                        <?php if ($feature_icon) : ?>
                            <span class="print-feature-icon"><i class="<?php echo esc_attr($feature_icon); ?>"></i></span>
                        <?php endif; ?>
                        <h3 class="print-feature-title"><?php echo esc_html($feature_title); ?></h3>
                        <?php if ($feature_desc) : ?>
                            <p class="print-feature-desc"><?php echo wp_trim_words($feature_desc, 15); ?></p>
                        <?php endif; ?>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Available Hotels -->
        <?php if (!empty($hotel_lists)) : ?>
        <section class="print-section" id="hotels">
            <h2 class="print-section-title"><?php esc_html_e('Accommodation Options', 'tour-booking-manager'); ?></h2>
            <div class="print-hotels">
                <?php foreach ($hotel_lists as $hotel_id) :
                    $hotel_name = get_the_title($hotel_id);
                    $hotel_image = get_the_post_thumbnail_url($hotel_id, 'medium');
                    $hotel_desc = get_post_field('post_content', $hotel_id);
                    $hotel_rating = MP_Global_Function::get_post_info($hotel_id, 'ttbm_hotel_rating', '');
                    $hotel_min_price = TTBM_Function::get_hotel_room_min_price($hotel_id);
                    $hotel_address = MP_Global_Function::get_post_info($hotel_id, 'ttbm_hotel_address', '');
                    $hotel_amenities = MP_Global_Function::get_post_info($hotel_id, 'ttbm_hotel_amenities', array());
                    if ($hotel_name) :
                ?>
                    <div class="print-hotel">
                        <?php if ($hotel_image) : ?>
                            <img src="<?php echo esc_url($hotel_image); ?>" alt="<?php echo esc_attr($hotel_name); ?>" class="print-hotel-image">
                        <?php endif; ?>
                        <div class="print-hotel-content">
                            <h3 class="print-hotel-name"><?php echo esc_html($hotel_name); ?></h3>
                            <?php if ($hotel_rating) : ?>
                                <div class="print-hotel-rating">
                                    <?php for ($i = 0; $i < $hotel_rating; $i++) echo '★'; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($hotel_address) : ?>
                                <div class="print-hotel-address">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo esc_html($hotel_address); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($hotel_desc) : ?>
                                <div class="print-hotel-desc">
                                    <?php echo wp_trim_words($hotel_desc, 30); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($hotel_amenities)) : ?>
                                <div class="print-hotel-amenities">
                                    <h4><?php esc_html_e('Amenities', 'tour-booking-manager'); ?></h4>
                                    <ul>
                                        <?php foreach ($hotel_amenities as $amenity) : ?>
                                            <li><?php echo esc_html($amenity); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($hotel_min_price) : ?>
                                <div class="print-hotel-price">
                                    <?php esc_html_e('Price From: ', 'tour-booking-manager'); ?> <?php echo wc_price($hotel_min_price); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Tour Guides -->
        <?php if (!empty($guides)) : ?>
        <section class="print-section" id="guides">
            <h2 class="print-section-title"><?php esc_html_e('Meet Your Tour Guides', 'tour-booking-manager'); ?></h2>
            <div class="print-guides">
                <?php foreach ($guides as $guide_id) :
                    $guide_name = get_the_title($guide_id);
                    $guide_image = MP_Global_Function::get_image_url($guide_id);
                    $guide_desc = get_post_field('post_content', $guide_id);
                    $guide_position = MP_Global_Function::get_post_info($guide_id, 'ttbm_guide_position', '');
                    $guide_experience = MP_Global_Function::get_post_info($guide_id, 'ttbm_guide_experience', '');
                    $guide_languages = MP_Global_Function::get_post_info($guide_id, 'ttbm_guide_languages', array());
                    if ($guide_name) :
                ?>
                    <div class="print-guide">
                        <?php if ($guide_image) : ?>
                            <img src="<?php echo esc_url($guide_image); ?>" alt="<?php echo esc_attr($guide_name); ?>" class="print-guide-image">
                        <?php endif; ?>
                        <h3 class="print-guide-name"><?php echo esc_html($guide_name); ?></h3>
                        <?php if ($guide_position) : ?>
                            <div class="print-guide-position"><?php echo esc_html($guide_position); ?></div>
                        <?php endif; ?>
                        <?php if ($guide_experience) : ?>
                            <div class="print-guide-experience">
                                <strong><?php esc_html_e('Experience:', 'tour-booking-manager'); ?></strong> <?php echo esc_html($guide_experience); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($guide_languages)) : ?>
                            <div class="print-guide-languages">
                                <strong><?php esc_html_e('Languages:', 'tour-booking-manager'); ?></strong> <?php echo esc_html(implode(', ', $guide_languages)); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($guide_desc) : ?>
                            <p class="print-guide-desc"><?php echo wp_trim_words($guide_desc, 30); ?></p>
                        <?php endif; ?>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Why Choose Us -->
        <?php if ($why_choose_us) : ?>
        <section class="print-section" id="why-us">
            <h2 class="print-section-title"><?php esc_html_e('Why Book With Us?', 'tour-booking-manager'); ?></h2>
            <div class="print-callout">
                <h3 class="print-callout-title"><?php esc_html_e('Our Commitment to You', 'tour-booking-manager'); ?></h3>
                <div class="print-why-choose-us">
                    <?php echo wp_kses_post($why_choose_us); ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- FAQ Section -->
        <?php
        $faq_arr = MP_Global_Function::get_post_info($tour_id, 'mep_event_faq', array());
        $display_faq = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_faq', 'on');

        if (!empty($faq_arr)) :
        ?>
        <section class="print-section" id="faq">
            <h2 class="print-section-title"><?php esc_html_e('FREQUENTLY ASKED QUESTIONS', 'tour-booking-manager'); ?></h2>
            <div class="print-faq">
                <?php
                $faq_count = 0;
                foreach ($faq_arr as $faq) :
                    if (!empty($faq['ttbm_faq_title']) && !empty($faq['ttbm_faq_content'])) :
                        $faq_count++;
                ?>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-number"><?php echo esc_html($faq_count); ?>.</span>
                            <?php echo esc_html($faq['ttbm_faq_title']); ?>
                        </div>
                        <div class="faq-answer"><?php echo wp_kses_post($faq['ttbm_faq_content']); ?></div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Booking Information -->
        <section class="print-section" id="booking">
            <h2 class="print-section-title"><?php esc_html_e('Booking Information', 'tour-booking-manager'); ?></h2>

            <!-- Ticket Types and Pricing -->
            <?php if (!empty($ticket_list)) : ?>
            <div class="print-ticket-types">
                <h3 class="print-subsection-title"><?php esc_html_e('Ticket Types & Pricing', 'tour-booking-manager'); ?></h3>
                <table class="print-ticket-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Ticket Type', 'tour-booking-manager'); ?></th>
                            <th><?php esc_html_e('Description', 'tour-booking-manager'); ?></th>
                            <th><?php esc_html_e('Regular Price', 'tour-booking-manager'); ?></th>
                            <th><?php esc_html_e('Sale Price', 'tour-booking-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ticket_list as $ticket) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($ticket['ticket_type_name']); ?></strong></td>
                                <td><?php echo !empty($ticket['ticket_type_description']) ? esc_html($ticket['ticket_type_description']) : ''; ?></td>
                                <td><?php echo wc_price($ticket['ticket_type_price']); ?></td>
                                <td><?php echo !empty($ticket['sale_price']) ? wc_price($ticket['sale_price']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Extra Services -->
            <?php if (!empty($extra_services)) : ?>
            <div class="print-extra-services">
                <h3 class="print-subsection-title"><?php esc_html_e('Optional Extra Services', 'tour-booking-manager'); ?></h3>
                <table class="print-services-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Service', 'tour-booking-manager'); ?></th>
                            <th><?php esc_html_e('Description', 'tour-booking-manager'); ?></th>
                            <th><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($extra_services as $service) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($service['service_name']); ?></strong></td>
                                <td><?php echo !empty($service['extra_service_description']) ? esc_html($service['extra_service_description']) : ''; ?></td>
                                <td><?php echo wc_price($service['service_price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Travel Tips -->
            <div class="print-travel-tips">
                <h3 class="print-subsection-title"><?php esc_html_e('Travel Tips', 'tour-booking-manager'); ?></h3>
                <ul class="print-tips-list">
                    <?php if ($min_age) : ?>
                        <li><strong><?php esc_html_e('Minimum Age:', 'tour-booking-manager'); ?></strong> <?php echo esc_html($min_age); ?></li>
                    <?php endif; ?>
                    <?php if ($max_people) : ?>
                        <li><strong><?php esc_html_e('Maximum Group Size:', 'tour-booking-manager'); ?></strong> <?php echo esc_html($max_people); ?> <?php esc_html_e('people', 'tour-booking-manager'); ?></li>
                    <?php endif; ?>
                    <?php if ($description) : ?>
                    <li><strong><?php esc_html_e('Tour Notes:', 'tour-booking-manager'); ?></strong> <?php echo wp_trim_words(wp_strip_all_tags($description), 20, '...'); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- QR Code for Booking -->
            <div class="print-qr-code">
                <h3 class="print-subsection-title"><?php esc_html_e('Book Online', 'tour-booking-manager'); ?></h3>
                <div class="print-qr-code-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($tour_permalink); ?>" alt="Scan to book this tour">
                    <div class="print-qr-info">
                        <p class="print-qr-code-text"><?php esc_html_e('Scan this QR code to book this tour online', 'tour-booking-manager'); ?></p>
                        <p class="print-qr-url"><?php echo esc_url($tour_permalink); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Information -->
        <?php if ($contact_phone || $contact_email || $contact_text) : ?>
        <section class="print-section" id="contact">
            <h2 class="print-section-title"><?php esc_html_e('Contact Information', 'tour-booking-manager'); ?></h2>
            <div class="print-contact">
                <?php if ($contact_text) : ?>
                    <p class="print-contact-text"><?php echo esc_html($contact_text); ?></p>
                <?php endif; ?>

                <div class="print-contact-details">
                    <?php if ($contact_phone) : ?>
                        <div class="print-contact-item">
                            <span class="print-contact-icon"><i class="fas fa-phone"></i></span>
                            <span class="print-contact-label"><?php esc_html_e('Phone:', 'tour-booking-manager'); ?></span>
                            <span class="print-contact-value"><?php echo esc_html($contact_phone); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($contact_email) : ?>
                        <div class="print-contact-item">
                            <span class="print-contact-icon"><i class="fas fa-envelope"></i></span>
                            <span class="print-contact-label"><?php esc_html_e('Email:', 'tour-booking-manager'); ?></span>
                            <span class="print-contact-value"><?php echo esc_html($contact_email); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="print-contact-item">
                        <span class="print-contact-icon"><i class="fas fa-globe"></i></span>
                        <span class="print-contact-label"><?php esc_html_e('Website:', 'tour-booking-manager'); ?></span>
                        <span class="print-contact-value"><?php echo esc_url($site_url); ?></span>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Categories and Tags -->
        <?php if (!empty($categories) || !empty($organizers) || !empty($locations)) : ?>
        <div class="print-categories">
            <?php if (!empty($categories)) :
                foreach ($categories as $category) : ?>
                    <span class="print-category"><?php echo esc_html($category->name); ?></span>
                <?php endforeach;
            endif; ?>

            <?php if (!empty($organizers)) :
                foreach ($organizers as $organizer) : ?>
                    <span class="print-category"><?php echo esc_html($organizer->name); ?></span>
                <?php endforeach;
            endif; ?>

            <?php if (!empty($locations)) :
                foreach ($locations as $location_term) : ?>
                    <span class="print-category"><?php echo esc_html($location_term->name); ?></span>
                <?php endforeach;
            endif; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="print-footer">
            <?php
            if ($logo_url) :
            ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="print-footer-logo">
            <?php endif; ?>
            <p><?php echo esc_html(get_bloginfo('name')); ?> © <?php echo esc_html(date('Y')); ?></p>
            <p><?php esc_html_e('This document was printed from', 'tour-booking-manager'); ?> <?php echo esc_html(get_permalink($tour_id)); ?></p>
        </footer>
    </div>
</body>
</html>
