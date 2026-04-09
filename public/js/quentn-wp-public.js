(function( $ ) {
    document["quentn_flipclock"] = function () {
        jQuery(document).ready(function () {
            jQuery(".quentn-flipclock:not(.quentn-initialized)").each(function (i, o) {
                $o = jQuery(o);
                $o.addClass("quentn-initialized");

                if (typeof qncountdown != "undefined") {
                    var currentLang = qncountdown.wpLang;
                    var $langHost = jQuery("[qn-countdown-language]").first();
                    if ($langHost.length) {
                        var attrLang = $langHost.attr("qn-countdown-language");
                        if (attrLang) currentLang = String(attrLang).trim().toLowerCase();
                    } else if (typeof ICL_LANGUAGE_CODE !== "undefined" && ICL_LANGUAGE_CODE) {
                        currentLang = String(ICL_LANGUAGE_CODE).trim().toLowerCase();
                    }
                    var clock = jQuery(o).FlipClock(qncountdown.seconds, {
                        clockFace: qncountdown.clockFace,
                        language: currentLang,
                        countdown: true,
                        callbacks : {
                            stop : function () {
                                if(qncountdown.is_redirect) {
                                    window.location = qncountdown.redirect_url
                                }
                                else {
                                    window.location.reload(false);
                                }
                            }
                        }
                    });
                } else {
                    var clock = jQuery(o).FlipClock();
                }
            });
        });
    };


    jQuery(document).ready(function () {
        document.quentn_flipclock();
        if ( typeof qncountdown != "undefined" && ! qncountdown.isDisplayQuentnFlipclock ) {
            window.setTimeout(function(){
                if(qncountdown.is_redirect) {
                    window.location = qncountdown.redirect_url
                }
                else {
                    window.location.reload(false);
                }
            }, qncountdown.seconds * 1000 );
        }
    });

})( jQuery );


