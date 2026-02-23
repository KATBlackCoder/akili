PRD — Application Questionnaires & GRH Terrain
Product Requirements Document
Version 1.0  |  Février 2025
Paramètre	Valeur
Stack Backend	Laravel 11
Stack Frontend	Blade (server-rendered) + HTMX + Alpine.js
UI Framework	DaisyUI v5 + Tailwind CSS v4 (mobile-first)
Base de données	PostgreSQL
Authentification	Laravel Breeze (version Blade) + Spatie/Permission
Export PDF	DomPDF (barriere-pdf/laravel-dompdf)
Stockage fichiers	Volume persisté Railway (MVP) → S3/R2 (V2)
PWA	vite-plugin-pwa (config MPA native — simple avec Blade)
Notifications temps réel	Badge rechargé à chaque navigation (MVP)
Queues	Driver database → Redis (production)
Déploiement	Railway / Render
Langue	Français (i18n structuré pour extension future)
Volume cible	50 à 150 employés par instance
Multi-tenant	Mono-tenant MVP — architecture company_id prête

1. Vision & Objectifs du Produit
1.1 Vision
Fournir aux entreprises de taille intermédiaire (50–150 employés terrain) une plateforme centralisée permettant de créer, distribuer et analyser des questionnaires dynamiques, tout en gérant les dimensions RH essentielles : profils, congés, présences et évaluations de performance.

1.2 Objectifs Produit
    • Réduire le temps de création et de distribution des formulaires terrain de 80 % par rapport aux processus manuels existants (Excel, papier).
    • Offrir une interface mobile-first fluide aux employés terrain, sans nécessiter de connexion permanente hors des moments de soumission.
    • Centraliser la gestion RH (congés, présences, évaluations) dans un seul outil, évitant la dispersion entre plusieurs outils.
    • Permettre l'export et l'analyse des données collectées en PDF et Excel pour faciliter le reporting.
    • Poser une architecture mono-tenant solide, extensible vers le multi-clients (SaaS) sans refactoring majeur.

1.3 Indicateurs de Succès (KPIs produit)
KPI	Cible MVP	Méthode de mesure
Taux de complétion des questionnaires assignés	> 85 %	submissions complétées / assignées
Délai moyen de soumission	< 24h après assignation	created_at vs submitted_at
Adoption managers (créateurs actifs)	> 90 % des managers	nb questionnaires créés / mois
Temps moyen de création d'un questionnaire	< 10 minutes	session analytics
Taux d'erreur export PDF/Excel	< 1 %	logs d'erreur jobs queue

2. Rôles & Permissions
L'application utilise Spatie/laravel-permission avec trois rôles principaux. La colonne company_id est présente sur toutes les tables métier dès le MVP pour préparer le passage multi-tenant.

Rôle	Description	Périmètre d'action
Super Admin	Gère l'intégralité de la plateforme	Toutes les entreprises (futur multi-tenant)
Manager	Crée les questionnaires, gère ses employés	Ses propres employés et questionnaires
Employé Terrain	Remplit les questionnaires assignés	Ses propres soumissions et profil

2.1 Matrice de Permissions Détaillée
Fonctionnalité	Super Admin	Manager	Employé
Définir les privilèges max d'un Manager	✅	❌	❌
Créer un nouvel utilisateur	✅	✅ si privilège accordé	❌
Créer un questionnaire	✅	✅ si privilège accordé	❌
Assigner un questionnaire	✅	✅ (ses employés)	❌
Déléguer ses privilèges à un autre Manager	✅	✅ si privilège accordé	❌
Soumettre une réponse	❌	❌	✅
Voir les soumissions	✅ (toutes)	✅ (ses employés)	✅ (les siennes)
Renvoyer une soumission en correction	✅	✅ (ses employés)	❌
Corriger une soumission renvoyée	❌	❌	✅ (parties renvoyées)
Exporter PDF / Excel	✅	✅	❌
Gérer les profils employés	✅	✅ (ses employés)	✅ (son profil)
Valider les congés	✅	✅ (ses employés)	❌
Demander un congé	❌	❌	✅
Saisir une présence	✅	✅	✅
Voir le tableau de bord KPI	✅	✅ (ses équipes)	❌
Gérer les utilisateurs (activation/désactivation)	✅	❌	❌

3. User Stories
3.1 Super Admin
    • En tant que Super Admin, je veux pouvoir créer et gérer les comptes managers, afin d'onboarder de nouveaux clients ou équipes.
    • En tant que Super Admin, je veux définir pour chaque Manager la liste des privilèges qu'il peut exercer (créer questionnaires, créer utilisateurs, déléguer), afin de contrôler finement les droits accordés.
    • En tant que Super Admin, je veux renvoyer tout ou partie d'une soumission en correction à un employé, avec ou sans message explicatif, afin de corriger des erreurs constatées.
    • En tant que Super Admin, je veux avoir une vue globale de toutes les soumissions et activités, afin de surveiller l'usage de la plateforme.
    • En tant que Super Admin, je veux configurer les paramètres globaux de l'entreprise (nom, logo, timezone), afin de personnaliser l'application.

