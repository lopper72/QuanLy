# Frontend Pages

## Primary Page Structure

### General Modules
- `/dashboard` → `resources/js/Pages/Dashboard/Index.vue` — Overview of child intervention metrics, notifications, and quick action widgets.
- `/assessment` → `resources/js/Pages/Assessment/Index.vue` — Assessment checklists and scoring profiles.
- `/reports` → `resources/js/Pages/Reports/Index.vue` — Analytics generation and progress trends.
- `/settings` → `resources/js/Pages/Settings/Index.vue` — Personal and application settings.

### Children Module Pages (Full CRUD)
- `/children` → `resources/js/Pages/Children/Index.vue` — Searchable grid of active and archived children.
- `/children/create` → `resources/js/Pages/Children/Create.vue` — Profile entry form for new children.
- `/children/{child}` → `resources/js/Pages/Children/Show.vue` — Detailed clinical profile, recent sessions, assessments, and behavior metrics.
- `/children/{child}/edit` → `resources/js/Pages/Children/Edit.vue` — Update interface for an existing child profile.

### Exercises Module Pages (Full CRUD)
- `/exercises` → `resources/js/Pages/Exercises/Index.vue` — Multi-filtered index grid view of therapeutic and developmental exercises.
- `/exercises/create` → `resources/js/Pages/Exercises/Create.vue` — Form to build and save a new exercise definition.
- `/exercises/{exercise}` → `resources/js/Pages/Exercises/Show.vue` — Full view of exercise details, category, difficulty, duration, and instructions.
- `/exercises/{exercise}/edit` → `resources/js/Pages/Exercises/Edit.vue` — Form to edit details or status of a specific exercise.

### Daily Training Schedule Module Pages (Full CRUD)
- `/training` → `resources/js/Pages/Training/Index.vue` — Filterable overview list of scheduled therapeutic training sessions.
- `/training/today` → `resources/js/Pages/Training/Today.vue` — High-frequency execution dashboard featuring real-time checklists, quick actions, therapist notes auto-saving, and child completion circles.
- `/training/create` → `resources/js/Pages/Training/Create.vue` — Comprehensive session composition screen with an interactive exercise picker.
- `/training/{session}` → `resources/js/Pages/Training/Show.vue` — Detailed clinical view of a session, its goals, child metadata, and individual item completion states.
- `/training/{session}/edit` → `resources/js/Pages/Training/Edit.vue` — Modify date, status, notes, and dynamically add, reorder, update, or remove training items.

### Behavior Tracking Module Pages (Full CRUD)
- `/behavior` → `resources/js/Pages/Behavior/Index.vue` — Searchable grid of logged Antecedent-Behavior-Consequence incidents with filter capabilities and metric summary cards.
- `/behavior/create` → `resources/js/Pages/Behavior/Create.vue` — Screen to log a new challenging behavior or positive incident.
- `/behavior/{behaviorLog}` → `resources/js/Pages/Behavior/Show.vue` — Thorough description page showing child name, incident time, behavior type, severity levels, antecedent/trigger factors, and the applied intervention/response.
- `/behavior/{behaviorLog}/edit` → `resources/js/Pages/Behavior/Edit.vue` — Update interface to correct or complete a logged behavior incident.

## Shared UI & Feature Components

- `resources/js/Components/layout` — Unified layout structure, lateral side-navigation, top-bar breadcrumbs, alerts, and system-wide feedback notifications.
- `resources/js/Components/ui` — Generic low-level design elements such as standard inputs, buttons, cards, list groups, badges, and modals.
- `resources/js/Components/children` — Children-specific UI components including:
  - `ChildList.vue` — A searchable data table and grid of children.
  - `ChildCard.vue` — Interactive preview card showing high-level child diagnostic information and quick actions.
  - `ChildForm.vue` — Reusable, validated form sheet binding both create and update operations under Inertia.js.
- `resources/js/Components/exercises` — Exercises-specific UI components including:
  - `ExerciseFilters.vue` — Multi-criteria interactive filters panel (category, difficulty, status, search string).
  - `ExerciseList.vue` — Responsive grid and list view wrapper displaying relevant matching exercises.
  - `ExerciseCard.vue` — Beautiful individual exercise representation showcasing tags, estimated minutes, status, and control actions.
  - `ExerciseForm.vue` — Highly intuitive, validated Vue/Inertia form layout that serves both creating and updating exercises.
- `resources/js/Components/dashboard` — Summary analytics, recent session indicators, and performance widgets.
- `resources/js/Components/training` — Dedicated Daily Training Schedule elements:
  - `TrainingStatusBadge.vue` — Clean visual indicator for training session and item statuses (planned, in_progress, completed, suspended, not_started).
  - `TrainingExercisePicker.vue` — Modular drawer/modal allowing search, filtration, and one-click injection of exercises into the session layout.
  - `TrainingItemList.vue` — Drag-and-drop styled list of active session items with fields for duration, status, order, and therapist notes.
  - `TrainingSessionCard.vue` — Overview card representing a session, showing child details, date, aggregate time, progress metrics, and action toggles.
  - `TrainingSessionList.vue` — Structured table/grid mapping out matched daily programs with empty state indicators.
  - `TrainingSessionForm.vue` — Composite Inertia.js Form binder coordinating child reference, program dates, session states, and dynamic items collection.
  - `ProgressRing.vue` — Dynamic SVG circular loader highlighting percentage completion of training checklist actions.
  - `RemainingCount.vue` — Micro-label listing number of remaining actions left to process.
  - `QuickStatusButton.vue` — Clickable circular toggle buttons for skipped / completed actions on individual items.
  - `TodayExerciseChecklist.vue` — Live checklists that dispatch Inertia.js background calls for marking exercises complete/skipped instantly.
  - `TodaySessionCard.vue` — Responsive parent card showing child meta, progress rings, collapsible checklist details, and inline clinician note pad.
- `resources/js/Components/assessment` — Custom scoring grids, past assessment selectors.
- `resources/js/Components/behavior` — Behavior-specific elements:
  - `BehaviorSummaryCards.vue` — Showcases statistical breakdowns of logged behaviors (e.g., total incidents, breakdown by high severity, or primary incident types).
  - `BehaviorFilters.vue` — Form panel permitting users to slice and filter logs by child, behavior type, or severity.
  - `BehaviorList.vue` — Responsive, detailed tabular grid summarizing logged behavior events with in-place action links.
  - `BehaviorForm.vue` — Validated, unified form interface that binds Inertia.js fields to both Store and Update actions.
