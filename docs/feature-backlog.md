# Feature Backlog & AI Task Breakdown

This backlog defines specific, granular features for upcoming product phases. Each card contains key parameters and a copy-pasteable **Suggested AI Coding Task Prompt** to accelerate agentic execution.

---

## Phase 1: MVP Stabilization

### 1.1 Secure Core Routes via Laravel Breeze
- **User Value**: Restricts access to sensitive child medical history and training records to verified guardians and specialists.
- **Priority**: High
- **Complexity**: Medium
- **Dependencies**: None
- **Suggested AI Coding Task Prompt**:
  ```text
  Scaffold standard user authentication and restrict core intervention routes.
  Steps:
  1. Execute `composer require laravel/breeze --dev` followed by `php artisan breeze:install vue` to generate authentication forms.
  2. Run the generated database migrations to add the `users` table.
  3. Locate `routes/web.php` and secure all routes (Dashboard, Children, Training, Exercises, Assessment, Behavior, Reports) by replacing `Route::middleware(['web'])` with `Route::middleware(['web', 'auth'])`.
  4. Write a feature test verifying that guests visiting '/dashboard' or '/children' are redirected back to the login path, while authenticated users render the Inertia components successfully.
  ```

### 1.2 Interactive Form Toasts and Validation UX
- **User Value**: Gives parents and clinical users clear, real-time feedback on successful updates and readable validation mistakes.
- **Priority**: High
- **Complexity**: Small
- **Dependencies**: None
- **Suggested AI Coding Task Prompt**:
  ```text
  Implement global flash alerts and interactive validation state feedback in the Vue 3 frontend.
  Steps:
  1. Add a global toast notification component inside `resources/js/Components/ui/` or pull in an npm package like `vue-toastification`.
  2. Hook into Inertia's page props in `resources/js/app.js` to automatically parse and display `flash.success` or `flash.error` messages dispatched by Laravel controller redirects.
  3. Update `ChildController` and other resource controllers to append flash variables on success redirect (e.g., `return redirect()->route('children.index')->with('success', 'Child record created successfully')`).
  4. Ensure that Vue forms (e.g. `ChildForm.vue`) bind Inertia's `form.errors` directly beneath input controls to highlight red validation feedback.
  ```

---

## Phase 2: Parent Daily Workflow

### 2.1 Today's Intervention Checklist
- **User Value**: Gives parents a focused, zero-distraction list of assigned activities to execute on their mobile device during the day.
- **Priority**: High
- **Complexity**: Medium
- **Dependencies**: `TrainingController`, `TrainingService`
- **Suggested AI Coding Task Prompt**:
  ```text
  Create an interactive "Today's Checklist" dashboard widget and dedicated page.
  Steps:
  1. Create a route `/training/today` that queries the training sessions active for the logged child on the current date.
  2. Implement an Inertia view `resources/js/Pages/Training/TodayChecklist.vue` that outputs the session's daily training items with large, thumb-friendly tap-to-complete checkboxes.
  3. Connect these check actions to an Axios / Inertia patch request updating `/training/items/{item_status}` dynamically in the backend database.
  4. Add a celebratory progress meter showing "Completed 3 of 5 tasks today!" upon item completion.
  ```

### 2.2 floating Quick-Log Behavior Button
- **User Value**: Allows caregivers to log sudden behavior tantrums or stims in 2 taps from any screen without leaving the page they are viewing.
- **Priority**: High
- **Complexity**: Small
- **Dependencies**: `BehaviorController`, `BehaviorService`
- **Suggested AI Coding Task Prompt**:
  ```text
  Add a global, floating "Quick Behavior Log" modal onto the application layout.
  Steps:
  1. Create a `QuickBehaviorModal.vue` component that houses simple, compact input fields: Child ID select, Behavior dropdown, and Severity slider.
  2. Embed this modal and a floating action button (FAB) inside `AppLayout.vue` so it renders globally at the bottom right corner of the screen.
  3. Bind form submission to an Inertia post request hitting the `BehaviorController@store` endpoint with silent background options to avoid full-page reloading.
  4. Write tests asserting that the modal submits and stores the behavior successfully.
  ```

---

## Phase 3: Intervention Planning

### 3.1 Skill Goals and Development Milestones
- **User Value**: Tracks target milestones over time so parents can verify if therapy is successfully reducing developmental gaps.
- **Priority**: Medium
- **Complexity**: Large
- **Dependencies**: `ChildService`
- **Suggested AI Coding Task Prompt**:
  ```text
  Build a Skill Goals and developmental milestones tracker module.
  Steps:
  1. Create a `Goal` Eloquent model with columns: `child_id`, `category` (e.g., Sensory, Speech, Motor), `description`, `target_date`, `status` (pending/achieved), and `achieved_at`.
  2. Generate a `GoalController.php` supporting standard CRUD methods.
  3. Create an Inertia tab layout on the Child Profile view (`Children/Show.vue`) listing Active Goals and Completed Goals.
  4. Write tests checking that therapists can add goals and mark existing milestones as achieved.
  ```

### 3.2 AI-Based Exercise Recommendations
- **User Value**: Automatically highlights therapeutic routines from the library that address a child's weakest assessment domains.
- **Priority**: Medium
- **Complexity**: Medium
- **Dependencies**: `ExerciseService`, `AssessmentService`
- **Suggested AI Coding Task Prompt**:
  ```text
  Build an automated exercise recommendation logic based on child assessment history.
  Steps:
  1. Add a service method `ExerciseService@getRecommendationsForChild($childId)` that fetches the child's latest assessment records.
  2. Parse the low-scoring domains from the assessment (e.g., if "Motor Skills" scored low, flag "Physical/Motor" exercises).
  3. Query the Exercise Library for matching categories with appropriate difficulty values matching the child's age limit.
  4. Render these recommended exercise suggestions in a card widget directly inside the child's training session builder page.
  ```

---

## Phase 4: Reports & Analytics

### 4.1 Progress Chart Integrations
- **User Value**: Visually demonstrates long-term progress slopes, keeping parents motivated and informed.
- **Priority**: Medium
- **Complexity**: Medium
- **Dependencies**: `ReportService`
- **Suggested AI Coding Task Prompt**:
  ```text
  Integrate visual interactive charts into the Child dashboard and reports section.
  Steps:
  1. Install Chart.js or Recharts (`npm install chart.js vue-chartjs`).
  2. Create a backend API endpoint `/api/children/{child}/progress-data` returning historical training scores grouped by week.
  3. Add a line chart widget in `Children/Show.vue` parsing this dataset to show training completion rates and average session duration patterns over the last 90 days.
  ```

---

## Phase 5: Multi-User / Scaling

### 5.1 Fine-grained RBAC Roles (Therapist vs. Parent)
- **User Value**: Secures patient profiles, allowing therapists to modify professional plans while keeping parent views clean and simple.
- **Priority**: Low
- **Complexity**: Large
- **Dependencies**: `User` model, Auth security
- **Suggested AI Coding Task Prompt**:
  ```text
  Implement role-based user access controls (RBAC) across the intervention framework.
  Steps:
  1. Introduce a `role` enum field on the `users` table ('admin', 'therapist', 'parent').
  2. Write middleware `EnsureUserHasRole.php` checking if a user matches permission categories before opening database gates.
  3. Hide clinical buttons (like deleting child files or adding professional assessments) in Vue pages if the active user page prop `auth.user.role` is 'parent'.
  4. Write integration unit tests ensuring parents receive 403 Forbidden errors when attempting to delete logs or settings.
  ```
