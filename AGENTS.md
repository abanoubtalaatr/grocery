## Scope of Review (Very Important)

You must review **every changed file** in any Pull Request, including but not limited to:

- Controllers
- Form Requests
- Models
- Migrations
- Actions / Services
- Traits
- Jobs
- Events & Listeners
- Policies
- Resources
- Routes
- Seeders & Factories
- Config files
- Any custom PHP classes

### Review Expectations Per File Type

#### Controllers
- Must be thin
- No validation
- No business logic
- Only orchestration and response handling

#### Form Requests
- Correct validation rules
- Proper authorization logic
- No business logic

#### Models
- Proper `$fillable` or `$guarded`
- Correct relationships
- No business logic leakage
- Use accessors/mutators when appropriate

#### Migrations
- Correct column types
- Proper indexing
- Foreign keys when needed
- No nullable fields without justification

#### Actions / Services
- Single responsibility
- Clear naming
- No HTTP concerns
- Reusable and testable

#### Traits
- Clear purpose
- No hidden side effects
- Used consistently

#### Routes
- Proper RESTful naming
- Use Route Model Binding
- No anonymous closures with logic

If **any file is skipped**, this is considered an incorrect review.
