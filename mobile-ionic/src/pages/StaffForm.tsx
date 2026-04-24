import React, { useState, useEffect } from 'react'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButtons, IonBackButton, IonButton, IonInput, IonSelect,
  IonSelectOption, IonSpinner, IonToast, IonIcon, IonAlert
} from '@ionic/react'
import { saveOutline, personCircleOutline, trashOutline, shieldCheckmarkOutline } from 'ionicons/icons'
import { getStaffRecord, createStaff, updateStaff, deleteStaff } from '../api'
import { useHistory, useParams } from 'react-router-dom'

export default function StaffForm() {
  const { id } = useParams<{ id?: string }>()
  const isEdit = !!id && id !== 'new'
  const isUserBased = id?.startsWith('u')
  const history = useHistory()

  const [form, setForm] = useState({
    name: '',
    role: 'Mechanic',
    custom_role: '',
    phone: '',
    status: 'active',
  })
  const [loading, setLoading] = useState(false)
  const [fetching, setFetching] = useState(isEdit)
  const [showDelete, setShowDelete] = useState(false)
  const [toast, setToast] = useState<{ show: boolean; message: string; color: string }>({ show: false, message: '', color: 'danger' })

  useEffect(() => {
    if (isEdit && id) {
      (async () => {
        try {
          const res = await getStaffRecord(id);
          if (res.success) {
            const item = res.data;
            const standardRoles = ['Mechanic', 'Guard'];
            const isCustom = !standardRoles.includes(item.role);
            
            setForm({
              name: item.name || '',
              role: isCustom ? 'Others' : item.role,
              custom_role: isCustom ? item.role : '',
              phone: item.phone || '',
              status: item.status || 'active',
            })
          }
        } catch (e) {
          setToast({ show: true, message: 'Failed to load staff record', color: 'danger' })
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
    if (isUserBased) return;
    
    if (!form.name.trim()) {
      setToast({ show: true, message: 'Name is required', color: 'warning' })
      return
    }

    const finalRole = form.role === 'Others' ? form.custom_role : form.role;
    if (!finalRole.trim()) {
      setToast({ show: true, message: 'Role is required', color: 'warning' })
      return
    }

    setLoading(true)
    try {
      const payload = { ...form, role: finalRole };
      if (isEdit) {
        await updateStaff(id!, payload)
        setToast({ show: true, message: 'Staff record updated!', color: 'success' })
      } else {
        await createStaff(payload)
        setToast({ show: true, message: 'Staff record created!', color: 'success' })
      }
      setTimeout(() => history.goBack(), 600)
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Save failed'
      setToast({ show: true, message: msg, color: 'danger' })
    } finally {
      setLoading(false)
    }
  }

  async function handleDelete() {
    if (!id || isUserBased) return;
    setLoading(true)
    try {
      await deleteStaff(id)
      setToast({ show: true, message: 'Staff record deleted', color: 'success' })
      setTimeout(() => history.push('/app/staff'), 600)
    } catch (e: any) {
      setToast({ show: true, message: 'Delete failed', color: 'danger' })
    } finally {
      setLoading(false)
    }
  }

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/app/staff" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? (isUserBased ? 'Administrative Profile' : 'Update Personnel') : 'Add Staff Member'}</div>
             <div className="header-modern-sub">{isEdit ? (isUserBased ? 'View protected system account credentials' : 'Modify personnel role, contact info, and status') : 'Onboard a new employee to the management system'}</div>
          </div>
          {isEdit && !isUserBased && (
             <IonButtons slot="end">
                <IonButton color="danger" onClick={() => setShowDelete(true)}>
                   <IonIcon icon={trashOutline} slot="icon-only" />
                </IonButton>
             </IonButtons>
          )}
        </IonToolbar>
      </IonHeader>

      <IonContent className="ion-padding">
        {fetching ? (
          <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
        ) : (
          <div className="animate-in" style={{ maxWidth: '500px', margin: '0 auto' }}>
            <div style={{ textAlign: 'center', margin: '20px 0' }}>
               <div style={{ 
                 width: 80, height: 80, borderRadius: '50%', 
                 background: isUserBased ? '#dbeafe' : '#fef3c7', 
                 display: 'flex', alignItems: 'center', justifyContent: 'center', 
                 margin: '0 auto', fontSize: '40px', color: isUserBased ? '#2563eb' : '#d97706' 
               }}>
                  <IonIcon icon={isUserBased ? shieldCheckmarkOutline : personCircleOutline} />
               </div>
               <div style={{ marginTop: 12 }}>
                  <div style={{ fontSize: '18px', fontWeight: '800', color: '#0f172a' }}>{form.name || 'Personnel Name'}</div>
                  <div style={{ fontSize: '12px', color: '#64748b' }}>{isUserBased ? 'System Administrative Account' : 'Manual Personnel Record'}</div>
               </div>
            </div>

            <div className="form-group">
              <label className="form-label">Full Name</label>
              <IonInput
                className="custom-input"
                placeholder="Enter full name"
                value={form.name}
                disabled={isUserBased}
                onIonInput={(e: any) => updateField('name', e.target.value || '')}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Role / Position</label>
              <IonSelect
                value={form.role}
                disabled={isUserBased}
                onIonChange={(e: any) => updateField('role', e.detail.value)}
                interface="action-sheet"
                className="custom-select"
              >
                <IonSelectOption value="Mechanic">Mechanic</IonSelectOption>
                <IonSelectOption value="Guard">Guard</IonSelectOption>
                <IonSelectOption value="Others">Others</IonSelectOption>
                {isUserBased && <IonSelectOption value="Admin">Admin</IonSelectOption>}
              </IonSelect>
            </div>

            {form.role === 'Others' && (
               <div className="form-group animate-in">
                  <label className="form-label">Specify Custom Role</label>
                  <IonInput
                    className="custom-input"
                    placeholder="Enter role name"
                    value={form.custom_role}
                    onIonInput={(e: any) => updateField('custom_role', e.target.value || '')}
                  />
               </div>
            )}

            <div className="form-group">
              <label className="form-label">Contact Number</label>
              <IonInput
                type="tel"
                className="custom-input"
                placeholder="09XXXXXXXXX"
                value={form.phone}
                disabled={isUserBased}
                onIonInput={(e: any) => updateField('phone', e.target.value || '')}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Employment Status</label>
              <IonSelect
                value={form.status}
                disabled={isUserBased}
                onIonChange={(e: any) => updateField('status', e.detail.value)}
                interface="action-sheet"
                className="custom-select"
              >
                <IonSelectOption value="active">Active</IonSelectOption>
                <IonSelectOption value="inactive">Inactive</IonSelectOption>
              </IonSelect>
            </div>

            {!isUserBased ? (
               <IonButton
                 expand="block"
                 className="btn-primary"
                 onClick={handleSave}
                 disabled={loading}
                 style={{ marginTop: 32 }}
               >
                 {loading ? <IonSpinner name="crescent" /> : <><IonIcon icon={saveOutline} slot="start" /> {isEdit ? 'Update Record' : 'Save Record'}</>}
               </IonButton>
            ) : (
               <div style={{ 
                 marginTop: 32, padding: '16px', borderRadius: '16px', 
                 background: '#f8fafc', border: '1px solid #e2e8f0',
                 textAlign: 'center'
               }}>
                  <p style={{ color: '#64748b', fontSize: '12px', margin: 0 }}>
                    This is a protected system account. Only the web administrator can modify credentials and role permissions.
                  </p>
               </div>
            )}
          </div>
        )}

        <IonAlert
          isOpen={showDelete}
          onDidDismiss={() => setShowDelete(false)}
          header="Confirm Deletion"
          message="Are you sure you want to permanently remove this staff record?"
          buttons={[
            { text: 'Cancel', role: 'cancel' },
            { text: 'Delete Staff', cssClass: 'danger', handler: handleDelete }
          ]}
        />

        <IonToast
          isOpen={toast.show}
          message={toast.message}
          color={toast.color}
          duration={2000}
          onDidDismiss={() => setToast({ ...toast, show: false })}
        />
      </IonContent>
    </IonPage>
  )
}

