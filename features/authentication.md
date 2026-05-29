Create a complete authentication and workspace onboarding flow for a Laravel backend application using clean architecture and scalable design principles.

Requirements:

1. Authentication Features

* User Register
* User Login
* Forgot Password
* Reset Password
* Login with Google OAuth
* Register with Google OAuth

2. Forgot Password Flow
   Implement the forgot password flow using OTP verification via email instead of traditional reset links.

Flow:

* User submits email address.
* System generates an OTP code and sends it to the user's email.
* User enters the OTP code.
* System verifies the OTP.
* After successful verification, the user is allowed to reset their password.

3. OTP System Requirements
   Create a generic OTP system that can be reused for multiple purposes in the future.

Requirements:

* Create a separate OTP entity/model/table.

* OTP migration fields should include:

  * id
  * email
  * otp
  * usage
  * expires_at
  * verified_at
  * created_at
  * updated_at

* The `usage` field should support multiple OTP purposes such as:

  * password_reset
  * email_verification
  * login_verification
  * etc.

* OTP logic must be implemented in isolated reusable classes/services.

* OTP verification should check:

  * expiration
  * usage type
  * matching email
  * matching OTP code

4. Workspace System
   When a user registers successfully:

* Automatically create a workspace.
* A user can own multiple workspaces.
* Each workspace must have only one owner.

Relationship Requirements:

* User → hasMany Workspaces
* Workspace → belongsTo Owner (User)

Suggested workspace fields:

* id
* owner_id
* name
* slug
* created_at
* updated_at

5. Google Authentication
   Implement Google OAuth authentication:

* Login with Google
* Register with Google

Requirements:

* If the user already exists, log them in.
* If the user does not exist, create the user and automatically create a default workspace for them.

6. Architecture Requirements

* Use clean and scalable architecture.
* Separate business logic into services/actions/use-cases.
* Use Form Requests for validation.
* Use DTOs where necessary.
* Use repository pattern if needed.
* Avoid fat controllers.
* Make the OTP system reusable and extendable.

7. Security Requirements

* Hash passwords properly.
* OTP expiration must be configurable.
* Prevent OTP brute force attempts.
* Invalidate OTP after successful usage.
* Rate limit forgot password requests.
* Use secure authentication tokens/sessions.

8. Deliverables
   Generate:

* Database migrations
* Models and relationships
* Controllers
* Services/actions/use-cases
* Form requests
* Routes
* OTP email implementation
* Google OAuth integration
* Example API responses
* Folder structure suggestion
* Full authentication flow explanation

The implementation should be production-ready, maintainable, and optimized for future scaling.
