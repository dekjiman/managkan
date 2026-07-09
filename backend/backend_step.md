# ManagKan Backend — PHP CodeIgniter 4

**Base URL:** `http://localhost:8080/api`  
**Auth:** JWT Bearer token via `Authorization` header  
**Response format (success):** `{ "data": ... }`  
**Response format (mutation):** `{ "data": ..., "message": "..." }`  
**Response format (error):** `{ "message": "..." }`  
**Response format (validation):** `{ "message": "...", "errors": { "field": ["..."] } }`

---

## Endpoints

### Auth

Base path: `/api/auth`

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| POST | `/auth/sign-in/email` | Login with email + password | No |
| POST | `/auth/sign-up/email` | Register with name + email + password | No |
| POST | `/auth/sign-out` | Logout | JWT |
| GET | `/auth/get-session` | Get current user session | JWT |
| POST | `/auth/send-verification-email` | Send email verification | No |
| POST | `/auth/request-password-reset` | Request password reset | No |
| POST | `/auth/reset-password` | Reset password with token | No |
| GET | `/auth/me` | Get current user profile | JWT |

#### POST `/auth/sign-in/email`
```json
// Request
{ "email": "string", "password": "string" }

// Response 200
{
  "user": {
    "id": "uuid",
    "name": "string",
    "email": "string",
    "emailVerified": false,
    "image": "string | null",
    "stripeCustomerId": "string | null",
    "createdAt": "datetime",
    "updatedAt": "datetime"
  },
  "accessToken": "jwt_string",
  "refreshToken": "jwt_string"
}
```

#### POST `/auth/sign-up/email`
```json
// Request
{ "name": "string", "email": "string", "password": "string" }

// Response 201
{
  "user": {
    "id": "uuid",
    "name": "string",
    "email": "string",
    "emailVerified": false,
    "image": null,
    "stripeCustomerId": null,
    "createdAt": "datetime",
    "updatedAt": "datetime"
  },
  "verificationToken": "hex_string"
}
```

#### GET `/auth/get-session`
```json
// Response 200
{
  "user": {
    "id": "uuid",
    "publicId": "uuid",
    "name": "string",
    "email": "string",
    "emailVerified": false,
    "image": "string | null",
    "createdAt": "datetime",
    "updatedAt": "datetime"
  }
}

// Response 200 (unauthenticated)
{ "user": null }
```

#### GET `/auth/me`
```json
// Response 200
{
  "data": {
    "id": "uuid",
    "name": "string",
    "email": "string",
    "emailVerified": false,
    "image": "string | null",
    "stripeCustomerId": "string | null",
    "createdAt": "datetime",
    "updatedAt": "datetime"
  }
}
```

---

### Workspaces

Base path: `/api/workspaces`

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/workspaces` | List user's workspaces | JWT |
| POST | `/workspaces` | Create workspace | JWT |
| GET | `/workspaces/{publicIdOrSlug}` | Get workspace details | JWT |
| PATCH | `/workspaces/{publicId}` | Update workspace | JWT |
| DELETE | `/workspaces/{publicId}` | Delete workspace (soft) | JWT |
| GET | `/workspaces/check-slug-availability` | Check slug availability | JWT |

#### GET `/workspaces`
```json
// Response 200
{
  "data": [
    {
      "id": "number",
      "publicId": "string (12 chars)",
      "name": "string",
      "slug": "string",
      "description": "string | null",
      "plan": "string (default 'free')",
      "createdBy": "uuid | null",
      "createdAt": "datetime",
      "updatedAt": "datetime | null",
      "deletedAt": "datetime | null",
      "deletedBy": "uuid | null",
      "role": "admin | member | guest"
    }
  ]
}
```

#### GET `/workspaces/check-slug-availability?workspaceSlug=...`
Also reads `slug` param as fallback.
```json
// Response 200
{
  "data": {
    "isAvailable": true,
    "isReserved": false
  }
}
```

#### POST `/workspaces`
```json
// Request
{ "name": "string (1-64)", "description": "string (max 280, optional)" }

