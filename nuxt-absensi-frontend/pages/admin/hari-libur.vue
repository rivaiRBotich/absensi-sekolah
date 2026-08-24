<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Kalender Hari Libur</h1>
      <p class="text-sm text-gray-500 mt-1">Tanggal yang terdaftar di sini otomatis dilewati oleh sistem absensi (tidak ada siswa yang ditandai Alfa).</p>
    </div>

    <!-- Form Tambah -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <h3 class="text-sm font-semibold text-gray-700 mb-3">Tambah Hari Libur</h3>
      <form @submit.prevent="submitHoliday" class="flex flex-col sm:flex-row gap-3 sm:items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
          <input v-model="form.tanggal" type="date" required class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Keterangan (opsional)</label>
          <input v-model="form.keterangan" type="text" placeholder="Misal: Hari Kemerdekaan RI" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
        </div>
        <button type="submit" :disabled="submitting" class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white text-sm font-semibold px-5 py-2.5 rounded-lg whitespace-nowrap">
          {{ submitting ? 'Menyimpan...' : '➕ Tambah' }}
        </button>
      </form>
      <p v-if="formError" class="text-xs text-red-600 mt-2">{{ formError }}</p>
    </div>

    <!-- Daftar Hari Libur -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Daftar Hari Libur Terdaftar</h2>
      </div>
      <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 font-medium border-b border-gray-100">
          <tr>
            <th class="px-6 py-3">Tanggal</th>
            <th class="px-6 py-3">Keterangan</th>
            <th class="px-6 py-3">Sumber</th>
            <th class="px-6 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading"><td colspan="4" class="px-6 py-6 text-center text-gray-400">Memuat data...</td></tr>
          <tr v-else-if="holidays.length === 0"><td colspan="4" class="px-6 py-6 text-center text-gray-400">Belum ada hari libur terdaftar.</td></tr>
          <tr v-else v-for="h in holidays" :key="h.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-800">{{ formatTanggal(h.tanggal) }}</td>
            <td class="px-6 py-4">{{ h.keterangan || '-' }}</td>
            <td class="px-6 py-4">
              <span :class="h.tenant_id ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'" class="text-xs px-2.5 py-1 rounded-full font-medium">
                {{ h.tenant_id ? 'Sekolah Ini' : 'Libur Nasional' }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <button v-if="h.tenant_id" @click="deleteHoliday(h.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button>
              <span v-else class="text-xs text-gray-400">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({ layout: 'admin' })
const config = useRuntimeConfig()

const token = useCookie('token')
const holidays = ref([])
const loading = ref(false)
const submitting = ref(false)
const formError = ref('')

const form = ref({ tanggal: '', keterangan: '' })

const formatTanggal = (dateStr) => {
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

const fetchHolidays = async () => {
  loading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/holidays`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    holidays.value = res
  } catch (err) {
    console.error('Gagal memuat data hari libur:', err)
  } finally {
    loading.value = false
  }
}

const submitHoliday = async () => {
  submitting.value = true
  formError.value = ''
  try {
    await $fetch(`${config.public.apiBase}/admin/holidays`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: form.value
    })
    form.value = { tanggal: '', keterangan: '' }
    await fetchHolidays()
  } catch (err) {
    formError.value = err.data?.message || 'Gagal menyimpan hari libur.'
  } finally {
    submitting.value = false
  }
}

const deleteHoliday = async (id) => {
  if (!confirm('Hapus hari libur ini?')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/holidays/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchHolidays()
  } catch (err) {
    alert('Gagal menghapus hari libur.')
  }
}

onMounted(() => fetchHolidays())
</script>