<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio Monti</title>
    <meta property="og:title" content="Consultorio Monti">
    <meta property="og:description" content="Tocá el link para ver los datos de tu turno y confirmarlo.">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1e293b;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 8px 32px rgba(0,0,0,0.06);
        }
        .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            background: #dbeafe;
        }
        .icon-wrap svg { width: 32px; height: 32px; color: #2563eb; }
        h1 {
            font-size: 1.375rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
            color: #0f172a;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.9375rem;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }
        .turno-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .turno-info-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        .turno-info-row:last-child { border-bottom: none; }
        .turno-info-row svg {
            width: 16px;
            height: 16px;
            color: #94a3b8;
            flex-shrink: 0;
        }
        .turno-info-label {
            color: #64748b;
            min-width: 90px;
            font-size: 0.8125rem;
        }
        .turno-info-value {
            color: #0f172a;
            font-weight: 500;
        }
        .btn {
            display: block;
            width: 100%;
            margin-top: 1.75rem;
            padding: 0.875rem 1rem;
            border: none;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            background: #16a34a;
            color: #ffffff;
        }
        .btn:hover { background: #15803d; }
    </style>
</head>
<body>
    <div class="card">

        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </div>

        <h1>Confirmar turno</h1>
        <p class="subtitle">Revisá los datos de tu turno y confirmalo.</p>

        <div class="turno-info">
            <div class="turno-info-row">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <span class="turno-info-label">Fecha</span>
                <span class="turno-info-value">{{ \Carbon\Carbon::parse($turno->fecha)->translatedFormat('l j \d\e F \d\e Y') }}</span>
            </div>
            <div class="turno-info-row">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="turno-info-label">Hora</span>
                <span class="turno-info-value">{{ \Carbon\Carbon::parse($turno->hora)->format('H:i') }}</span>
            </div>
            @if($turno->medico)
                <div class="turno-info-row">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span class="turno-info-label">Médico</span>
                    <span class="turno-info-value">{{ $turno->medico->name }} {{ $turno->medico->surname }}</span>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('turno.confirmar.store', $turno->id) }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit" class="btn">Confirmar turno</button>
        </form>

    </div>
</body>
</html>
