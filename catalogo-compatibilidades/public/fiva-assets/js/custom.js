(function($){
    "use strict";

    jQuery(document).on('ready', function () {
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 30) {
                $('.top-navbar').addClass('is-sticky');
            } else {
                $('.top-navbar').removeClass('is-sticky');
            }
        });

        $('.burger-menu').on('click', function() {
            $(this).toggleClass('active');
            $('.main-content').toggleClass('hide-sidemenu-area');
            $('.sidemenu-area').toggleClass('toggle-sidemenu-area');
            $('.top-navbar').toggleClass('toggle-navbar-area');
        });

        $('.responsive-burger-menu').on('click', function() {
            $('.responsive-burger-menu').toggleClass('active');
            $('.sidemenu-area').toggleClass('active-sidemenu-area');
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(function () {
            $('#sidemenu-nav').metisMenu();
        });

        $('#fullscreen-button').on('click', function toggleFullScreen() {
            var doc = document;
            var docEl = document.documentElement;

            var isFull =
                doc.fullScreenElement ||
                doc.msFullscreenElement ||
                doc.mozFullScreen ||
                doc.webkitIsFullScreen;

            if (!isFull) {
                if (docEl.requestFullScreen) {
                    docEl.requestFullScreen();
                } else if (docEl.mozRequestFullScreen) {
                    docEl.mozRequestFullScreen();
                } else if (docEl.webkitRequestFullScreen) {
                    docEl.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
                } else if (docEl.msRequestFullscreen) {
                    docEl.msRequestFullscreen();
                }
            } else {
                if (doc.cancelFullScreen) {
                    doc.cancelFullScreen();
                } else if (doc.mozCancelFullScreen) {
                    doc.mozCancelFullScreen();
                } else if (doc.webkitCancelFullScreen) {
                    doc.webkitCancelFullScreen();
                } else if (doc.msExitFullscreen) {
                    doc.msExitFullscreen();
                }
            }
        });

        $('.bx-fullscreen-btn').on('click', function() {
            $(this).toggleClass('active');
        });

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}(jQuery));
