jQuery(document).ready(function () {

    jQuery('#quentn_page_restrict_datepicker').datetimepicker({
        weekStart: true,
        todayBtn:  true,
        autoclose: true,
        todayHighlight: true,
        startView: 2,
        forceParse: false,
        language : wp_qntn.datepicker_lang,
    });

    //dropdown to select quentn tags for wp user roles
    jQuery(".quentn-term-selection").select2({
        placeholder: wp_qntn.choose_quentn_tags,
    });

    //ajax call when user dismiss cookie plugin notice
    jQuery(document).on( 'click', '.quentn-cookie-notice', function() {
        jQuery.ajax({
            url: ajaxurl,
            data: {
                action: 'quentn_dismiss_cookie_notice'
            },
        })

    });

    //ajax call when user dismiss members plugin notice
    jQuery(document).on( 'click', '.quentn-member-plugin-notice', function() {
        jQuery.ajax({
            url: ajaxurl,
            data: {
                action: 'quentn_dismiss_member_plugin_notice'
            },
        })

    });

    //when user activate quentn restrictions
    jQuery("#quentn_page_access_status").click(function() {
        jQuery( "#quentn_permission_panel" ).toggle( 'slow' );
        jQuery( "#quentn_page_redirection_type_panel" ).toggle( 'slow' );
        jQuery( "#quentn_page_access_countdown" ).attr( 'checked', true );
        jQuery( "#quentn_countdown_settings" ).show( 'slow' );
        jQuery( "#relative-div" ).show( 'slow' );
        jQuery( "#forbidden_message_div" ).show( 'slow' );
    });



    //when user select restriction as countdown
    jQuery( '#quentn_page_access_countdown' ).on( 'change', function() {
            if ( jQuery( this ).prop( 'checked' ) ) {
                jQuery( "#quentn_countdown_settings" ).show( 'slow' );
            } else {
                jQuery( "#quentn_countdown_settings" ).hide( 'slow' );
            }
        }
    );

    //when user change restriction countdown type, i.e absolute or relative
    jQuery( '#quentn_page_restrict_countdown_type' ).on( 'change', function() {
            if(this.value=='relative'){
                jQuery( "#absolute-div" ).hide();
                jQuery( "#relative-div" ).show( 'slow' );

            }else {
                jQuery( "#relative-div" ).hide();
                jQuery( "#absolute-div" ).show( 'slow' );
            }
        }
    );

    //when user change redirection settings i.e message or redirect
    jQuery( '#quentn_page_redirection_type' ).on( 'change', function() {
            if(this.value=='restricted_url'){
                jQuery( "#forbidden_message_div" ).hide();
                jQuery( "#redirect_url-div" ).show( 'slow' );
            }else {
                jQuery( "#redirect_url-div" ).hide();
                jQuery( "#forbidden_message_div" ).show( 'slow' );
            }
        }
    );


    //enable/disable create or update quentn contact for wp user roles
    jQuery( '.add-wp-qntn' ).on( 'change', function() {
            if ( jQuery( this ).prop( 'checked' ) ) {
                jQuery( "#quentn_tags_remove_wp_user"+jQuery( this ).attr('data-role') ).prop('disabled', false);
                jQuery( "#quentn_tags_wp_user"+jQuery( this ).attr('data-role') ).prop('disabled', false);
            } else {
                jQuery( "#quentn_tags_remove_wp_user"+jQuery( this ).attr('data-role') ).prop('checked', false);
                jQuery( "#quentn_tags_remove_wp_user"+jQuery( this ).attr('data-role') ).prop('disabled', true);
                jQuery( "#quentn_tags_wp_user"+jQuery( this ).attr('data-role') ).prop('disabled', true);
            }
        }
    );


    //copy access url
    jQuery(".copy_access_url").click(function() {
        copyText = jQuery( this ).prev(".get_access_url");
        copyText.select();
        document.execCommand("copy");
        return false;
    });

    //display consent methods when web tracking checked
    jQuery( '#quentn_web_tracking_enabled' ).on( 'change', function() {
            if ( jQuery( this ).prop( 'checked' ) ) {
                jQuery( "#quentn_web_tracking_consent_method" ).prop('disabled', false);

            } else {
                jQuery( "#quentn_web_tracking_consent_method" ).prop('disabled', true);
            }
        }
    );


    //bootstrap popover
    jQuery('[data-toggle="popover"]').popover();

    //bootstrap touchspin settings for hours, minutes and seconds
    jQuery('#quentn_page_restrict_hours').TouchSpin({
        min: 0,
        max: 1000000,
        buttonup_class: 'btn btn-default',
        buttondown_class: 'btn btn-default',
    });

    jQuery('#quentn_page_restrict_minutes').TouchSpin({
        min: 0,
        max: 59,
        buttonup_class: 'btn btn-default',
        buttondown_class: 'btn btn-default',
    });

    jQuery('#quentn_page_restrict_seconds').TouchSpin({
        min: 0,
        max: 59,
        buttonup_class: 'btn btn-default',
        buttondown_class: 'btn btn-default',
    })

    jQuery('#doaction').click( function( event ) {

        if( jQuery( "#bulk-action-selector-top" ).val() == 'quentn-bulk-delete-access' && ! confirm( wp_qntn.delete_confirmation_message ) ) {
            event.preventDefault();
        }
    });
});