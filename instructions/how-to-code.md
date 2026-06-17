- this md file show you the guide lines you should to follow to code 

# Workspace scoping
- EVERY model MUST use the `App\Models\Concerns\BelongsToWorkspace` trait. There is no child-table exception — parent, child, pivot-like tables all carry their own `workspace_id` and scope directly.
- The trait only works if the model's table has a `workspace_id` column. Every `create` migration MUST add it (and an index):

```php
$table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
$table->index('workspace_id');
```

- The trait applies the `WorkspaceScope` global scope (`WHERE table.workspace_id IN (...)`), auto-assigns `workspace_id` from the authenticated user's workspace on create, and re-enforces it on update. The client NEVER sends `workspace_id` and you NEVER filter it manually in a service or repository.

```php
use App\Models\Concerns\BelongsToWorkspace;

class Stage extends Model
{
    use BelongsToWorkspace;

    protected $fillable = ['workspace_id', /* ... */];
}
```

# Crud operation 
- any feature will have some crud operation maybe some maybe all so you need to follow the comming list 

## Validation rule
- every controller method that receives request data MUST validate it through a dedicated `FormRequest`. Type-hint the FormRequest in the method signature and pass `$request->validated()` down to the service.
- NEVER validate inline inside a controller method (no manual `$request->validate([...])`, no `throw ValidationException`). All validation rules live in the FormRequest only.
- this applies to every method that takes the request — including `index` (use a `FilterXRequest`). Do not throw errors or run validation logic inside the controller method itself.

## Listing: 
- in case you want to list a list of something you have to create a scopeFilter inside the model and then it must be passed from repository to service to controller in that order as example 

in model 
```php
public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (isset($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }

        return $query;
    }
```

in repository 

```php
public function filter(array $filters): Collection
{
    return $this->project
        ->filter($filters)
        ->latest()
        ->get();
}
```

in service 

```php
public function filter(array $filters): Collection
{
    return $this->projectRepository->filter($filters);
}
```

in controller — always validate with a FormRequest first, never pass raw request

```php
public function index(FilterProjectRequest $request): JsonResponse
{
    $projects = $this->projectService->filter($request->validated());

    return response()->json([
        'data' => $projects,
    ]);
}
```

## Read (show single):
- fetch one record by id. Repository uses `findOrFail` so a missing id returns 404 automatically.

in repository

```php
public function find(int $id): Project
{
    return $this->project->findOrFail($id);
}
```

in service

```php
public function find(int $id): Project
{
    return $this->projectRepository->find($id);
}
```

in controller

```php
public function show(int $project): JsonResponse
{
    return response()->json([
        'data' => new ProjectResource($this->projectService->find($project)),
    ]);
}
```

## Update:
- service resolves the model first via `find`, then hands it to the repository to update. Repository returns the refreshed model.

in repository

```php
public function update(Project $project, array $data): Project
{
    $project->update($data);

    return $project->refresh();
}
```

in service

```php
public function update(int $id, array $data): Project
{
    $project = $this->projectRepository->find($id);

    return $this->projectRepository->update($project, $data);
}
```

in controller — validate with an Update FormRequest

```php
public function update(UpdateProjectRequest $request, int $project): JsonResponse
{
    $updated = $this->projectService->update($project, $request->validated());

    return response()->json([
        'message' => 'Project updated successfully.',
        'data' => new ProjectResource($updated),
    ]);
}
```

## Delete:
- service resolves the model first, then deletes. Returns nothing.

in repository

```php
public function delete(Project $project): void
{
    $project->delete();
}
```

in service

```php
public function delete(int $id): void
{
    $project = $this->projectRepository->find($id);

    $this->projectRepository->delete($project);
}
```

in controller

```php
public function destroy(int $project): JsonResponse
{
    $this->projectService->delete($project);

    return response()->json([
        'message' => 'Project deleted successfully.',
    ]);
}
```

## Routes
- Never use `Route::match(['put', 'patch'], ...)`. For an update endpoint declare two explicit routes pointing at the same controller method — name only the `put` one.

```php
Route::put('projects/{project}/form', [FormController::class, 'update'])->name('projects.form.update');
Route::patch('projects/{project}/form', [FormController::class, 'update']);
```
