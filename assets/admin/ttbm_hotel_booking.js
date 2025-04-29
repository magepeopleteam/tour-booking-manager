(function ($) {

    $(document).on("click", ".ttbm_total_booking_view_more", function (e) {
        e.preventDefault();
        $('.ttbm_booking_user_more_info').fadeOut();
        $(this).siblings('.ttbm_booking_user_more_info').toggle();
    });

})(jQuery);