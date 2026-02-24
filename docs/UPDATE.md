# PROMPT CORRECTIF COMPLET
## Suppression groupes + Création utilisateur dynamique + Assignation questionnaires
> Modification ciblée uniquement — ne toucher à rien d'autre

---

## CONTEXTE

Trois corrections à apporter sur l'application existante :

1. **Supprimer** complètement le concept de groupes (table, colonne, controller, vues)
2. **Créer** un formulaire d'utilisateur dynamique selon le type choisi avec gestion des privilèges
3. **Corriger** l'assignation des questionnaires : mode global (superviseur / employé / les deux)
   et mode individuel (sélection multiple mixte sans notion de groupe)

**Règle absolue : pas de table `groups`, pas de `group_id`.**
La hiérarchie seule organise les utilisateurs : Super Admin → Manager → Superviseur → Employé.

---

## ORDRE D'EXÉCUTION OBLIGATOIRE

```
1. Migrations (dans l'ordre exact ci-dessous)
2. Suppression fichiers obsolètes (Group, GroupController, vues groups/)
3. Modèles Eloquent mis à jour
4. Controllers
5. Routes
6. Vues Blade + Alpine.js
7. Tests Pest
```

---

## PARTIE 1 — SUPPRESSION DES GROUPES

### Fichiers à supprimer immédiatement
```
✗ app/Models/Group.php
✗ app/Http/Controllers/GroupController.php
✗ resources/views/groups/  (tout le dossier)
✗ Toute référence à group_id dans les controllers, vues, factories, seeders
```

### Routes à supprimer dans routes/web.php
```php
// Supprimer ces lignes :
Route::resource('groups', GroupController::class);
// ou toute route commençant par /groups
```

### Migration 1 — Supprimer group_id de users
```php
Schema::table('users', function (Blueprint $table) {
    $table->dropForeign(['group_id']);
    $table->dropColumn('group_id');
});
```

### Migration 2 — Dropper la table groups
```php
// S'assurer que group_id est retiré de users avant de dropper groups
Schema::dropIfExists('groups');
```

---

## PARTIE 2 — BASE DE DONNÉES (form_assignments)

### Migration 3 — Supprimer scope_user_id, ajouter scope_role 'both', créer pivot

```php
// Étape A : retirer scope_user_id de form_assignments
Schema::table('form_assignments', function (Blueprint $table) {
    $table->dropForeign(['scope_user_id']);
    $table->dropColumn('scope_user_id');
});

// Étape B : étendre scope_role pour supporter 'both'
Schema::table('form_assignments', function (Blueprint $table) {
    $table->dropColumn('scope_role');
});
Schema::table('form_assignments', function (Blueprint $table) {
    $table->enum('scope_role', ['superviseur', 'employe', 'both'])
          ->nullable()
          ->after('scope_type');
});

// Étape C : créer table pivot pour sélection individuelle multiple
Schema::create('form_assignment_users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('form_assignment_id')
          ->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')
          ->constrained()->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['form_assignment_id', 'user_id']);
    $table->index(['user_id']);
});
```

### Table form_assignments — structure finale
```
id
company_id         FK → companies
form_id            FK → forms
assigned_by        FK → users
scope_type         enum : role | individual
scope_role         enum nullable : superviseur | employe | both
                   → utilisé si scope_type = role
                   → both = Superviseurs + Employés en même temps
due_at             timestamp nullable (Type 1 uniquement)
is_active          boolean default true
created_at / updated_at

→ scope_type = individual : destinataires dans form_assignment_users (pivot)
→ scope_type = role       : scope_role définit qui est concerné
```

---

## PARTIE 3 — MODÈLES ELOQUENT

### Modèle User — retirer toute référence aux groupes
```php
// Supprimer :
// protected $fillable : retirer 'group_id'
// public function group() : supprimer cette relation

// Relations hiérarchiques à conserver/ajouter :
public function manager(): BelongsTo
{
    return $this->belongsTo(User::class, 'manager_id');
}

public function supervisor(): BelongsTo
{
    return $this->belongsTo(User::class, 'supervisor_id');
}

public function superviseurs(): HasMany
{
    return $this->hasMany(User::class, 'manager_id')
                ->where('role', 'superviseur');
}

public function employes(): HasMany
{
    return $this->hasMany(User::class, 'supervisor_id')
                ->where('role', 'employe');
}
```

