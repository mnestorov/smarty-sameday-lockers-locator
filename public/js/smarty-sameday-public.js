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
        const lockerField = $('#sameday_locker');
        const lockerFieldRow = lockerField.closest('.form-row');
        const billingCityField = $('#billing_city_field');
        const billingPostCode = $('#billing_postcode_field');
        const billingAddressField = $('#billing_address_1_field');
        const billingStateField = $('#billing_state_field');
        const hideSamedayLocker = sameday_params.hide_sameday_locker === 'yes';

        const lockerSelected = lockerCheckbox.is(':checked');
        const addressSelected = addressCheckbox.is(':checked');

        console.log('setSamedayFieldVisibility - Locker Selected:', lockerSelected, 'Address Selected:', addressSelected);

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

            lockerField.prop('required', true).addClass('error-field');
            lockerFieldRow.find('label').html(`${sameday_params.selectSamedayLockerFieldTitle} <span style="color: #E01020;">*</span>`);
        } else {
            lockerFieldRow.hide();
            lockerField.prop('required', false).removeClass('error-field');
            $('#' + lockerField.attr('id') + '-error').remove();

            billingCityField.show();
            billingPostCode.show();
            billingAddressField.show();
            billingStateField.show();
        }

        // Explicitly ensure locker is not required when address is selected
        if (addressSelected) {
            lockerField.prop('required', false).removeClass('error-field');
            $('#' + lockerField.attr('id') + '-error').remove();
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

    $('input[name="carrier_sameday"]').change(function () {
        const selectedValue = $(this).val();
        console.log('Checkbox changed to:', selectedValue);

        toggleSamedayLockerField();
        updateSamedayRadioWrapBorder();

        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'update_shipping_method',
                shipping_method: selectedValue
            },
            success: function (response) {
                console.log('AJAX success:', response);
            },
            error: function (xhr, status, error) {
                console.log('AJAX error:', error);
            }
        });
    });

    $('.radio-wrap').click(function (e) {
        e.preventDefault();
        const checkbox = $(this).find('input[type="checkbox"]');
        const isChecked = checkbox.prop('checked');

        console.log('Radio wrap clicked, checkbox value:', checkbox.val(), 'isChecked:', isChecked);

        $('input[name="carrier_sameday"]').prop('checked', false);
        $('.radio-wrap').removeClass('selected');

        checkbox.prop('checked', true);
        $(this).addClass('selected');

        // Send correct selected value to server
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'update_shipping_method',
                shipping_method: checkbox.val()
            },
            success: function (response) {
                console.log('AJAX success:', response);
                setSamedayFieldVisibility();
            },
            error: function (xhr, status, error) {
                console.log('AJAX error:', error);
            }
        });
    });

    $('form.checkout').on('submit', function (e) {
        const lockerField = $('#sameday_locker');
        const lockerSelected = $('#carrier_sameday_locker').is(':checked');
        const addressSelected = $('#carrier_sameday_address').is(':checked');

        console.log('Form submit - Locker Selected:', lockerSelected, 'Address Selected:', addressSelected, 'Locker Value:', lockerField.val());

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

            const lockerInput = $('#carrier_sameday_locker');
            const addressInput = $('#carrier_sameday_address');

            if (!lockerInput.is(':checked') && !addressInput.is(':checked')) {
                console.log('Neither selected, defaulting to To Address');
                addressInput.prop('checked', true).trigger('change');
                $('.radio-wrap').removeClass('selected');
                addressInput.closest('.radio-wrap').addClass('selected');

                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.ajax_url,
                    data: {
                        action: 'update_shipping_method',
                        shipping_method: addressInput.val()
                    },
                    success: function (response) {
                        console.log('Default AJAX success:', response);
                    },
                    error: function (xhr, status, error) {
                        console.log('Default AJAX error:', error);
                    }
                });
            }
        }, 50);
    });
})(jQuery);