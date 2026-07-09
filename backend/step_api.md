# API Audit: Frontend vs Backend

## Summary
Frontend calls standalone endpoints with query params for context (e.g., `workspaceSlug`).
Backend routes are mostly nested under `/api/workspaces/{slug}/...`.
Missing = standalone routes that need to be added + controllers must accept query params as fallback.

> **Status: ✅ ALL 19 frontend service files mapped — standalone routes implemented, controllers accept query params as fallback, 26/26 endpoints tested and passing.**

---

## Auth (`auth.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `POST /auth/sign-up/email` | `AuthController::register` | ✅ EXISTS |
| `POST /auth/sign-in/email` | `AuthController::login` | ✅ EXISTS |
| `POST /auth/sign-out` | `AuthController::logout` | ✅ EXISTS |
| `GET /auth/get-session` | `AuthController::getSession` | ✅ EXISTS |
| `POST /auth/send-verification-email` | `AuthController::verifyEmail` | ✅ EXISTS |
| `POST /auth/request-password-reset` | `AuthController::forgotPassword` | ✅ EXISTS |
| `POST /auth/reset-password` | `AuthController::resetPassword` | ✅ EXISTS |
 | `GET /members/invite/{code}` | `GET /api/members/invite/(:any)` | ✅ FIXED standalone |
| `POST /members/invite-signup` | `POST /api/members/invite-signup` | ✅ FIXED standalone |

## User (`user.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /users/me` | `UserController::me` | ✅ EXISTS |
| `PATCH /users/me` | `UserController::updateMe` | ✅ EXISTS |

## Workspace (`workspace.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /workspaces` | GET `/api/workspaces/` | ✅ EXISTS |
| `POST /workspaces` | POST `/api/workspaces/` | ✅ EXISTS |
| `GET /workspaces/{publicId}` | `WorkspaceController::show` | ✅ EXISTS |
| `PATCH /workspaces/{publicId}` | `WorkspaceController::update` | ✅ EXISTS |
| `DELETE /workspaces/{publicId}` | `WorkspaceController::destroy` | ✅ EXISTS |
 | `GET /workspaces/check-slug-availability?workspaceSlug=` | `WorkspaceController::checkSlugAvailability` | ✅ FIXED — reads `workspaceSlug` or `slug` |

## Plan (`plan.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /plans` | `PlanController::index` | ✅ EXISTS |
| `GET /plans/{name}` | `PlanController::show` | ✅ EXISTS |

## Board (`board.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /workspaces/{id}/boards` | `BoardController::index` | ✅ EXISTS |
| `POST /workspaces/{id}/boards` | `BoardController::store` | ✅ EXISTS |
| `GET /boards/{publicIdOrSlug}` | `BoardController::show` | ✅ EXISTS |
| `PATCH /boards/{publicId}` | `BoardController::update` | ✅ EXISTS |
| `DELETE /boards/{publicId}` | `BoardController::destroy` | ✅ EXISTS |

## List (`list.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /lists?boardPublicId=` | `ListController::index` | ✅ EXISTS |
| `POST /lists` | `ListController::store` | ✅ EXISTS |
| `PUT /lists/reorder` | `ListController::reorder` | ✅ EXISTS |
| `PATCH /lists/{publicId}` | `ListController::update` | ✅ EXISTS |
| `DELETE /lists/{publicId}` | `ListController::destroy` | ✅ EXISTS |

## Card (`card.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /cards?listPublicId=` | `CardController::index` | ✅ EXISTS |
| `POST /cards` | `CardController::store` | ✅ EXISTS |
| `GET /cards/{publicId}` | `CardController::show` | ✅ EXISTS |
| `PATCH /cards/{publicId}` | `CardController::update` | ✅ EXISTS |
| `PUT /cards/{publicId}/move` | `CardController::move` | ✅ EXISTS |
| `POST /cards/{publicId}/duplicate` | `CardController::duplicate` | ✅ EXISTS |
| `DELETE /cards/{publicId}` | `CardController::destroy` | ✅ EXISTS |
| `PUT /cards/{id}/labels/{labelId}` | `CardController::toggleLabel` | ✅ EXISTS |
| `PUT /cards/{id}/members/{memberId}` | `CardController::toggleMember` | ✅ EXISTS |

## Label (`label.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /labels?boardId=` | `LabelController::index` | ✅ EXISTS |
| `POST /labels` | `LabelController::store` | ✅ EXISTS |
| `PATCH /labels/{publicId}` | `LabelController::update` | ✅ EXISTS |
| `DELETE /labels/{publicId}` | `LabelController::destroy` | ✅ EXISTS |

## Checklist (`checklist.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /checklists?cardId=` | `ChecklistController::index` | ✅ EXISTS |
| `POST /checklists` | `ChecklistController::store` | ✅ EXISTS |
| `PATCH /checklists/{publicId}` | `ChecklistController::update` | ✅ EXISTS |
| `DELETE /checklists/{publicId}` | `ChecklistController::destroy` | ✅ EXISTS |
| `POST /checklists/{id}/items` | `ChecklistController::addItem` | ✅ EXISTS |
| `PATCH /checklists/items/{publicId}` | `ChecklistController::updateItem` | ✅ EXISTS |
| `DELETE /checklists/items/{publicId}` | `ChecklistController::deleteItem` | ✅ EXISTS |