### Modèle FormAssignment — complet
```php
class FormAssignment extends Model
{
    protected $fillable = [
        'company_id', 'form_id', 'assigned_by',
        'scope_type', 'scope_role', 'due_at', 'is_active',
    ];

    protected $casts = [
        'due_at'    => 'datetime',
        'is_active' => 'boolean',
    ];

    // Pivot — users sélectionnés individuellement
    public function selectedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'form_assignment_users')
                    ->withTimestamps();
    }

    // Résoudre tous les destinataires effectifs
    public function resolveRecipients(): Collection
    {
        if ($this->scope_type === 'individual') {
            return $this->selectedUsers;
        }

        $manager = User::find($this->assigned_by);
        $superviseurIds = User::where('manager_id', $manager->id)->pluck('id');

        $recipients = collect();

        if (in_array($this->scope_role, ['superviseur', 'both'])) {
            $recipients = $recipients->merge(
                User::whereIn('id', $superviseurIds)
                    ->where('is_active', true)->get()
            );
        }

        if (in_array($this->scope_role, ['employe', 'both'])) {
            $recipients = $recipients->merge(
                User::whereIn('supervisor_id', $superviseurIds)
                    ->where('is_active', true)->get()
            );
        }

        return $recipients->unique('id');
    }
}
```

---

## PARTIE 4 — CRÉATION D'UTILISATEUR DYNAMIQUE

### Règles de qui peut créer quoi
```
Super Admin   → Manager, Superviseur, Employé
               → Définit les privilèges de tous les types

Manager       → Superviseur (si can_create_superviseurs)
               → Employé (si can_create_employes)
               → Définit les privilèges du Superviseur et Employé
               → NE PEUT PAS créer un Manager

Superviseur   → Employé uniquement (si can_create_employes)
               → NE PEUT PAS créer Manager ni Superviseur
               → Pas de privilèges à définir pour un Employé
```

### Formulaire dynamique — logique Alpine.js
```
Étape 1 : Sélecteur de type (radio — options filtrées selon rôle du créateur)
  [ ] Manager      → visible si Super Admin
  [ ] Superviseur  → visible si Super Admin ou Manager avec can_create_superviseurs
  [ ] Employé      → visible si Super Admin, Manager ou Superviseur avec can_create_employes

Étape 2 : Champs communs (toujours visibles)
  Prénom, Nom, Téléphone

Étape 3 : Champs conditionnels selon type sélectionné

  type = manager :
    Privilèges (checkboxes — Super Admin uniquement) :
      □ can_create_forms        → Peut créer des questionnaires
      □ can_create_superviseurs → Peut créer des Superviseurs
      □ can_create_employes     → Peut créer des Employés
      □ can_delegate            → Peut déléguer ses privilèges

  type = superviseur :
    Dropdown → Son Manager (liste des Managers de la company)
    Privilèges (checkboxes — filtrées selon ce que le créateur possède) :
      □ can_create_employes     → Peut créer des Employés

  type = employe :
    Dropdown → Son Superviseur
      Si créateur = Manager    : liste tous ses Superviseurs
      Si créateur = Superviseur : pré-rempli avec lui-même (non modifiable)
    Pas de section privilèges
```

### UserController@create
```php
public function create()
{
    $user = auth()->user();

    $creatableRoles = match($user->role) {
        'super_admin' => ['manager', 'superviseur', 'employe'],
        'manager'     => collect([
            $user->hasPrivilege('can_create_superviseurs') ? 'superviseur' : null,
            $user->hasPrivilege('can_create_employes')     ? 'employe'     : null,
        ])->filter()->values()->toArray(),
        'superviseur' => $user->hasPrivilege('can_create_employes')
                         ? ['employe'] : [],
        default       => [],
    };

    abort_if(empty($creatableRoles), 403);

    $managers = User::where('company_id', $user->company_id)
                    ->where('role', 'manager')
                    ->where('is_active', true)
                    ->orderBy('lastname')->get();

    $superviseurs = User::where('company_id', $user->company_id)
                        ->where('role', 'superviseur')
                        ->where('is_active', true)
                        ->when($user->role === 'manager',
                            fn($q) => $q->where('manager_id', $user->id))
                        ->when($user->role === 'superviseur',
                            fn($q) => $q->where('id', $user->id))
                        ->orderBy('lastname')->get();

    $availablePrivileges = $this->resolveAvailablePrivileges($user);

    return view('users.create', compact(
        'creatableRoles', 'managers', 'superviseurs', 'availablePrivileges'
    ));
}
```

