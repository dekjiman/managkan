<template>
  <div v-if="isLoading" class="flex justify-center py-12">
    <LoadingSpinner />
  </div>
  <div v-else-if="boardError" class="flex flex-col items-center justify-center py-12 gap-3">
    <p class="text-sm text-red-600 dark:text-red-400">{{ boardError }}</p>
    <Button size="sm" @click="loadBoard">Retry</Button>
  </div>
  <div v-else-if="board" class="h-[calc(100vh-5.5rem)] flex flex-col">
    <!-- Board Header -->
    <div class="h-12 shrink-0 border-b border-light-300 dark:border-dark-400 flex items-center justify-between px-4 bg-light-50 dark:bg-dark-50">
      <div class="flex items-center gap-3">
        <h1 class="font-semibold text-sm text-light-1000 dark:text-dark-1000">{{ board.name }}</h1>

        <!-- Visibility Dropdown -->
        <div class="relative">
            <button @click.stop="showVisibilityDropdown = !showVisibilityDropdown" class="focus:outline-none">
              <Badge :variant="board.visibility === 'public' ? 'success' : 'default'">
                {{ board.visibility }}
              </Badge>
            </button>
          <div v-if="showVisibilityDropdown" class="absolute top-full left-0 mt-1 z-50 min-w-[140px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg" @click.stop>
            <button @click="toggleVisibility('private')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              Private
            </button>
            <button @click="toggleVisibility('public')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Public
            </button>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Filter Dropdown -->
        <div class="relative">
          <Button size="sm" variant="ghost" @click.stop="showFilterDropdown = !showFilterDropdown">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter
            <span v-if="activeFilterCount > 0" class="ml-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary-500 text-[10px] text-white">{{ activeFilterCount }}</span>
          </Button>
          <div v-if="showFilterDropdown" class="absolute top-full right-0 mt-1 z-50 min-w-[240px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg" @click.stop>
            <!-- Labels -->
            <div>
              <button @click="expandFilterCategory('labels')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
                <svg class="h-4 w-4 shrink-0" :class="expandedFilterCategory === 'labels' ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                <span class="flex-1 text-left">Labels</span>
                <span v-if="filterLabels.length" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-[10px] text-white">{{ filterLabels.length }}</span>
              </button>
              <div v-if="expandedFilterCategory === 'labels' && board.labels?.length" class="px-3 pb-2">
                <div v-for="label in board.labels" :key="label.publicId" class="flex items-center gap-2 py-1">
                  <input type="checkbox" :checked="filterLabels.includes(label.publicId)" @change="toggleFilterLabel(label.publicId)" class="h-3.5 w-3.5 rounded border-light-400" />
                  <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="{ backgroundColor: label.colourCode }" />
                  <span class="text-sm text-light-1000 dark:text-dark-1000">{{ label.name }}</span>
                </div>
              </div>
            </div>
            <!-- Members -->
            <div v-if="board.workspace?.members?.length">
              <button @click="expandFilterCategory('members')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
                <svg class="h-4 w-4 shrink-0" :class="expandedFilterCategory === 'members' ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" /></svg>
                <span class="flex-1 text-left">Members</span>
                <span v-if="filterMembers.length" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-[10px] text-white">{{ filterMembers.length }}</span>
              </button>
              <div v-if="expandedFilterCategory === 'members'" class="px-3 pb-2">
                <div v-for="member in board.workspace.members" :key="member.publicId" class="flex items-center gap-2 py-1">
                  <input type="checkbox" :checked="filterMembers.includes(member.publicId)" @change="toggleFilterMember(member.publicId)" class="h-3.5 w-3.5 rounded border-light-400" />
                  <span class="text-sm text-light-1000 dark:text-dark-1000">{{ member.user?.name || member.email }}</span>
                </div>
              </div>
            </div>
            <!-- Lists -->
            <div v-if="board.lists?.length">
              <button @click="expandFilterCategory('lists')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
                <svg class="h-4 w-4 shrink-0" :class="expandedFilterCategory === 'lists' ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M7 5a2 2 0 012-2h6a2 2 0 012 2M7 5h10" /></svg>
                <span class="flex-1 text-left">Lists</span>
                <span v-if="filterLists.length" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-[10px] text-white">{{ filterLists.length }}</span>
              </button>
              <div v-if="expandedFilterCategory === 'lists'" class="px-3 pb-2">
                <div v-for="list in board.lists" :key="list.publicId" class="flex items-center gap-2 py-1">
                  <input type="checkbox" :checked="filterLists.includes(list.publicId)" @change="toggleFilterList(list.publicId)" class="h-3.5 w-3.5 rounded border-light-400" />
                  <span class="text-sm text-light-1000 dark:text-dark-1000">{{ list.name }}</span>
                </div>
              </div>
            </div>
            <!-- Due Date -->
            <div>
              <button @click="expandFilterCategory('dueDate')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
                <svg class="h-4 w-4 shrink-0" :class="expandedFilterCategory === 'dueDate' ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="flex-1 text-left">Due date</span>
                <span v-if="filterDueDate" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-[10px] text-white">1</span>
              </button>
              <div v-if="expandedFilterCategory === 'dueDate'" class="px-3 pb-2">
                <div v-for="opt in dueDateOptions" :key="opt.value" class="flex items-center gap-2 py-1">
                  <input type="checkbox" :checked="filterDueDate === opt.value" @change="toggleFilterDueDate(opt.value)" class="h-3.5 w-3.5 rounded border-light-400" />
                  <span class="text-sm text-light-1000 dark:text-dark-1000">{{ opt.label }}</span>
                </div>
              </div>
            </div>
            <div v-if="activeFilterCount > 0" class="border-t border-light-200 dark:border-dark-400 px-3 pt-2 pb-1">
              <button @click="clearFilters" class="text-xs text-primary-500 hover:text-primary-600">Clear all filters</button>
            </div>
          </div>
        </div>

        <!-- New List Button -->
        <Button size="sm" @click="showNewListForm = true">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          New list
        </Button>

        <!-- Board Dropdown -->
        <div class="relative">
          <Button size="sm" variant="ghost" @click.stop="showBoardDropdown = !showBoardDropdown">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
          </Button>
          <div v-if="showBoardDropdown" class="absolute top-full right-0 mt-1 z-50 min-w-[180px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg" @click.stop>
            <button @click="copyBoardLink" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
              Copy board link
            </button>
            <button @click="showDeleteConfirm = true" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-light-200 dark:hover:bg-dark-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              Delete board
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lists Area -->
    <div class="flex-1 overflow-x-auto overflow-y-hidden p-4 scrollbar-w-none scrollbar-track-rounded-[4px] scrollbar-thumb-rounded-[4px] scrollbar-h-[8px] scrollbar scrollbar-track-light-200 scrollbar-thumb-light-400 dark:scrollbar-track-dark-100 dark:scrollbar-thumb-dark-300">
      <div class="flex gap-5 h-full items-start w-max">
        <draggable
          v-model="board.lists"
          group="lists"
          item-key="publicId"
          @end="onListReorder"
          class="contents"
          :disabled="activeFilterCount > 0"
          ghost-class="opacity-50"
        >
          <template #item="{element: list}">
            <div
              class="h-fit min-w-[18rem] max-w-[18rem] rounded-md border border-light-400 dark:border-dark-300 bg-light-300 dark:bg-dark-100 py-2 pl-2 pr-1 text-light-1000 dark:text-dark-1000"
            >
              <!-- List Header -->
              <div class="mb-2 flex justify-between items-center">
                <input
                  :value="list.name"
                  @blur="updateListName(list, ($event.target as HTMLInputElement).value)"
                  class="w-full border-0 bg-transparent px-4 pt-1 text-sm font-medium text-light-1000 dark:text-dark-1000 focus:ring-0 focus-visible:outline-none"
                />
                <div class="flex items-center gap-1">
                  <button
                    @click="openNewCardModal(list.publicId)"
                    class="inline-flex h-fit items-center rounded-md p-1 text-sm font-semibold hover:bg-light-400 dark:hover:bg-dark-200 transition-colors"
                  >
                    <svg class="h-5 w-5 text-dark-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </button>
                  <button
                    @click="deleteList(list.publicId)"
                    class="inline-flex h-fit items-center rounded-md p-1 text-sm font-semibold text-light-700 hover:bg-light-400 dark:hover:bg-dark-200 transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Cards (draggable) -->
              <draggable
                :list="list.cards"
                group="cards"
                item-key="publicId"
                @change="(change) => onCardChange(change, list)"
                class="flex-1 overflow-y-auto p-1 space-y-1 min-h-[40px] max-h-[calc(100vh-16rem)]"
                :disabled="activeFilterCount > 0"
                ghost-class="opacity-50"
              >
                <template #item="{element: card}">
                  <CardItem
                    :card="card"
                    @click="openCard(card)"
                    @contextmenu="(e: MouseEvent) => openContextMenu(e, card)"
                  />
                </template>
              </draggable>
              <div v-if="filteredCards(list.cards, list.publicId).length === 0 && activeFilterCount > 0" class="py-4 text-center text-xs text-light-700 dark:text-dark-700">
                No cards match filters
              </div>
            </div>
          </template>
        </draggable>

        <!-- Empty Board State -->
        <div v-if="!board.lists?.length && !showNewListForm" class="flex flex-col items-center justify-center min-w-[20rem] py-16">
          <svg class="w-12 h-12 text-light-400 dark:text-dark-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
          </svg>
          <p class="text-sm text-light-700 dark:text-dark-700 mb-3">This board has no lists yet.</p>
          <Button size="sm" @click="showNewListForm = true">Create your first list</Button>
        </div>

        <!-- New List Form -->
        <div class="min-w-[18rem] max-w-[18rem]">
          <div v-if="showNewListForm" class="rounded-md border border-light-400 dark:border-dark-300 bg-light-300 dark:bg-dark-100 p-3">
            <input
              v-model="newListName"
              @keydown.enter="createList"
              @keydown.escape="showNewListForm = false"
              placeholder="List name..."
              class="w-full px-2 py-1 text-sm bg-transparent border-none focus:outline-none text-light-1000 dark:text-dark-1000 placeholder-light-700 dark:placeholder-dark-600"
              autofocus
            />
            <div class="flex gap-2 mt-2">
              <Button size="sm" @click="createList" :loading="isCreatingList" :disabled="!newListName.trim()">Add list</Button>
              <Button size="sm" variant="ghost" @click="showNewListForm = false">Cancel</Button>
            </div>
          </div>
          <button
            v-else
            @click="showNewListForm = true"
            class="w-full text-left px-3 py-2 text-sm text-light-800 dark:text-dark-700 hover:bg-light-300 dark:hover:bg-dark-200 rounded-md transition-colors"
          >
            + Add list
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Card Context Menu -->
  <div
    v-if="contextMenu"
    class="fixed z-[200] min-w-[200px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg"
    :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
    @click.stop
  >
    <button @click="contextMenuAction('copyLink')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
      Copy link to card
    </button>
    <button @click="contextMenuAction('duplicate')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
      Duplicate card
    </button>
    <button @click="contextMenuAction('move')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
      Move to list
    </button>
    <button @click="contextMenuAction('labels')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
      Add / edit label
    </button>
    <button @click="contextMenuAction('members')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" /></svg>
      Add / edit members
    </button>
    <button @click="contextMenuAction('dueDate')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
      Set due date
    </button>
    <hr class="my-1 border-light-200 dark:border-dark-400" />
    <button @click="contextMenuAction('delete')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
      Delete card
    </button>
  </div>

  <!-- Move to List Submenu -->
  <div
    v-if="contextSubmenu === 'move'"
    class="fixed z-[201] min-w-[180px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg"
    :style="{ left: (contextMenu?.x || 0) + 210 + 'px', top: (contextMenu?.y || 0) + 'px' }"
    @click.stop
  >
    <button
      v-for="list in board?.lists"
      :key="list.publicId"
      @click="moveCardTo(contextMenuCard?.publicId, list.publicId)"
      class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400"
    >
      {{ list.name }}
    </button>
  </div>

  <!-- Labels Submenu -->
  <div
    v-if="contextSubmenu === 'labels'"
    class="fixed z-[201] min-w-[180px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-2 shadow-lg"
    :style="{ left: (contextMenu?.x || 0) + 210 + 'px', top: (contextMenu?.y || 0) + 'px' }"
    @click.stop
  >
    <div v-for="label in board?.labels" :key="label.publicId" class="flex items-center gap-2 px-3 py-1.5">
      <input
        type="checkbox"
        :checked="contextMenuCard?.labels?.some((l: any) => l.publicId === label.publicId)"
        @change="toggleCardLabel(contextMenuCard?.publicId, label.publicId)"
        class="h-3.5 w-3.5 rounded border-light-400"
      />
      <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: label.colourCode }" />
      <span class="text-sm text-light-1000 dark:text-dark-1000">{{ label.name }}</span>
    </div>
  </div>

  <!-- Members Submenu -->
  <div
    v-if="contextSubmenu === 'members'"
    class="fixed z-[201] min-w-[180px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-2 shadow-lg"
    :style="{ left: (contextMenu?.x || 0) + 210 + 'px', top: (contextMenu?.y || 0) + 'px' }"
    @click.stop
  >
    <div v-for="member in board?.workspace?.members" :key="member.publicId" class="flex items-center gap-2 px-3 py-1.5">
      <input
        type="checkbox"
        :checked="contextMenuCard?.members?.some((m: any) => m.publicId === member.publicId)"
        @change="toggleCardMember(contextMenuCard?.publicId, member.publicId)"
        class="h-3.5 w-3.5 rounded border-light-400"
      />
      <span class="text-sm text-light-1000 dark:text-dark-1000">{{ member.user?.name || member.email }}</span>
    </div>
  </div>

  <!-- Due Date Submenu -->
  <div
    v-if="contextSubmenu === 'dueDate'"
    class="fixed z-[201] min-w-[180px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg"
    :style="{ left: (contextMenu?.x || 0) + 210 + 'px', top: (contextMenu?.y || 0) + 'px' }"
    @click.stop
  >
    <input type="date" @change="setCardDueDate(contextMenuCard?.publicId, ($event.target as HTMLInputElement).value)" class="mx-2 my-1 input text-sm" />
    <button @click="setCardDueDate(contextMenuCard?.publicId, null)" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-400">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
      Remove due date
    </button>
  </div>

  <!-- Delete Board Confirmation -->
  <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteConfirm = false">
    <div class="bg-light-50 dark:bg-dark-200 rounded-lg shadow-xl p-6 w-96">
      <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-2">Delete board</h3>
      <p class="text-sm text-light-700 dark:text-dark-700 mb-4">Are you sure you want to delete "{{ board?.name }}"? This action cannot be undone.</p>
      <div class="flex justify-end gap-2">
        <Button variant="secondary" size="sm" @click="showDeleteConfirm = false">Cancel</Button>
        <Button size="sm" variant="danger" @click="deleteBoard" :loading="isDeleting">Delete</Button>
      </div>
    </div>
  </div>

  <!-- NewCardModal -->
  <NewCardModal
    v-if="showNewCardModal"
    :board="board"
    :list-public-id="newCardListPublicId"
    @close="showNewCardModal = false"
    @created="loadBoard"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { boardService } from '@/services/board.service'
