/* Write here your custom javascript codes */
function browserSupportsCSSProperty(propertyName) {
    var elm = document.createElement('div');
    propertyName = propertyName.toLowerCase();

    if (elm.style[propertyName] !== undefined) {
        return true;
    }
    var propertyNameCapital = propertyName.charAt(0).toUpperCase() + propertyName.substr(1), domPrefixes = 'Webkit Moz ms O'.split(' ');

    for (var i = 0; i < domPrefixes.length; i++) {
        if (elm.style[domPrefixes[i] + propertyNameCapital] !== undefined) {
            return true;
        }
    }

    return false;
}

$(document).ready(function(){
    /**
     * Animation script
     */
    $animateIn = $(".animate-in");
    var animateInOffset = 100;

    // Only animate in elements if the browser supports animations
    if (browserSupportsCSSProperty('animation') && browserSupportsCSSProperty('transition')) {
        $animateIn.addClass("pre-animate");
    }

    $(window).scroll(function(e) {
        var windowHeight = $(window).height(),
            windowScrollPosition = $(window).scrollTop(),
            bottomScrollPosition = windowHeight + windowScrollPosition;

        $animateIn.each(function(i, element) {
            if ($(element).offset().top + animateInOffset < bottomScrollPosition) {
                $(element).removeClass('pre-animate');
            }
        });
    });

    /**
     * Checks if cookies are working
     */
    Cookies.set('cookiesEnabled', true);
    if(typeof Cookies.get('cookiesEnabled') == 'undefined') {
        noty({
            layout: 'topCenter',
            theme: 'bootstrapTheme',
            type: 'error',
            text: 'Missing cookie support! Cookies are disabled on your browser. Please enable cookies.',
            animation: {
                open: 'animated bounceInLeft',
                close: 'animated bounceOutRight'
            },
            timeout: false,
            closeWith: ['button']
        });
    }
    Cookies.remove('cookiesEnabled');

    /**
     * Cookie usage reminder
     */
    if(typeof Cookies.get('acceptCookies') == 'undefined') {
        Cookies.set('acceptCookies', 'true', { expires: 365, domain: 'orbitrondev.org' });
        noty({
            layout: 'topCenter',
            theme: 'bootstrapTheme',
            type: 'info',
            text: 'We use cookies to give you the best experience on our website. By continuing to use our site you consent to our cookies.',
            animation: {
                open: 'animated bounceInLeft',
                close: 'animated bounceOutRight'
            },
            timeout: false,
            closeWith: ['button']
        });
    }
});