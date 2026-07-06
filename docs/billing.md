# Billing & Subscription — User Flow

## Plans Overview

| Plan | Price | Boards | Members | Storage |
|------|-------|--------|---------|---------|
| **Free** | Rp 0 | 3 | 3 | 10 MB |
| **Team** | Rp 99.000/month | 20 | 10 | 500 MB |
| **Pro** | Rp 299.000/month | Unlimited | Unlimited | 5 GB |

---

## Flow 1: View Billing Page

1. User clicks **Settings > Billing** in workspace settings
2. System fetches workspace's current plan from `workspace.plan` column
3. System fetches active subscription from `subscriptions` table
4. System fetches usage stats (boards, members, storage) from DB
5. Page displays:
   - **Current plan** card (Free / Team / Pro)
   - **Usage bars** (boards, members, storage with real numbers)
   - **Compare plans** section (3 plan cards: Free, Team, Pro)
   - **Payment info** section

---

## Flow 2: Upgrade to Team or Pro

1. User clicks **Upgrade** on Team or Pro card
2. Frontend calls `POST /api/billing/checkout` with `{ planName: "team" }` or `{ planName: "pro" }`
3. Backend:
   - Validates user is workspace **admin**
   - Looks up plan price from `plans` table
   - Creates `subscription` record with status `pending`
   - Creates Midtrans Snap transaction with correct price
   - Returns Snap `token` to frontend
4. Frontend opens **Midtrans Snap popup** (`window.snap.pay(token)`)
5. User sees payment options (bank transfer, e-wallet, credit card, convenience store, etc.)

---

## Flow 3: Payment Success

1. User completes payment in Midtrans popup
2. Midtrans sends **webhook notification** to `POST /api/billing/notification`
3. Backend:
   - Matches `order_id` to subscription record
   - Verifies transaction status (`capture` or `settlement`)
   - Updates `subscriptions.status` → `active`
   - Sets `subscriptions.endDate` = now + 1 month
   - Updates `workspace.plan` → `team` or `pro`
4. Frontend refreshes billing page, shows **Active** badge

---

## Flow 4: Payment Pending

1. User selected bank transfer / VA (not instant payment)
2. Midtrans popup shows payment instructions
3. Status stays `pending` until Midtrans webhook confirms
4. User returns to billing page — sees subscription is pending
5. When payment completes, webhook activates subscription

---

## Flow 5: Payment Failed / Expired / Cancelled

1. If payment fails, expires, or is cancelled
2. Midtrans sends webhook with status `deny`, `expire`, or `cancel`
3. Backend updates `subscriptions.status` → `failed`
4. Workspace plan remains unchanged (still `free` if upgrading from free)

---

## Flow 6: Free Plan Limits

### Boards
- Free: max **3 boards**
- When at limit: "New" button disabled, yellow warning banner shown
- Warning links to billing page to upgrade

### Members
- Free: max **3 members**
- When at limit: "Invite" button disabled, yellow warning banner shown
- Warning links to billing page to upgrade

### Storage
- Free: max **10 MB**
- Shown in usage bar on billing page

---

## Flow 7: Plan Expiration (Manual Renewal)

> Note: Currently no auto-expiry cron job. Plans stay active until manually renewed or cancelled.

1. Subscription has `endDate` set to 1 month from payment
2. User can upgrade to a higher plan at any time
3. New subscription record created, old one kept as history

---

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/plans` | No | List all active plans |
| `GET` | `/api/plans/:name` | No | Get plan by name |
| `GET` | `/api/billing?workspaceSlug=` | Yes | Get subscription + usage |
| `POST` | `/api/billing/checkout?workspaceSlug=` | Yes (admin) | Create Midtrans Snap token |
| `POST` | `/api/billing/notification` | No (webhook) | Midtrans payment notification |

---

## Database Tables

### `plans` (config)
```
id, name, displayName, price, currency, boardLimit, memberLimit, storageLimit, features, isActive
```

### `subscriptions` (payment history)
```
id, publicId, workspaceId, plan, status, startDate, endDate, midtransOrderId, paymentAmount, createdAt, updatedAt
```

### `workspace` (current plan)
```
... existing columns ..., plan (text: 'free' | 'team' | 'pro')
```

---

## Subscription Statuses

| Status | Meaning |
|--------|---------|
| `pending` | Payment initiated, waiting for confirmation |
| `active` | Payment confirmed, plan is active |
| `failed` | Payment failed/expired/cancelled |
| `expired` | Plan expired (not yet implemented) |
