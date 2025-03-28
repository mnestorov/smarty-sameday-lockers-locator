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

    function setSamedayFieldVisibility() {
        const lockerCheckbox = $('#carrier_sameday_locker');
        const addressCheckbox = $('#carrier_sameday_address');

        const lockerSelected = lockerCheckbox.is(':checked');
        const addressSelected = addressCheckbox.is(':checked');

        const lockerField = $('#sameday_locker');
        const lockerFieldRow = lockerField.closest('.form-row');

        const billingCityField = $('#billing_city_field');
        const billingPostCode = $('#billing_postcode_field');
        const billingAddressField = $('#billing_address_1_field');
        const billingStateField = $('#billing_state_field');

        const hideSamedayLocker = sameday_params.hide_sameday_locker === 'yes';

        if (lockerSelected) {
            if (!hideSamedayLocker) {
                lockerFieldRow.show();
                if (!lockerField.hasClass('select2-hidden-accessible')) {
                    lockerField.select2();
                }
            }

            billingCityField.hide();
            billingPostCode.hide();
            billingAddressField.hide();
            billingStateField.hide();

            lockerField.attr('required', 'required').addClass('error-field');
            lockerFieldRow.find('label').html(`${sameday_params.selectSamedayLockerFieldTitle} <span style="color: #E01020;">*</span>`);
        } else {
            lockerFieldRow.hide();
            lockerField.removeAttr('required').removeClass('error-field');
            $('#' + lockerField.attr('id') + '-error').remove();
        }

        if (addressSelected) {
            billingCityField.show();
            billingPostCode.show();
            billingAddressField.show();
            billingStateField.show();
        } else if (!lockerSelected) {
            // if none are selected, reset everything
            billingCityField.show();
            billingPostCode.show();
            billingAddressField.show();
            billingStateField.show();
        }
    }

    function showSamedayErrorMessage(field, message) {
        let errorMessageId = field.attr('id') + '-error';
        $('#' + errorMessageId).remove();
        field.closest('.woocommerce-input-wrapper').after('<div id="' + errorMessageId + '" class="error-message"><b>' + message + '</b></div>');
    }

    function updateSamedayRadioWrapBorder() {
        $('.radio-wrap').each(function () {
            if ($(this).find('input[type="checkbox"]').is(':checked')) {
                $(this).addClass('selected');
            } else {
                $(this).removeClass('selected');
            }
        });
    }

    function toggleSamedayLockerField() {
        setSamedayFieldVisibility();
        updateSamedayRadioWrapBorder();
    }

    function updateHiddenBillingCityField() {
        const selectedLocker = $('#sameday_locker').val();
        if (selectedLocker) {
            $('#billing_city').val(selectedLocker);
        }
    }

    // Handle changing shipping checkboxes
    $('input[name="carrier_sameday"]').change(function () {
        toggleSamedayLockerField();
        updateSamedayRadioWrapBorder();

        const selectedValue = $(this).val();
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'update_shipping_method',
                shipping_method: selectedValue
            }
        });
    });

    // Make .radio-wrap divs clickable
    $('.radio-wrap').click(function (e) {
        e.preventDefault();
        const checkbox = $(this).find('input[type="checkbox"]');
        const isChecked = checkbox.prop('checked');

        $('input[name="carrier_sameday"]').prop('checked', false); // only one at a time
        $('.radio-wrap').removeClass('selected');

        if (!isChecked) {
            checkbox.prop('checked', true).trigger('change');
            $(this).addClass('selected');
        }
    });

    // On form submit
    $('form.checkout').on('submit', function (e) {
        const lockerField = $('#sameday_locker');
        const lockerSelected = $('#carrier_sameday_locker').is(':checked');

        if (lockerSelected && lockerField.prop('required') && lockerField.val() === '') {
            lockerField.addClass('error-field');
            showSamedayErrorMessage(lockerField, sameday_params.selectSamedayLockerMessage);
            e.preventDefault();
        }
    });

    $('#sameday_locker').on('change', function () {
        updateHiddenBillingCityField();
        $(this).removeClass('error-field');
        $('#' + $(this).attr('id') + '-error').remove();
    });

    $(document).ready(function () {
        setTimeout(function () {
            setSamedayFieldVisibility();
            updateSamedayRadioWrapBorder();
            $('#sameday_locker').select2();
            updateHiddenBillingCityField();
    
            // Default to "To Address" if neither is selected
            const lockerInput = $('#carrier_sameday_locker');
            const addressInput = $('#carrier_sameday_address');

            if (!lockerInput.is(':checked') && !addressInput.is(':checked')) {
                addressInput.prop('checked', true).trigger('change');
                $('.radio-wrap').removeClass('selected');
                addressInput.closest('.radio-wrap').addClass('selected');

                // Also update the session in WooCommerce
                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.ajax_url,
                    data: {
                        action: 'update_shipping_method',
                        shipping_method: addressInput.val()
                    }
                });
            }
        }, 50);
    });    
})(jQuery);