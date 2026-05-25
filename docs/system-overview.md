# System Overview

## Purpose

This project is a child intervention management system for tracking children, daily training schedules, exercises, assessments, behavior, reports, and dashboards.

## Core Capabilities

- Child profile management
- Daily training schedule management
- Exercise tracking and progress
- Assessment recording and evaluation
- Behavior tracking and incident logging
- Reporting and insights dashboards
- Settings and administration

## Architecture Style

- Laravel monolith application
- Vue 3 UI managed by Inertia
- Vite for frontend asset bundling
- Tailwind CSS for styling
- Domain-based folders to keep backend and frontend together

## Implementation Status

- **Foundation Skeleton**: Fully set up with initial placeholders for all modules (Dashboard, Training, Exercises, Assessment, Behavior, Reports, Settings).
- **Children Module**: Fully implemented with full CRUD capabilities (list, show, create, edit, update, delete).
  - Validation: Handled via specialized form requests (`StoreChildRequest`, `UpdateChildRequest`).
  - Architecture: Follows a clean Service-Repository-Controller flow utilizing `ChildService`.
  - Frontend: Interactive Vue 3 components including listing filter search, detail profiles, and full form validations.
  - Testing: Complete automated test suite covering all CRUD endpoints and database interactions.
- **Exercises Module**: Fully implemented with full CRUD capabilities. Includes filterable lists, full validations via StoreExerciseRequest/UpdateExerciseRequest, backend logic powered by ExerciseService, and comprehensive tests.
- **Training Module**: Fully implemented daily training sessions with multi-exercise details, custom exercise picker component, status badges, complete validation via form requests, backend powered by TrainingService, and comprehensive test suite.
- **Dashboard Module**: Fully implemented.
  - Real-time data-driven dashboard utilizing `DashboardService` with sophisticated database aggregation queries.
  - Dynamic KPI cards: Total Children, Active Exercises, Today's Scheduled and Completed Sessions.
  - Interactive components: Today's training overview, weekly completion trends, a children's progress summary table, recent session list, latest behavior incidents log, latest assessment results, and quick action links.
  - Automated testing: Verified via comprehensive feature tests (`tests/Feature/DashboardControllerTest.php`).
