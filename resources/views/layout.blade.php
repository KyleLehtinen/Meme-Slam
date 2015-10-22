<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Meme Slam | Welcome</title>
	{!! HTML::style('css/app.css') !!}
</head>
<body>

	@yield('header')
	@yield('main')
	
	{!! HTML::script('js/jquery.js') !!}
	{!! HTML::script('js/jquery-ui.min.js') !!}
    {!! HTML::script('js/login.js') !!}
    {!! HTML::script('js/snabbt.min.js') !!}
    {!! HTML::script('js/dashboard.js') !!}
</body>
</html>