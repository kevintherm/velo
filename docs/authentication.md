# Authentication

Velo simplifies user authentication by treating users as records in an **Auth Collection**.
Velo uses stateful authentication, which means it must verify your token on every request. You can manage authentication sessions in the Admin Panel under the System section. Authentication is handled through `AuthMiddleware`.
By using stateful authentication approach, you can also instantly terminate a user's session or session for a separate device.

## Auth Collection

Any collection with the type `Auth` acts as a user provider. You can have multiple auth collections (e.g., `users`, `admins`, `customers`) completely separated from each other.

By default, an Auth collection has the following fields:
- `email`
- `password`
- `verified` (bool)

## Authentication Methods

Velo supports multiple ways to authenticate:

### 1. Standard
The standard flow. Using identifier which you can configure on the admin panel, and password.
- Endpoint: `POST /api/collections/{collection}/auth/authenticate-with-password`
- Payload: `{"identifier": "email@example.com", "password": "..."}`

### 2. OTP (One-Time Password)
Passwordless login via email codes.
- Enable `otp` in the Collection options.
- Endpoint (Request): `POST /api/collections/{collection}/auth/request-auth-otp`
- Endpoint (Login): `POST /api/collections/{collection}/auth/authenticate-with-otp`

## Token Management

On successful authentication, the API returns a Bearer Token. This token should be included in the `Authorization` header for subsequent requests.

- `POST /api/collections/{collection}/auth/logout`: Invalidate the current token.
- `POST /api/collections/{collection}/auth/logout-all`: Invalidate *all* tokens for the user (e.g., "Sign out everywhere").

## Account Management

Also built-in are standard account management flows:
- **Forgot Password**: Request and confirm password reset via email.
- **Update Email**: Request and confirm email change (with OTP verification).
- **Verification**: Email verification flows.
