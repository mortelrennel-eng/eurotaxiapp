import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonItem, IonLabel, IonInput, IonSelect, IonSelectOption,
  IonButton, IonButtons, IonBackButton, IonSpinner, IonToast,
  IonIcon, IonCard, IonTextarea, IonGrid, IonRow, IonCol
} from '@ionic/react';
import { 
  saveOutline, constructOutline, buildOutline, 
  calendarOutline, documentTextOutline, searchOutline,
  personOutline, settingsOutline, trashOutline
} from 'ionicons/icons';
import { useParams, useHistory } from 'react-router-dom';
import { 
  getMaintenance, createMaintenance, updateMaintenance, deleteMaintenance, 
  getUnits, Unit 
} from '../api';

export default function MaintenanceForm() {
  const { id } = useParams<{ id?: string }>();
  const isEdit = !!id;
  const history = useHistory();

  const [form, setForm] = useState<any>({
    unit_id: '',
    maintenance_type: 'preventive',
    status: 'pending',
    description: '',
    cost: 0,
    mechanic_name: '',
    date_started: new Date().toISOString().split('T')[0],
    date_completed: '',
    parts_list: '',
    odometer_reading: ''
  });

  const [units, setUnits] = useState<Unit[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', color: 'success' });

  // Searchable Unit Selector
  const [unitSearch, setUnitSearch] = useState('');
  const [showUnitDrop, setShowUnitDrop] = useState(false);

  const loadData = useCallback(async () => {
    setLoading(true);
    try {
      const uRes = await getUnits();
      if (uRes.success) setUnits(uRes.data);

      if (isEdit && id) {
        const res = await getMaintenance(parseInt(id));
        if (res.success) {
          const m = res.data;
          setForm({
            unit_id: m.unit_id,
            maintenance_type: m.maintenance_type,
            status: m.status,
            description: m.description,
            cost: m.cost,
            mechanic_name: m.mechanic_name || '',
            date_started: m.date_started,
            date_completed: m.date_completed || '',
            parts_list: m.parts_list || '',
            odometer_reading: m.odometer_reading || ''
          });
          
          const u = uRes.data.find((x: any) => x.id === m.unit_id);
          if (u) setUnitSearch(`${u.plate_number} (${u.unit_number})`);
        }
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Failed to sync record components', color: 'danger' });
    } finally {
      setLoading(false);
    }
  }, [id, isEdit]);

  useEffect(() => { loadData(); }, [loadData]);

  const handleSave = async () => {
    if (!form.unit_id || !form.description || !form.cost || !form.date_started) {
      setToast({ show: true, message: 'Required fields missing', color: 'warning' });
      return;
    }
    setSaving(true);
    try {
      const res = isEdit 
        ? await updateMaintenance(parseInt(id!), form)
        : await createMaintenance(form);
      
      if (res.success) {
        setToast({ show: true, message: `Record ${isEdit ? 'updated' : 'created'}!`, color: 'success' });
        setTimeout(() => history.goBack(), 1000);
      } else {
        setToast({ show: true, message: res.message || 'Saving failed', color: 'danger' });
      }
    } catch (e: any) {
      setToast({ show: true, message: e.message, color: 'danger' });
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm('Delete this maintenance record?')) return;
    setLoading(true);
    try {
      await deleteMaintenance(parseInt(id!));
      history.goBack();
    } catch (e) {
      setToast({ show: true, message: 'Delete failed', color: 'danger' });
      setLoading(false);
    }
  };

  if (loading) return (
    <IonPage>
      <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
    </IonPage>
  );

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/app/maintenance" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? 'Update Maintenance' : 'Log Maintenance Task'}</div>
             <div className="header-modern-sub">{isEdit ? 'Modify existing repair logs, costs, and mechanic info' : 'Record a new preventive or corrective maintenance task'}</div>
          </div>
          <IonButtons slot="end">
            {isEdit && (
              <IonButton color="danger" onClick={handleDelete}><IonIcon icon={trashOutline} slot="icon-only" /></IonButton>
            )}
            <IonButton onClick={handleSave} disabled={saving}>
              {saving ? <IonSpinner name="dots" /> : <IonIcon icon={saveOutline} slot="icon-only" style={{ color: '#ca8a04' }} />}
            </IonButton>
          </IonButtons>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <div className="animate-in" style={{ padding: '16px' }}>
          
          <div className="section-title">Unit & Type</div>
          <IonCard className="form-card">
            <IonList lines="none">
              <IonItem>
                <div className="input-group" style={{ position: 'relative' }}>
                  <label><IonIcon icon={searchOutline} /> Select Unit</label>
                  <IonInput 
                    placeholder="Search Plate/Unit #"
                    value={unitSearch} 
                    onIonInput={(e: any) => { setUnitSearch(e.target.value); setShowUnitDrop(true); }}
                    onIonFocus={() => setShowUnitDrop(true)}
                  />
                  {showUnitDrop && (
                    <div className="custom-dropdown">
                        {units.filter(u => `${u.plate_number} ${u.unit_number}`.toLowerCase().includes(unitSearch.toLowerCase())).slice(0, 5).map(u => (
                            <div key={u.id} className="drop-item" onClick={() => { setForm({...form, unit_id: u.id}); setUnitSearch(`${u.plate_number} (${u.unit_number})`); setShowUnitDrop(false); }}>
                                <strong>{u.plate_number}</strong>
                                <span style={{ fontSize: '10px', color: '#64748b', marginLeft: '10px' }}>Unit {u.unit_number}</span>
                            </div>
                        ))}
                    </div>
                  )}
                </div>
              </IonItem>

              <IonItem>
                <IonGrid className="ion-no-padding">
                    <IonRow>
                        <IonCol size="6" style={{ paddingRight: 8 }}>
                            <div className="input-group">
                                <label><IonIcon icon={buildOutline} /> Repair Type</label>
                                <IonSelect value={form.maintenance_type} onIonChange={(e) => setForm({...form, maintenance_type: e.detail.value})}>
                                    <IonSelectOption value="preventive">Preventive</IonSelectOption>
                                    <IonSelectOption value="corrective">Corrective</IonSelectOption>
                                    <IonSelectOption value="emergency">Emergency</IonSelectOption>
                                </IonSelect>
                            </div>
                        </IonCol>
                        <IonCol size="6">
                            <div className="input-group">
                                <label><IonIcon icon={settingsOutline} /> Current Status</label>
                                <IonSelect value={form.status} onIonChange={(e) => setForm({...form, status: e.detail.value})}>
                                    <IonSelectOption value="pending">Pending</IonSelectOption>
                                    <IonSelectOption value="in_progress">In Progress</IonSelectOption>
                                    <IonSelectOption value="completed">Completed</IonSelectOption>
                                    <IonSelectOption value="cancelled">Cancelled</IonSelectOption>
                                </IonSelect>
                            </div>
                        </IonCol>
                    </IonRow>
                </IonGrid>
              </IonItem>
            </IonList>
          </IonCard>

          <div className="section-title" style={{ marginTop: 24 }}>Job Details</div>
          <IonCard className="form-card">
              <IonItem>
                <IonTextarea 
                    rows={3} 
                    placeholder="Describe the issue or maintenance work..." 
                    value={form.description}
                    onIonInput={(e: any) => setForm({...form, description: e.target.value})}
                />
              </IonItem>
              <IonItem>
                <IonGrid className="ion-no-padding">
                    <IonRow>
                        <IonCol size="6" style={{ paddingRight: 8, borderRight: '1px solid #f1f5f9' }}>
                            <div className="input-group">
                                <label><IonIcon icon={constructOutline} /> Mechanic Name</label>
                                <IonInput placeholder="Lead Mechanic" value={form.mechanic_name} onIonChange={(e: any) => setForm({...form, mechanic_name: e.detail.value})} />
                            </div>
                        </IonCol>
                        <IonCol size="6" style={{ paddingLeft: 8 }}>
                            <div className="input-group">
                                <label><IonIcon icon={calendarOutline} /> Est. Cost</label>
                                <IonInput type="number" placeholder="₱ 0" value={form.cost} onIonChange={(e: any) => setForm({...form, cost: e.detail.value})} style={{ color: '#16a34a', fontWeight: '900' }} />
                            </div>
                        </IonCol>
                    </IonRow>
                </IonGrid>
              </IonItem>
          </IonCard>

          <div className="section-title" style={{ marginTop: 24 }}>Timeline & Extras</div>
          <IonCard className="form-card">
              <IonItem>
                <IonGrid className="ion-no-padding">
                    <IonRow>
                        <IonCol size="6" style={{ paddingRight: 8 }}>
                            <div className="input-group">
                                <label><IonIcon icon={calendarOutline} /> Started</label>
                                <IonInput type="date" value={form.date_started} onIonChange={(e: any) => setForm({...form, date_started: e.detail.value})} />
                            </div>
                        </IonCol>
                        <IonCol size="6">
                            <div className="input-group">
                                <label><IonIcon icon={calendarOutline} /> Completed</label>
                                <IonInput type="date" value={form.date_completed} onIonChange={(e: any) => setForm({...form, date_completed: e.detail.value})} />
                            </div>
                        </IonCol>
                    </IonRow>
                </IonGrid>
              </IonItem>
              <IonItem>
                <div className="input-group">
                    <label><IonIcon icon={documentTextOutline} /> Parts Utilization</label>
                    <IonTextarea 
                        rows={3} 
                        placeholder="List of parts used or replaced..." 
                        value={form.parts_list}
                        onIonInput={(e: any) => setForm({...form, parts_list: e.target.value})}
                    />
                </div>
              </IonItem>
          </IonCard>

          <IonButton expand="block" className="btn-primary" style={{ marginTop: 32 }} onClick={handleSave} disabled={saving}>
            {saving ? <IonSpinner name="crescent" /> : (isEdit ? 'Update Record' : 'Confirm & Post Record')}
          </IonButton>

          <div style={{ height: 100 }} />
        </div>
      </IonContent>

      <IonToast
        isOpen={toast.show} message={toast.message} color={toast.color} duration={2000}
        onDidDismiss={() => setToast({ ...toast, show: false })}
      />

      <style>{`
        .section-title { font-size: 11px; font-weight: 900; color: #64748b; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 12px; }
        .form-card { margin: 0; border-radius: 18px; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .input-group { width: 100%; padding: 8px 0; }
        .input-group label { display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 800; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase; }
        .input-group label ion-icon { font-size: 14px; color: #3b82f6; }
        .input-group ion-input, .input-group ion-select, .input-group ion-textarea { --padding-start: 0; font-size: 14px; font-weight: 600; color: #1e293b; }
        .custom-dropdown { position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); max-height: 200px; overflow-y: auto; }
        .drop-item { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        .drop-item:active { background: #f8fafc; }
      `}</style>
    </IonPage>
  );
}
