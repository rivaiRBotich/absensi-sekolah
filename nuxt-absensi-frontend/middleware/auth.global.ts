export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('token')

  // Halaman yang tidak butuh login
  const publicPages = ['/login']

  if (!token.value && !publicPages.includes(to.path)) {
    return navigateTo('/login')
  }
})