import { listService } from '@/services/list.service'
import { cardService } from '@/services/card.service'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import CardItem from '@/components/card/CardItem.vue'
import NewCardModal from '@/components/card/NewCardModal.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import draggable from 'vuedraggable'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const board = ref<any>(null)
const isLoading = ref(true)
const boardError = ref('')
const showNewCardModal = ref(false)
const newCardListPublicId = ref('')
const showNewListForm = ref(false)
const newListName = ref('')
const isCreatingList = ref(false)
const showDeleteConfirm = ref(false)
const isDeleting = ref(false)

// Dropdowns
const showBoardDropdown = ref(false)
const showVisibilityDropdown = ref(false)
const showFilterDropdown = ref(false)

// Card Context Menu
interface ContextMenuState {
  x: number
  y: number
}
const contextMenu = ref<ContextMenuState | null>(null)
const contextMenuCard = ref<any>(null)
const contextSubmenu = ref<string | null>(null)

// Filters
const filterLabels = ref<string[]>([])
const filterMembers = ref<string[]>([])
const filterLists = ref<string[]>([])
const filterDueDate = ref<string | null>(null)
const expandedFilterCategory = ref<string | null>(null)

const dueDateOptions = [
  { label: 'Overdue', value: 'overdue' },
  { label: 'Due today', value: 'today' },
  { label: 'Due tomorrow', value: 'tomorrow' },
  { label: 'Due this week', value: 'week' },
  { label: 'Due next week', value: 'nextWeek' },
  { label: 'No due date', value: 'none' }
]

