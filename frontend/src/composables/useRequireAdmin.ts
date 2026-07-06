import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'

export function useRequireAdmin() {
  const router = useRouter()
  const workspaceStore = useWorkspaceStore()

  onMounted(() => {
    const ws = workspaceStore.currentWorkspace
    if (ws && ws.role !== 'admin') {
      router.replace('/settings/account')
    }
  })
}
