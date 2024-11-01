jQuery(document).ready(function($) {
    var mediaUploader;

    // Open media uploader when "scll_logo_button" is clicked
    $('#scll_logo_button').click(function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#scll_logo').val(attachment.url);
            $('#scll_logo_preview').attr('src', attachment.url);
        });
        mediaUploader.open();
    });

    // Clear logo when "scll_logo_remove" is clicked
    $('#scll_logo_remove').click(function(e) {
        e.preventDefault();
        $('#scll_logo').val('');
        $('#scll_logo_preview').attr('src', '');
    });

    // Initialize color picker for elements with the class "color-field"
    $('.color-field').wpColorPicker();
});
