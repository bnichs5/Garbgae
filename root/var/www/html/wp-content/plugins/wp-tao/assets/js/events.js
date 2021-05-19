
/**
 * Add new track event on client side
 *
 * This is a way to register track event
 *  
 * @param string action
 * @param string value,  max 255 chars
 * @param array tags
 * @param meta object
 * @param userData object
 */
function wptaoEvent( action, value, tags, meta, userData ) {

    var data = {
        action: 'wptao_event',
        event_action: typeof action == 'undefined' && false || action,
        event_value: typeof value == 'undefined' && false || value,
    };

    // Add tags
    if ( Array.isArray( tags ) ) {
        data.event_tags = JSON.stringify( tags );
    };

    // Add meta
    if ( typeof meta == 'object' ) {
        data.event_meta = JSON.stringify( meta );
    };
    
    // Add uderData
    if ( typeof userData == 'object' ) {
        data.user_data = JSON.stringify( userData );
    };

    jQuery.ajax( {
        data: data,
        type: 'post',
        url: wtbpWptao.ajaxEndpoint,
    } );

}

