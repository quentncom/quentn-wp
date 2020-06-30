(function( $ ) {
    // js-cookie v2.2.0 (https://github.com/js-cookie)
    !function(e){var n = !1; if ("function" == typeof define && define.amd && (define(e), n = !0), "object" == typeof exports && (module.exports = e(), n = !0), !n){var o = window.Cookies, t = window.Cookies = e(); t.noConflict = function(){return window.Cookies = o, t}}}(function(){function e(){for (var e = 0, n = {}; e < arguments.length; e++){var o = arguments[e]; for (var t in o)n[t] = o[t]}return n}function n(o){function t(n, r, i){var c; if ("undefined" != typeof document){if (arguments.length > 1){if ("number" == typeof (i = e({path:"/"}, t.defaults, i)).expires){var a = new Date; a.setMilliseconds(a.getMilliseconds() + 864e5 * i.expires), i.expires = a}i.expires = i.expires?i.expires.toUTCString():""; try{c = JSON.stringify(r), /^[\{\[]/.test(c) && (r = c)} catch (e){}r = o.write?o.write(r, n):encodeURIComponent(r + "").replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent), n = (n = (n = encodeURIComponent(n + "")).replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent)).replace(/[\(\)]/g, escape); var s = ""; for (var f in i)i[f] && (s += "; " + f, !0 !== i[f] && (s += "=" + i[f])); return document.cookie = n + "=" + r + s}n || (c = {}); for (var p = document.cookie?document.cookie.split("; "):[], d = /(%[0-9A-Z]{2})+/g, u = 0; u < p.length; u++){var l = p[u].split("="), C = l.slice(1).join("="); this.json || '"' !== C.charAt(0) || (C = C.slice(1, - 1)); try{var m = l[0].replace(d, decodeURIComponent); if (C = o.read?o.read(C, m):o(C, m) || C.replace(d, decodeURIComponent), this.json)try{C = JSON.parse(C)} catch (e){}if (n === m){c = C; break}n || (c[m] = C)} catch (e){}}return c}}return t.set = t, t.get = function(e){return t.call(t, e)}, t.getJSON = function(){return t.apply({json:!0}, [].slice.call(arguments))}, t.defaults = {}, t.remove = function(n, o){t(n, "", e(o, {expires: - 1}))}, t.withConverter = n, t}return n(function(){})});

    $( document ).ready(function() {

        //look for contact fields in the query
        var params_raw = get_query();

        var params = clear_params(params_raw);

        //if params are not there, look in the cookie
        if ( $.isEmptyObject( params ) ) {
            params = getCookie();
        } else {
            //otherwise save params in cookie
            setCookie( params );
        }

        $.each( params, function( key, value ) {
            var qntnField = $("[data-qntn-field-name="+key+"]"); //selector
            if ( qntnField.length ) {
                if ( qntnField.is( ':radio' ) ) {
                    $( 'input[type=radio][name="'+ qntnField.attr('name') + '"][value="'+ value + '"]' ).prop( 'checked', true );
                } else if ( qntnField.is( ':checkbox' ) &&  value === '1' ) { //if value of checkbox field is 1, we assume it is acceptance field
                    qntnField.prop( 'checked', true );
                } else if ( qntnField.is( ':checkbox' ) ) { //take multiple values for checkbox and separate with comma
                    var options = value.split( "," );
                    options.forEach( function ( item, index ) {
                        $('input[type=checkbox][name="'+qntnField.attr( 'name' )+'"][value="' + item + '"]').prop( 'checked', true );
                    });
                } else if ( qntnField.is( ':input' ) || qntnField.is( ':select' ) ) {
                    qntnField.val( value );
                }
            }
        });
    });

    /**
     * Get quentn cookie
     *
     * @returns object
     */
    function getCookie() {
        var cookie = Cookies.getJSON( 'qntnElementor' );
        if (!cookie) {
            cookie = {};
        }
        return cookie;
    }

    /**
     * Set cookie
     *
     * @param object cookie
     */
    function setCookie(cookie) {
        Cookies.set( 'qntnElementor', cookie );
    }

    /**
     * get URL query
     */
    function get_query() {
        var url_raw = location.search;
        var url_decode = decodeURIComponent( url_raw );
        var url = url_decode.replace(/\+/g,' ');
        var qs = url.substring( url.indexOf( '?' ) + 1 ).split( '&' );
        for ( var i = 0, result = {}; i < qs.length; i++ ) {
            qs[i] = qs[i].split( '=' );
            if ( qs[i][0] ) {
                result[qs[i][0]] = qs[i][1] ;
            }
        }
        return result;
    }

    /**
     * remove fields which were not substituted params
     */
    function clear_params(params_raw) {
        var clean_params = {};
        $.each( params_raw, function( key, value ) {
            if (! ( value.indexOf('[') > -1 ) ) {
                clean_params[key] = value;
            }
        });
        return clean_params;
    }
} )( jQuery );