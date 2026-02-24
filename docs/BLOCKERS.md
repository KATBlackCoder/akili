# BLOCKERS — Akili

> Points de blocage actifs, risques identifiés et dettes techniques à surveiller.

---

## Blocages actifs

*Aucun blocage actif au 23/02/2026. Le Sprint 1–8 (MVP) est terminé.*

---

## Risques identifiés

### RISQUE-001 — Scoping `company_id` non automatisé

**Sévérité :** Haute
**Impacte :** Sécurité, intégrité des données

**Description :**
Le filtrage par `company_id` est effectué manuellement dans chaque controller. Un controller oublié ou mal implémenté pourrait exposer des données cross-company lors du passage multi-tenant.

**Mitigation actuelle :**
Les controllers existants scopent explicitement via `->where('company_id', $user->company_id)`.

**Action requise avant V3 :**
Implémenter un `CompanyScope` Eloquent global sur tous les modèles métier, activé automatiquement dès qu'un utilisateur est authentifié.

---

### RISQUE-002 — Pas de tests automatisés

**Sévérité :** Haute
**Impacte :** Stabilité, régressions

**Description :**
Aucun test Pest n'est écrit pour le MVP. Les modules critiques (auth, corrections, privilèges) ne sont pas couverts.

**Action requise en priorité :**
- Tests Feature : `AuthenticationTest`, `ForcePasswordChangeTest`
- Tests Feature : `FormCreationTest`, `SubmissionTest`, `CorrectionWorkflowTest`
- Tests Feature : `PrivilegeDelegationTest`
- Tests Unit : `UserIdentifierGenerationTest`

---

### RISQUE-003 — Stockage fichiers local non compatible multi-tenant

**Sévérité :** Moyenne
**Impacte :** Migration V2/V3

**Description :**
Les fichiers uploadés sont stockés dans `storage/app/local/submissions/` et `storage/app/local/avatars/`. Ce chemin n'est pas scopé par `company_id`, ce qui créera des conflits lors du passage multi-instance.

**Mitigation actuelle :**
Chemins relatifs stockés en base (pas absolus). `FILESYSTEM_DISK=local` en variable d'environnement.

**Action requise avant V3 :**
Migrer vers S3/R2 avec des paths scopés par company : `{company_id}/submissions/{file}`.

---

### RISQUE-004 — Serveur de files d'attente non géré en dev

**Sévérité :** Basse
**Impacte :** Notifications DB non envoyées si `queue:work` n'est pas lancé

**Description :**
Les notifications (`FormAssigned`, `LeaveRequestStatusChanged`, `SubmissionReturned`) implémentent `ShouldQueue`. Si `php artisan queue:work` n'est pas lancé, elles ne seront pas traitées.

**Mitigation actuelle :**
`composer run dev` lance `php artisan queue:listen --tries=1` automatiquement.

**Action en production :**
Configurer un processus `php artisan queue:work` supervisé (Railway second service ou Supervisor).

---

### RISQUE-005 — CSRF sur requêtes HTMX

**Sévérité :** Basse (déjà mitigé)
**Impacte :** Sécurité

**Description :**
HTMX ne envoie pas automatiquement le token CSRF.

**Mitigation actuelle (implémentée) :**
Le layout `app.blade.php` inclut un listener `htmx:configRequest` qui injecte le token CSRF dans toutes les requêtes HTMX :
```js
document.addEventListener('htmx:configRequest', function(evt) {
    evt.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
});
```

---

## Dettes techniques acceptées (MVP → V2)

| Ref | Description | Cible |
|-----|-------------|-------|
| DT-001 | Notifications polling HTMX 60s → migration WebSocket Reverb | V2 |
| DT-002 | Stockage fichiers local → S3/Cloudflare R2 | V2 |
| DT-003 | Queue driver `database` → Redis (Upstash) en production | V2 |
| DT-004 | Champ `signature` prévu en enum mais non implémenté | V2 |
| DT-005 | PWA (vite-plugin-pwa, manifest, page `/offline`) | V2 |
| DT-006 | Scoping `company_id` manuel → Global Scope automatique | V3 |
| DT-007 | Exports PDF/Excel synchrones → jobs queued si volume élevé | V2 |
| DT-008 | Pas de rate limiting sur les routes sensibles (login, API) | V2 |
| DT-009 | Pas de politique de complexité mot de passe | V2 |
| DT-010 | Avatar servi directement depuis `storage/` sans auth | V2 |

---

### RISQUE-006 — Deux systèmes d'assignation coexistants

**Sévérité :** Basse  
**Impacte :** Cohérence données, maintenance

**Description :**
Les tables `assignments` (ancien système 1-to-1) et `form_assignments` (nouveau scope role|individual) coexistent. Les contrôleurs de soumission hérités (`SubmissionController`) utilisent encore `assignments`.

**Action requise en V3 :**
Migrer `SubmissionController` vers `form_assignments`, supprimer `assignments`.

---

## Résolu

| Date | Description | Solution |
|------|-------------|----------|
| 23/02/2026 | Conflit Tailwind v3 (Breeze) vs DaisyUI v5 (requiert v4) | Mise à jour vers Tailwind v4, migration CSS + suppression postcss tailwind plugin |
| 23/02/2026 | Token CSRF non envoyé par HTMX | Listener `htmx:configRequest` dans le layout principal |
| 23/02/2026 | Route model binding cassé sur `employees` | `->parameters(['employees' => 'user'])` pour aligner `{employee}` avec `User $user` |
| 23/02/2026 | Formulaires imbriqués (`<form>` dans `<form>`) | `onclick` natif + form caché pour logout et modal assign |
| 23/02/2026 | `x-on:click` Alpine.js hors `x-data` | Migration vers `onclick` vanilla JS |
| 23/02/2026 | Colonne `is_published` inexistante dans `forms` | Corrigé en `is_active` dans `DashboardController` |
| 23/02/2026 | Bottom nav mobile cassée (DaisyUI `btm-nav` + `<a>` imbriquée) | Remplacement par nav custom Tailwind |
| 23/02/2026 | `welcome.blade.php` inutilisée | Supprimée — route `/` redirige directement |
| 23/02/2026 | First-login : soumission POST interceptée en boucle | `routeIs('first-login')` → `$request->is('first-login')` pour exclure GET et POST |
| 23/02/2026 | Tables employés/assignations : double rendu (liste + table) affichés ensemble | Une seule table avec colonnes `hidden sm:table-cell` et sous-titres mobile |
| 23/02/2026 | Avatars (initiales) non centrés dans le carré bleu (DaisyUI v5) | Remplacement `avatar placeholder` par div `flex items-center justify-center` partout |
| 23/02/2026 | Header mobile : hamburger inutile avec bottom nav | Hamburger supprimé, header simplifié |