3.2 Manager
    • En tant que Manager (avec privilège can_create_users), je veux créer un nouveau compte employé en saisissant son prénom, nom et téléphone, afin que ses identifiants soient générés automatiquement et qu'il puisse se connecter immédiatement.
    • En tant que Manager (avec privilège can_create_forms), je veux créer un questionnaire avec des champs de types variés (texte, choix multiple, date, fichier, note), afin de collecter des informations terrain structurées.
    • En tant que Manager (avec privilège can_delegate), je veux accorder à un autre Manager un sous-ensemble de mes propres privilèges, sans pouvoir lui en donner plus que ce que je possède moi-même, afin de répartir les responsabilités dans mon équipe.
    • En tant que Manager, je veux assigner un questionnaire à un employé ou un groupe d'employés avec une date d'échéance, afin de planifier les collectes de données.
    • En tant que Manager, je veux voir le statut de chaque questionnaire assigné (en attente, complété, renvoyé, expiré), afin de suivre l'avancement.
    • En tant que Manager, je veux renvoyer une soumission en correction en sélectionnant librement les sections ou champs à corriger, avec un message optionnel pour l'employé, afin de signaler des erreurs précises sans bloquer l'ensemble de la soumission.
    • En tant que Manager, je veux exporter les réponses en PDF et Excel, afin de les intégrer dans mes rapports.
    • En tant que Manager, je veux valider ou refuser les demandes de congé de mes employés, afin de gérer les plannings.
    • En tant que Manager, je veux saisir ou corriger les entrées de présence de mes employés, afin de maintenir un registre accurate.
    • En tant que Manager, je veux voir un tableau de bord avec les KPIs de mon équipe (taux de complétion, présences, congés en cours), afin de piloter mon équipe.

3.3 Employé Terrain
    • En tant qu'Employé, à ma première connexion avec les identifiants générés (username = nom@telephone.org / password = MLtelephone), je suis forcé de changer mon mot de passe avant d'accéder à l'application.
    • En tant qu'Employé, je veux voir la liste de mes questionnaires assignés avec leur statut et date d'échéance, afin de savoir ce que j'ai à faire.
    • En tant qu'Employé, je veux être notifié lorsqu'une soumission m'est renvoyée en correction, avec le message du manager et la liste des champs ou sections à corriger, afin de comprendre ce que je dois modifier.
    • En tant qu'Employé, je veux pouvoir corriger uniquement les parties renvoyées (ou toute la soumission selon le choix du manager), sans pouvoir toucher aux parties non renvoyées qui restent verrouillées, afin de resoumettre une réponse corrigée.
    • En tant qu'Employé, je veux remplir un questionnaire depuis mon smartphone de manière intuitive, afin de soumettre mes réponses facilement sur le terrain.
    • En tant qu'Employé, je veux pouvoir uploader un fichier (photo, document) dans un champ de formulaire, afin de joindre des preuves terrain.
    • En tant qu'Employé, je veux soumettre une demande de congé avec les dates et le motif, afin de planifier mon absence.
    • En tant qu'Employé, je veux saisir mon entrée de présence manuellement, afin d'enregistrer ma journée de travail.
    • En tant qu'Employé, je veux voir l'historique de mes soumissions passées, afin de retrouver mes réponses antérieures.

4. Modèle de Données
4.1 Tables Principales
Toutes les tables métier incluent company_id pour préparer le multi-tenant. Les relations sont gérées par des clés étrangères avec contraintes ON DELETE appropriées.

Table : companies
Colonne	Type	Description
id	bigint PK	Identifiant auto-incrémenté
name	string	Nom de l'entreprise
slug	string unique	Identifiant URL de l'entreprise
logo_path	string nullable	Chemin du logo stocké
timezone	string default 'UTC'	Fuseau horaire de l'entreprise
settings	jsonb nullable	Configuration JSON flexible
created_at / updated_at	timestamps	

Table : users
Colonne	Type	Description
id	bigint PK	
company_id	bigint FK → companies	Scoping multi-tenant
firstname	string	Prénom de l'utilisateur
lastname	string	Nom de famille de l'utilisateur
username	string unique	Login généré : lastname@phone.org (ex: dupont@0612345678.org)
password	string	Hash bcrypt — généré : ML+phone (ex: ML0612345678)
must_change_password	boolean default true	Forcer changement mot de passe à la 1ère connexion
manager_id	bigint FK → users nullable	Supérieur hiérarchique direct
department	string nullable	Département / service
job_title	string nullable	Intitulé du poste
phone	string NOT NULL	Téléphone obligatoire — utilisé pour générer les identifiants
avatar_path	string nullable	Photo de profil
hired_at	date nullable	Date d'embauche
is_active	boolean default true	Compte actif ou désactivé
created_at / updated_at	timestamps	

