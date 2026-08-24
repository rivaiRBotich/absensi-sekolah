<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar Left -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between shrink-0 hidden md:flex">
      <div>
        <div class="p-6 border-b border-slate-800 flex items-center gap-3">
          <div class="bg-blue-600 p-2 rounded-lg font-bold text-xl">S</div>
          <div>
            <h1 class="font-bold text-sm tracking-wide">PORTAL GURU</h1>
            <p class="text-xs text-slate-400">Sistem Absensi Sekolah</p>
          </div>
        </div>

        <nav class="p-4 space-y-1 text-sm font-medium">
          <NuxtLink
            to="/guru"
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
      <header class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-800">Rekap Absensi</h2>
          <p class="text-xs text-gray-500">Pantau kehadiran siswa seluruh kelas hari ini</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-sm font-bold text-gray-800">{{ userName }}</p>
            <p class="text-xs text-gray-500">Guru</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
            {{ userInitial }}
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const route = useRoute()
const token = useCookie('token')
const userCookie = useCookie('user') // simpan object user (termasuk name) saat login

const userName = computed(() => userCookie.value?.name || 'Guru')
const userInitial = computed(() => (userCookie.value?.name || 'G').charAt(0).toUpperCase())

const handleLogout = async () => {
  if (!confirm('Apakah Anda yakin ingin keluar?')) return
  token.value = null
  userCookie.value = null
  await navigateTo('/login')
}
</script>