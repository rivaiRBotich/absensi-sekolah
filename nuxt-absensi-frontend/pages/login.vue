<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Absensi Sekolah</h1>
        <p class="text-sm text-gray-500 mt-1">Silakan masuk ke akun Anda</p>
      </div>

      <!-- Alert Error jika Login Gagal -->
      <div 
        v-if="errorMessage" 
        class="mb-5 p-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg flex items-center justify-between"
      >
        <span>{{ errorMessage }}</span>
        <button @click="errorMessage = ''" class="font-bold text-red-700 hover:text-red-900">&times;</button>
      </div>

      <!-- Form Login -->
      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email / Username</label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="email@gmail.com"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input  
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          />
        </div>

        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center text-gray-600 cursor-pointer">
            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2" />
            Ingat Saya
          </label>
          <a href="#" class="text-blue-600 hover:underline">Lupa Password?</a>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition duration-200 disabled:opacity-50 flex items-center justify-center"
        >
          <span v-if="loading">Memproses...</span>
          <span v-else>Masuk</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false // Tidak menggunakan layout default
})
const config = useRuntimeConfig()  
const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  
  try {
    // 1. Tembak API Login ke Backend Laravel
    const response = await $fetch(`${config.public.apiBase}/login`, {
    //const response = await $fetch('https://chose-connector-likelihood-question.trycloudflare.com/api/login', {
      method: 'POST',
      body: {
        email: form.value.email,
        password: form.value.password
      }
    })

    // 2. Simpan Access Token & Data User ke Cookie Nuxt 3
    const tokenCookie = useCookie('token', { maxAge: 60 * 60 * 24 * 7 }) // Expire 7 hari
    const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7 })
    
    tokenCookie.value = response.access_token
    userCookie.value = response.user

    // 3. Ambil array role dari response Laravel (Spatie)
    const userRoles = response.user.roles || []

    // 4. Pengecekan Role & Redirect Dinamis
    if (userRoles.includes('super-admin')) {
      // Jika Super Admin -> Ke Dashboard Super Admin
      await navigateTo('/superadmin/dashboard')
    } else if (userRoles.includes('school-admin')) {
      // Jika Admin Sekolah -> Ke Dashboard Admin Sekolah
      await navigateTo('/admin')
    } else if (userRoles.includes('school-guru') || userRoles.includes('homeroom-teacher')) {
      // Jika Guru -> Ke Dashboard Guru
      await navigateTo('/guru')
    } else {
      // Fallback untuk role lain / siswa
      await navigateTo('/absen')
    }

  } catch (error) {
    // Tangkap error jika email/password salah atau server mati
    errorMessage.value = error.data?.message || 'Gagal masuk. Periksa kembali email dan password Anda.'
  } finally {
    loading.value = false
  }
}
</script>