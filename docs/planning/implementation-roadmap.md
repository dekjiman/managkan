# Implementation Roadmap — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Timeline dan urutan implementasi fitur.

---

## Phase 1: Foundation (Week 1)

### Goals
- Project setup and configuration
- Database schema and migrations
- Basic auth flow

### Tasks

| # | Task | Priority | Est. Hours |
|---|------|----------|-----------|
| 1.1 | Setup frontend (Vue + Vite + Tailwind) | P0 | 2h |
| 1.2 | Setup backend (Express + TypeScript) | P0 | 2h |
| 1.3 | Setup Drizzle ORM + Supabase connection | P0 | 2h |
| 1.4 | Create database schema (auth tables) | P0 | 2h |
| 1.5 | Create database schema (workspace, board, list, card) | P0 | 3h |
| 1.6 | Create database schema (label, checklist, comment, activity) | P0 | 2h |
| 1.7 | Run migrations | P0 | 1h |
| 1.8 | Setup Better Auth (backend) | P0 | 3h |
| 1.9 | Setup auth routes (register, login, logout, session) | P0 | 2h |
| 1.10 | Setup auth middleware | P0 | 1h |
| 1.11 | Setup frontend auth (login/register pages) | P0 | 3h |
| 1.12 | Setup Vue Router + route guards | P0 | 1h |
| 1.13 | Setup Pinia stores (auth) | P0 | 1h |
| 1.14 | Setup Axios API client | P0 | 1h |

**Total: ~26 hours**

---

## Phase 2: Core CRUD (Week 2)

### Goals
- Workspace, Board, List, Card CRUD
- Basic UI layout

### Tasks

| # | Task | Priority | Est. Hours |
|---|------|----------|-----------|
| 2.1 | Workspace API routes (CRUD) | P0 | 3h |
| 2.2 | Board API routes (CRUD) | P0 | 3h |
| 2.3 | List API routes (CRUD + reorder) | P0 | 3h |
| 2.4 | Card API routes (CRUD + move) | P0 | 4h |
| 2.5 | Activity logging service | P0 | 2h |
| 2.6 | Frontend layout (sidebar, header) | P0 | 3h |
| 2.7 | Dashboard view (workspace list) | P0 | 2h |
| 2.8 | Workspace creation form | P0 | 1h |
| 2.9 | Board list view | P0 | 2h |
| 2.10 | Board creation form | P0 | 1h |
| 2.11 | Board detail view (lists + cards) | P0 | 4h |
| 2.12 | List creation form | P0 | 1h |
| 2.13 | Card creation form | P0 | 1h |

**Total: ~30 hours**

---

## Phase 3: Card Features (Week 3)

### Goals
- Card detail panel
- Labels, Members, Checklists, Comments

### Tasks

| # | Task | Priority | Est. Hours |
|---|------|----------|-----------|
| 3.1 | Card detail panel (slide-over) | P0 | 4h |
| 3.2 | Card title editing (inline) | P0 | 1h |
| 3.3 | Card description editing | P0 | 2h |
| 3.4 | Label API routes | P0 | 2h |
| 3.5 | Label assignment UI | P0 | 2h |
| 3.6 | Member API routes | P0 | 2h |
| 3.7 | Member assignment UI | P0 | 2h |
| 3.8 | Checklist API routes | P0 | 3h |
| 3.9 | Checklist UI | P0 | 3h |
| 3.10 | Comment API routes | P0 | 2h |
| 3.11 | Comment UI | P0 | 2h |
| 3.12 | Activity feed UI | P0 | 2h |
| 3.13 | Due date picker | P1 | 2h |

**Total: ~29 hours**

---

## Phase 4: Polish & UX (Week 4)

### Goals
- Drag and drop
- Filtering
- Dark mode
- Responsive design

### Tasks