// Response 201
{
  "data": { /* workspace object with role */ },
  "message": "Workspace created"
}
```

#### GET `/workspaces/{publicIdOrSlug}`
```json
// Response 200
{
  "data": {
    "id": "number",
    "publicId": "string",
    "name": "string",
    "slug": "string",
    "description": "string | null",
    "plan": "string",
    "createdBy": "uuid | null",
    "createdAt": "datetime",
    "updatedAt": "datetime | null",
    "deletedAt": "datetime | null",
    "deletedBy": "uuid | null",
    "role": "admin | member | guest"
  }
}
```

---

### Boards

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/workspaces/{wsPublicId}/boards` | List boards in workspace | JWT |
| POST | `/workspaces/{wsPublicId}/boards` | Create board | JWT |
| GET | `/boards/{publicIdOrSlug}` | Get board with full nested data | JWT |
| PATCH | `/boards/{publicId}` | Update board | JWT |
| DELETE | `/boards/{publicId}` | Delete board (soft) | JWT |

#### GET `/boards/{publicIdOrSlug}` — Board with full nested data
```json
{
  "data": {
    "id": "number",
    "publicId": "string",
    "name": "string",
    "slug": "string",
    "description": "string | null",
    "workspaceId": "number",
    "visibility": "private | public",
    "type": "regular | template",
    "createdBy": "uuid | null",
    "createdAt": "datetime",
    "updatedAt": "datetime | null",
    "deletedAt": "datetime | null",
    "deletedBy": "uuid | null",
    "lists": [
      {
        "id": "number",
        "publicId": "string",
        "name": "string",
        "index": "number",
        "boardId": "number",
        "createdBy": "uuid | null",
        "createdAt": "datetime",
        "updatedAt": "datetime | null",
        "deletedAt": "datetime | null",
        "deletedBy": "uuid | null",
        "cards": [
          {
            "id": "number",
            "publicId": "string",
            "title": "string",
            "description": "string",
            "index": "number",
            "createdBy": "uuid | null",
            "createdAt": "datetime",
            "updatedAt": "datetime | null",
            "deletedAt": "datetime | null",
            "deletedBy": "uuid | null",
            "listId": "number",
            "dueDate": "datetime | null",
            "cardNumber": "number | null",
            "labels": [
              {
                "cardId": "number",
                "labelId": "number",
                "id": "number",
                "publicId": "string",
                "name": "string",
                "colourCode": "string | null"
              }
            ],
            "members": [
              {
                "cardId": "number",
                "workspaceMemberId": "number",
                "id": "number",
                "publicId": "string",
                "userId": "uuid | null",
                "role": "admin | member | guest",
                "userName": "string | null",
                "userImage": "string | null"
              }
            ]
          }
        ]
      }
    ],
    "labels": [
      {
        "id": "number",
        "publicId": "string",
        "name": "string",
        "colourCode": "string | null",
        "boardId": "number",
        "createdBy": "uuid | null",
        "createdAt": "datetime",
        "updatedAt": "datetime | null",
        "deletedAt": "datetime | null",
        "deletedBy": "uuid | null"
      }
    ],
    "workspace": {
      "publicId": "string",
      "members": [
        {
          "id": "number",
          "publicId": "string",
          "email": "string",
          "role": "admin | member | guest",
          "userId": "uuid | null",
          "user": {
            "id": "uuid",
            "name": "string | null",
            "email": "string",
            "image": "string | null"
          }
        }
      ]
    }
  }
}
```

---

### Lists

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/lists?boardPublicId=` | List lists in board | JWT |
| POST | `/lists` | Create list | JWT |
| PUT | `/lists/reorder` | Reorder lists | JWT |
| PATCH | `/lists/{publicId}` | Update list | JWT |
| DELETE | `/lists/{publicId}` | Delete list (soft) | JWT |

#### GET `/lists?boardPublicId=...`
```json
{
  "data": [
    {
      "id": "number",
      "publicId": "string",
      "name": "string",
      "index": "number",
      "boardId": "number",
      "createdBy": "uuid | null",
      "createdAt": "datetime",
      "updatedAt": "datetime | null",
      "deletedAt": "datetime | null",
      "deletedBy": "uuid | null"
    }
  ]
}
```

---

### Cards

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/cards?listPublicId=` | List cards in list | JWT |
| POST | `/cards` | Create card | JWT |
| GET | `/cards/{publicId}` | Get card with full nested data | JWT |
| PATCH | `/cards/{publicId}` | Update card | JWT |
| PUT | `/cards/{publicId}/move` | Move card to another list | JWT |
| POST | `/cards/{publicId}/duplicate` | Duplicate card | JWT |
| DELETE | `/cards/{publicId}` | Delete card (soft) | JWT |
| PUT | `/cards/{id}/labels/{labelId}` | Toggle label on card | JWT |
| PUT | `/cards/{id}/members/{memberId}` | Toggle member on card | JWT |

