
/*
 * Display points of the events timeline
 * 
 * @param string arrt - name of a attribute to check ( data-year, data-month, data-day )
 * @param string html - HTML to display {value} - tag to replace
 */
function wptao_timeline( attr, html ) {

    var array = [ ];
    var events = jQuery( '.wptao-event-item' );


    if ( events.length > 0 ) {

        for ( i = 0; i < events.length; i++ ) {

            array.push( jQuery( events[i] ).attr( attr ) );
        }

        // Unique years
        jQuery.unique( array )


        if ( array.length > 0 ) {
            for ( i = 0; i < array.length; i++ ) {


                j = 0;
                find = true;
                while ( find ) {

                    if ( jQuery( events[j] ).attr( attr ) === array[i] ) {

                        var output = '';

                        output = html.replace( "{value}", array[i] );

                        if ( attr === 'data-day-text' ) {

                            var more = jQuery( events[j] ).attr( 'data-day' );

                            output = html.replace( "{value}", more );

                            output = output.replace( "{value2}", array[i] );
                        }


                        jQuery( events[j] ).before( output );

                        find = false;
                    }
                    j++;
                }

            }
        }

    }

}


function wptao_build_timeline() {

    jQuery( '.wptao-added-by-js' ).remove();

    var wptao_year_html = '<div class="wptao-event wptao-added-by-js wptao-date-year"><span class="wptao-table-mark-lg">{value}</span></div>';
    wptao_timeline( 'data-year', wptao_year_html );

    var wptao_month_html = '<div class="wptao-event wptao-added-by-js wptao-date-month"><span class="wptao-table-mark-dot"></span><span class="wptao-table-month">{value}</span></div>';
    wptao_timeline( 'data-month', wptao_month_html );

    var wptao_day_html = '<div class="wptao-event wptao-added-by-js wptao-date-day"><span class="wptao-table-mark-md">{value}</span><span class="wptao-table-day">{value2}</span></div>';
    wptao_timeline( 'data-day-text', wptao_day_html );
}



