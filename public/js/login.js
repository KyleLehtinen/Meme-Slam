$(function(){

    loadBackground();

    //toggles login form for register mode
    $('.register').on('click', function() {
        $('form').attr('action', '/auth/register');
        $('#conf-pass').removeAttr('hidden');
        $('#username').removeAttr('hidden');
        $('#remember-me').attr('hidden','');

        $('.register-login').children().removeClass('login-selected');
        $(this).addClass('login-selected');
    });

    //toggles form back to login mode
    $('.login').on('click', function() {
        $('form').attr('action', '/auth/login');
        $('#conf-pass').attr('hidden','');
        $('#username').attr('hidden','');
        $('#remember-me').removeAttr('hidden');
        
        $('.register-login').children().removeClass('login-selected');
        $(this).addClass('login-selected');
    });

    function getRandomArbitrary(min, max) {
        return Math.random() * (max - min) + min;
    }

    function loadBackground() {
        $.ajax({
            url: '/api/get_background_mogs',
        }).done(function(data){
            for(var i = 0; i < 105; i++) {

                var randomIdx = Math.floor(getRandomArbitrary(1,data.length));
                var style = 'background-image: url(\/images\/mogs\/' + data[randomIdx] + ')';

                $('.background-mogs-container').append('<div class=\"mog-img\" style=\"' + style + '\"></div>');
            }
        }).error(function(){
            console.log("Couldn't generate background...");
        });
    }
});