/**
 * Slider Editor
 *
 * @copyright Commercial License By PavoThemes.Com
 * @email pavothemes@gmail.com
 * @visit http://www.pavothemes.com
 */
(function( $ ) {

    $.fn.pavoNewsletter = function( options ) {
        var self = this;
        options = $.extend({}, {
            message: ''
        }, options);

        this.work = function(form){
            // var form = $( this ).find('form');
            // this.msg = options.message;
            switch( $(this).data('mode') ){
                case 'popup':
                    if( self.getCookie('cnewsletter') == 1 ){
                        return true;
                    }
                    self.showPopup( form );
                    break;
                case 'flybot' :
                    if( self.getCookie('cnewsletter') == 1 ){
                        return true;
                    }
                    self.flybot( form );
                    break;
            }

            self.submit( form );
        },

        this.flybot = function( form ){
            var container = $(form).parent();
            container.appendTo( 'body' );
            $(container).slideToggle();
            container.removeClass('hide').addClass('slide-bottom');
            $( '.button-slide', container ).click( function(){
                $(container).slideToggle();
            } );
            $(':checkbox', container ).click( function(){
                self.setCookie( 'cnewsletter', '1', 10 );
                $(container).slideToggle();
            } );
        },

        this.showPopup = function ( form ){
            $.magnificPopup.open({
                items: {
                    src: form,
                    type: 'inline',
                    width:"500"
                 },

            });
            $(':checkbox', form ).click( function(){
                self.setCookie( 'cnewsletter', '1', 10 );
                $.magnificPopup.close();
            } );
        },
        this.setCookie = function ( cname, cvalue, exdays ) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        },

        this.getCookie = function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for(var i = 0; i <ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },
        this.isValidEmailAddress = function (emailAddress) {
            var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
            return pattern.test(emailAddress);
        },

        this.submit = function ( _form ) {
            $( _form ).on('submit', function() {
                var email = $('.inputNew').val();
                $(".success_inline, .warning_inline, .error").remove();
                if(!self.isValidEmailAddress(email)) {
                    _form.find('.valid').html("<div class=\"error alert alert-danger\">"+options.message+"<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div></div>");
                    _form.find('.inputNew').focus();
                    return false;
                }
                var url = $(_form).attr('action');
                $.ajax({
                    type: "post",
                    url: url,
                    data: $( _form ).serialize(),
                    dataType: 'json',
                    success: function(json)
                    {
                        $(".success_inline, .warning_inline, .error").remove();
                        if (json['error']) {
                            _form.find('.valid').html("<div class=\"warning_inline alert alert-danger\">"+json['error']+"<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>");
                        }
                        if (json['success']) {
                            _form.find('.valid').html("<div class=\"success_inline alert alert-success\">"+json['success']+"<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button></div>");
                        }
                    }
                });
                return false;
            });
        }
        return $(this).each(function() {
            self.work($(this).find('form'));
        });
        //THIS IS VERY IMPORTANT TO KEEP AT THE END
        // return this;
    };

})( jQuery );
/***/