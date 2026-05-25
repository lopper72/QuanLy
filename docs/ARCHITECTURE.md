# Child Intervention Management System Architecture

## 1. Recommended Folder Structure

Root structure for a Laravel monolith using Vite + Vue + Inertia:

- app/
  - Http/
    - Controllers/
    - Requests/
  - Models/
  - Services/
- bootstrap/
- config/
- database/
  - migrations/
  - factories/
  - seeders/
- public/
- resources/
  - js/
    - Pages/
      - Dashboard.vue
      - Interventions/
      - Children/
      - Sessions/
    - Components/
      - InterventionForm.vue
      - NavigationBar.vue
    - app.js
  - css/
    - app.css
  - views/
    - app.blade.php
- routes/
  - web.php
  - api.php
- storage/
- tests/
- docs/
  - ARCHITECTURE.md
- package.json
- composer.json
- vite.config.js
- .gitignore

## 2. Core Domain Modules

1. Intervention Management
   - Intervention planning
   - Session scheduling
   - Progress tracking
2. Child / Case Management
   - Child profiles
   - Case status
   - Needs assessment
3. Stakeholder Collaboration
   - Referrals
   - Family / caregiver records
   - Service provider assignments
4. Session & Outcome Tracking
   - Session notes
   - Attendance
   - Outcome scoring
5. Reporting & Insights
   - Compliance and timelines
   - Progress summaries
   - Risk and escalation flags

## 3. Route Map

- `GET /` → Dashboard
- `GET /interventions` → Intervention list
- `GET /interventions/create` → Create intervention
- `POST /interventions` → Store intervention
- `GET /interventions/{intervention}/edit` → Edit intervention
- `PUT /interventions/{intervention}` → Update intervention
- `GET /children` → Child list
- `GET /children/{child}` → Child detail
- `GET /sessions` → Session list
- `GET /sessions/create` → Create session
- `POST /sessions` → Store session
- `GET /stakeholders` → Stakeholder list
- `GET /reports` → Reporting dashboard

## 4. Database Entity List

- `children`
- `interventions`
- `sessions`
- `case_plans`
- `stakeholders`
- `referrals`
- `users`
- `session_notes`
- `outcomes`

## 5. Frontend Page List

- `Dashboard` — high-level program overview
- `Intervention Index` — active interventions list
- `Intervention Create` — plan a new intervention
- `Intervention Edit` — update intervention details
- `Child Index` — list of children and cases
- `Child Details` — profile and intervention history
- `Session Index` — scheduled and completed sessions
- `Session Create` — record a new session
- `Report Center` — progress and compliance views

## 6. AI Task Breakdown

1. Scaffold the Laravel monolith file structure
2. Add Vite + Vue + Inertia page and component placeholders
3. Define core domain modules and routes in docs
4. Build initial controller/service/request placeholders
5. Add database entity classes and migration placeholders
6. Add route mapping and Inertia app shell
7. Document ownership rules and architectural boundaries

## 7. Folder Ownership Rules

- `app/Models` — domain entities and Eloquent models
- `app/Http/Controllers` — HTTP entry points, request orchestration
- `app/Http/Requests` — validation and authorization rules
- `app/Services` — business logic, orchestration, application services
- `resources/js/Pages` — top-level Inertia pages tied to routes
- `resources/js/Components` — reusable Vue UI components
- `resources/views` — Inertia root view and any legacy Blade views
- `routes` — web and API route definitions
- `database/migrations` — table and schema evolution
- `docs` — architecture, onboarding, and decision records
