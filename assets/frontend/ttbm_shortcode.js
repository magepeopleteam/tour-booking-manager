(function ($) {

    function ttbm_promotional_tour_carousal( id ){
        let target_related_tour = $("#"+id+" .owl-carousel");
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

    const loadMoreBtn = $('#ttbm_attraction_load_more_text');
    const itemsPerLoad = parseInt($('#ttbm_load_more_attraction_number').val()) || 3;
    const allItems = $('.ttbm_load_top_attractive');
    let currentIndex = 0;

    // Hide all items initially
    // allItems.hide();

    function showNextItems() {
        const nextItems = allItems.slice(currentIndex, currentIndex + itemsPerLoad);
        nextItems.each(function() {
            const itemId = $(this).attr('id');
            $('#' + itemId).fadeIn();
        });

        currentIndex += itemsPerLoad;

        if (currentIndex >= allItems.length) {
            loadMoreBtn.hide();
        }
    }

    // Initial load
    if ( $('#ttbm_load_more_attraction_number').val() !== '') {
        showNextItems();
        loadMoreBtn.show();
    }

    // On "Load More" click
    loadMoreBtn.on('click', function() {
        showNextItems();
    });



}(jQuery));