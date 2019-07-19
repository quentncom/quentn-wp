(function( $ ) {
    document["quentn_flipclock"] = function () {
        jQuery(document).ready(function () {
            jQuery(".quentn-flipclock:not(.quentn-initialized)").each(function (i, o) {
                $o = jQuery(o);
                $o.addClass("quentn-initialized");

                if (typeof qncountdown != "undefined") {
                    var clock = jQuery(o).FlipClock(qncountdown.seconds, {
                        clockFace: qncountdown.clockFace,
                        language: qncountdown.wpLang,
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
    });


})( jQuery );
