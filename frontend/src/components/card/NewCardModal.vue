<template>
  <Modal :is-open="true" @close="$emit('close')">
    <template #header>New card</template>
    <form @submit.prevent="createCard" class="space-y-3">
      <Input v-model="form.title" placeholder="Card title" autofocus />

      <textarea
        v-model="form.description"
        placeholder="Description"
        rows="3"
        class="input text-sm resize-none"
      />

      <div class="flex flex-wrap gap-1.5">
        <div class="relative">
          <button
            type="button"
            @click.stop="toggleDropdown('list')"
            class="toolbar-btn"
            :class="{ 'toolbar-btn-active': form.listPublicId !== props.listPublicId }"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            {{ selectedListName }}
          </button>
          <div v-if="openDropdown === 'list'" class="dropdown-menu" @click.stop>
            <button
              v-for="list in board.lists"
              :key="list.publicId"
              type="button"
              @click="selectList(list.publicId)"
              class="dropdown-item"
              :class="{ 'dropdown-item-active': form.listPublicId === list.publicId }"
            >
              {{ list.name }}
            </button>
          </div>
        </div>

        <div class="relative" v-if="!isTemplate">
          <button
            type="button"
            @click.stop="toggleDropdown('members')"
            class="toolbar-btn"
            :class="{ 'toolbar-btn-active': form.memberPublicIds.length > 0 }"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            {{ form.memberPublicIds.length ? `${form.memberPublicIds.length} members` : 'Members' }}
          </button>
          <div v-if="openDropdown === 'members'" class="dropdown-menu w-48 max-h-40 overflow-y-auto" @click.stop>
            <button
              v-for="member in board.workspace?.members || []"
              :key="member.publicId"
              type="button"
              @click="toggleMember(member.publicId)"
              class="dropdown-item gap-2"
              :class="{ 'dropdown-item-active': form.memberPublicIds.includes(member.publicId) }"
            >
              <Avatar :name="member.user?.name || member.email" size="sm" />
              <span class="truncate">{{ member.user?.name || member.email }}</span>
            </button>
          </div>
        </div>

        <div class="relative">
          <button
            type="button"
            @click.stop="toggleDropdown('labels')"
            class="toolbar-btn"
            :class="{ 'toolbar-btn-active': form.labelPublicIds.length > 0 }"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            {{ form.labelPublicIds.length ? `${form.labelPublicIds.length} labels` : 'Labels' }}
          </button>
          <div v-if="openDropdown === 'labels'" class="dropdown-menu w-44" @click.stop>
            <button
              v-for="label in board.labels || []"
              :key="label.publicId"
              type="button"
              @click="toggleLabel(label.publicId)"
              class="dropdown-item gap-2"
              :class="{ 'dropdown-item-active': form.labelPublicIds.includes(label.publicId) }"
            >
              <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: label.colourCode }" />
              <span class="truncate">{{ label.name }}</span>
            </button>
          </div>
        </div>

        <div class="relative">
          <button
            type="button"
            @click.stop="toggleDropdown('date')"
            class="toolbar-btn"
            :class="{ 'toolbar-btn-active': form.dueDate }"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ formattedDueDate || 'Due date' }}
          </button>
          <div v-if="openDropdown === 'date'" class="dropdown-menu p-2" @click.stop>
            <input
              type="date"
              :value="form.dueDate"
              @change="form.dueDate = ($event.target as HTMLInputElement).value; openDropdown = ''"
              class="input text-xs w-full"
            />
            <button v-if="form.dueDate" type="button" @click="form.dueDate = ''; openDropdown = ''" class="mt-1.5 text-xs text-red-500 hover:text-red-600 w-full text-left">Clear date</button>
          </div>
        </div>

        <button
          type="button"
          @click="form.position = form.position === 'start' ? 'end' : 'start'"
          class="toolbar-btn px-1.5"
        >
          <svg v-if="form.position === 'start'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
          </svg>
          <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>
    </form>

    <template #footer>
      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="createAnother" class="rounded border-light-500 dark:border-dark-500 text-primary-600 focus:ring-primary-500 w-3.5 h-3.5" />
          <span class="text-xs text-light-800 dark:text-dark-700">Create another</span>
        </label>
        <Button @click="createCard" :loading="isCreating" :disabled="!form.title.trim()" size="sm">Create card</Button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { cardService } from '@/services/card.service'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import Avatar from '@/components/ui/Avatar.vue'

