import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonItem, IonLabel, IonInput, IonSelect, IonSelectOption,
  IonButton, IonButtons, IonBackButton, IonSpinner, IonToast,
  IonIcon, IonCard, IonNote, IonGrid, IonRow, IonCol, IonBadge, IonFooter
} from '@ionic/react';
import { 
  saveOutline, cashOutline, personOutline, 
  calendarOutline, alertCircleOutline, searchOutline,
  checkmarkCircleOutline, warningOutline, trashOutline, printOutline,
  chevronBackOutline
} from 'ionicons/icons';
import { useParams, useHistory } from 'react-router-dom';
import { 
  getBoundary, createBoundary, updateBoundary, deleteBoundary,
  getUnits, getDrivers, Unit, Driver, getCoding 
} from '../api';

export default function BoundaryForm() {
  const { id } = useParams<{ id?: string }>();
  const isEdit = !!id;
  const history = useHistory();

  const [form, setForm] = useState<any>({
    unit_id: '',
    driver_id: '',
    date: new Date().toISOString().split('T')[0],
    boundary_amount: 1100,
    actual_boundary: 0,
    shortage: 0,
    excess: 0,
    status: 'pending',
    notes: ''
  });

  const [units, setUnits] = useState<Unit[]>([]);
  const [drivers, setDrivers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', color: 'success' });

  const [unitSearch, setUnitSearch] = useState('');
  const [driverSearch, setDriverSearch] = useState('');
  const [showUnitDrop, setShowUnitDrop] = useState(false);
  const [showDriverDrop, setShowDriverDrop] = useState(false);

  const calculateBalances = (target: number, actual: number) => {
    const shortage = Math.max(0, target - actual);
    const excess = Math.max(0, actual - target);
    let status = 'paid';
    if (shortage > 0) status = 'shortage';
    else if (excess > 0) status = 'excess';
    return { shortage, excess, status };
  };

  const autoCalcBoundary = (baseRate: number, codingDay: string, dateStr: string) => {
    if (!dateStr || !baseRate) return baseRate;
    const date = new Date(dateStr);
    const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const dayName = dayNames[date.getDay()];
    
    let adjusted = baseRate;
    if (codingDay && codingDay.toLowerCase() === dayName) {
      adjusted = baseRate * 0.5;
    } else if (date.getDay() === 6) { // Sat
      adjusted = baseRate - 100;
    } else if (date.getDay() === 0) { // Sun
      adjusted = baseRate - 200;
    }
    return Math.max(0, adjusted);
  };

  const loadData = useCallback(async () => {
    setLoading(true);
    try {
      const [uRes, dRes] = await Promise.all([getUnits(), getDrivers()]);
      if (uRes.success) setUnits(uRes.data);
      if (dRes.success) setDrivers(dRes.data);

      if (isEdit && id) {
        const bRes = await getBoundary(parseInt(id));
        if (bRes.success) {
            const b = bRes.data;
            setForm({
                unit_id: b.unit_id,
                driver_id: b.driver_id,
                date: b.date,
                boundary_amount: b.boundary_amount,
                actual_boundary: b.actual_boundary,
                shortage: b.shortage,
                excess: b.excess,
                status: b.status,
                notes: b.notes || ''
            });
            const u = uRes.data.find((x: any) => x.id === b.unit_id);
            if (u) setUnitSearch(`${u.plate_number} (${u.unit_number})`);
            const d = dRes.data.find((x: any) => x.id === b.driver_id);
            if (d) setDriverSearch(d.name || d.full_name);
        }
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Sync failed', color: 'danger' });
    } finally {
      setLoading(false);
    }
  }, [id, isEdit]);

  useEffect(() => { loadData(); }, [loadData]);

  const handleUnitSelect = (u: Unit) => {
    const target = autoCalcBoundary(u.boundary_rate, u.coding_day, form.date);
    const balances = calculateBalances(target, form.actual_boundary);
    setForm({ 
        ...form, 
        unit_id: u.id, 
        boundary_amount: target,
        ...balances
    });
    setUnitSearch(`${u.plate_number} (${u.unit_number})`);
    setShowUnitDrop(false);
    
    if (u.driver_id) {
        const d = drivers.find(x => x.user_id === u.driver_id || x.id === u.driver_id);
        if (d) {
            setForm((prev: any) => ({ ...prev, driver_id: d.id }));
            setDriverSearch(d.name || d.full_name);
        }
    }
  };

  const handleActualChange = (val: number) => {
    const balances = calculateBalances(form.boundary_amount, val);
    setForm({ ...form, actual_boundary: val, ...balances });
  };

  const handleDateChange = (val: string) => {
    const u = units.find(x => x.id === form.unit_id);
    const target = u ? autoCalcBoundary(u.boundary_rate, u.coding_day, val) : form.boundary_amount;
    const balances = calculateBalances(target, form.actual_boundary);
    setForm({ ...form, date: val, boundary_amount: target, ...balances });
  };

  const handleSave = async () => {
    if (!form.unit_id || !form.driver_id) {
        setToast({ show: true, message: 'Unit and Driver required', color: 'warning' });
        return;
    }
    setSaving(true);
    try {
      const res = isEdit 
        ? await updateBoundary(parseInt(id!), form)
        : await createBoundary(form);
      
      if (res.success) {
        setToast({ show: true, message: 'Remittance Posted!', color: 'success' });
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

  const handleDelete = async () => {
    if (!window.confirm('Archive this collection record?')) return;
    setLoading(true);
    try {
        await deleteBoundary(parseInt(id!));
        history.goBack();
    } catch (e) {
        setToast({ show: true, message: 'Delete failed', color: 'danger' });
        setLoading(false);
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
            <IonBackButton defaultHref="/app/boundaries" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? 'Update Settlement' : 'New Boundary Entry'}</div>
             <div className="header-modern-sub">{isEdit ? 'Modify existing boundary collection and balances' : 'Record a new daily boundary payment for a unit'}</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent style={{ '--background': '#f1f5f9' }}>
        <div className="animate-in" style={{ padding: '20px' }}>
          
          <div className="section-mockup-label" style={{ color: '#64748b' }}>SETTLEMENT INFO</div>
          <IonCard className="mockup-form-card" mode="ios" style={{ marginBottom: '24px' }}>
             <div className="mockup-field">
                <label><IonIcon icon={calendarOutline} style={{ color: '#3b82f6', opacity: 0.6 }} /> COLLECTION DATE</label>
                <IonInput 
                  type="date"
                  value={form.date} 
                  onIonChange={(e: any) => handleDateChange(e.detail.value)}
                  className="mockup-input-text"
                />
             </div>
             <div className="input-divider" />
             <div className="mockup-field" style={{ position: 'relative' }}>
                <label><IonIcon icon={searchOutline} style={{ color: '#3b82f6', opacity: 0.6 }} /> SELECT UNIT</label>
                <IonInput 
                  placeholder="Search Plate/Unit #"
                  value={unitSearch} 
                  onIonInput={(e: any) => { setUnitSearch(e.target.value); setShowUnitDrop(true); }}
                  onIonFocus={() => setShowUnitDrop(true)}
                  className="mockup-input-text"
                />
                {showUnitDrop && (
                    <div className="mockup-dropdown">
                        {units.filter(u => `${u.plate_number} ${u.unit_number}`.toLowerCase().includes(unitSearch.toLowerCase())).slice(0, 5).map(u => (
                            <div key={u.id} className="mockup-drop-item" onClick={() => handleUnitSelect(u)}>
                                <strong>{u.plate_number}</strong>
                                <span>Unit {u.unit_number}</span>
                            </div>
                        ))}
                    </div>
                )}
             </div>
             <div className="input-divider" />
             <div className="mockup-field" style={{ position: 'relative' }}>
                <label><IonIcon icon={personOutline} style={{ color: '#3b82f6', opacity: 0.6 }} /> DRIVER</label>
                <IonInput 
                  placeholder="Search Driver Name"
                  value={driverSearch} 
                  onIonInput={(e: any) => { setDriverSearch(e.target.value); setShowDriverDrop(true); }}
                  onIonFocus={() => setShowDriverDrop(true)}
                  className="mockup-input-text"
                />
                {showDriverDrop && (
                    <div className="mockup-dropdown">
                        {drivers.filter(d => (d.name || d.full_name).toLowerCase().includes(driverSearch.toLowerCase())).slice(0, 5).map(d => (
                            <div key={d.id} className="mockup-drop-item" onClick={() => { setForm({...form, driver_id: d.id}); setDriverSearch(d.name || d.full_name); setShowDriverDrop(false); }}>
                                <strong>{d.name || d.full_name}</strong>
                                {d.assigned_plate && <span className="assigned-tag">{d.assigned_plate}</span>}
                            </div>
                        ))}
                    </div>
                )}
             </div>
          </IonCard>

          <div className="section-mockup-label" style={{ color: '#64748b' }}>AMOUNT BREAKDOWN</div>
          <IonCard className="mockup-form-card amount-card" mode="ios" style={{ borderLeft: '5px solid #3b82f6', marginBottom: '24px' }}>
             <IonGrid className="ion-no-padding">
                <IonRow>
                   <IonCol size="6" className="mockup-field">
                      <label style={{ color: '#94a3b8' }}>TARGET BOUNDARY</label>
                      <IonInput 
                         type="number" 
                         value={form.boundary_amount} 
                         onIonChange={(e: any) => {
                             const target = parseFloat(e.detail.value)||0;
                             const balances = calculateBalances(target, form.actual_boundary);
                             setForm({...form, boundary_amount: target, ...balances});
                         }}
                         className="mockup-input-bold"
                      />
                   </IonCol>
                   <IonCol size="6" className="mockup-field">
                      <label style={{ color: '#10b981' }}>ACTUAL PAYMENT</label>
                      <IonInput 
                         type="number" 
                         value={form.actual_boundary} 
                         onIonChange={(e: any) => handleActualChange(parseFloat(e.detail.value)||0)}
                         className="mockup-input-bold val-green"
                      />
                   </IonCol>
                </IonRow>
             </IonGrid>
          </IonCard>

          <div className="section-mockup-label" style={{ color: '#64748b' }}>REMARKS</div>
          <IonCard className="mockup-form-card" mode="ios" style={{ marginBottom: '30px' }}>
             <div className="mockup-field">
                <IonInput 
                    placeholder="Input notes or issues here..."
                    value={form.notes}
                    onIonInput={(e: any) => setForm({...form, notes: e.target.value})}
                    className="mockup-input-text"
                />
             </div>
          </IonCard>

          <button className="btn-mockup-primary" onClick={handleSave} disabled={saving}>
             {saving ? <IonSpinner name="dots" color="light" /> : (isEdit ? 'UPDATE COLLECTION' : 'CONFIRM & POST REMITTANCE')}
          </button>

          {isEdit && (
             <IonButton fill="clear" color="danger" expand="block" style={{ marginTop: '15px', fontSize: '11px', fontWeight: '800' }} onClick={handleDelete}>
                <IonIcon icon={trashOutline} slot="start" /> ARCHIVE COLLECTION RECORD
             </IonButton>
          )}

          <div style={{ height: 100 }} />
        </div>
      </IonContent>

      <IonToast
        isOpen={toast.show} message={toast.message} color={toast.color} duration={2000}
        onDidDismiss={() => setToast({ ...toast, show: false })}
      />

      <style>{`
        .section-mockup-label { font-size: 11px; font-weight: 900; color: #64748b; letter-spacing: 0.05em; margin-bottom: 8px; font-family: 'Inter', sans-serif; }
        .mockup-form-card { margin: 0; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; background: #fff; overflow: hidden; }
        .mockup-field { padding: 15px 20px; }
        .mockup-field label { display: flex; align-items: center; gap: 8px; font-size: 9px; font-weight: 900; color: #94a3b8; margin-bottom: 6px; }
        .mockup-input-text { --padding-start: 0; font-size: 16px; font-weight: 800; color: #0f172a; --padding-top: 0; --padding-bottom: 0; }
        .mockup-input-bold { --padding-start: 0; font-size: 20px; font-weight: 900; color: #0f172a; --padding-top: 0; --padding-bottom: 0; }
        .mockup-input-bold.val-green { color: #10b981; }
        .input-divider { height: 1px; background: #f1f5f9; width: 100%; margin: 0 20px; }

        .mockup-dropdown { position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.12); max-height: 200px; overflow-y: auto; margin-top: 5px; }
        .mockup-drop-item { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .mockup-drop-item strong { font-size: 14px; color: #0f172a; }
        .mockup-drop-item span { font-size: 11px; color: #64748b; font-weight: 700; }
        .assigned-tag { background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 5px; font-size: 9px; font-weight: 900; }

        .btn-mockup-primary { width: 100%; height: 50px; border-radius: 14px; border: none; background: #3b82f6; color: #fff; font-size: 14px; font-weight: 900; letter-spacing: 0.02em; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3); outline: none; transition: 0.2s; }
        .btn-mockup-primary:active { transform: scale(0.97); }

        .loading-center { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; }
        .animate-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </IonPage>
  );
}
