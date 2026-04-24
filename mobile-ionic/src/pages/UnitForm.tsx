import React, { useState, useEffect } from 'react'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButtons, IonBackButton, IonButton, IonInput, IonSelect,
  IonSelectOption, IonSpinner, IonToast, IonIcon, IonLabel, IonItem, IonList
} from '@ionic/react'
import { saveOutline, carSportOutline, settingsOutline, cashOutline, mapOutline } from 'ionicons/icons'
import { createUnit, updateUnit, getUnit } from '../api'
import { useHistory, useParams } from 'react-router-dom'

export default function UnitForm() {
  const { id } = useParams<{ id?: string }>()
  const isEdit = !!id
  const history = useHistory()

  const [form, setForm] = useState({
    unit_number: '',
    plate_number: '',
    make: '',
    model: '',
    year: new Date().getFullYear().toString(),
    color: 'White',
    status: 'active',
    unit_type: 'new',
    fuel_status: 'full',
    boundary_rate: '1100',
    purchase_cost: '0',
    purchase_date: '',
    gps_link: '',
  })
  const [loading, setLoading] = useState(false)
  const [fetching, setFetching] = useState(isEdit)
  const [toast, setToast] = useState<{ show: boolean; message: string; color: string }>({ show: false, message: '', color: 'danger' })

  useEffect(() => {
    if (isEdit && id) {
      (async () => {
        try {
          const res = await getUnit(Number(id))
          if (res.success && res.data) {
            setForm({
              unit_number: res.data.unit_number || '',
              plate_number: res.data.plate_number || '',
              make: res.data.make || '',
              model: res.data.model || '',
              year: res.data.year?.toString() || '2023',
              color: res.data.color || 'White',
              status: res.data.status || 'active',
              unit_type: res.data.unit_type || 'new',
              fuel_status: res.data.fuel_status || 'full',
              boundary_rate: res.data.boundary_rate?.toString() || '1100',
              purchase_cost: res.data.purchase_cost?.toString() || '0',
              purchase_date: res.data.purchase_date || '',
              gps_link: res.data.gps_link || '',
            })
          }
        } catch (e) {
          setToast({ show: true, message: 'Failed to load unit', color: 'danger' })
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
    if (!form.unit_number.trim() || !form.plate_number.trim()) {
      setToast({ show: true, message: 'Unit number and plate number are required', color: 'warning' })
      return
    }

    setLoading(true)
    try {
      const payload = {
        ...form,
        boundary_rate: parseFloat(form.boundary_rate) || 0,
        purchase_cost: parseFloat(form.purchase_cost) || 0,
        year: parseInt(form.year) || 2023,
      }

      if (isEdit) {
        await updateUnit(Number(id), payload)
        setToast({ show: true, message: 'Unit updated!', color: 'success' })
      } else {
        await createUnit(payload)
        setToast({ show: true, message: 'Unit created!', color: 'success' })
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
            <IonBackButton defaultHref="/app/units" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? 'Update Vehicle' : 'Register Vehicle'}</div>
             <div className="header-modern-sub">{isEdit ? 'Modify vehicle specifications' : 'Onboard a new fleet unit'}</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent className="ion-padding">
        {fetching ? (
          <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
        ) : (
          <div className="animate-in" style={{ paddingBottom: 60 }}>
            
            <div style={{ textAlign: 'center', marginBottom: '24px' }}>
               <div className="modal-detail-icon" style={{ background: '#eff6ff', color: '#1d4ed8' }}>
                  <IonIcon icon={carSportOutline} />
               </div>
               <h2 style={{ fontWeight: 900, fontSize: '20px', color: '#0f172a', margin: '0' }}>{isEdit ? 'Edit Asset' : 'Vehicle Identity'}</h2>
            </div>

            {/* IDENTITY SECTION */}
            <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
               <div className="executive-label" style={{ marginBottom: '16px' }}>Core Identification</div>
               <IonList lines="none" style={{ background: 'transparent' }}>
                  <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                    <IonLabel position="stacked">UNIT NUMBER *</IonLabel>
                    <IonInput placeholder="e.g. 001" value={form.unit_number} onIonInput={(e: any) => updateField('unit_number', e.target.value || '')} />
                  </IonItem>
                  <IonItem className="custom-filter-select">
                    <IonLabel position="stacked">PLATE NUMBER *</IonLabel>
                    <IonInput placeholder="e.g. ABC 1234" value={form.plate_number} onIonInput={(e: any) => updateField('plate_number', (e.target.value || '').toUpperCase())} />
                  </IonItem>
               </IonList>
            </div>

            {/* SPECS SECTION */}
            <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
               <div className="executive-label" style={{ marginBottom: '16px' }}>Technical Specifications</div>
               <IonList lines="none" style={{ background: 'transparent' }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                    <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                       <IonLabel position="stacked">MAKE</IonLabel>
                       <IonInput placeholder="TOYOTA" value={form.make} onIonInput={(e: any) => updateField('make', (e.target.value || '').toUpperCase())} />
                    </IonItem>
                    <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                       <IonLabel position="stacked">MODEL</IonLabel>
                       <IonInput placeholder="VIOS" value={form.model} onIonInput={(e: any) => updateField('model', (e.target.value || '').toUpperCase())} />
                    </IonItem>
                  </div>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                    <IonItem className="custom-filter-select">
                       <IonLabel position="stacked">YEAR</IonLabel>
                       <IonInput type="number" value={form.year} onIonInput={(e: any) => updateField('year', e.target.value || '')} />
                    </IonItem>
                    <IonItem className="custom-filter-select">
                       <IonLabel position="stacked">COLOR</IonLabel>
                       <IonInput value={form.color} onIonInput={(e: any) => updateField('color', e.target.value || '')} />
                    </IonItem>
                  </div>
               </IonList>
            </div>

            {/* OPERATING SECTION */}
            <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
               <div className="executive-label" style={{ marginBottom: '16px' }}>Operating Intelligence</div>
               <IonList lines="none" style={{ background: 'transparent' }}>
                  <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                    <IonLabel position="stacked">FUEL STATUS</IonLabel>
                    <IonSelect value={form.fuel_status} onIonChange={(e) => updateField('fuel_status', e.detail.value)} interface="action-sheet">
                        <IonSelectOption value="full">Full Tank</IonSelectOption>
                        <IonSelectOption value="3/4">3/4 Tank</IonSelectOption>
                        <IonSelectOption value="1/2">1/2 Tank</IonSelectOption>
                        <IonSelectOption value="1/4">1/4 Tank</IonSelectOption>
                        <IonSelectOption value="empty">Empty</IonSelectOption>
                    </IonSelect>
                  </IonItem>
                  <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                    <IonLabel position="stacked">BOUNDARY RATE (₱)</IonLabel>
                    <IonInput type="number" value={form.boundary_rate} onIonInput={(e: any) => updateField('boundary_rate', e.target.value || '')} />
                  </IonItem>
                  <IonItem className="custom-filter-select">
                    <IonLabel position="stacked">GPS TRACKING LINK</IonLabel>
                    <IonInput placeholder="https://..." value={form.gps_link} onIonInput={(e: any) => updateField('gps_link', e.target.value || '')} />
                  </IonItem>
               </IonList>
            </div>

            <IonButton expand="block" onClick={handleSave} disabled={loading} className="btn-amber" style={{ height: '56px', borderRadius: '16px', marginTop: '10px' }}>
              {loading ? <IonSpinner name="crescent" /> : (
                <>
                  <IonIcon icon={saveOutline} slot="start" />
                  {isEdit ? 'UPDATE UNIT' : 'REGISTER UNIT'}
                </>
              )}
            </IonButton>

          </div>
        )}

        <IonToast isOpen={toast.show} message={toast.message} color={toast.color} duration={2000} position="top" onDidDismiss={() => setToast({ ...toast, show: false })} />
      </IonContent>
    </IonPage>
  )
}