jQuery( document ).ready( function ( $ ) {

    // =============================================================================================
    // Profile events filter - datepicker
    // =============================================================================================

    $( '.wptao-date-input' ).datepicker( {
        dateFormat: 'yy-mm-dd',
        closeText: wptao_datepicker.close_text,
        currentText: wptao_datepicker.current_text,
        monthNames: wptao_datepicker.month_names,
        monthNamesShort: wptao_datepicker.month_names_short,
        dayNames: wptao_datepicker.day_names,
        dayNamesShort: wptao_datepicker.day_names_short,
        dayNamesMin: wptao_datepicker.day_names_min,
        firstDay: wptao_datepicker.first_day,
        isRTL: wptao_datepicker.is_rtl,
        beforeShow: function ( input, inst ) {
            $( '#ui-datepicker-div' ).addClass( 'wptao-datepicker' );
        }
    } );


    ( function ( $ ) {
        // =============================================================================================
        // Dashboard Packery formatting
        // @since 1.2.3
        // =============================================================================================
        function wptao_build_packery_grid() {
            var $packery,
                $dashboard = $( '.wptao-dashboard-boxes' );

            if ( $dashboard.length > 0 ) {

                $packery = $( '.wptao-dashboard-boxes' ).packery( {
                    itemSelector: '.wptao-dbox',
                    gutter: 5,
                    columnWidth: 150,
                    rowHeight: 150
                } );

                // make all items draggable
                var $packery = $dashboard.find( '.wptao-dbox' ).draggable( {
                    handle: '.wptao-dbox-handler'
                } );
                // bind drag events to Packery
                $dashboard.packery( 'bindUIDraggableEvents', $packery );

                $dashboard.on( 'dragItemPositioned', function () {
                    var order = [ ],
                        itemElems = $dashboard.packery( 'getItemElements' );

                    $( itemElems ).each( function ( i ) {
                        var id = $( this ).attr( 'data-id' );
                        order[i] = id;
                    } );

                    var data = {
                        action: 'wptao_dashboard_order',
                        token: $( '.wptao-dashboard-boxes' ).attr( 'data-token' ),
                        order: JSON.stringify( order )
                    };

                    $.post( ajaxurl, data );

                } );
            }
        }
        wptao_build_packery_grid();

        // =============================================================================================
        // Hide espresso report
        // @since 1.2.4
        // =============================================================================================

        var hanlerClass = 'wptao-dbox-close',
            reportClass = 'wptao-dbox';

        $( document ).on( 'click', '.' + hanlerClass, function () {
            var $espresso = $( this ).closest( '.' + reportClass );


            if ( $espresso.length > 0 ) {

                $espresso.addClass( 'wptao-event-delete-effect' );

                $.ajax( {
                    url: ajaxurl,
                    data: {
                        action: 'wptao_hide_escpresso_report',
                        espresso_id: $espresso.data( 'id' ),
                        nonce: $( this ).data( 'nonce' )
                    }
                } ).done( function ( data ) {

                    if ( data == 1 ) {

                        setTimeout( function () {
                            var $dashboard = $( '.wptao-dashboard-boxes' );
                            $espresso.remove();
                            if ( $dashboard.length > 0 ) {
                                $dashboard.packery( 'destroy' );
                                wptao_build_packery_grid();
                            }
                        }, 300 );
                    }

                } );

            }

            return false;

        } );

    }( jQuery ) );

    // =============================================================================================
    // Toogle widgets
    // =============================================================================================
    $( '.wptao-toggle-module' ).click( function () {

        var content = $( this ).parent().find( '.wptao-module-content' );

        if ( $( this ).parent().hasClass( 'wptao-module-open' ) ) {
            content.slideUp();
            $( this ).parent().removeClass( 'wptao-module-open' );
        } else {
            content.slideDown();
            $( this ).parent().addClass( 'wptao-module-open' );
        }

    } );

    // =============================================================================================
    // Build timeline
    // =============================================================================================
    wptao_build_timeline();


    // =============================================================================================
    // Ajax load
    // =============================================================================================

    wptao_activity_block_ajax = false;

    function wptao_get_events_user_profile() {

        if ( wptao_activity_block_ajax === false ) {
            $( '.wptao-event-preloader' ).fadeIn();
        }

        var q = wptao_events_vars;


        var data = {
            action: 'wptao_get_events',
            token: $( '#wptao-activity' ).attr( 'data-token' ),
            user_id: q.user_id !== 'undefinded' ? q.user_id : null,
            items_per_page: q.items_per_page !== 'undefinded' ? q.items_per_page : null,
            fingerprint_id: q.fingerprint_id !== 'undefinded' ? q.fingerprint_id : null,
            category: q.category !== 'undefinded' ? q.category : null,
            event_action: q.event_action !== 'undefinded' ? q.event_action : null,
            tags: q.tags !== 'undefinded' ? q.tags : null,
            meta_key: q.meta_key !== 'undefinded' ? q.meta_key : null,
            meta_value: q.meta_value !== 'undefinded' ? q.meta_value : null,
            date_start: q.date_start !== 'undefinded' ? q.date_start : null,
            date_end: q.date_end !== 'undefinded' ? q.date_end : null,
            identified: q.identified !== 'undefinded' ? q.identified : null,
            offset: $( '.wptao-event-item' ).length,
            base_url: $( '#wptao-timeline-form' ).attr( 'action' )
        };


        if ( wptao_activity_block_ajax === false ) {

            $.post( ajaxurl, data, function ( response ) {

                $( '.wptao-event-preloader' ).fadeOut();

                $( '.wptao-events-content-inner' ).append( response );

                wptao_build_timeline();


                if ( response.indexOf( 'wptao-event-nores' ) !== -1 ) {

                    $( '.wptao-older-events-wrapp' ).remove();
                    wptao_activity_block_ajax = true;

                }

            } );

        }


    }


    // Remove load button if there is too little results
    if ( typeof wptao_events_vars !== 'undefined' && wptao_events_vars.items_per_page > $( '.wptao-event-item' ).length ) {
        $( '.wptao-older-events-wrapp' ).remove();
    }


    $( '#wptao-older-events' ).click( function () {

        wptao_get_events_user_profile();
    } );

    // =============================================================================================
    // Dismiss notice
    // =============================================================================================

    $( document ).on( 'click', '.wptao-mail-notice-dismiss .notice-dismiss', function () {
        $.ajax( {
            url: ajaxurl,
            data: {
                action: 'wptao_dismiss_mail_notice'
            }
        } );
    } );

    // =============================================================================================
    // Dismiss notice - promo boxes
    // @since 1.2.5.3
    // =============================================================================================

    $( document ).on( 'click', '.wptao-promobox-dismiss', function () {
        var $box = $( this ).closest( '.wptao-promobox' ),
            id = $box.attr( 'data-id' );

        $.ajax( {
            url: ajaxurl,
            data: {
                action: 'wptao_dismiss_promobox_notice',
                id: id
            }
        } ).done( function ( data ) {

            $box.fadeOut( 500, function () {
                $box.remove();
            } );

        } );


        return false;
    } );

    // =============================================================================================
    // Record's actions
    // =============================================================================================

    $( document ).on( 'change', '#add_to_blacklist', function () {
        if ( $( '#add_to_blacklist' ).is( ':checked' ) ) {
            $( "#delete_record" ).prop( {
                checked: false,
                disabled: true
            } );
        } else {
            $( "#delete_record" ).prop( {
                disabled: false
            } );
        }
    } );

    $( document ).on( 'change', '#remove_from_blacklist', function () {
        if ( $( '#remove_from_blacklist' ).is( ':checked' ) ) {
            $( "#delete_record" ).prop( {
                checked: false,
                disabled: true
            } );
        } else {
            $( "#delete_record" ).prop( {
                disabled: false
            } );
        }
    } );

    $( document ).on( 'change', '#delete_record', function () {
        if ( $( '#delete_record' ).is( ':checked' ) ) {
            $( "#add_to_blacklist" ).prop( {
                checked: false,
                disabled: true
            } );
            $( "#remove_from_blacklist" ).prop( {
                checked: false,
                disabled: true
            } );
        } else {
            $( "#add_to_blacklist" ).prop( {
                disabled: false
            } );
            $( "#remove_from_blacklist" ).prop( {
                disabled: false
            } );
        }
    } );


    // =============================================================================================
    // Show/hide the event actions panel on the timeline
    // @since 1.1.7
    // =============================================================================================

    var wptao_event_action_timeout, wptao_event_action_this;
    $( '.wptao-event-content' ).on( 'mouseenter', function ( ) {

        if ( window.innerWidth <= 782 ) {
            return;
        }

        clearTimeout( wptao_event_action_timeout );

        wptao_event_action_this = $( this );

        wptao_event_action_timeout = setTimeout( function () {
            wptao_event_action_this.find( '.wptao-event-actions-panel' ).slideDown( 300 );
        }, 200 );

    } );

    $( '.wptao-event-content' ).on( 'mouseleave', function ( ) {

        if ( window.innerWidth <= 782 ) {
            return;
        }

        clearTimeout( wptao_event_action_timeout );

        $( this ).find( '.wptao-event-actions-panel' ).slideUp( 200 );

    } );


    // =============================================================================================
    // Delete event
    // @since 1.1.7
    // =============================================================================================

    $( document ).on( 'click', '.wptao-event-id', function () {
        if ( !confirm( wptao_events_vars['confirm_message'] ) )
            return;

        var event_id = $( this ).data( 'event-id' );
        var row = $( '.wptao-row[data-event-id="' + event_id + '"]' );

        row.addClass( 'wptao-event-delete-effect' );

        $.ajax( {
            url: ajaxurl,
            data: {
                action: 'wptao_delete_event',
                event_id: event_id,
                nonce: $( this ).data( 'nonce' )
            }
        } ).done( function ( data ) {

            var id = parseInt( data );

            if ( id !== -1 ) {

                $( '.wptao-row[data-event-id="' + id + '"]' ).slideUp( "normal", function () {
                    $( this ).remove();
                } );

                // Rebuild timeline
                setTimeout( function () {
                    wptao_build_timeline();
                }, 500 );
            }
        } );

        return false;
    } );


    // =============================================================================================
    // Datepicker
    // @since 1.1.9
    // =============================================================================================

    // Store current date
    if ( $( '.wptao-date-range input' ).length > 0 ) {
        $( '.wptao-date-range input' ).each( function () {
            $( this ).attr( 'data-temp', $( this ).val() );
        } );
    }


    $( document ).on( 'click', '.wptao-date-button', function () {
        var open = 'wptao-filter-expanded-open';
        var expanded = $( this ).next();

        // Show/hide expanded popup
        if ( expanded.hasClass( 'wptao-filter-expanded' ) ) {

            if ( expanded.hasClass( open ) ) {

                expanded.removeClass( open );
                expanded.fadeOut( 100 );

            } else {
                expanded.addClass( open );
                expanded.fadeIn( 100 );
            }
        }
    } );

    // Show/hide apply and cancel buttons after select date
    $( document ).on( 'change', '.wptao-date-range input', function () {

        $( '.wptao-date-exp-btns' ).show();

    } );

    // Cancel current choice
    $( document ).on( 'click', '.wptao-date-exp-btn-cancel', function () {

        $( '.wptao-date-range input' ).each( function () {
            $( this ).val( $( this ).attr( 'data-temp' ) );
        } );
        $( '.wptao-filter-expanded' ).hide();
        $( '.wptao-filter-expanded' ).removeClass( 'wptao-filter-expanded-open' );

    } );




    // Hide expanded pupup on cliks outsite of it
    $( document ).mouseup( function ( e ) {

        var open = 'wptao-filter-expanded-open';
        var expanded = $( '.wptao-filter-expanded' );
        var jquerDatepicker = $( '.ui-datepicker' );
        var btn = $( '.wptao-date-button' );

        if (
            !expanded.is( e.target ) &&
            expanded.has( e.target ).length === 0 &&
            !jquerDatepicker.is( e.target ) &&
            jquerDatepicker.has( e.target ).length === 0 &&
            !btn.is( e.target ) &&
            btn.has( e.target ).length === 0
            )
        {
            expanded.removeClass( open );
            expanded.fadeOut( 100 );
        }
    } );


    // =============================================================================================
    // Show/Hide user fingerprints
    // @since 1.1.9
    // =============================================================================================

    // Show more fingerprints
    $( document ).on( 'click', '.wptao-more-user-fp', function () {

        $( this ).hide();
        $( '.wptao-mod-user-fingerprint-older' ).slideDown( 400, function () {
            $( '.wptao-less-user-fp' ).show();
        } );


    } );

    // Hide older fingerprints
    $( document ).on( 'click', '.wptao-less-user-fp', function () {

        $( this ).hide();
        $( '.wptao-mod-user-fingerprint-older' ).slideUp( 400, function () {
            $( '.wptao-more-user-fp' ).show();
        } );


    } );


    // Add attr target to the addons submenu item
    if ( $( '.wptao-addons-link' ).length > 0 ) {
        $( '.wptao-addons-link' ).attr( 'target', '_blank' );
    }

    // =============================================================================================
    // Fire jQuery tooltip for Tao question marks with hints
    // @since 1.2.4
    // =============================================================================================
    if ( $( '[data-wptao-tooltip]' ).length > 0 && !!$.prototype.tooltip ) {
        $( document ).tooltip( {
            tooltipClass: 'wptao-tooltip',
            items: '[data-wptao-tooltip]',
            content: function () {
                var $content = $( this ).children( '.wptao-tooltip-content' );

                if ( $content.length > 0 ) {
                    return $content.html();
                } else {
                    return $( this ).attr( 'title' );
                }
            },
            position: { my: "center bottom", at: "center top-10", collision: "none" },
            open: function ( event, ui ) {
                var w = ui.tooltip.innerWidth(),
                    pos = ui.tooltip.position(),
                    rightPoint = ( $( window ).width() - w ) - 20;

                if ( pos.left < 20 ) {
                    ui.tooltip.css( 'left', '20px' );
                }
                if ( pos.left > rightPoint ) {
                    ui.tooltip.css( 'left', 'auto' );
                    ui.tooltip.css( 'right', '20px' );
                }
            }
        } );
    }

} );