Tables : forms, form_sections, form_fields
Colonne	Type	Description
--- forms ---		
id	bigint PK	
company_id	bigint FK	
created_by	bigint FK → users	Manager créateur
title	string	Titre du questionnaire
description	text nullable	Description / instructions
is_active	boolean default true	Questionnaire actif
created_at / updated_at	timestamps	
--- form_sections ---		
id	bigint PK	
form_id	bigint FK → forms	
title	string	Titre de la section
order	integer	Ordre d'affichage
--- form_fields ---		
id	bigint PK	
section_id	bigint FK → form_sections	
type	enum	text, textarea, select, radio, checkbox, date, number, file, rating
label	string	Libellé du champ
placeholder	string nullable	Texte d'aide
is_required	boolean default false	
order	integer	Ordre dans la section
config	jsonb nullable	Options (choices[], min, max, accept, etc.)

Tables : assignments, submissions, answers
Colonne	Type	Description
--- assignments ---		
id	bigint PK	
form_id	bigint FK → forms	
assigned_to	bigint FK → users	Employé assigné
assigned_by	bigint FK → users	Manager assignant
due_at	timestamp nullable	Date d'échéance
status	enum	pending, completed, expired
notified_at	timestamp nullable	Dernière notification envoyée
--- submissions ---		
id	bigint PK	
assignment_id	bigint FK → assignments	
submitted_by	bigint FK → users	
submitted_at	timestamp	Date de soumission effective
--- answers ---		
id	bigint PK	
submission_id	bigint FK → submissions	
field_id	bigint FK → form_fields	
value	text nullable	Réponse textuelle ou JSON (multi-select)
file_path	string nullable	Chemin fichier si type=file

Tables GRH : leave_requests, attendances
Colonne	Type	Description
--- leave_requests ---		
id	bigint PK	
company_id	bigint FK	
user_id	bigint FK → users	Employé demandeur
manager_id	bigint FK → users	Manager validateur
type	enum	paid, unpaid, sick, other
start_date	date	
end_date	date	
reason	text nullable	Motif de la demande
status	enum	pending, approved, rejected
reviewed_at	timestamp nullable	Date de validation/refus
--- attendances ---		
id	bigint PK	
company_id	bigint FK	
user_id	bigint FK → users	
date	date	Date de la présence
check_in	time nullable	Heure d'arrivée
check_out	time nullable	Heure de départ
note	string nullable	Observation (retard, absence partielle...)
entered_by	bigint FK → users	Qui a saisi (employé ou manager)

Table : manager_privileges
Gère les privilèges accordés à chaque Manager par le Super Admin ou un Manager délégant. Un Manager ne peut déléguer que les privilèges qu'il possède lui-même (vérification applicative).
Colonne	Type	Description
id	bigint PK	
company_id	bigint FK → companies	
user_id	bigint FK → users	Le Manager qui reçoit les privilèges
granted_by	bigint FK → users	Qui a accordé (Super Admin ou Manager délégant)
can_create_forms	boolean default false	Autorisation de créer des questionnaires
can_create_users	boolean default false	Autorisation de créer des utilisateurs
can_delegate	boolean default false	Autorisation de déléguer ses propres privilèges à d'autres Managers
created_at / updated_at	timestamps	

Tables : submission_corrections, correction_fields
Gère le workflow de renvoi en correction. Une correction est liée à une soumission et contient le message optionnel du manager ainsi que la liste des champs ou sections ciblés.
Colonne	Type	Description
--- submission_corrections ---		
id	bigint PK	
submission_id	bigint FK → submissions	Soumission concernée
requested_by	bigint FK → users	Manager ou Super Admin ayant renvoyé
message	text nullable	Message explicatif pour l'employé (optionnel)
scope	enum	partial (champs ciblés uniquement) | full (toute la soumission)
status	enum	pending (en attente de correction) | corrected (soumis à nouveau)
corrected_at	timestamp nullable	Date de resoumission par l'employé
created_at / updated_at	timestamps	
--- correction_fields ---		
id	bigint PK	
correction_id	bigint FK → submission_corrections	
field_id	bigint FK → form_fields nullable	Champ ciblé (null si scope=full)
section_id	bigint FK → form_sections nullable	Section ciblée (null si scope=full ou ciblage par champ)

Mise à jour : table submissions
Ajout d'un champ status pour suivre l'état global de la soumission dans le cycle de vie incluant les corrections.
Colonne ajoutée	Type	Description
status	enum default 'submitted'	submitted | returned (renvoyée) | corrected (recorrigée et finale)

Colonne	Type	Description
id	uuid PK	UUID Laravel Notifications
type	string	Classe de notification PHP
notifiable_type / notifiable_id	morphs	Polymorphe → users
data	jsonb	Contenu de la notification (titre, lien, etc.)
read_at	timestamp nullable	Date de lecture (null = non lu)
created_at	timestamp	

5. Routes Laravel Suggérées
Toutes les routes retournent des vues Blade complètes ou des fragments Blade partiels (pour les requêtes HTMX). Pas d'API REST exposée. Les réponses HTMX utilisent l'en-tête HX-Request pour détecter si la requête vient de HTMX et retourner un partial ou une page complète.

