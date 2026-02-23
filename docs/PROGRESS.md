# PROGRESS — Akili

> Suivi de l'avancement du projet par sprint et par module.

---

## Statut global

| Phase | Statut | Avancement |
|-------|--------|------------|
| Sprint 1–2 : Fondations | ✅ Terminé | 100% |
| Sprint 3–4 : Questionnaires | ✅ Terminé | 100% |
| Sprint 5–6 : Assignations & Soumissions | ✅ Terminé | 100% |
| Sprint 7–8 : GRH, Dashboard & Corrections | ✅ Terminé | 100% |
| Phase V2 : Améliorations | ⏳ Non démarré | 0% |
| Phase V3 : Multi-Tenant & Scale | ⏳ Non démarré | 0% |

---

## Sprint 1–2 : Fondations — ✅ Terminé (23/02/2026)

### Réalisé
- [x] Laravel 12 + Tailwind CSS v4 + DaisyUI v5 + HTMX 2 + Alpine.js
- [x] Base de données SQLite (dev) — architecture prête PostgreSQL
- [x] **10 migrations** : `companies`, `users`, `forms`, `form_sections`, `form_fields`, `assignments`, `submissions`, `answers`, `leave_requests`, `attendances`, `manager_privileges`, `submission_corrections`, `correction_fields`, `notifications`
- [x] Authentification Laravel Breeze (Blade) — login par `username`, sans page d'inscription publique
- [x] Middleware `ForcePasswordChange` — redirige vers `/first-login`, compatible requêtes HTMX (`HX-Redirect`)
- [x] Génération automatique des identifiants : `username = strtolower(lastname)@phone.org` / `password = ML+phone`
- [x] Rôles Spatie : `super-admin`, `manager`, `employee`
- [x] Layout principal Blade + DaisyUI — drawer sidebar desktop, bottom nav mobile
- [x] Seeder Super Admin : `admin@akili.local` / `password`

---

## Sprint 3–4 : Module Questionnaires — ✅ Terminé (23/02/2026)

### Réalisé
- [x] `FormController` — CRUD complet avec policy scoping `company_id`
- [x] Créateur de formulaire Alpine.js — 9 types de champs (text, textarea, select, radio, checkbox, date, number, file, rating)
- [x] Type `signature` prévu dans l'enum `form_fields.type` (implémentation V2)
- [x] Sections et champs ordonnés, options dynamiques pour select/radio/checkbox
- [x] Composants Blade réutilisables : `x-field-text`, `x-field-select`, `x-field-date`, `x-field-number`, `x-field-file`, `x-field-rating`
- [x] `x-badge-status`, `x-kpi-card`, `x-notification-bell`, `x-confirm-modal`

---

## Sprint 5–6 : Assignations & Soumissions — ✅ Terminé (23/02/2026)

### Réalisé
- [x] `AssignmentController` — assignation multi-employés avec date d'échéance
- [x] `SubmissionController` — remplissage sectionné avec progression, confirmation modale Alpine.js
- [x] Upload fichiers via `Storage::disk('local')` dans `submissions/`
- [x] Validation et soumission définitive — statut assignment → `completed`
- [x] Notifications DB : `FormAssigned` (queued `ShouldQueue`)
- [x] Helpers `isDueSoon()` et `isExpired()` sur le modèle `Assignment`

---

## Sprint 7–8 : GRH, Dashboard & Corrections — ✅ Terminé (23/02/2026)

### Réalisé
- [x] `EmployeeController` — CRUD complet, toggle actif/inactif, recherche HTMX, suppression avec confirmation
- [x] `LeaveController` — demandes de congé, approbation/refus avec notification
- [x] `AttendanceController` — saisie présence (employé + manager), vue calendrier mensuelle
- [x] `DashboardController` — KPIs différenciés Manager/Super Admin vs Employé (taux complétion, soumissions en attente, équipe)
- [x] `CorrectionController` — workflow `submitted → returned → corrected`, champs partiellement verrouillés
- [x] `PrivilegeController` — gestion Super Admin + délégation Manager→Manager (sans escalade)
- [x] `NotificationController` — badge HTMX polling toutes les 60s, liste, marquage lu
- [x] `ExportController` — PDF (DomPDF) + Excel (Maatwebsite)
- [x] Notifications DB : `LeaveRequestStatusChanged`, `SubmissionReturned`