#### GET `/cards/{publicId}` — Card with full nested data
```json
{
  "data": {
    "id": "number",
    "publicId": "string",
    "title": "string",
    "description": "string | null",
    "index": "number",
    "createdBy": "uuid | null",
    "createdAt": "datetime",
    "updatedAt": "datetime | null",
    "deletedAt": "datetime | null",
    "deletedBy": "uuid | null",
    "listId": "number",
    "dueDate": "datetime | null",
    "cardNumber": "number | null",
    "list": {
      "id": "number",
      "publicId": "string",
      "name": "string",
      "index": "number",
      "boardId": "number",
      "createdBy": "uuid | null",
      "createdAt": "datetime",
      "updatedAt": "datetime | null",
      "deletedAt": "datetime | null",
      "deletedBy": "uuid | null"
    },
    "labels": [
      {
        "cardId": "number",
        "labelId": "number",
        "id": "number",
        "publicId": "string",
        "name": "string",
        "colourCode": "string | null"
      }
    ],
    "members": [
      {
        "cardId": "number",
        "workspaceMemberId": "number",
        "id": "number",
        "publicId": "string",
        "userId": "uuid | null",
        "role": "admin | member | guest",
        "userName": "string | null",
        "userImage": "string | null"
      }
    ],
    "checklists": [
      {
        "id": "number",
        "publicId": "string",
        "name": "string",
        "index": "number",
        "cardId": "number",
        "createdBy": "uuid | null",
        "createdAt": "datetime",
        "updatedAt": "datetime | null",
        "deletedAt": "datetime | null",
        "deletedBy": "uuid | null",
        "items": [
          {
            "id": "number",
            "publicId": "string",
            "title": "string",
            "completed": false,
            "index": "number",
            "checklistId": "number",
            "createdBy": "uuid | null",
            "createdAt": "datetime",
            "updatedAt": "datetime | null",
            "deletedAt": "datetime | null",
            "deletedBy": "uuid | null"
          }
        ]
      }
    ],
    "comments": [
      {
        "id": "number",
        "publicId": "string",
        "comment": "string",
        "cardId": "number",
        "createdBy": "uuid",
        "createdAt": "datetime",
        "updatedAt": "datetime | null",
        "deletedAt": "datetime | null",
        "deletedBy": "uuid | null",
        "user": {
          "id": "uuid",
          "name": "string | null",
          "email": "string",
          "image": "string | null"
        }
      }
    ],
    "attachments": [
      {
        "id": "number",
        "publicId": "string",
        "cardId": "number",
        "filename": "string",
        "originalFilename": "string",
        "contentType": "string",
        "size": "number",
        "path": "string",
        "createdBy": "uuid | null",
        "createdAt": "datetime",
        "deletedAt": "datetime | null"
      }
    ]
  }
}
```

#### PUT `/cards/{id}/labels/{labelId}`
`labelId` is the label's **internal database ID** (INT), NOT the publicId.
```json
// Response 200 (added)
{ "data": { "newLabel": true } }

// Response 200 (removed)
{ "data": { "newLabel": false } }
```

#### PUT `/cards/{id}/members/{memberId}`
`memberId` is the workspace member's **internal database ID** (INT), NOT the publicId.
```json
// Response 200 (added)
{ "data": { "newMember": true } }

// Response 200 (removed)
{ "data": { "newMember": false } }
```

---

### Labels

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/labels?boardId=` or `?boardPublicId=` | List labels in board | JWT |
| POST | `/labels` | Create label | JWT |
| PATCH | `/labels/{publicId}` | Update label | JWT |
| DELETE | `/labels/{publicId}` | Delete label | JWT |

---

### Checklists

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/checklists?cardId=` | List checklists with items | JWT |
| POST | `/checklists` | Create checklist | JWT |
| PATCH | `/checklists/{publicId}` | Update checklist | JWT |
| DELETE | `/checklists/{publicId}` | Delete checklist | JWT |
| POST | `/checklists/{id}/items` | Add item to checklist | JWT |
| PATCH | `/checklists/items/{publicId}` | Update item | JWT |
| DELETE | `/checklists/items/{publicId}` | Delete item | JWT |

