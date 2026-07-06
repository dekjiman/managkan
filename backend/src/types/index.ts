export interface User {
  id: string
  name: string
  email: string
  emailVerified: boolean
  image?: string | null
  createdAt: Date
  updatedAt: Date
}

export interface Workspace {
  id: string
  publicId: string
  name: string
  slug: string
  description?: string | null
  createdBy: string
  createdAt: Date
  updatedAt: Date
  deletedAt?: Date | null
}

export interface WorkspaceMember {
  id: string
  publicId: string
  userId: string
  workspaceId: string
  role: 'admin' | 'member' | 'guest'
  createdAt: Date
  updatedAt: Date
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
  createdAt: Date
  updatedAt: Date
  deletedAt?: Date | null
}

export interface List {
  id: string
  publicId: string
  name: string
  boardId: string
  index: number
  createdAt: Date
  updatedAt: Date
  deletedAt?: Date | null
}

export interface Card {
  id: string
  publicId: string
  title: string
  description?: string | null
  number: number
  listId: string
  index: number
  dueDate?: Date | null
  createdBy: string
  createdAt: Date
  updatedAt: Date
  deletedAt?: Date | null
}

export interface Label {
  id: string
  publicId: string
  name: string
  color: string
  boardId: string
  createdAt: Date
  updatedAt: Date
}

export interface CardChecklist {
  id: string
  publicId: string
  name: string
  cardId: string
  createdAt: Date
  updatedAt: Date
}

export interface CardChecklistItem {
  id: string
  publicId: string
  title: string
  completed: boolean
  checklistId: string
  index: number
  createdAt: Date
  updatedAt: Date
}

export interface CardComment {
  id: string
  publicId: string
  text: string
  cardId: string
  userId: string
  createdAt: Date
  updatedAt: Date
  deletedAt?: Date | null
}

export interface CardActivity {
  id: string
  type: string
  cardId: string
  userId: string
  data?: any
  createdAt: Date
}
