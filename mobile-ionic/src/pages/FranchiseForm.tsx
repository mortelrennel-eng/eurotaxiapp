import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonList, IonItem, IonInput, IonButton, IonButtons, IonBackButton, IonSpinner, IonToast,
  IonIcon, IonCard, IonGrid, IonRow, IonCol, IonSegment, IonSegmentButton, IonLabel, IonFooter, IonTitle
} from '@ionic/react';
import { 
  saveOutline, printOutline, refreshOutline, 
  calendarOutline, listOutline, trashOutline,
  addOutline, businessOutline, documentTextOutline, chevronBackOutline
} from 'ionicons/icons';
import { useParams, useHistory } from 'react-router-dom';
import { 
  getFranchise, createFranchise, updateFranchise, deleteFranchise,
} from '../api';

export default function FranchiseForm() {
  const { id } = useParams<{ id?: string }>();
  const isEdit = !!id;
  const history = useHistory();

  const initialForm = {
    case_no: '',
    applicant_name: '',
    type_of_application: 'New/Renewal',
    denomination: 'Taxi',
    date_filed: new Date().toISOString().split('T')[0],
    expiry_date: '',
    status: 'pending',
    notes: '',
    units: [{ make: '', motor_no: '', chassis_no: '', plate_no: '', year_model: '' }]
  };

  const [form, setForm] = useState<any>(initialForm);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState('info');
  const [toast, setToast] = useState({ show: false, message: '', color: 'success' });

  const loadData = useCallback(async () => {
    setLoading(true);
    try {
      if (isEdit && id) {
        const res = await getFranchise(parseInt(id));
        if (res.success) {
          const f = res.data;
          setForm({
            case_no: f.case_no,
            applicant_name: f.applicant_name,
            type_of_application: f.type_of_application,
            denomination: f.denomination,
            date_filed: f.date_filed,
            expiry_date: f.expiry_date || '',
            status: f.status,
            notes: f.notes || '',
            units: (f.units && f.units.length > 0) ? f.units : [{ make: '', motor_no: '', chassis_no: '', plate_no: '', year_model: '' }]
          });
        }
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Sync failed', color: 'danger' });
    } finally {
      setLoading(false);
    }
  }, [id, isEdit]);

  useEffect(() => { loadData(); }, [loadData]);

  const handleUnitChange = (index: number, field: string, value: string) => {
    const newUnits = [...form.units];
    newUnits[index][field] = value;
    setForm({ ...form, units: newUnits });
  };

  const addUnitRow = () => {
    if (form.units.length >= 20) {
      setToast({ show: true, message: 'Maximum 20 units allowed', color: 'warning' });
      return;
    }
    setForm({ ...form, units: [...form.units, { make: '', motor_no: '', chassis_no: '', plate_no: '', year_model: '' }] });
    setActiveTab('units');
  };

  const removeUnitRow = (index: number) => {
    const newUnits = form.units.filter((_: any, i: number) => i !== index);
    setForm({ ...form, units: newUnits.length ? newUnits : [{ make: '', motor_no: '', chassis_no: '', plate_no: '', year_model: '' }] });
  };

  const handleSave = async () => {
    if (!form.case_no || !form.applicant_name) {
      setToast({ show: true, message: 'Case # and Applicant required', color: 'warning' });
      return;
    }
    setSaving(true);
    try {
      const res = isEdit 
        ? await updateFranchise(parseInt(id!), form)
        : await createFranchise(form);
      
      if (res.success) {
        setToast({ show: true, message: 'Decision Saved Successfully', color: 'success' });
        setTimeout(() => history.goBack(), 1000);
      } else {
        setToast({ show: true, message: res.message || 'Action failed', color: 'danger' });
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Connection Error', color: 'danger' });
    } finally {
      setSaving(false);
    }
  };

  const handleClear = () => {
    if (window.confirm('Clear form and start new case?')) {
      setForm(initialForm);
      setActiveTab('info');
      if (isEdit) history.push('/app/franchises/new');
    }
  };

  if (loading) return (
    <IonPage>
      <div className="loading-center"><IonSpinner name="lines" /></div>
    </IonPage>
  );

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/app/franchises" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? 'Update LTFRB Case' : 'New Franchise Application'}</div>
             <div className="header-modern-sub">{isEdit ? 'Modify existing regulatory decision and legal records' : 'Enter new case details for franchise board decision'}</div>
          </div>
        </IonToolbar>
        <IonToolbar style={{ '--background': '#fff', borderBottom: '1px solid #f1f5f9', '--min-height': '44px' }}>
           <IonSegment value={activeTab} onIonChange={e => setActiveTab(e.detail.value!)} mode="md" className="custom-segment">
              <IonSegmentButton value="info"><IonLabel>APPLICATION</IonLabel></IonSegmentButton>
              <IonSegmentButton value="units">
                <IonLabel>UNITS ({form.units.length})</IonLabel>
              </IonSegmentButton>
           </IonSegment>
        </IonToolbar>
      </IonHeader>

      <IonContent style={{ '--background': '#f1f5f9' }}>
        <div className="animate-in" style={{ padding: '20px' }}>
          
          {activeTab === 'info' && (
            <>
              <div className="form-section-label">CASE IDENTIFICATION</div>
              <IonCard className="mockup-card" mode="ios">
                 <div className="input-field">
                    <label>APPLICANT NAME</label>
                    <IonInput 
                       placeholder="Registered Owner" 
                       value={form.applicant_name} 
                       onIonChange={(e: any) => setForm({...form, applicant_name: e.detail.value})} 
                       className="mockup-input"
                    />
                 </div>
                 <div className="input-divider" />
                 <div className="input-field">
                    <label>CASE NUMBER</label>
                    <IonInput 
                       placeholder="ex. 2024-XXXX" 
                       value={form.case_no} 
                       onIonChange={(e: any) => setForm({...form, case_no: e.detail.value})} 
                       className="mockup-input"
                    />
                 </div>
              </IonCard>

              <div className="form-section-label" style={{ marginTop: '25px' }}>FILING PARAMETERS</div>
              <IonCard className="mockup-card" mode="ios">
                 <div className="input-field">
                    <label>TYPE OF APPLICATION</label>
                    <IonInput 
                       value={form.type_of_application} 
                       onIonChange={(e: any) => setForm({...form, type_of_application: e.detail.value})} 
                       className="mockup-input"
                    />
                 </div>
                 <div className="input-divider" />
                 <div className="input-field">
                    <label>DENOMINATION</label>
                    <IonInput 
                       value={form.denomination} 
                       onIonChange={(e: any) => setForm({...form, denomination: e.detail.value})} 
                       className="mockup-input"
                    />
                 </div>
                 <div className="input-divider" />
                 <div style={{ display: 'flex' }}>
                    <div className="input-field" style={{ flex: 1, borderRight: '1px solid #f1f5f9' }}>
                       <label>DATE FILED</label>
                       <IonInput 
                          type="date" 
                          value={form.date_filed} 
                          onIonChange={(e: any) => setForm({...form, date_filed: e.detail.value})} 
                          className="mockup-input"
                       />
                    </div>
                    <div className="input-field" style={{ flex: 1 }}>
                       <label>EXPIRY DATE</label>
                       <IonInput 
                          type="date" 
                          value={form.expiry_date} 
                          onIonChange={(e: any) => setForm({...form, expiry_date: e.detail.value})} 
                          className="mockup-input"
                       />
                    </div>
                 </div>
              </IonCard>

              {isEdit && (
                <div style={{ marginTop: '30px' }}>
                    <div className="form-section-label">DECISION STATUS</div>
                    <div style={{ padding: '10px 0' }}>
                       <IonSegment mode="ios" value={form.status} onIonChange={(e: any) => setForm({...form, status: e.detail.value})} className="status-segment">
                          <IonSegmentButton value="pending"><IonLabel>Pending</IonLabel></IonSegmentButton>
                          <IonSegmentButton value="approved"><IonLabel>Approved</IonLabel></IonSegmentButton>
                          <IonSegmentButton value="rejected"><IonLabel>Rejected</IonLabel></IonSegmentButton>
                       </IonSegment>
                    </div>
                </div>
              )}
            </>
          )}

          {activeTab === 'units' && (
            <>
              <div className="form-section-label">VEHICLE INVENTORY (MAX 20)</div>
              {form.units.map((unit: any, idx: number) => (
                <IonCard key={idx} mode="ios" className="unit-mockup-card">
                    <div className="unit-card-header">
                        <span>SLOT #{idx + 1}</span>
                        {form.units.length > 1 && (
                            <IonButton fill="clear" color="danger" size="small" onClick={() => removeUnitRow(idx)}>
                                <IonIcon icon={trashOutline} slot="icon-only" />
                            </IonButton>
                        )}
                    </div>
                    <div className="unit-card-body">
                        <IonGrid className="ion-no-padding">
                            <IonRow>
                                <IonCol size="6"><div className="input-field"><label>Make</label><IonInput value={unit.make} onIonChange={(e: any) => handleUnitChange(idx, 'make', e.detail.value)} /></div></IonCol>
                                <IonCol size="6"><div className="input-field"><label>Plate #</label><IonInput value={unit.plate_no} onIonChange={(e: any) => handleUnitChange(idx, 'plate_no', e.detail.value)} /></div></IonCol>
                            </IonRow>
                            <div className="input-divider" />
                            <IonRow>
                                <IonCol size="6"><div className="input-field"><label>Motor #</label><IonInput value={unit.motor_no} onIonChange={(e: any) => handleUnitChange(idx, 'motor_no', e.detail.value)} /></div></IonCol>
                                <IonCol size="6"><div className="input-field"><label>Chasis #</label><IonInput value={unit.chassis_no} onIonChange={(e: any) => handleUnitChange(idx, 'chassis_no', e.detail.value)} /></div></IonCol>
                            </IonRow>
                            <div className="input-divider" />
                            <IonRow>
                                <IonCol size="12"><div className="input-field"><label>Year Model</label><IonInput value={unit.year_model} onIonChange={(e: any) => handleUnitChange(idx, 'year_model', e.detail.value)} /></div></IonCol>
                            </IonRow>
                        </IonGrid>
                    </div>
                </IonCard>
              ))}
              <IonButton fill="outline" expand="block" onClick={addUnitRow} className="btn-add-unit" mode="ios">
                <IonIcon icon={addOutline} slot="start" /> Append Unit Row
              </IonButton>
            </>
          )}

          <div style={{ height: 100 }} />
        </div>
      </IonContent>

      <IonFooter className="ion-no-border footer-franchise" style={{ background: '#fff', borderTop: '1px solid #f1f5f9', padding: '15px' }}>
          <div style={{ display: 'flex', gap: '10px' }}>
             <button className="btn-mockup-outline" onClick={() => window.print()}>
                <IonIcon icon={printOutline} /> Print
             </button>
             <button className="btn-mockup-outline" style={{ flex: 1.5 }} onClick={handleClear}>
                <IonIcon icon={refreshOutline} /> Clear / New Case
             </button>
             <button className="btn-mockup-solid" onClick={handleSave} disabled={saving}>
                {saving ? <IonSpinner name="dots" color="light" /> : (
                  <>
                    <IonIcon icon={saveOutline} /> 
                    {isEdit ? 'Update Case' : 'Save Case'}
                  </>
                )}
             </button>
          </div>
      </IonFooter>

      <IonToast
        isOpen={toast.show} message={toast.message} color={toast.color} duration={2000}
        onDidDismiss={() => setToast({ ...toast, show: false })}
      />

      <style>{`
        .form-section-label { font-size: 11px; font-weight: 900; color: #94a3b8; letter-spacing: 0.05em; margin-bottom: 8px; font-family: 'Inter', sans-serif; }
        .mockup-card { margin: 0; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; background: #fff; overflow: hidden; }
        .input-field { padding: 12px 20px; }
        .input-field label { display: block; font-size: 9px; font-weight: 900; color: #94a3b8; margin-bottom: 4px; }
        .mockup-input { --padding-start: 0; font-size: 15px; font-weight: 800; color: #0f172a; --padding-top: 0; --padding-bottom: 0; }
        .input-divider { height: 1px; background: #f1f5f9; width: 100%; }

        .custom-segment { --background: #fff; --indicator-color: #f59e0b; padding: 0 20px 10px 20px; }
        .custom-segment ion-segment-button { --color: #64748b; --color-checked: #f59e0b; font-size: 11px; font-weight: 900; min-height: 48px; border-bottom: 2px solid transparent; }
        .custom-segment ion-segment-button.segment-button-checked { border-bottom: 2px solid #f59e0b; }

        .unit-mockup-card { margin: 0 0 15px 0; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .unit-card-header { background: #f8fafc; padding: 10px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; font-size: 10px; font-weight: 900; color: #94a3b8; }
        
        .btn-add-unit { margin-top: 10px; --border-radius: 14px; --border-color: #e2e8f0; --color: #64748b; font-size: 12px; font-weight: 800; }

        .footer-franchise { box-shadow: 0 -10px 20px rgba(0,0,0,0.02); }
        .btn-mockup-outline { flex: 1; height: 48px; border-radius: 12px; border: 1px solid #0f172a; background: #fff; color: #0f172a; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; outline: none; transition: 0.2s; }
        .btn-mockup-outline:active { background: #f1f5f9; }
        .btn-mockup-solid { flex: 2; height: 48px; border-radius: 12px; border: none; background: #ca8a04; color: #fff; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(202, 138, 4, 0.3); outline: none; }
        .btn-mockup-solid:active { transform: scale(0.98); }

        .status-segment { --background: #f1f5f9; --indicator-color: #fff; border-radius: 12px; padding: 4px; }
        .status-segment ion-segment-button { --color: #64748b; --color-checked: #0f172a; font-size: 12px; font-weight: 800; }

        .loading-center { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; }
        .animate-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </IonPage>
  );
}
