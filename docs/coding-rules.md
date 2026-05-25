# Coding Rules

## Backend rules

- Keep controllers slim: orchestrate requests and delegate business logic to services.
- Use form requests for validation and authorization.
- Place domain logic in `app/Services` or model methods, not controllers.
- Use Eloquent model relationships for related data.
- Keep route definitions organized by feature.

## Frontend rules

- Use Inertia pages for route-level views.
- Keep shared UI components in `resources/js/Components`.
- Use the Composition API and reusable composables for shared logic.
- Keep page components simple and delegate reusable UI to components.
- Use Tailwind utility classes for styling and consistent layout.

# Vietnamese Font Rules

This application is for Vietnamese users.

All UI must use fonts that fully support Vietnamese diacritics.

## Required font stack

Use this global font stack:

Inter, "Be Vietnam Pro", "Noto Sans", "Segoe UI", Arial, sans-serif

## Rules

- Do not use decorative fonts for main UI.
- Do not use random Google Fonts in individual components.
- Do not define page-level font-family unless absolutely necessary.
- Do not use serif fonts for admin UI.
- Do not use monospace fonts for Vietnamese body text.
- Avoid excessive uppercase for Vietnamese labels because diacritics can look bad.
- Avoid extreme tracking/letter-spacing on Vietnamese text.
- Use normal Tailwind font weights:
  - font-normal
  - font-medium
  - font-semibold
  - font-bold
- Do not use custom font classes unless defined globally.
- If a component needs special typography, it must still inherit the global Vietnamese-safe font stack.

## Implementation rule

All Vue pages/components must inherit font from the app root.
Do not add local font-family styles.

Backend/code names remain English.
User-facing Vietnamese text must render correctly.

# Vietnamese Encoding Rules

- All source files must be saved as UTF-8 without BOM.
- Do not paste mojibake/corrupted Vietnamese text.
- If Vietnamese appears as `Ã`, `Ä`, `á»`, `áº`, `Â`, `Æ`, or broken punctuation such as `â€`, it is invalid and must be fixed.
- Run `npm run check:encoding` after editing Vietnamese UI text.

# Language Rules

This system is built for Vietnamese parents and therapists.

## User-facing UI must always be Vietnamese

All visible UI text MUST be Vietnamese:

- navigation menu
- page titles
- buttons
- forms
- validation messages
- empty states
- loading states
- toast notifications
- dashboard cards
- filters
- table headers
- modal dialogs
- placeholder text
- fake/demo/sample data
- reports
- notes
- recommendations

Examples:

GOOD:
- Lưu
- Xóa
- Hồ sơ trẻ
- Không có dữ liệu
- Đang tải...

BAD:
- Save
- Delete
- Children
- No records found
- Loading...

---

# Internal Code Rules

Internal implementation must remain English:

- PHP class names
- controllers
- services
- model names
- database columns
- route names
- enum keys
- variable names
- Vue component filenames

Examples:

Database value:
completed

UI display:
Hoàn thành

Database value:
gross_motor

UI display:
Vận động thô

---

# Enum Display Rules

Never display raw enum values directly to users.

Always map enum keys through display label helpers.

Use:
resources/js/Lib/labels.js

Examples:
- statusLabels
- categoryLabels
- severityLabels
- behaviorTypeLabels
- reportTypeLabels

---

# Seed Data Rules

Seed/demo data must be Vietnamese and realistic.

Use:
- Vietnamese names
- Vietnamese notes
- Vietnamese exercises
- Vietnamese recommendations
- Vietnamese reports

Avoid:
- John Doe
- Test Child
- Sample Exercise
- Lorem ipsum

---

# Before Coding

Before making UI changes:

1. Search for remaining English UI labels
2. Replace them with Vietnamese labels
3. Ensure no raw enum keys are shown
4. Ensure new UI follows Vietnamese display rules

---

# Forbidden

Do not:
- translate database columns
- translate route names
- translate PHP class names
- translate enum keys stored in DB
- mix English/Vietnamese inconsistently in UI
