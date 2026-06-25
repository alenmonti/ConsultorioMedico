# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Medical office management system (consultorio) built with Laravel 11 + Filament 3 + Livewire 3. The entire UI is a single Filament admin panel served at the root path (`/`). Database is SQLite.

## Commands

```bash
# PHP / Laravel
php artisan serve          # start dev server
php artisan migrate        # run migrations
php artisan pint           # lint PHP (Laravel Pint)
php artisan test           # run test suite
php artisan tinker         # REPL

# Frontend
npm run dev                # Vite dev server (required for Filament custom theme)
npm run build              # build assets for production
```

## Architecture

### Multi-tenancy via `medico_id`

The `Own` global scope ([app/Models/Scopes/Own.php](app/Models/Scopes/Own.php)) is applied to `Turno` and `Paciente`. It filters all queries to `medico_id = auth()->user()->medico_id` unless the user is Admin. This means:

- **Medico** users have `medico_id = id` (self-referential, set by `UserObserver` on creation).
- **Secretario** users have `medico_id` pointing to the medico they belong to — they see that medico's data.
- **Admin** users bypass the scope entirely and see all records.

When a new Medico registers, `UserObserver` auto-assigns `medico_id = user->id` and creates a default Mon–Fri 09:00–18:00 schedule at 20-minute intervals.

### Roles

Defined in `app/Enums/Roles.php`: `Admin`, `Medico`, `Paciente`, `Secretario`.

Use the global helpers:
- `user()` — returns `auth()->user()`
- `role(Roles::Admin)` — checks current user's role

### Filament panel

Single panel registered in [app/Providers/Filament/DashboardPanelProvider.php](app/Providers/Filament/DashboardPanelProvider.php). Resources, pages, and widgets are auto-discovered. The custom Tailwind theme is at `resources/css/filament/dashboard/theme.css` and loaded via `viteTheme()` — the Vite dev server must be running during development.

Global Filament defaults are configured in `AppServiceProvider::boot()`: all table actions render as icon buttons, filters open in a modal, `CreateAction` hides the "create another" button.

### Key models and relationships

- **User** — represents doctors, secretaries, and admins. Has `horariosDisponibles($fecha)` which computes free time slots by diffing `Horario` config against existing `Turno` rows.
- **Horario** — stores weekly schedule config per day (`dia`, `desde`, `hasta`, `intervalo`).
- **Turno** — appointment. `tipo` is either `'turno'` or `'sobre_turno'`. `estado` is `EstadosTurno` enum (Pendiente → Confirmado/Cancelado/Ausente/Atendido). Has the `Own` and `orderByDHU` global scopes.
- **Paciente** — patient record. Has the `Own` global scope. `documento` stores an uploaded file path in storage.
- **HistoriaClinica** — clinical history entries for a patient. Accessed through the `ViewFile` page (`/historia-clinica/paciente/{paciente_id}`), which shows all histories for a given patient.

### Calendar

`CalendarioTurnos` page embeds the `Calendario` widget (Saade FullCalendar). Appointments are color-coded by `EstadosTurno::getHexColor()`. Days with no available slots are highlighted in red as background events. Clicking a day pre-fills the creation form with that date.

### Custom config

`config/paciente.php` — list of obras sociales (health insurance providers) with their display labels and badge colors. Add new providers here.

### Obra social / insurance

The list of accepted insurances lives in `config/paciente.php` under `obras_sociales` and `obras_sociales_colores`.
