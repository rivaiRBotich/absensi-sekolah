<template>
  <div class="space-y-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 max-w-2xl">
      <h2 class="text-base font-semibold text-gray-800 mb-1">Hari Aktif Sekolah</h2>
      <p class="text-xs text-gray-500 mb-4">Pilih hari-hari sekolah aktif. Di luar hari ini, sistem tidak akan menandai siswa sebagai Alfa.</p>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <label
          v-for="hari in daftarHari"
          :key="hari.value"
          class="flex items-center gap-2 px-3 py-2.5 rounded-lg border cursor-pointer transition-colors"
          :class="form.hari_aktif.includes(hari.value)
            ? 'bg-blue-50 border-blue-300 text-blue-700'
            : 'bg-gray-50 border-gray-200 text-gray-500 hover:bg-gray-100'"
        >
          <input
            type="checkbox"
            :value="hari.value"
            v-model="form.hari_aktif"
            class="accent-blue-600"
          />
          <span class="text-sm font-medium">{{ hari.label }}</span>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jam Masuk</label>
          <input
            type="time"
            v-model="form.jam_masuk"
            class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jam Pulang</label>
          <input
            type="time"
            v-model="form.jam_pulang"
            class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div v-if="feedback" :class="['text-sm px-4 py-3 rounded-lg mb-4', feedback.success ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200']">
        {{ feedback.message }}
      </div>

      <button
        @click="saveSchedule"
        :disabled="saving || form.hari_aktif.length === 0"
        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white text-sm font-semibold rounded-lg transition-colors"
      >
        {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({ layout: 'admin' })

const config = useRuntimeConfig()
const token = useCookie('token')
const loading = ref(false)
const saving = ref(false)
const feedback = ref(null)

const daftarHari = [
  { value: 'senin', label: 'Senin' },
  { value: 'selasa', label: 'Selasa' },
  { value: 'rabu', label: 'Rabu' },
  { value: 'kamis', label: 'Kamis' },
  { value: 'jumat', label: "Jum'at" },
  { value: 'sabtu', label: 'Sabtu' },
  { value: 'minggu', label: 'Minggu' },
]

const form = ref({
  hari_aktif: [],
  jam_masuk: '07:00',
  jam_pulang: '15:00',
})

const fetchSchedule = async () => {
  loading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/settings/schedule`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })

    form.value.hari_aktif = res.hari_aktif ?? []
    // Backend simpan format 'HH:mm:ss', input time butuh 'HH:mm'
    form.value.jam_masuk = res.jam_masuk ? res.jam_masuk.slice(0, 5) : '07:00'
    form.value.jam_pulang = res.jam_pulang ? res.jam_pulang.slice(0, 5) : '15:00'
  } catch (err) {
    console.error('Gagal memuat pengaturan jadwal:', err)
  } finally {
    loading.value = false
  }
}

const saveSchedule = async () => {
  saving.value = true
  feedback.value = null

  try {
    // await $fetch('http://localhost:8000/api/admin/settings/schedule', {
    await $fetch(`${config.public.apiBase}/admin/settings/schedule`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: {
        hari_aktif: form.value.hari_aktif,
        jam_masuk: form.value.jam_masuk,
        jam_pulang: form.value.jam_pulang,
      }
    })

    feedback.value = { success: true, message: 'Pengaturan jadwal berhasil disimpan.' }
  } catch (err) {
    feedback.value = {
      success: false,
      message: err.data?.message || 'Gagal menyimpan pengaturan. Coba lagi.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchSchedule()
})
</script>