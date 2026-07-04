# Horarios por mes + apertura mensual + adición/exclusión con sistema/portal

## Contexto

Ya existe `HorarioEspecial` (adición/exclusión de un día puntual). Ahora se pide que la
configuración de horarios sea **por mes**: el médico debe poder abrir el calendario de un
mes (2026/2027 por ahora), ver/editar ahí su horario semanal base y sus horarios especiales
de ese mes, y poder preparar el mes siguiente sin que quede visible (ni en sistema ni en
portal) hasta que lo "abra" explícitamente. Los meses ya iniciados u pasados se consideran
abiertos automáticamente (no romper el uso actual). Las adiciones especiales necesitan poder
marcarse como visibles en sistema y/o portal, igual que ya hace `Horario`; las exclusiones no
(siempre bloquean todo). El usuario pidió separar el alta de adición/exclusión en dos
formularios/botones distintos (misma tabla, distinto form), y que el listado de especiales
muestre solo los del mes seleccionado.

## Modelo de datos (todo vía migraciones nuevas, nada de tocar migraciones ya corridas)

1. **`horarios`**: agregar `anio` (smallInt) y `mes` (tinyInt), nullable, backfill de las filas
   existentes a `now()->year` / `now()->month` en la misma migración. `Horario` pasa a tener un
   `creating` hook que autocompleta `anio`/`mes` con `now()` si no vienen seteados (igual patrón
   que ya usa para `medico_id`).
2. **`aperturas_mensuales`** (tabla nueva): `id, medico_id (FK), anio, mes, abierto (bool default
   true), timestamps`, unique (`medico_id`,`anio`,`mes`). Modelo `AperturaMensual` con el scope
   `Own` (mismo patrón que `Horario`/`HorarioEspecial`). Solo se crea una fila cuando alguien abre
   (o cierra) explícitamente un mes futuro; si no hay fila, el mes se considera abierto cuando
   `(anio,mes) <= (año actual, mes actual)`, cerrado en caso contrario.
3. **`horarios_especiales`**: agregar `activo_sistema` (bool default true) y `activo_portal`
   (bool default false) — mismos nombres/semántica que en `Horario`. Solo se usan cuando
   `tipo = adicion`; para `exclusion` se ignoran (siempre bloquea todo).

## Enums nuevos

- `App\Enums\Mes` (int-backed 1..12, `HasLabel`) — para el `Select` de mes y para textos.
- `config/horario.php` con `'anios_disponibles' => [2026, 2027]` (mismo estilo que
  `config/paciente.php`).

## Servicio de disponibilidad (`app/Services/ScheduleService.php`)

- Nuevo método público `mesesAbiertos(User $medico, string $desde, string $hasta): array`
  que precarga TODAS las filas de `AperturaMensual` del médico en una sola query, recorre
  mes a mes el rango `[$desde,$hasta]` y devuelve las claves `"anio-mes"` que están abiertas
  (fila explícita `abierto=true`, o sin fila y `(anio,mes) <= hoy`). `horariosDisponibles`
  lo llama con `($fecha,$fecha)` y corta devolviendo `[]` si el mes no está en el resultado
  (antes de tocar `Horario`/`HorarioEspecial`). `diasNoDisponibles` lo llama una vez antes del
  loop de días y, dentro del loop, si `"{$cursor->year}-{$cursor->month}"` no está en el array,
  marca el día no disponible y hace `continue` (mismo lugar donde hoy se chequea
  `configHorarios->isEmpty()`).
- Las queries a `Horario` pasan a filtrar también por `anio`/`mes` de la fecha consultada.
  En `diasNoDisponibles` (que puede recorrer un rango que cruza meses) el prefetch de
  `$horariosPorDia` pasa de agrupar solo por `dia` a agrupar por clave compuesta
  `"{$h->anio}-{$h->mes}-{$dia}"`.
- En los dos lugares donde hoy se recorren `$especiales` con `tipo === Adicion` para sumar
  slots, se agrega el filtro `portal ? $especial->activo_portal : $especial->activo_sistema`.
