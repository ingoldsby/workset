# Workset API Documentation

**Version:** 1.0
**Base URL:** `https://workset.kneebone.com.au/api`
**Authentication:** Laravel Sanctum (Token-based)

---

## Table of Contents

1. [Authentication](#authentication)
2. [PWA / Push Notifications](#pwa--push-notifications)
3. [Offline Sync](#offline-sync)
4. [Response Format](#response-format)
5. [Error Handling](#error-handling)
6. [Rate Limiting](#rate-limiting)
7. [Versioning](#versioning)

---

## Authentication

Workset uses Laravel Sanctum for API authentication with token-based auth.

### Obtaining an Auth Token

**Endpoint:** `POST /sanctum/token`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your-password",
  "device_name": "iPhone 14"
}
```

**Response:**
```json
{
  "token": "1|abc123def456...",
  "user": {
    "id": "01H...",
    "name": "John Smith",
    "email": "user@example.com",
    "role": "member",
    "timezone": "Australia/Brisbane"
  }
}
```

**Status Codes:**
- `200 OK` - Authentication successful
- `401 Unauthorized` - Invalid credentials
- `422 Unprocessable Entity` - Validation errors

### Using the Token

Include the token in the `Authorization` header for all subsequent requests:

```
Authorization: Bearer 1|abc123def456...
```

### Revoking Tokens

**Endpoint:** `POST /sanctum/token/revoke`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Token revoked successfully"
}
```

**Status Codes:**
- `200 OK` - Token revoked
- `401 Unauthorized` - Invalid or missing token

---

## PWA / Push Notifications

### Get VAPID Public Key

Retrieve the public key required for Web Push subscriptions.

**Endpoint:** `GET /api/push/vapid-public-key`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "publicKey": "BG...publickeyhere...=="
}
```

**Status Codes:**
- `200 OK` - Success
- `401 Unauthorized` - Not authenticated

**Example:**
```javascript
const response = await fetch('/api/push/vapid-public-key', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const { publicKey } = await response.json();
```

---

### Subscribe to Push Notifications

Register a push notification subscription for the authenticated user.

**Endpoint:** `POST /api/push/subscribe`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "endpoint": "https://fcm.googleapis.com/fcm/send/...",
  "keys": {
    "p256dh": "BG...publickey...==",
    "auth": "auth-secret-here"
  }
}
```

**Response:**
```json
{
  "success": true
}
```

**Status Codes:**
- `200 OK` - Subscription created/updated
- `401 Unauthorized` - Not authenticated
- `422 Unprocessable Entity` - Validation errors

**Validation Rules:**
- `endpoint` - Required, must be a valid URL
- `keys` - Required, must be an object
- `keys.p256dh` - Required, string
- `keys.auth` - Required, string

**Example:**
```javascript
// Register service worker
const registration = await navigator.serviceWorker.register('/sw.js');

// Subscribe to push
const subscription = await registration.pushManager.subscribe({
  userVisibleOnly: true,
  applicationServerKey: publicKey
});

// Send to server
await fetch('/api/push/subscribe', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify(subscription.toJSON())
});
```

---

### Unsubscribe from Push Notifications

Remove a push notification subscription.

**Endpoint:** `POST /api/push/unsubscribe`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "endpoint": "https://fcm.googleapis.com/fcm/send/..."
}
```

**Response:**
```json
{
  "success": true
}
```

**Status Codes:**
- `200 OK` - Subscription removed
- `401 Unauthorized` - Not authenticated
- `422 Unprocessable Entity` - Validation errors

**Validation Rules:**
- `endpoint` - Required, must be a valid URL

**Example:**
```javascript
const subscription = await registration.pushManager.getSubscription();

await fetch('/api/push/unsubscribe', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    endpoint: subscription.endpoint
  })
});

await subscription.unsubscribe();
```

---

## Offline Sync

### Create Session Set

Create a session set entry for offline sync.

**Endpoint:** `POST /api/session-sets`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "training_session_id": "01H...",
  "exercise_id": "01H...",
  "member_exercise_id": null,
  "set_number": 1,
  "weight_performed": 100.0,
  "reps_performed": 10,
  "rpe_performed": 8.5,
  "notes": "Felt strong, good form"
}
```

**Response:**
```json
{
  "id": "01H...",
  "training_session_id": "01H...",
  "exercise_id": "01H...",
  "member_exercise_id": null,
  "set_number": 1,
  "weight_performed": 100.0,
  "reps_performed": 10,
  "rpe_performed": 8.5,
  "notes": "Felt strong, good form",
  "created_at": "2025-11-16T10:30:00.000000Z",
  "updated_at": "2025-11-16T10:30:00.000000Z"
}
```

**Status Codes:**
- `201 Created` - Set created successfully
- `401 Unauthorized` - Not authenticated
- `404 Not Found` - Training session not found
- `422 Unprocessable Entity` - Validation errors

**Validation Rules:**
- `training_session_id` - Required, must exist in training_sessions table
- `exercise_id` - Optional (if member_exercise_id provided), must exist
- `member_exercise_id` - Optional (if exercise_id provided), must exist
- `set_number` - Required, integer, minimum 1
- `weight_performed` - Optional, numeric, minimum 0
- `reps_performed` - Optional, integer, minimum 0
- `rpe_performed` - Optional, numeric, 1-10 scale
- `notes` - Optional, string, maximum 500 characters

**Example:**
```javascript
const set = {
  training_session_id: sessionId,
  exercise_id: exerciseId,
  set_number: 1,
  weight_performed: 100,
  reps_performed: 10,
  rpe_performed: 8.5
};

const response = await fetch('/api/session-sets', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify(set)
});

const createdSet = await response.json();
```

---

### Complete Training Session

Mark a training session as completed.

**Endpoint:** `POST /api/sessions/{sessionId}/complete`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Path Parameters:**
- `sessionId` - The ULID of the training session

**Request Body:**
```json
{
  "completed_at": "2025-11-16T11:45:00Z"
}
```

**Response:**
```json
{
  "id": "01H...",
  "user_id": "01H...",
  "program_day_id": "01H...",
  "started_at": "2025-11-16T10:00:00.000000Z",
  "completed_at": "2025-11-16T11:45:00.000000Z",
  "notes": "Great session, felt strong",
  "created_at": "2025-11-16T10:00:00.000000Z",
  "updated_at": "2025-11-16T11:45:30.000000Z"
}
```

**Status Codes:**
- `200 OK` - Session completed
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorised (session belongs to another user)
- `404 Not Found` - Session not found
- `422 Unprocessable Entity` - Validation errors

**Validation Rules:**
- `completed_at` - Required, must be a valid date/time

**Example:**
```javascript
const response = await fetch(`/api/sessions/${sessionId}/complete`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    completed_at: new Date().toISOString()
  })
});

const completedSession = await response.json();
```

---

## Response Format

### Success Response

All successful responses follow this format:

**Single Resource:**
```json
{
  "id": "01H...",
  "attribute1": "value1",
  "attribute2": "value2",
  "created_at": "2025-11-16T10:00:00.000000Z",
  "updated_at": "2025-11-16T10:00:00.000000Z"
}
```

**Collection:**
```json
{
  "data": [
    {
      "id": "01H...",
      "attribute1": "value1"
    },
    {
      "id": "01H...",
      "attribute1": "value2"
    }
  ],
  "links": {
    "first": "https://workset.kneebone.com.au/api/resource?page=1",
    "last": "https://workset.kneebone.com.au/api/resource?page=10",
    "prev": null,
    "next": "https://workset.kneebone.com.au/api/resource?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

### Date/Time Format

All timestamps use ISO 8601 format with UTC timezone:

```
2025-11-16T10:30:00.000000Z
```

When sending dates to the API, you can use:
- ISO 8601: `2025-11-16T10:30:00Z`
- Date only: `2025-11-16`
- Laravel formats: `2025-11-16 10:30:00`

### JSON Keys

All JSON keys use **camelCase** format:

```json
{
  "trainingSessionId": "01H...",
  "exerciseId": "01H...",
  "setNumber": 1,
  "weightPerformed": 100.0,
  "repsPerformed": 10,
  "rpePerformed": 8.5
}
```

---

## Error Handling

### Error Response Format

```json
{
  "message": "Human-readable error message",
  "errors": {
    "field_name": [
      "Validation error message 1",
      "Validation error message 2"
    ]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request succeeded |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request succeeded, no content returned |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required or failed |
| 403 | Forbidden | Authenticated but not authorised |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 503 | Service Unavailable | Maintenance mode |

### Common Error Examples

**Validation Error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

**Unauthorised (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Forbidden (403):**
```json
{
  "message": "This action is unauthorised."
}
```

**Not Found (404):**
```json
{
  "message": "Resource not found."
}
```

**Rate Limit (429):**
```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

### Error Handling Best Practices

```javascript
async function apiRequest(endpoint, options = {}) {
  try {
    const response = await fetch(endpoint, {
      ...options,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
      }
    });

    if (!response.ok) {
      const error = await response.json();

      switch (response.status) {
        case 401:
          // Redirect to login
          window.location = '/login';
          break;
        case 422:
          // Handle validation errors
          handleValidationErrors(error.errors);
          break;
        case 429:
          // Rate limited - retry after delay
          await sleep(error.retry_after * 1000);
          return apiRequest(endpoint, options);
        default:
          // Show generic error
          showError(error.message);
      }

      throw error;
    }

    return await response.json();
  } catch (error) {
    console.error('API request failed:', error);
    throw error;
  }
}
```

---

## Rate Limiting

### Limits

API requests are rate-limited to prevent abuse:

- **Authenticated requests**: 60 requests per minute
- **Unauthenticated requests**: 10 requests per minute
- **Push subscription endpoints**: 10 requests per minute

### Rate Limit Headers

Response headers indicate your current rate limit status:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1700123456
```

- `X-RateLimit-Limit` - Maximum requests allowed
- `X-RateLimit-Remaining` - Requests remaining in current window
- `X-RateLimit-Reset` - Unix timestamp when limit resets

### Handling Rate Limits

When you exceed the rate limit (HTTP 429):

1. Check the `Retry-After` header (seconds to wait)
2. Implement exponential backoff
3. Queue requests client-side
4. Show user-friendly message

```javascript
if (response.status === 429) {
  const retryAfter = response.headers.get('Retry-After');
  await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
  // Retry request
}
```

---

## Versioning

### Current Version

The current API version is **v1**. All endpoints are prefixed with `/api`.

### Version Strategy

- **Backwards-compatible changes**: No version bump required
- **Breaking changes**: New version endpoint (e.g., `/api/v2`)
- **Deprecation**: 6 months notice before removal

### Version Header

Optionally specify API version in header:

```
Accept: application/vnd.workset.v1+json
```

### Breaking vs Non-Breaking Changes

**Non-breaking (no version change needed):**
- Adding new endpoints
- Adding new optional fields
- Adding new response fields
- Relaxing validation rules

**Breaking (requires version bump):**
- Removing endpoints
- Removing request/response fields
- Making optional fields required
- Changing field types
- Changing authentication method

---

## Data Models

### User

```json
{
  "id": "01H...",
  "name": "John Smith",
  "email": "john@example.com",
  "role": "member",
  "timezone": "Australia/Brisbane",
  "preferredWeightUnit": "kg",
  "preferredDistanceUnit": "km",
  "createdAt": "2025-01-01T00:00:00.000000Z",
  "updatedAt": "2025-11-16T10:00:00.000000Z"
}
```

### Training Session

```json
{
  "id": "01H...",
  "userId": "01H...",
  "programDayId": "01H...",
  "startedAt": "2025-11-16T10:00:00.000000Z",
  "completedAt": "2025-11-16T11:30:00.000000Z",
  "notes": "Great session",
  "createdAt": "2025-11-16T10:00:00.000000Z",
  "updatedAt": "2025-11-16T11:30:30.000000Z"
}
```

### Session Set

```json
{
  "id": "01H...",
  "sessionExerciseId": "01H...",
  "setNumber": 1,
  "setType": "working",
  "prescribedReps": 10,
  "prescribedWeight": 100.0,
  "prescribedRpe": 8.0,
  "performedReps": 10,
  "performedWeight": 100.0,
  "performedRpe": 8.5,
  "completed": true,
  "completedAt": "2025-11-16T10:15:00.000000Z",
  "notes": "Felt strong",
  "createdAt": "2025-11-16T10:00:00.000000Z",
  "updatedAt": "2025-11-16T10:15:30.000000Z"
}
```

---

## Security

### HTTPS Only

All API requests must use HTTPS. HTTP requests will be redirected.

### CORS

Cross-Origin Resource Sharing (CORS) is enabled for:

- `https://workset.kneebone.com.au`
- `https://staging.workset.kneebone.com.au`
- Mobile apps (via Capacitor/Cordova)

### Authentication

- Tokens transmitted via `Authorization: Bearer {token}` header
- Tokens are revocable
- Tokens expire after 90 days of inactivity
- Use separate tokens per device

### Input Validation

All inputs are validated and sanitised:

- SQL injection protection
- XSS protection
- CSRF protection (for web forms)
- Request size limits (10MB max)

### Sensitive Data

The following fields are never included in API responses:

- Passwords
- Password reset tokens
- Remember tokens
- API tokens (only shown on creation)

---

## Support

### Reporting Issues

- **Bugs**: support@workset.kneebone.com.au
- **Security**: security@workset.kneebone.com.au
- **Feature requests**: feedback@workset.kneebone.com.au

### API Status

Check API status at: https://status.workset.kneebone.com.au

### Changelog

See [CHANGELOG.md](../CHANGELOG.md) for API changes.

---

**API Version:** 1.0
**Last Updated:** November 2025
**© 2025 Workset. All rights reserved.**
