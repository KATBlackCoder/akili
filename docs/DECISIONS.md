# DECISIONS — Akili

> Registre des décisions techniques et architecturales (ADR — Architecture Decision Records).
> Chaque décision documente le contexte, le choix retenu et les alternatives écartées.

---

## ADR-001 — Stack Frontend : Blade + HTMX + Alpine.js (pas Inertia/Livewire)

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
L'application cible des employés terrain sur mobile avec une connexion parfois instable. La stack frontend doit être légère, fonctionner correctement hors connexion partielle, et permettre une PWA native.

### Décision
- **Blade** server-rendered pour toutes les pages
- **HTMX** pour les interactions nécessitant le serveur (ajout de champs, filtres, badge notifications)
- **Alpine.js** exclusivement pour les interactions purement côté client (modales, preview fichier, champs conditionnels, compteurs)

### Alternatives écartées
- **Inertia.js + Vue** : navigateFallback PWA problématique avec SPA, complexité inutile pour la cible
- **Livewire** : dépendance forte au serveur pour chaque interaction, moins adapté offline
- **React/Next.js** : overkill pour un outil RH interne, coût de maintenance plus élevé

### Règle de séparation
> HTMX = serveur. Alpine.js = client. Aucune logique métier dans Alpine.js.

---

## ADR-002 — Authentification : Login par username (pas email)

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
Les employés terrain n'ont pas forcément d'adresse email professionnelle. Le manager leur communique leurs identifiants directement.

### Décision
- **username** = `strtolower(lastname)@phone.org` (ex: `dupont@0612345678.org`)
- **Mot de passe initial** = `ML` + téléphone (ex: `ML0612345678`)
- `must_change_password = true` à la création → forcé au premier login
- Pas de page d'inscription publique, pas de reset par email

### Gestion collision
En cas de collision username (deux Dupont avec le même téléphone), une exception métier est levée avec un message d'erreur clair. Cas jugé quasi impossible en pratique.

