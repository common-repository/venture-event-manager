(function (ventureGallery, $, undefined) {

    var dialog;

    $(document).ready(function () {
        $('.gallery').imagesLoaded(function() {
            $('.gallery').masonry({
                itemSelector: '.gallery-item',
                columnWidth: '.gallery-item',
                horizontalOrder: true
            });
        })

        $('.wp-block-gallery').imagesLoaded(function() {
            $('.wp-block-gallery').masonry({
                itemSelector: '.blocks-gallery-item',
                columnWidth: '.blocks-gallery-item',
                horizontalOrder: true
            });
        })

        $('.gallery').on('click', '.gallery-item a', function() {
            if (typeof dialog === 'undefined') {
                $('body').append('<div id="vem-gallery-dialog"><img src="" /></div>');
                dialog = $('#vem-gallery-dialog').dialog({
                    autoOpen: false,
                    autoResize: true,
                    width: 'auto',
                    modal: true
                });
            }

            $('#vem-gallery-dialog')
                .find('img').attr('src', $(this).attr('href'));
            var alt =  $('img', this).attr('alt');
            var caption = $(this).closest('figure').find('figcaption').html();

            dialog.dialog('option', 'title', caption || alt || 'Untitled');
            dialog.dialog('open');

            return false;
        });

        $('.wp-block-gallery').on('click', '.blocks-gallery-item a', function() {
            if (typeof dialog === 'undefined') {
                $('body').append('<div id="vem-gallery-dialog"><img src="" /></div>');
                dialog = $('#vem-gallery-dialog').dialog({
                    autoOpen: false,
                    autoResize: true,
                    width: 'auto',
                    modal: true
                });
            }

            $('#vem-gallery-dialog')
                .find('img').attr('src', $(this).attr('href'));

            var alt =  $('img', this).attr('alt');
            var caption = $(this).closest('figure').find('figcaption').html();
    
            dialog.dialog('option', 'title', caption || alt || 'Untitled');
            dialog.dialog('open');

            return false;
        });

    });

}(window.ventureGallery = window.ventureGallery || {}, jQuery));
