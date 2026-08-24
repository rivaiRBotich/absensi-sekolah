<template>
  <div class="space-y-6">
    <!-- Header Informasi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Absensi Face Recognition Otomatis</h1>
        <p class="text-sm text-gray-500">Sistem otomatis mendeteksi dan mencocokkan wajah siswa dari kamera sekolah.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Kolom Kiri: Kamera & Tombol Scan -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center">
          <h3 class="font-bold text-gray-800 text-sm">Kamera Utama Absensi</h3>
          <span class="text-xs px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full font-medium">
            {{ cameraActive ? '🟢 Kamera Aktif' : '🔴 Kamera Standby' }}
          </span>
        </div>

        <!-- Video / Webcam Container -->
        <div class="relative w-full aspect-video bg-slate-900 rounded-xl overflow-hidden flex items-center justify-center border border-gray-300">
          <video ref="videoElement" autoplay playsinline class="w-full h-full object-cover"></video>
          <canvas ref="canvasElement" class="hidden"></canvas>

          <!-- Placeholder jika kamera mati -->
          <div v-if="!cameraActive" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 bg-slate-900/90 p-4 text-center">
            <span class="text-4xl mb-2">📷</span>
            <p class="text-sm font-medium">Kamera belum diaktifkan.</p>
            <p class="text-xs text-slate-500 mt-1">Klik tombol di bawah untuk menyalakan kamera.</p>
          </div>
        </div>

        <!-- Tombol Kontrol Kamera -->
        <div class="flex gap-3 pt-2">
          <button 
            v-if="!cameraActive" 
            @click="startCamera" 
            class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg w-full transition-colors shadow-sm"
          >
            🚀 Nyalakan Kamera Absensi
          </button>
          <button 
            v-else 
            @click="scanAndVerify" 
            :disabled="loadingVerify"
            class="px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white text-sm font-semibold rounded-lg w-full transition-colors flex items-center justify-center gap-2 shadow-sm"
          >
            <span v-if="loadingVerify" class="animate-spin">⏳</span>
            <span>{{ loadingVerify ? 'Memproses Liveness & AI...' : '📸 Scan & Absen Otomatis' }}</span>
          </button>
          <button 
            v-if="cameraActive" 
            @click="stopCamera" 
            class="px-4 py-3 bg-red-100 hover:bg-red-200 text-red-600 text-sm font-semibold rounded-lg transition-colors"
          >
            Matikan
          </button>
        </div>
      </div>

      <!-- Kolom Kanan: Hasil Deteksi Siswa -->
      <div class="space-y-6">
        <!-- Hasil Siswa yang Dikenali -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
          <h3 class="font-bold text-gray-800 text-sm">Informasi Siswa Terdeteksi</h3>
          
          <div class="w-full aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center">
            <img 
              v-if="detectedStudent?.face_image_path" 
              :src="`${config.public.apiBase}/storage/${detectedStudent.face_image_path}`" 
              alt="Foto Siswa Terdeteksi" 
              class="w-full h-full object-cover" 
            />
            <div v-else class="text-center p-4 text-gray-400 text-xs">
              <span>👤</span>
              <p class="mt-1">Belum ada siswa yang terdeteksi.</p>
            </div>
          </div>

          <div class="text-sm space-y-1 bg-gray-50 p-3 rounded-lg border border-gray-100">
            <p><strong class="text-gray-500">Nama:</strong> {{ detectedStudent?.name || '-' }}</p>
            <p><strong class="text-gray-500">NISN:</strong> {{ detectedStudent?.nisn || '-' }}</p>
            <p><strong class="text-gray-500">Gender:</strong> {{ detectedStudent?.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}</p>
          </div>
        </div>

        <!-- Status / Notifikasi Sistem -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
          <h3 class="font-bold text-gray-800 text-sm">Status Log Absensi</h3>
          
          <div v-if="verificationResult" :class="['p-4 rounded-xl border text-sm', verificationResult.verified ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700']">
            <p class="font-bold text-base mb-1">{{ verificationResult.verified ? '✅ Berhasil Tercatat' : '❌ Gagal Terdeteksi' }}</p>
            <p class="text-xs opacity-90">{{ verificationResult.message }}</p>
          </div>
          <div v-else class="p-4 rounded-xl border border-dashed border-gray-200 text-center text-xs text-gray-400">
            Sistem siaga. Silakan arahkan wajah ke kamera lalu klik scan.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'

definePageMeta({ layout: 'admin' })
const config = useRuntimeConfig()

const token = useCookie('token')
const cameraActive = ref(false)
const videoElement = ref(null)
const canvasElement = ref(null)
let mediaStream = null

const loadingVerify = ref(false)
const verificationResult = ref(null)
const detectedStudent = ref(null)

// Nyalakan Webcam
const startCamera = async () => {
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
    if (videoElement.value) {
      videoElement.value.srcObject = mediaStream
      cameraActive.value = true
    }
  } catch (err) {
    alert('Gagal mengakses kamera browser. Pastikan izin kamera aktif.')
    console.error(err)
  }
}

// Matikan Webcam
const stopCamera = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop())
    mediaStream = null
  }
  cameraActive.value = false
  detectedStudent.value = ref(null)
}

// Proses Tangkap Foto & Kirim ke Laravel
const scanAndVerify = async () => {
  if (!videoElement.value || !canvasElement.value) return

  const video = videoElement.value
  const canvas = canvasElement.value
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)

  canvas.toBlob(async (blob) => {
    if (!blob) return alert('Gagal mengambil tangkapan layar kamera.')

    loadingVerify.value = true
    verificationResult.value = null
    detectedStudent.value = null

    try {
      const formData = new FormData()
      formData.append('tenant_id', 1) // Ubah sesuai ID tenant sekolah yang aktif
      formData.append('captured_image', blob, 'captured.jpg')

      // Request langsung ke API Laravel yang sudah diatur di Controller
      const res = await $fetch(`${config.public.apiBase}/admin/attendance/face-automatic`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}` },
        body: formData
      })

      verificationResult.value = {
        verified: true,
        message: res.message
      }
      detectedStudent.value = res.student

    } catch (err) {
      console.error(err)
      verificationResult.value = {
        verified: false,
        message: err.data?.message || 'Wajah tidak dikenali atau gagal terhubung ke server.'
      }
    } finally {
      loadingVerify.value = false
    }
  }, 'image/jpeg', 0.95)
}

onUnmounted(() => {
  stopCamera()
})
</script>