- Impacto en queries: `diasNoDisponibles` pasa de 3 a 4 queries (horarios, turnos, especiales,
  aperturas) — hay que actualizar `test_uses_at_most_three_queries_for_date_range` (comentario +
  `assertLessThanOrEqual(4, ...)`).

## `app/Filament/Resources/TurnoResource/Widgets/Calendario.php`

- `getAvailableSlotEvents`: llama a `app(ScheduleService::class)->mesesAbiertos(...)` una vez
  y salta la generación de slots "DISPONIBLE" para días de meses cerrados (el overlay rojo de
  "no disponible" ya sale gratis vía `diasNoDisponibles`, esto evita el contrasentido visual de
  mostrar slots sueltos en un mes cerrado).
- El prefetch de `Horario` pasa a agrupar por la misma clave compuesta `"anio-mes-dia"` que en
  `ScheduleService::diasNoDisponibles`.
- `getSlotRange()`/`getHiddenDays()` (helpers cosméticos para el config de FullCalendar) se
  acotan a `now()->year`/`now()->month` — no están relacionados al selector de mes de la página
  de configuración, son de la agenda de turnos.

## Servicio nuevo: `app/Services/HorarioMesService.php`

- `asegurarMesConfigurado(User $medico, int $anio, int $mes): void`. Si ya hay `Horario` para
  ese `(medico,anio,mes)` no hace nada. Si no, busca hacia atrás mes a mes (tope 24 meses) el
  mes más reciente con filas y clona sus `dia/desde/hasta/intervalo/activo_sistema/activo_portal`
  al mes destino. Si no encuentra ninguno, no crea nada (el médico arma desde cero, ya existe
  `CreateAction` en el resource).

## `app/Filament/Resources/HorarioResource.php`

- Agrega `Tables\Filters\SelectFilter::make('anio')` (options desde
  `config('horario.anios_disponibles')`, default `now()->year`) y
  `SelectFilter::make('mes')` (options `Mes::class`, default `now()->month`) — Filament aplica
  el `where` automáticamente porque el nombre del filtro coincide con la columna. Con el
  default global del proyecto (filtros en modal) esto ya da una UI consistente con el resto
  del panel sin blade custom.

## `app/Filament/Resources/HorarioResource/Pages/ListHorarios.php`

- `mount()`: además del mount original, llama a
  `app(HorarioMesService::class)->asegurarMesConfigurado($medico, now()->year, now()->month)`.
- Override de `updatedTableFilters()` (hook que ya expone el trait `HasFilters` de Filament):
  llama al padre, resuelve `$anio`/`$mes` desde `$this->tableFilters`, llama a
  `asegurarMesConfigurado` para ese mes (clona si hace falta) y hace
  `$this->dispatch('mes-horario-cambiado', anio: $anio, mes: $mes)` para sincronizar el widget
  de especiales (los parámetros que pasa `getWidgetData()` a un footer widget solo se usan en
  el mount inicial de Livewire, no son reactivos en renders siguientes — por eso el evento).
- `getHeaderActions()`: mantiene el `CreateAction` pero con `->mutateFormDataUsing()` para
  inyectarle `anio`/`mes` desde `$this->tableFilters` (así una fila nueva creada mientras se
  mira "Agosto 2026" queda en agosto 2026, no requiere que el usuario la tipee). Se agrega un
  `Action::make('abrir_mes')` visible solo cuando el mes/año filtrado es **posterior** al actual
  (mes actual/pasado ya está abierto solo por fecha); label/color dinámico según
  `AperturaMensual` exista y esté abierta; al ejecutar hace
  `AperturaMensual::updateOrCreate(['medico_id'=>..,'anio'=>..,'mes'=>..], ['abierto' => true])`
  (toggle simple: si ya existe y está abierta, la cierra).
- `getWidgetData()`: `['anio' => ..., 'mes' => ...]` (valores iniciales para el primer mount
  del footer widget).

