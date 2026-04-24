import axios from 'axios'

const API_BASE = 'http://192.168.254.104/eurotaxisystem-main/api'

const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Attach token automatically
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token && config.headers) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
})

export async function login(identifier: string, password?: string, otp?: string, device_name = 'mobile') {
  const payload: any = { identifier, device_name }
  if (otp) payload.otp = otp
  if (password) payload.password = password
  const res = await api.post('/login', payload)
  return res.data
}

export async function registerAccount(data: {
  first_name: string
  middle_name?: string
  last_name: string
  suffix?: string
  phone_number: string
  email: string
  role: string
  password: string
  password_confirmation: string
  otp_method?: 'email' | 'sms'
}) {
  const res = await api.post('/register', data)
  return res.data
}

export async function verifyRegistrationOtp(email: string, otp: string) {
  const res = await api.post('/register/verify-otp', { email, otp })
  return res.data
}

export async function resendRegistrationOtp(email: string, otp_method: 'email' | 'sms' = 'email') {
  const res = await api.post('/register/resend-otp', { email, otp_method })
  return res.data
}

export async function logout() {
  const res = await api.post('/logout')
  localStorage.removeItem('token')
  return res.data
}

export async function getDashboard() {
  const res = await api.get('/dashboard')
  return res.data
}

export async function getIncomeDetails() {
  const res = await api.get('/dashboard/income-details')
  return res.data
}

export async function getDashboardMaintenance() {
  const res = await api.get('/dashboard/maintenance-units')
  return res.data
}

export async function getDashboardCoding() {
  const res = await api.get('/dashboard/coding-units')
  return res.data
}

export async function getDashboardDrivers() {
  const res = await api.get('/dashboard/active-drivers')
  return res.data
}

// Units
export interface Unit {
  id: number;
  unit_number: string;
  plate_number: string;
  make: string;
  model: string;
  year: number;
  color: string;
  status: string;
  unit_type: string;
  fuel_status: string;
  boundary_rate: number;
  purchase_cost: number;
  purchase_date: string;
  coding_day: string;
  gps_link: string;
  driver_id?: number | null;
  secondary_driver_id?: number | null;
  driver_name?: string;
  secondary_driver_name?: string;
}

export async function getUnits() {
  const res = await api.get('/units')
  return res.data
}
export async function getUnit(id: number) {
  const res = await api.get(`/units/${id}`)
  return res.data
}
export async function createUnit(data: any) {
  const res = await api.post('/units', data)
  return res.data
}
export async function updateUnit(id: number, data: any) {
  const res = await api.put(`/units/${id}`, data)
  return res.data
}
export async function deleteUnit(id: number) {
  const res = await api.delete(`/units/${id}`)
  return res.data
}

// Drivers
export interface Driver {
  id: number;
  full_name: string;
  email: string;
  username: string;
  contact_number: string;
  address: string;
  license_number: string;
  license_expiry: string;
  hire_date: string;
  daily_boundary_target: number;
  emergency_contact: string;
  emergency_phone: string;
  driver_status: string;
  driver_type: string;
  is_active: boolean;
  creator_name?: string;
  editor_name?: string;
  assigned_plate?: string;
}

export async function getDrivers() {
  const res = await api.get('/drivers')
  return res.data
}
export async function getDriver(id: number) {
  const res = await api.get(`/drivers/${id}`)
  return res.data
}
export async function createDriver(data: any) {
  const res = await api.post('/drivers', data)
  return res.data
}
export async function updateDriver(id: number, data: any) {
  const res = await api.put(`/drivers/${id}`, data)
  return res.data
}
export async function deleteDriver(id: number) {
  const res = await api.delete(`/drivers/${id}`)
  return res.data
}

// Analytics & Statistics
export async function getAnalytics(params?: any) { return (await api.get('/analytics', { params })).data }

// Franchise & Decision Management
export interface FranchiseCase {
  id: number;
  case_no: string;
  applicant_name: string;
  type_of_application: string;
  denomination: string;
  date_filed: string;
  expiry_date?: string;
  status: string;
  unit_count: number;
}

export async function getFranchises(params?: any) { return (await api.get('/franchises', { params })).data }
export async function getFranchise(id: number) { return (await api.get(`/franchises/${id}`)).data }
export async function createFranchise(data: any) { return (await api.post('/franchises', data)).data }
export async function updateFranchise(id: number, data: any) { return (await api.put(`/franchises/${id}`, data)).data }
export async function deleteFranchise(id: number) { return (await api.delete(`/franchises/${id}`)).data }
export async function approveFranchise(id: number) { return (await api.post(`/franchises/${id}/approve`)).data }
export async function rejectFranchise(id: number) { return (await api.post(`/franchises/${id}/reject`)).data }