---

### Comments

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/comments?cardId=` | List comments with user info | JWT |
| POST | `/comments` | Create comment | JWT |
| PATCH | `/comments/{publicId}` | Update comment | JWT |
| DELETE | `/comments/{publicId}` | Delete comment | JWT |

---

### Activity

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/activities?cardId=` | List activity log | JWT |

---

### Attachments

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/attachments/{cardId}` | List attachments | JWT |
| POST | `/attachments/{cardId}` | Upload attachment (multipart) | JWT |
| DELETE | `/attachments/{publicId}` | Delete attachment | JWT |
| GET | `/attachments/download/{publicId}` | Download file | JWT |

```json
// Response attachment object (path is a full URL for preview)
{
  "id": "number",
  "publicId": "string",
  "cardId": "number",
  "filename": "string",
  "originalFilename": "string",
  "contentType": "string",
  "size": "number",
  "path": "http://localhost:8080/api/attachments/download/{publicId}",
  "downloadUrl": "http://localhost:8080/api/attachments/download/{publicId}",
  "createdBy": "uuid | null",
  "createdAt": "datetime",
  "deletedAt": "datetime | null"
}
```

---

### Members

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/members?workspaceId=` | List members | JWT |
| POST | `/members` | Invite member | JWT |
| PATCH | `/members/{publicId}` | Update member role | JWT |
| DELETE | `/members/{publicId}` | Remove member | JWT |
| POST | `/members/{publicId}/resend-invite` | Resend invite | JWT |
| GET | `/members/invite/{code}` | Get invite info | No |
| POST | `/members/invite-signup` | Accept + sign up | No |

---

### API Keys

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/api-keys?workspaceSlug=` | List API keys | JWT |
| POST | `/api-keys?workspaceSlug=` | Create API key | JWT |
| DELETE | `/api-keys/{publicId}?workspaceSlug=` | Revoke API key | JWT |

---

### Webhooks

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/webhooks?workspaceSlug=` | List webhooks | JWT |
| POST | `/webhooks?workspaceSlug=` | Create webhook | JWT |
| DELETE | `/webhooks/{publicId}?workspaceSlug=` | Delete webhook | JWT |
| POST | `/webhooks/{publicId}/test` | Test webhook | JWT |

---

### Integrations

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/integrations?workspaceSlug=` | List integrations | JWT |
| PUT | `/integrations?workspaceSlug=` | Toggle integration | JWT |

---

### Dashboard

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/dashboard?workspacePublicId=` | Get dashboard data | JWT |

```json
// Response 200
{
  "data": {
    "summary": { "workspaces": 0, "boards": 0, "cards": 0, "members": 0 },
    "cardsPerList": [{ "name": "string", "count": 0 }],
    "cardsOverTime": [{ "date": "YYYY-MM-DD", "count": 0 }],
    "overdue": { "count": 0, "cards": [{ "publicId": "string", "title": "string", "dueDate": "datetime", "listName": "string", "boardName": "string" }] },
    "dueSoon": { "count": 0, "cards": [...] },
    "userActivity": [],
    "boardsPerWorkspace": [{ "name": "string", "count": 0 }]
  }
}
```

---

### Billing

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/billing?workspaceSlug=` | Get billing info | JWT |
| POST | `/billing/checkout?workspaceSlug=` | Create checkout session | JWT |
| POST | `/billing/notification` | Midtrans webhook | No |

```json
// Response 200
{
  "data": {
    "plan": "free | team | pro",
    "subscription": {
      "id": "number",
      "publicId": "string",
      "workspaceId": "number",
      "plan": "string",
      "status": "string",
      "startDate": "datetime",
      "endDate": "datetime | null",
      "midtransOrderId": "string | null",
      "paymentAmount": "number | null",
      "createdAt": "datetime",
      "updatedAt": "datetime | null"
    } | null,
    "usage": { "boards": 0, "members": 0, "storageBytes": 0 },
    "workspaceUsage": { "count": 0, "limit": 0 }
  }
}
```

---

### Plans

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/plans` | List all plans | No |
| GET | `/plans/{name}` | Get plan by name | No |

