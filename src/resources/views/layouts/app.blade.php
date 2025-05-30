<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 勤怠管理')</title>

    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="{{ asset('css/base/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout/app.css') }}">
    @yield('css')
</head>

@yield('scripts')
<body>
    <header>
        <div class="header-inner">
            <a href="">
                <img class="header-logo" src="{{ asset('image/logo.svg') }}" alt="COACHTECHロゴ">
            </a>

            <nav class="header-nav">
                {{-- @guest

                @else
                    @if(Auth::guard('admin')->check())
                    <ul>
                        <li><a href="">勤怠一覧</a></li>
                        <li><a href="">スタッフ一覧</a></li>
                        <li><a href="">申請一覧</a></li>
                        <li>
                            <form method="POST" action="">
                                @csrf
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                    @else
                        @if(isset($workingStatus) && $workingStatus === 'done')
                        <ul>
                            <li><a href="">今月の出勤一覧</a></li>
                            <li><a href="">申請一覧</a></li>
                            <li>
                                <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit">ログアウト</button>
                </form>
                </li>
                </ul>
                @else --}}
                <ul>
                    <li><a href="">勤怠</a></li>
                    <li><a href="">勤怠一覧</a></li>
                    <li><a href="">申請</a></li>
                    <li>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit">ログアウト</button>
                        </form>
                    </li>
                </ul>
                {{-- @endif
                @endif
                @endguest --}}
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>