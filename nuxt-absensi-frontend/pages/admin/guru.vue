<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-lg font-bold text-gray-800">Daftar Guru</h2>
      <button @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2">
        ➕ <span>Tambah Guru</span>
      </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
          <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 whitespace-nowrap">NIP</th>
              <th class="px-6 py-3 whitespace-nowrap">Nama Guru</th>
              <th class="px-6 py-3 whitespace-nowrap">Email</th>
              <th class="px-6 py-3 whitespace-nowrap">Akun Login</th>
              <th class="px-6 py-3 text-center whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="5" class="px-6 py-4 text-center">Memuat data...</td>
            </tr>
            <tr v-else-if="teachers.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-400">Belum ada data guru.</td>
            </tr>
            <tr v-else v-for="guru in teachers" :key="guru.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-mono text-xs whitespace-nowrap">{{ guru.nip }}</td>
              <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">{{ guru.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ guru.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="guru.has_account ? 'bg-green-50 text-green-700 border-green-100' : 'bg-gray-50 text-gray-500 border-gray-200'"
                  class="text-xs px-2.5 py-1 rounded-full font-medium border"
                >
                  {{ guru.has_account ? 'Punya Akun' : 'Belum Ada Akun' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center space-x-2 whitespace-nowrap">
                <button @click="openEditModal(guru)" class="text-blue-600 hover:text-blue-900 text-xs font-semibold bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">
                  ✏️ Edit
                </button>
                <button @click="openPasswordModal(guru)" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                  🔑 {{ guru.has_account ? 'Ubah Password' : 'Buat Akun' }}
                </button>
                <button @click="deleteTeacher(guru.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tambah Guru -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Tambah Guru Baru</h3>
        <p class="text-xs text-gray-500 -mt-2">Akun login untuk masuk ke Portal Guru akan dibuat otomatis.</p>
        <form @submit.prevent="submitTeacher" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">NIP</label>
            <input v-model="form.nip" type="text" required class="w-full border rounded-lg p-2 text-sm" placeholder="NIP Guru" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap</label>
            <input v-model="form.name" type="text" required class="w-full border rounded-lg p-2 text-sm" placeholder="Nama beserta Gelar" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
            <input v-model="form.email" type="email" required class="w-full border rounded-lg p-2 text-sm" placeholder="email@sekolah.sch.id" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Password</label>
            <input v-model="form.password" type="password" required minlength="6" class="w-full border rounded-lg p-2 text-sm" placeholder="Minimal 6 karakter" />
          </div>
          <div v-if="formFeedback" :class="['text-xs px-3 py-2 rounded-lg', formFeedback.success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">
            {{ formFeedback.message }}
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
            <button type="submit" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Edit Guru -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Edit Data Guru</h3>
        <form @submit.prevent="submitEditTeacher" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">NIP</label>
            <input v-model="editForm.nip" type="text" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap</label>
            <input v-model="editForm.name" type="text" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
            <input v-model="editForm.email" type="email" required class="w-full border rounded-lg p-2 text-sm" />
          </div>
          <div v-if="editFeedback" :class="['text-xs px-3 py-2 rounded-lg', editFeedback.success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">
            {{ editFeedback.message }}
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
            <button type="submit" :disabled="savingEdit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
              {{ savingEdit ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Ubah / Buat Password -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-6 max-w-sm w-full space-y-4">
        <div>
          <h3 class="text-lg font-bold text-gray-800">{{ passwordForm.hasAccount ? 'Ubah Password' : 'Buat Akun Login' }}</h3>
          <p class="text-xs text-gray-500 mt-1">{{ passwordForm.name }}</p>
        </div>
        <form @submit.prevent="submitPassword" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Password Baru</label>
            <input v-model="passwordForm.password" type="password" required minlength="6" class="w-full border rounded-lg p-2 text-sm" placeholder="Minimal 6 karakter" />
          </div>
          <div v-if="passwordFeedback" :class="['text-xs px-3 py-2 rounded-lg', passwordFeedback.success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">
            {{ passwordFeedback.message }}
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showPasswordModal = false" class="px-4 py-2 border rounded-lg text-sm">Batal</button>
            <button type="submit" :disabled="savingPassword" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">
              {{ savingPassword ? 'Menyimpan...' : 'Simpan' }}
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
const teachers = ref([])
const loading = ref(false)

// Tambah
const showAddModal = ref(false)
const submitting = ref(false)
const formFeedback = ref(null)
const form = ref({ nip: '', name: '', email: '', password: '' })

// Edit
const showEditModal = ref(false)
const savingEdit = ref(false)
const editFeedback = ref(null)
const editForm = ref({ id: null, nip: '', name: '', email: '' })

// Password
const showPasswordModal = ref(false)
const savingPassword = ref(false)
const passwordFeedback = ref(null)
const passwordForm = ref({ id: null, name: '', hasAccount: false, password: '' })

const fetchTeachers = async () => {
  loading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/teachers`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    teachers.value = res
  } catch (err) {
    alert('Gagal mengambil data guru.')
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  form.value = { nip: '', name: '', email: '', password: '' }
  formFeedback.value = null
  showAddModal.value = true
}

const submitTeacher = async () => {
  submitting.value = true
  formFeedback.value = null
  try {
    await $fetch(`${config.public.apiBase}/admin/teachers`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: form.value
    })
    showAddModal.value = false
    await fetchTeachers()
  } catch (err) {
    formFeedback.value = { success: false, message: err.data?.message || 'Gagal menyimpan data guru.' }
  } finally {
    submitting.value = false
  }
}

const openEditModal = (guru) => {
  editForm.value = { id: guru.id, nip: guru.nip, name: guru.name, email: guru.email }
  editFeedback.value = null
  showEditModal.value = true
}

const submitEditTeacher = async () => {
  savingEdit.value = true
  editFeedback.value = null
  try {
    await $fetch(`${config.public.apiBase}/admin/teachers/${editForm.value.id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { nip: editForm.value.nip, name: editForm.value.name, email: editForm.value.email }
    })
    editFeedback.value = { success: true, message: 'Data guru berhasil diperbarui.' }
    await fetchTeachers()
    setTimeout(() => { showEditModal.value = false }, 500)
  } catch (err) {
    editFeedback.value = { success: false, message: err.data?.message || 'Gagal memperbarui data guru.' }
  } finally {
    savingEdit.value = false
  }
}

const openPasswordModal = (guru) => {
  passwordForm.value = { id: guru.id, name: guru.name, hasAccount: guru.has_account, password: '' }
  passwordFeedback.value = null
  showPasswordModal.value = true
}

const submitPassword = async () => {
  savingPassword.value = true
  passwordFeedback.value = null
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/teachers/${passwordForm.value.id}/reset-password`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { password: passwordForm.value.password }
    })
    passwordFeedback.value = { success: true, message: res.message }
    await fetchTeachers()
    setTimeout(() => { showPasswordModal.value = false }, 700)
  } catch (err) {
    passwordFeedback.value = { success: false, message: err.data?.message || 'Gagal menyimpan password.' }
  } finally {
    savingPassword.value = false
  }
}

const deleteTeacher = async (id) => {
  if (!confirm('Hapus data guru ini? Akun login guru ini (jika ada) juga akan ikut terhapus.')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/teachers/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchTeachers()
  } catch (err) {
    alert('Gagal menghapus data guru.')
  }
}

onMounted(() => fetchTeachers())
</script>