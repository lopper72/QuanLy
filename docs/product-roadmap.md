# Product Roadmap

This roadmap outlines the long-term vision and product evolution phases for the **Child Intervention Management System** following the completion of the core MVP skeleton.

---

## Roadmap Phases Overview

```
┌────────────────────────┐
│ Phase 1: MVP Stable    │ ──► Focus on UI polish, test safety, robust seeders, and Breeze security
└────────────────────────┘
            │
            ▼
┌────────────────────────┐
│ Phase 2: Parent Daily  │ ──► Today's checklist, media notes, and mobile quick-logging workflows
└────────────────────────┘
            │
            ▼
┌────────────────────────┐
│ Phase 3: Planning      │ ──► Skill taxonomy maps, progress milestones, and exercise recommendations
└────────────────────────┘
            │
            ▼
┌────────────────────────┐
│ Phase 4: Analytics     │ ──► Automated PDF generation, progress charts, and behavior trends
└────────────────────────┘
            │
            ▼
┌────────────────────────┐
│ Phase 5: Team & Scale  │ ──► Therapist/Parent portals, role-based controls, and clinic hierarchies
└────────────────────────┘
```

---

## Phase Detail & Core Objectives

### Phase 1: MVP Stabilization (High Priority)
*Objective: Build a secure, robust foundation with production-grade reliability.*
- **Security Foundations**: Integrate Laravel Breeze/Fortify for User Auth and protect clinical routes via `'auth'` middleware.
- **Mobile Polish**: Tweak Tailwind grid views and table views to be 100% fluid across small smartphone screen sizes.
- **Enhanced Data Seeders**: Introduce detailed intervention database seeders containing realistic development patterns (e.g. sensory exercises, cognitive routines).
- **Interactive Feedback**: Refine system form validation states and toast message notifications on resource actions.

### Phase 2: Parent Daily Workflow (High Priority)
*Objective: Streamline daily interventions for parents, making log entries take less than 30 seconds.*
- **Today Training Checklist**: A simple checklist interface for parents to review and execute assigned exercise tasks for the current day.
- **Quick-Log Behavior Widget**: A simple, floating action button layout to record behavior tantrums or stims instantly from any page.
- **Intervention Daily Notes**: Rich-text notepad entries capturing child moods, sleeping conditions, and overall performance indicators.
- **Media Attachments**: Support photo/video uploads showing a child's movement or articulation during exercise execution.

### Phase 3: Intervention Planning (Medium Priority)
*Objective: Empower therapists and clinicians to architect long-term, goal-oriented pathways.*
- **Skill Goals & Target Milestones**: A system to track individual target goals (e.g., "Maintains eye contact for 5 seconds", "Expresses 3-word sentences").
- **Monthly Training Schemes**: Advanced schedule calendar templates allowing therapists to lock in weekly physical and verbal plans.
- **Smart Skill Recommendations**: Automatic listing of matching library exercises based on a child's weak skill assessment scores.
- **Therapist Logs**: Secure columns specifically configured for therapist comments separate from parent records.

### Phase 4: Reports & Analytics (Medium Priority)
*Objective: Synthesize high-frequency log entries into actionable progress evidence.*
- **Interactive Progress Charts**: Dynamic Line/Bar diagrams comparing training session scores against weekly averages using Chart.js or Recharts.
- **Weekly / Monthly PDF Exporters**: Clean, printable PDF documents that parents can export to present to school boards or clinical therapists.
- **Behavior Trend Analytics**: Matrix views showing correlation links (e.g., if tantrums increase on days with lower training session completion rates).

### Phase 5: Multi-User & Multi-Clinic Scale (Low Priority)
*Objective: Evolve the single-child tracking tool into a secure B2B platform hosting clinics and teams.*
- **Role-Based Access Controls (RBAC)**: Fine-grained user permissions separating `System Administrator`, `Therapist / Clinician`, and `Parent / Caregiver` roles.
- **Multi-Child Profiles**: Enabling a single parent account or therapist account to toggle between multiple children profiles.
- **Clinic/Team Workspaces**: Organization-based account tiers supporting user invites, staff management, and collaborative patient boards.