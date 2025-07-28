(function ($) {

    function ttbm_promotional_tour_carousal( id ){
        let target_related_tour = $("#"+id+" .owl-carousel");
        console.log( target_related_tour );
        let trt_num = target_related_tour.data('show');
        let trt_num_600 = Math.min(trt_num - 2, 2)
        trt_num_600 = Math.max(trt_num_600, 1)
        let trt_num_800 = Math.min(trt_num - 1, 3)
        trt_num_800 = Math.max(trt_num_800, 1)
        target_related_tour.owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: trt_num_600
                },
                800: {
                    items: trt_num_800
                },
                1000: {
                    items: trt_num
                }
            }
        });

        $("#"+id+" .next").click(function () {
            $('#'+id+' .owl-next').trigger('click');
        });
        $("#"+id+" .prev").click(function () {
            $('#'+id+' .owl-prev').trigger('click');
        });
    }

    ttbm_promotional_tour_carousal( 'ttbm_popular_tour' );
    ttbm_promotional_tour_carousal( 'ttbm_feature_tour' );
    ttbm_promotional_tour_carousal( 'ttbm_trending_tour' );
    ttbm_promotional_tour_carousal( 'ttbm_deal-discount_tour' );
    ttbm_promotional_tour_carousal( 'ttbm_top_attraction' );
    ttbm_promotional_tour_carousal( 'ttbm_browse_activities' );
    ttbm_promotional_tour_carousal( 'ttbm_category_shortcode' );
    ttbm_promotional_tour_carousal( 'ttbm_organizer_shortcode' );
    ttbm_promotional_tour_carousal( 'ttbm_tag_shortcode' );
    ttbm_promotional_tour_carousal( 'ttbm_feature_shortcode' );


    function ttbm_taxonomy_list_load_more( a, b, c) {
        const loadMoreBtn = $('#'+a);
        const itemsPerLoad = parseInt($('#'+b).val()) || 3;
        const allItems = $('.'+c);
        let currentIndex = 0;

        function showNextItems() {
            const nextItems = allItems.slice(currentIndex, currentIndex + itemsPerLoad);
            nextItems.each(function () {
                const itemId = $(this).attr('id');
                // $('#' + itemId).fadeIn();
                // console.log( itemId);
                $(this).fadeIn();
            });

            currentIndex += itemsPerLoad;

            if (currentIndex >= allItems.length) {
                loadMoreBtn.hide();
            }
        }

        // Initial load
        if ($('#'+b).val() !== '') {
            showNextItems();
            loadMoreBtn.show();
        }

        // On "Load More" click
        loadMoreBtn.on('click', function () {
            showNextItems();
        });
    }

    ttbm_taxonomy_list_load_more( 'ttbm_attraction_load_more_text', 'ttbm_load_more_attraction_number', 'ttbm_load_top_attractive');
    ttbm_taxonomy_list_load_more( 'ttbm_activities_load_more_text', 'ttbm_load_more_activities_number', 'ttbm_load_activity');
    ttbm_taxonomy_list_load_more( 'ttbm_category_load_more_text', 'ttbm_load_more_category_number', 'ttbm_load_category');
    ttbm_taxonomy_list_load_more( 'ttbm_organizer_load_more_text', 'ttbm_load_more_organizer_number', 'ttbm_load_organizer');
    ttbm_taxonomy_list_load_more( 'ttbm_tag_load_more_text', 'ttbm_load_more_tag_number', 'ttbm_load_tag');
    ttbm_taxonomy_list_load_more( 'ttbm_feature_load_more_text', 'ttbm_load_more_feature_number', 'ttbm_load_feature');




}(jQuery));