## `app/Filament/Widgets/HorarioEspecialesWidget.php` (rediseño del form)

- Propiedades públicas `?int $anio`, `?int $mes` con default a `now()` en `mount()`, y
  `#[On('mes-horario-cambiado')]` que las actualiza cuando cambia el filtro de la página
  (dispara re-render de Livewire y por lo tanto recalcula la query de la tabla).
- La query del widget filtra por `whereYear('fecha', $this->anio)->whereMonth('fecha', $this->mes)`.
- Dos header actions en vez de un único `CreateAction`:
  - **"Agregar adición"**: form con `fecha` (acotada al mes/año actual del widget vía
    `minDate`/`maxDate`), `desde`, `hasta` (siempre requeridos, no hay `todo_el_dia`),
    `activo_sistema` (toggle, default true), `activo_portal` (toggle, default false), `motivo`.
    Guarda con `tipo = TipoHorarioEspecial::Adicion`.
  - **"Agregar exclusión"**: el form actual (fecha, `todo_el_dia`, desde/hasta condicionales,
    motivo), guarda con `tipo = TipoHorarioEspecial::Exclusion`.
- `EditAction` con `->form(fn (HorarioEspecial $record) => ...)` que elige el schema de
  adición o exclusión según `$record->tipo` (no se permite cambiar el tipo al editar).
- Columna `tipo` en la tabla ya existe (del trabajo anterior); se agregan columnas
  `activo_sistema`/`activo_portal` (icon boolean) — con placeholder/oculto cuando el registro
  es de tipo exclusión (`visible` a nivel de columna no aplica por fila, así que se deja el
  ícono tal cual, total en exclusión quedan en su default y no se usan en la lógica).
- "Cargar feriados": se cambia `now()->year` por `$this->anio` (currently viewed year), para
  poder cargar feriados de 2027 sin estar parado en el año calendario real.

## Modelo `HorarioEspecial`

- Agrega `activo_sistema`, `activo_portal` a `$fillable` y `$casts` (bool).

## `UserObserver`

- Los `createMany` de horarios por defecto agregan `'anio' => now()->year, 'mes' => now()->month`
  a cada fila.

## Tests (`tests/Feature/ScheduleServiceTest.php`)

- `createHorario()` gana params opcionales `int $anio = 2024, int $mes = 1` (con ese default no
  hace falta tocar ninguna llamada existente: todas las fechas usadas en el archivo caen en
  enero de 2024).
- `createEspecial()` gana params opcionales `activoSistema`/`activoPortal`.
- Nuevos casos:
  - Mes futuro sin `AperturaMensual` → `horariosDisponibles`/`diasNoDisponibles` lo tratan como
    cerrado (vacío / no disponible) aunque haya `Horario`/adiciones configuradas.
  - Con `AperturaMensual(abierto:true)` para ese mes futuro, vuelve a estar disponible.
  - `Horario` de un mes no debe filtrarse en otro mes con el mismo día de semana (mismo `dia`,
    distinto `anio`/`mes`).
  - Adición con `activo_sistema=false, activo_portal=true` no aparece con `portal:false` pero sí
    con `portal:true` (y viceversa).
- Ajuste de `test_uses_at_most_three_queries_for_date_range` → 4 queries.
- Nuevo test rápido para `HorarioMesService::asegurarMesConfigurado` (clona del mes anterior,
  no hace nada si ya hay filas, no hace nada si no hay ningún mes previo con datos).

## Verificación

- `php artisan test` (suite completa).
- Repasar manualmente en `php artisan serve` + navegador: entrar a "Horarios", cambiar el
  filtro de mes/año a un mes futuro, confirmar que aparece vacío la primera vez que se entra
  a un mes nuevo salvo que clone del anterior, agregar una adición y una exclusión desde los
  dos botones separados, abrir el mes, y verificar en el calendario de turnos que un mes
  futuro cerrado no ofrece slots ni en el widget de especiales se filtra el mes correcto.