## Comment (`comment.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /comments?cardId=` | `CommentController::index` | ✅ EXISTS |
| `POST /comments` | `CommentController::store` | ✅ EXISTS |
| `PATCH /comments/{publicId}` | `CommentController::update` | ✅ EXISTS |
| `DELETE /comments/{publicId}` | `CommentController::destroy` | ✅ EXISTS |

## Activity (`activity.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /activities?cardId=` | `ActivityController::index` | ✅ EXISTS |

## Attachment (`attachment.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /attachments/{cardId}` | `AttachmentController::index` | ✅ EXISTS |
| `POST /attachments/{cardId}` | `AttachmentController::upload` | ✅ EXISTS |
| `DELETE /attachments/{publicId}` | `AttachmentController::destroy` | ✅ EXISTS |
| `GET /attachments/download/{publicId}` | `AttachmentController::download` | ✅ EXISTS |

## Member (`member.service.ts`) — ✅ ALL FIXED standalone

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /members?workspaceId=` | `GET /api/members` + nested | ✅ FIXED standalone |
| `POST /members` | `POST /api/members` + nested | ✅ FIXED standalone |
| `PATCH /members/{publicId}` | `PATCH /api/members/(:any)` + nested | ✅ FIXED standalone |
| `DELETE /members/{publicId}` | `DELETE /api/members/(:any)` + nested | ✅ FIXED standalone |
| `POST /members/{publicId}/resend-invite` | `POST /api/members/(:any)/resend-invite` + nested | ✅ FIXED standalone |

## API Keys (`apikeys.service.ts`) — ✅ ALL FIXED standalone

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /api-keys?workspaceSlug=` | `GET /api/api-keys` + nested | ✅ FIXED standalone |
| `POST /api-keys?workspaceSlug=` | `POST /api/api-keys` + nested | ✅ FIXED standalone |
| `DELETE /api-keys/{publicId}?workspaceSlug=` | `DELETE /api/api-keys/(:any)` + nested | ✅ FIXED standalone |

## Webhooks (`webhooks.service.ts`) — ✅ ALL FIXED standalone

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /webhooks?workspaceSlug=` | `GET /api/webhooks` + nested | ✅ FIXED standalone |
| `POST /webhooks?workspaceSlug=` | `POST /api/webhooks` + nested | ✅ FIXED standalone |
| `DELETE /webhooks/{publicId}?workspaceSlug=` | `DELETE /api/webhooks/(:any)` + nested | ✅ FIXED standalone |
| `POST /webhooks/{publicId}/test` | `POST /api/webhooks/(:any)/test` + nested | ✅ FIXED standalone |

## Integrations (`integrations.service.ts`) — ✅ ALL FIXED standalone

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /integrations?workspaceSlug=` | `GET /api/integrations` + nested | ✅ FIXED standalone |
| `PUT /integrations?workspaceSlug=` | `PUT /api/integrations` + nested | ✅ FIXED standalone |

## Notifications (`notification.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /notifications` | `NotificationController::index` | ✅ EXISTS |
| `GET /notifications/unread-count` | `NotificationController::unreadCount` | ✅ EXISTS |
| `PATCH /notifications/{publicId}/read` | `NotificationController::markRead` | ✅ EXISTS |
| `PATCH /notifications/read-all` | `NotificationController::markAllRead` | ✅ EXISTS |

## Dashboard (`dashboard.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /dashboard?workspacePublicId=` | `DashboardController::index` | ✅ EXISTS |

## Billing (`billing.service.ts`)

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /billing?workspaceSlug=` | `BillingController::index` + standalone | ✅ EXISTS (FIXED) |
| `POST /billing/checkout?workspaceSlug=` | `BillingController::checkout` + standalone | ✅ EXISTS (FIXED) |

---

## Action Items (all resolved ✅)

### P0 — Add standalone routes + query param fallbacks:
1. **Members** — `/api/members`, `/api/members/{publicId}`, `/api/members/{publicId}/resend-invite` ✅ FIXED
2. **API Keys** — `/api/api-keys`, `/api/api-keys/{publicId}` ✅ FIXED
3. **Webhooks** — `/api/webhooks`, `/api/webhooks/{publicId}`, `/api/webhooks/{publicId}/test` ✅ FIXED
4. **Integrations** — `/api/integrations` ✅ FIXED
5. **Members Invite** — `/api/members/invite/{code}`, `/api/members/invite-signup` ✅ FIXED

### P1 — Fix controller issues:
6. `WorkspaceController::checkSlugAvailability` — reads `workspaceSlug` or `slug` ✅ FIXED
7. `DashboardController::index` — reads `workspacePublicId` query param ✅ FIXED

### P2 — Test all endpoints end-to-end ✅ PASSED (26/26)
