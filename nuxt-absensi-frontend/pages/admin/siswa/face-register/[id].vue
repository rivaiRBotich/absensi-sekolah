<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Pendaftaran Wajah Siswa</h1>
        <p class="text-sm text-gray-500" v-if="student">Siswa: <span class="font-semibold text-gray-700">{{ student.name }}</span> (NISN: {{ student.nisn }})</p>
      </div>
      <NuxtLink to="/admin/students" class="text-sm text-blue-600 hover:underline">← Kembali</NuxtLink>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
      <!-- Area Kamera / Preview -->
      <div class="relative w-full aspect-video bg-black rounded-lg overflow-hidden flex items-center justify-center">
        <video v-show="!capturedImage" ref="videoRef" autoplay playsinline class="w-full h-full object-cover"></video>
        <canvas v-show="false" ref="canvasRef"></canvas>
        
        <!-- Hasil Capture Preview -->
        <img v-if="capturedImage" :src="capturedImage" class="w-full h-full object-cover" alt="Captured Face" />
        
        <div v-if="!cameraActive && !capturedImage" class="absolute text-white text-sm">
          Memuat kamera...
        </div>
      </div>

      <!-- Tombol Kontrol Kamera -->
      <div class="flex justify-center gap-4">
        <button v-if="!capturedImage" @click="takeSnapshot" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
          📸 Ambil Foto Wajah
        </button>
        <template v-else>
          <button @click="retakePhoto" type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
            🔄 Ulangi Foto
          </button>
          <button @click="uploadFace" :disabled="loading" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
            {{ loading ? 'Menyimpan...' : '💾 Simpan & Daftarkan' }}
          </button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

definePageMeta({ layout: 'admin' })

const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()
const token = useCookie('token')

const studentId = route.params.id
const student = ref(null)

const videoRef = ref(null)
const canvasRef = ref(null)
const cameraActive = ref(false)
const capturedImage = ref(null)
const imageBlob = ref(null)
const loading = ref(false)

let mediaStream = null

// Ambil detail siswa
const fetchStudentDetail = async () => {
  try {
    const res = await $fetch(`${config.public.apiBase}/admin/students`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    student.value = res.find(s => s.id == studentId)
  } catch (err) {
    console.error('Gagal mengambil data siswa')
  }
}

// Aktifkan Webcam
const startCamera = async () => {
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
    if (videoRef.value) {
      videoRef.value.srcObject = mediaStream
      cameraActive.value = true
    }
  } catch (err) {
    alert('Tidak dapat mengakses kamera. Pastikan izin kamera diaktifkan pada browser.')
  }
}

// Ambil Screenshot dari Video
const takeSnapshot = () => {
  const video = videoRef.value
  const canvas = canvasRef.value
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)

  capturedImage.value = canvas.toDataURL('image/jpeg')
  
  canvas.toBlob((blob) => {
    imageBlob.value = blob
  }, 'image/jpeg', 0.95)
}

// Ulangi Foto
const retakePhoto = () => {
  capturedImage.value = null
  imageBlob.value = null
}

// Kirim ke Laravel Backend
const uploadFace = async () => {
  if (!imageBlob.value) return

  loading.value = true
  const formData = new FormData()
  formData.append('face_image', imageBlob.value, 'face.jpg')

  try {
    await $fetch(`${config.public.apiBase}/admin/siswa/${studentId}/face`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData
    })
    alert('Wajah berhasil didaftarkan!')
    router.push('/admin/siswa')
  } catch (err) {
    alert(err.data?.message || 'Gagal mendaftarkan wajah.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStudentDetail()
  startCamera()
})

onUnmounted(() => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop())
  }
})
</script>
