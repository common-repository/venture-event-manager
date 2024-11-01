(function(ventureEventEditorVisualComposer, $, undefined) {

    String.prototype.toProperCase = function () {
        return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    };
    // tinymce.get('content').getContent()

    $(document).ready(function() {
        if ($("#wpb_visual_composer").length == 0) {
            // console.log('No Visual Composer');
            return;
        }

        window.setTimeout(function() {
            var editors = tinymce.get();
            var options = '';

            for (var i=0, len=editors.length; i<len; i++) {
                if (editors[i].id != 'content') {
                    options += '<option value="'+editors[i].id+'">'+editors[i].id.toProperCase()+'</option>';
                }
            }

            var classContainer = $("#post-body-content #postdivrich");
            var vcContainer = $("#wpb_visual_composer"); 

            var actionBar =
                '<div id="venture-vc-controller">' +
                    '<h2>Visual Composer Content</h2>' +
                    '<div class="actions">' +
                        '<div class="one-action" data-action="get">' +
                            '<select class="widefat">'+options+'</select>' +
                            '<button class="button button-primary button-large">Get Content</button>' +
                        '</div>' +
                        '<div class="one-action" data-action="set">' +
                            '<select class="widefat">'+options+'</select>' +
                            '<button class="button button-primary button-large">Send Content</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            classContainer.append(actionBar);
            vcContainer.append(actionBar);

        }, 1000);

        $('body').on('click', '.one-action button', function() {
            var action = $(this).parents('.one-action').data('action');
            var target = $(this).parents('.one-action').find('option:selected').val();

            switch (action) {
                case 'get':
                    tinymce.get('content').setDirty(false);
                    tinymce.get('content').setContent(tinymce.get(target).getContent());
                    console.log(action, target);
                    break;

                case 'set':
                    tinymce.get(target).setContent(tinymce.get('content').getContent());
                    tinymce.get('content').setDirty(false);
                    console.log(action, target);
                    break;
            }
            return false;
        });

    });

} (window.ventureEventEditorVisualComposer = window.ventureEventEditorVisualComposer || {}, jQuery));
