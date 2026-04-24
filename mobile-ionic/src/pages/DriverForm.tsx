import React, { useState, useEffect } from 'react'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButtons, IonBackButton, IonButton, IonInput,
  IonSpinner, IonToast, IonIcon,
} from '@ionic/react'
import { saveOutline } from 'ionicons/icons'
import { createDriver, updateDriver, getDriver } from '../api'
import { useHistory, useParams } from 'react-router-dom'

export default function DriverForm() {
  const { id } = useParams<{ id?: string }>()
  const isEdit = !!id
  const history = useHistory()

    const [form, setForm] = useState({
    full_name: '',
    email: '',
    password: '',
    license_number: '',
    license_expiry: '',
    contact_number: '',
    address: '',
    emergency_contact: '',
    emergency_phone: '',
    daily_boundary_target: '1100',
    hire_date: new Date().toISOString().split('T')[0],
  })
  const [loading, setLoading] = useState(false)
  const [fetching, setFetching] = useState(isEdit)
  const [toast, setToast] = useState<{ show: boolean; message: string; color: string }>({ show: false, message: '', color: 'danger' })

  useEffect(() => {
    if (isEdit && id) {
      (async () => {
        try {
          const res = await getDriver(Number(id))
          if (res.success && res.data) {
            setForm({
              full_name: res.data.full_name || '',
              email: res.data.email || '',
              password: '',
              license_number: res.data.license_number || '',
              license_expiry: res.data.license_expiry || '',
              contact_number: res.data.contact_number || '',
              address: res.data.address || '',
              emergency_contact: res.data.emergency_contact || '',
              emergency_phone: res.data.emergency_phone || '',
              daily_boundary_target: res.data.daily_boundary_target?.toString() || '1100',
              hire_date: res.data.hire_date || new Date().toISOString().split('T')[0],
            })
          }
        } catch (e) {
          setToast({ show: true, message: 'Failed to load driver', color: 'danger' })
        } finally {
          setFetching(false)
        }
      })()
    }
  }, [id, isEdit])

  function updateField(field: string, value: string) {
    setForm(prev => ({ ...prev, [field]: value }))
  }

  async function handleSave() {
    if (!form.license_number.trim()) {
      setToast({ show: true, message: 'License number is required', color: 'warning' })
      return
    }

    if (!isEdit && (!form.full_name.trim() || !form.email.trim() || !form.password.trim())) {
      setToast({ show: true, message: 'Name, email, and password are required for new drivers', color: 'warning' })
      return
    }

    setLoading(true)
    try {
      if (isEdit) {
        await updateDriver(Number(id), { ...form })
        setToast({ show: true, message: 'Driver updated!', color: 'success' })
      } else {
        await createDriver({ ...form })
        setToast({ show: true, message: 'Driver created!', color: 'success' })
      }
      setTimeout(() => history.goBack(), 600)
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Save failed'
      setToast({ show: true, message: msg, color: 'danger' })
    } finally {
      setLoading(false)
    }
  }

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/app/drivers" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? 'Update Driver Profile' : 'Onboard New Driver'}</div>
             <div className="header-modern-sub">{isEdit ? 'Modify driver credentials, contact info, and licensing' : 'Register a new driver to the fleet system'}</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent className="ion-padding">
        {fetching ? (
          <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
        ) : (
          <div style={{ paddingBottom: 40 }}>
            {/* Account Info */}
            {!isEdit && (
              <div className="form-section">
                <div className="section-title">New User Account</div>
                <label className="form-label">Full Name *</label>
                <IonInput
                  placeholder="e.g. Juan Dela Cruz"
                  value={form.full_name}
                  onIonInput={(e: any) => updateField('full_name', e.target.value || '')}
                />

                <label className="form-label">Email Address *</label>
                <IonInput
                  type="email"
                  placeholder="e.g. juan@email.com"
                  value={form.email}
                  onIonInput={(e: any) => updateField('email', e.target.value || '')}
                />

                <label className="form-label">Login Password *</label>
                <IonInput
                  type="password"
                  placeholder="Min. 6 characters"
                  value={form.password}
                  onIonInput={(e: any) => updateField('password', e.target.value || '')}
                />
              </div>
            )}

            {isEdit && (
                <div style={{
                  background: '#f8fafc', borderRadius: 16, padding: 16,
                  marginBottom: 16, border: '1px solid #e2e8f0',
                }}>
                  <div style={{ fontWeight: 700, color: '#1e293b', marginBottom: 4 }}>{form.full_name}</div>
                  <div style={{ fontSize: '0.82rem', color: '#64748b' }}>{form.email}</div>
                </div>
            )}

            <div className="form-section" style={{ marginTop: 24 }}>
              <div className="section-title">Professional Details</div>
              <label className="form-label">License Number *</label>
              <IonInput
                placeholder="e.g. N01-12-345678"
                value={form.license_number}
                onIonInput={(e: any) => updateField('license_number', e.target.value || '')}
              />

              <label className="form-label">License Expiry *</label>
              <IonInput
                type="date"
                value={form.license_expiry}
                onIonInput={(e: any) => updateField('license_expiry', e.target.value || '')}
              />

              <label className="form-label">Hire Date</label>
              <IonInput
                type="date"
                value={form.hire_date}
                onIonInput={(e: any) => updateField('hire_date', e.target.value || '')}
              />
            </div>

            <div className="form-section" style={{ marginTop: 24 }}>
              <div className="section-title">Contact & Address</div>
              <label className="form-label">Primary Contact Number</label>
              <IonInput
                type="tel"
                placeholder="e.g. 09171234567"
                value={form.contact_number}
                onIonInput={(e: any) => updateField('contact_number', e.target.value || '')}
              />

              <label className="form-label">Home Address</label>
              <IonInput
                placeholder="Full address"
                value={form.address}
                onIonInput={(e: any) => updateField('address', e.target.value || '')}
              />
            </div>

            <div className="form-section" style={{ marginTop: 24 }}>
              <div className="section-title">Emergency info</div>
              <label className="form-label">Emergency Contact Name</label>
              <IonInput
                value={form.emergency_contact}
                onIonInput={(e: any) => updateField('emergency_contact', e.target.value || '')}
              />
              <label className="form-label">Emergency Phone</label>
              <IonInput
                type="tel"
                value={form.emergency_phone}
                onIonInput={(e: any) => updateField('emergency_phone', e.target.value || '')}
              />
            </div>

            <div className="form-section" style={{ marginTop: 24 }}>
              <div className="section-title">Financial Targets</div>
              <label className="form-label">Daily Boundary Target (₱)</label>
              <IonInput
                type="number"
                value={form.daily_boundary_target}
                onIonInput={(e: any) => updateField('daily_boundary_target', e.target.value || '')}
              />
            </div>

            <IonButton
              expand="block"
              className="btn-primary"
              onClick={handleSave}
              disabled={loading}
              style={{ marginTop: 32 }}
            >
              {loading ? (
                <IonSpinner name="crescent" style={{ marginRight: 8 }} />
              ) : (
                <IonIcon icon={saveOutline} slot="start" />
              )}
              {loading ? 'Saving...' : (isEdit ? 'Update Driver' : 'Register Driver')}
            </IonButton>
          </div>
        )}

        <IonToast
          isOpen={toast.show}
          message={toast.message}
          color={toast.color}
          duration={2000}
          position="top"
          onDidDismiss={() => setToast({ ...toast, show: false })}
        />
      </IonContent>
    </IonPage>
  )
}
