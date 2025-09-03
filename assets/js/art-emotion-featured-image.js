jQuery(document).ready(function($) {
    var mediaUploader;
    
    // Select featured image button
    $('#art_emotion_featured_image_button').on('click', function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create a new media uploader
        mediaUploader = wp.media({
            title: 'Choose Featured Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // When an image is selected in the media uploader
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Set the hidden input value
            $('#art_emotion_featured_image').val(attachment.id);
            
            // Update preview
            var previewHtml = '<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto;" />';
            $('#art_emotion_featured_image_preview').html(previewHtml);
            
            // Update button text and show remove button
            $('#art_emotion_featured_image_button').text('Change Featured Image');
            $('#art_emotion_remove_featured_image_button').show();
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // Remove featured image button
    $('#art_emotion_remove_featured_image_button').on('click', function(e) {
        e.preventDefault();
        
        // Clear the hidden input
        $('#art_emotion_featured_image').val('');
        
        // Clear preview
        $('#art_emotion_featured_image_preview').html('');
        
        // Update button text and hide remove button
        $('#art_emotion_featured_image_button').text('Select Featured Image');
        $('#art_emotion_remove_featured_image_button').hide();
    });
});