const activeFilterCount = computed(() => {
  let count = 0
  if (filterLabels.value.length) count++
  if (filterMembers.value.length) count++
  if (filterLists.value.length) count++
  if (filterDueDate.value) count++
  return count
})

function expandFilterCategory(category: string) {
  expandedFilterCategory.value = expandedFilterCategory.value === category ? null : category
}

function isDateInRange(dateStr: string | null, range: string): boolean {
  if (!dateStr) return range === 'none'
  if (range === 'none') return false

  const date = new Date(dateStr)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  const endOfWeek = new Date(today)
  endOfWeek.setDate(today.getDate() + (7 - today.getDay()))
  const endOfNextWeek = new Date(endOfWeek)
  endOfNextWeek.setDate(endOfWeek.getDate() + 7)

  switch (range) {
    case 'overdue': return date < today
    case 'today': return date >= today && date < tomorrow
    case 'tomorrow': return date >= tomorrow && date < new Date(tomorrow.getTime() + 86400000)
    case 'week': return date >= today && date <= endOfWeek
    case 'nextWeek': return date > endOfWeek && date <= endOfNextWeek
    default: return true
  }
}

function matchesFilters(card: any): boolean {
  if (filterLabels.value.length > 0) {
    const cardLabelIds = card.labels?.map((l: any) => l.publicId) || []
    if (!filterLabels.value.some((id) => cardLabelIds.includes(id))) return false
  }
  if (filterMembers.value.length > 0) {
    const cardMemberIds = card.members?.map((m: any) => m.publicId) || []
    if (!filterMembers.value.some((id) => cardMemberIds.includes(id))) return false
  }
  if (filterDueDate.value) {
    if (!isDateInRange(card.dueDate, filterDueDate.value)) return false
  }
  return true
}

