<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Overlay saat sidebar mobile terbuka -->
    <div
      v-if="isSidebarOpen"
      @click="isSidebarOpen = false"
      class="fixed inset-0 bg-black/50 z-40 md:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
      class="w-64 bg-slate-900 text-white flex flex-col justify-between shrink-0 fixed md:static inset-y-0 left-0 z-50 transform transition-transform duration-300 ease-in-out md:translate-x-0"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div>
        <!-- Logo Header -->
        <div class="p-6 border-b border-slate-800 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg font-bold text-xl">S</div>
            <div>
              <h1 class="font-bold text-sm tracking-wide">PANEL ADMIN</h1>
              <p class="text-xs text-slate-400">Sistem Absensi Sekolah</p>
            </div>
          </div>
          <!-- Tombol tutup, hanya muncul di mobile -->
          <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
            ✕
          </button>
        </div>

        <!-- Menu Navigation -->
        <nav class="p-4 space-y-1 text-sm font-medium">
          <NuxtLink
            to="/admin"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path === '/admin' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            📊 <span>Dashboard</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/kelas"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/kelas') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            🏫 <span>Data Kelas & Wali</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/guru"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/guru') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            👨‍🏫 <span>Data Guru</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/siswa"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/siswa') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            🎓 <span>Data Siswa</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/absensi-kamera"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/absensi-kamera') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            📷 <span>Absensi Kamera</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/rekap-absensi"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/rekap-absensi') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            📋 <span>Rekap Absensi</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/pengaturan-jadwal"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/pengaturan-jadwal') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            ⏰ <span>Jadwal Sekolah</span>
          </NuxtLink>

          <NuxtLink
            to="/admin/hari-libur"
            @click="isSidebarOpen = false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors"
            :class="route.path.startsWith('/admin/hari-libur') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
          >
            🎌 <span>Hari Libur</span>
          </NuxtLink>
        </nav>
      </div>

      <!-- User Profile Footer & Logout -->
      <div class="p-4 border-t border-slate-800">
        <button
          @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600/10 text-red-400 hover:bg-red-600 hover:text-white rounded-xl text-sm font-medium transition-colors"
        >
          🚪 <span>Keluar App</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Topbar Header -->
      <header class="bg-white border-b border-gray-100 px-4 md:px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <!-- Tombol hamburger, hanya muncul di mobile -->
          <button
            @click="isSidebarOpen = true"
            class="md:hidden text-gray-600 hover:text-gray-900 text-2xl leading-none"
          >
            ☰
          </button>
          <div>
            <h2 class="text-lg md:text-xl font-bold text-gray-800">{{ pageTitle }}</h2>
            <p class="text-xs text-gray-500 hidden sm:block">Kelola dan pantau aktivitas sekolah dalam satu tempat</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-gray-800">Admin Sekolah</p>
            <p class="text-xs text-gray-500">Administrator</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">
            A
          </div>
        </div>
      </header>

      <!-- Page Slot Container -->
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

const isSidebarOpen = ref(false)

const pageTitle = computed(() => {
  if (route.path === '/admin') return 'Dashboard Utama'
  if (route.path.startsWith('/admin/kelas')) return 'Manajemen Kelas & Wali Kelas'
  if (route.path.startsWith('/admin/guru')) return 'Manajemen Data Guru'
  if (route.path.startsWith('/admin/siswa')) return 'Manajemen Data Siswa'
  if (route.path.startsWith('/admin/absensi-kamera')) return 'Live Face Recognition Absensi'
  if (route.path.startsWith('/admin/rekap-absensi')) return 'Rekap & Laporan Absensi'
  if (route.path.startsWith('/admin/pengaturan-jadwal')) return 'Pengaturan Jadwal Sekolah'
  if (route.path.startsWith('/admin/hari-libur')) return 'Kalender Hari Libur'
  return 'Panel Administrator'
})

const handleLogout = async () => {
  if (!confirm('Apakah Anda yakin ingin keluar?')) return
  token.value = null
  await navigateTo('/login')
}
</script>