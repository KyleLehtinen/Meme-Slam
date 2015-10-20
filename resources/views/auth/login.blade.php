@extends('layout')

@section('header')
@endsection

@section('main')
<div class="background">
    <div class="login-container">
        <div class="login-container-content">
            <div class="register-login">
                <div class="register">
                    <a>Register</a>
                </div>
                <div class="login">
                    <a>Login</a>
                </div>
            </div>
            <div class="logo-container">
                <img src="img/logo.jpg" alt="meme-slam">
            </div>

            <form method="POST" action="/auth/login">
                {!! csrf_field() !!}
                <div class="fields-container">
                    <input type="email" name="email" placeholder="Enter E-mail">
                    <input id="username"type="text" name="name" value="" placeholder="Username" hidden>
                    <input type="password" name="password" id="password" placeholder="Password">
                    <input id="conf-pass" type="password" name="password_confirmation" placeholder="Confirm Password" hidden>
                </div>
                <div id="remember-me" hidden>
                    <label>
                        <input type="checkbox" name="remember">  Remember Me  
                    </label>
                    
                </div>

                <div class="btn-submit">
                   <button>
                       Submit
                   </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection