<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Turnos {{ $rango === 'semana' ? 'semana' : 'día' }} {{ $desde->format('d/m/Y') }}@if($rango === 'semana') - {{ $hasta->format('d/m/Y') }}@endif
    </title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
            padding: 1.5rem;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .toolbar button, .toolbar a {
            font-family: inherit;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #1e293b;
            cursor: pointer;
            text-decoration: none;
        }
        .toolbar button.primary {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }
        header.doc-header {
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 0.75rem;
        }
        header.doc-header h1 {
            font-size: 1.375rem;
            margin-bottom: 0.25rem;
        }
        header.doc-header .subtitle {
            color: #64748b;
            font-size: 0.9375rem;
        }
        h2.dia-titulo {
            font-size: 1.0625rem;
            text-transform: capitalize;
            margin: 1.5rem 0 0.5rem;
            padding: 0.4rem 0.6rem;
            background: #f1f5f9;
            border-radius: 6px;
        }
        h2.dia-titulo:first-of-type { margin-top: 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5rem;
        }
        th, td {
            text-align: left;
            padding: 0.5rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }
        th {
            background: #f8fafc;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
        }
        .estado-badge {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
        }
        .sin-turnos {
            color: #94a3b8;
            font-size: 0.875rem;
            padding: 0.5rem 0.6rem 1rem;
        }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
            h2.dia-titulo { break-inside: avoid; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ url('/') }}">Volver al calendario</a>
        <button class="primary" onclick="window.print()">Imprimir</button>
    </div>

    <header class="doc-header">
        <h1>
            Turnos —
            @if($rango === 'semana')
                Semana del {{ $desde->translatedFormat('j \d\e F') }} al {{ $hasta->translatedFormat('j \d\e F \d\e Y') }}
            @else
                {{ $desde->translatedFormat('l j \d\e F \d\e Y') }}
            @endif
        </h1>
        @if($medico)
            <div class="subtitle">{{ $medico->name }} {{ $medico->surname }}</div>
        @endif
    </header>

    @php
        $cursor = $desde->copy();
    @endphp

    @while($cursor <= $hasta)
        @php
            $fechaStr = $cursor->format('Y-m-d');
            $turnosDelDia = $turnos->get($fechaStr, collect());
        @endphp

        <h2 class="dia-titulo">{{ $cursor->translatedFormat('l j \d\e F') }}</h2>

        @if($turnosDelDia->isEmpty())
            <div class="sin-turnos">Sin turnos.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Obra social</th>
                        <th>Teléfono</th>
                        <th>Práctica</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($turnosDelDia as $turno)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($turno->hora)->format('H:i') }}</td>
                            <td>{{ $turno->paciente ? $turno->paciente->nombre.' '.$turno->paciente->apellido : 'Sin paciente' }}</td>
                            <td>{{ $turno->paciente ? (config('paciente.obras_sociales')[$turno->paciente->obra_social] ?? $turno->paciente->obra_social) : '-' }}</td>
                            <td>{{ $turno->paciente->telefono ?? '-' }}</td>
                            <td>{{ $turno->practica->nombre ?? '-' }}</td>
                            <td>{{ $turno->practica?->costo !== null ? '$'.number_format($turno->practica->costo, 2, ',', '.') : '-' }}</td>
                            <td><span class="estado-badge" style="background-color: {{ $turno->estado->getHexColor() }}">{{ $turno->estado->getLabel() }}</span></td>
                            <td>{{ $turno->notas ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @php $cursor->addDay(); @endphp
    @endwhile

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
