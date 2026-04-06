(function ($) {

    function ttbm_promotional_tour_carousal(id, scope = $(document)) {
        let $scope = scope && scope.jquery ? scope : $(scope);
        let targetHolder = $scope.find("#" + id);
        if (!targetHolder.length && $scope.is("#" + id)) {
            targetHolder = $scope;
        }
        if (!targetHolder.length) {
            targetHolder = $("#" + id);
        }
        targetHolder.each(function () {
            let currentHolder = $(this);
            let targetRelatedTour = currentHolder.find(".owl-carousel");
            if (!targetRelatedTour.length) {
                return;
            }
            if (!targetRelatedTour.hasClass('owl-loaded')) {
                let trtNum = parseInt(targetRelatedTour.data('show'), 10) || 1;
                let trtNum600 = Math.min(trtNum - 2, 2);
                trtNum600 = Math.max(trtNum600, 1);
                let trtNum800 = Math.min(trtNum - 1, 3);
                trtNum800 = Math.max(trtNum800, 1);
                targetRelatedTour.owlCarousel({
                    loop: true,
                    margin: 10,
                    nav: true,
                    responsive: {
                        0: {
                            items: 1
                        },
                        600: {
                            items: trtNum600
                        },
                        800: {
                            items: trtNum800
                        },
                        1000: {
                            items: trtNum
                        }
                    }
                });
            }

            currentHolder.find(".next").off('click.ttbmShortcode').on('click.ttbmShortcode', function () {
                currentHolder.find('.owl-next').trigger('click');
            });
            currentHolder.find(".prev").off('click.ttbmShortcode').on('click.ttbmShortcode', function () {
                currentHolder.find('.owl-prev').trigger('click');
            });
        });
    }

    function ttbm_taxonomy_list_load_more(a, b, c, scope = $(document)) {
        let $scope = scope && scope.jquery ? scope : $(scope);
        const loadMoreBtn = $scope.find('#' + a).first().length ? $scope.find('#' + a).first() : $('#' + a).first();
        const itemsPerLoadTarget = $scope.find('#' + b).first().length ? $scope.find('#' + b).first() : $('#' + b).first();
        const allItems = $scope.find('.' + c).length ? $scope.find('.' + c) : $('.' + c);
        if (!loadMoreBtn.length || !itemsPerLoadTarget.length || !allItems.length) {
            return;
        }

        const itemsPerLoad = parseInt(itemsPerLoadTarget.val(), 10) || 3;
        let currentIndex = parseInt(loadMoreBtn.data('ttbmCurrentIndex'), 10) || 0;

        function showNextItems() {
            const nextItems = allItems.slice(currentIndex, currentIndex + itemsPerLoad);
            nextItems.each(function () {
                $(this).fadeIn();
            });

            currentIndex += itemsPerLoad;
            loadMoreBtn.data('ttbmCurrentIndex', currentIndex);

            if (currentIndex >= allItems.length) {
                loadMoreBtn.hide();
            }
        }

        if (!loadMoreBtn.data('ttbmInitDone')) {
            allItems.hide();
            loadMoreBtn.data('ttbmCurrentIndex', 0);
            currentIndex = 0;

            if (itemsPerLoadTarget.val() !== '') {
                showNextItems();
                loadMoreBtn.show();
            }

            loadMoreBtn.off('click.ttbmShortcode').on('click.ttbmShortcode', function () {
                showNextItems();
            });
            loadMoreBtn.data('ttbmInitDone', true);
        }
    }

    window.ttbmShortcodeInit = function (scope) {
        ttbm_promotional_tour_carousal('ttbm_popular_tour', scope);
        ttbm_promotional_tour_carousal('ttbm_feature_tour', scope);
        ttbm_promotional_tour_carousal('ttbm_trending_tour', scope);
        ttbm_promotional_tour_carousal('ttbm_deal-discount_tour', scope);
        ttbm_promotional_tour_carousal('ttbm_top_attraction', scope);
        ttbm_promotional_tour_carousal('ttbm_browse_activities', scope);
        ttbm_promotional_tour_carousal('ttbm_category_shortcode', scope);
        ttbm_promotional_tour_carousal('ttbm_organizer_shortcode', scope);
        ttbm_promotional_tour_carousal('ttbm_tag_shortcode', scope);
        ttbm_promotional_tour_carousal('ttbm_feature_shortcode', scope);

        ttbm_taxonomy_list_load_more('ttbm_attraction_load_more_text', 'ttbm_load_more_attraction_number', 'ttbm_load_top_attractive', scope);
        ttbm_taxonomy_list_load_more('ttbm_activities_load_more_text', 'ttbm_load_more_activities_number', 'ttbm_load_activity', scope);
        ttbm_taxonomy_list_load_more('ttbm_category_load_more_text', 'ttbm_load_more_category_number', 'ttbm_load_category', scope);
        ttbm_taxonomy_list_load_more('ttbm_organizer_load_more_text', 'ttbm_load_more_organizer_number', 'ttbm_load_organizer', scope);
        ttbm_taxonomy_list_load_more('ttbm_tag_load_more_text', 'ttbm_load_more_tag_number', 'ttbm_load_tag', scope);
        ttbm_taxonomy_list_load_more('ttbm_feature_load_more_text', 'ttbm_load_more_feature_number', 'ttbm_load_feature', scope);
    };

    $(document).ready(function () {
        window.ttbmShortcodeInit($(document));
    });

}(jQuery));
