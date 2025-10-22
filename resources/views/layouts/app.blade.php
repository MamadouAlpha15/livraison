<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <!-- ✅ Rendre responsive sur mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ✅ Protection CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ✅ Titre de page (tombe à 'Application' si $title absent) -->
    <title>{{ $title ?? config('app.name', 'Application') }}</title>

    <!-- ✅ Favicon / Icône de projet (remplace par ton fichier si tu veux) -->
    <!-- Place un fichier public/images/logo.svg et dé-commente la ligne suivante -->
    <!-- <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}"> -->
    <!-- Fallback png -->
    <!-- <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}"> -->

    <!-- ✅ Bootstrap & Icons (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- ✅ Styles globaux : simples, élégants, responsive -->
    <style>
        :root{
            /* 🎨 Couleurs projet : change juste ici */
            --brand1:#059669;  /* vert émeraude */
            --brand2:#16a34a;  /* vert plus clair */
            --text:#1f2937;    /* gris foncé lisible */
            --muted:#6b7280;   /* gris secondaire */
            --card:#ffffff;    /* fond cartes */
            --bg:#f8fafc;      /* fond page */
        }

        /* 🌙 Mode sombre auto si le système est en dark */
        @media (prefers-color-scheme: dark){
            :root{
                --text:#e5e7eb;
                --muted:#9aa3b2;
                --card:#0f172a;
                --bg:#0b1220;
            }
            .dropdown-menu{ background: var(--card); color: var(--text); }
            .dropdown-item{ color: var(--text); }
            .dropdown-item:hover{ background: rgba(255,255,255,.06); }
            footer{ background: var(--card); }
        }

        html,body{ height: 100%; }
        body{
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ✅ Navbar : dégradé vert + ombre légère (plus moderne) */
        .navbar{
            background: linear-gradient(90deg, var(--brand1), var(--brand2));
            box-shadow: 0 8px 24px rgba(22,163,74,.20);
        }
        .navbar-brand{
            font-weight: 700;
            letter-spacing:.2px;
        }
        .nav-link{ font-weight: 500; }
        .nav-link:hover{ opacity:.95; }

        /* ✅ Titres de page optionnels (adaptatifs) */
        .page-header{ padding: 28px 0 6px; }
        .page-header h1{
            font-size: clamp(1.25rem, 2.2vw, 1.8rem);
            font-weight: 700; margin:0;
        }

        /* ✅ Cartes sobres (ombre douce) */
        .card{ background: var(--card); border: none; box-shadow: 0 8px 30px rgba(0,0,0,.06); }
        .card-header{ background: transparent; border-bottom: 1px solid rgba(0,0,0,.06); }

        /* ✅ Footer discret */
        footer{
            background: #ffffff;
            border-top: 1px solid rgba(0,0,0,.06);
            color: var(--muted);
        }

        /* (Optionnel) Bouton translucide sur la navbar */
        .btn-ghost{
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
        }
        .btn-ghost:hover{ background: rgba(255,255,255,.25); color:#fff; }
    </style>

    @stack('styles')
</head>
<body>
    <!-- ==================== NAVBAR ==================== -->
    <!-- 💡 Utilise container-xxl pour respirer sur PC grand écran -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-xxl">

            <!-- 🏠 Logo + nom : icône + texte "Accueil" qui renvoie vers / -->
            <!-- Remplace l'icône par ton image: 
                 <img src="{{ asset('images/logo.svg') }}" alt="Logo" height="22" class="me-2"> -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-bag-check-fill"></i> {{-- icône de projet (change-la si tu veux) --}}
                Accueil
            </a>

            <!-- Burger mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav"
                    aria-controls="topnav" aria-expanded="false" aria-label="Ouvrir la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="topnav" class="collapse navbar-collapse">
                <!-- Liens à gauche (laisser vide = navbar clean) -->
                <ul class="navbar-nav me-auto"></ul>

                <!-- À droite : invité = Connexion/Inscription, connecté = Profil + Déconnexion -->
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
                        <!-- Menu profil minimaliste -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="d-none d-sm-inline">{{ Str::limit(auth()->user()->name, 22) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                                <!-- Ajoute ici un lien "Mon tableau de bord" si tu veux -->
                                <!-- <li><a class="dropdown-item" href="{{ route('client.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Tableau de bord</a></li> -->
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

    <!-- ==================== EN-TÊTE OPTIONNEL ==================== -->
    @isset($title)
        <!-- 💡 Ici on garde un container large pour aligner avec la navbar -->
        <header class="page-header">
            <div class="container-xxl">
                <h1>{{ $title }}</h1>
            </div>
        </header>
    @endisset

    <!-- ==================== ALERTES FLASH ==================== -->
    <div class="container-xxl mt-3">
        @foreach (['success','info','warning','danger'] as $type)
            @if(session($type))
                <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                    {{ session($type) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif
        @endforeach
    </div>

    <!-- ==================== CONTENU ==================== -->
    <!-- 💡 container-xxl pour PC, Bootstrap s’occupe d’être fluide sur mobile -->
    <main class="container-xxl py-4">
        @yield('content')
    </main>

    <!-- ==================== PIED DE PAGE ==================== -->
    <footer class="py-4 mt-5">
        <div class="container-xxl text-center small">
            &copy; {{ date('Y') }} {{ config('app.name', 'Application') }} &middot; Laravel & Bootstrap
        </div>
    </footer>

    <!-- ✅ Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
