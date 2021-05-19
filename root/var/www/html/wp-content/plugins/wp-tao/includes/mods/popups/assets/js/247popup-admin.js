

( function ( $ ) {

    WTBP247popup = {
        tabClass: 'wtbp-247p-mb-section',
        activeClass: 'wtbp-247p-mb-active',
        panelsSel: '.wtbp-247p-mb-panels .metabox',
        firstPanelSel: '.metabox-247p-logic',
        loaderClass: 'metabox-247p-mb-loader',
        getSlug: function ( el ) {
            if ( el.length > 0 && el.data( 'rel' ).length > 0 ) {
                return el.data( 'rel' );
            }
            return false;
        },
        updateMenu: function ( el ) {
            $( '.' + this.tabClass ).removeClass( this.activeClass );
            el.addClass( this.activeClass );
        },
        showPanel: function ( slug ) {
            var el = $( '.metabox-247p-' + slug ),
                panels = $( this.panelsSel );

            if ( el.length > 0 ) {
                panels.hide();
                el.fadeIn();
            }
            return false;
        },
        colorPickerInit: function () {
            var cpClass = $( '.wtbp-247p-colorpicker' );

            if ( cpClass.length > 0 ) {
                cpClass.wpColorPicker();
            }
        },
        init: function () {
            var panels = $( this.panelsSel ),
                first = $( this.firstPanelSel );
            panels.hide();
            first.show();
            $( '.' + this.loaderClass ).removeClass( this.loaderClass );
            this.colorPickerInit();
        }
    };



    $( document ).ready( function () {

        WTBP247popup.init();

        $( '.' + WTBP247popup.tabClass ).on( 'click', function () {
            var el = $( this ),
                slug = WTBP247popup.getSlug( el );

            WTBP247popup.updateMenu( el );
            WTBP247popup.showPanel( slug );

        } );

    } );

}( jQuery ) );