function filteredCards(cards: any[], listPublicId: string) {
  if (filterLists.value.length > 0 && !filterLists.value.includes(listPublicId)) return []
  if (activeFilterCount.value === 0) return cards
  return cards.filter(matchesFilters)
}

function toggleFilterLabel(publicId: string) {
  const idx = filterLabels.value.indexOf(publicId)
  if (idx >= 0) filterLabels.value.splice(idx, 1)
  else filterLabels.value.push(publicId)
}

function toggleFilterMember(publicId: string) {
  const idx = filterMembers.value.indexOf(publicId)
  if (idx >= 0) filterMembers.value.splice(idx, 1)
  else filterMembers.value.push(publicId)
}

function toggleFilterList(publicId: string) {
  const idx = filterLists.value.indexOf(publicId)
  if (idx >= 0) filterLists.value.splice(idx, 1)
  else filterLists.value.push(publicId)
}

function toggleFilterDueDate(value: string) {
  if (filterDueDate.value === value) filterDueDate.value = null
  else filterDueDate.value = value
}

function clearFilters() {
  filterLabels.value = []
  filterMembers.value = []
  filterLists.value = []
  filterDueDate.value = null
}

// Drag-and-drop handlers
async function onListReorder() {
  if (!board.value) return
  const listIds = board.value.lists.map((l: any) => l.publicId)
  try {
    await listService.reorder(board.value.publicId, listIds)
  } catch {
    toast.error('Failed to reorder lists')
    await loadBoard()
  }
}

