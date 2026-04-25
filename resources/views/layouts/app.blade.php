<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f06a0f">
    <title>{{ $title ?? config('app.name', 'Shopio') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Shopio3.jpeg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Shopio3.jpeg') }}">


    {{-- ══ Ressource hints (connexions anticipées) ══ --}}
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- ══ Bootstrap CSS — chargement normal (bloquant voulu : évite le flash de page non stylée) ══ --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand1: #059669;
            --brand2: #16a34a;
            --text:   #1f2937;
            --muted:  #6b7280;
            --card:   #ffffff;
            --bg:     #f8fafc;
        }

        @media (prefers-color-scheme: dark) {
            :root { --text:#e5e7eb; --muted:#9aa3b2; --card:#0f172a; --bg:#0b1220; }
            .dropdown-menu  { background: var(--card); color: var(--text); }
            .dropdown-item  { color: var(--text); }
            .dropdown-item:hover { background: rgba(255,255,255,.06); }
            footer { background: var(--card); }
        }

        html, body { height: 100%; margin: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Navbar (masquée sur les pages avec sidebar) ── */
        .navbar {
            background: linear-gradient(90deg, var(--brand1), var(--brand2));  /*couleurs de la navbar*/
            box-shadow: 0 8px 24px rgba(22,163,74,.20);
        }
        .navbar-brand { font-weight: 700; letter-spacing: .2px; }
        .nav-link { font-weight: 500; }
        .nav-link:hover { opacity: .95; }

        /* ── Sur les pages "dashboard" : on cache navbar + footer + on neutralise le main ── */
        body.is-dashboard .app-navbar  { display: none !important; }
        body.is-dashboard .app-footer  { display: none !important; }
        body.is-dashboard .app-flash   { display: none !important; }
        body.is-dashboard > main.app-main {
            padding: 0 !important;
            margin:  0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        /* ── Pages normales ── */
        .page-header { padding: 28px 0 6px; }
        .page-header h1 {
            font-size: clamp(1.25rem, 2.2vw, 1.8rem);
            font-weight: 700; margin: 0;
        }
        .card { background: var(--card); border: none; box-shadow: 0 8px 30px rgba(0,0,0,.06); }
        .card-header { background: transparent; border-bottom: 1px solid rgba(0,0,0,.06); }

        footer {
            background: #ffffff;
            border-top: 1px solid rgba(0,0,0,.06);
            color: var(--muted);
        }

        .btn-ghost {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.25); color: #fff; }

        /* ── Pagination Bootstrap 5 — style global ── */
        .pagination { gap: 3px; flex-wrap: wrap; }
        .page-link {
            border-radius: 8px !important;
            border: 1px solid #dee2e6;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 13px;
            transition: all .15s;
            background: #fff;
        }
        .page-link:hover {
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #059669;
        }
        .page-item.active .page-link {
            background: #10b981;
            border-color: #059669;
            color: #fff;
            box-shadow: 0 2px 8px rgba(16,185,129,.35);
        }
        .page-item.disabled .page-link {
            opacity: .45;
            background: #f9fafb;
        }
        /* Conteneur centré */
        .pagination-wrap {
            display: flex;
            justify-content: center;
            padding: 16px 0 4px;
        }
    </style>

    @stack('styles')
</head>
<body class="{{ $bodyClass ?? '' }}">

    {{-- ═══ NAVBAR (cachée sur dashboard via body.is-dashboard) ═══ --}}
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top app-navbar">
        <div class="container-xxl">
             <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <img src="{{ asset('images/Shopio2.jpeg') }}" alt="Shopio" style="height:32px;width:auto;object-fit:contain">
                {{ config('app.name', 'Shopio') }}
            </a>
           

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#topnav" aria-controls="topnav"
                    aria-expanded="false" aria-label="Ouvrir la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="topnav" class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto"></ul>
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-light text-success fw-semibold" href="{{ route('register') }}">
                                <i class="bi bi-pencil-square me-1"></i> Inscription
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                               id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="d-none d-sm-inline">{{ Str::limit(auth()->user()->name, 22) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    {{-- ═══ TITRE OPTIONNEL (pages normales seulement) ═══ --}}
    @isset($title)
        @if(!isset($bodyClass) || !str_contains($bodyClass, 'is-dashboard'))
        <header class="page-header">
            <div class="container-xxl">
                <h1>{{ $title }}</h1>
            </div>
        </header>
        @endif
    @endisset

    {{-- ═══ FLASH (pages normales seulement) ═══ --}}
    <div class="container-xxl mt-3 app-flash">
        @foreach (['success','info','warning','danger'] as $type)
            @if(session($type))
                <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                    {{ session($type) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif
        @endforeach
    </div>

    {{-- ═══ CONTENU ═══ --}}
    <main class="app-main container-xxl py-4">
        @yield('content')
    </main>

    {{-- ═══ FOOTER (caché sur dashboard) ═══ --}}
    <footer class="py-4 mt-5 app-footer">
        <div class="container-xxl text-center small">
            &copy; {{ date('Y') }} {{ config('app.name', 'Application') }} &middot; Laravel & Bootstrap
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    @stack('scripts')

</body>
</html>