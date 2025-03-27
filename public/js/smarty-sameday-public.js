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
        let samedayLockerField = $('#sameday_locker');
        let samedayLockerFieldRow = samedayLockerField.closest('.form-row');
        let billingCityField = $('#billing_city_field');
        let billingPostCode = $('#billing_postcode_field');
        let billingAddressField = $('#billing_address_1_field');
    
        let hideSamedayLocker = sameday_params.hide_sameday_locker === 'yes';
        let samedaySelected = $('#carrier_sameday_locker').is(':checked');
    
        if (samedaySelected) {
            // Show locker field and init select2
            if (!hideSamedayLocker) {
                samedayLockerFieldRow.show();
                if (!samedayLockerField.hasClass('select2-hidden-accessible')) {
                    samedayLockerField.select2();
                }
            }
            billingCityField.hide();
            billingPostCode.hide();
            billingAddressField.hide();
    
            samedayLockerField.attr('required', 'required').addClass('error-field');
            samedayLockerFieldRow.find('label').html(`${sameday_params.selectSamedayLockerFieldTitle} <span style="color: #E01020;">*</span>`);
        } else {
            // Hide locker if Sameday not selected
            samedayLockerFieldRow.hide();
            samedayLockerField.removeAttr('required').removeClass('error-field');
            $('#' + samedayLockerField.attr('id') + '-error').remove();
        }
    }    

    // Function to display an error message below the select field
    function showSamedayErrorMessage(field, message) {
        let errorMessageId = field.attr('id') + '-error';
        let existingMessage = $('#' + errorMessageId);

        if (existingMessage.length > 0) {
            existingMessage.remove();
        }

        field.closest('.woocommerce-input-wrapper').after('<div id="' + errorMessageId + '" class="error-message"><b>' + message + '</b></div>');
    }

    // Function to update the border color for selected radio button
    function updateSamedayRadioWrapBorder() {
        $('.radio-wrap').each(function () {
            if ($(this).find('input[type="radio"]').is(':checked')) {
                $(this).addClass('selected');
            } else {
                $(this).removeClass('selected');
            }
        });
    };

    // Function to handle change event on radio buttons
    function toggleSamedayLockerField() {
        setSamedayFieldVisibility();
        // Add code to deselect Econt radio buttons
        $('input[name="carrier_econt"]').prop('checked', false);
        updateSamedayRadioWrapBorder();
    }

    // Function to extract city name from the selected Sameday Locker and set it to the hidden billing_city field
	function updateHiddenBillingCityField() {
        // Don’t try to extract city anymore
        let selectedLocker = $('#sameday_locker').val();
    
        if (selectedLocker) {
            $('#billing_city').val(selectedLocker); // set it just so form doesn't error out
        }
    }

    // Event handler for radio button change
    $('input[name="carrier_sameday"]').change(function () {
        toggleSamedayLockerField();
        updateSamedayRadioWrapBorder();
    })

    // Make entire radio-wrap clickable and maintain hover state
    $('.radio-wrap').click(function () {
        // Remove selected class from all radio-wraps
        $('.radio-wrap').removeClass('selected');
        // Add selected class to the clicked radio-wrap
        $(this).addClass('selected');
        // Check the radio button inside the clicked radio-wrap
        $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
    });

    // Form submit event
    $('form.checkout').on('submit', function (e) {
        const samedayLockerField = $('#sameday_locker');
        if (samedayLockerField.is(':visible') && samedayLockerField.prop('required') && samedayLockerField.val() === '') {
            samedayLockerField.addClass('error-field');
            showSamedayErrorMessage(samedayLockerField, sameday_params.selectSamedayLockerMessage);
            e.preventDefault(); // Prevent form submission
        }
    });

    // Remove the error message when an option is selected in the Sameday locker field
    $('#sameday_locker').on('change', function () {
        updateHiddenBillingCityField(); // Update the hidden billing_city field when Sameday Locker changes
        $(this).removeClass('error-field');
        $('#' + $(this).attr('id') + '-error').remove(); // Remove the error message
    });

    // Handle AJAX request to update the shipping method in session
    $('input[name="carrier_sameday"]').change(function () {
        var shippingMethod = $(this).val();
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'update_shipping_method',
                shipping_method: shippingMethod
            },
            //success: function (response) {
            //	console.log(response);
            //}
        });
    });

    $(document).ready(function() {
        setTimeout(function () {
         	setSamedayFieldVisibility();
         	updateSamedayRadioWrapBorder();
         	$('#sameday_locker').select2();
            updateHiddenBillingCityField();
		}, 50);
    });     
})(jQuery);