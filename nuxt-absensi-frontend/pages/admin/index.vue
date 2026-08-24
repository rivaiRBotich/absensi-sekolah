<template>
  <div class="space-y-6">
    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ stats.totalSiswa }}</h3>
        <p class="text-xs text-green-600 mt-2">↑ {{ stats.siswaBaru }} siswa baru bulan ini</p>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-sm text-gray-500 font-medium">Hadir Hari Ini</p>
        <h3 class="text-3xl font-bold text-green-600 mt-2">{{ stats.hadir }}</h3>
        <p class="text-xs text-gray-500 mt-2">{{ stats.persentaseHadir }}% Tingkat Kehadiran</p>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-sm text-gray-500 font-medium">Izin / Sakit</p>
        <h3 class="text-3xl font-bold text-yellow-500 mt-2">{{ stats.izin + stats.sakit }}</h3>
        <p class="text-xs text-gray-500 mt-2">Sakit: {{ stats.sakit }} | Izin: {{ stats.izin }}</p>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-sm text-gray-500 font-medium">Tanpa Keterangan (Alpha)</p>
        <h3 class="text-3xl font-bold text-red-600 mt-2">{{ stats.alpha }}</h3>
        <p class="text-xs text-red-500 mt-2">Perlu tindakan wali kelas</p>
      </div>
    </div>

    <!-- Tabel Aktivitas Absensi Terbaru -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
        <h2 class="text-base font-semibold text-gray-800">Aktivitas Absensi Terbaru (Hari Ini)</h2>
        <button @click="navigateTo('/admin/rekap-absensi')" class="text-sm text-blue-600 hover:underline">Lihat Semua</button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
          <thead class="bg-gray-50 text-gray-700 font-medium border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 whitespace-nowrap">Nama Siswa</th>
              <th class="px-6 py-3 whitespace-nowrap">Kelas</th>
              <th class="px-6 py-3 whitespace-nowrap">Waktu Masuk</th>
              <th class="px-6 py-3 whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="4" class="px-6 py-4 text-center text-gray-400">Memuat data...</td>
            </tr>
            <tr v-else-if="dataAbsensi.length === 0">
              <td colspan="4" class="px-6 py-4 text-center text-gray-400">Belum ada data absensi hari ini.</td>
            </tr>
            <tr v-else v-for="(siswa, index) in dataAbsensi" :key="index" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium text-gray-800">{{ siswa.nama }}</td>
              <td class="px-6 py-4">{{ siswa.kelas }}</td>
              <td class="px-6 py-4">{{ siswa.waktu }}</td>
              <td class="px-6 py-4">
                <span
                  :class="{
                    'bg-green-100 text-green-700': siswa.status === 'Hadir',
                    'bg-orange-100 text-orange-700': siswa.status === 'Telat',
                    'bg-yellow-100 text-yellow-700': siswa.status === 'Izin' || siswa.status === 'Sakit',
                    'bg-red-100 text-red-700': siswa.status === 'Alpha'
                  }"
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                >
                  {{ siswa.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: 'admin'
})

const config = useRuntimeConfig()
const token = useCookie('token')
const loading = ref(false)

const stats = ref({
  totalSiswa: 0,
  siswaBaru: 0,
  hadir: 0,
  persentaseHadir: 0,
  izin: 0,
  sakit: 0,
  alpha: 0
})

const dataAbsensi = ref([])

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/dashboard`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    stats.value = res.stats
    dataAbsensi.value = res.recent_attendances
  } catch (err) {
    console.error('Gagal memuat data dashboard:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>