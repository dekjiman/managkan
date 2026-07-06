# Database Plan — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Cetak biru database: ERD, schema, relasi, indexing strategy.

---

## 1. Database Topology

| Connection | DB Engine | Database Name | Purpose |
|-----------|----------|--------------|---------|
| default | PostgreSQL 15+ (Supabase) | managpro | Primary data |

---

## 2. Entity Relationship Diagram

```text
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│     user     │───<   │ workspace_member  │>───   │  workspace   │
├──────────────┤       ├──────────────────┤       ├──────────────┤
│ id (UUID)    │       │ id               │       │ id (UUID)    │
│ publicId     │       │ publicId         │       │ publicId     │
│ name         │       │ userId (FK)      │       │ name         │
│ email        │       │ workspaceId (FK) │       │ slug         │
│ emailVerified│       │ role             │       │ description  │
│ image        │       │ createdAt        │       │ createdBy    │
│ createdAt    │       │ updatedAt        │       │ createdAt    │
│ updatedAt    │       └──────────────────┘       │ updatedAt    │
└──────────────┘                                  │ deletedAt    │
                                                  └──────────────┘
                                                          │
                                                          ▼
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│     card     │───<   │     list     │>───   │     board    │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id (UUID)    │       │ id (UUID)    │       │ id (UUID)    │
│ publicId     │       │ publicId     │       │ publicId     │
│ title        │       │ name         │       │ name         │
│ description  │       │ boardId (FK) │       │ slug         │
│ number       │       │ index        │       │ workspaceId  │
│ listId (FK)  │       │ createdAt    │       │ visibility   │
│ index        │       │ updatedAt    │       │ createdBy    │
│ dueDate      │       │ deletedAt    │       │ createdAt    │
│ createdAt    │       └──────────────┘       │ updatedAt    │
│ updatedAt    │                              │ deletedAt    │
│ deletedAt    │                              └──────────────┘
└──────────────┘
       │
       ├──< card_label >── label
       ├──< card_member >── workspace_member
       ├──< card_checklist >── card_checklist_item
       ├──< card_comment
       └──< card_activity
```

---

## 3. Table Schemas

### `user` (Better Auth)

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | Auto-generated |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External-facing ID |
| `name` | VARCHAR(255) | NOT NULL | | Display name |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | UNIQUE | Login email |
| `emailVerified` | BOOLEAN | DEFAULT false | | Email verification status |
| `image` | TEXT | nullable | | Avatar URL |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |

### `session` (Better Auth)

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `expiresAt` | TIMESTAMP | NOT NULL | INDEX | Session expiry |
| `token` | VARCHAR(255) | UNIQUE, NOT NULL | UNIQUE | Session token |
| `ipAddress` | TEXT | nullable | | Client IP |
| `userAgent` | TEXT | nullable | | Browser info |
| `userId` | UUID | FK → user.id | INDEX | Session owner |

### `account` (Better Auth)

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `accountId` | TEXT | NOT NULL | INDEX | OAuth provider ID |
| `providerId` | TEXT | NOT NULL | INDEX | OAuth provider name |
| `userId` | UUID | FK → user.id | INDEX | Account owner |
| `accessToken` | TEXT | nullable | | OAuth token |
| `refreshToken` | TEXT | nullable | | OAuth refresh token |
| `accessTokenExpiresAt` | TIMESTAMP | nullable | | Token expiry |
| `refreshTokenExpiresAt` | TIMESTAMP | nullable | | Refresh expiry |
| `password` | TEXT | nullable | | Hashed password (email auth) |

### `verification` (Better Auth)

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `identifier` | TEXT | NOT NULL | INDEX | Email or other identifier |
| `value` | TEXT | NOT NULL | | Verification code |
| `expiresAt` | TIMESTAMP | NOT NULL | | Code expiry |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |

### `workspace`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `name` | VARCHAR(255) | NOT NULL | | Workspace name |
| `slug` | VARCHAR(255) | UNIQUE, NOT NULL | UNIQUE | URL-friendly slug |
| `description` | TEXT | nullable | | Optional description |
| `createdBy` | UUID | FK → user.id | INDEX | Creator |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |
| `deletedAt` | TIMESTAMP | nullable | INDEX | Soft delete |

