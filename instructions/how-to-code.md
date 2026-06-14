# How to Code in Artmes

Practical, step-by-step guide for adding a feature. Follow it top to bottom. Every example here is copied from real code in this repo — `ProjectManagment` (clean CRUD slice) and `UserManagement` (event-driven slice).

> Read [architecture.md](architecture.md) first. This file shows **how**; that file states the **rules**.

---

## 0. Before you write anything

Decide the **main reason** of the request, then list every side effect it implies.

> Example (from project flow): user wants to **register**. Main reason = create the user. But registration also needs a **workspace**, an **OTP email**, and a **welcome email**. So the real job is: create user → create workspace → send OTP → send welcome. The first two are the core write; the last two are side effects → they go through **events + queue**, never inline.

Rules of thumb:

- Core write (the thing the request is named after) → Service orchestrates it, inside a DB transaction if more than one row is touched.
- Side effects (email, notification, activity log, anything external/slow) → dispatch an **event**, handle in a **queued listener**. Never send mail or call slow services directly from the Service.
- Group the feature into an **existing module** when it is related. Don't spin up a module per micro-feature. When unsure, ask.

---

## 1. Pick / create the module

Every feature lives under `Modules/`. Reuse a module if the feature belongs to its group:

- `UserManagement` → auth, profile, users, workspaces
- `ProjectManagment` → projects, boards, related task/list features
- `ActivityLog` → cross-cutting activity tracking

Create a new module only with approval:

```bash
php artisan module:make ModuleName --no-interaction
```

---

## 2. Migration (workspace_id required)

Top-level entity tables MUST carry `workspace_id`. Child tables reached only through a parent do not.

```php
// Modules/ProjectManagment/database/migrations/..._create_projects_table.php
public function up(): void
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
        $table->string('title');
        $table->timestamps();

        $table->index('workspace_id');
        $table->index('title', 'idx_title_projects');
    });
}
```

---

## 3. Model (BelongsToWorkspace + scopeFilter)

Two non-negotiables on a top-level model:

1. `use BelongsToWorkspace;` — applies the global `WorkspaceScope`, auto-fills `workspace_id` on create, re-enforces it on update. **Never** accept `workspace_id` from the request, never filter it by hand.
2. A `scopeFilter` — the single entry point for every filterable column.

```php
// Modules/ProjectManagment/app/Models/Project.php
class Project extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'title',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }

        return $query;
    }
}
```

---

## 4. Repository (only place that touches the DB)

Inject the **model instance** via constructor. Build queries with `$this->model->newQuery()` — never static `Model::`. No business rules here.

```php
// Modules/ProjectManagment/app/Repositories/ProjectRepository.php
class ProjectRepository
{
    public function __construct(private Project $project) {}

    public function filter(array $filters): Collection
    {
        return $this->project->newQuery()
            ->filter($filters)
            ->latest()
            ->get();
    }

    public function find(int $id): Project
    {
        return $this->project->newQuery()->findOrFail($id);
    }

    public function create(array $data): Project
    {
        return $this->project->newQuery()->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
```

> **Why `findOrFail` here matters:** `WorkspaceScope` runs on this query, so an id from another tenant throws `ModelNotFoundException` → 404 automatically. That is why controllers pass a scalar id, not a route-bound model — tenant isolation lives in the repository query.

---

## 5. Service (all business logic, orchestration, events)

Inject the **Repository**. No DB queries, no `Model::query()`. Fetch every model through the repository.

Simple CRUD slice:

```php
// Modules/ProjectManagment/app/Services/ProjectService.php
class ProjectService
{
    public function __construct(private ProjectRepository $projectRepository) {}

    public function filter(array $filters): Collection
    {
        return $this->projectRepository->filter($filters);
    }

    public function find(int $id): Project
    {
        return $this->projectRepository->find($id);
    }

    public function create(array $data): Project
    {
        return $this->projectRepository->create($data);
    }

    public function update(int $id, array $data): Project
    {
        $project = $this->projectRepository->find($id);

        return $this->projectRepository->update($project, $data);
    }

    public function delete(int $id): void
    {
        $project = $this->projectRepository->find($id);

        $this->projectRepository->delete($project);
    }
}
```

Event-driven slice (core write in a transaction, side effects dispatched):

```php
// Modules/UserManagement/app/Services/AuthService.php
public function register(array $data): array
{
    $user = $this->saveNewUser($data);          // core write, wrapped in DB::transaction

    UserRegistered::dispatch($user);            // welcome email → queued listener
    OtpRequested::dispatch($user->email, OtpUsage::EmailVerification); // otp → queued listener

    return [
        'user' => $user->refresh()->load('workspaces'),
        'token' => $this->issueToken($user),
    ];
}

public function saveNewUser(array $data): User
{
    return DB::transaction(function () use ($data) {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->workspaceService->createForOwner($user, $data['workspace_name'] ?? null);

        return $user;
    });
}
```

