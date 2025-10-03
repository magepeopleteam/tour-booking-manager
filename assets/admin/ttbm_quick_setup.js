/**
 * Tour Booking Manager - Quick Setup Wizard
 */
(function($) {
    'use strict';

    let currentStep = 1;
    const totalSteps = 3;

    $(document).ready(function() {
        initializeSetup();
        bindEvents();
    });

    /**
     * Initialize setup wizard
     */
    function initializeSetup() {
        // If WooCommerce is already active, skip to step 2
        if (ttbmQuickSetup.woo_status == 1) {
            currentStep = 2;
        }
        
        // Always update UI on load
        updateStepUI();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Next button
        $('#ttbm-next-step').on('click', function() {
            nextStep();
        });

        // Previous button
        $('#ttbm-prev-step').on('click', function() {
            prevStep();
        });

        // Install WooCommerce
        $('#ttbm-install-woo').on('click', function() {
            installWooCommerce();
        });

        // Activate WooCommerce
        $('#ttbm-activate-woo').on('click', function() {
            activateWooCommerce();
        });

        // Labels & Slugs form
        $('#ttbm-labels-slugs-form').on('submit', function(e) {
            e.preventDefault();
            saveLabelsAndSlugs();
        });

        // Finish setup
        $('#ttbm-finish-setup').on('click', function() {
            finishSetup();
        });
    }

    /**
     * Navigate to next step
     */
    function nextStep() {
        // If already on final step, trigger finish instead
        if (currentStep === totalSteps) {
            finishSetup();
            return;
        }

        // Validate current step before proceeding
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep < totalSteps) {
            currentStep++;
            updateStepUI();
        }
    }

    /**
     * Navigate to previous step
     */
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepUI();
        }
    }

    /**
     * Update step UI
     */
    function updateStepUI() {
        // Update progress steps
        $('.ttbm-step').removeClass('active completed');
        $('.ttbm-step').each(function() {
            const stepNum = $(this).data('step');
            if (stepNum < currentStep) {
                $(this).addClass('completed');
            } else if (stepNum === currentStep) {
                $(this).addClass('active');
            }
        });

        // Update content
        $('.ttbm-step-content').removeClass('active');
        $('.ttbm-step-content[data-step="' + currentStep + '"]').addClass('active');

        // Update navigation buttons
        if (currentStep === 1) {
            $('#ttbm-prev-step').hide();
        } else if (currentStep === totalSteps) {
            // Hide both buttons on final step
            $('#ttbm-prev-step').hide();
        } else {
            $('#ttbm-prev-step').show();
        }

        if (currentStep === totalSteps) {
            $('#ttbm-next-step').hide();
        } else {
            $('#ttbm-next-step').show();
        }

        // Scroll to top
        $('.ttbm-quick-setup-wrapper').animate({ scrollTop: 0 }, 300);
    }

    /**
     * Validate current step
     */
    function validateStep(step) {
        if (step === 1) {
            // Check if WooCommerce is active
            if (ttbmQuickSetup.woo_status != 1) {
                showNotice('Please install and activate WooCommerce to continue.', 'error');
                return false;
            }
        }
        return true;
    }

    /**
     * Install WooCommerce
     */
    function installWooCommerce() {
        const $button = $('#ttbm-install-woo');
        const $loading = $('.ttbm-step-content[data-step="1"] .ttbm-loading');
        
        $button.prop('disabled', true);
        $loading.show();
        $loading.find('.ttbm-loading-text').text(ttbmQuickSetup.texts.installing);

        $.ajax({
            url: ttbmQuickSetup.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_install_woocommerce',
                nonce: ttbmQuickSetup.nonce
            },
            success: function(response) {
                $loading.hide();
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    ttbmQuickSetup.woo_status = 1;
                    
                    // Update UI to show WooCommerce is installed
                    $('.ttbm-step-content[data-step="1"]').html(
                        '<div class="ttbm-status-box success">' +
                        '<span class="dashicons dashicons-yes-alt"></span>' +
                        '<p>WooCommerce is now installed and activated!</p>' +
                        '</div>'
                    );
                    
                    // Mark step as completed
                    $('.ttbm-step[data-step="1"]').removeClass('active').addClass('completed');
                    
                    // Auto-proceed to next step after 1 second
                    setTimeout(function() {
                        nextStep();
                    }, 1000);
                } else {
                    showNotice(response.data.message || ttbmQuickSetup.texts.error, 'error');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                $loading.hide();
                showNotice(ttbmQuickSetup.texts.error, 'error');
                $button.prop('disabled', false);
            }
        });
    }

    /**
     * Activate WooCommerce
     */
    function activateWooCommerce() {
        const $button = $('#ttbm-activate-woo');
        const $loading = $('.ttbm-step-content[data-step="1"] .ttbm-loading');
        
        $button.prop('disabled', true);
        $loading.show();
        $loading.find('.ttbm-loading-text').text(ttbmQuickSetup.texts.activating);

        $.ajax({
            url: ttbmQuickSetup.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_activate_woocommerce',
                nonce: ttbmQuickSetup.nonce
            },
            success: function(response) {
                $loading.hide();
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    ttbmQuickSetup.woo_status = 1;
                    
                    // Update UI
                    $('.ttbm-step-content[data-step="1"]').html(
                        '<div class="ttbm-status-box success">' +
                        '<span class="dashicons dashicons-yes-alt"></span>' +
                        '<p>WooCommerce is now activated!</p>' +
                        '</div>'
                    );
                    
                    // Mark step as completed
                    $('.ttbm-step[data-step="1"]').removeClass('active').addClass('completed');
                    
                    // Auto-proceed to next step
                    setTimeout(function() {
                        nextStep();
                    }, 1000);
                } else {
                    showNotice(response.data.message || ttbmQuickSetup.texts.error, 'error');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                $loading.hide();
                showNotice(ttbmQuickSetup.texts.error, 'error');
                $button.prop('disabled', false);
            }
        });
    }

    /**
     * Save labels and slugs
     */
    function saveLabelsAndSlugs() {
        const tourLabel = $('#tour_label').val().trim();
        const tourSlug = $('#tour_slug').val().trim();

        if (!tourLabel || !tourSlug) {
            showNotice('Please fill in all fields.', 'error');
            return;
        }

        const $button = $('#ttbm-labels-slugs-form button[type="submit"]');
        $button.prop('disabled', true).text('Saving...');

        $.ajax({
            url: ttbmQuickSetup.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_save_labels_slugs',
                nonce: ttbmQuickSetup.nonce,
                tour_label: tourLabel,
                tour_slug: tourSlug
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Mark step as completed
                    $('.ttbm-step[data-step="2"]').removeClass('active').addClass('completed');
                    
                    // Auto-proceed to next step
                    setTimeout(function() {
                        nextStep();
                    }, 1000);
                } else {
                    showNotice(response.data.message || ttbmQuickSetup.texts.error, 'error');
                    $button.prop('disabled', false).text('Save & Continue');
                }
            },
            error: function() {
                showNotice(ttbmQuickSetup.texts.error, 'error');
                $button.prop('disabled', false).text('Save & Continue');
            }
        });
    }

    /**
     * Finish setup
     */
    function finishSetup() {
        const $button = $('#ttbm-finish-setup');
        $button.prop('disabled', true).text('Completing...');

        $.ajax({
            url: ttbmQuickSetup.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_finish_setup',
                nonce: ttbmQuickSetup.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Redirect to dashboard
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                } else {
                    showNotice(response.data.message || ttbmQuickSetup.texts.error, 'error');
                    $button.prop('disabled', false).text('Go to Dashboard');
                }
            },
            error: function() {
                showNotice(ttbmQuickSetup.texts.error, 'error');
                $button.prop('disabled', false).text('Go to Dashboard');
            }
        });
    }

    /**
     * Show notice message
     */
    function showNotice(message, type) {
        // Remove existing notices
        $('.ttbm-setup-notice').remove();

        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $('<div class="notice ttbm-setup-notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.ttbm-quick-setup-container').prepend(notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

})(jQuery);

