@extends('login-layout')

@section('header')
@endsection

@section('main')
<div class="background-mogs-container">

</div>
<div class="background-cover">
    
</div>
<div class="login-container">
        <div class="login-container-content">
            <div class="register-login">
                <div class="register">
                    <a>Register</a>
                </div>
                <div class="login login-selected">
                    <a>Login</a>
                </div>
            </div>
            <div class="logo-container">
                <img src="../../images/memeslam.png" alt="meme-slam">
            </div>

            <form method="POST" action="/auth/login">
                {!! csrf_field() !!}
                <div class="fields-container">
                    <input type="email" name="email" placeholder="Email Address">
                    <input id="username"type="text" name="name" value="" placeholder="Username" hidden>
                    <input type="password" name="password" id="password" placeholder="Password">
                    <input id="conf-pass" type="password" name="password_confirmation" placeholder="Confirm Password" hidden>
                </div>
                <div id="remember-me">
                    <label>
                        <input type="checkbox" name="remember">  Remember Me  
                    </label>
                    
                </div>
                <button class="btn-submit">
                    Login
                </button>
            
            </form>
        </div>
    </div>
@endsection