### UserController@store
```php
public function store(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'role'          => 'required|in:manager,superviseur,employe',
        'firstname'     => 'required|string|max:100',
        'lastname'      => 'required|string|max:100',
        'phone'         => 'required|string|max:20',
        'manager_id'    => 'required_if:role,superviseur|nullable|exists:users,id',
        'supervisor_id' => 'required_if:role,employe|nullable|exists:users,id',
        'privileges'    => 'nullable|array',
        'privileges.*'  => 'in:can_create_forms,can_create_superviseurs,can_create_employes,can_delegate',
    ]);

    $this->authorize('createRole', [$validated['role']]);

    // Génération identifiants
    $base     = strtolower($validated['lastname']) . '@' . $validated['phone'] . '.org';
    $username = $base;
    $i = 2;
    while (User::where('username', $username)->exists()) {
        $username = str_replace('.org', $i . '.org', $base);
        $i++;
    }
    $plainPassword = 'ML' . $validated['phone'];

    $newUser = User::create([
        'company_id'           => $user->company_id,
        'firstname'            => $validated['firstname'],
        'lastname'             => $validated['lastname'],
        'phone'                => $validated['phone'],
        'username'             => $username,
        'password'             => bcrypt($plainPassword),
        'must_change_password' => true,
        'role'                 => $validated['role'],
        'manager_id'           => $validated['manager_id'] ?? null,
        'supervisor_id'        => $validated['supervisor_id'] ?? null,
        'is_active'            => true,
    ]);

    $newUser->assignRole($validated['role']);

    // Sauvegarder les privilèges (filtrés selon ce que le créateur peut accorder)
    if (!empty($validated['privileges'])) {
        $available = $this->resolveAvailablePrivileges($user);
        $privData  = array_fill_keys([
            'can_create_forms', 'can_create_superviseurs',
            'can_create_employes', 'can_delegate',
        ], false);

        foreach ($validated['privileges'] as $priv) {
            if (in_array($priv, $available)) {
                $privData[$priv] = true;
            }
        }

        UserPrivilege::create(array_merge([
            'company_id' => $user->company_id,
            'user_id'    => $newUser->id,
            'granted_by' => $user->id,
        ], $privData));
    }

    return redirect()->route('users.index')
                     ->with('success',
                         "Compte créé — Login : {$username} / Mot de passe : {$plainPassword}");
}

private function resolveAvailablePrivileges(User $creator): array
{
    if ($creator->role === 'super_admin') {
        return ['can_create_forms', 'can_create_superviseurs',
                'can_create_employes', 'can_delegate'];
    }
    $priv = UserPrivilege::where('user_id', $creator->id)->first();
    if (!$priv) return [];

    return collect([
        'can_create_forms'        => $priv->can_create_forms,
        'can_create_superviseurs' => $priv->can_create_superviseurs,
        'can_create_employes'     => $priv->can_create_employes,
        'can_delegate'            => $priv->can_delegate,
    ])->filter()->keys()->toArray();
}
```

