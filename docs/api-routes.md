# Routes and Endpoints Documentation

## Public Routes

### Landing Page
```
GET /
```
Displays the public landing page with login option.

### Patreon OAuth

```
GET /auth/patreon
```
Redirects user to Patreon for authentication.

```
GET /auth/patreon/callback
```
Handles the OAuth callback from Patreon. Creates or updates user account.

### Webhook

```
POST /webhooks/patreon
```
Receives webhook events from Patreon when membership changes occur.

**Headers:**
- `X-Patreon-Event`: Event type (e.g., `members:pledge:create`)
- `X-Patreon-Signature`: HMAC-MD5 signature for verification

**Events handled:**
- `members:pledge:create` - New pledge
- `members:pledge:update` - Pledge amount changed
- `members:pledge:delete` - Pledge cancelled

## Authenticated Routes

All routes below require authentication via the `auth` middleware.

### Dashboard

```
GET /dashboard
```
User dashboard showing patron status and recent requests.

**Response props:**
```typescript
{
  isActivePatron: boolean;
  patronStatus: string | null;
  tierCents: number;
  monthlyLimit: number;
  remainingRequests: number;
  recentRequests: VideoRequest[];
}
```

### Subscribe

```
GET /subscribe
```
Shown to non-patrons. Displays subscription options.

**Response props:**
```typescript
{
  tiers: Array<{
    amount: string;
    requests_per_month: number;
  }>;
}
```

### Logout

```
POST /logout
```
Logs out the user and invalidates session.

## Patron-Only Routes

These routes require the `patron` middleware (active Patreon subscription).

### Video Queue

```
GET /requests
```
View the pending video queue (chronological order).

**Response props:**
```typescript
{
  requests: Array<{
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    youtube_video_id: string;
    requested_at: string;
    user: {
      name: string;
      avatar: string | null;
    };
  }>;
}
```

### New Request Form

```
GET /requests/new
```
Form to submit a new video request.

**Response props:**
```typescript
{
  remainingRequests: number;
  monthlyLimit: number;
}
```

### Submit Request

```
POST /requests
```
Submit a new video request.

**Request body:**
```json
{
  "youtube_url": "https://www.youtube.com/watch?v=VIDEO_ID"
}
```

**Validation:**
- `youtube_url` - Required, valid YouTube URL
- User must have remaining requests for the month
- Video must not already be in the pending queue

**Success:** Redirects to `/my-requests` with success message
**Error:** Returns validation errors

### My Requests

```
GET /my-requests
```
View the user's own requests and their status.

**Response props:**
```typescript
{
  requests: Array<{
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    youtube_video_id: string;
    status: 'pending' | 'done';
    requested_at: string;
    completed_at: string | null;
  }>;
  remainingRequests: number;
  monthlyLimit: number;
}
```

## Admin Routes

These routes require the `admin` middleware.

### Admin Requests List

```
GET /admin/requests
GET /admin/requests?status=pending
GET /admin/requests?status=done
```
View all video requests with filtering options.

**Query parameters:**
- `status` - Filter by status: `all`, `pending`, `done`

**Response props:**
```typescript
{
  requests: Array<{
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    youtube_video_id: string;
    status: 'pending' | 'done';
    requested_at: string;
    completed_at: string | null;
    user: {
      id: number;
      name: string;
      email: string;
      avatar: string | null;
      tier_cents: number;
    };
  }>;
  stats: {
    total: number;
    pending: number;
    done: number;
  };
  currentFilter: string;
}
```

### Mark Request Done

```
PATCH /admin/requests/{id}/done
```
Mark a video request as completed.

**Success:** Redirects back with success message

### Mark Request Pending

```
PATCH /admin/requests/{id}/pending
```
Revert a completed request back to pending status.

**Success:** Redirects back with success message

## Middleware Reference

| Middleware | Description |
|------------|-------------|
| `auth` | User must be logged in |
| `patron` | User must be an active Patreon subscriber |
| `admin` | User must have admin privileges |

## Error Responses

### 401 Unauthorized
User is not authenticated.

### 403 Forbidden
- Non-patron accessing patron routes
- Non-admin accessing admin routes

### 422 Validation Error
Invalid form submission. Returns error messages.

### 500 Server Error
Internal server error. Check logs for details.