async function onCardChange(change: any, list: any) {
  if (!board.value) return

  if (change.added) {
    const card = change.added.element
    const newIndex = change.added.newIndex
    try {
      await cardService.move(card.publicId, { listPublicId: list.publicId, index: newIndex })
    } catch {
      toast.error('Failed to move card')
      await loadBoard()
    }
  } else if (change.moved) {
    const card = change.moved.element
    const newIndex = change.moved.newIndex
    try {
      await cardService.move(card.publicId, { listPublicId: list.publicId, index: newIndex })
    } catch {
      toast.error('Failed to reorder card')
      await loadBoard()
    }
  }
}

// Board actions
async function toggleVisibility(visibility: string) {
  try {
    await boardService.update(board.value.publicId, { visibility })
    showVisibilityDropdown.value = false
    await loadBoard()
    toast.success(`Board set to ${visibility}`)
  } catch (error: any) {
    toast.error('Failed to update visibility')
  }
}

async function copyBoardLink() {
  const url = window.location.href
  try {
    await navigator.clipboard.writeText(url)
    showBoardDropdown.value = false
    toast.success('Board link copied')
  } catch {
    toast.error('Failed to copy link')
  }
}

async function deleteBoard() {
  isDeleting.value = true
  try {
    await boardService.delete(board.value.publicId)
    showDeleteConfirm.value = false
    toast.success('Board deleted')
    router.push(`/${route.params.workspaceSlug}`)
  } catch (error: any) {
    toast.error('Failed to delete board')
  } finally {
    isDeleting.value = false
  }
}

// Card Context Menu
function openContextMenu(e: MouseEvent, card: any) {
  contextMenu.value = { x: e.clientX, y: e.clientY }
  contextMenuCard.value = card
  contextSubmenu.value = null
}

function closeContextMenu() {
  contextMenu.value = null
  contextMenuCard.value = null
  contextSubmenu.value = null
}

