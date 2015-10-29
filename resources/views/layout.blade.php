<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Meme Slam</title>
	<meta name="csrf_token" content="{{ $encrypted_csrf_token }}" />
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
    {!! HTML::script('js/memeslam.js') !!}
    {{-- {!! HTML::script('js/memeslam2.js') !!} --}}
</body>
</html>