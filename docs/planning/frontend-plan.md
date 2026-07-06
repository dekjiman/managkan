# Frontend Plan — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Rencana implementasi frontend Vue.js.

---

## 1. Tech Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| Framework | Vue.js | 3.4+ | UI framework |
| Build | Vite | 5.x | Dev server + bundler |
| Styling | Tailwind CSS | 3.4+ | Utility-first CSS |
| Routing | Vue Router | 4.x | Client-side routing |
| State | Pinia | 2.x | State management |
| HTTP | Axios | 1.x | API client |
| DnD | vuedraggable/vue-draggable-plus | Latest | Drag and drop |
| Icons | Lucide Vue | Latest | Icon library |

---

## 2. Project Structure

```
frontend/
├── public/                    # Static assets
├── src/
│   ├── assets/                # Images, fonts
│   ├── components/            # Reusable components
│   │   ├── ui/                # Base UI components
│   │   │   ├── Button.vue
│   │   │   ├── Input.vue
│   │   │   ├── Modal.vue
│   │   │   ├── Dropdown.vue
│   │   │   ├── Avatar.vue
│   │   │   ├── Badge.vue
│   │   │   ├── Tooltip.vue
│   │   │   ├── Toggle.vue
│   │   │   ├── LoadingSpinner.vue
│   │   │   └── ConfirmDialog.vue
│   │   ├── layout/            # Layout components
│   │   │   ├── AppLayout.vue
│   │   │   ├── Sidebar.vue
│   │   │   ├── Header.vue
│   │   │   └── WorkspaceSwitcher.vue
│   │   ├── board/             # Board-related
│   │   │   ├── BoardView.vue
│   │   │   ├── BoardHeader.vue
│   │   │   ├── BoardFilters.vue
│   │   │   └── BoardList.vue
│   │   ├── list/              # List-related
│   │   │   ├── ListView.vue
│   │   │   ├── ListHeader.vue
│   │   │   └── ListForm.vue
│   │   ├── card/              # Card-related
│   │   │   ├── CardItem.vue
│   │   │   ├── CardDetail.vue
│   │   │   ├── CardForm.vue
│   │   │   ├── CardLabels.vue
│   │   │   ├── CardMembers.vue
│   │   │   ├── CardDueDate.vue
│   │   │   ├── CardChecklists.vue
│   │   │   ├── CardComments.vue
│   │   │   └── CardActivity.vue
│   │   └── workspace/         # Workspace-related
│   │       ├── WorkspaceList.vue
│   │       ├── WorkspaceForm.vue
│   │       └── MemberList.vue
│   ├── composables/           # Vue composables (hooks)
│   │   ├── useAuth.ts
│   │   ├── useWorkspaces.ts
│   │   ├── useBoards.ts
│   │   ├── useLists.ts
│   │   ├── useCards.ts
│   │   ├── useLabels.ts
│   │   ├── useMembers.ts
│   │   ├── useTheme.ts
│   │   └── useModal.ts
│   ├── views/                 # Page-level components
│   │   ├── auth/
│   │   │   ├── LoginView.vue
│   │   │   └── RegisterView.vue
│   │   ├── dashboard/
│   │   │   └── DashboardView.vue
│   │   ├── workspace/
│   │   │   ├── WorkspaceView.vue
│   │   │   └── WorkspaceSettingsView.vue
│   │   ├── board/
│   │   │   └── BoardDetailView.vue
│   │   └── settings/
│   │       └── AccountSettingsView.vue
│   ├── stores/                # Pinia stores
│   │   ├── auth.ts
│   │   ├── workspace.ts
│   │   ├── board.ts
│   │   └── ui.ts
│   ├── services/              # API client
│   │   ├── api.ts             # Axios instance
│   │   ├── auth.service.ts
│   │   ├── workspace.service.ts
│   │   ├── board.service.ts
│   │   ├── list.service.ts
│   │   ├── card.service.ts
│   │   ├── label.service.ts
│   │   ├── checklist.service.ts
│   │   ├── comment.service.ts
│   │   ├── member.service.ts
│   │   └── user.service.ts
│   ├── router/                # Vue Router
│   │   └── index.ts
│   ├── types/                 # TypeScript types
│   │   ├── auth.ts
│   │   ├── workspace.ts
│   │   ├── board.ts
│   │   ├── list.ts
│   │   ├── card.ts
│   │   ├── label.ts
│   │   ├── checklist.ts
│   │   ├── comment.ts
│   │   ├── member.ts
│   │   └── api.ts
│   ├── utils/                 # Utilities
│   │   ├── date.ts
│   │   ├── slug.ts
│   │   └── format.ts
│   ├── App.vue
│   └── main.ts
├── index.html
├── vite.config.ts
├── tailwind.config.js
├── postcss.config.js
├── tsconfig.json
├── package.json
└── .env.example
```

---

## 3. Routing

```typescript
const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: LoginView, meta: { guest: true } },
  { path: '/register', component: RegisterView, meta: { guest: true } },
  {
    path: '/dashboard',
    component: DashboardView,
    meta: { auth: true }
  },
  {
    path: '/:workspaceSlug',
    component: WorkspaceView,
    meta: { auth: true },
    children: [
      { path: '', component: BoardListView },
      { path: 'settings', component: WorkspaceSettingsView },
      { path: ':boardSlug', component: BoardDetailView },
    ]
  },
  {
    path: '/settings/account',
    component: AccountSettingsView,
    meta: { auth: true }
  },
]
```

---

## 4. State Management (Pinia)

### Auth Store
```typescript
interface AuthState {
  user: User | null
  session: Session | null
  isAuthenticated: boolean
}
// Actions: login, register, logout, fetchSession
```

### Workspace Store
```typescript
interface WorkspaceState {
  workspaces: Workspace[]
  currentWorkspace: Workspace | null
}
// Actions: fetchWorkspaces, createWorkspace, setCurrentWorkspace
```

### Board Store
```typescript
interface BoardState {
  boards: Board[]
  currentBoard: BoardWithLists | null
}
// Actions: fetchBoards, createBoard, fetchBoardDetail
```

### UI Store
```typescript
interface UIState {
  theme: 'light' | 'dark' | 'system'
  sidebarOpen: boolean
  activeModal: string | null
}
// Actions: toggleTheme, toggleSidebar, openModal, closeModal
```

---

## 5. API Client (Axios)

```typescript
// services/api.ts
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' }
})

// Request interceptor: attach token if needed
// Response interceptor: handle errors, refresh session
```

---

## 6. Key Components

### Board View
- Horizontal scrollable columns
- Drag-and-drop cards between lists
- Drag-and-drop list reordering
- Board header with filters and actions
- Quick card creation at bottom of each list

### Card Detail
- Slide-over or modal panel
- Inline title editing
- Plain text description editing
- Label management (add/remove)
- Member management (add/remove)
- Due date picker
- Checklist management
- Comments section
- Activity feed

### Auth Pages
- Clean, minimal forms
- Email + password login/register
- OAuth buttons (Google, GitHub)
- Error handling with friendly messages

---

## 7. Styling Strategy

- Tailwind CSS for all styling
- Dark mode via `dark:` class variant
- CSS custom properties for theme colors
- Responsive design: mobile-first approach
- Consistent spacing and typography scale

---

## 8. Performance Targets

| Metric | Target |
|--------|--------|
| First Contentful Paint | < 1s |
| Largest Contentful Paint | < 2s |
| Bundle Size (gzipped) | < 200KB |
| Lighthouse Score | > 90 |
