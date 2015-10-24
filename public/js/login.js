$(function(){
    //toggles login form for register mode
    $('.register').on('click', function() {
        $('form').attr('action', '/auth/register');
        $('#conf-pass').removeAttr('hidden');
        $('#username').removeAttr('hidden');
        $('#remember-me').attr('hidden','');
    });

    //toggles form back to login mode
    $('.login').on('click', function() {
        $('form').attr('action', '/auth/login');
        $('#conf-pass').attr('hidden','');
        $('#username').attr('hidden','');
        $('#remember-me').removeAttr('hidden');
    });
});