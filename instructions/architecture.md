- This Project need to follow event architecture pattern 

To know how to do that you need to do the following decide first what is the main reason of the request as example lets say that the user want to register so the thing that the user need is to have 2 things the first one is the user data the secound is the workspace so first you will create the user second you will create the workspace but also the user need otp and need welcome on board so you have 2 things so you will use mail service (Create it if you didnt find it ) and send otp and board email but through even queue not direct 

- Every feature MUST be added inside a feature folder: its own module under `Modules/` (grouping related features per the module rule). No feature code in `app/` outside cross-cutting concerns.

- This Project need to be clean pattern 

(Policy if neeeded) Controller (Validate in request) -> Service -> Repository ->  Model 

- Workspace scoping (tenant isolation): every user operates inside his own workspace and can only see records belonging to that workspace. Any model that owns a `workspace_id` MUST use the `App\Models\Concerns\BelongsToWorkspace` trait, which:
  - applies the `App\Models\Scopes\WorkspaceScope` global scope so every read is automatically constrained to the authenticated user's workspace(s);
  - auto-assigns `workspace_id` from the current user's workspace on create (client never sends it);
  - re-enforces `workspace_id` to the current user's workspace on update, so a record can never leave the tenant.
  Never accept `workspace_id` from the request and never filter it manually inside a service or repository — the trait is the single source of tenant isolation. Route-model binding returns 404 for records outside the user's workspace, so no extra ownership check is needed in the controller.

- Data source rule: all data must come from the Service, and every model instance must be fetched from MySQL through the Repository. Controllers must NOT use route-model binding — type-hint the route id as a scalar (e.g. `int $project`) and pass it down: Controller -> Service -> Repository (`findOrFail`) -> Model. The global `WorkspaceScope` runs on that repository query, so a foreign id throws `ModelNotFoundException` (404) automatically and tenant isolation is preserved.

- Do not write prose comments on functions. PHPDoc blocks are only for type information (array shapes via `@param`/`@return`).

- Do not create factories unless explicitly requested.

- Testing: every public Service method MUST have a corresponding test covering its behavior (happy path + relevant edge cases such as not-found or validation failures). Use Pest feature tests. A Service function is not done until its test exists and passes.
