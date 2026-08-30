<template>
  <div class="space-y-6">
    <!-- Header & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Siswa</h1>
        <p class="text-sm text-gray-500">
          {{ selectedClassId ? 'Menampilkan siswa berdasarkan kelas terpilih' : 'Silakan pilih card kelas di bawah untuk melihat siswa' }}
        </p>
      </div>

      <div class="flex gap-2 w-full sm:w-auto justify-end">
        <button @click="showImportModal = true" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2.5 rounded-lg flex items-center gap-2">
          📥 <span>Import Excel</span>
        </button>
        <button @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2.5 rounded-lg flex items-center gap-2">
          ➕ <span>Tambah Siswa</span>
        </button>
      </div>
    </div>

    <!-- CARD KELAS (Pilih Kelas Terlebih Dahulu) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <div 
        @click="selectedClassId = ''"
        :class="['p-4 rounded-xl border cursor-pointer transition-all shadow-sm flex flex-col justify-between', !selectedClassId ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300']"
      >
        <span class="text-xs font-medium opacity-80">Semua Data</span>
        <h3 class="text-lg font-bold mt-2">Semua Kelas</h3>
      </div>

      <div 
        v-for="kelas in listKelas" 
        :key="kelas.id"
        @click="selectedClassId = kelas.id"
        :class="['p-4 rounded-xl border cursor-pointer transition-all shadow-sm flex flex-col justify-between', selectedClassId === kelas.id ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300']"
      >
        <span :class="['text-xs font-medium', selectedClassId === kelas.id ? 'text-blue-100' : 'text-gray-400']">Kelas</span>
        <h3 class="text-lg font-bold mt-2 truncate">{{ kelas.name }}</h3>
      </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="flex items-center gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
      <input 
        v-model="searchQuery" 
        @keyup.enter="fetchStudents" 
        type="text" 
        placeholder="Cari nama atau NISN siswa..." 
        class="bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5 w-full sm:w-80 focus:ring-blue-500 focus:border-blue-500" 
      />
      <button @click="fetchStudents" class="bg-gray-800 hover:bg-gray-900 text-white text-sm px-4 py-2.5 rounded-lg">Cari</button>
    </div>

    <!-- TABEL DATA SISWA -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <span class="font-semibold text-sm text-gray-700">Daftar Siswa Terdaftar</span>
        <span class="text-xs text-gray-500">Total: {{ students.length }} siswa</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
          <thead class="bg-gray-50/50 text-gray-700 font-semibold border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 whitespace-nowrap">NISN</th>
              <th class="px-6 py-3 whitespace-nowrap">Nama Siswa</th>
              <th class="px-6 py-3 whitespace-nowrap">Jenis Kelamin</th>
              <th class="px-6 py-3 whitespace-nowrap">Kelas</th>
              <th class="px-6 py-3 whitespace-nowrap">No. Orang Tua</th>
              <th class="px-6 py-3 text-center whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-if="loading"><td colspan="6" class="px-6 py-4 text-center text-gray-400">Memuat data siswa...</td></tr>
            <tr v-else-if="students.length === 0"><td colspan="6" class="px-6 py-4 text-center text-gray-400">Tidak ada siswa pada filter ini.</td></tr>
            <tr v-else v-for="siswa in students" :key="siswa.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-mono text-xs whitespace-nowrap">{{ siswa.nisn }}</td>
              <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">{{ siswa.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ siswa.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">
                  {{ siswa.school_class?.name || '-' }}
                </span>
              </td>
              <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">{{ siswa.parent_phone || '-' }}</td>
              <td class="px-6 py-4 text-center space-x-2 whitespace-nowrap">
                <!-- Tombol Edit -->
                <button 
                  @click="openEditModal(siswa)" 
                  class="text-blue-600 hover:text-blue-900 text-xs font-semibold bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100"
                >
                  ✏️ Edit
                </button>

                <!-- Tombol Lihat Foto (Hanya muncul jika sudah ada foto wajah) -->
                <button 
                  v-if="siswa.face_image_path" 
                  @click="openPreviewModal(siswa)" 
                  class="text-green-600 hover:text-green-900 text-xs font-semibold bg-green-50 px-3 py-1.5 rounded-lg border border-green-100"
                  title="Lihat Foto Wajah"
                >
                  👁️ Lihat Foto
                </button>

                <!-- Tombol Daftarkan Wajah -->
                <button @click="openFaceRegistration(siswa)" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                  📷 {{ siswa.face_image_path ? 'Ubah Wajah' : 'Daftarkan Wajah' }}
                </button>
                <!-- Tombol Hapus -->
                <button @click="deleteStudent(siswa.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL TAMBAH SISWA -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-xl">
        <h3 class="text-lg font-bold text-gray-800">Tambah Siswa Baru</h3>
        <form @submit.prevent="submitAddStudent" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold mb-1">NISN</label>
            <input v-model="studentForm.nisn" type="text" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Nama Lengkap</label>
            <input v-model="studentForm.name" type="text" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Jenis Kelamin</label>
            <select v-model="studentForm.gender" required class="w-full border rounded-lg p-2 text-sm">
              <option value="L">Laki-Laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Kelas</label>
            <select v-model="studentForm.class_id" required class="w-full border rounded-lg p-2 text-sm">
              <option value="">-- Pilih Kelas --</option>
              <option v-for="kelas in listKelas" :key="kelas.id" :value="kelas.id">{{ kelas.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Nomor Orang Tua (WhatsApp)</label>
            <input v-model="studentForm.parent_phone" type="text" placeholder="Contoh: 6281234567890" class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL EDIT SISWA -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-xl">
        <h3 class="text-lg font-bold text-gray-800">Edit Data Siswa</h3>
        <form @submit.prevent="submitEditStudent" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold mb-1">NISN</label>
            <input v-model="editForm.nisn" type="text" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Nama Lengkap</label>
            <input v-model="editForm.name" type="text" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Jenis Kelamin</label>
            <select v-model="editForm.gender" required class="w-full border rounded-lg p-2 text-sm">
              <option value="L">Laki-Laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Kelas</label>
            <select v-model="editForm.class_id" required class="w-full border rounded-lg p-2 text-sm">
              <option value="">-- Pilih Kelas --</option>
              <option v-for="kelas in listKelas" :key="kelas.id" :value="kelas.id">{{ kelas.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Nomor Orang Tua (WhatsApp)</label>
            <input v-model="editForm.parent_phone" type="text" placeholder="Contoh: 6281234567890" class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL IMPORT EXCEL -->
    <div v-if="showImportModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-xl">
        <h3 class="text-lg font-bold text-gray-800">Import Data Siswa (Excel)</h3>
        <p class="text-xs text-gray-500">Kolom header Excel wajib bertuliskan: <strong>nisn, nama, gender, class_id</strong></p>
        <input type="file" @change="e => fileExcel = e.target.files[0]" accept=".xlsx, .xls, .csv" class="w-full text-sm border p-2 rounded-lg" />
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="showImportModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
          <button type="button" @click="submitImport" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Upload & Import</button>
        </div>
      </div>
    </div>

    <!-- MODAL PREVIEW FOTO WAJAH -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-sm w-full space-y-4 shadow-xl text-center">
        <h3 class="text-lg font-bold text-gray-800">Foto Wajah Terdaftar</h3>
        <p class="text-xs text-gray-500">{{ activeStudent?.name }} (NISN: {{ activeStudent?.nisn }})</p>
        
        <div class="w-full aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center">
          <img 
            v-if="activeStudent?.face_image_path" 
            :src="`${config.public.apiBase}/storage/${activeStudent.face_image_path}`" 
            alt="Foto Wajah" 
            class="w-full h-full object-cover" 
          />
        </div>

        <div class="flex justify-center pt-2">
          <button @click="showPreviewModal = false" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm w-full">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

definePageMeta({ layout: 'admin' })
const config = useRuntimeConfig()
const token = useCookie('token')
const students = ref([])
const listKelas = ref([])
const loading = ref(false)
const selectedClassId = ref('')
const searchQuery = ref('')

const showAddModal = ref(false)
const showEditModal = ref(false)
const showImportModal = ref(false)
const showPreviewModal = ref(false)
const activeStudent = ref(null)
const fileExcel = ref(null)

const studentForm = ref({ nisn: '', name: '', gender: 'L', class_id: '', parent_phone: '' })
const editForm = ref({ id: null, nisn: '', name: '', gender: 'L', class_id: '', parent_phone: '' })

const fetchStudents = async () => {
  loading.value = true
  try {
    const queryParams = new URLSearchParams()
    if (selectedClassId.value) queryParams.append('class_id', selectedClassId.value)
    if (searchQuery.value) queryParams.append('search', searchQuery.value)

    const res = await $fetch(`${config.public.apiBase}/admin/students?${queryParams.toString()}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    students.value = res
  } catch (err) {
    console.error('Gagal mengambil data siswa:', err)
  } finally {
    loading.value = false
  }
}

const fetchKelas = async () => {
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/classes`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    listKelas.value = res
  } catch (err) {
    console.error('Gagal mengambil data kelas:', err)
  }
}

// Watcher agar data siswa otomatis ter-refresh saat card kelas diklik
watch(selectedClassId, () => {
  fetchStudents()
})

const openAddModal = () => {
  studentForm.value = { nisn: '', name: '', gender: 'L', class_id: '', parent_phone: '' }
  showAddModal.value = true
}

const submitAddStudent = async () => {
  try {
    await $fetch(`${config.public.apiBase}/admin/students`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: studentForm.value
    })
    alert('Siswa berhasil ditambahkan!')
    showAddModal.value = false
    studentForm.value = { nisn: '', name: '', gender: 'L', class_id: '', parent_phone: '' }
    await fetchStudents()
  } catch (err) {
    alert(err.data?.message || 'Gagal menambahkan siswa.')
  }
}

const openEditModal = (siswa) => {
  editForm.value = {
    id: siswa.id,
    nisn: siswa.nisn,
    name: siswa.name,
    gender: siswa.gender,
    class_id: siswa.class_id,
    parent_phone: siswa.parent_phone || '',
  }
  showEditModal.value = true
}

const submitEditStudent = async () => {
  try {
    await $fetch(`${config.public.apiBase}/admin/students/${editForm.value.id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${token.value}` },
      body: {
        nisn: editForm.value.nisn,
        name: editForm.value.name,
        gender: editForm.value.gender,
        class_id: editForm.value.class_id,
        parent_phone: editForm.value.parent_phone,
      }
    })
    alert('Data siswa berhasil diperbarui!')
    showEditModal.value = false
    await fetchStudents()
  } catch (err) {
    alert(err.data?.message || 'Gagal memperbarui data siswa.')
  }
}

const submitImport = async () => {
  if (!fileExcel.value) return alert('Pilih file terlebih dahulu')
  
  const formData = new FormData()
  formData.append('file', fileExcel.value)

  try {
    await $fetch(`${config.public.apiBase}/admin/students/import`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData
    })
    alert('Import data Excel berhasil!')
    showImportModal.value = false
    await fetchStudents()
  } catch (err) {
    alert('Gagal mengimpor file Excel.')
  }
}

const deleteStudent = async (id) => {
  if (!confirm('Hapus siswa ini?')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/students/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchStudents()
  } catch (err) {
    alert('Gagal menghapus siswa.')
  }
}

const openFaceRegistration = (siswa) => {
  navigateTo(`/admin/siswa/face-register/${siswa.id}`)
}

const openPreviewModal = (siswa) => {
  activeStudent.value = siswa
  showPreviewModal.value = true
}

onMounted(() => {
  fetchKelas()
  fetchStudents()
})
</script>