### Conséquences
- `LoginRequest` utilise `username` comme champ d'authentification
- La table `password_reset_tokens` utilise `username` comme primary key (au lieu d'`email`)
- Le middleware `ForcePasswordChange` intercepte toutes les requêtes HTTP + HTMX (via `HX-Redirect`)
- **Important** : le middleware doit exclure l’URL `/first-login` via `$request->is('first-login')` (et non `routeIs('first-login')`), afin que la soumission POST du formulaire de changement de mot de passe ne soit pas interceptée en boucle

---

## ADR-003 — Architecture mono-tenant prête multi-tenant

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
MVP ciblé mono-tenant (une seule entreprise par instance), mais le passage en SaaS multi-tenant doit être possible sans refactoring majeur.

### Décision
- Colonne `company_id` présente sur **toutes les tables métier** dès le MVP
- Seeder crée une company `default` automatiquement
- Les requêtes Eloquent sont scopées manuellement sur `company_id` dans les controllers (V1)
- En V3, migration vers un middleware ou Eloquent Global Scope automatique

### Dette technique acceptée
Le scoping `company_id` est actuellement manuel dans chaque controller. En V3, ce scoping sera automatisé via un `CompanyScope` Eloquent global ou un middleware dédié.

---

## ADR-004 — Système de privilèges Manager (table dédiée vs permissions Spatie)

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
Les Managers ont des permissions variables selon ce que le Super Admin (ou un Manager délégant) leur accorde. La délégation doit respecter la règle : "on ne peut donner que ce qu'on possède".

### Décision
- Table `manager_privileges` distincte de Spatie/Permission
- 3 booléens : `can_create_forms`, `can_create_users`, `can_delegate`
- Champ `granted_by` pour l'audit trail
- `PrivilegePolicy::delegate()` vérifie côté applicatif que le délégant ne peut pas accorder plus que ce qu'il possède

### Pourquoi pas des permissions Spatie pures ?
Spatie/Permission ne gère pas nativement le concept de "délégation conditionnelle" (donner seulement ce qu'on a). La table dédiée permet un audit trail propre et une logique de délégation explicite.

---

## ADR-005 — Workflow corrections soumissions (état machine)

**Date :** 23/02/2026  
**Statut :** Accepté

### États de la soumission
```
submitted → returned → corrected
               ↑          |
               └──────────┘ (peut être renvoyé plusieurs fois)
```

### Décision
- Chaque renvoi crée un **nouvel** enregistrement `submission_corrections` (historique complet)
- En mode `partial` : seuls les `field_id` ciblés sont modifiables — les autres sont rendus avec `disabled` HTML + style visuel DaisyUI `input-disabled`
- En mode `full` : comportement identique à la soumission initiale
- Après correction : `submission.status = corrected`, `assignment.status = completed`
- Un renvoi remet `assignment.status = pending` côté Manager

### Sécurité
La liste des `field_id` verrouillés est calculée **côté serveur** dans `CorrectionController::update()`. Alpine.js ne fait que du rendu visuel — la vérification applicative est en PHP.

---

## ADR-006 — Stockage fichiers : disque local Railway (MVP)

**Date :** 23/02/2026  
**Statut :** Accepté (dette V2)

### Décision MVP
- `Storage::disk('local')` → volume persisté Railway monté sur `/app/storage`
- Chemin relatif stocké en base (pas de chemin absolu)
- Variable d'environnement `FILESYSTEM_DISK=local` pour faciliter le switch

### Migration V2
En V2, basculer vers S3/Cloudflare R2 via `FILESYSTEM_DISK=s3` sans toucher au code applicatif. Les URLs privées seront servies via `Storage::temporaryUrl()`.

---

## ADR-007 — Notifications : polling HTMX (MVP) vs WebSocket (V2)

**Date :** 23/02/2026  
**Statut :** Accepté (dette V2)

### Décision MVP
Badge de notifications rechargé via HTMX toutes les 60 secondes :
```html
hx-get="/notifications/count" hx-trigger="load, every 60s"
```

### Migration V2
Si le besoin de temps réel strict émerge, migration vers Laravel Reverb (WebSocket) sans changer la structure des notifications DB (table standard Laravel `notifications`).

---

## ADR-008 — Export PDF/Excel : à la demande, non stocké

**Date :** 23/02/2026  
**Statut :** Accepté

### Décision
- Les exports sont générés **à la demande** et servis directement en réponse HTTP (pas stockés sur disque)
- PDF : DomPDF (`barryvdh/laravel-dompdf`) avec template Blade dédié `exports/form-pdf.blade.php`
- Excel : Maatwebsite Excel (`maatwebsite/excel`) avec classe `FormSubmissionsExport`

### Conséquence
Pas de gestion de cache ou de job pour les exports. Si les exports deviennent lents (nombreuses soumissions), migrer vers un job queued en V2.

---

## ADR-011 — Hiérarchie explicite : colonne `role` sur users + Spatie

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
La hiérarchie super_admin > manager > superviseur > employe nécessite des requêtes fréquentes du type "tous les employés d'un superviseur" qui seraient coûteuses via Spatie (jointures sur les tables de rôles).

### Décision
- Colonne `role` (enum) directement sur `users` pour les requêtes Eloquent efficaces
- Spatie/Permission conservé pour les vérifications `hasRole()` dans les Policies
- Les relations `superviseurs()`, `employes()` utilisent `where('role', ...)` pour éviter les N+1

### Conséquence
Duplication contrôlée : `role` sur users + Spatie. À la création, les deux sont assignés simultanément.

---

## ADR-012 — Deux systèmes d'assignation coexistants : `assignments` + `form_assignments`

**Date :** 23/02/2026  
**Statut :** Accepté (dette à traiter)

### Contexte
L'ancien système `assignments` (1-to-1 employé) coexiste avec le nouveau `form_assignments` (scope role|individual).

### Décision
Les deux tables coexistent en V2. `form_assignments` gère les rapports Type 1 & Type 2. L'ancien `assignments` reste pour la rétrocompatibilité.

### Action requise en V3
Migrer entièrement vers `form_assignments` et supprimer `assignments`.

---

## ADR-009 — Tailwind v4 + DaisyUI v5 (migration depuis Breeze v3)

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
Laravel Breeze installe Tailwind CSS v3 par défaut. DaisyUI v5 requiert Tailwind v4.

### Actions réalisées
1. Remplacement de `tailwindcss: ^3.1.0` par `tailwindcss: ^4.0.0` dans `package.json`
2. Ajout de `@tailwindcss/vite` comme plugin Vite (remplace le plugin PostCSS)
3. Suppression de `tailwindcss` du `postcss.config.js` (ne conserver qu'`autoprefixer`)
4. Migration `app.css` vers la syntaxe v4 : `@import "tailwindcss"` + `@plugin "daisyui"` + `@plugin "@tailwindcss/forms"`
5. Suppression de `tailwind.config.js` (non nécessaire en v4)

---

## ADR-010 — Thèmes DaisyUI : fantasy (light) et luxury (dark)

**Date :** 23/02/2026  
**Statut :** Accepté

### Contexte
L’application doit proposer un mode clair et un mode sombre, avec persistance du choix utilisateur.

### Décision
- **2 thèmes DaisyUI uniquement** : `fantasy` (light, défaut) et `luxury` (dark)
- Configuration dans `app.css` : `@plugin "daisyui" { themes: fantasy --default, luxury --prefersdark; }`
- Bouton de bascule dans le header mobile et dans le sidebar desktop
- Persistance du choix dans `localStorage` (`akili-theme`) et restauration avant premier rendu pour éviter le flash

### Conséquence
Le `<html>` reçoit `data-theme="fantasy"` ou `data-theme="luxury"` via JavaScript ; le layout guest utilise `fantasy` par défaut.