---

## Correctifs post-MVP — ✅ (23/02/2026)

### Bugs corrigés
- [x] **Route model binding** — `Route::resource('employees')->parameters(['employees' => 'user'])` : le paramètre `{employee}` ne correspondait pas à `User $user` dans le controller → modèles non liés
- [x] **Formulaires imbriqués** — modal "Assigner" et sidebar "Déconnexion" utilisaient `<form>` imbriqués dans `<form>` (HTML invalide) → remplacés par `onclick` natif + form caché
- [x] **`x-on:click` sans `x-data`** — directives Alpine.js utilisées hors contexte → migrés vers `onclick` natif
- [x] **`is_published` → `is_active`** — colonne inexistante dans `forms`, renommée dans `DashboardController`
- [x] **Bottom nav mobile** — refaite en CSS custom (suppression `btm-nav` DaisyUI v5 cassé, `<a>` imbriquée dans `<a>` via composant)
- [x] **`welcome.blade.php`** — page de bienvenue Laravel par défaut supprimée (inutilisée, route `/` redirige directement)
- [x] **First-login** — `ForcePasswordChange` utilisait `routeIs('first-login')` qui ne matchait que la route GET ; la soumission POST était interceptée en boucle → remplacé par `$request->is('first-login')` pour exclure GET et POST
- [x] **Tables employés / assignations** — double rendu (liste mobile + table desktop) affichait les deux en même temps → une seule table avec colonnes `hidden sm:table-cell` et infos secondaires en sous-titre sur mobile
- [x] **Avatars non centrés** — DaisyUI v5 `avatar placeholder` ne centre plus le contenu → remplacés par des divs `flex items-center justify-center` (employés, sidebar, dashboard, privileges, show)
- [x] **Header mobile** — hamburger supprimé (navigation via bottom nav uniquement), header simplifié (logo + thème + notifications + avatar)

### Améliorations UX / design
- [x] **Thèmes DaisyUI** — 2 thèmes uniquement : `fantasy` (light, défaut) et `luxury` (dark), bascule via bouton + persistance `localStorage`
- [x] **Bouton Supprimer** — ajouté dans les actions Employés (super-admin, avec confirmation), masqué pour soi-même

---

## Prochaines étapes — V2

### Priorité haute
- [ ] Champ `signature` (Signature Pad canvas JS + Alpine.js)
- [ ] Rappels automatiques par job schedulé (assignments pending avant échéance)
- [ ] Notifications email (queue database driver) lors d'assignation
- [ ] Avatars uploadés — route sécurisée pour servir les fichiers privés
- [ ] Politique de mot de passe renforcée (historique, complexité)
- [ ] Tests Pest — Feature tests pour les modules critiques

### Priorité normale
- [ ] Migration stockage fichiers vers S3/Cloudflare R2
- [ ] Notifications temps réel WebSocket (Laravel Reverb)
- [ ] PWA — vite-plugin-pwa + manifest.json + page `/offline`
- [ ] Dashboard analytics avancé (Chart.js via Alpine.js)
- [ ] Import/export de modèles de questionnaires (JSON)
- [ ] Évaluations de performance liées aux soumissions

### V3 — Multi-Tenant
- [ ] Scoping `company_id` via middleware global ou Eloquent global scope
- [ ] Portail d'inscription en libre-service
- [ ] Plans et facturation (Stripe / Laravel Cashier)
- [ ] Migration Redis pour queues et cache
- [ ] API REST exposée (versionnée)
- [ ] i18n complet multi-langues
