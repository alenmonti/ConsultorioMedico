<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Pacientes — Reservar turno</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --verde: #085C68;
            --verde-hover: #064e58;
            --verde-light: #e0f2f4;
            --crema: #f8f9f7;
            --crema-border: #e5e5e5;
            --texto: #1c1c1c;
            --texto-muted: #6b7280;
            --wsp: #25D366;
            --wsp-hover: #1da851;
            --danger: #c0392b;
            --slot-taken: #d1d5db;
            --card-bg: #ffffff;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--crema);
            color: var(--texto);
            min-height: 100vh;
        }

        /* header ~64px + footer ~55px */
        @media (min-width: 900px) {
            .page { min-height: calc(100vh - 119px); }
        }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            background: #405b6a;
            width: 100%;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header img { height: 36px; object-fit: contain; flex-shrink: 0; }
        .header-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 500;
            color: #fff;
            letter-spacing: .01em;
        }
        .header-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .header-label-line { width: 20px; height: 1px; background: rgba(255,255,255,0.35); }
        .header-label-text {
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
        }

        /* ── Layout ── */
        .page {
            max-width: 480px;
            margin: 0 auto;
            padding: 24px 16px 80px;
        }

        @media (min-width: 900px) {
            .page { max-width: 960px; padding: 40px 32px 60px; }
        }

        /* ── PC two-col ── */
        .pc-layout {
            display: flex;
            gap: 48px;
            align-items: flex-start;
        }
        .pc-left {
            flex: 1;
            padding-top: 8px;
            display: flex;
            flex-direction: column;
        }
        .pc-left-tag {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--verde);
            margin-bottom: 10px;
        }
        .pc-left-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 500;
            line-height: 1.15;
            color: #1c1c1c;
            margin-bottom: 16px;
        }
        .pc-left-title .brand { color: var(--verde); }
        .pc-left-accent {
            width: 48px;
            height: 2px;
            background: #E8A598;
            margin-bottom: 20px;
        }
        .pc-left-sub {
            font-size: 14px;
            color: var(--texto-muted);
            line-height: 1.6;
            max-width: 320px;
        }
        .pc-right { flex: 0 0 440px; width: 440px; }

        @media (max-width: 899px) {
            .pc-left { display: none; }
            .pc-layout { display: block; }
            .pc-right { width: 100%; }
        }

        /* ── Card ── */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            min-height: 320px;
        }

        /* ── Steps ── */
        .steps {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 24px;
        }
        .step-circle {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid var(--crema-border);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            color: var(--texto-muted);
            flex-shrink: 0;
            background: var(--card-bg);
            transition: all .2s;
        }
        .step-circle.active {
            background: var(--verde);
            border-color: var(--verde);
            color: #fff;
            box-shadow: 0 4px 12px rgba(8,92,104,0.25);
        }
        .step-circle.done {
            background: var(--verde);
            border-color: var(--verde);
            color: #fff;
        }
        .step-line {
            flex: 1;
            height: 2px;
            background: var(--crema-border);
            transition: background .2s;
        }
        .step-line.done { background: var(--verde); }
        .step-label {
            font-size: 11px;
            color: var(--texto-muted);
            text-align: right;
            margin-bottom: 4px;
        }

        /* ── Section heading ── */
        .section-tag {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--verde);
            margin-bottom: 6px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 500;
            color: var(--texto);
            margin-bottom: 20px;
        }

        /* ── Medico cards ── */
        .medico-list { display: flex; flex-direction: column; gap: 10px; margin-top: 50px; }
        .medico-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 1.5px solid var(--crema-border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: border-color .15s, background .15s;
            background: var(--card-bg);
        }
        @media (max-width: 899px) {
            .medico-card { padding: 18px 16px; gap: 16px; }
            .medico-avatar, .medico-avatar-placeholder { width: 60px; height: 60px; }
            .medico-nombre { font-size: 16px; }
        }
        .medico-card:hover { border-color: #7ab8c0; }
        .medico-card.selected { border-color: var(--verde); background: var(--verde-light); }
        .medico-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--crema);
            flex-shrink: 0;
        }
        .medico-avatar-placeholder {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--crema-border);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: var(--texto-muted);
            flex-shrink: 0;
        }
        .medico-info { flex: 1; min-width: 0; }
        .medico-nombre { font-size: 15px; font-weight: 600; }
        .medico-esp {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--verde);
            margin-bottom: 2px;
        }
        .medico-desc { font-size: 13px; color: var(--texto-muted); }
        .medico-radio {
            width: 20px; height: 20px;
            border-radius: 50%;
            border: 2px solid var(--crema-border);
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: border-color .15s;
        }
        .medico-card.selected .medico-radio {
            border-color: var(--verde);
            background: var(--verde);
        }
        .medico-card.selected .medico-radio::after {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #fff;
        }

        /* ── Back link ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: var(--texto-muted);
            cursor: pointer;
            margin-bottom: 18px;
            text-decoration: none;
        }
        .back-link:hover { color: var(--texto); }

        /* ── Week nav ── */
        .week-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .week-nav-label { font-size: 14px; font-weight: 600; }
        .week-btn {
            font-size: 13px;
            color: var(--texto-muted);
            cursor: pointer;
            border: none;
            background: none;
            padding: 4px 8px;
        }
        .week-btn:hover { color: var(--texto); }
        .week-btn:disabled { opacity: .3; cursor: default; }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            margin-bottom: 20px;
        }
        .day-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 4px;
            border-radius: var(--radius-sm);
            border: 2px solid var(--crema-border);
            cursor: pointer;
            transition: border-color .15s, background .15s;
            background: var(--card-bg);
        }
        .day-card:hover:not(.day-unavailable):not(.day-pasado) { border-color: #7ab8c0; }
        .day-card.day-selected { border-color: var(--verde); background: var(--verde); }
        .day-card.day-unavailable, .day-card.day-pasado { opacity: .45; cursor: default; pointer-events: none; }
        .day-name { font-size: 10px; font-weight: 700; color: var(--texto-muted); letter-spacing: .06em; }
        .day-card.day-selected .day-name { color: rgba(255,255,255,.75); }
        .day-number { font-size: 18px; font-weight: 700; margin: 2px 0; }
        .day-card.day-selected .day-number { color: #fff; }
        .day-status {
            font-size: 10px;
            color: var(--texto-muted);
        }
        .day-card.day-selected .day-status { color: rgba(255,255,255,.8); }
        .day-status.status-pocos { color: #b45309; }
        .day-status.status-libre { color: var(--verde); }

        /* ── Time slots ── */
        .slots-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--texto-muted);
            margin-bottom: 10px;
            margin-top: 16px;
        }
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 4px;
        }
        .slot-btn {
            padding: 11px 8px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--crema-border);
            background: var(--card-bg);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: border-color .15s, background .15s, color .15s;
            text-align: center;
        }
        .slot-btn:hover { border-color: #7ab8c0; }
        .slot-btn.selected {
            background: var(--verde);
            border-color: var(--verde);
            color: #fff;
            box-shadow: 0 4px 12px rgba(8,92,104,0.2);
        }

        /* ── Inputs ── */
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--crema-border);
            border-radius: var(--radius-sm);
            font-size: 15px;
            background: var(--card-bg);
            color: var(--texto);
            outline: none;
            transition: border-color .15s;
        }
        .form-input:focus { border-color: var(--verde); }
        .form-input::placeholder { color: #b0a89e; }

        /* ── Info box ── */
        .info-box {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            background: var(--verde-light);
            border: 1px solid rgba(8,92,104,0.15);
            border-radius: var(--radius);
            padding: 16px;
            font-size: 13px;
            color: #374151;
            margin-bottom: 20px;
            line-height: 1.55;
        }
        .info-box-icon {
            width: 36px; height: 36px;
            background: var(--verde);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        /* ── Summary card ── */
        .summary-card {
            background: var(--crema);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 24px;
        }
        .summary-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .summary-row + .summary-row {
            margin-top: 4px;
            padding-top: 16px;
            border-top: 1px solid var(--crema-border);
        }
        .summary-item { display: flex; flex-direction: column; gap: 3px; }
        .summary-key {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .15em;
            color: var(--verde);
        }
        .summary-val {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            font-weight: 500;
            color: #1c1c1c;
            line-height: 1.2;
        }
        .summary-val.verde { color: var(--verde); }

        /* ── Buttons ── */
        .btn {
            display: block;
            width: 100%;
            padding: 15px 32px;
            border-radius: 9999px;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            cursor: pointer;
            text-align: center;
            transition: background .15s, opacity .15s, box-shadow .15s;
        }
        .btn:disabled { opacity: .45; cursor: default; }
        .btn-primary {
            background: var(--verde);
            color: #fff;
            box-shadow: 0 8px 24px rgba(8,92,104,0.2);
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--verde-hover);
            box-shadow: 0 8px 28px rgba(8,92,104,0.3);
        }
        .btn-wsp {
            background: var(--wsp);
            color: #fff;
            margin-bottom: 10px;
            box-shadow: 0 8px 24px rgba(37,211,102,0.2);
        }
        .btn-wsp:hover { background: var(--wsp-hover); }
        .btn-secondary {
            background: transparent;
            color: var(--texto-muted);
            border: 1.5px solid var(--crema-border);
        }
        .btn-secondary:hover { border-color: #7ab8c0; color: var(--texto); }

        /* ── Bottom bar ── */
        .bottom-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--card-bg);
            border-top: 1px solid var(--crema-border);
            padding: 12px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 10;
        }
        .wsp-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--wsp);
            text-decoration: none;
            margin-bottom: 2px;
        }
        .wsp-link:hover { text-decoration: underline; }
        .wsp-link-pc {
            justify-content: flex-start;
            font-size: 12px;
        }

        @media (min-width: 900px) {
            .bottom-bar { display: none; }
            .card { padding: 32px 32px 28px; }
        }

        /* ── Success / Error screens ── */
        .result-screen {
            text-align: center;
            padding: 8px 0;
        }
        .result-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px;
        }
        .result-icon.success { background: var(--verde); color: #fff; }
        .result-icon.error { background: #fdecea; color: var(--danger); }
        .result-title { font-size: 24px; font-weight: 700; margin-bottom: 10px; }
        .result-title.error-color { color: var(--danger); }
        .result-sub { font-size: 14px; color: var(--texto-muted); line-height: 1.6; margin-bottom: 24px; }

        /* ── Skeleton loader ── */
        .skeleton {
            background: linear-gradient(90deg, #e8ecea 25%, #f8f9f7 50%, #e8ecea 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        /* ── Misc ── */
        .hidden { display: none !important; }
        .pc-actions {
            display: none;
            flex-direction: column;
            gap: 12px;
            margin-top: 36px;
        }
        @media (min-width: 900px) { .pc-actions { display: flex; } }
        .pc-back-link {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .08em;
            color: var(--texto-muted);
            cursor: pointer;
            align-self: flex-start;
        }
        .pc-back-link:hover { color: var(--texto); }
        .pc-btn { width: auto; align-self: flex-start; padding: 14px 32px; }

        .fecha-label-str {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--texto-muted);
            margin-bottom: 6px;
        }

        .error-msg { color: var(--danger); font-size: 13px; margin-top: 6px; }

        /* ── Footer (solo PC) ── */
        .site-footer {
            display: none;
            width: 100%;
            border-top: 1px solid var(--crema-border);
            padding: 18px 32px;
            text-align: center;
            font-size: 11px;
            color: var(--texto-muted);
        }
        .site-footer a { color: var(--texto-muted); text-decoration: none; }
        .site-footer a:hover { color: var(--verde); }
        @media (min-width: 900px) { .site-footer { display: block; } }

        /* ── Card min-height mobile ── */
        @media (max-width: 899px) {
            .card { min-height: calc(100dvh - 64px - 110px - 48px); }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-brand">
        <img src="/logo-transparent.png" alt="Portal Pacientes">
        <span class="header-name">Portal Pacientes</span>
    </div>
</header>

<div class="page">
    <div class="pc-layout">
        <div class="pc-left">
            <div id="pc-left-content">
                <!-- updated dynamically per step -->
            </div>
            <!-- PC actions: WA + Continuar en panel izquierdo (solo desktop) -->
            <div class="pc-actions" id="pc-actions" style="display:none;">
                <a class="wsp-link wsp-link-pc" id="pc-wsp-link" href="#" target="_blank" rel="noopener">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    ¿Dudas? Consultanos por WhatsApp
                </a>
                <button class="btn btn-primary pc-btn" id="pc-main-btn" disabled>Continuar →</button>
                <span class="pc-back-link" id="pc-back-link"></span>
            </div>
        </div>
        <div class="pc-right">
            <div class="card" id="main-card">

                <!-- ══ STEP 1: Especialista ══ -->
                <div id="step-1">
                    <div class="step-label" id="step1-label">Paso 1 de 3</div>
                    <div class="steps" id="steps-1">
                        <div class="step-circle active">1</div>
                        <div class="step-line"></div>
                        <div class="step-circle">2</div>
                        <div class="step-line"></div>
                        <div class="step-circle">3</div>
                    </div>
                    <div class="section-tag">Paso 1 de 3</div>
                    <div class="section-title">Elegí a tu especialista</div>
                    <div class="medico-list" id="medico-list">
                        <div class="skeleton" style="height:80px;border-radius:12px;"></div>
                        <div class="skeleton" style="height:80px;border-radius:12px;"></div>
                    </div>
                </div>

                <!-- ══ STEP 2: Día y horario ══ -->
                <div id="step-2" class="hidden">
                    <div class="steps" id="steps-2">
                        <div class="step-circle done">✓</div>
                        <div class="step-line done"></div>
                        <div class="step-circle active">2</div>
                        <div class="step-line"></div>
                        <div class="step-circle">3</div>
                    </div>

                    <span class="back-link" id="back-to-1">‹ <span id="back-medico-label"></span></span>

                    <div class="section-title">Elegí día y horario</div>

                    <div class="week-nav">
                        <button class="week-btn" id="btn-prev-week" disabled>‹ anterior</button>
                        <span class="week-nav-label" id="week-label">Cargando…</span>
                        <button class="week-btn" id="btn-next-week">siguiente ›</button>
                    </div>

                    <div class="days-grid" id="days-grid">
                        <!-- filled dynamically -->
                    </div>

                    <div id="slots-section" class="hidden">
                        <div class="fecha-label-str" id="fecha-label-str"></div>
                        <div id="slots-manana-wrap">
                            <div class="slots-label">Mañana</div>
                            <div class="slots-grid" id="slots-manana"></div>
                        </div>
                        <div id="slots-tarde-wrap">
                            <div class="slots-label">Tarde</div>
                            <div class="slots-grid" id="slots-tarde"></div>
                        </div>
                        <div id="slots-empty" class="hidden" style="color:var(--texto-muted);font-size:14px;padding:12px 0;">No hay horarios disponibles para este día.</div>
                    </div>
                </div>

                <!-- ══ STEP 3: Tus datos ══ -->
                <div id="step-3" class="hidden">
                    <div class="steps" id="steps-3">
                        <div class="step-circle done">✓</div>
                        <div class="step-line done"></div>
                        <div class="step-circle done">✓</div>
                        <div class="step-line done"></div>
                        <div class="step-circle active">3</div>
                    </div>

                    <span class="back-link" id="back-to-2">‹ Cambiar día u horario</span>

                    <div class="section-title">Tus datos</div>

                    <div class="summary-card" id="step3-summary">
                        <!-- filled dynamically -->
                    </div>

                    <div class="form-group">
                        <label for="input-nombre">Nombre y apellido</label>
                        <input class="form-input" id="input-nombre" type="text" placeholder="Ej: María García" autocomplete="name">
                        <div class="error-msg hidden" id="err-nombre">Ingresá tu nombre completo.</div>
                    </div>
                    <div class="form-group">
                        <label for="input-wsp">WhatsApp</label>
                        <input class="form-input" id="input-wsp" type="tel" placeholder="Ej: 1112345678" autocomplete="tel">
                        <div class="error-msg hidden" id="err-wsp">Ingresá tu número de WhatsApp sin código de país (54) ni el 9. Solo código de área + número. Ej: 1112345678</div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <span>Te escribimos por <strong>WhatsApp en 24–48 hs hábiles</strong> para confirmar el turno y coordinar la seña.</span>
                    </div>
                </div>

                <!-- ══ STEP 4: Success ══ -->
                <div id="step-success" class="hidden">
                    <div class="result-screen">
                        <div class="result-icon success">✓</div>
                        <div class="result-title">¡Turno reservado!</div>
                        <div class="result-sub">Guardamos tu solicitud. Falta el último paso para confirmarlo.</div>

                        <div class="summary-card" id="success-summary" style="text-align:left;margin-bottom:20px;"></div>

                        <div class="info-box" style="margin-bottom:24px;">
                            <div class="info-box-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </div>
                            <span>Te contactamos por <strong>WhatsApp en 24–48 hs hábiles</strong> para confirmar y coordinar la seña.</span>
                        </div>

                        <button class="btn btn-secondary" id="btn-nuevo-turno">Reservar otro turno</button>
                    </div>
                </div>

                <!-- ══ STEP 5: Error ══ -->
                <div id="step-error" class="hidden">
                    <div class="result-screen">
                        <div class="result-icon error">!</div>
                        <div class="result-title error-color">No pudimos reservar tu turno</div>
                        <div class="result-sub">Tuvimos un problema al procesar la reserva y no quedó confirmada. No te preocupes: <strong>escribinos por WhatsApp y te atendemos para coordinar el turno enseguida.</strong></div>

                        <div class="info-box" style="margin-bottom:24px;">
                            <div class="info-box-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <span>Contanos qué especialista y horario querías y te respondemos en nuestro horario de atención.</span>
                        </div>

                        <a class="btn btn-wsp" id="error-wsp-btn" href="#" target="_blank" rel="noopener">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="display:inline;vertical-align:-2px;margin-right:6px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            Escribir por WhatsApp
                        </a>
                        <button class="btn btn-secondary" id="btn-reintentar">Reintentar reserva</button>
                    </div>
                </div>

            </div><!-- /.card -->
        </div><!-- /.pc-right -->
    </div><!-- /.pc-layout -->
</div><!-- /.page -->

<footer class="site-footer">
    © 2026 Portal Pacientes. Todos los derechos reservados — <a href="https://bewit.com.ar" target="_blank" rel="noopener">bewit.com.ar</a>
</footer>

<!-- Mobile bottom bar -->
<div class="bottom-bar" id="bottom-bar">
    <a class="wsp-link" id="mobile-wsp-link" href="#" target="_blank" rel="noopener">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        ¿Dudas? Consultanos por WhatsApp
    </a>
    <button class="btn btn-primary" id="mobile-main-btn" disabled>Continuar →</button>
</div>

<script>
(function () {
    // ── State ──
    const S = {
        step: 1,
        medicos: [],
        medicoId: null,
        medicoNombre: '',
        medicoEsp: '',
        medicoWsp: null,
        semanaDesde: null,
        fechaSeleccionada: null,
        horaSeleccionada: null,
        nombre: '',
        whatsapp: '',
    };

    // ── Helpers ──
    function $id(id) { return document.getElementById(id); }
    function show(id) { $id(id).classList.remove('hidden'); }
    function hide(id) { $id(id).classList.add('hidden'); }
    function isDesktop() { return window.innerWidth >= 900; }

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    async function apiFetch(url, opts = {}) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            ...opts
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Error al conectar con el servidor.');
        }
        return res.json();
    }

    function wspUrl(numero, texto = '') {
        const n = numero ? numero.replace(/\D/g, '') : '';
        if (!n) return '#';
        return `https://wa.me/${n}` + (texto ? `?text=${encodeURIComponent(texto)}` : '');
    }

    function setWspLinks(numero, texto = '') {
        const url = wspUrl(numero, texto);
        $id('mobile-wsp-link').href = url;
        const pcWsp = $id('pc-wsp-link');
        if (pcWsp) pcWsp.href = url;
    }

    // ── Render step indicator & PC left panel ──
    function renderUI() {
        // Update PC left text
        const pcLeft = $id('pc-left-content');
        const titles = [
            { tag: 'Portal Pacientes', title: 'Reservá<br>tu turno.', sub: 'Elegí especialista, día y horario. Después te contactamos por WhatsApp en 24–48 hs hábiles para confirmar y coordinar la seña.' },
            { tag: 'Portal Pacientes', title: 'Elegí día<br><span class="brand">y horario.</span>', sub: 'Seleccioná el día y el horario que más te convenga.' },
            { tag: 'Casi listo.', title: 'Tus<br><span class="brand">datos.</span>', sub: 'Dejanos tu nombre y WhatsApp. La reserva queda registrada y te confirmamos a la brevedad.' },
        ];
        const t = titles[Math.min(S.step - 1, 2)];
        if (t) {
            pcLeft.innerHTML = `<div class="pc-left-tag">${t.tag}</div><div class="pc-left-title">${t.title}</div><div class="pc-left-accent"></div><p class="pc-left-sub">${t.sub}</p>`;
        }

        // Show/hide steps
        ['step-1','step-2','step-3','step-success','step-error'].forEach(id => hide(id));
        const stepId = S.step <= 3 ? `step-${S.step}` : (S.step === 4 ? 'step-success' : 'step-error');
        show(stepId);

        // Bottom bar (solo mobile — en desktop el CSS lo oculta vía media query)
        const bottomBar = $id('bottom-bar');
        if (S.step >= 4 || isDesktop()) {
            bottomBar.style.display = 'none';
        } else {
            bottomBar.style.display = 'flex';
        }

        updateMainBtn();
    }

    function updateMainBtn() {
        const label = btnLabel();
        const enabled = isBtnEnabled();
        ['mobile-main-btn', 'pc-main-btn'].forEach(id => {
            const btn = $id(id);
            if (!btn) return;
            btn.textContent = label;
            btn.disabled = !enabled;
        });

        // PC actions: visible en desktop para pasos 1–3
        const pcActions = $id('pc-actions');
        if (S.step <= 3) {
            pcActions.style.display = '';  // respeta el CSS (flex en ≥900px, none en mobile)
        } else {
            pcActions.style.display = 'none';
        }
    }

    function btnLabel() {
        if (S.step === 1) return 'Continuar →';
        if (S.step === 2) {
            if (S.fechaSeleccionada && S.horaSeleccionada) {
                const d = new Date(S.fechaSeleccionada + 'T00:00:00');
                const dia = d.toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'short' });
                return `Continuar · ${dia} · ${S.horaSeleccionada} →`;
            }
            return 'Continuar →';
        }
        if (S.step === 3) return 'Reservar turno';
        return 'Continuar →';
    }

    function isBtnEnabled() {
        if (S.step === 1) return !!S.medicoId;
        if (S.step === 2) return !!(S.fechaSeleccionada && S.horaSeleccionada);
        if (S.step === 3) return true;
        return false;
    }

    // ── Step 1: load medicos ──
    async function loadMedicos() {
        try {
            S.medicos = await apiFetch('/portal-turnos/medicos');
            renderMedicos();
        } catch (e) {
            $id('medico-list').innerHTML = `<p style="color:var(--danger);font-size:14px;">No se pudieron cargar los especialistas. Intentá de nuevo más tarde.</p>`;
        }
    }

    function renderMedicos() {
        const list = $id('medico-list');
        if (!S.medicos.length) {
            list.innerHTML = '<p style="color:var(--texto-muted);font-size:14px;">No hay especialistas disponibles en este momento.</p>';
            return;
        }
        list.innerHTML = S.medicos.map(m => `
            <div class="medico-card" data-id="${m.id}" data-nombre="${m.nombre}" data-esp="${m.especialidad||''}" data-wsp="${m.whatsapp||''}">
                ${m.foto
                    ? `<img class="medico-avatar" src="${m.foto}" alt="${m.nombre}">`
                    : `<div class="medico-avatar-placeholder">👤</div>`}
                <div class="medico-info">
                    <div class="medico-esp">${m.especialidad || ''}</div>
                    <div class="medico-nombre">${m.nombre}</div>
                    ${m.descripcion ? `<div class="medico-desc">${m.descripcion}</div>` : ''}
                </div>
                <div class="medico-radio"></div>
            </div>
        `).join('');

        list.querySelectorAll('.medico-card').forEach(card => {
            card.addEventListener('click', () => {
                list.querySelectorAll('.medico-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                S.medicoId = parseInt(card.dataset.id);
                S.medicoNombre = card.dataset.nombre;
                S.medicoEsp = card.dataset.esp;
                S.medicoWsp = card.dataset.wsp || null;
                setWspLinks(S.medicoWsp);
                updateMainBtn();
            });
        });

        // Seleccionar el primero por defecto
        const primero = list.querySelector('.medico-card');
        if (primero) primero.click();
    }

    // ── Step 2: week & slots ──
    let currentWeekDesde = null;

    function startOfWeek(date) {
        const d = new Date(date);
        const day = d.getDay();
        const diff = (day === 0 ? -6 : 1 - day);
        d.setDate(d.getDate() + diff);
        return d;
    }

    function addDays(date, n) {
        const d = new Date(date);
        d.setDate(d.getDate() + n);
        return d;
    }

    function fmtDate(d) {
        return d.toISOString().slice(0, 10);
    }

    async function loadSemana(desde, autoSelect = false, _attempts = 0) {
        $id('days-grid').innerHTML = Array(6).fill('<div class="skeleton" style="height:72px;border-radius:8px;"></div>').join('');
        hide('slots-section');
        S.fechaSeleccionada = null;
        S.horaSeleccionada = null;
        updateMainBtn();

        try {
            const data = await apiFetch(`/portal-turnos/semana?medico_id=${S.medicoId}&desde=${fmtDate(desde)}`);
            $id('week-label').textContent = data.semana_label;
            currentWeekDesde = new Date(data.desde + 'T00:00:00');
            renderDays(data.dias);

            // prev button: disable if week starts today or earlier
            const hoy = new Date(); hoy.setHours(0,0,0,0);
            $id('btn-prev-week').disabled = currentWeekDesde <= hoy;

            if (autoSelect) {
                const primer = $id('days-grid').querySelector('.day-card:not(.day-unavailable):not(.day-pasado)');
                if (primer) {
                    primer.click();
                } else if (_attempts < 8) {
                    // semana sin disponibilidad: avanzar automáticamente
                    loadSemana(addDays(currentWeekDesde, 7), true, _attempts + 1);
                }
            }
        } catch (e) {
            $id('days-grid').innerHTML = `<p style="grid-column:span 6;color:var(--danger);font-size:13px;">Error al cargar disponibilidad.</p>`;
        }
    }

    function renderDays(dias) {
        const grid = $id('days-grid');
        grid.innerHTML = dias.filter(d => new Date(d.fecha + 'T00:00:00').getDay() !== 0).map(d => {
            const unavailable = ['cerrado','lleno','pasado'].includes(d.estado);
            const statusText = { libre: 'libre', pocos: 'pocos', cerrado: 'cerrado', lleno: 'lleno', pasado: '' }[d.estado] || '';
            const statusClass = d.estado === 'libre' ? 'status-libre' : d.estado === 'pocos' ? 'status-pocos' : '';
            return `
                <div class="day-card ${unavailable ? 'day-unavailable' : ''} ${d.estado === 'pasado' ? 'day-pasado' : ''}" data-fecha="${d.fecha}">
                    <span class="day-name">${d.nombre}</span>
                    <span class="day-number">${d.numero}</span>
                    <span class="day-status ${statusClass}">${statusText}</span>
                </div>`;
        }).join('');

        grid.querySelectorAll('.day-card:not(.day-unavailable):not(.day-pasado)').forEach(card => {
            card.addEventListener('click', () => {
                grid.querySelectorAll('.day-card').forEach(c => c.classList.remove('day-selected'));
                card.classList.add('day-selected');
                S.fechaSeleccionada = card.dataset.fecha;
                S.horaSeleccionada = null;
                loadHorarios(S.fechaSeleccionada);
            });
        });
    }

    async function loadHorarios(fecha) {
        show('slots-section');
        hide('slots-empty');
        $id('slots-manana').innerHTML = '<div class="skeleton" style="height:44px;border-radius:8px;grid-column:span 3;"></div>';
        $id('slots-tarde').innerHTML = '';
        $id('fecha-label-str').textContent = '';
        updateMainBtn();

        try {
            const data = await apiFetch(`/portal-turnos/horarios?medico_id=${S.medicoId}&fecha=${fecha}`);
            $id('fecha-label-str').textContent = data.fecha_label;

            const allEmpty = !data.manana.length && !data.tarde.length;
            if (allEmpty) {
                $id('slots-manana').innerHTML = '';
                $id('slots-tarde').innerHTML = '';
                show('slots-empty');
                return;
            }

            renderSlots('slots-manana', data.manana);
            renderSlots('slots-tarde', data.tarde);

            $id('slots-manana-wrap').style.display = data.manana.length ? 'block' : 'none';
            $id('slots-tarde-wrap').style.display = data.tarde.length ? 'block' : 'none';
        } catch (e) {
            $id('slots-manana').innerHTML = `<p style="grid-column:span 3;color:var(--danger);font-size:13px;">Error al cargar horarios.</p>`;
        }
    }

    function renderSlots(containerId, horas) {
        const container = $id(containerId);
        container.innerHTML = horas.map(h => `
            <button class="slot-btn" data-hora="${h}">${h}</button>
        `).join('');

        container.querySelectorAll('.slot-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                S.horaSeleccionada = btn.dataset.hora;
                updateMainBtn();
            });
        });
    }

    // ── Step 3: summary ──
    function renderStep3Summary() {
        const fecha = new Date(S.fechaSeleccionada + 'T00:00:00');
        const fechaStr = fecha.toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'long' });
        const cap = s => s.charAt(0).toUpperCase() + s.slice(1);

        $id('step3-summary').innerHTML = `
            <div class="summary-row">
                <div class="summary-item"><div class="summary-key">Profesional</div><div class="summary-val">${S.medicoNombre}</div></div>
                <div class="summary-item"><div class="summary-key">Especialidad</div><div class="summary-val verde">${S.medicoEsp}</div></div>
            </div>
            <div class="summary-row">
                <div class="summary-item"><div class="summary-key">Fecha</div><div class="summary-val">${cap(fechaStr)}</div></div>
                <div class="summary-item"><div class="summary-key">Horario</div><div class="summary-val">${S.horaSeleccionada} hs</div></div>
            </div>`;
    }

    // ── Navigation ──
    function goToStep(n) {
        S.step = n;
        renderUI();
        window.scrollTo(0, 0);
    }

    function handleMainAction() {
        if (S.step === 1 && S.medicoId) {
            $id('back-medico-label').textContent = `${S.medicoNombre} · ${S.medicoEsp}`;
            setWspLinks(S.medicoWsp);
            goToStep(2);
            const hoy = startOfWeek(new Date());
            loadSemana(hoy, true);
        } else if (S.step === 2 && S.fechaSeleccionada && S.horaSeleccionada) {
            renderStep3Summary();
            goToStep(3);
        } else if (S.step === 3) {
            submitReserva();
        }
    }

    // ── Submit ──
    async function submitReserva() {
        const nombre = $id('input-nombre').value.trim();
        const wsp = $id('input-wsp').value.trim();
        let valid = true;

        hide('err-nombre'); hide('err-wsp');

        if (!nombre) { show('err-nombre'); valid = false; }
        if (!/^\d{10,11}$/.test(wsp)) { show('err-wsp'); valid = false; }
        if (!valid) return;

        const mobileBtn = $id('mobile-main-btn');
        const pcBtn = $id('pc-main-btn');
        mobileBtn.disabled = true; mobileBtn.textContent = 'Reservando…';
        if (pcBtn) { pcBtn.disabled = true; pcBtn.textContent = 'Reservando…'; }

        try {
            await apiFetch('/portal-turnos/reservar', {
                method: 'POST',
                body: JSON.stringify({
                    medico_id: S.medicoId,
                    fecha: S.fechaSeleccionada,
                    hora: S.horaSeleccionada,
                    nombre,
                    whatsapp: wsp,
                }),
            });

            S.nombre = nombre;
            S.whatsapp = wsp;

            // Success screen
            const fecha = new Date(S.fechaSeleccionada + 'T00:00:00');
            const fechaStr = fecha.toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'long' });
            const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
            $id('success-summary').innerHTML = `
                <div class="summary-row">
                    <div class="summary-item"><div class="summary-key">Profesional</div><div class="summary-val">${S.medicoNombre}</div></div>
                    <div class="summary-item"><div class="summary-key">Especialidad</div><div class="summary-val verde">${S.medicoEsp}</div></div>
                </div>
                <div class="summary-row">
                    <div class="summary-item"><div class="summary-key">Fecha</div><div class="summary-val">${cap(fechaStr)}</div></div>
                    <div class="summary-item"><div class="summary-key">Horario</div><div class="summary-val">${S.horaSeleccionada} hs</div></div>
                </div>`;

            const wspMensaje = `Hola! Acabo de reservar un turno con ${S.medicoNombre} para el ${cap(fechaStr)} a las ${S.horaSeleccionada} hs. Mi nombre es ${nombre}.`;
            const wspUrl2 = wspUrl(S.medicoWsp, wspMensaje);
            $id('success-wsp-btn').href = wspUrl2;
            $id('error-wsp-btn').href = wspUrl(S.medicoWsp);

            S.step = 4;
            renderUI();
        } catch (e) {
            $id('error-wsp-btn').href = wspUrl(S.medicoWsp);
            S.step = 5;
            renderUI();
        }

        window.scrollTo(0, 0);
    }

    // ── Event listeners ──
    function bindEvents() {
        ['mobile-main-btn', 'pc-main-btn'].forEach(id => {
            const btn = $id(id);
            if (btn) btn.addEventListener('click', handleMainAction);
        });

        $id('back-to-1').addEventListener('click', () => {
            S.fechaSeleccionada = null;
            S.horaSeleccionada = null;
            goToStep(1);
        });
        $id('back-to-2').addEventListener('click', () => {
            goToStep(2);
        });
        $id('pc-back-link').addEventListener('click', () => {
            if (S.step === 2) { S.fechaSeleccionada = null; S.horaSeleccionada = null; goToStep(1); }
            else if (S.step === 3) goToStep(2);
        });

        $id('btn-prev-week').addEventListener('click', () => {
            loadSemana(addDays(currentWeekDesde, -7));
        });
        $id('btn-next-week').addEventListener('click', () => {
            loadSemana(addDays(currentWeekDesde, 7));
        });

        $id('btn-nuevo-turno').addEventListener('click', resetWizard);
        $id('btn-reintentar').addEventListener('click', () => {
            S.step = 3;
            renderUI();
        });

        // PC back label text
        document.addEventListener('step-changed', () => {
            const pcBack = $id('pc-back-link');
            if (S.step === 2) pcBack.textContent = '‹ Cambiar especialista';
            else if (S.step === 3) pcBack.textContent = '‹ Cambiar día u horario';
            else pcBack.textContent = '';
        });
    }

    function resetWizard() {
        S.step = 1;
        S.medicoId = null;
        S.medicoNombre = '';
        S.medicoEsp = '';
        S.medicoWsp = null;
        S.fechaSeleccionada = null;
        S.horaSeleccionada = null;
        S.nombre = '';
        S.whatsapp = '';
        $id('input-nombre').value = '';
        $id('input-wsp').value = '';
        document.querySelectorAll('.medico-card').forEach(c => c.classList.remove('selected'));
        renderUI();
    }

    // ── Init ──
    function init() {
        bindEvents();
        renderUI();
        loadMedicos();
        setWspLinks('');
    }

    init();
})();
</script>
</body>
</html>
