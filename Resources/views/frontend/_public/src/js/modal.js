(function ($, window) {
    'use strict';

    $.plugin('magediaAddPasswordModal', {
        init: function () {
            var me = this;

            me.applyDataAttributes(true);

            me._on(me.$el, 'click', $.proxy(me.onClick, me));

            $.publish('plugin/magediaAddPasswordModal/onRegisterEvents', [ me ]);
        },

        /**
         * Handle click event and delegate to the open() method
         *
         * @param event
         */
        onClick: function(event) {
            var me = this;

            event.preventDefault();


            $.publish('plugin/magediaAddPasswordModal/onBeforeClick', [ me ]);

            if ($('#confirm--form')[0][1].reportValidity()) {
               me.open()
            }

            $.publish('plugin/magediaAddPasswordModal/onAfterClick', [ me ]);
        },

        open: function() {
            var me = this,
                sizing = me.opts.sizing,
                maxHeight = 0,
                requestData = {
                    extraData: {
                        sessionKey: me.opts.sessionKey,
                    }
                };

            if (window.StateManager._getCurrentDevice() === 'mobile') {
                sizing = 'auto';
            } else {
                maxHeight = me.opts.height;
            }

            // reset modal
            $.modal.close();
            $.loadingIndicator.open();

            $.publish('plugin/magediaAddPasswordModal/onBeforeOpen', [ me, requestData ]);

            // Ajax request to fetch available addresses
            $.ajax({
                'url': window.controller['ajax_add_password'],
                'data': requestData,
                'success': function(data) {
                    $.loadingIndicator.close(function() {
                        $.subscribe(me.getEventName('plugin/swModal/onOpen'), $.proxy(me._onSetContent, me));

                        $.modal.open(data, {
                            width: 600,
                            height: 500,
                            maxHeight: maxHeight,
                            sizing: sizing,
                            additionalClass: 'address-manager--modal address-manager--editor',
                        });

                        $.unsubscribe(me.getEventName('plugin/swModal/onOpen'));
                    });

                }
            });

            $.publish('plugin/magediaAddPasswordModal/onAfterOpen', [ me ]);
        },

        /**
         * Callback from $.modal setContent method
         *
         * @param event
         * @param $modal
         * @private
         */
        _onSetContent: function(event, $modal) {
            var me = this;

            me._registerPlugins();
            me._bindButtonAction($modal);
        },

        /**
         * Re-register plugins to enable them in the modal
         * @private
         */
        _registerPlugins: function() {
            window.StateManager
                .addPlugin('div[data-register="true"]', 'swRegister');

            $.publish('plugin/magediaAddPasswordModal/onRegisterPlugins', [ this ]);
        },

        /**
         * Registers listeners for the click event on the "change address" buttons. The buttons contain the
         * needed data for the address selection. It then sends an ajax post request to the form
         * action
         *
         * @param $modal
         * @private
         */
        _bindButtonAction: function($modal) {
            var me = this,
                $submitButtons = $modal._$content.find(me.opts.submitButtonSelector),
                $actionInput = $modal._$content.find('input[name=saveAction]');

            $.publish('plugin/magediaAddPasswordModal/onBeforeBindButtonAction', [ me, $modal ]);

            // hook into submit button click to eventually update the saveAction value bound to data-value
            $submitButtons.on('click', function(event) {
                var $elem = $(this);

                event.preventDefault();

                $actionInput.val($elem.attr('data-value'));
                $elem.closest('form').submit();
            });

            // submit form via ajax
            $modal._$content
                .find('form')
                .on('submit', function(event) {
                    var $target = $(event.target),
                        actionData = {};

                    me._resetErrorMessage($modal);
                    me._disableSubmitButtons($modal);

                    event.preventDefault();

                    $.each($target.serializeArray(), function() {
                        actionData[this.name] = this.value;
                    });

                    $.publish('plugin/magediaAddPasswordModal/onBeforeSave', [ me, actionData ]);

                    // send data to api endpoint
                    $.ajax({
                        url: $target.attr('action'),
                        data: actionData,
                        method: 'POST',
                        success: function(response) {
                            if (response.success) {
                                $('#confirm--form').submit();
                            }
                        }
                    });
                });

            $.publish('plugin/magediaAddPasswordModal/onAfterBindButtonAction', [ me, $modal ]);
        },

        /**
         * Hide error container in popup
         *
         * @param $modal
         * @private
         */
        _resetErrorMessage: function($modal) {
            $modal._$content.find('.address-editor--errors').addClass('is--hidden');
        },

        /**
         * Disable submit buttons to prevent multiple submissions
         *
         * @param $modal
         * @private
         */
        _disableSubmitButtons: function($modal) {
            var me = this;
            $modal._$content.find(me.opts.submitButtonSelector).attr('disabled', 'disabled');
        },
    });

    window.StateManager.addPlugin('#confirm-order', 'magediaAddPasswordModal')

})(jQuery, window);