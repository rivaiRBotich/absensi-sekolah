<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-md">
      <h1 class="text-2xl font-bold">Rekap Absensi</h1>
      <p class="text-sm text-blue-100 mt-1">Pantau dan kelola riwayat kehadiran siswa berdasarkan kelas dan periode waktu.</p>
    </div>

    <!-- Ringkasan Cepat (sesuai filter aktif) -->
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
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
      <!-- Preset Cepat -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="preset in presetTanggal"
          :key="preset.label"
          @click="applyPreset(preset)"
          class="text-xs font-medium px-3 py-1.5 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors"
        >
          {{ preset.label }}
        </button>
      </div>

      <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
          <input v-model="dariTanggal" type="date" :max="todayDate" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
          <input v-model="sampaiTanggal" type="date" :max="todayDate" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
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
        <button @click="exportFile('excel')" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg whitespace-nowrap">
        📊 Export Excel
        </button>
        <button @click="exportFile('pdf')" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg whitespace-nowrap">
        📄 Export PDF
        </button>
      </div>
    </div>

    <!-- TABEL -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Detail Riwayat Absensi</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
          <thead class="bg-gray-50 text-gray-700 font-medium border-b border-gray-100">
            <tr>
              <th class="px-6 py-3">Tanggal</th>
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
              <td colspan="7" class="px-6 py-8 text-center text-gray-400">Memuat data...</td>
            </tr>
            <tr v-else-if="dataAbsensi.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-400">Tidak ada data absensi pada filter ini.</td>
            </tr>
            <tr v-else v-for="siswa in dataAbsensi" :key="siswa.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">{{ formatTanggal(siswa.tanggal) }}</td>
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

    <!-- MODAL SUNTING STATUS -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-sm w-full space-y-4 shadow-xl">
        <div>
          <h3 class="text-lg font-bold text-gray-800">Sunting Status Absensi</h3>
          <p class="text-xs text-gray-500 mt-1">{{ editForm.nama }} — {{ editForm.kelas }} · {{ formatTanggal(editForm.tanggal) }}</p>
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

definePageMeta({ layout: 'admin' })

const config = useRuntimeConfig()
const token = useCookie('token')
const todayDate = new Date().toISOString().slice(0, 10)

const listKelas = ref([])
const selectedClassId = ref('')
const dariTanggal = ref(todayDate)
const sampaiTanggal = ref(todayDate)
const filterStatus = ref('')
const searchQuery = ref('')

const dataAbsensi = ref([])
const loading = ref(false)

const showEditModal = ref(false)
const savingStatus = ref(false)
const editFeedback = ref(null)
const editForm = ref({ id: null, nama: '', kelas: '', tanggal: '', status: 'hadir', note: '' })

const presetTanggal = [
  { label: 'Hari Ini', range: 'today' },
  { label: '7 Hari Terakhir', range: '7days' },
  { label: 'Minggu Ini', range: 'week' },
  { label: 'Bulan Ini', range: 'month' },
]

const toDateStr = (d) => d.toISOString().slice(0, 10)

const applyPreset = (preset) => {
  const now = new Date()

  if (preset.range === 'today') {
    dariTanggal.value = todayDate
    sampaiTanggal.value = todayDate
  } else if (preset.range === '7days') {
    const start = new Date(now)
    start.setDate(start.getDate() - 6)
    dariTanggal.value = toDateStr(start)
    sampaiTanggal.value = todayDate
  } else if (preset.range === 'week') {
    const start = new Date(now)
    const day = start.getDay() === 0 ? 7 : start.getDay() // Senin = 1
    start.setDate(start.getDate() - (day - 1))
    dariTanggal.value = toDateStr(start)
    sampaiTanggal.value = todayDate
  } else if (preset.range === 'month') {
    const start = new Date(now.getFullYear(), now.getMonth(), 1)
    dariTanggal.value = toDateStr(start)
    sampaiTanggal.value = todayDate
  }

  fetchAttendanceList()
}

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
    queryParams.append('dari', dariTanggal.value)
    queryParams.append('sampai', sampaiTanggal.value)
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

const openEditModal = (siswa) => {
  editForm.value = {
    id: siswa.id,
    nama: siswa.nama,
    kelas: siswa.kelas,
    tanggal: siswa.tanggal,
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

const exportFile = async (type) => {
  try {
    const queryParams = new URLSearchParams()
    queryParams.append('dari', dariTanggal.value)
    queryParams.append('sampai', sampaiTanggal.value)
    if (selectedClassId.value) queryParams.append('class_id', selectedClassId.value)
    if (filterStatus.value) queryParams.append('status', filterStatus.value)
    if (searchQuery.value) queryParams.append('search', searchQuery.value)

    const url = `${config.public.apiBase}/admin/attendance/export/${type}?${queryParams.toString()}`

    const blob = await $fetch(url, {
      headers: { Authorization: `Bearer ${token.value}` },
      responseType: 'blob'
    })

    const blobUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = blobUrl
    link.download = `rekap-absensi-${dariTanggal.value}_${sampaiTanggal.value}.${type === 'excel' ? 'xlsx' : 'pdf'}`
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(blobUrl)
  } catch (err) {
    alert('Gagal mengekspor data.')
    console.error(err)
  }
}

onMounted(() => {
  fetchKelas()
  fetchAttendanceList()
})
</script>