5.1 Authentification
Aucune page d'inscription publique. Seuls le Super Admin et le Manager (selon privilège accordé) peuvent créer de nouveaux utilisateurs. À la première connexion, l'utilisateur est forcé de changer son mot de passe.
Méthode	URI	Controller	Description
GET	/login	AuthController@showLogin	Page connexion
POST	/login	AuthController@login	Traitement connexion
POST	/logout	AuthController@logout	Déconnexion
GET	/first-login	AuthController@showChangePassword	Forcer changement mot de passe (1ère connexion)
POST	/first-login	AuthController@changePassword	Enregistrer nouveau mot de passe

5.2 Module Questionnaires
Méthode	URI	Controller	Description
GET	/forms	FormController@index	Liste des questionnaires
GET	/forms/create	FormController@create	Créateur de formulaire
POST	/forms	FormController@store	Sauvegarder le formulaire
GET	/forms/{form}/edit	FormController@edit	Éditer un formulaire
PUT	/forms/{form}	FormController@update	Mettre à jour
DELETE	/forms/{form}	FormController@destroy	Supprimer
GET	/assignments	AssignmentController@index	Liste des assignations
POST	/assignments	AssignmentController@store	Assigner un questionnaire
GET	/assignments/{assignment}/fill	SubmissionController@show	Formulaire à remplir
POST	/assignments/{assignment}/submit	SubmissionController@store	Soumettre les réponses
GET	/submissions/{submission}	SubmissionController@detail	Détail d'une soumission
GET	/forms/{form}/export/pdf	ExportController@pdf	Export PDF
GET	/forms/{form}/export/excel	ExportController@excel	Export Excel
POST	/submissions/{submission}/return	CorrectionController@store	Renvoyer en correction (champs/sections ciblés + message optionnel)
GET	/submissions/{submission}/correct	CorrectionController@show	Vue correction côté employé (champs renvoyés déverrouillés)
POST	/submissions/{submission}/correct	CorrectionController@update	Soumettre la correction

5.3 Module GRH
La création d'utilisateur est réservée au Super Admin et aux Managers ayant le privilège 'can_create_users'. Les identifiants sont générés automatiquement à la création.
Méthode	URI	Controller	Description
GET	/employees	EmployeeController@index	Liste des employés
GET	/employees/create	EmployeeController@create	Formulaire création employé (Admin/Manager privilégié)
POST	/employees	EmployeeController@store	Créer l'utilisateur — génère username=lastname@phone.org + password=ML+phone + must_change_password=true
GET	/employees/{user}	EmployeeController@show	Profil employé
PUT	/employees/{user}	EmployeeController@update	Modifier profil
PATCH	/employees/{user}/toggle	EmployeeController@toggle	Activer / désactiver le compte
GET	/leave-requests	LeaveController@index	Liste des congés
POST	/leave-requests	LeaveController@store	Demander un congé
PATCH	/leave-requests/{req}/approve	LeaveController@approve	Approuver
PATCH	/leave-requests/{req}/reject	LeaveController@reject	Refuser
GET	/attendances	AttendanceController@index	Tableau des présences
POST	/attendances	AttendanceController@store	Saisir une présence
PUT	/attendances/{att}	AttendanceController@update	Corriger une présence
GET	/dashboard	DashboardController@index	Tableau de bord KPI
GET	/managers/{user}/privileges	PrivilegeController@edit	Gérer les privilèges d'un Manager (Super Admin)
PUT	/managers/{user}/privileges	PrivilegeController@update	Enregistrer les privilèges accordés
GET	/managers/{user}/delegate	PrivilegeController@delegateForm	Déléguer ses propres privilèges à un Manager
POST	/managers/{user}/delegate	PrivilegeController@delegate	Enregistrer la délégation (vérif : pas plus que soi-même)

5.4 Notifications
Méthode	URI	Controller	Description
GET	/notifications	NotificationController@index	Liste notifications (JSON partial)
PATCH	/notifications/{id}/read	NotificationController@markRead	Marquer comme lu
PATCH	/notifications/read-all	NotificationController@readAll	Tout marquer comme lu

6. Wireframes Textuels (Description des Écrans)
6.1 Dashboard Manager
En-tête : Logo entreprise | Navigation principale | Badge notifications | Avatar + menu déroulant (Profil, Déconnexion)
Zone principale (grille 2 colonnes sur desktop, 1 colonne mobile) :
    • Carte KPI 1 : Questionnaires actifs — nombre total / en attente / expirés
    • Carte KPI 2 : Taux de complétion global de l'équipe (%)
    • Carte KPI 3 : Congés en attente de validation
    • Carte KPI 4 : Présences aujourd'hui (présents / absent / non saisi)
    • Tableau : Dernières soumissions (employé, formulaire, date, statut) avec lien vers détail
    • Tableau : Demandes de congé en attente (employé, période, type) avec boutons Approuver / Refuser

