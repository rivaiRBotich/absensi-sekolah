<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Overlay gelap saat sidebar mobile terbuka -->
    <div
      v-if="isSidebarOpen"
      @click="isSidebarOpen = false"
      class="fixed inset-0 bg-black/50 z-40 md:hidden"
    ></div>

    <!-- Sidebar Left -->
    <aside
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
      class="w-64 bg-slate-900 text-white flex flex-col justify-between shrink-0 fixed md:static inset-y-0 left-0 z-50 transition-transform duration-200 ease-in-out"
    >
      <div>
        <div class="p-6 border-b border-slate-800 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg font-bold text-xl">S</div>
            <div>
              <h1 class="font-bold text-sm tracking-wide">PORTAL GURU</h1>
              <p class="text-xs text-slate-400">Sistem Absensi Sekolah</p>
            </div>
          </div>
          <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-white text-xl leading-none">
            &times;
          </button>
        </div>

        <nav class="p-4 space-y-1 text-sm font-medium">
          <NuxtLink
            to="/guru"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path === '/guru' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            📋 <span>Rekap Absensi</span>
          </NuxtLink>
        </nav>
      </div>

      <div class="p-4 border-t border-slate-800">
        <button
          @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600/10 text-red-400 hover:bg-red-600 hover:text-white rounded-xl text-sm font-medium transition-colors"
        >
          🚪 <span>Keluar App</span>
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-gray-100 px-4 md:px-8 py-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <button @click="isSidebarOpen = true" class="md:hidden text-gray-600 hover:text-gray-900 text-2xl leading-none shrink-0">
            ☰
          </button>
          <div class="min-w-0">
            <h2 class="text-lg md:text-xl font-bold text-gray-800 truncate">Rekap Absensi</h2>
            <p class="text-xs text-gray-500 hidden sm:block">Pantau kehadiran siswa seluruh kelas hari ini</p>
          </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-gray-800">{{ userName }}</p>
            <p class="text-xs text-gray-500">Guru</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">
            {{ userInitial }}
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-4 md:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const route = useRoute()
const token = useCookie('token')
const userCookie = useCookie('user')

const isSidebarOpen = ref(false)

const userName = computed(() => userCookie.value?.name || 'Guru')
const userInitial = computed(() => (userCookie.value?.name || 'G').charAt(0).toUpperCase())

const handleLogout = async () => {
  if (!confirm('Apakah Anda yakin ingin keluar?')) return
  token.value = null
  userCookie.value = null
  await navigateTo('/login')
}
</script>