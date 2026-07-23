<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turnos de mañana</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif; color:#1e293b;">
    <div style="max-width:640px; margin:0 auto; padding:1.5rem;">
        <h1 style="font-size:1.25rem; margin-bottom:0.25rem;">
            Turnos de mañana — {{ $fecha->translatedFormat('l j \d\e F \d\e Y') }}
        </h1>
        <p style="color:#64748b; font-size:0.875rem; margin-top:0; margin-bottom:1.5rem;">
            Resumen automático generado a las {{ now()->format('H:i') }} hs.
        </p>

        @forelse($turnosPorMedico as $medicoNombre => $turnos)
            <h2 style="font-size:1rem; background:#f1f5f9; border-radius:6px; padding:0.5rem 0.75rem; margin-bottom:0.5rem;">
                {{ $medicoNombre }}
            </h2>

            <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:1.5rem;">
                <thead>
                    <tr>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Hora</th>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Paciente</th>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Obra social</th>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Teléfono</th>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Práctica</th>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Precio</th>
                        <th align="left" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($turnos as $turno)
                        <tr>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">{{ \Carbon\Carbon::parse($turno->hora)->format('H:i') }}</td>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">{{ $turno->paciente ? $turno->paciente->nombre.' '.$turno->paciente->apellido : 'Sin paciente' }}</td>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">{{ $turno->paciente ? (config('paciente.obras_sociales')[$turno->paciente->obra_social] ?? $turno->paciente->obra_social) : '-' }}</td>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">{{ $turno->paciente->telefono ?? '-' }}</td>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">{{ $turno->practica->nombre ?? '-' }}</td>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">{{ $turno->practica?->costo !== null ? '$'.number_format($turno->practica->costo, 2, ',', '.') : '-' }}</td>
                            <td style="font-size:0.875rem; border-bottom:1px solid #e2e8f0; padding:0.5rem 0.6rem;">
                                <span style="display:inline-block; padding:0.15rem 0.5rem; border-radius:999px; font-size:0.75rem; font-weight:bold; color:#fff; background:{{ $turno->estado->getHexColor() }};">
                                    {{ $turno->estado->getLabel() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="color:#94a3b8; font-size:0.875rem;">No hay turnos programados para mañana.</p>
        @endforelse
    </div>
</body>
</html>
