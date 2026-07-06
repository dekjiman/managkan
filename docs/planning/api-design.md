# API Contract — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Kontrak API antara frontend dan backend. Backend harus comply ke kontrak ini.

---

## 1. API Conventions

| Item | Convention |
|------|-----------|
| Base URL | `/api/v1` |
| Content Type | `application/json` |
| Auth Header | `Cookie: better-auth.session_token=xxx` |
| Date Format | ISO 8601 (`YYYY-MM-DDTHH:mm:ssZ`) |
| Pagination | `?page=1&limit=20` |
| Response Envelope | `{ "data": ..., "message": "..." }` |
| ID Format | 12-char `publicId` (external), UUID (internal) |

---

## 2. Standard Response Formats

### Success (200 / 201)
```json
{
  "data": { ... },
  "message": "Success"
}
```

### Success with Pagination
```json
{
  "data": [...],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "totalPages": 8
  }
}
```

### Error (400/401/403/404/500)
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error 1"]
  }
}
```

---

## 3. Authentication Endpoints (Better Auth)

### Sign Up
```
POST /api/auth/sign-up
Body:
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securePassword123"
}

Response 200:
{
  "data": {
    "user": { "id": "...", "publicId": "...", "name": "...", "email": "..." },
    "session": { "token": "...", "expiresAt": "..." }
  }
}
```

### Sign In
```
POST /api/auth/sign-in
Body:
{
  "email": "john@example.com",
  "password": "securePassword123"
}

Response 200:
{
  "data": {
    "user": { "id": "...", "publicId": "...", "name": "...", "email": "...", "image": "..." },
    "session": { "token": "...", "expiresAt": "..." }
  }
}
```

### Sign Out
```
POST /api/auth/sign-out
Response 200:
{
  "message": "Signed out successfully"
}
```

### Get Session
```
GET /api/auth/session
Response 200:
{
  "data": {
    "user": { "id": "...", "publicId": "...", "name": "...", "email": "...", "image": "..." },
    "session": { "token": "...", "expiresAt": "..." }
  }
}
```

### OAuth (Google/GitHub)
```
GET /api/auth/google    → Redirect to Google OAuth
GET /api/auth/github    → Redirect to GitHub OAuth
GET /api/auth/google/callback  → Callback handler
GET /api/auth/github/callback  → Callback handler
```

---

## 4. Workspace Endpoints

### List Workspaces
```
GET /api/v1/workspaces
Auth: required

Response 200:
{
  "data": [
    {
      "publicId": "abc123def456",
      "name": "My Team",
      "slug": "my-team",
      "description": "...",
      "role": "admin",
      "createdAt": "2026-01-01T00:00:00Z"
    }
  ]
}
```

### Create Workspace
```
POST /api/v1/workspaces
Auth: required
Body:
{
  "name": "My Team",
  "slug": "my-team",
  "description": "Team workspace"
}

Response 201:
{
  "data": {
    "publicId": "abc123def456",
    "name": "My Team",
    "slug": "my-team",
    "description": "Team workspace",
    "role": "admin",
    "createdAt": "2026-01-01T00:00:00Z"
  },
  "message": "Workspace created"
}
```

### Get Workspace
```
GET /api/v1/workspaces/:publicId
Auth: required (workspace member)

Response 200:
{
  "data": {
    "publicId": "abc123def456",
    "name": "My Team",
    "slug": "my-team",
    "description": "...",
    "role": "admin",
    "memberCount": 5,
    "createdAt": "2026-01-01T00:00:00Z"
  }
}
```

### Update Workspace
```
PATCH /api/v1/workspaces/:publicId
Auth: required (admin)
Body:
{
  "name": "Updated Name",
  "description": "Updated description"
}

Response 200:
{
  "data": { ... },
  "message": "Workspace updated"
}
```

### Delete Workspace
```
DELETE /api/v1/workspaces/:publicId
Auth: required (admin)

Response 200:
{
  "message": "Workspace deleted"
}
```

---

## 5. Board Endpoints

### List Boards
```
GET /api/v1/boards?workspaceId=abc123
Auth: required

Response 200:
{
  "data": [
    {
      "publicId": "...",
      "name": "Project Alpha",
      "slug": "project-alpha",
      "visibility": "private",
      "listCount": 5,
      "cardCount": 23,
      "createdAt": "..."
    }
  ]
}
```

### Create Board
```
POST /api/v1/boards
Auth: required
Body:
{
  "workspaceId": "abc123",
  "name": "Project Alpha",
  "slug": "project-alpha",
  "visibility": "private"
}

Response 201:
{
  "data": { "publicId": "...", "name": "Project Alpha", ... },
  "message": "Board created"
}
```

### Get Board (with lists and cards)
```
GET /api/v1/boards/:publicId
Auth: required

Response 200:
{
  "data": {
    "publicId": "...",
    "name": "Project Alpha",
    "lists": [
      {
        "publicId": "...",
        "name": "To Do",
        "index": 0,
        "cards": [
          {
            "publicId": "...",
            "title": "Design homepage",
            "number": 1,
            "labels": [...],
            "members": [...],
            "dueDate": "...",
            "checklistProgress": { "total": 5, "completed": 2 }
          }
        ]
      }
    ]
  }
}
```

### Update Board
```
PATCH /api/v1/boards/:publicId
Auth: required
Body: { "name": "...", "description": "...", "visibility": "public" }
```

### Delete Board
```
DELETE /api/v1/boards/:publicId
Auth: required
```

---

## 6. List Endpoints

### List Lists
```
GET /api/v1/lists?boardId=abc123
Auth: required

