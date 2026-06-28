<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.75rem;
        }
        p {
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.6;
        }
        .turno-info {
            margin-top: 1.5rem;
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
            text-align: left;
        }
        .turno-info p {
            color: #374151;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .turno-info strong {
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $exito ? '✅' : '❌' }}</div>
        <h1>{{ $titulo }}</h1>
        <p>{{ $mensaje }}</p>

        @if($exito && isset($turno))
            <div class="turno-info">
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($turno->fecha)->format('d/m/Y') }}</p>
                <p><strong>Hora:</strong> {{ $turno->hora }}</p>
            </div>
        @endif
    </div>
</body>
</html>
