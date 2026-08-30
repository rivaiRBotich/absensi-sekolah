// export default defineNuxtRouteMiddleware((to) => {
//   const token = useCookie('token')

//   // Halaman yang tidak butuh login
//   const publicPages = ['/login']

//   if (!token.value && !publicPages.includes(to.path)) {
//     return navigateTo('/login')
//   }

//   // 2. Sudah login tapi buka /login -> lempar ke dashboard sesuai role
//   if (token.value && to.path === '/login') {
//     return navigateTo(getDashboardByRole(user.value?.roles))
//   }

// })
interface AuthUser {
  id: number
  name: string
  email: string
  role: string
  roles: string[]
  tenant_id: number | null
}

function getDashboardByRole(roles: string[] = []) {
  if (roles.includes('super-admin')) return '/superadmin/dashboard'
  if (roles.includes('school-admin')) return '/admin'
  if (roles.includes('school-guru') || roles.includes('homeroom-teacher')) return '/guru'
  return '/absen'
}

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('token')
  const user = useCookie('user')

  // Halaman yang tidak butuh login
  const publicPages = ['/login']

  // 1. Belum login -> lempar ke /login
  if (!token.value && !publicPages.includes(to.path)) {
    return navigateTo('/login')
  }

  // 2. Sudah login tapi buka /login -> lempar ke dashboard sesuai role
  if (token.value && to.path === '/login') {
    return navigateTo(getDashboardByRole(user.value?.roles))
  }

  // 3. Sudah login -> cek role vs halaman yang diakses
  if (token.value && user.value) {
    const roles = user.value.roles || []

    const isSuperAdmin = roles.includes('super-admin')
    const isSchoolAdmin = roles.includes('school-admin')
    const isGuru = roles.includes('school-guru') || roles.includes('homeroom-teacher')

    if (to.path.startsWith('/superadmin') && !isSuperAdmin) {
      return navigateTo(getDashboardByRole(roles))
    }

    if (to.path.startsWith('/admin') && !isSchoolAdmin) {
      return navigateTo(getDashboardByRole(roles))
    }

    if (to.path.startsWith('/guru') && !isGuru) {
      return navigateTo(getDashboardByRole(roles))
    }
  }
})