const props = defineProps<{
  board: any
  listPublicId: string
  isTemplate?: boolean
}>()

const emit = defineEmits<{
  close: []
  created: []
}>()

const toast = useToast()

const form = ref({
  title: '',
  description: '',
  listPublicId: props.listPublicId,
  labelPublicIds: [] as string[],
  memberPublicIds: [] as string[],
  position: 'end' as 'start' | 'end',
  dueDate: ''
})

const createAnother = ref(false)
const isCreating = ref(false)
const openDropdown = ref('')

const selectedListName = computed(() => {
  const list = props.board.lists?.find((l: any) => l.publicId === form.value.listPublicId)
  return list?.name || 'List'
})

const formattedDueDate = computed(() => {
  if (!form.value.dueDate) return ''
  return new Date(form.value.dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
})

function toggleDropdown(name: string) {
  openDropdown.value = openDropdown.value === name ? '' : name
}

function selectList(listPublicId: string) {
  form.value.listPublicId = listPublicId
  openDropdown.value = ''
}

function toggleMember(memberPublicId: string) {
  const idx = form.value.memberPublicIds.indexOf(memberPublicId)
  if (idx === -1) {
    form.value.memberPublicIds.push(memberPublicId)
  } else {
    form.value.memberPublicIds.splice(idx, 1)
  }
}

function toggleLabel(labelPublicId: string) {
  const idx = form.value.labelPublicIds.indexOf(labelPublicId)
  if (idx === -1) {
    form.value.labelPublicIds.push(labelPublicId)
  } else {
    form.value.labelPublicIds.splice(idx, 1)
  }
}

async function createCard() {
  if (!form.value.title.trim()) return

  isCreating.value = true
  try {
    await cardService.create({
      title: form.value.title.trim(),
      description: form.value.description,
      listPublicId: form.value.listPublicId,
      labelPublicIds: form.value.labelPublicIds,
      memberPublicIds: form.value.memberPublicIds,
      position: form.value.position,
      dueDate: form.value.dueDate || null
    })

    toast.success('Card created')

    if (createAnother.value) {
      form.value = {
        title: '',
        description: '',
        listPublicId: props.listPublicId,
        labelPublicIds: [],
        memberPublicIds: [],
        position: form.value.position,
        dueDate: ''
      }
    } else {
      emit('created')
      emit('close')
    }
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to create card')
  } finally {
    isCreating.value = false
  }
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (!target.closest('.relative')) {
    openDropdown.value = ''
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.toolbar-btn {
  @apply flex items-center gap-1.5 rounded-md border border-light-500 bg-light-100 px-2.5 py-1.5 text-xs text-light-900
         hover:bg-light-200 transition-colors
         dark:border-dark-500 dark:bg-dark-400 dark:text-dark-900 dark:hover:bg-dark-500;
}

.toolbar-btn-active {
  @apply border-primary-400 bg-primary-50 text-primary-700
         dark:border-primary-500 dark:bg-dark-200 dark:text-primary-400;
}

.dropdown-menu {
  @apply absolute left-0 top-full mt-1 w-40 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg
         border border-light-300 dark:border-dark-400 py-1 z-20 max-h-48 overflow-y-auto;
}

.dropdown-item {
  @apply w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300 transition-colors flex items-center;
}

.dropdown-item-active {
  @apply bg-primary-50 text-primary-600 dark:bg-dark-300 dark:text-primary-400;
}
</style>