### Vue users/create.blade.php
```html
<div x-data="createUser()" class="max-w-2xl mx-auto">

    {{-- Étape 1 : Type --}}
    <div class="form-control mb-6">
        <label class="label">
            <span class="label-text font-semibold text-base">Type d'utilisateur</span>
        </label>
        <div class="flex flex-wrap gap-3">
            @foreach($creatableRoles as $role)
            <label class="label cursor-pointer gap-2 border rounded-lg px-4 py-2
                          hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                <input type="radio" name="role" value="{{ $role }}"
                       class="radio radio-primary radio-sm"
                       x-model="selectedRole"/>
                <span class="label-text capitalize">{{ ucfirst($role) }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <input type="hidden" name="role" :value="selectedRole"/>

        {{-- Étape 2 : Champs communs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="form-control">
                <label class="label"><span class="label-text">Prénom *</span></label>
                <input type="text" name="firstname" required
                       class="input input-bordered" placeholder="Prénom"/>
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Nom *</span></label>
                <input type="text" name="lastname" required
                       class="input input-bordered" placeholder="Nom"/>
            </div>
            <div class="form-control sm:col-span-2">
                <label class="label"><span class="label-text">Téléphone *</span></label>
                <input type="text" name="phone" required
                       class="input input-bordered" placeholder="Ex: 0612345678"/>
                <label class="label">
                    <span class="label-text-alt text-base-content/60">
                        Sert à générer automatiquement les identifiants de connexion
                    </span>
                </label>
            </div>
        </div>

        {{-- Étape 3A : Superviseur → dropdown Manager --}}
        <div x-show="selectedRole === 'superviseur'" class="form-control mb-4">
            <label class="label"><span class="label-text">Son Manager *</span></label>
            <select name="manager_id" class="select select-bordered w-full">
                <option value="">— Choisir un Manager —</option>
                @foreach($managers as $m)
                <option value="{{ $m->id }}">{{ $m->firstname }} {{ $m->lastname }}</option>
                @endforeach
            </select>
        </div>

        {{-- Étape 3B : Employé → dropdown Superviseur --}}
        <div x-show="selectedRole === 'employe'" class="form-control mb-4">
            <label class="label"><span class="label-text">Son Superviseur *</span></label>
            <select name="supervisor_id" class="select select-bordered w-full">
                <option value="">— Choisir un Superviseur —</option>
                @foreach($superviseurs as $s)
                <option value="{{ $s->id }}">{{ $s->firstname }} {{ $s->lastname }}</option>
                @endforeach
            </select>
        </div>

        {{-- Étape 3C : Privilèges (Manager + Superviseur) --}}
        <div x-show="selectedRole === 'manager' || selectedRole === 'superviseur'"
             class="mb-6">
            <div class="divider text-sm">Privilèges</div>
            <div class="flex flex-col gap-2">

                @if(in_array('can_create_forms', $availablePrivileges))
                <label class="label cursor-pointer justify-start gap-3"
                       x-show="selectedRole === 'manager'">
                    <input type="checkbox" name="privileges[]"
                           value="can_create_forms" class="checkbox checkbox-primary"/>
                    <span class="label-text">Peut créer des questionnaires</span>
                </label>
                @endif

                @if(in_array('can_create_superviseurs', $availablePrivileges))
                <label class="label cursor-pointer justify-start gap-3"
                       x-show="selectedRole === 'manager'">
                    <input type="checkbox" name="privileges[]"
                           value="can_create_superviseurs" class="checkbox checkbox-primary"/>
                    <span class="label-text">Peut créer des Superviseurs</span>
                </label>
                @endif

                @if(in_array('can_create_employes', $availablePrivileges))
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="privileges[]"
                           value="can_create_employes" class="checkbox checkbox-primary"/>
                    <span class="label-text">Peut créer des Employés</span>
                </label>
                @endif

                @if(in_array('can_delegate', $availablePrivileges))
                <label class="label cursor-pointer justify-start gap-3"
                       x-show="selectedRole === 'manager'">
                    <input type="checkbox" name="privileges[]"
                           value="can_delegate" class="checkbox checkbox-primary"/>
                    <span class="label-text">Peut déléguer ses privilèges</span>
                </label>
                @endif

            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Créer le compte
        </button>
    </form>
</div>

<script>
function createUser() {
    return {
        selectedRole: '{{ $creatableRoles[0] ?? "" }}',
    }
}
</script>
```

---

## PARTIE 5 — ASSIGNATION DES QUESTIONNAIRES

### FormAssignmentController@store — version finale
```php
public function store(Form $form, Request $request)
{
    $this->authorize('assign', $form);

    $validated = $request->validate([
        'scope_type'  => 'required|in:role,individual',
        'scope_role'  => 'required_if:scope_type,role|nullable|in:superviseur,employe,both',
        'user_ids'    => 'required_if:scope_type,individual|nullable|array|min:1',
        'user_ids.*'  => 'exists:users,id',
        'due_at'      => 'nullable|date|after:now',
    ]);

    $assignment = FormAssignment::create([
        'company_id'  => auth()->user()->company_id,
        'form_id'     => $form->id,
        'assigned_by' => auth()->id(),
        'scope_type'  => $validated['scope_type'],
        'scope_role'  => $validated['scope_role'] ?? null,
        'due_at'      => $validated['due_at'] ?? null,
        'is_active'   => true,
    ]);

    if ($validated['scope_type'] === 'individual') {
        $this->validateUsersInBranch($validated['user_ids']);
        $assignment->selectedUsers()->attach($validated['user_ids']);
    }

    NotifyFormAssigned::dispatch($assignment)->onQueue('default');

    return redirect()->back()->with('success', 'Questionnaire assigné.');
}

private function validateUsersInBranch(array $userIds): void
{
    $manager        = auth()->user();
    $superviseurIds = User::where('manager_id', $manager->id)->pluck('id');
    $employeIds     = User::whereIn('supervisor_id', $superviseurIds)->pluck('id');
    $authorizedIds  = $superviseurIds->merge($employeIds)->toArray();

    if (!empty(array_diff($userIds, $authorizedIds))) {
        abort(403, 'Certains utilisateurs ne sont pas dans votre branche.');
    }
}
```

