jQuery(document).ready(function ($) {
    function ct_media_upload(button_class) {
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;
        $('body').on('click', button_class, function (e) {
            var button_id = '#' + $(this).attr('id');
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(button_id);
            _custom_media = true;
            wp.media.editor.send.attachment = function (props, attachment) {
                if (_custom_media) {
                    $('#term-image').val(attachment.id);
                    $('#term-image-wrapper').html('<img class="custom_media_image" src="" style="max-height:150px;" class="attachment-thumbnail size-thumbnail" />');
                    $('#term-image-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                } else {
                    return false; //_orig_send_attachment.apply(button_id, [props, attachment]);
                }
            }
            wp.media.editor.open(button);
            return false;
        });
    }
    ct_media_upload('.ct_tax_media_button.button');
    $('body').on('click', '.ct_tax_media_remove', function () {
        $('#term-image').val('');
        $('#term-image-wrapper').html('<img class="custom_media_image" src="" style="max-height:150px;margin-bottom:4px" class="attachment-thumbnail size-thumbnail" />');
    });
    // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
    $(document).ajaxComplete(function (event, xhr, settings) {
        var queryStringArr = settings.data.split('&');
        if ($.inArray('action=add-tag', queryStringArr) !== -1) {
            var xml = xhr.responseXML;
            $response = $(xml).find('term_id').text();
            if ($response != "") {
                // Clear the thumb image and hidden value
                $('#term-image-wrapper').html('');
                $('#term-image').val('');
            }
        }
    });

});