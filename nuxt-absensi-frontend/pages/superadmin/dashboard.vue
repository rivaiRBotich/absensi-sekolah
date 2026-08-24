<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex">
    
    <!-- 1. SIDEBAR (Kiri) -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between shrink-0 min-h-screen sticky top-0 h-screen overflow-y-auto">
      <div>
        <!-- Brand Header -->
        <div class="p-6 flex items-center gap-3 border-b border-slate-800">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4m-4 0H9m4 0V5"/></svg>
          </div>
          <div>
            <h1 class="font-bold text-base tracking-tight leading-none text-slate-100">Absensi Sekolah</h1>
            <span class="text-[10px] uppercase font-bold text-indigo-400">Super Admin</span>
          </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="p-4 space-y-1.5">
          <button 
            @click="activeMenu = 'dashboard'" 
            :class="activeMenu === 'dashboard' ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Dashboard</span>
          </button>

          <button 
            @click="activeMenu = 'tenants'" 
            :class="activeMenu === 'tenants' ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4m-4 0H9m4 0V5"/></svg>
            <span>Kelola Sekolah</span>
          </button>

          <button 
            @click="activeMenu = 'revenue'" 
            :class="activeMenu === 'revenue' ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Pendapatan</span>
          </button>
        </nav>
      </div>

      <!-- User Profile & Logout -->
      <div class="p-4 border-t border-slate-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-slate-300">
              SA
            </div>
            <div class="text-left text-xs overflow-hidden">
              <p class="font-semibold text-slate-200 truncate">Super Admin</p>
              <p class="text-slate-500 truncate">admin@sekolah.app</p>
            </div>
          </div>
          <button @click="handleLogout" title="Logout" class="p-2 text-rose-400 hover:bg-rose-500/10 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- 2. MAIN CONTENT AREA (Kanan) -->
    <div class="flex-1 flex flex-col min-w-0">
      
      <!-- Top Navbar -->
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-20">
        <h2 class="font-bold text-lg text-slate-800">
          <span v-if="activeMenu === 'dashboard'">Dashboard Overview</span>
          <span v-else-if="activeMenu === 'tenants'">Kelola Sekolah (Tenants)</span>
          <span v-else-if="activeMenu === 'revenue'">Kalkulator Pendapatan</span>
        </h2>
        
        <button 
          v-if="activeMenu === 'tenants' || activeMenu === 'dashboard'"
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-600/20 flex items-center gap-2 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Tambah Sekolah Baru</span>
        </button>
      </header>

      <!-- Dynamic Page Body -->
      <main class="p-8 space-y-6">

        <!-- STATS KPI (Tampil di semua menu) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Total Sekolah</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ stats.total_tenants || 0 }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-600 uppercase">Sekolah Aktif</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ stats.active_tenants || 0 }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Mendekati Tempo</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ stats.due_soon_tenants || 0 }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-rose-600 uppercase">Suspended</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ stats.suspended_tenants || 0 }}</p>
          </div>
        </div>

        <!-- MENU 1: DASHBOARD OVERVIEW -->
        <div v-if="activeMenu === 'dashboard'" class="space-y-6">
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-2">Selamat Datang di System Control Super Admin</h3>
            <p class="text-sm text-slate-500">Pilih menu <span class="font-semibold text-blue-600">Kelola Sekolah</span> untuk manajemen lisensi & reset password, atau <span class="font-semibold text-blue-600">Pendapatan</span> untuk melihat proyeksi omzet berbasis jumlah siswa.</p>
          </div>
        </div>

        <!-- MENU 2: KELOLA SEKOLAH -->
        <div v-if="activeMenu === 'tenants' || activeMenu === 'dashboard'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          
          <!-- Filter Bar -->
          <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <input 
              v-model="searchQuery" 
              @input="fetchData"
              type="text" 
              placeholder="Cari nama sekolah / NPSN..." 
              class="w-full sm:w-80 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
            />
            <select 
              v-model="statusFilter" 
              @change="fetchData"
              class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700"
            >
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <!-- Table Sekolah -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200/70">
                  <th class="py-3.5 px-5">Sekolah</th>
                  <th class="py-3.5 px-5">Admin Sekolah</th>
                  <th class="py-3.5 px-5">Jatuh Tempo</th>
                  <th class="py-3.5 px-5">Siswa</th>
                  <th class="py-3.5 px-5">Status</th>
                  <th class="py-3.5 px-5 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm">
                <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-slate-50/70">
                  <td class="py-4 px-5 font-bold text-slate-900">
                    <div>{{ tenant.name }}</div>
                    <div class="text-xs text-slate-400 font-mono mt-0.5">NPSN: {{ tenant.npsn || '-' }}</div>
                  </td>
                  <td class="py-4 px-5">
                    <div v-if="tenant.users && tenant.users.length > 0">
                      <div class="text-slate-800 font-semibold">{{ tenant.users[0].name }}</div>
                      <div class="text-xs text-slate-400">{{ tenant.users[0].email }}</div>
                    </div>
                    <span v-else class="text-slate-400 text-xs italic">Belum ada admin</span>
                  </td>
                  <td class="py-4 px-5 text-slate-600 font-medium">{{ formatDate(tenant.due_date) }}</td>
                  <td class="py-4 px-5 font-semibold text-slate-700">{{ tenant.students_count || 0 }} Siswa</td>
                  <td class="py-4 px-5">
                    <span :class="tenant.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'" class="px-2.5 py-1 rounded-full text-xs font-bold border">
                      {{ tenant.status === 'active' ? 'Aktif' : 'Suspended' }}
                    </span>
                  </td>
                  
                  <!-- AKSI: Masa Aktif, Matikan, & Reset Password Admin -->
                  <td class="py-4 px-5 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                      <!-- 1. Perpanjang Masa Aktif -->
                      <button @click="openExtendModal(tenant)" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg border border-amber-200 text-xs font-semibold">
                        + Masa Aktif
                      </button>

                      <!-- 2. Matikan / Aktifkan -->
                      <button 
                        @click="toggleStatus(tenant.id)"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border"
                        :class="tenant.status === 'active' ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200'"
                      >
                        {{ tenant.status === 'active' ? 'Matikan' : 'Aktifkan' }}
                      </button>

                      <!-- 3. Reset Password Admin -->
                      <button 
                        v-if="tenant.users && tenant.users.length > 0"
                        @click="openResetPasswordModal(tenant)"
                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-300 text-xs font-semibold"
                        title="Reset Password Admin"
                      >
                        🔑 Reset Pass
                      </button>
                    </div>
                  </td>

                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- MENU 3: MENU PENDAPATAN (REVENUE CALCULATOR) -->
        <div v-if="activeMenu === 'revenue'" class="space-y-6">
          
          <!-- Kartu Pengaturan Tarif -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h3 class="text-base font-bold text-slate-900">Pengaturan Tarif Lisensi / Siswa</h3>
              <p class="text-xs text-slate-500 mt-1">Ubah patokan harga langganan bulanan yang dikenakan per siswa.</p>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-sm font-semibold text-slate-600">Tarif per Siswa:</span>
              <div class="flex items-center gap-1 bg-slate-100 px-3 py-2 rounded-xl border border-slate-200 font-bold">
                <span>Rp</span>
                <input v-model.number="pricePerStudent" type="number" step="100" class="w-24 bg-transparent outline-none font-bold text-blue-600" />
                <span class="text-xs text-slate-400 font-normal">/ Bln</span>
              </div>
              
              <!-- Tombol Simpan -->
              <button 
                @click="savePrice" 
                class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition"
              >
                Simpan Tarif
              </button>
            </div>
          </div> <!-- TAG PENUTUP DIV YANG SEBELUMNYA HILANG -->

          <!-- KPI Pendapatan -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-6 rounded-2xl shadow-lg shadow-blue-600/20">
              <span class="text-xs font-bold uppercase text-blue-200">Total Seluruh Siswa</span>
              <p class="text-3xl font-black mt-1">{{ totalAllStudents }} <span class="text-base font-normal">Siswa</span></p>
              <p class="text-xs text-blue-200 mt-2">Dari {{ stats.active_tenants || 0 }} sekolah aktif</p>
            </div>

            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-6 rounded-2xl shadow-lg shadow-emerald-600/20">
              <span class="text-xs font-bold uppercase text-emerald-200">Estimasi Pendapatan Bulanan</span>
              <p class="text-3xl font-black mt-1">Rp {{ formatRupiah(totalMonthlyRevenue) }}</p>
              <p class="text-xs text-emerald-200 mt-2">Perhitungan (Siswa × Rp {{ formatRupiah(pricePerStudent) }})</p>
            </div>

            <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white p-6 rounded-2xl shadow-lg shadow-amber-500/20">
              <span class="text-xs font-bold uppercase text-amber-100">Proyeksi Pendapatan Tahunan</span>
              <p class="text-3xl font-black mt-1">Rp {{ formatRupiah(totalMonthlyRevenue * 12) }}</p>
              <p class="text-xs text-amber-100 mt-2">Estimasi dalam 1 tahun penuh</p>
            </div>
          </div>

          <!-- Break Down Pendapatan per Sekolah -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h4 class="font-bold text-slate-800 mb-4">Rincian Omzet per Sekolah</h4>
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="text-slate-400 text-xs font-bold uppercase border-b border-slate-100">
                    <th class="py-3">Nama Sekolah</th>
                    <th class="py-3">Jumlah Siswa</th>
                    <th class="py-3">Tarif / Siswa</th>
                    <th class="py-3 text-right">Tagihan Bulanan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                  <tr v-for="t in tenants" :key="t.id">
                    <td class="py-3 font-semibold text-slate-800">{{ t.name }}</td>
                    <td class="py-3 font-medium text-slate-600">{{ t.students_count || 0 }} Siswa</td>
                    <td class="py-3 text-slate-500">Rp {{ formatRupiah(pricePerStudent) }}</td>
                    <td class="py-3 font-bold text-emerald-600 text-right">
                      Rp {{ formatRupiah((t.students_count || 0) * pricePerStudent) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </main>
    </div>

    <!-- MODAL 1: Tambah Sekolah -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
        <h3 class="text-base font-bold text-slate-900 mb-4">Daftarkan Sekolah Baru</h3>
        <form @submit.prevent="createTenant" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Sekolah</label>
            <input v-model="form.name" type="text" required placeholder="SMA Negeri 1 Contoh" class="w-full px-3.5 py-2 border rounded-xl text-sm" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">NPSN</label>
              <input v-model="form.npsn" type="text" placeholder="10293847" class="w-full px-3.5 py-2 border rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Jatuh Tempo Pertama</label>
              <input v-model="form.due_date" type="date" required class="w-full px-3.5 py-2 border rounded-xl text-sm" />
            </div>
          </div>
          <p class="text-xs font-bold text-blue-600 uppercase pt-2">Akun Admin Sekolah Pertama</p>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Admin</label>
            <input v-model="form.admin_name" type="text" required placeholder="Pak Budi" class="w-full px-3.5 py-2 border rounded-xl text-sm" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Admin</label>
              <input v-model="form.admin_email" type="email" required placeholder="admin@sekolah.sch.id" class="w-full px-3.5 py-2 border rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password</label>
              <input v-model="form.admin_password" type="password" required placeholder="••••••••" class="w-full px-3.5 py-2 border rounded-xl text-sm" />
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 border rounded-xl text-sm text-slate-600">Batal</button>
            <button type="submit" :disabled="loading" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold">Simpan Sekolah</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: Perpanjang Masa Aktif -->
    <div v-if="showExtendModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
        <h3 class="text-base font-bold text-slate-900 mb-2">Perpanjang Masa Aktif</h3>
        <p class="text-xs text-slate-500 mb-4">Sekolah: <span class="font-bold text-slate-800">{{ selectedTenant?.name }}</span></p>
        <form @submit.prevent="submitExtend">
          <select v-model="extendMonths" class="w-full px-3.5 py-2 border rounded-xl text-sm mb-6">
            <option :value="1">1 Bulan</option>
            <option :value="3">3 Bulan</option>
            <option :value="6">6 Bulan</option>
            <option :value="12">12 Bulan (1 Tahun)</option>
          </select>
          <div class="flex justify-end gap-2">
            <button type="button" @click="showExtendModal = false" class="px-4 py-2 border rounded-xl text-sm">Batal</button>
            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-sm font-semibold">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: Reset Password Admin -->
    <div v-if="showResetModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
        <h3 class="text-base font-bold text-slate-900 mb-1">Reset Password Admin</h3>
        <p class="text-xs text-slate-500 mb-4">Admin: <span class="font-bold text-slate-800">{{ selectedAdminUser?.name }}</span> ({{ selectedAdminUser?.email }})</p>
        
        <form @submit.prevent="submitResetPassword">
          <div class="mb-4">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password Baru</label>
            <input v-model="newPassword" type="password" required placeholder="Minimal 6 Karakter" class="w-full px-3.5 py-2 border rounded-xl text-sm" />
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="showResetModal = false" class="px-4 py-2 border rounded-xl text-sm">Batal</button>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-semibold">Reset Password</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
const config = useRuntimeConfig()
const activeMenu = ref('dashboard') // Menu Navigasi: 'dashboard', 'tenants', 'revenue'
const pricePerStudent = ref(2000) // Default Rp 2.000 per siswa (Dapat dirubah)

const tenants = ref([])
const stats = ref({})
const searchQuery = ref('')
const statusFilter = ref('')
const loading = ref(false)

const showCreateModal = ref(false)
const showExtendModal = ref(false)
const showResetModal = ref(false)

const selectedTenant = ref(null)
const selectedAdminUser = ref(null)
const newPassword = ref('')
const extendMonths = ref(1)

const form = ref({
  name: '',
  npsn: '',
  due_date: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0],
  admin_name: '',
  admin_email: '',
  admin_password: ''
})

const token = useCookie('token')

// Fetch Data Tenants & Stats dari Backend
const fetchData = async () => {
  try {
    const res = await $fetch(`${config.public.apiBase}/superadmin/tenants?search=${searchQuery.value}&status=${statusFilter.value}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    tenants.value = res.tenants
    stats.value = res.stats
  } catch (err) {
    console.error('Gagal mengambil data:', err)
  }
}

// Computeds untuk Pendapatan
const totalAllStudents = computed(() => {
  return tenants.value.reduce((acc, curr) => acc + (curr.students_count || 0), 0)
})

const totalMonthlyRevenue = computed(() => {
  return totalAllStudents.value * pricePerStudent.value
})

// Action Reset Password Admin
const openResetPasswordModal = (tenant) => {
  if (tenant.users && tenant.users.length > 0) {
    selectedAdminUser.value = tenant.users[0]
    newPassword.value = ''
    showResetModal.value = true
  }
}

const submitResetPassword = async () => {
  try {
    await $fetch(`${config.public.apiBase}/superadmin/users/${selectedAdminUser.value.id}/reset-password`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { password: newPassword.value }
    })
    alert(`Password untuk admin ${selectedAdminUser.value.name} berhasil direset!`)
    showResetModal.value = false
  } catch (err) {
    alert('Gagal mereset password admin.')
  }
}

// Action Logout
const handleLogout = async () => {
  if (!confirm('Apakah Anda yakin ingin keluar dari akun?')) return
  try {
    await $fetch(`${config.public.apiBase}/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` }
    }).catch(() => {})
  } finally {
    token.value = null
    navigateTo('/login')
  }
}

const createTenant = async () => {
  loading.value = true
  try {
    await $fetch(`${config.public.apiBase}/superadmin/tenants`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: form.value
    })
    showCreateModal.value = false
    await fetchData()
  } catch (err) {
    alert(err.data?.message || 'Gagal menambahkan sekolah baru')
  } finally {
    loading.value = false
  }
}

const toggleStatus = async (id) => {
  if (!confirm('Ubah status aktif/suspend sekolah ini?')) return
  try {
    await $fetch(`${config.public.apiBase}/superadmin/tenants/${id}/toggle`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchData()
  } catch (err) {
    alert('Gagal mengubah status sekolah')
  }
}

const openExtendModal = (tenant) => {
  selectedTenant.value = tenant
  showExtendModal.value = true
}

const submitExtend = async () => {
  try {
    await $fetch(`${config.public.apiBase}/superadmin/tenants/${selectedTenant.value.id}/extend`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { months: extendMonths.value }
    })
    showExtendModal.value = false
    await fetchData()
  } catch (err) {
    alert('Gagal memperpanjang masa aktif')
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatRupiah = (val) => {
    if (!val) return '0'
    return val.toLocaleString('id-ID')
}

// Fetch Tarif dari DB saat dipanggil
const fetchPrice = async () => {
  try {
    const res = await $fetch(`${config.public.apiBase}/superadmin/settings/price`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    pricePerStudent.value = res.price_per_student
  } catch (err) {
    console.error('Gagal mengambil tarif:', err)
  }
}

// Simpan Tarif Baru ke DB
const savePrice = async () => {
  loading.value = true
  try {
    await $fetch(`${config.public.apiBase}/superadmin/settings/price`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { price_per_student: pricePerStudent.value }
    })
    alert('Tarif berhasil disimpan ke database!')
  } catch (err) {
    alert('Gagal menyimpan tarif baru.')
  } finally {
    loading.value = false
  }
}
onMounted(() => {
    fetchData()
    fetchPrice()
})
</script>