<a href="#main-content" class="skip-link">Skip to content</a>
<header class="product-header">
    <a class="product-brand" href="{{ session('user_id') ? route('dashboard') : url('/') }}"><span aria-hidden="true">◈</span> FTMBALL <small>TEAM WORKSPACE</small></a>
    <details class="product-menu" open>
        <summary>Menu <span aria-hidden="true">☰</span></summary>
        <nav aria-label="Main navigation">
            @if(session('user_id'))
                @foreach(['dashboard'=>'Overview','teams.index'=>'Teams','matches.index'=>'Matches','trainings.index'=>'Training','formations.index'=>'Formations','chat.index'=>'Chat','profile.show'=>'Account'] as $destination=>$label)
                    <a href="{{ route($destination) }}" @if(request()->routeIs($destination === 'dashboard' ? 'dashboard' : explode('.', $destination)[0].'.*')) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
                <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Sign out</button></form>
            @else
                <a href="{{ url('/') }}#features">Features</a>
                <a href="{{ url('/') }}#how-it-works">How it works</a>
                <a href="{{ route('login.form') }}">Sign in</a>
                <a class="product-cta" href="{{ route('register.form') }}">Create account</a>
            @endif
        </nav>
    </details>
</header>
