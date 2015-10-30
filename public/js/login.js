$(function(){

    loadBackground();

    //toggles login form for register mode
    $('.register').on('click', function() {
        $('form').attr('action', '/auth/register');
        $('#conf-pass').removeAttr('hidden');
        $('#username').removeAttr('hidden');
        $('#remember-me').attr('hidden','');
        $('.btn-submit').text('Register');

        $('.register-login').children().removeClass('login-selected');
        $(this).addClass('login-selected');
    });

    //toggles form back to login mode
    $('.login').on('click', function() {
        $('form').attr('action', '/auth/login');
        $('#conf-pass').attr('hidden','');
        $('#username').attr('hidden','');
        $('#remember-me').removeAttr('hidden');
        $('.btn-submit').text('Login');
        $('.register-login').children().removeClass('login-selected');
        $(this).addClass('login-selected');
    });

    function getRandomArbitrary(min, max) {
        return Math.random() * (max - min) + min;
    }

    function loadBackground() {

        var w = window.innerWidth;
        var h = window.innerHeight;    

        var multiplier = Math.floor(h /120) + 1;
        var count = (Math.floor((multiplier * (w / 120)))) + 5;

        // for(var i = 0; i < )

        $.ajax({
            url: '/api/get_background_mogs/',
        }).done(function(data){
            for(var j = 0; j < count; j++) {

                var randomIdx = Math.floor(getRandomArbitrary(1,data.length));
                var style = 'background-image: url(\/images\/mogs\/' + data[randomIdx] + ')';

                $('.background-mogs-container').append('<div class=\"mog-img\" style=\"' + style + '\"></div>');
            }
        }).error(function(){
            console.log("Couldn't generate background...");
        });
    }
});