6.2 Créateur de Questionnaire
Interface drag & drop (ou boutons + / –) organisée en 3 panneaux :
    • Panneau gauche : Bibliothèque de types de champs (Texte court, Texte long, Choix multiple, Case à cocher, Date, Nombre, Upload fichier, Note / étoiles)
    • Panneau central : Canvas du formulaire — sections déplaçables, champs ordonnables, prévisualisation en temps réel
    • Panneau droit : Propriétés du champ sélectionné (libellé, placeholder, obligatoire, options si applicable, validation)
Barre d'actions : Sauvegarder brouillon | Prévisualiser | Publier & Assigner

6.3 Interface Employé — Liste Questionnaires
Vue mobile-first. En-tête simple avec logo et menu hamburger.
    • Onglets : À faire | Complétés | Expirés
    • Chaque carte questionnaire : Titre, Manager assignant, Date d'échéance (badge rouge si < 24h), Bouton Remplir / Voir
    • Pas de tableau — cartes empilées verticalement pour facilité mobile

6.4 Formulaire de Remplissage (Employé)
Progression visible en haut (Section 1/3 — 40% complété). Navigation section par section.
    • Chaque champ rendu par son partial Blade dédié (_field_text.blade.php, _field_select.blade.php, _field_date.blade.php, _field_file.blade.php, _field_rating.blade.php...) avec composants DaisyUI
    • Validation côté client (Vee-Validate ou validation native HTML5) avant soumission
    • Bouton Suivant / Précédent entre sections, Soumettre en dernière section
    • Confirmation modale avant soumission définitive (non modifiable après)

