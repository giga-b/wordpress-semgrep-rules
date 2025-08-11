(function( $ ) {

    // Rest base setting

    $('#gamipress_rest_api_extended_rest_base').on( 'keyup', function() {
        var $this = $(this);
        var rest_base = $this.val();
        var preview = $this.next('.cmb2-metabox-description').find('.gamipress-rest-api-extended-rest-base');

        if( preview.length ) {
            preview.text(rest_base);
        }
    });

    $('#gamipress_rest_api_extended_rest_base').keyup();
})( jQuery );