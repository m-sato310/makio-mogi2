<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 勤怠管理')</title>
</head>

<body>
    <header>
        <div class="header-inner">
            <a href="">
                <img class="header-logo" src="{{ asset('img/logo.svg') }}" alt="COACHTECHロゴ">
            </a>

            <nav class="header-nav">
                @guest
                <!-- ログイン前はナビゲーションなし -->

                @else
                    @if(Auth::guard('admin')->check())
                    <!-- 管理者ログイン時 -->
                        <ul>
                            <!-- リンクは後で設定 -->
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
                            <!-- 一般ユーザー：退勤済の時 -->
                            <ul>
                                <!-- リンクは後で設定 -->
                                <li><a href="">今月の出勤一覧</a></li>
                                <li><a href="">申請一覧</a></li>
                                <li>
                                    <form method="POST" action="">
                                        @csrf
                                        <button type="submit">ログアウト</button>
                                    </form>
                                </li>
                            </ul>
                        @else
                            <!-- 一般ユーザー：勤務外・出勤中・休憩中の時 -->
                            <ul>
                                <!-- リンクは後で設定 -->
                                <li><a href="">勤怠</a></li>
                                <li><a href="">勤怠一覧</a></li>
                                <li><a href="">申請</a></li>
                                <li>
                                    <form method="POST" action="">
                                        @csrf
                                        <button type="submit"></button>
                                    </form>
                                </li>
                            </ul>
                        @endif
                    @endif
                @endguest
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>