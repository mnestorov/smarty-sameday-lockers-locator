(function ($) {
	'use strict';

    /**
	 * All of the code for plugin public JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed we will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables us to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 */

    function toggleFieldsBasedOnShipping() {
        const isLockerSelected = $('#carrier_sameday_locker').is(':checked');
        const lockerField = $('#sameday_locker');
        const lockerFieldRow = lockerField.closest('.form-row');

        const billingFieldsToToggle = [
            '#billing_address_1_field',
            '#billing_postcode_field',
            '#billing_city_field',
            '#billing_state_field'
        ];

        if (isLockerSelected) {
            lockerFieldRow.show().find('label').html(
                `${sameday_params.selectSamedayLockerFieldTitle} <span style="color: #E01020;">*</span>`
            );
            lockerField.prop('required', true).addClass('error-field');

            billingFieldsToToggle.forEach(selector => {
                $(selector).hide();
            });
        } else {
            lockerFieldRow.hide();
            lockerField.prop('required', false).removeClass('error-field');
            $(`#${lockerField.attr('id')}-error`).remove();

            billingFieldsToToggle.forEach(selector => {
                $(selector).show();
            });
        }
    }

    function updateRadioSelectionUI() {
        $('.radio-wrap').removeClass('selected');
        $('input[name="carrier_sameday"]:checked').closest('.radio-wrap').addClass('selected');
    }

    function updateSamedaySelection(value) {
        $.post(wc_checkout_params.ajax_url, {
            action: 'update_shipping_method',
            shipping_method: value
        }, function (response) {
            console.log('Shipping method updated:', response);
        });
    }

    $(document).ready(function () {
        toggleFieldsBasedOnShipping();
        updateRadioSelectionUI();
        $('#sameday_locker').select2();

        // Default fallback
        const locker = $('#carrier_sameday_locker');
        const address = $('#carrier_sameday_address');
        if (!locker.is(':checked') && !address.is(':checked')) {
            address.prop('checked', true);
            updateSamedaySelection(address.val());
            toggleFieldsBasedOnShipping();
        }
    });

    $('input[name="carrier_sameday"]').on('change', function () {
        updateRadioSelectionUI();
        toggleFieldsBasedOnShipping();
        updateSamedaySelection($(this).val());
    });

    $('form.checkout').on('submit', function (e) {
        const locker = $('#carrier_sameday_locker');
        const lockerField = $('#sameday_locker');

        if (locker.is(':checked') && lockerField.prop('required') && lockerField.val() === '') {
            lockerField.addClass('error-field');
            const errorId = lockerField.attr('id') + '-error';
            $(`#${errorId}`).remove();
            lockerField.closest('.woocommerce-input-wrapper').after(
                `<div id="${errorId}" class="error-message"><b>${sameday_params.selectSamedayLockerMessage}</b></div>`
            );
            e.preventDefault();
        }
    });

    $('#sameday_locker').on('change', function () {
        $(this).removeClass('error-field');
        $(`#${$(this).attr('id')}-error`).remove();
    });
    
    $(document).on('click', '.radio-wrap', function (e) {
        const radio = $(this).find('input[type="radio"]');

        if (!$(e.target).is('input[type="radio"], label, img')) {
            radio.prop('checked', true).trigger('change');
        }

        updateRadioSelectionUI();
        toggleFieldsBasedOnShipping();
        updateSamedaySelection(radio.val());
    });
})(jQuery);