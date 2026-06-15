export interface User {
  id: string
  employee_code: string | null
  first_name: string
  last_name: string
  email: string
  phone: string | null
  department_id: string | null
  shift_id: string | null
  designation: string | null
  avatar_path: string | null
  is_active: boolean
  last_login_at: string | null
  roles: Role[]
  permissions: string[]
}

export interface Role {
  id: number
  name: string
  display_name: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface AuthState {
  user: User | null
  token: string | null
  orgSlug: string | null
}
