/* 
    @Created on : Dec 11, 2018, 2:43:40 AM
    @since 1.0.9
    @author CodeSolz <customer-service@codesolz.net>
*/
( function( $) {
    $.wpMediaUploader = function( options ) {
        var settings = $.extend({
            
            target : '.smartcat-uploader', // The class wrapping the textbox
            uploaderTitle : 'Select or upload image', // The title of the media upload popup
            uploaderButton : 'Set image', // the text of the button in the media upload popup
            multiple : false, // Allow the user to select multiple images
            buttonText : 'Upload image', // The text of the upload button
            buttonClass : '.smartcat-upload', // the class of the upload button
            previewSize : '150px', // The preview image size
            preview : false,
            modal : false, // is the upload button within a bootstrap modal ?
            buttonStyle : { // style the button
                margin : '10px 0px',                
            },
            
        }, options );
        
        
        $( settings.target ).append( '<a href="#" class="' + settings.buttonClass.replace('.','') + '">' + settings.buttonText + '</a>' );
        
        // console.log( settings.preview);
        
        if( settings.preview !== false ){
            $( settings.target ).append('<div><br><img src="#" style="display: none; width: ' + settings.previewSize + '"/></div>')
        }
        
        $( settings.buttonClass ).css( settings.buttonStyle );
        
        $('body').on('click', settings.buttonClass, function(e) {
            
            e.preventDefault();
            var selector = $(this).parent( settings.target );
            var custom_uploader = wp.media({
                title: settings.uploaderTitle,
                button: {
                    text: settings.uploaderButton
                },
                multiple: settings.multiple
            })
            .on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                selector.find( 'img' ).attr( 'src', attachment.url).show();
                selector.find( 'input' ).val(attachment.url);
                if( settings.modal ) {
                    $('.modal').css( 'overflowY', 'auto');
                }
            })
            .open();
        });
    };
})(jQuery);
