<template>
  <div class="space-y-6">
    <!-- Header & Action Button -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Tabel Manajemen Kelas & Wali Kelas</h2>
        <p class="text-xs text-gray-500">Atur penamaan rombel dan penugasan guru sebagai wali kelas</p>
      </div>
      <button 
        @click="openModalCreate" 
        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 shadow-sm transition"
      >
        ➕ <span>Tambah Kelas</span>
      </button>
    </div>

    <!-- Tabel Kelas & Wali Kelas -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 w-16 text-center">No</th>
            <th class="px-6 py-3">Nama Kelas</th>
            <th class="px-6 py-3">Wali Kelas</th>
            <th class="px-6 py-3">Jumlah Siswa</th>
            <th class="px-6 py-3 text-center w-36">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="5" class="px-6 py-6 text-center text-gray-500">Memuat data kelas...</td>
          </tr>
          <tr v-else-if="classes.length === 0">
            <td colspan="5" class="px-6 py-6 text-center text-gray-400">Belum ada kelas yang dibuat.</td>
          </tr>
          <tr v-else v-for="(kelas, index) in classes" :key="kelas.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-center font-medium text-gray-500">{{ index + 1 }}</td>
            <td class="px-6 py-4 font-bold text-gray-800">{{ kelas.name }}</td>
            <td class="px-6 py-4">
              <div v-if="kelas.homeroom_teacher" class="flex items-center gap-2">
                <span class="text-gray-800 font-medium">{{ kelas.homeroom_teacher.name }}</span>
                <span class="text-xs text-gray-400 font-mono">({{ kelas.homeroom_teacher.nip }})</span>
              </div>
              <span v-else class="text-xs text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md font-medium">
                Belum ditentukan
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">
                {{ kelas.students_count || 0 }} Siswa
              </span>
            </td>
            <td class="px-6 py-4 text-center space-x-2">
              <button 
                @click="openModalEdit(kelas)" 
                class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded transition"
              >
                Edit
              </button>
              <button 
                @click="deleteClass(kelas.id)" 
                class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded transition"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-xl">
        <h3 class="text-lg font-bold text-gray-800">
          {{ isEditing ? 'Edit Data Kelas & Wali' : 'Tambah Kelas Baru' }}
        </h3>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Kelas</label>
            <input 
              v-model="form.name" 
              type="text" 
              required 
              class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500" 
              placeholder="Contoh: X RPL 1, XII IPA 2" 
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Wali Kelas (Opsional)</label>
            <select 
              v-model="form.teacher_id" 
              class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
            >
              <option :value="null">-- Belum Ada Wali Kelas --</option>
              <option v-for="guru in teachers" :key="guru.id" :value="guru.id">
                {{ guru.name }} ({{ guru.nip }})
              </option>
            </select>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button 
              type="button" 
              @click="showModal = false" 
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="submitting" 
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition"
            >
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({ layout: 'admin' })

const config = useRuntimeConfig()
const token = useCookie('token')
const classes = ref([])
const teachers = ref([])
const loading = ref(false)
const submitting = ref(false)

const showModal = ref(false)
const isEditing = ref(false)
const currentEditId = ref(null)

const form = ref({
  name: '',
  teacher_id: null
})

// Ambil Data Kelas
const fetchClasses = async () => {
  loading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/classes`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    classes.value = res
  } catch (err) {
    alert('Gagal mengambil data kelas.')
  } finally {
    loading.value = false
  }
}

// Ambil Data Guru untuk Dropdown Wali Kelas
const fetchTeachers = async () => {
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/teachers`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    teachers.value = res
  } catch (err) {}
}

const openModalCreate = () => {
  isEditing.value = false
  currentEditId.value = null
  form.value = { name: '', teacher_id: null }
  showModal.value = true
}

const openModalEdit = (kelas) => {
  isEditing.value = true
  currentEditId.value = kelas.id
  form.value = {
    name: kelas.name,
    teacher_id: kelas.teacher_id || null
  }
  showModal.value = true
}

const submitForm = async () => {
  submitting.value = true
  try {
    const url = isEditing.value 
      ? `${config.public.apiBase}/admin/classes/${currentEditId.value}`
      : `${config.public.apiBase}/admin/classes`
    
    const method = isEditing.value ? 'PUT' : 'POST'

    await $fetch(url, {
      method,
      headers: { Authorization: `Bearer ${token.value}` },
      body: form.value
    })

    alert(isEditing.value ? 'Data kelas berhasil diperbarui!' : 'Kelas berhasil ditambahkan!')
    showModal.value = false
    await fetchClasses()
  } catch (err) {
    alert(err.data?.message || 'Terjadi kesalahan saat menyimpan data.')
  } finally {
    submitting.value = false
  }
}

const deleteClass = async (id) => {
  if (!confirm('Hapus kelas ini? Siswa di dalam kelas ini akan ikut terhapus.')) return
  try {
    // await $fetch(`http://localhost:8000/api/admin/classes/${id}`, {
    await $fetch(`${config.public.apiBase}/admin/classes/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchClasses()
  } catch (err) {
    alert('Gagal menghapus kelas.')
  }
}

onMounted(() => {
  fetchClasses()
  fetchTeachers()
})
</script>