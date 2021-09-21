var frame,
    tawcvs = tawcvs || {};

jQuery(document).ready(function ($) {
    'use strict';
    var wp = window.wp,
        $body = $('body');

    $('#term-color,#term-secondary-color').wpColorPicker();

    // Update attribute image
    $body.on('click', '.tawcvs-upload-image-button', function (event) {
        event.preventDefault();

        var $button = $(this);

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create the media frame.
        frame = wp.media.frames.downloadable_file = wp.media({
            title: tawcvs.i18n.mediaTitle,
            button: {
                text: tawcvs.i18n.mediaButton
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();

            $button.siblings('input.tawcvs-term-image').val(attachment.id);
            $button.siblings('.tawcvs-remove-image-button').show();
            $button.parent().prev('.tawcvs-term-image-thumbnail').find('img').attr('src', attachment.sizes.thumbnail.url);
        });

        // Finally, open the modal.
        frame.open();

    }).on('click', '.tawcvs-remove-image-button', function () {
        var $button = $(this);

        $button.siblings('input.tawcvs-term-image').val('');
        $button.siblings('.tawcvs-remove-image-button').show();
        $button.parent().prev('.tawcvs-term-image-thumbnail').find('img').attr('src', tawcvs.placeholder);

        return false;
    });

    // Toggle add new attribute term modal
    var $modal = $('#tawcvs-modal-container'),
        $spinner = $modal.find('.spinner'),
        $msg = $modal.find('.message'),
        $metabox = null;

    $body.on('click', '.tawcvs_add_new_attribute', function (e) {
        e.preventDefault();
        var $button = $(this),
            taxInputTemplate = wp.template('tawcvs-input-tax'),
            data = {
                type: $button.data('type'),
                tax: $button.closest('.woocommerce_attribute').data('taxonomy')
            };

        // Insert input
        $modal.find('.tawcvs-term-swatch').html($('#tmpl-tawcvs-input-' + data.type).html());
        $modal.find('.tawcvs-term-tax').html(taxInputTemplate(data));

        if ('color' === data.type) {
            $modal.find('input.tawcvs-input-color').wpColorPicker();
        }

        $metabox = $button.closest('.woocommerce_attribute.wc-metabox');
        $modal.show();
    }).on('click', '.tawcvs-modal-close, .tawcvs-modal-backdrop', function (e) {
        e.preventDefault();

        closeModal();
    });

    // Send ajax request to add new attribute term
    $body.on('click', '.tawcvs-new-attribute-submit', function (e) {
        e.preventDefault();

        var $button = $(this),
            type = $button.data('type'),
            error = false,
            data = {};

        // Validate
        $modal.find('.tawcvs-input').each(function () {
            var $this = $(this);

            if ($this.attr('name') !== 'slug' && !$this.val()) {
                $this.addClass('error');
                error = true;
            } else {
                $this.removeClass('error');
            }

            data[$this.attr('name')] = $this.val();
        });

        if (error) {
            return;
        }

        // Send ajax request
        $spinner.addClass('is-active');
        $msg.hide();
        wp.ajax.send('tawcvs_add_new_attribute', {
            data: data,
            error: function (res) {
                $spinner.removeClass('is-active');
                $msg.addClass('error').text(res).show();
            },
            success: function (res) {
                $spinner.removeClass('is-active');
                $msg.addClass('success').text(res.msg).show();

                $metabox.find('select.attribute_values').append('<option value="' + res.id + '" selected="selected">' + res.name + '</option>');
                $metabox.find('select.attribute_values').change();

                closeModal();
            }
        });


    });

    /**
     * Close modal
     */
    function closeModal() {
        $modal.find('.tawcvs-term-name input, .tawcvs-term-slug input').val('');
        $spinner.removeClass('is-active');
        $msg.removeClass('error success').hide();
        $modal.hide();
    }

    // accordion js code


    //track changed settings
    $('#tawcvs-settings-wrap :input').each(function (key) {
        $(this).change(function () {
            $(this).addClass('dirty');
        });
    });

    //Ajax save settings
    $body.on('click', '#tawcvs_save_settings', function (e) {
        e.preventDefault();

        const data = JSON.parse(getAllValues());
        let savingNoticeEle = $('.wcvs-saving-notice');

        $('.wcvs-notice').hide();
        savingNoticeEle.fadeIn();

        wp.ajax.send('tawcvs_save_settings', {
            data: data,
            error: function (res) {
                savingNoticeEle.hide();
                $('.wcvs-failed-notice').fadeIn().delay(5000).fadeOut();
            },
            success: function (res) {
                savingNoticeEle.hide();
                $('.wcvs-saved-notice').fadeIn().delay(5000).fadeOut();
            }
        });
    });

    function getAllValues() {
        var inputValues = '{';
        $('#tawcvs-settings-wrap :input').map(function () {
            var type = $(this).prop("type");

            // checked radios/checkboxes
            if (type === "checkbox") {
                if (this.checked) {
                    inputValues = inputValues.concat('"' + $(this).attr('name') + '":' + '"1",');
                } else {
                    inputValues = inputValues.concat('"' + $(this).attr('name') + '":' + '"0",');
                }
            } else if (type === "radio") {
                if (this.checked) {
                    inputValues = inputValues.concat('"' + $(this).attr('name') + '":' + '"' + $(this).val() + '",');
                }
            }
            // all other fields, except buttons
            else if (type !== "button" && type !== "submit" && type !== "hidden") {
                inputValues = inputValues.concat('"' + $(this).attr('name') + '":' + '"' + $(this).val() + '",');
            }
        })
        return inputValues.slice(0, -1).concat('}');

    }


});


jQuery(document).ready(function ($) {
    // accordion js
    $('.variation-item-head').on('click', function () {
        var $clickedHead = $(this);
        $('.variation-item-head').each(function () {
            if ($(this).is($clickedHead)) {
                // Do nothing
            } else {
                $(this).removeClass('active-accordion');
                $(this).next().slideUp();
            }
        });
        $clickedHead.next().slideToggle();
        $clickedHead.toggleClass('active-accordion');

    });

    // accordion tab

    $('.accor-tab-btn').on('click', function (e) {
        e.preventDefault();
        var index = $(this).index();
        $('.accor-tab-btn').removeClass('active-at-btn');
        $(this).addClass('active-at-btn');
        $('.wcvs-accor-tab-content').hide().eq(index).show();
    });

    //Dynamically Call the accordion target
    $(".wcvs-accordion-switch").change(function () {
        var wcvs_accordion_target = $(this).attr('data-target');
        if (this.checked) {
            $(wcvs_accordion_target).slideDown();
        } else {
            $(wcvs_accordion_target).slideUp();
        }
    });

    // Add Color Picker to all inputs that have 'color-field' class
    $(function () {
        $('.vs-color-picker').wpColorPicker();
    });

    //Toggle show/hide a field when checking its conditional field
    $(".variation-switcher-item[data-conditional]").each(function () {

        const currentEle = $(this);

        const conditionalFieldID = currentEle.data("conditional");

        const conditionalFieldEle = $("#" + conditionalFieldID);

        if (conditionalFieldEle.length) {

            conditionalFieldEle.on("change", function () {
                const currentConditionalField = $(this);

                if (currentConditionalField.is(':checked')) {
                    currentEle.slideDown('fast');
                } else {
                    currentEle.slideUp('fast');
                }
            })

            //Trigger for the first time on page load
            conditionalFieldEle.trigger("change");
        }
    })
    //Show the Pro features popup
    $('#tawcvs-settings-wrap').on('click', '.wcvs-pro-item, .wcvs-pro-item *', function (e) {
        e.preventDefault();
        $('.wcvs-pro-feature-popup,.wcvs-popup-blur').show();
    })
    //Hide the Pro features popup when clicking on close button or outside the popup
    $('.popup-close,.wcvs-popup-blur').on('click', function () {
        $('.wcvs-popup,.wcvs-popup-blur').hide();
    })
});