### Controller — charger branchUsers pour la vue
```php
// Dans FormController@index — passer aux vues
$manager        = auth()->user();
$superviseurIds = User::where('manager_id', $manager->id)->pluck('id');

$branchUsers = User::where('company_id', $manager->company_id)
    ->where('is_active', true)
    ->where(function ($q) use ($manager, $superviseurIds) {
        $q->where('manager_id', $manager->id)
          ->orWhereIn('supervisor_id', $superviseurIds);
    })
    ->orderBy('role')
    ->orderBy('lastname')
    ->get();
```

### Vue — Modal d'assignation (dans forms/index.blade.php)
```html
<button onclick="document.getElementById('modal-assign-{{ $form->id }}').showModal()"
        class="btn btn-sm btn-primary">
    Assigner
</button>

<dialog id="modal-assign-{{ $form->id }}" class="modal">
  <div class="modal-box w-11/12 max-w-2xl" x-data="assignForm()">
    <h3 class="font-bold text-lg mb-4">Assigner — {{ $form->title }}</h3>

    <form method="POST" action="/forms/{{ $form->id }}/assign">
        @csrf

        {{-- Mode --}}
        <div class="form-control mb-4">
            <label class="label">
                <span class="label-text font-medium">Mode d'assignation</span>
            </label>
            <div class="flex flex-wrap gap-3">
                <label class="label cursor-pointer gap-2 border rounded-lg px-3 py-2
                              hover:bg-base-200 has-[:checked]:border-primary">
                    <input type="radio" name="scope_type" value="role"
                           class="radio radio-primary radio-sm" x-model="scopeType"/>
                    <span class="label-text">Global par type</span>
                </label>
                <label class="label cursor-pointer gap-2 border rounded-lg px-3 py-2
                              hover:bg-base-200 has-[:checked]:border-primary">
                    <input type="radio" name="scope_type" value="individual"
                           class="radio radio-primary radio-sm" x-model="scopeType"/>
                    <span class="label-text">Sélection individuelle</span>
                </label>
            </div>
        </div>

        {{-- MODE GLOBAL --}}
        <div x-show="scopeType === 'role'" class="form-control mb-4">
            <label class="label">
                <span class="label-text font-medium">Assigner à</span>
            </label>
            <div class="flex flex-col gap-2">
                <label class="label cursor-pointer justify-start gap-3 border rounded-lg px-3 py-2
                              hover:bg-base-200 has-[:checked]:border-primary">
                    <input type="radio" name="scope_role" value="employe"
                           class="radio radio-primary radio-sm"/>
                    <span class="label-text">Tous les Employés</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 border rounded-lg px-3 py-2
                              hover:bg-base-200 has-[:checked]:border-primary">
                    <input type="radio" name="scope_role" value="superviseur"
                           class="radio radio-primary radio-sm"/>
                    <span class="label-text">Tous les Superviseurs</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 border rounded-lg px-3 py-2
                              hover:bg-base-200 has-[:checked]:border-primary">
                    <input type="radio" name="scope_role" value="both"
                           class="radio radio-primary radio-sm"/>
                    <span class="label-text">
                        Superviseurs <span class="badge badge-ghost badge-sm">+</span> Employés
                    </span>
                </label>
            </div>
        </div>

        {{-- MODE INDIVIDUEL --}}
        <div x-show="scopeType === 'individual'" class="form-control mb-4">
            <label class="label">
                <span class="label-text font-medium">Sélectionner les personnes</span>
                <span class="label-text-alt badge badge-primary badge-sm"
                      x-text="selectedCount + ' sélectionné(s)'"></span>
            </label>

            <input type="text" placeholder="Rechercher par nom..."
                   class="input input-bordered input-sm mb-2"
                   x-model="search"/>

            <div class="border rounded-lg overflow-y-auto max-h-56 p-2 space-y-1">

                {{-- Superviseurs --}}
                @foreach($branchUsers->where('role', 'superviseur') as $u)
                <label class="label cursor-pointer justify-start gap-3
                              hover:bg-base-200 rounded px-2 py-1"
                       x-show="matchSearch('{{ strtolower($u->firstname . ' ' . $u->lastname) }}')">
                    <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                           class="checkbox checkbox-primary checkbox-sm"
                           x-on:change="updateCount()"/>
                    <span class="label-text font-medium">
                        {{ $u->firstname }} {{ $u->lastname }}
                    </span>
                    <span class="badge badge-warning badge-sm ml-auto">Superviseur</span>
                </label>
                @endforeach

                <div class="divider my-1 text-xs">Employés</div>

                {{-- Employés --}}
                @foreach($branchUsers->where('role', 'employe') as $u)
                <label class="label cursor-pointer justify-start gap-3
                              hover:bg-base-200 rounded px-2 py-1"
                       x-show="matchSearch('{{ strtolower($u->firstname . ' ' . $u->lastname) }}')">
                    <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                           class="checkbox checkbox-primary checkbox-sm"
                           x-on:change="updateCount()"/>
                    <span class="label-text font-medium">
                        {{ $u->firstname }} {{ $u->lastname }}
                    </span>
                    <span class="badge badge-info badge-sm ml-auto">Employé</span>
                </label>
                @endforeach

            </div>
        </div>

        {{-- Échéance (Type 1 uniquement) --}}
        @if($form->report_type === 'type1')
        <div class="form-control mb-4">
            <label class="label">
                <span class="label-text">Date d'échéance</span>
                <span class="label-text-alt">Optionnel</span>
            </label>
            <input type="datetime-local" name="due_at"
                   class="input input-bordered w-full"/>
        </div>
        @endif

        <div class="modal-action">
            <button type="button"
                    onclick="this.closest('dialog').close()"
                    class="btn btn-ghost">Annuler</button>
            <button type="submit" class="btn btn-primary">Assigner</button>
        </div>
    </form>
  </div>
</dialog>

<script>
function assignForm() {
    return {
        scopeType: 'role',
        search: '',
        selectedCount: 0,
        matchSearch(name) {
            return name.includes(this.search.toLowerCase());
        },
        updateCount() {
            this.selectedCount = document.querySelectorAll(
                'input[name="user_ids[]"]:checked'
            ).length;
        }
    }
}
</script>
```

