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

---

## Update V2 — Hiérarchie & Rapports Types — ✅ Terminé (23/02/2026)

### Réalisé

#### Changement 1 — Nouvelle hiérarchie utilisateurs
- [x] Table `groups` créée (company_id, manager_id, name, description, is_active)
- [x] Table `users` modifiée : suppression `department`/`job_title`, ajout `role` (enum), `supervisor_id`, `group_id`
- [x] Rôle `superviseur` ajouté dans Spatie Permission
- [x] `UserFactory` mise à jour avec états `manager()`, `superviseur()`, `employe()`
- [x] Modèle `User` — relations `manager()`, `supervisor()`, `superviseurs()`, `employes()`, `group()`
- [x] `EmployeeController` mis à jour : visibilité hiérarchique, création selon rôle, plus de dept/job_title
- [x] Vues employees (create, show, index/table) mises à jour : groupe remplace département

#### Changement 2 — Privilèges étendus
- [x] Table `manager_privileges` renommée `user_privileges` avec colonnes `can_create_superviseurs`, `can_create_employes`
- [x] Modèle `UserPrivilege` créé (remplace `ManagerPrivilege`)
- [x] `PrivilegeController` mis à jour — délégation Manager→Superviseur uniquement `can_create_employes`
- [x] `UserPrivilegeObserver` — révocation en cascade `can_create_employes`
- [x] `PrivilegePolicy` — logique de délégation selon niveau hiérarchique

#### Changement 3 — Questionnaires Type 1 & Type 2
- [x] Table `forms` modifiée : ajout `report_type` (enum type1/type2)
- [x] Table `form_assignments` créée (scope_type role|individual, scope_role, scope_user_id)
- [x] Table `submissions` modifiée : ajout `company_id`, `form_id`, `form_assignment_id`, `report_type`, statut `draft`
- [x] Table `submission_rows` créée (lignes Type 1)
- [x] Table `submission_drafts` créée (brouillon auto Type 1)
- [x] Table `answers` modifiée : ajout `row_id` nullable

#### Changement 4 — Interface Rapport Type 1 (journalier)
- [x] `ReportType1Controller` — show, addRow, deleteRow, saveDraft, submit
- [x] Vue `reports/type1/show.blade.php` — tableau HTMX, brouillon auto 30s
- [x] Partial `reports/type1/partials/row.blade.php` — ligne dynamique
- [x] Partial `reports/type1/partials/draft-badge.blade.php`
- [x] `resources/js/reports.js` — Alpine.js `reportType1()` : localStorage + restore + getDraftData

#### Changement 5 — Interface Rapport Type 2 (urgent)
- [x] `ReportType2Controller` — show, submit
- [x] Vue `reports/type2/show.blade.php` — formulaire urgent
- [x] Job `NotifyUrgentReport` — queue `urgent`, notifie superviseur + manager
- [x] Notification `UrgentReportNotification` créée

#### Changement 6 — Assignation globale/individuelle
- [x] `FormAssignmentController@store` — scope_type role|individual
- [x] Job `NotifyFormAssigned` avec résolution des destinataires
- [x] Notification `FormAssignedNew` créée
- [x] Modal d'assignation intégrée dans `forms/index.blade.php`
- [x] Routes dédiées `/forms/{form}/assign`

#### Changement 7 — Vues mises à jour
- [x] `forms/create.blade.php` — champ report_type (radio Journalier/Urgent)
- [x] `forms/index.blade.php` — badge Type 1/Type 2, bouton Assigner
- [x] `groups/index.blade.php` et `groups/create.blade.php` créées

#### Tests Pest — 16 tests écrits et passants
- [x] `HierarchyTest` — création superviseur/employé, visibilité hiérarchique
- [x] `ReportType1Test` — show, saveDraft, submit, accès non autorisé
- [x] `ReportType2Test` — multi-soumission, dispatch queue urgent
- [x] `PrivilegeDelegationTest` — délégation, révocation en cascade, sécurité
- [x] `FormAssignmentTest` — assignation role/individual, protection

---

---

## Update V3 — Suppression Groupes + Créateur Utilisateur Dynamique + Assignation Étendue — ✅ Terminé (23/02/2026)

### Réalisé

#### Correctif 1 — Suppression complète du concept de Groupes
- [x] Migration : `group_id` supprimé de la table `users`
- [x] Migration : table `groups` droppée
- [x] `app/Models/Group.php` supprimé
- [x] `app/Http/Controllers/GroupController.php` supprimé
- [x] `database/factories/GroupFactory.php` supprimé
- [x] `resources/views/groups/` supprimé
- [x] Route `groups` supprimée de `web.php`
- [x] `EmployeeController` nettoyé de toute référence Group
- [x] Modèle `User` : `group_id` retiré du `$fillable`, relation `group()` supprimée
- [x] Vues `employees/show`, `employees/edit`, `employees/create` mises à jour

#### Correctif 2 — Base de données form_assignments
- [x] Migration : `scope_user_id` supprimé de `form_assignments`
- [x] Migration : `scope_role` étendu à `superviseur | employe | both`
- [x] Migration : table pivot `form_assignment_users` créée (many-to-many)
- [x] Modèle `FormAssignment` : `selectedUsers()` BelongsToMany + `resolveRecipients()` avec `both`
- [x] `FormAssignmentFactory` mise à jour (état `individual()` utilise le pivot)

#### Correctif 3 — Création d'utilisateur dynamique
- [x] `UserController` créé avec `create()` et `store()` — rôles filtrés selon créateur
- [x] Vue `users/create.blade.php` — formulaire dynamique Alpine.js (3 étapes)
- [x] Génération username/password automatique avec gestion des collisions
- [x] Privilèges filtrés : jamais accorder plus que ce que l'on possède
- [x] Routes `users.create` et `users.store` ajoutées
- [x] `employees.index` pointe vers `users.create` pour la création

#### Correctif 4 — Assignation questionnaires
- [x] `FormAssignmentController@store` : mode global avec `both`, mode individuel multi-sélection
- [x] `validateUsersInBranch()` → 403 si utilisateur hors branche
- [x] Modal d'assignation `forms/index.blade.php` refait avec Alpine.js : 3 options globales + liste mixte filtrée
- [x] `FormController@index` passe `$branchUsers` à la vue
- [x] `ReportType1Controller` et `ReportType2Controller` mis à jour pour le pivot
- [x] `NotifyFormAssigned` job utilise désormais `resolveRecipients()` du modèle

#### Tests Pest
- [x] `HierarchyTest` — 5 tests (création Manager/Superviseur/Employé, interdiction Manager→Manager, visibilité)
- [x] `FormAssignmentTest` — 5 tests (role, both, individuel multiple, hors branche 403, non autorisé 403)

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
