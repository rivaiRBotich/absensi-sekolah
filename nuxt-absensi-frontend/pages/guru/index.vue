<template>
  <div class="space-y-6">
    <!-- Tab Switcher -->
    <div class="flex gap-2 bg-white p-1.5 rounded-xl border border-gray-100 shadow-sm w-fit">
      <button
        @click="activeTab = 'harian'"
        :class="activeTab === 'harian' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
      >
        📅 Rekap Harian
      </button>
      <button
        v-if="isWaliKelas"
        @click="switchToSummaryTab"
        :class="activeTab === 'bulanan' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50'"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
      >
        📊 Rekap Per Siswa (Wali Kelas)
      </button>
    </div>

    <!-- ============ TAB 1: REKAP HARIAN (semua kelas, seperti sebelumnya) ============ -->
    <template v-if="activeTab === 'harian'">
      <!-- Ringkasan Cepat -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
          <p class="text-2xl font-bold text-gray-800">{{ dataAbsensi.length }}</p>
          <p class="text-xs text-gray-500 mt-1">Total Data</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
          <p class="text-2xl font-bold text-green-600">{{ countByStatus('hadir') }}</p>
          <p class="text-xs text-gray-500 mt-1">Hadir</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
          <p class="text-2xl font-bold text-orange-500">{{ countByStatus('telat') }}</p>
          <p class="text-xs text-gray-500 mt-1">Telat</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
          <p class="text-2xl font-bold text-yellow-500">{{ countByStatus('izin') }}</p>
          <p class="text-xs text-gray-500 mt-1">Izin</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
          <p class="text-2xl font-bold text-yellow-500">{{ countByStatus('sakit') }}</p>
          <p class="text-xs text-gray-500 mt-1">Sakit</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
          <p class="text-2xl font-bold text-red-600">{{ countByStatus('alpha') }}</p>
          <p class="text-xs text-gray-500 mt-1">Alpha</p>
        </div>
      </div>

      <!-- CARD FILTER KELAS -->
      <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Filter Kelas</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
          <div
            @click="selectedClassId = ''"
            :class="['p-3 rounded-xl border cursor-pointer transition-all shadow-sm text-center', !selectedClassId ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300']"
          >
            <span class="text-sm font-semibold">Semua Kelas</span>
          </div>
          <div
            v-for="kelas in listKelas"
            :key="kelas.id"
            @click="selectedClassId = kelas.id"
            :class="['p-3 rounded-xl border cursor-pointer transition-all shadow-sm text-center', selectedClassId === kelas.id ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300']"
          >
            <span class="text-sm font-semibold truncate">{{ kelas.name }}</span>
          </div>
        </div>
      </div>

      <!-- FILTER BAR -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
            <input v-model="filterTanggal" type="date" :max="todayDate" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
            <select v-model="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
              <option value="">Semua Status</option>
              <option value="hadir">Hadir</option>
              <option value="telat">Telat</option>
              <option value="izin">Izin</option>
              <option value="sakit">Sakit</option>
              <option value="alpha">Alpha</option>
            </select>
          </div>
          <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Cari Nama / NISN</label>
            <input v-model="searchQuery" @keyup.enter="fetchAttendanceList" type="text" placeholder="Ketik nama atau NISN..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
          </div>
          <button @click="fetchAttendanceList" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg whitespace-nowrap">
            🔍 Terapkan Filter
          </button>
        </div>
      </div>

      <!-- TABEL -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-800">Detail Absensi — {{ formatTanggalLabel(filterTanggal) }}</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-medium border-b border-gray-100">
              <tr>
                <th class="px-6 py-3">Nama Siswa</th>
                <th class="px-6 py-3">Kelas</th>
                <th class="px-6 py-3">Masuk</th>
                <th class="px-6 py-3">Pulang</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="loading">
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Memuat data...</td>
              </tr>
              <tr v-else-if="dataAbsensi.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada data absensi pada filter ini.</td>
              </tr>
              <tr v-else v-for="siswa in dataAbsensi" :key="siswa.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium text-gray-800">{{ siswa.nama }}</td>
                <td class="px-6 py-4">{{ siswa.kelas }}</td>
                <td class="px-6 py-4">{{ siswa.waktu_masuk }}</td>
                <td class="px-6 py-4">{{ siswa.waktu_pulang }}</td>
                <td class="px-6 py-4">
                  <span :class="statusBadgeClass(siswa.status)" class="px-3 py-1 rounded-full text-xs font-semibold capitalize">
                    {{ siswa.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <button @click="openEditModal(siswa)" class="text-blue-600 hover:text-blue-900 text-xs font-semibold bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">
                    ✏️ Sunting
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ============ TAB 2: REKAP PER SISWA (khusus wali kelas) ============ -->
    <template v-if="activeTab === 'bulanan' && isWaliKelas">
      <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl p-6 text-white shadow-md">
        <h1 class="text-xl font-bold">Rekap Per Siswa — Kelas {{ waliKelasClassName }}</h1>
        <p class="text-sm text-blue-100 mt-1">Total kehadiran, telat, izin, sakit, dan alpha tiap siswa di kelas yang kamu wali-i.</p>
      </div>

      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex flex-wrap gap-3 items-end">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Periode</label>
            <select v-model="summaryPeriod" @change="fetchSummary" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
              <option value="week">Mingguan</option>
              <option value="month">Bulanan</option>
              <option value="6months">6 Bulan Terakhir</option>
            </select>
          </div>
          <button @click="exportSummary('excel')" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
            📊 Export Excel
          </button>
          <button @click="exportSummary('pdf')" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
            📄 Export PDF
          </button>
          <p v-if="summaryPeriode" class="text-xs text-gray-500 ml-auto">
            Periode: {{ formatTanggal(summaryPeriode.dari) }} — {{ formatTanggal(summaryPeriode.sampai) }}
          </p>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-gray-600">
          <thead class="bg-gray-50 text-gray-700 font-medium border-b border-gray-100">
            <tr>
              <th class="px-6 py-3">Nama Siswa</th>
              <th class="px-6 py-3 text-center">Hadir</th>
              <th class="px-6 py-3 text-center">Telat</th>
              <th class="px-6 py-3 text-center">Izin</th>
              <th class="px-6 py-3 text-center">Sakit</th>
              <th class="px-6 py-3 text-center">Alpha</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loadingSummary"><td colspan="6" class="px-6 py-8 text-center text-gray-400">Memuat data...</td></tr>
            <tr v-else-if="summaryData.length === 0"><td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada data pada periode ini.</td></tr>
            <tr v-else v-for="s in summaryData" :key="s.siswa_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium text-gray-800">{{ s.nama }}</td>
              <td class="px-6 py-4 text-center text-green-600 font-semibold">{{ s.hadir }}</td>
              <td class="px-6 py-4 text-center text-orange-500 font-semibold">{{ s.telat }}</td>
              <td class="px-6 py-4 text-center text-yellow-600 font-semibold">{{ s.izin }}</td>
              <td class="px-6 py-4 text-center text-yellow-600 font-semibold">{{ s.sakit }}</td>
              <td class="px-6 py-4 text-center text-red-600 font-semibold">{{ s.alpha }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- MODAL SUNTING STATUS (dipakai tab Harian) -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-sm w-full space-y-4 shadow-xl">
        <div>
          <h3 class="text-lg font-bold text-gray-800">Sunting Status Absensi</h3>
          <p class="text-xs text-gray-500 mt-1">{{ editForm.nama }} — {{ editForm.kelas }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold mb-1.5">Status Kehadiran</label>
          <select v-model="editForm.status" class="w-full border rounded-lg p-2.5 text-sm">
            <option value="hadir">Hadir</option>
            <option value="telat">Telat</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
            <option value="alpha">Alpha</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold mb-1.5">Catatan (opsional)</label>
          <textarea v-model="editForm.note" rows="3" placeholder="Misal: ada surat izin dari orang tua" class="w-full border rounded-lg p-2.5 text-sm resize-none"></textarea>
        </div>

        <div v-if="editFeedback" :class="['text-xs px-3 py-2 rounded-lg', editFeedback.success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">
          {{ editFeedback.message }}
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="showEditModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
          <button @click="submitEditStatus" :disabled="savingStatus" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white rounded-lg text-sm">
            {{ savingStatus ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'

definePageMeta({ layout: 'guru' })

const config = useRuntimeConfig()
const token = useCookie('token')
const userCookie = useCookie('user')
const todayDate = new Date().toISOString().slice(0, 10)

const isWaliKelas = computed(() => userCookie.value?.is_wali_kelas || false)
const waliKelasClassName = computed(() => userCookie.value?.wali_kelas_class_name || '-')

const activeTab = ref('harian')

// ===== State Tab Harian =====
const listKelas = ref([])
const selectedClassId = ref('')
const filterTanggal = ref(todayDate)
const filterStatus = ref('')
const searchQuery = ref('')
const dataAbsensi = ref([])
const loading = ref(false)

const showEditModal = ref(false)
const savingStatus = ref(false)
const editFeedback = ref(null)
const editForm = ref({ id: null, nama: '', kelas: '', status: 'hadir', note: '' })

const statusBadgeClass = (status) => {
  const map = {
    hadir: 'bg-green-100 text-green-700',
    telat: 'bg-orange-100 text-orange-700',
    izin: 'bg-yellow-100 text-yellow-700',
    sakit: 'bg-yellow-100 text-yellow-700',
    alpha: 'bg-red-100 text-red-700',
  }
  return map[status] || 'bg-gray-100 text-gray-700'
}

const formatTanggalLabel = (dateStr) => {
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

const formatTanggal = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const countByStatus = (status) => computed(() => dataAbsensi.value.filter(s => s.status === status).length).value

const fetchKelas = async () => {
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/classes`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    listKelas.value = res
  } catch (err) {
    console.error('Gagal memuat data kelas:', err)
  }
}

const fetchAttendanceList = async () => {
  loading.value = true
  try {
    const queryParams = new URLSearchParams()
    queryParams.append('tanggal', filterTanggal.value)
    if (selectedClassId.value) queryParams.append('class_id', selectedClassId.value)
    if (filterStatus.value) queryParams.append('status', filterStatus.value)
    if (searchQuery.value) queryParams.append('search', searchQuery.value)

    const res = await $fetch(`${config.public.apiBase}/admin/attendance?${queryParams.toString()}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    dataAbsensi.value = res
  } catch (err) {
    console.error('Gagal memuat data absensi:', err)
  } finally {
    loading.value = false
  }
}

watch(selectedClassId, () => fetchAttendanceList())
watch(filterStatus, () => fetchAttendanceList())
watch(filterTanggal, () => fetchAttendanceList())

const openEditModal = (siswa) => {
  editForm.value = {
    id: siswa.id,
    nama: siswa.nama,
    kelas: siswa.kelas,
    status: siswa.status,
    note: siswa.note || '',
  }
  editFeedback.value = null
  showEditModal.value = true
}

const submitEditStatus = async () => {
  savingStatus.value = true
  editFeedback.value = null
  try {
    await $fetch(`${config.public.apiBase}/admin/attendance/${editForm.value.id}`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { status: editForm.value.status, note: editForm.value.note }
    })
    editFeedback.value = { success: true, message: 'Status berhasil diperbarui.' }
    await fetchAttendanceList()
    setTimeout(() => { showEditModal.value = false }, 600)
  } catch (err) {
    editFeedback.value = { success: false, message: err.data?.message || 'Gagal menyimpan perubahan status.' }
  } finally {
    savingStatus.value = false
  }
}

// ===== State Tab Rekap Per Siswa (wali kelas) =====
const summaryPeriod = ref('month')
const summaryData = ref([])
const summaryPeriode = ref(null)
const loadingSummary = ref(false)

const fetchSummary = async () => {
  loadingSummary.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/attendance/summary?period=${summaryPeriod.value}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    summaryData.value = res.data
    summaryPeriode.value = res.periode
  } catch (err) {
    console.error('Gagal memuat rekap per siswa:', err)
  } finally {
    loadingSummary.value = false
  }
}

const exportSummary = async (type) => {
  try {
    const url = `${config.public.apiBase}/admin/attendance/summary/export/${type}?period=${summaryPeriod.value}`
    const blob = await $fetch(url, {
      headers: { Authorization: `Bearer ${token.value}` },
      responseType: 'blob'
    })
    const blobUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = blobUrl
    link.download = `rekap-siswa-${waliKelasClassName.value}.${type === 'excel' ? 'xlsx' : 'pdf'}`
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(blobUrl)
  } catch (err) {
    alert('Gagal mengekspor data.')
    console.error(err)
  }
}

const switchToSummaryTab = () => {
  activeTab.value = 'bulanan'
  if (summaryData.value.length === 0) fetchSummary()
}

onMounted(() => {
  fetchKelas()
  fetchAttendanceList()
})
</script>