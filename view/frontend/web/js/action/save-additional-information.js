define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'GoMage_LightCheckout/js/model/resource-url-manager',
        'underscore'
    ],
    function (
        $,
        quote,
        storage,
        errorProcessor,
        fullScreenLoader,
        resourceUrlManager,
        _
    ) {
        'use strict';

        return function () {
            var serviceUrl = resourceUrlManager.getUrlForSaveAdditionalInformation(),
                selectors = {
                    password: '#account-password',
                    accountCheckbox: 'input[name=create-account-checkbox]',
                    passwordForLoginForm: '.form-login #customer-email-fieldset #customer-password',
                    customerEmail: '.form-login #customer-email-fieldset #customer-email',
                    deliveryDate: '#delivery-date input',
                    deliveryTime: '#delivery-date select option:selected',
                    subscribeToNewsletter: '#opc-sidebar #subscribe-newsletter input[type=checkbox]'
                },
                passwordVal = $(selectors.password).val(),
                isAccountCheckboxChecked = $(selectors.accountCheckbox).is(":checked"),
                deliveryDateVal = $(selectors.deliveryDate).val(),
                deliveryTimeVal = $(selectors.deliveryTime).text(),
                isSubscribeToNewsletterCheckboxVisible = $(selectors.subscribeToNewsletter).is(":visible"),
                isSubscribeToNewsletterCheckboxChecked = $(selectors.subscribeToNewsletter).is(":checked"),
                payload = {
                    additionInformation: {}
                };

            if (isAccountCheckboxChecked) {
                payload.additionInformation.password = passwordVal;
            }

            if (isSubscribeToNewsletterCheckboxVisible && isSubscribeToNewsletterCheckboxChecked) {
                payload.additionInformation.subscribe = isSubscribeToNewsletterCheckboxChecked;
                payload.additionInformation.customerEmail = $(selectors.customerEmail).val();
            }

            if (deliveryDateVal) {
                payload.additionInformation.deliveryDate = deliveryDateVal;
                payload.additionInformation.deliveryDateTime = deliveryTimeVal;
            }

            if (_.isEmpty(payload.additionInformation)) {
                return;
            }

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(function () {
                fullScreenLoader.stopLoader();
            });
        };
    }
);
