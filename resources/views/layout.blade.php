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
	
	{!! HTML::script('js/jquery-2.1.4.min.js') !!}
    {!! HTML::script('js/login.js') !!}
    {!! HTML::script('js/snabbt.min.js') !!}
</body>
</html>