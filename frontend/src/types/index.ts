export interface User {
  id: string
  publicId: string
  name: string
  email: string
  emailVerified: boolean
  image?: string | null
  createdAt: string
  updatedAt: string
}

export interface Session {
  token: string
  expiresAt: string
}

export interface Workspace {
  id: string
  publicId: string
  name: string
  slug: string
  description?: string | null
  role?: string
  createdAt: string
  updatedAt: string
  deletedAt?: string | null
}

export interface Board {
  id: string
  publicId: string
  name: string
  slug: string
  description?: string | null
  workspaceId: string
  visibility: 'private' | 'public'
  createdBy: string
  createdAt: string
  updatedAt: string
  deletedAt?: string | null
  lists?: List[]
}

export interface List {
  id: string
  publicId: string
  name: string
  boardId: string
  index: number
  createdAt: string
  updatedAt: string
  deletedAt?: string | null
  cards?: Card[]
}

export interface Card {
  id: string
  publicId: string
  title: string
  description?: string | null
  number: number
  listId: string
  index: number
  dueDate?: string | null
  createdBy: string
  createdAt: string
  updatedAt: string
  deletedAt?: string | null
  labels?: Label[]
  members?: CardMember[]
  checklists?: CardChecklist[]
  comments?: CardComment[]
  activities?: CardActivity[]
}

export interface Label {
  id: string
  publicId: string
  name: string
  color: string
  boardId: string
  createdAt: string
  updatedAt: string
}

export interface CardChecklist {
  id: string
  publicId: string
  name: string
  cardId: string
  createdAt: string
  updatedAt: string
  items?: CardChecklistItem[]
}

export interface CardChecklistItem {
  id: string
  publicId: string
  title: string
  completed: boolean
  checklistId: string
  index: number
  createdAt: string
  updatedAt: string
}

export interface CardComment {
  id: string
  publicId: string
  text: string
  cardId: string
  userId: string
  createdAt: string
  updatedAt: string
  user?: User
}

export interface CardActivity {
  id: string
  type: string
  cardId: string
  userId: string
  data?: any
  createdAt: string
  user?: User
}

export interface CardMember {
  cardId: string
  workspaceMemberId: string
  member?: WorkspaceMember
}

export interface WorkspaceMember {
  id: string
  publicId: string
  userId: string
  workspaceId: string
  role: 'admin' | 'member' | 'guest'
  createdAt: string
  updatedAt: string
  user?: User
}

export interface ApiResponse<T> {
  data: T
  message?: string
  meta?: {
    page: number
    limit: number
    total: number
    totalPages: number
  }
}