6.5 Gestion des Présences
Vue calendrier mensuel (desktop) / liste hebdomadaire (mobile).
    • Chaque jour : indicateur coloré (Présent = vert, Absent = rouge, Non saisi = gris)
    • Clic sur un jour : modal de saisie (heure d'arrivée, heure de départ, note)
    • Filtres : par employé (manager), par département, par mois

7. Partials Blade, Composants Alpine.js & HTMX
Avec la stack Blade + HTMX + Alpine.js + DaisyUI, la réutilisabilité passe par les partials Blade (includes), les composants anonymes Blade (x-components), et les blocs Alpine.js. Organisation dans resources/views/components/ et resources/views/partials/.

7.1 Composants Blade (x-components)
Composant	Usage	Description
x-badge-status	<x-badge-status :status="$sub->status"/>	Badge DaisyUI coloré selon statut (pending/completed/returned/expired)
x-kpi-card	<x-kpi-card title="..." value="..." />	Carte statistique DaisyUI stat pour le dashboard
x-notification-bell	<x-notification-bell :count="$unread"/>	Cloche DaisyUI avec badge compteur non-lus
x-confirm-modal	<x-confirm-modal id="..." message="..."/>	Modal DaisyUI de confirmation générique (dialog natif HTML)
x-data-table	<x-data-table :rows="$rows" :cols="$cols"/>	Tableau DaisyUI paginé avec filtres
x-field-text	Partial champ texte court/long	Input ou textarea DaisyUI avec label, placeholder, validation
x-field-select	Partial champ select	Select DaisyUI simple ou multiple avec options dynamiques
x-field-date	Partial champ date	Input date DaisyUI avec contraintes min/max
x-field-file	Partial champ upload	Input file DaisyUI avec prévisualisation Alpine.js
x-field-rating	Partial champ étoiles	Rating DaisyUI interactif (composant natif DaisyUI)
x-field-number	Partial champ numérique	Input number DaisyUI avec min/max/step

7.2 Rôles HTMX vs Alpine.js
La règle de séparation est simple : HTMX pour tout ce qui implique le serveur, Alpine.js pour tout ce qui est purement visuel côté client.
Fonctionnalité	Outil	Exemple
Ajouter un champ au créateur	HTMX	hx-post='/forms/fields/add' hx-target='#fields-list' hx-swap='beforeend'
Supprimer un champ	HTMX	hx-delete='/forms/fields/{id}' hx-target='#field-{id}' hx-swap='outerHTML'
Boutons ▲ ▼ réordonner	HTMX	hx-patch='/forms/fields/{id}/move' hx-vals='{"direction":"up"}'
Champs conditionnels (show/hide)	Alpine.js	x-show="answer === 'oui'" sur le champ dépendant
Ouvrir/fermer une modale	Alpine.js	x-on:click="$refs.modal.showModal()" sur DaisyUI dialog
Preview fichier uploadé	Alpine.js	x-on:change="previewFile($event)" sur input file
Compteur caractères textarea	Alpine.js	x-text="maxLength - text.length" en temps réel
Soumission formulaire partielle	HTMX	hx-post='/assignments/{id}/submit' hx-indicator='#spinner'
Filtrer un tableau	HTMX	hx-get='/employees' hx-trigger='change' hx-target='#table-body'
Badge notifications refresh	HTMX	hx-get='/notifications/count' hx-trigger='every 60s'

7.3 PWA — Configuration vite-plugin-pwa
Avec Blade (MPA classique), la configuration PWA est directe sans contournements. Chaque navigation est une vraie requête HTTP que le Service Worker comprend nativement.
Paramètre PWA	Valeur / Description
registerType	autoUpdate — le SW se met à jour silencieusement
navigateFallback	/offline — page Blade dédiée affichée si pas de réseau (impossible avec Inertia)
globPatterns	**/*.{js,css,png,svg,woff2} — assets statiques mis en cache
Strategy assets	CacheFirst — assets servis depuis le cache, réseau en fallback
Strategy pages	NetworkFirst — pages toujours depuis le réseau, cache en fallback offline
Manifest name	GRH Terrain
Manifest display	standalone — plein écran sans barre navigateur
Manifest start_url	/dashboard
Icônes requises	192x192 et 512x512 PNG pour installation mobile

8. Plan de Développement par Phases
Phase MVP — Fonctionnalités Core (Semaines 1–8)
Sprint 1–2 : Fondations (2 semaines)
    • Setup Laravel 11 + Tailwind CSS v4 + DaisyUI v5 + HTMX + Alpine.js (via npm ou CDN)
    • Configuration PostgreSQL, migrations de base (companies, users avec firstname, lastname, username, phone, must_change_password)
    • Authentification Laravel Breeze version Blade (pas Vue) — login par username, zéro page d'inscription publique
    • Middleware ForcePasswordChange — redirige vers /first-login si must_change_password = true, bloquant toute autre route
    • Logique de génération automatique des identifiants : username = strtolower(lastname)@phone.org — password = ML+phone
    • Rôles Super Admin, Manager, Employé — Spatie/Permission — permission can_create_users, can_create_forms, can_delegate
    • Layout principal responsive Blade + DaisyUI (drawer sidebar desktop, bottom nav mobile)
    • Configuration PWA : vite-plugin-pwa + manifest.json + page /offline + Service Worker MPA
    • Système de notifications — table DB + badge HTMX (hx-trigger='every 60s' pour refresh auto du compteur)

Sprint 3–4 : Module Questionnaires (2 semaines)
    • Migrations forms, form_sections, form_fields
    • CRUD complet formulaires côté Manager — vues Blade + DaisyUI
    • Créateur de formulaires : interface Blade avec HTMX pour ajout/suppression/réordonnancement (▲ ▼) de sections et champs sans rechargement de page
    • Partials Blade pour chaque type de champ : x-field-text, x-field-select, x-field-date, x-field-file, x-field-rating, x-field-number
    • Prévisualisation du formulaire avant publication — page Blade dédiée
    • Upload fichiers via input standard avec prévisualisation Alpine.js (x-on:change)

Sprint 5–6 : Assignation & Soumissions (2 semaines)
    • Migrations assignments, submissions, answers
    • Assignation questionnaire à un ou plusieurs employés avec date d'échéance — formulaire Blade + HTMX
    • Interface employé : liste questionnaires assignés DaisyUI cards + formulaire de remplissage sectionné en Blade
    • Champs conditionnels dès le MVP : logique Alpine.js (x-show / x-if) basée sur les règles config JSON du champ
    • Validation et soumission définitive HTMX — mise à jour statut assignment sans rechargement de page
    • Notifications email (queue database driver) lors d'assignation
    • Rappels automatiques par job schedulé (assignments pending avant échéance)

Sprint 7–8 : GRH de Base, Dashboard & Corrections (2 semaines)
    • Gestion profils employés (CRUD complet, avatar upload)
    • Module congés : demande employé, validation/refus manager, solde manuel
    • Saisie présences manuelle (employé et manager) — vue calendrier
    • Export PDF des soumissions (DomPDF — template Blade dédié)
    • Export Excel des réponses (Laravel Excel / Maatwebsite)
    • Dashboard Manager : KPI cards + tableaux récapitulatifs
    • Workflow renvoi en correction : CorrectionController, tables submission_corrections + correction_fields, vue correction employé avec champs partiellement déverrouillés
    • Gestion des privilèges Manager : PrivilegeController, table manager_privileges, interface Super Admin + délégation Manager → Manager

Phase V2 — Améliorations (Semaines 9–14)
    • Champ Signature (Signature Pad canvas JS — V2 comme décidé)
    • Migration stockage fichiers vers S3 / Cloudflare R2
    • Notifications temps réel WebSocket (Laravel Reverb)
    • Évaluations de performance liées aux questionnaires soumis
    • Tableau de bord analytics avancé (graphiques Chart.js vanilla intégré dans Blade via Alpine.js)
    • Questionnaires conditionnels (affichage de champ selon réponse précédente)
    • Import/export de modèles de questionnaires (JSON)

Phase V3 — Multi-Tenant & Scale (Semaines 15+)
    • Architecture multi-tenant complète (Spatie Multitenancy ou scoping company_id — déjà préparé)
    • Portail d'inscription en libre-service pour nouvelles entreprises
    • Plans et facturation (Stripe / Laravel Cashier)
    • Migration Redis pour queues et cache
    • API REST exposée optionnelle pour intégrations tierces
    • i18n complet multi-langues (structure déjà préparée dès MVP)

9. Critères d'Acceptance
9.1 Module Questionnaires
Fonctionnalité	Critères d'acceptance
Création de formulaire	Le manager peut créer un formulaire avec au moins 6 types de champs. L'ordre des champs est modifiable. Le formulaire est sauvegardable en brouillon et publiable.
Assignation	Un questionnaire peut être assigné à 1 à N employés simultanément. Chaque assignation génère une notification email. La date d'échéance est optionnelle.
Soumission employé	L'employé ne peut soumettre qu'une fois par assignation. La soumission est définitive (non modifiable). Le statut de l'assignment passe à 'completed' immédiatement.
Export PDF	Le PDF généré contient : titre du formulaire, nom de l'employé, date de soumission, toutes les questions et réponses. Généré en < 10 secondes.
Export Excel	Le fichier Excel contient une ligne par soumission et une colonne par champ. Les fichiers uploadés sont référencés par leur URL.
Rappels automatiques	Un email de rappel est envoyé 24h avant l'échéance si le statut est toujours 'pending'. Un seul rappel par assignation.

9.2 Module GRH
Fonctionnalité	Critères d'acceptance
Renvoi en correction	Le manager sélectionne scope=partial (liste de champs/sections) ou scope=full (toute la soumission). Le message est optionnel. La soumission passe au statut 'returned'. L'employé reçoit une notification. Les champs non ciblés en mode partial sont en lecture seule pour l'employé lors de la correction. Après resoumission, le statut passe à 'corrected'. Un manager peut renvoyer plusieurs fois la même soumission (chaque renvoi crée une nouvelle correction_correction, la précédente étant archivée).
Délégation de privilèges	Un Manager ne peut accorder à un autre Manager que les privilèges qu'il possède lui-même (vérification côté Policy Laravel). Le Super Admin peut accorder n'importe quel privilège. L'interface de délégation n'affiche que les privilèges que le délégant possède. Un log de qui a accordé quoi est conservé via le champ granted_by.
Profil employé	Les champs firstname, lastname et phone sont obligatoires à la création. Le username (lastname@phone.org) et le mot de passe initial (MLphone) sont générés automatiquement sans saisie manuelle. must_change_password est mis à true à la création. À la première connexion, l'utilisateur est redirigé vers /first-login et ne peut pas accéder à une autre page avant d'avoir changé son mot de passe.
Demande de congé	L'employé soumet une demande avec type, dates de début/fin et motif optionnel. Le manager reçoit une notification. Le manager peut approuver ou refuser avec commentaire.
Saisie présence	La saisie manuelle est possible par l'employé ET le manager. Une seule entrée par employé par jour. La modification d'une entrée existante est tracée (entered_by).
Dashboard KPI	Le dashboard affiche : taux de complétion des questionnaires, nombre de congés en attente, récapitulatif présences du mois. Les données sont rafraîchies à chaque chargement de page.

9.3 Contraintes Techniques
Contrainte	Critères de vérification
Blade + HTMX	Zéro API REST exposée publiquement. Les requêtes HTMX sont détectées via l'en-tête HX-Request et retournent des partials Blade. Les navigations classiques retournent des pages Blade complètes.
Alpine.js	Utilisé exclusivement pour les interactions purement côté client : show/hide champs conditionnels, modales, preview fichier, état UI local. Aucune logique métier dans Alpine.js.
Mobile-first	L'interface employé est testée et validée sur viewport 375px (iPhone SE). Tous les formulaires sont utilisables au pouce. Les composants DaisyUI sont responsive par défaut.
company_id partout	Toutes les requêtes DB sont scopées sur company_id via un middleware ou un scope Eloquent global. Aucune donnée cross-company n'est accessible.
Queues	Les emails et rappels passent par Laravel Queue (driver database MVP). Aucun email n'est envoyé de façon synchrone dans les controllers.
Stockage fichiers	Les fichiers sont stockés via Laravel Storage (disque 'local' pointant vers le volume Railway). Le chemin est relatif, pas absolu, pour faciliter la migration S3.

10. Notes d'Architecture & Points d'Attention
10.1 Dette Technique Acceptée (MVP → V2)
    • Stockage fichiers local (Railway volume) : migration vers S3/R2 obligatoire avant multi-tenant. Utiliser Storage::disk() avec variable d'environnement FILESYSTEM_DISK pour faciliter le switch.
    • Badge notifications HTMX polling (every 60s) : migration vers WebSocket (Laravel Reverb) en V2 si le besoin de temps réel strict émerge.
    • Queues driver database : passer à Redis (Upstash sur Railway) en production V2 pour la fiabilité et les performances.
    • Champ signature reporté en V2 : prévoir le type 'signature' dans l'enum form_fields.type dès le MVP pour éviter une migration ALTER TABLE. Implémentation via Signature Pad JS + Alpine.js.
    • PWA offline partiel : en MVP le cache couvre uniquement les assets statiques et la page /offline. En V2 envisager le cache des questionnaires assignés pour consultation hors réseau (lecture seule).

10.2 Logique de Génération des Identifiants
À chaque création d'utilisateur (par Super Admin ou Manager privilégié), les identifiants sont générés automatiquement selon ces règles :
Champ	Règle de génération	Exemple
username	strtolower(lastname) + '@' + phone + '.org'	dupont@0612345678.org
password initial	'ML' + phone (hashé bcrypt)	ML0612345678
must_change_password	true — toujours à la création	
    • Le username doit être unique en base. En cas de collision (deux Dupont avec le même téléphone), une exception est levée — cas quasi impossible en pratique mais à gérer avec un message d'erreur clair.
    • Le middleware ForcePasswordChange intercepte toutes les requêtes HTTP après login (y compris les requêtes HTMX via HX-Request). Si must_change_password = true, il redirige vers /first-login quelle que soit la route demandée.
    • Après changement de mot de passe réussi, must_change_password passe à false et l'utilisateur est redirigé vers son dashboard.
    • Le mot de passe initial ne doit jamais être affiché ou envoyé par email en clair dans la V1 — le Manager communique les identifiants directement à l'employé.

10.3 Logique de Délégation de Privilèges
Le système de privilèges suit une chaîne de délégation stricte : chaque acteur ne peut donner que ce qu'il possède.
Règle	Implémentation
Super Admin peut tout accorder	Bypass de la vérification de possession — rôle super-admin dans Spatie
Manager ne délègue que ce qu'il a	PrivilegePolicy@delegate vérifie que chaque privilège accordé existe dans manager_privileges du délégant
Révocation en cascade	Si un Manager perd un privilège, ses délégations de ce privilège sont automatiquement révoquées (observer Eloquent)
Audit trail	Le champ granted_by trace qui a accordé quoi — visible dans l'interface Super Admin

10.4 Workflow Renvoi en Correction
Le cycle de vie d'une soumission avec correction suit ces états :
Statut submission	Déclencheur	Qui peut agir
submitted	Employé soumet le formulaire	Manager/Admin peut lire ou renvoyer
returned	Manager/Admin crée une correction	Employé reçoit notif et doit corriger
corrected	Employé resoumet la correction	Manager/Admin peut lire ou renvoyer à nouveau
    • En mode partial, le partial Blade de chaque champ reçoit une variable $disabled (liste des field_id verrouillés). Les champs non ciblés sont rendus avec l'attribut disabled et un style visuel distinct (DaisyUI input-disabled). Alpine.js empêche toute interaction côté client sur ces champs.
    • En mode full, tous les champs sont éditables — comportement identique à la soumission initiale.
    • Chaque renvoi crée un nouvel enregistrement submission_corrections. L'historique complet des allers-retours est donc conservé et visible dans le détail de la soumission.
    • Un renvoi en correction remet le statut de l'assignment à 'pending' visuellement côté manager (en attente de correction), sans créer un nouvel assignment.

10.5 Sécurité
    • Toutes les actions sont protégées par des Policies Laravel (FormPolicy, AssignmentPolicy, CorrectionPolicy, PrivilegePolicy, etc.) — ne pas se fier uniquement aux rôles Spatie.
    • Les fichiers uploadés sont stockés hors du dossier public. L'accès se fait via des routes signées (Storage::temporaryUrl()) en V2 S3, ou via un controller servant le fichier avec vérification d'autorisation en MVP.
    • Les exports PDF/Excel sont générés à la demande et servis directement — pas stockés sur le disque.
    • CSRF protection Laravel activée sur toutes les routes POST/PUT/PATCH/DELETE. HTMX envoie automatiquement le token CSRF via un meta tag configuré dans le layout Blade principal.

10.6 Performance
    • Pagination systématique sur tous les tableaux (15 items par page par défaut).
    • Eager loading des relations (with()) dans tous les controllers pour éviter le problème N+1.
    • Index DB à créer : users(company_id), assignments(form_id, assigned_to, status, due_at), submissions(assignment_id), answers(submission_id, field_id), attendances(user_id, date), leave_requests(user_id, status).

10.7 Déploiement Railway
    • Service Web : application Laravel (Nixpacks ou Dockerfile).
    • Service Worker : php artisan queue:work — second service dans le même projet Railway.
    • Service Scheduler : php artisan schedule:work ou cron via Railway Cron Jobs pour les rappels automatiques.
    • Base de données : PostgreSQL plugin Railway natif.
    • Volume persisté : monté sur /app/storage pour les uploads fichiers.
    • Variables d'environnement : APP_KEY, DB_*, MAIL_*, QUEUE_CONNECTION=database, FILESYSTEM_DISK=local.

— Fin du PRD —
Document généré et validé avant démarrage du développement