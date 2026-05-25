# Database Design

## Key Entities

- `children`
- `training_sessions`
- `exercises`
- `assessments`
- `behaviors`
- `reports`
- `users`

## Domain relationships

- A child can have many training sessions.
- A child can have many assessments.
- A child can have many behavior logs.
- Reports aggregate child, training, assessment, and behavior data.

## Implementation Notes

- Use Eloquent models for each core entity.
- Keep migration files under `database/migrations`.
- Use factories and seeders for initial test data.

## ERD Relationship Summary

- `children` is the central entity.
- `training_sessions` belongs to `children`.
- `training_session_items` belongs to `training_sessions` and `exercises`.
- `assessments` belongs to `children`.
- `assessment_items` belongs to `assessments`.
- `behavior_logs` belongs to `children`.
- `reports` belongs to `children`.

## Table relationship summary

- children 1..* training_sessions
- children 1..* assessments
- children 1..* behavior_logs
- children 1..* reports
- training_sessions 1..* training_session_items
- exercises 1..* training_session_items
- assessments 1..* assessment_items

## Notes on scaling

- Use foreign keys and indexes to support query filtering by child and date.
- Add soft deletes to major domain tables to allow archival and recovery.
- Keep items as separate tables so training and assessment details can grow independently.