Response 200:
{
  "data": [
    { "publicId": "...", "name": "To Do", "index": 0, "cardCount": 5 }
  ]
}
```

### Create List
```
POST /api/v1/lists
Auth: required
Body: { "boardId": "...", "name": "In Progress" }
```

### Update List
```
PATCH /api/v1/lists/:publicId
Auth: required
Body: { "name": "Updated Name" }
```

### Delete List
```
DELETE /api/v1/lists/:publicId
Auth: required
```

### Reorder Lists
```
PUT /api/v1/lists/reorder
Auth: required
Body: {
  "boardId": "...",
  "listIds": ["list1", "list2", "list3"]
}
```

---

## 7. Card Endpoints

### List Cards
```
GET /api/v1/cards?listId=abc123&page=1&limit=50
Auth: required

Response 200:
{
  "data": [...],
  "meta": { "page": 1, "limit": 50, "total": 25, "totalPages": 1 }
}
```

### Create Card
```
POST /api/v1/cards
Auth: required
Body: { "listId": "...", "title": "New task" }

Response 201:
{
  "data": {
    "publicId": "...",
    "title": "New task",
    "number": 1,
    "index": 0,
    "createdAt": "..."
  },
  "message": "Card created"
}
```

### Get Card
```
GET /api/v1/cards/:publicId
Auth: required

Response 200:
{
  "data": {
    "publicId": "...",
    "title": "...",
    "description": "...",
    "number": 1,
    "dueDate": "...",
    "labels": [...],
    "members": [...],
    "checklists": [
      {
        "publicId": "...",
        "name": "Sub-tasks",
        "items": [
          { "publicId": "...", "title": "Item 1", "completed": true, "index": 0 }
        ]
      }
    ],
    "comments": [...],
    "activities": [...],
    "createdAt": "...",
    "updatedAt": "..."
  }
}
```

### Update Card
```
PATCH /api/v1/cards/:publicId
Auth: required
Body: { "title": "...", "description": "...", "dueDate": "..." }
```

### Delete Card
```
DELETE /api/v1/cards/:publicId
Auth: required
```

### Move Card
```
PUT /api/v1/cards/:publicId/move
Auth: required
Body: {
  "listId": "target-list-id",
  "index": 2
}
```

---

## 8. Label Endpoints

### List Labels
```
GET /api/v1/labels?boardId=abc123
```

### Create Label
```
POST /api/v1/labels
Body: { "boardId": "...", "name": "Bug", "color": "#ef4444" }
```

### Update Label
```
PATCH /api/v1/labels/:publicId
Body: { "name": "Critical", "color": "#dc2626" }
```

### Delete Label
```
DELETE /api/v1/labels/:publicId
```

### Assign Label to Card
```
POST /api/v1/cards/:cardId/labels
Body: { "labelId": "..." }
```

### Remove Label from Card
```
DELETE /api/v1/cards/:cardId/labels/:labelId
```

---

## 9. Checklist Endpoints

### List Checklists
```
GET /api/v1/checklists?cardId=abc123
```

### Create Checklist
```
POST /api/v1/checklists
Body: { "cardId": "...", "name": "Sub-tasks" }
```

### Update Checklist
```
PATCH /api/v1/checklists/:publicId
Body: { "name": "Updated name" }
```

### Delete Checklist
```
DELETE /api/v1/checklists/:publicId
```

### Create Checklist Item
```
POST /api/v1/checklists/:checklistId/items
Body: { "title": "First item" }
```

### Update Checklist Item
```
PATCH /api/v1/checklist-items/:publicId
Body: { "title": "...", "completed": true }
```

### Delete Checklist Item
```
DELETE /api/v1/checklist-items/:publicId
```

---

## 10. Comment Endpoints

### List Comments
```
GET /api/v1/comments?cardId=abc123
```

### Create Comment
```
POST /api/v1/comments
Body: { "cardId": "...", "text": "Great work!" }
```

### Update Comment
```
PATCH /api/v1/comments/:publicId
Body: { "text": "Updated comment" }
```

### Delete Comment
```
DELETE /api/v1/comments/:publicId
```

---

## 11. Member Endpoints

### List Members
```
GET /api/v1/members?workspaceId=abc123
```

### Add Member
```
POST /api/v1/members
Body: { "workspaceId": "...", "email": "user@example.com", "role": "member" }
```

### Update Member
```
PATCH /api/v1/members/:publicId
Body: { "role": "admin" }
```

### Remove Member
```
DELETE /api/v1/members/:publicId
```

---

## 12. User Endpoints

### Get Profile
```
GET /api/v1/users/me
Response: { "data": { "publicId": "...", "name": "...", "email": "...", "image": "..." } }
```

### Update Profile
```
PATCH /api/v1/users/me
Body: { "name": "New Name", "image": "https://..." }
```