// Staff Management
export interface StaffMember {
  id: string | number;
  name: string;
  role: string;
  phone?: string;
  status: string;
  type: 'admin' | 'general';
}

export async function getStaff(params?: any) { return (await api.get('/staff', { params })).data }
export async function getStaffRecord(id: string | number) { return (await api.get(`/staff/${id}`)).data }
export async function createStaff(data: any) { return (await api.post('/staff', data)).data }
export async function updateStaff(id: string | number, data: any) { return (await api.put(`/staff/${id}`, data)).data }
export async function deleteStaff(id: string | number) { return (await api.delete(`/staff/${id}`)).data }

// Archive & Restoration
export async function getArchive() { return (await api.get('/archive')).data }
export async function restoreArchive(type: string, id: number) { return (await api.post(`/archive/restore/${type}/${id}`)).data }
export async function deletePermanent(type: string, id: number) { return (await api.delete(`/archive/delete/${type}/${id}`)).data }

// Boundaries & Collection
export async function getBoundaries(month?: number, year?: number, search?: string) {
  return (await api.get('/boundaries', { params: { month, year, search } })).data
}
export async function getBoundary(id: number) { return (await api.get(`/boundaries/${id}`)).data }
export async function createBoundary(data: any) { return (await api.post('/boundaries', data)).data }
export async function updateBoundary(id: number, data: any) { return (await api.put(`/boundaries/${id}`, data)).data }
export async function deleteBoundary(id: number) { return (await api.delete(`/boundaries/${id}`)).data }

// Salaries & Payroll
export async function getSalaries(month?: number, year?: number, search?: string) {
  return (await api.get('/salaries', { params: { month, year, search } })).data
}
export async function createSalary(data: any) {
  return (await api.post('/salaries', data)).data
}
export async function updateSalary(id: number, data: any) {
  return (await api.put(`/salaries/${id}`, data)).data
}
export async function deleteSalary(id: number) {
  return (await api.delete(`/salaries/${id}`)).data
}

// Expenses
export async function getExpenses(params?: any) { return (await api.get('/expenses', { params })).data }
export async function getExpense(id: number) { return (await api.get(`/expenses/${id}`)).data }
export async function createExpense(data: any) { return (await api.post('/expenses', data)).data }
export async function updateExpense(id: number, data: any) { return (await api.put(`/expenses/${id}`, data)).data }
export async function deleteExpense(id: number) { return (await api.delete(`/expenses/${id}`)).data }
export async function approveExpense(id: number) { return (await api.post(`/expenses/${id}/approve`)).data }
export async function rejectExpense(id: number) { return (await api.post(`/expenses/${id}/reject`)).data }

// Maintenance
export interface MaintenanceRecord {
  id: number;
  unit_id: number;
  plate_number: string;
  unit_number: string;
  maintenance_type: string;
  description: string;
  status: string;
  cost: number;
  mechanic_name?: string;
  date_started: string;
  date_completed?: string;
  parts_list?: string;
  odometer_reading?: number;
  creator_name?: string;
  editor_name?: string;
}

export async function getMaintenances(params?: any) { return (await api.get('/maintenance', { params })).data }
export async function getMaintenance(id: number) { return (await api.get(`/maintenance/${id}`)).data }
export async function createMaintenance(data: any) { return (await api.post('/maintenance', data)).data }
export async function updateMaintenance(id: number, data: any) { return (await api.put(`/maintenance/${id}`, data)).data }
export async function deleteMaintenance(id: number) { return (await api.delete(`/maintenance/${id}`)).data }

// Behavioral & Others
export async function getBehavior(params?: any) { return (await api.get('/behavior', { params })).data }
export async function createBehavior(data: any) { return (await api.post('/behavior', data)).data }
export async function deleteBehavior(id: number) { return (await api.delete(`/behavior/${id}`)).data }
export async function getProfitability(params?: any) { return (await api.get('/profitability', { params })).data }
export async function getTracking() { return (await api.get('/tracking')).data }
export async function getCoding() { return (await api.get('/coding')).data }

export async function logoutApi() {
  const res = await api.post('/logout');
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  return res.data;
}

export function getStoredUser() {
  const user = localStorage.getItem('user');
  return user ? JSON.parse(user) : null;
}

export async function updateProfile(data: any) { return (await api.post('/profile/update', data)).data }
export async function changePassword(data: any) { return (await api.post('/password/change', data)).data }

export function isLoggedIn() {
  return !!localStorage.getItem('token');
}

export default api;
