import React, { useState, useEffect } from 'react'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButtons, IonBackButton, IonButton, IonInput, IonSelect,
  IonSelectOption, IonSpinner, IonToast, IonIcon, IonTextarea, IonItem, IonLabel, IonList
} from '@ionic/react'
import { saveOutline, warningOutline, alertCircleOutline, carSportOutline, personOutline, shieldCheckmarkOutline } from 'ionicons/icons'
import { getUnits, getDrivers, createBehavior } from '../api'
import { useHistory } from 'react-router-dom'

export default function BehaviorForm() {
  const history = useHistory()
  const [units, setUnits] = useState<any[]>([])
  const [drivers, setDrivers] = useState<any[]>([])
  const [loading, setLoading] = useState(false)
  const [fetching, setFetching] = useState(true)
  const [form, setForm] = useState({
    unit_id: '',
    driver_id: '',
    incident_type: 'Over-speeding',
    severity: 'Medium',
    description: '',
    latitude: 0,
    longitude: 0,
    video_url: ''
  })
  const [toast, setToast] = useState<{ show: boolean; message: string; color: string }>({ show: false, message: '', color: 'danger' })

  useEffect(() => {
    (async () => {
      try {
        const [uRes, dRes] = await Promise.all([getUnits(), getDrivers()])
        if (uRes.success) setUnits(Array.isArray(uRes.data) ? uRes.data : uRes.data.records || [])
        if (dRes.success) setDrivers(Array.isArray(dRes.data) ? dRes.data : dRes.data.records || [])
      } catch (e) {
        setToast({ show: true, message: 'Failed to load options', color: 'danger' })
      } finally {
        setFetching(false)
      }
    })()
  }, [])

  function updateField(field: string, value: any) {
    setForm(prev => ({ ...prev, [field]: value }))
  }

  async function handleSave() {
    if (!form.unit_id || !form.driver_id || !form.description.trim()) {
      setToast({ show: true, message: 'Please fill in all required fields', color: 'warning' })
      return
    }

    setLoading(true)
    try {
      const res = await createBehavior(form)
      if (res.success) {
        setToast({ show: true, message: 'Incident recorded!', color: 'success' })
        setTimeout(() => history.goBack(), 600)
      } else {
        setToast({ show: true, message: res.message || 'Save failed', color: 'danger' })
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Save error', color: 'danger' })
    } finally {
      setLoading(false)
    }
  }

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/app/behavior" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">Behavior Reporting</div>
             <div className="header-modern-sub">Log safety violations and performance incidents</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent className="ion-padding">
        {fetching ? (
          <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
        ) : (
          <div className="animate-in" style={{ paddingBottom: 60 }}>
            
            <div style={{ textAlign: 'center', marginBottom: '24px' }}>
               <div className="modal-detail-icon" style={{ background: '#fee2e2', color: '#dc2626' }}>
                  <IonIcon icon={alertCircleOutline} />
               </div>
               <h2 style={{ fontWeight: 900, fontSize: '20px', color: '#0f172a', margin: '0' }}>New Incident Report</h2>
            </div>

            {/* ASSIGNMENT SECTION */}
            <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
               <div className="executive-label" style={{ marginBottom: '16px' }}>Incident Assignment</div>
               <IonList lines="none" style={{ background: 'transparent' }}>
                  <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                    <IonLabel position="stacked">ASSOCIATED UNIT</IonLabel>
                    <IonSelect value={form.unit_id} onIonChange={(e: any) => updateField('unit_id', e.detail.value)} interface="action-sheet" placeholder="Select Unit">
                      {units.map((u: any) => (
                        <IonSelectOption key={u.id} value={u.id}>Unit {u.unit_number} ({u.plate_number})</IonSelectOption>
                      ))}
                    </IonSelect>
                  </IonItem>
                  <IonItem className="custom-filter-select">
                    <IonLabel position="stacked">DRIVER INVOLVED</IonLabel>
                    <IonSelect value={form.driver_id} onIonChange={(e: any) => updateField('driver_id', e.detail.value)} interface="action-sheet" placeholder="Select Driver">
                      {drivers.map((d: any) => (
                        <IonSelectOption key={d.id} value={d.id}>{d.full_name || d.user?.full_name}</IonSelectOption>
                      ))}
                    </IonSelect>
                  </IonItem>
               </IonList>
            </div>

            {/* INCIDENT DETAILS */}
            <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
               <div className="executive-label" style={{ marginBottom: '16px' }}>Violation Specification</div>
               <IonList lines="none" style={{ background: 'transparent' }}>
                  <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                    <IonLabel position="stacked">INCIDENT TYPE</IonLabel>
                    <IonSelect value={form.incident_type} onIonChange={(e: any) => updateField('incident_type', e.detail.value)} interface="action-sheet">
                      <IonSelectOption value="Over-speeding">Over-speeding</IonSelectOption>
                      <IonSelectOption value="Reckless Driving">Reckless Driving</IonSelectOption>
                      <IonSelectOption value="Customer Complaint">Customer Complaint</IonSelectOption>
                      <IonSelectOption value="Unauthorized Routing">Unauthorized Routing</IonSelectOption>
                      <IonSelectOption value="Late Remittance">Late Remittance</IonSelectOption>
                      <IonSelectOption value="Others">Others</IonSelectOption>
                    </IonSelect>
                  </IonItem>
                  
                  <div className="executive-label" style={{ marginBottom: '10px', fontSize: '9px' }}>Severity Level</div>
                  <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '8px', marginBottom: '16px' }}>
                    {['Low', 'Medium', 'High', 'Critical'].map(s => (
                      <div 
                        key={s} 
                        onClick={() => updateField('severity', s)}
                        style={{
                          padding: '8px 4px',
                          textAlign: 'center',
                          borderRadius: '12px',
                          fontSize: '9px',
                          fontWeight: 800,
                          cursor: 'pointer',
                          transition: '0.2s',
                          border: '1.5px solid',
                          background: form.severity === s ? (s === 'Critical' ? '#be123c' : (s === 'High' ? '#ea580c' : (s === 'Medium' ? '#ca8a04' : '#2563eb'))) : 'transparent',
                          color: form.severity === s ? 'white' : '#64748b',
                          borderColor: form.severity === s ? 'transparent' : '#e2e8f0'
                        }}
                      >
                        {s.toUpperCase()}
                      </div>
                    ))}
                  </div>

                  <IonItem className="custom-filter-select">
                    <IonLabel position="stacked">DETAILED DESCRIPTION *</IonLabel>
                    <IonTextarea placeholder="Explain the incident details..." rows={4} value={form.description} onIonInput={(e: any) => updateField('description', e.target.value || '')} />
                  </IonItem>
               </IonList>
            </div>

            <IonButton expand="block" onClick={handleSave} disabled={loading} color="danger" style={{ height: '56px', borderRadius: '16px', fontWeight: 900, letterSpacing: '1px' }}>
              {loading ? <IonSpinner name="crescent" /> : (
                <>
                  <IonIcon icon={saveOutline} slot="start" />
                  SUBMIT REPORT
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