### `workspace_member`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `userId` | UUID | FK → user.id | INDEX | Member user |
| `workspaceId` | UUID | FK → workspace.id | INDEX | Workspace |
| `role` | VARCHAR(20) | DEFAULT 'member' | INDEX | admin/member/guest |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |

**Unique constraint:** (userId, workspaceId)

### `board`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `name` | VARCHAR(255) | NOT NULL | | Board name |
| `slug` | VARCHAR(255) | NOT NULL | | URL slug |
| `description` | TEXT | nullable | | Board description |
| `workspaceId` | UUID | FK → workspace.id | INDEX | Parent workspace |
| `visibility` | VARCHAR(10) | DEFAULT 'private' | INDEX | private/public |
| `createdBy` | UUID | FK → user.id | INDEX | Creator |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |
| `deletedAt` | TIMESTAMP | nullable | INDEX | Soft delete |

**Unique constraint:** (slug, workspaceId)

### `list`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `name` | VARCHAR(255) | NOT NULL | | List name |
| `boardId` | UUID | FK → board.id | INDEX | Parent board |
| `index` | INTEGER | NOT NULL | INDEX | Sort order |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |
| `deletedAt` | TIMESTAMP | nullable | INDEX | Soft delete |

### `card`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `title` | VARCHAR(500) | NOT NULL | | Card title |
| `description` | TEXT | nullable | | Card description |
| `number` | INTEGER | NOT NULL | | Auto-increment per workspace |
| `listId` | UUID | FK → list.id | INDEX | Parent list |
| `index` | INTEGER | NOT NULL | INDEX | Sort order within list |
| `dueDate` | TIMESTAMP | nullable | INDEX | Optional due date |
| `createdBy` | UUID | FK → user.id | INDEX | Creator |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |
| `deletedAt` | TIMESTAMP | nullable | INDEX | Soft delete |

### `label`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `name` | VARCHAR(255) | NOT NULL | | Label name |
| `color` | VARCHAR(7) | NOT NULL | | Hex color code |
| `boardId` | UUID | FK → board.id | INDEX | Parent board |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |

### `card_label`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `cardId` | UUID | FK → card.id | INDEX | |
| `labelId` | UUID | FK → label.id | INDEX | |

**Primary key:** (cardId, labelId)

### `card_member`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `cardId` | UUID | FK → card.id | INDEX | |
| `workspaceMemberId` | UUID | FK → workspace_member.id | INDEX | |

**Primary key:** (cardId, workspaceMemberId)

### `card_checklist`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `name` | VARCHAR(255) | NOT NULL | | Checklist name |
| `cardId` | UUID | FK → card.id | INDEX | Parent card |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |

### `card_checklist_item`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `title` | VARCHAR(500) | NOT NULL | | Item text |
| `completed` | BOOLEAN | DEFAULT false | | Completion status |
| `checklistId` | UUID | FK → card_checklist.id | INDEX | Parent checklist |
| `index` | INTEGER | NOT NULL | | Sort order |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |

### `card_comment`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `publicId` | VARCHAR(12) | UNIQUE, NOT NULL | UNIQUE | External ID |
| `text` | TEXT | NOT NULL | | Comment content |
| `cardId` | UUID | FK → card.id | INDEX | Parent card |
| `userId` | UUID | FK → user.id | INDEX | Author |
| `createdAt` | TIMESTAMP | DEFAULT now() | | |
| `updatedAt` | TIMESTAMP | DEFAULT now() | | |
| `deletedAt` | TIMESTAMP | nullable | | Soft delete |

### `card_activity`

| Column | Type | Constraints | Index | Notes |
|--------|------|------------|-------|-------|
| `id` | UUID | PK | PRIMARY | |
| `type` | VARCHAR(50) | NOT NULL | INDEX | Activity type |
| `cardId` | UUID | FK → card.id | INDEX | Related card |
| `userId` | UUID | FK → user.id | INDEX | Actor |
| `data` | JSONB | nullable | | Activity metadata |
| `createdAt` | TIMESTAMP | DEFAULT now() | INDEX | When it happened |

---

## 4. Relationships Summary

