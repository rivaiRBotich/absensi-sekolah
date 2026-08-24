<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
    <h1 class="text-2xl font-bold mb-4">Presensi Wajah Siswa</h1>
    
    <div class="relative bg-black rounded-lg overflow-hidden w-[400px] h-[300px]">
      <video ref="videoRef" autoplay playsinline class="w-full h-full object-cover"></video>
    </div>

    <button 
      @click="captureAndSubmit" 
      class="mt-4 px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
    >
      Ambil Foto & Absen
    </button>

    <p v-if="statusMessage" class="mt-4 text-sm font-medium" :class="isError ? 'text-red-500' : 'text-green-600'">
      {{ statusMessage }}
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const config = useRuntimeConfig()
const token = useCookie('token')
const videoRef = ref(null)
const statusMessage = ref('')
const isError = ref(false)

onMounted(async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true })
    if (videoRef.value) {
      videoRef.value.srcObject = stream
    }
  } catch (err) {
    statusMessage.value = "Gagal mengakses kamera"
    isError.value = true
  }
})

const captureAndSubmit = async () => {
  const canvas = document.createElement('canvas')
  canvas.width = videoRef.value.videoWidth
  canvas.height = videoRef.value.videoHeight
  const ctx = canvas.getContext('2d')
  ctx.drawImage(videoRef.value, 0, 0)

  canvas.toBlob(async (blob) => {
    const formData = new FormData()
    formData.append('tenant_id', '1') // Dynamic ID Sekolah
    formData.append('student_id', '10') // Selected Student ID
    formData.append('captured_image', blob, 'capture.jpg')

    statusMessage.value = "Memverifikasi..."
    isError.value = false

    try {
      const res = await fetch(`${config.public.apiBase}/Absen/face`, {
        method: 'POST',
        body: formData
      })
      const data = await res.json()
      
      if (!res.ok) throw new Error(data.message)
      
      statusMessage.value = data.message
    } catch (err) {
      isError.value = true
      statusMessage.value = err.message || "Gagal Absen"
    }
  }, 'image/jpeg')
}
</script>