---

## CHECKLIST DE VALIDATION COMPLÈTE

```
SUPPRESSION GROUPES
□ Migration : group_id supprimé de users
□ Migration : table groups droppée
□ app/Models/Group.php supprimé
□ app/Http/Controllers/GroupController.php supprimé
□ resources/views/groups/ supprimé
□ Routes /groups/* supprimées dans web.php
□ Toutes références group_id retirées (controllers, vues, factories, seeders)
□ Relation group() retirée du modèle User

BASE DE DONNÉES
□ Migration : scope_user_id supprimé de form_assignments
□ Migration : scope_role étendu à superviseur | employe | both
□ Migration : table form_assignment_users créée (pivot)
□ Modèle FormAssignment : selectedUsers() + resolveRecipients() avec 'both'

CRÉATION UTILISATEUR
□ UserController@create : creatableRoles filtré selon rôle du créateur
□ Formulaire : sélecteur type radio (options dynamiques selon créateur)
□ Type manager  → 4 privilèges (Super Admin uniquement)
□ Type superviseur → dropdown Manager + 1 privilège can_create_employes
□ Type employé  → dropdown Superviseur (pré-rempli si créateur = Superviseur)
□ Génération username/password automatique + gestion collision
□ Privilèges filtrés : jamais accorder plus que ce qu'on possède
□ Rôle Spatie assigné + UserPrivilege créé après création
□ Message succès avec login + mot de passe initial affiché

ASSIGNATION QUESTIONNAIRES
□ Mode global : 3 options (Employés / Superviseurs / Les deux)
□ Mode individuel : liste mixte Superviseurs + Employés avec badges
□ Filtre recherche Alpine.js opérationnel
□ Compteur sélectionnés mis à jour en temps réel
□ resolveRecipients() gère scope_role = 'both'
□ validateUsersInBranch() → 403 si hors branche
□ NotifyFormAssigned dispatché sur queue 'default'
□ Date d'échéance visible uniquement si report_type = 'type1'

TESTS PEST
□ Création Manager avec privilèges par Super Admin
□ Création Superviseur avec manager_id par Manager privilégié
□ Création Employé avec supervisor_id par Superviseur privilégié
□ Tentative création Manager par Manager → 403
□ Assignation globale scope_role = 'both' → tous reçoivent
□ Assignation individuelle multiple → seules les personnes cochées reçoivent
□ Sélection hors branche → 403 Forbidden
```

---

*Prompt correctif combiné v1.0*
*Suppression groupes + Création utilisateur dynamique + Assignation questionnaires*
*Stack : Laravel 11 + Blade + HTMX + Alpine.js + DaisyUI v5*