| # | Task | Priority | Est. Hours |
|---|------|----------|-----------|
| 4.1 | Drag & drop cards between lists | P0 | 4h |
| 4.2 | Drag & drop list reordering | P0 | 3h |
| 4.3 | Board filters (by member, label) | P1 | 3h |
| 4.4 | Dark mode toggle | P1 | 2h |
| 4.5 | Responsive design (mobile) | P1 | 4h |
| 4.6 | Loading states | P1 | 1h |
| 4.7 | Error handling (toast notifications) | P1 | 2h |
| 4.8 | Empty states | P1 | 1h |
| 4.9 | Confirmation dialogs | P1 | 1h |
| 4.10 | Keyboard shortcuts | P2 | 2h |

**Total: ~23 hours**

---

## Phase 5: Deployment (Week 4-5)

### Goals
- Deploy to Vercel
- Environment setup
- Documentation

### Tasks

| # | Task | Priority | Est. Hours |
|---|------|----------|-----------|
| 5.1 | Vercel project setup | P0 | 1h |
| 5.2 | Environment variables | P0 | 1h |
| 5.3 | Frontend deployment | P0 | 1h |
| 5.4 | Backend deployment (serverless) | P0 | 2h |
| 5.5 | Custom domain setup | P1 | 1h |
| 5.6 | Supabase database setup | P0 | 1h |
| 5.7 | Run migrations on Supabase | P0 | 1h |
| 5.8 | OAuth setup (Google, GitHub) | P0 | 1h |
| 5.9 | .env.example file | P0 | 0.5h |
| 5.10 | README.md | P0 | 1h |

**Total: ~10.5 hours**

---

## Phase 6: Post-MVP Features (Future)

### Deferred Features

| # | Feature | Priority | Est. Hours |
|---|---------|----------|-----------|
| 6.1 | Rich text editor (TipTap) | High | 8h |
| 6.2 | File attachments (Supabase Storage) | High | 6h |
| 6.3 | Due date filtering | Medium | 3h |
| 6.4 | Board templates | Medium | 4h |
| 6.5 | Notifications | Medium | 6h |
| 6.6 | Public boards | Medium | 4h |
| 6.7 | RBAC (custom roles) | Medium | 6h |
| 6.8 | Trello import | Medium | 6h |
| 6.9 | i18n (multiple languages) | Low | 8h |
| 6.10 | Command palette (Ctrl+K) | Low | 4h |
| 6.11 | Webhooks | Low | 4h |
| 6.12 | API keys | Low | 4h |
| 6.13 | Stripe billing | Low | 8h |
| 6.14 | MCP server | Low | 8h |

**Total: ~75 hours (future)**

---

## Summary

| Phase | Hours | Timeline |
|-------|-------|----------|
| Phase 1: Foundation | 26h | Week 1 |
| Phase 2: Core CRUD | 30h | Week 2 |
| Phase 3: Card Features | 29h | Week 3 |
| Phase 4: Polish & UX | 23h | Week 4 |
| Phase 5: Deployment | 10.5h | Week 4-5 |
| **MVP Total** | **~118h** | **5 weeks** |
| Phase 6: Post-MVP | 75h | Future |

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|-----------|
| Better Auth Express incompatibility | Low | High | Test early, check docs |
| Vercel serverless cold start | Medium | Medium | Use edge functions where possible |
| Supabase connection limits | Low | Low | Use connection pooling |
| Drag & drop complexity | Medium | Medium | Use proven library (vue-draggable) |
| OAuth callback URL issues | Medium | High | Test in staging first |

---

## Success Criteria

### MVP is done when:

- [ ] User can register and login (email + OAuth)
- [ ] User can create workspaces
- [ ] User can create boards within workspaces
- [ ] User can create lists within boards
- [ ] User can create cards within lists
- [ ] User can move cards between lists (drag & drop)
- [ ] User can add/edit labels, members, checklists, comments
- [ ] Activity log tracks all card changes
- [ ] Dark mode works
- [ ] Responsive on mobile
- [ ] Deployed to Vercel
- [ ] Supabase database connected
- [ ] OAuth (Google, GitHub) working