Note: multi-step writes go in `DB::transaction`; side effects are dispatched **after** the core write, never executed inline.

---

## 6. Event + queued listener (side effects)

Event = a dumb data carrier:

```php
// Modules/UserManagement/app/Events/UserRegistered.php
class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user) {}
}
```

Listener = `implements ShouldQueue`, `$afterCommit = true` so it only runs once the transaction commits:

```php
// Modules/UserManagement/app/Listeners/SendWelcomeEmailListener.php
class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function __construct(private MailService $mailService) {}

    public function handle(UserRegistered $event): void
    {
        $this->mailService->send($event->user->email, new WelcomeMail($event->user->name));
    }
}
```

Wire it in the module's `EventServiceProvider` (or rely on event discovery, which is on):

```php
// Modules/UserManagement/app/Providers/EventServiceProvider.php
protected $listen = [
    OtpRequested::class => [
        SendOtpListener::class,
    ],
];

protected static $shouldDiscoverEvents = true;
```

> Mail goes through a shared `MailService` (`App\Services\MailService`). If it doesn't exist for a new channel, create it — don't call `Mail::` from a listener directly.

---

## 7. FormRequests (validate before anything)

One request per action. Filter endpoints get their **own** request — never pass `$request->all()` / `$request->query()` into a scope.

```php
// StoreProjectRequest — write validation
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
    ];
}

// FilterProjectRequest — filter validation (all nullable)
public function rules(): array
{
    return [
        'title' => ['nullable', 'string', 'max:255'],
    ];
}
```

Notice `workspace_id` is **absent** from both — the trait owns it. Clients never send it.

---

## 8. API Resource (response shape)

```php
// Modules/ProjectManagment/app/Http/Resources/ProjectResource.php
/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at,
        ];
    }
}
```

---

## 9. Controller (HTTP only)

Inject the **Service**. Type-hint route ids as **scalars** (`int $project`), not models — no route-model binding. Validate via the FormRequest, call the service, return a resource.

```php
// Modules/ProjectManagment/app/Http/Controllers/ProjectController.php
class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(FilterProjectRequest $request): JsonResponse
    {
        $projects = $this->projectService->filter($request->validated());

        return response()->json([
            'data' => ProjectResource::collection($projects),
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create($request->validated());

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function show(int $project): JsonResponse
    {
        return response()->json([
            'data' => new ProjectResource($this->projectService->find($project)),
        ]);
    }

    public function update(UpdateProjectRequest $request, int $project): JsonResponse
    {
        $updated = $this->projectService->update($project, $request->validated());

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => new ProjectResource($updated),
        ]);
    }

    public function destroy(int $project): JsonResponse
    {
        $this->projectService->delete($project);

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }
}
```

When the filter needs the tenant id, inject it from the authenticated user — don't trust the client:

```php
// Modules/ActivityLog/app/Http/Controllers/ActivityLogController.php
$logs = $this->activityLogger->filter([
    ...$request->validated(),
    'workspace_id' => $request->user()->workspace_id,
]);
```

---

## 10. Routes

Versioned, auth-guarded, `apiResource`:

```php
// Modules/ProjectManagment/routes/api.php
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('projects', ProjectController::class);
});
```

---

## 11. Dependency injection — constructor only

Each layer injects the layer below it via promoted constructor properties. No `app()`, `resolve()`, or facades for these dependencies.

```
Controller(Service)  →  Service(Repository)  →  Repository(Model)
```

---

## 12. Finish line

- **No prose comments on functions.** PHPDoc is for type shapes only (`@param`/`@return` array shapes).
- **No factories** unless explicitly requested.
- Run the formatter before finalizing:

```bash
vendor/bin/pint --dirty --format agent
```

- Prefer **feature tests with factories** over tinker/verification scripts when proving behavior.

---

## Layer cheat-sheet

| Layer | Injects | Does | Never does |
|-------|---------|------|------------|
| Controller | Service | HTTP: validate via FormRequest, call Service, return Resource | Business logic, Eloquent, route-model binding |
| Service | Repository | Business logic, transactions, dispatch events | Direct DB queries, `Model::query()` |
| Repository | Model | All DB access via `$this->model->newQuery()` | Business rules, HTTP concerns |
| Model | — | Relationships, casts, `scopeFilter`, `BelongsToWorkspace` | Service logic |
| Listener | shared services (e.g. MailService) | Side effects, queued, `$afterCommit = true` | Run inline / block the request |