async function contextMenuAction(action: string) {
  if (!contextMenuCard.value) return

  switch (action) {
    case 'copyLink': {
      const url = `${window.location.origin}/${route.params.workspaceSlug}/${route.params.boardSlug}/cards/${contextMenuCard.value.publicId}`
      try {
        await navigator.clipboard.writeText(url)
        toast.success('Card link copied')
      } catch {
        toast.error('Failed to copy link')
      }
      closeContextMenu()
      break
    }
    case 'duplicate': {
      try {
        await cardService.duplicate(contextMenuCard.value.publicId)
        await loadBoard()
        toast.success('Card duplicated')
      } catch {
        toast.error('Failed to duplicate card')
      }
      closeContextMenu()
      break
    }
    case 'delete': {
      try {
        await cardService.delete(contextMenuCard.value.publicId)
        await loadBoard()
        toast.success('Card deleted')
      } catch {
        toast.error('Failed to delete card')
      }
      closeContextMenu()
      break
    }
    case 'move':
      contextSubmenu.value = 'move'
      break
    case 'labels':
      contextSubmenu.value = 'labels'
      break
    case 'members':
      contextSubmenu.value = 'members'
      break
    case 'dueDate':
      contextSubmenu.value = 'dueDate'
      break
  }
}

async function moveCardTo(cardPublicId: string | undefined, listPublicId: string) {
  if (!cardPublicId) return
  try {
    await cardService.move(cardPublicId, { listPublicId, index: 0 })
    await loadBoard()
    toast.success('Card moved')
  } catch {
    toast.error('Failed to move card')
  }
  closeContextMenu()
}

async function toggleCardLabel(cardPublicId: string | undefined, labelPublicId: string) {
  if (!cardPublicId) return
  try {
    await cardService.toggleLabel(cardPublicId, labelPublicId)
    await loadBoard()
  } catch {
    toast.error('Failed to update label')
  }
}

async function toggleCardMember(cardPublicId: string | undefined, memberPublicId: string) {
  if (!cardPublicId) return
  try {
    await cardService.toggleMember(cardPublicId, memberPublicId)
    await loadBoard()
  } catch {
    toast.error('Failed to update member')
  }
}

async function setCardDueDate(cardPublicId: string | undefined, dueDate: string | null) {
  if (!cardPublicId) return
  try {
    await cardService.update(cardPublicId, { dueDate: dueDate ? new Date(dueDate).toISOString() : null })
    await loadBoard()
    toast.success(dueDate ? 'Due date set' : 'Due date removed')
  } catch {
    toast.error('Failed to update due date')
  }
}

// Board loading
async function loadBoard() {
  isLoading.value = true
  boardError.value = ''
  try {
    const response = await boardService.getById(route.params.boardSlug as string)
    board.value = response.data
  } catch {
    boardError.value = 'Failed to load board. Please try again.'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadBoard)

watch(() => route.params.boardSlug, () => {
  isLoading.value = true
  loadBoard()
})

// Close dropdowns/context menu on outside click
function handleClick() {
  if (showBoardDropdown.value) showBoardDropdown.value = false
  if (showVisibilityDropdown.value) showVisibilityDropdown.value = false
  if (showFilterDropdown.value) showFilterDropdown.value = false
  if (contextMenu.value && !contextSubmenu.value) closeContextMenu()
}

onMounted(() => document.addEventListener('click', handleClick))
onUnmounted(() => document.removeEventListener('click', handleClick))

function openNewCardModal(listPublicId: string) {
  newCardListPublicId.value = listPublicId
  showNewCardModal.value = true
}

function openCard(card: any) {
  router.push(`/${route.params.workspaceSlug}/${route.params.boardSlug}/cards/${card.publicId}`)
}

async function updateListName(list: any, newName: string) {
  if (!newName.trim() || newName === list.name) return
  try {
    await listService.update(list.publicId, { name: newName.trim() })
    await loadBoard()
  } catch (error: any) {
    toast.error('Failed to update list name')
  }
}

async function deleteList(listPublicId: string) {
  if (!confirm('Delete this list and all its cards?')) return
  try {
    await listService.delete(listPublicId)
    await loadBoard()
    toast.success('List deleted')
  } catch {
    toast.error('Failed to delete list')
  }
}

async function createList() {
  if (!newListName.value.trim()) return

  isCreatingList.value = true
  try {
    await listService.create({
      boardPublicId: board.value.publicId,
      name: newListName.value.trim()
    })
    newListName.value = ''
    showNewListForm.value = false
    await loadBoard()
    toast.success('List created')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to create list')
  } finally {
    isCreatingList.value = false
  }
}
</script>
