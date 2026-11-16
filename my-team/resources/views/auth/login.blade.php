<x-layouts.auth title="Welcome back">
<p class="auth-intro">Sign in to your team workspace.</p>
@include('components.feedback')
<form method="POST" action="{{ route('login') }}" class="auth-fields">@csrf
<div><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @error('email') aria-describedby="email-error" @enderror>@error('email')<p id="email-error" class="auth-error">{{ $message }}</p>@enderror</div>
<div><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password"  required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @error('password') aria-describedby="password-error" @enderror>@error('password')<p id="password-error" class="auth-error">{{ $message }}</p>@enderror</div>
<button type="submit" class="product-cta">Sign in</button></form>
<p class="auth-switch">New to FTMBALL? <a href="{{ route('register.form') }}">Create an account</a></p></x-layouts.auth>