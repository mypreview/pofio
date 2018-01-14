/**
 * All of the code for your admin-facing JavaScript source
 * resides in this file.
 *
 * @package         Pofio
 * @subpackage      Pofio/includes
 * @link            https://github.com/mypreview/pofio
 * @author          Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since           1.0.0
 */
(function(window, $, undefined) {
    $(document).ready(function() {

        // Move the subtitle field to right after the title field
        if ($('#pofio_subtitle_meta_data').length > 0) {
            $('#pofio_subtitle_meta_data').insertAfter('#title').end().show();
        }

        // Add keyboard tab support to focus on subtitle field
        $(document).on('keydown', '#title, #pofio_subtitle_meta_data', function(e) {
            var keyCode = e.keyCode || e.which;
            if (9 == keyCode) {
                e.preventDefault();
                var target = $(this).attr('id') == 'title' ? '#pofio_subtitle_meta_data' : 'textarea#content';
                if ((target === '#pofio_subtitle_meta_data') || $('#wp-content-wrap').hasClass('html-active')) {
                    $(target).focus();
                } else {
                    tinymce.execCommand('mceFocus', false, 'content');
                }
            }
        });

        $(document).on('click', '#pofio_fg_select', function(e) {
            e.preventDefault();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            var file_frame = wp.media.frame = wp.media({
                frame: 'post',
                state: 'featured-gallery',
                library: {
                    type: 'image'
                },
                multiple: true
            });

            // Create Featured Gallery state. 
            // This is essentially the Gallery state, but selection behavior is altered.
            file_frame.states.add([
                new wp.media.controller.Library({
                    id: 'featured-gallery',
                    title: pofioplusFGVars.fg_add_to_gallery,
                    priority: 20,
                    toolbar: 'main-gallery',
                    filterable: 'uploaded',
                    library: wp.media.query(file_frame.options.library),
                    multiple: file_frame.options.multiple ? 'reset' : false,
                    editable: true,
                    allowLocalEdits: true,
                    displaySettings: true,
                    displayUserSettings: true
                }),
            ]);

            file_frame.on('open', function() {
                var selection = file_frame.state().get('selection'),
                    library = file_frame.state('gallery-edit').get('library'),
                    ids = $('#pofio_fg_perm_meta_data').val();
                if (ids) {
                    idsArray = ids.split(',');
                    idsArray.forEach(function(id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                    file_frame.setState('gallery-edit');
                    idsArray.forEach(function(id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        library.add(attachment ? [attachment] : []);
                    });
                }
            });

            file_frame.on('ready', function() {
                $('.media-modal').addClass('no-sidebar');
            });

            // When an image is selected, run a callback.
            file_frame.on('update', function() {
                var imageIDArray = [],
                    imageHTML = '',
                    metadataString = '';
                images = file_frame.state().get('library');
                images.each(function(attachment) {
                    imageIDArray.push(attachment.attributes.id);
                    imageHTML += '<li><button class="remove-item">&times;</button><img id="' + attachment.attributes.id + '" src="' + attachment.attributes.url + '"></li>';
                });
                metadataString = imageIDArray.join(",");
                if (metadataString) {
                    $('#pofio_fg_perm_meta_data').val(metadataString);
                    $('#pofio-fg-wrapper ul').html(imageHTML);
                    $('#pofio_fg_select').text(pofioplusFGVars.fg_edit_images_lbl);
                    $('#pofio_fg_select').parent('p').addClass('hidden');
                    $('#pofio_fg_remove_all').parent('p').removeClass('hidden');
                    $('#pofio-fg-wrapper .howto').removeClass('hidden');
                    setTimeout(function() {
                        pofio_update_temp_meta_data();
                    }, 0);
                }
            });

            // Finally, open the modal
            file_frame.open();

        });

        // Open modal window if user clicks on any image on metabox
        $(document).on('click', '#pofio-fg-wrapper ul img', function(e) {
            e.preventDefault();
            $('#pofio_fg_select').trigger('click');
            pofio_go_back_to_media();
        });

        // Remove single item from the list of featured gallery items.
        $(document).on('click', '#pofio-fg-wrapper ul .remove-item', function(e) {
            e.preventDefault();

            if (confirm(pofioplusFGVars.fg_remove_single_img)) {

                var removedImage = $(this).parent().children('img').attr('id'),
                    oldGallery = $('#pofio_fg_perm_meta_data').val(),
                    newGallery = oldGallery.replace(',' + removedImage, '').replace(removedImage + ',', '').replace(removedImage, '');

                $(this).parent('li').remove();
                $('#pofio_fg_perm_meta_data').val(newGallery);
                if ('' === newGallery) {
                    $('#pofio_fg_select').text(pofioplusFGVars.fg_select_images_lbl);
                    $('#pofio_fg_select').parent('p').removeClass('hidden');
                    $('#pofio_fg_remove_all').parent('p').addClass('hidden');
                    $('#pofio-fg-wrapper .howto').addClass('hidden');
                }
                pofio_update_temp_meta_data();

            }

        });

        // Remove all inserted gallery items from the metabox.
        $(document).on('click', '#pofio_fg_remove_all', function(e) {
            e.preventDefault();

            if (confirm(pofioplusFGVars.fg_remove_all_imgs)) {

                $('#pofio-fg-wrapper ul').html('');
                $('#pofio_fg_perm_meta_data').val('');
                $('#pofio_fg_remove_all').parent('p').addClass('hidden');
                $('#pofio-fg-wrapper p.howto').addClass('hidden');
                $('#pofio_fg_select').text(pofioplusFGVars.fg_select_images_lbl);
                $('#pofio_fg_select').parent('p').removeClass('hidden');
                pofio_update_temp_meta_data();

            }

        });

    });
})(this, jQuery);

// Open media uploader modal window
function pofio_go_back_to_media() {

    setTimeout(function() {
        if (jQuery('.media-menu a:first-child').length > 0) {
            jQuery('.media-menu a:first-child').trigger('click');
        }
    }, 0);

}

// Insert selected media items into featured gallery metabox.
function pofio_update_temp_meta_data() {

    jQuery.ajax({
        type: 'POST',
        dataType: 'JSON',
        url: pofioplusFGVars.ajax_url,
        data: {
            action: 'fg_update_temp',
            fg_post_id: jQuery('#pofio_fg_perm_meta_data').data('post_id'),
            pofio_fg_temp_nonce_data: jQuery('#pofio_fg_temp_nonce_data').val(),
            pofio_fg_temp_meta_data: jQuery('#pofio_fg_perm_meta_data').val()
        },
        success: function(response) {
            if (response == 'error') {
                alert(pofioplusFGVars.fg_ajax_error);
            }
        }
    });

}