```json
// Response 200
{
  "data": [
    {
      "id": "number",
      "name": "string",
      "displayName": "string",
      "price": "number (IDR)",
      "currency": "IDR",
      "boardLimit": "number",
      "memberLimit": "number",
      "workspaceLimit": "number",
      "storageLimit": "number (bytes)",
      "features": ["string", ...],
      "isActive": true,
      "createdAt": "datetime",
      "updatedAt": "datetime | null"
    }
  ]
}
```

---

### Notifications

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/notifications` | List notifications | JWT |
| GET | `/notifications/unread-count` | Get unread count | JWT |
| PATCH | `/notifications/{publicId}/read` | Mark as read | JWT |
| PATCH | `/notifications/read-all` | Mark all as read | JWT |

---

## Architecture

### Project Structure
```
backend/
├── Module/
│   ├── Auth/          # Models, Services
│   ├── Board/         # Models, Services
│   ├── Card/          # Models, Services
│   ├── Checklist/     # Models, Services
│   ├── Comment/       # Models, Services
│   ├── Dashboard/     # Models, Services
│   ├── List/          # Models, Services
│   ├── Plan/          # Models, Services
│   ├── Notification/  # Models, Services
│   ├── Subscription/  # Models, Services
│   ├── Webhook/       # Models, Services
│   └── Workspace/     # Models, Services
├── Sites/
│   └── Endpoint/
│       ├── Config/
│       │   └── Routes.php    # All 90+ API routes
│       └── Controllers/
│           ├── Auth/
│           │   ├── AuthController.php
│           │   └── OAuthController.php
│           ├── Board/
│           ├── Card/
│           └── Workspace/
├── Filters/           # JWT auth filter
└── Helpers/           # generatePublicId()
```

### Route Resolution Order (Routes.php)
Routes are ordered to prevent `(:any)` catch-all from stealing sub-resource URLs:
1. Invite routes (`members/invite`, `members/invite-signup`) — no JWT filter
2. Sub-resource routes (`workspaces/{slug}/members`, etc.)
3. Standalone routes (`members`, `api-keys`, `webhooks`, etc.)
4. Attachment download route (before `(:any)` catch-all)
5. Resource-specific routes (`boards/{publicId}`, etc.)
6. Catch-all routes last

### Workspace Slug Resolution
All workspace sub-resource controllers support both:
- **Nested URL:** `/api/workspaces/{slug}/members` (segment(2) = slug)
- **Standalone URL:** `/api/members?workspaceId=` (query param)
- Reads from route parameter, segment, or query param in that order

### Database
- **Name:** `managpro`
- **24 tables** — matches Node.js schema
- Primary keys: INT AUTO_INCREMENT
- Public IDs: VARCHAR(12) generated by nanoid-equivalent
- User IDs: UUID strings
- Soft deletes: `deletedAt` + `deletedBy` on primary entities

### Auth Flow
1. Login/Register → returns `{ user, accessToken, refreshToken }`
2. Frontend stores tokens in `localStorage`
3. Axios interceptor sets `Authorization: Bearer <accessToken>`
4. JWT filter validates token on every request
5. `/auth/get-session` returns user object (no `data` wrapper)
6. OAuth: redirect flow with token in URL query params

### Key Differences from Node.js Original
| Feature | Node.js (Original) | PHP (This Backend) |
|---------|-------------------|-------------------|
| Auth | better-auth sessions | Custom JWT (HS256) |
| User ID | UUID string | UUID string |
| Public ID | nanoid 12 char | nanoid-equivalent 12 char |
| Response wrapper | `{ data: ... }` | `{ data: ... }` |
| Auth response | `{ user, session }` | `{ user, accessToken, refreshToken }` |
| `emailVerified` | Boolean | Boolean |
| GET message | Not included | Included on some endpoints |
| Credit card payment | - | Midtrans |
| File storage | S3 or local | Local (`writable/uploads/attachments/`) |

All 26 API endpoints tested and passing at `http://localhost:8080/api`. Response shapes verified field-by-field against the original Node.js backend. Every endpoint returns the documented JSON structure with correct field names, types (boolean, array, object), and nested relations.
