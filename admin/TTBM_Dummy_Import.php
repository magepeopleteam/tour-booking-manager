<?php

    if (!defined('ABSPATH')) 
    {
        die;
    } // Cannot access pages directly.

    if (!class_exists('TTBM_Dummy_Import')) 
    {

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        class TTBM_Dummy_Import 
        {
            public function __construct() 
            {
                //update_option('ttbm_dummy_already_inserted','no');exit;
                add_action('admin_init', array($this, 'dummy_import'), 99);
            }

            public static function check_plugin($plugin_dir_name, $plugin_file): int
            {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
                $plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
                if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) 
                {
                    return 1;
                } 
                elseif (is_dir($plugin_dir)) 
                {
                    return 2;
                } 
                else 
                {
                    return 0;
                }
            }

            public function dummy_import() 
            {

                $dummy_post_inserted = get_option('ttbm_dummy_already_inserted','no');
                $count_existing_event = wp_count_posts('ttbm_tour')->publish;
                
                $plugin_active = self::check_plugin('tour-booking-manager', 'tour-booking-manager.php');
                
                if ($count_existing_event == 0 && $plugin_active == 1 && $dummy_post_inserted != 'yes') 
                {
                    $dummy_taxonomies = $this->dummy_taxonomy();

                    if(array_key_exists('taxonomy', $dummy_taxonomies))
                    {
                        foreach ($dummy_taxonomies['taxonomy'] as $taxonomy => $dummy_taxonomy) 
                        { 
                            if (taxonomy_exists($taxonomy)) 
                            { 
                                $check_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));

                                if (is_string($check_terms) || sizeof($check_terms) == 0) {
                                    foreach ($dummy_taxonomy as $taxonomy_data) {
                                        unset($term);
                                        $term = wp_insert_term($taxonomy_data['name'], $taxonomy);
                                        
                                        if (array_key_exists('term_id', $taxonomy_data)) 
                                        {
                                            add_term_meta( $taxonomy_data['term_id'], 'ttbm_feature_icon', 'raselsa', false );
                                        }
                                        
                                        if (array_key_exists('tax_data', $taxonomy_data)) {
                                            foreach ($taxonomy_data['tax_data'] as $meta_key => $data) {
                                                update_term_meta($term['term_id'], $meta_key, $data);
                                            }
                                        }
                                    }
                                }

                            }

                        }

                    }

                    $dummy_cpt = $this->dummy_cpt();

                    if(array_key_exists('custom_post', $dummy_cpt))
                    {
                        $dummy_images = self::dummy_images();

                        foreach ($dummy_cpt['custom_post'] as $custom_post => $dummy_post) 
                        {
                            unset($args);
                            $args = array(
                                'post_type' => $custom_post,
                                'posts_per_page' => -1,
                            );

                            unset($post);
                            $post = new WP_Query($args);

                            if ($post->post_count == 0) 
                            {
                                foreach ($dummy_post as $dummy_data) 
                                {
                                    $args = array();
                                    if(isset($dummy_data['name']))$args['post_title'] = $dummy_data['name'];
                                    if(isset($dummy_data['content']))$args['post_content'] = $dummy_data['content'];
                                    $args['post_status'] = 'publish';
                                    $args['post_type'] = $custom_post;

                                    $post_id = wp_insert_post($args);

                                    if (array_key_exists('taxonomy_terms', $dummy_data) && count($dummy_data['taxonomy_terms'])) 
                                    {
                                        foreach ($dummy_data['taxonomy_terms'] as $taxonomy_term) 
                                        {
                                            wp_set_object_terms( $post_id, $taxonomy_term['terms'], $taxonomy_term['taxonomy_name'], true );
                                        }
                                    }

                                    if (array_key_exists('post_data', $dummy_data)) 
                                    {
                                        foreach ($dummy_data['post_data'] as $meta_key => $data) 
                                        {
                                            if ($meta_key == 'ttbm_gallery_images') 
                                            {
                                                if(is_array($data))
                                                {
                                                    $thumnail_ids = array();

                                                    foreach($data as $url_index)
                                                    {
                                                        if(isset($dummy_images[$url_index]))
                                                        {
                                                            $thumnail_ids[] = $dummy_images[$url_index];
                                                        }
                                                        
                                                    }

                                                    update_post_meta($post_id,'ttbm_gallery_images',$thumnail_ids);
                                                    if(count($thumnail_ids))
                                                    {
                                                        set_post_thumbnail( $post_id, $thumnail_ids[0] );
                                                    }                                                    
                                                }
                                                else
                                                {
                                                    update_post_meta($post_id,'ttbm_gallery_images',array(isset($dummy_images[$data])?$dummy_images[$data]:''));
                                                }

                                            }
                                            else 
                                            {
                                                update_post_meta($post_id, $meta_key, $data);
                                            }

                                        }
                                    }

                                }
                            }
                        }
                    }
                    //$this->craete_pages();
                    update_option('ttbm_dummy_already_inserted', 'yes');
                }
            }

            public static function dummy_images()
            {
                $urls = array(
                    'https://img.freepik.com/free-photo/blue-villa-beautiful-sea-hotel_1203-5316.jpg',
                    'https://img.freepik.com/free-photo/beautiful-mountains-ratchaprapha-dam-khao-sok-national-park-surat-thani-province-thailand_335224-851.jpg',
                    'https://img.freepik.com/free-photo/photographer-taking-picture-ocean-coast_657883-287.jpg',
                    'https://img.freepik.com/free-photo/pileh-blue-lagoon-phi-phi-island-thailand_231208-1487.jpg',
                    'https://img.freepik.com/free-photo/godafoss-waterfall-sunset-winter-iceland-guy-red-jacket-looks-godafoss-waterfall_335224-673.jpg',

                );

                unset($image_ids);
                $image_ids = array();

                foreach($urls as $url)
                {
                    $image_ids[] = media_sideload_image($url, '0', $url, 'id');
                }

                return $image_ids;
            }

            public function dummy_taxonomy(): array {
                return [
                    'taxonomy' => [
                        'ttbm_tour_cat' => [
                            0 => ['name' => 'Fixed Tour'],
                            1 => ['name' => 'Flexible Tour']
                        ],
                        'ttbm_tour_org' => [
                            0 => ['name' => 'Autotour'],
                            1 => ['name' => 'Holiday Partner'],
                            2 => ['name' => 'Zayman']
                        ],
                        'ttbm_tour_location' => [
                            0 => ['name' => 'Bandarban', 'country' => 'Bangladesh'],
                            1 => ['name' => 'Coxbazar', 'country' => 'Bangladesh'],
                            2 => ['name' => 'Las Vegas', 'country' => 'United States'],
                            3 => ['name' => 'Naples Italy', 'country' => 'Italy'],
                            4 => ['name' => 'Rangamati', 'country' => 'Bangladesh'],
                            5 => ['name' => 'Sajek', 'country' => 'Bangladesh'],
                            6 => ['name' => 'Sapuland', 'country' => 'Afghanistan'],
                        ],
                        'ttbm_tour_features_list' => [

                            0 => [
                                'name' => 'Accommodation',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-hotel'
                                ),
                            ],
                            1 => [
                                'name' => 'Additional Services',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-spa'
                                ),
                            ],
                            2 => [
                                'name' => 'Airport Transfer',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-car'
                                ),
                            ],
                            3 => [
                                'name' => 'BBQ Night',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-drumstick-bite'
                                ),
                            ],
                            4 => [
                                'name' => 'Breakfast',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-utensils'
                                ),
                            ],
                            5 => [
                                'name' => 'Concert Ticket',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas  fa-ticket-alt'
                                ),
                            ],
                            6 => [
                                'name' => 'Flights',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-fighter-jet'
                                ),
                            ],
                            7 => [
                                'name' => 'Guide',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-book-open'
                                ),
                            ],
                            8 => [
                                'name' => 'Hotel Rent',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-bed'
                                ),
                            ],
                            9 => [
                                'name' => 'Insurance',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-user-shield'
                                ),
                            ],
                            10 => [
                                'name' => 'Lunch',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-hamburger'
                                ),
                            ],
                            11 => [
                                'name' => 'Meals',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-cheese'
                                ),
                            ],
                            12 => [
                                'name' => 'Newspaper',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-newspaper'
                                ),
                            ],
                            13 => [
                                'name' => 'Outing Ticket',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-clipboard-check'
                                ),
                            ],
                            14 => [
                                'name' => 'Transport',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-taxi'
                                ),
                            ],
                            15 => [
                                'name' => 'Welcome Drinks',
                                'tax_data' => array(
                                    'ttbm_feature_icon' => 'fas fa-wine-glass-alt'
                                ),
                            ],
                        ],
                        'ttbm_tour_tag' => [
                            0 => ['name' => 'Cultural'],
                            1 => ['name' => 'Relax']
                        ],
                        'ttbm_tour_activities' => [
                            0 => ['name' => 'Beach'],
                            1 => ['name' => 'City Tours'],
                            2 => ['name' => 'Hiking'],
                            3 => ['name' => 'Rural'],
                            4 => ['name' => 'Snow & Ice']
                        ],
                    ],
                ];
            }
            public function dummy_cpt(): array {
                return [
                    'custom_post' => [
                        'ttbm_places' => [
                            0 => ['name' => 'Bogura'],
                            1 => ['name' => 'Dim Pahar'],
                            2 => ['name' => 'Ravello'],
                            3 => ['name' => 'Amalfi'],
                            4 => ['name' => 'Positano'],
                            5 => ['name' => 'Pompeii'],
                            6 => ['name' => 'Capri'],
                            7 => ['name' => 'Sorrento'],
                            8 => ['name' => 'Naples'],
                            9 => ['name' => 'Brandenburger Tor'],
                            10 => ['name' => 'Rotes Rathaus (Neptune Fountain)'],
                            11 => ['name' => 'Alexanderplatz (Alexa)'],
                            12 => ['name' => 'Gendarmenmarkt / Taubenstr'],
                            13 => ['name' => 'Checkpoint Charlie'],
                            14 => ['name' => 'Berliner Mauer / Martin-Gropius-Bau'],
                            15 => ['name' => 'Dolphine Square'],
                            16 => ['name' => 'Moheshkhali'],
                            17 => ['name' => 'Inani Beach'],
                            18 => ['name' => 'Ramu'],
                            19 => ['name' => 'Himchori'],
                        ],
                        'ttbm_guide' => [
                            0 => ['name' => 'Adam Smith'],
                            1 => ['name' => 'Mahim'],
                            2 => ['name' => 'Shamim'],
                            3 => ['name' => 'Sumon'],
                            4 => ['name' => 'Rabiul'],
                        ],
                        'ttbm_tour' => [
                            0 => [
                                'name' => 'The Mentalist Tickets: Las Vegas',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'ttbm_travel_duration' => 2,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 3,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => 250,
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 10,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => 'Las Vegas',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => 'Watch Gerry McCambridge perform comedy, magic, and mind reading live on stage at the amazing 75-minute Las Vegas show, The Mentalist! McCambridge has been nominated “Best Magician in Las Vegas”, so come and see him live for a mind-blowing night.',
                                    //date_settings
                                    'ttbm_travel_type' => 'fixed',
                                    'ttbm_travel_start_date' => date('Y-m-d', strtotime(' +25 day')),
                                    'ttbm_travel_reg_end_date' => date('Y-m-d', strtotime(' +30 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 15,
                                            'sale_price' => 10,
                                            'ticket_type_qty' => 150,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 5,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 10,
                                            'sale_price' => 7,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 25,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'Breakfast',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'BBQ Night',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'Beach',
                                        1 => 'Hiking',
                                        2 => 'Snow & Ice',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => array(0,1,2,3,4), 
                                    
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitage to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'availability_section',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            1 => [
                                'name' => 'Highlights of Naples and the Amalfi Coast',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    'ttbm_travel_duration' => 1,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 1,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => 180,
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 5,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => 'Naple',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => 'Watch Gerry McCambridge perform comedy, magic, and mind reading live on stage at the amazing 75-minute Las Vegas show, The Mentalist! McCambridge has been nominated “Best Magician in Las Vegas”, so come and see him live for a mind-blowing night.',
                                    //date_settings
                                    'ttbm_travel_type' => 'fixed',
                                    'ttbm_travel_start_date' => date('Y-m-d', strtotime(' +35 day')),
                                    'ttbm_travel_reg_end_date' => date('Y-m-d', strtotime(' +36 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 55,
                                            'sale_price' => 40,
                                            'ticket_type_qty' => 220,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 5,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 70,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'Breakfast',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'BBQ Night',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'City Tours',
                                        1 => 'Hiking',
                                        2 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => array(4,3,2,1,0),
                                    
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'availability_section',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            2 => [
                                'name' => 'Deep-Sea Exploration on a Shampan',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'ttbm_travel_duration' => 1,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 1,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => '',
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 5,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => '',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => 'Watch Gerry McCambridge perform comedy, magic, and mind reading live on stage at the amazing 75-minute Las Vegas show, The Mentalist! McCambridge has been nominated “Best Magician in Las Vegas”, so come and see him live for a mind-blowing night.',
                                    //date_settings
                                    'ttbm_travel_type' => 'repeated',
                                    'ttbm_travel_repeated_after' => '4',
                                    'ttbm_travel_repeated_start_date' => date('Y-m-d', strtotime(' +15 day')),
                                    'ttbm_travel_repeated_end_date' => date('Y-m-d', strtotime(' +365 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 55,
                                            'sale_price' => 40,
                                            'ticket_type_qty' => 220,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 5,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 70,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'Breakfast',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'BBQ Night',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'City Tours',
                                        1 => 'Hiking',
                                        2 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => array(3,4,2,1,0),
                                    
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'regular_ticket',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            3 => [
                                'name' => 'Beach Hopping at Inani, Himchari, Patuartek',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'ttbm_travel_duration' => 2,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 1,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => '',
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 12,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => '',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => 'Watch Gerry McCambridge perform comedy, magic, and mind reading live on stage at the amazing 75-minute Las Vegas show, The Mentalist! McCambridge has been nominated “Best Magician in Las Vegas”, so come and see him live for a mind-blowing night.',
                                    //date_settings
                                    'ttbm_travel_type' => 'repeated',
                                    'ttbm_travel_repeated_after' => '7',
                                    'ttbm_travel_repeated_start_date' => date('Y-m-d', strtotime(' +25 day')),
                                    'ttbm_travel_repeated_end_date' => date('Y-m-d', strtotime(' +365 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 105,
                                            'sale_price' => 100,
                                            'ticket_type_qty' => 200,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 2,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 90,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'BBQ Night',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'Breakfast',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'City Tours',
                                        1 => 'Hiking',
                                        2 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => array(1,2,3,4,0),
                                    
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'regular_ticket',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            4 => [
                                'name' => 'Boga Lake : A Relaxing Gateway Tour',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'ttbm_travel_duration' => 4,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 5,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => '',
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 18,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => '',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => 'Watch Gerry McCambridge perform comedy, magic, and mind reading live on stage at the amazing 75-minute Las Vegas show, The Mentalist! McCambridge has been nominated “Best Magician in Las Vegas”, so come and see him live for a mind-blowing night.',
                                    //date_settings
                                    'ttbm_travel_type' => 'repeated',
                                    'ttbm_travel_repeated_after' => '15',
                                    'ttbm_travel_repeated_start_date' => date('Y-m-d', strtotime(' +35 day')),
                                    'ttbm_travel_repeated_end_date' => date('Y-m-d', strtotime(' +365 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 105,
                                            'sale_price' => 100,
                                            'ticket_type_qty' => 200,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 2,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 90,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'BBQ Night',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'Breakfast',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'Hiking',
                                        1 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => array(2,0,3,4,1),
                                    
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'regular_ticket',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                        ]
                    ]
                ];
            }
        }

        new TTBM_Dummy_Import();
    }

