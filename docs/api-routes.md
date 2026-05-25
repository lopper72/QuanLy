# API & Web Routes

## Web Route Map (Inertia Pages & Endpoints)

### General Modules
- `GET /dashboard` → Dashboard page (`DashboardController@index`)
- `GET /assessment` → Assessment page (`AssessmentController@index`)
- `GET /reports` → Reporting page (`ReportController@index`)
- `GET /settings` → Settings page (`SettingController@index`)

### Children Module (Full CRUD)
- `GET /children` → List all children with optional search and diagnosis filters (`ChildController@index`, named `children.index`)
- `GET /children/create` → Show create form for a new child profile (`ChildController@create`, named `children.create`)
- `POST /children` → Store a new child profile (`ChildController@store`, named `children.store`)
- `GET /children/{child}` → Show detailed view of a single child's records (`ChildController@show`, named `children.show`)
- `GET /children/{child}/edit` → Show edit form for an existing child profile (`ChildController@edit`, named `children.edit`)
- `PUT /children/{child}` → Update an existing child profile (`ChildController@update`, named `children.update`)
- `DELETE /children/{child}` → Soft delete an existing child profile (`ChildController@destroy`, named `children.destroy`)

### Exercises Module (Full CRUD)
- `GET /exercises` → List all exercises with multi-criteria filters (`ExerciseController@index`, named `exercises.index`)
- `GET /exercises/create` → Show create form for a new exercise (`ExerciseController@create`, named `exercises.create`)
- `POST /exercises` → Store a new exercise profile (`ExerciseController@store`, named `exercises.store`)
- `GET /exercises/{exercise}` → Show detailed view of a single exercise (`ExerciseController@show`, named `exercises.show`)
- `GET /exercises/{exercise}/edit` → Show edit form for an existing exercise (`ExerciseController@edit`, named `exercises.edit`)
- `PUT /exercises/{exercise}` → Update an existing exercise profile (`ExerciseController@update`, named `exercises.update`)
- `DELETE /exercises/{exercise}` → Soft delete an existing exercise profile (`ExerciseController@destroy`, named `exercises.destroy`)

### Daily Training Schedule Module (Full CRUD)
- `GET /training` → List scheduled training sessions with optional filters (`TrainingController@index`, named `training.index`)
- `GET /training/create` → Show composition view for a new daily program (`TrainingController@create`, named `training.create`)
- `POST /training` → Store a new training session along with its detailed program items (`TrainingController@store`, named `training.store`)
- `GET /training/{session}` → View detailed diagnostic run of a scheduled training session (`TrainingController@show`, named `training.show`)
- `GET /training/{session}/edit` → Show edit form to modify date, status, notes, and items (`TrainingController@edit`, named `training.edit`)
- `PUT /training/{session}` → Update a training session's details and synchronize program items (`TrainingController@update`, named `training.update`)
- `DELETE /training/{session}` → Soft delete training session and related child items (`TrainingController@destroy`, named `training.destroy`)

### Behavior Tracking Module (Full CRUD)
- `GET /behavior` → List logged behavior events with child, type, and severity filters (`BehaviorController@index`, named `behavior.index`)
- `GET /behavior/create` → Show create form for a new behavior incident (`BehaviorController@create`, named `behavior.create`)
- `POST /behavior` → Store a new behavior incident log (`BehaviorController@store`, named `behavior.store`)
- `GET /behavior/{behaviorLog}` → Show details of a single logged behavior incident (`BehaviorController@show`, named `behavior.show`)
- `GET /behavior/{behaviorLog}/edit` → Show edit form for an existing behavior log (`BehaviorController@edit`, named `behavior.edit`)
- `PUT /behavior/{behaviorLog}` → Update an existing behavior incident log (`BehaviorController@update`, named `behavior.update`)
- `DELETE /behavior/{behaviorLog}` → Soft delete a behavior incident log (`BehaviorController@destroy`, named `behavior.destroy`)

## Route Responsibilities

- Backend controllers handle request validation, authorize access, invoke services, and return Inertia pages.
- Front-end views are fully integrated inside the Laravel framework utilizing Inertia.js.
- Validation is enforced at the request level via Form Request objects (`StoreChildRequest`, `UpdateChildRequest`, `StoreExerciseRequest`, `UpdateExerciseRequest`, `StoreSessionRequest`, `UpdateSessionRequest`, `StoreBehaviorRequest`, `UpdateBehaviorRequest`).
- Controller/Database integration is abstracted through Domain Services (`ChildService`, `ExerciseService`, `TrainingService`, `BehaviorService`).
