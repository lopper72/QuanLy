# Seed Data Guide

## Purpose

This document explains the sample development and test data seeded into the child intervention management system.

## Seed Data Contents

The database seeder generates:

- 2 children
- 20 exercises across the required categories
- 14 training sessions with session items
- assessments and assessment items
- behavior logs with severity levels
- weekly reports for each child

## Exercise categories included

- gross_motor
- fine_motor
- sensory
- communication
- cognitive
- social
- self_care

## Training statuses used

- planned
- in_progress
- completed
- skipped

## Behavior severities used

- low
- medium
- high

## Running migrations and seeders

1. Run database migrations:

```bash
php artisan migrate
```

2. Seed the database:

```bash
php artisan db:seed
```

3. If you need a fresh database with seeded data:

```bash
php artisan migrate:fresh --seed
```

## Sample data notes

- Children are generated with realistic names, birthdays, and optional diagnosis details.
- Training sessions span the last 14 days and are assigned to the seeded children.
- Each training session includes 3–5 exercise items.
- Assessments and assessment items provide a structure for skill scoring and levels.
- Behavior logs include a trigger, response, and severity rating.
- Weekly reports are created for each child to represent progress summaries.