| Table A | Relation | Table B | Foreign Key |
|---------|----------|---------|-------------|
| user | hasMany | session | session.userId |
| user | hasMany | account | account.userId |
| user | hasMany | workspace_member | workspace_member.userId |
| user | hasMany | board (createdBy) | board.createdBy |
| user | hasMany | card (createdBy) | card.createdBy |
| user | hasMany | card_comment | card_comment.userId |
| user | hasMany | card_activity | card_activity.userId |
| workspace | hasMany | workspace_member | workspace_member.workspaceId |
| workspace | hasMany | board | board.workspaceId |
| workspace_member | belongsTo | user | workspace_member.userId |
| workspace_member | belongsTo | workspace | workspace_member.workspaceId |
| workspace_member | hasMany | card_member | card_member.workspaceMemberId |
| board | hasMany | list | list.boardId |
| board | hasMany | label | label.boardId |
| board | belongsTo | workspace | board.workspaceId |
| list | hasMany | card | card.listId |
| list | belongsTo | board | list.boardId |
| card | belongsTo | list | card.listId |
| card | hasMany | card_label | card_label.cardId |
| card | hasMany | card_member | card_member.cardId |
| card | hasMany | card_checklist | card_checklist.cardId |
| card | hasMany | card_comment | card_comment.cardId |
| card | hasMany | card_activity | card_activity.cardId |
| card_checklist | hasMany | card_checklist_item | card_checklist_item.checklistId |
| label | belongsTo | board | label.boardId |

---

## 5. Indexing Strategy

| Table | Index | Columns | Type | Reason |
|-------|-------|---------|------|--------|
| session | `idx_session_user` | userId | BTREE | Session lookup by user |
| session | `idx_session_token` | token | UNIQUE | Token lookup |
| workspace | `idx_workspace_slug` | slug | UNIQUE | Slug lookup |
| workspace | `idx_workspace_deleted` | deletedAt | BTREE | Soft delete filter |
| workspace_member | `idx_wm_user` | userId | BTREE | User's workspaces |
| workspace_member | `idx_wm_workspace` | workspaceId | BTREE | Workspace members |
| workspace_member | `idx_wm_unique` | userId, workspaceId | UNIQUE | Prevent duplicates |
| board | `idx_board_workspace` | workspaceId | BTREE | Workspace boards |
| board | `idx_board_deleted` | deletedAt | BTREE | Soft delete filter |
| list | `idx_list_board` | boardId | BTREE | Board lists |
| list | `idx_list_index` | boardId, index | BTREE | Ordering |
| card | `idx_card_list` | listId | BTREE | List cards |
| card | `idx_card_index` | listId, index | BTREE | Ordering within list |
| card | `idx_card_due` | dueDate | BTREE | Due date queries |
| card | `idx_card_deleted` | deletedAt | BTREE | Soft delete filter |
| card_activity | `idx_activity_card` | cardId | BTREE | Card activity feed |
| card_activity | `idx_activity_created` | createdAt | BTREE | Chronological sorting |

---

## 6. Migration Plan

| # | Migration | Table | Domain | Notes |
|---|----------|-------|--------|-------|
| 1 | `001_create_auth_tables` | user, session, account, verification | Auth | Better Auth schema |
| 2 | `002_create_workspace` | workspace, workspace_member | Workspace | Multi-workspace |
| 3 | `003_create_board` | board | Board | Board management |
| 4 | `004_create_list` | list | List | Board columns |
| 5 | `005_create_card` | card | Card | Core entity |
| 6 | `006_create_label` | label, card_label | Label | Card labels |
| 7 | `007_create_card_member` | card_member | Card | Member assignment |
| 8 | `008_create_checklist` | card_checklist, card_checklist_item | Card | Sub-tasks |
| 9 | `009_create_comment` | card_comment | Card | Discussions |
| 10 | `010_create_activity` | card_activity | Card | Change tracking |

---

## 7. Seed Data

| Seeder | Purpose | Data Source |
|--------|---------|------------|
| Default Workspace | Auto-create on first login | User's name |
| Default Board | Auto-create "Getting Started" board | Hardcoded |
| Default Lists | To Do, In Progress, Done | Hardcoded |
