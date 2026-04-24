import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonItem, IonLabel, IonRefresher, IonRefresherContent,
  IonFab, IonFabButton, IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonBadge, IonCard, IonSearchbar, IonButton, IonModal, IonGrid, IonRow, IonCol
} from '@ionic/react';
import { 
  addOutline, carSportOutline, warningOutline, personOutline, 
  radioButtonOnOutline, cashOutline, closeOutline, alertCircleOutline, 
  trendingUpOutline, mapOutline, timeOutline, shieldCheckmarkOutline, 
  colorPaletteOutline, peopleOutline, calendarOutline, funnelOutline,
  searchOutline, settingsOutline
} from 'ionicons/icons';
import { getUnits, createUnit, getDrivers } from '../api';
import { useHistory } from 'react-router-dom';

export default function Units() {
  const [units, setUnits] = useState<any[]>([]);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [sortBy, setSortBy] = useState('plate-asc');
  
  const [showAddModal, setShowAddModal] = useState(false);
  const [drivers, setDrivers] = useState<any[]>([]);
  const [form, setForm] = useState({
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
    driver_id: '',
    secondary_driver_id: '',
    coding_day: '',
    next_coding_date: '',
    days_until: ''
  });

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const history = useHistory();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const [uRes, dRes] = await Promise.all([getUnits(), getDrivers()]);
      if (uRes.success) {
        const data = Array.isArray(uRes.data) ? uRes.data : [];
        setUnits(data);
        setFiltered(data);
      } else {
        setError(uRes.message || 'Failed to sync fleet units');
      }
      if (dRes.success) setDrivers(dRes.data);
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    let list = Array.isArray(units) ? [...units] : [];
    if (statusFilter !== 'all') {
      list = list.filter(u => u?.status?.toLowerCase() === statusFilter.toLowerCase());
    }
    if (search.trim()) {
      const q = search.toLowerCase();
      list = list.filter((u: any) =>
        (u?.plate_number || '').toString().toLowerCase().includes(q) ||
        (u?.driver_name || '').toLowerCase().includes(q)
      );
    }
    list.sort((a, b) => {
      if (sortBy === 'plate-asc') return (a.plate_number || '').localeCompare(b.plate_number || '');
      if (sortBy === 'plate-desc') return (b.plate_number || '').localeCompare(a.plate_number || '');
      return 0;
    });
    setFiltered(list);
  }, [search, units, statusFilter, sortBy]);

  const calculateCodingInfo = (plate: string) => {
    if (!plate) return { day: '', nextDate: '', daysUntil: '' };
    const lastDigit = parseInt(plate.replace(/\s/g, '').slice(-1));
    if (isNaN(lastDigit)) return { day: '', nextDate: '', daysUntil: '' };
    
    let dayIndex = 0; let dayName = '';
    if (lastDigit === 1 || lastDigit === 2) { dayIndex = 1; dayName = 'Monday'; }
    else if (lastDigit === 3 || lastDigit === 4) { dayIndex = 2; dayName = 'Tuesday'; }
    else if (lastDigit === 5 || lastDigit === 6) { dayIndex = 3; dayName = 'Wednesday'; }
    else if (lastDigit === 7 || lastDigit === 8) { dayIndex = 4; dayName = 'Thursday'; }
    else if (lastDigit === 9 || lastDigit === 0) { dayIndex = 5; dayName = 'Friday'; }
    else return { day: '', nextDate: '', daysUntil: '' };

    const today = new Date(); today.setHours(0,0,0,0);
    const resDate = new Date(today);
    let daysToAdd = (dayIndex + 7 - today.getDay()) % 7;
    if (daysToAdd === 0) daysToAdd = 7; 
    resDate.setDate(today.getDate() + daysToAdd);
    
    return {
      day: dayName,
      nextDate: resDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      daysUntil: `${Math.ceil(Math.abs(resDate.getTime() - today.getTime()) / (1000*60*60*24))} days`
    };
  };

  const handlePlateChange = (val: string) => {
    const upper = val.toUpperCase();
    const info = calculateCodingInfo(upper);
    setForm({ ...form, plate_number: upper, coding_day: info.day, next_coding_date: info.nextDate, days_until: info.daysUntil });
  };

  const handleSaveUnit = async () => {
    if (!form.plate_number) return;
    setSaving(true);
    try {
      const res = await createUnit({ ...form, unit_number: form.plate_number, boundary_rate: parseFloat(form.boundary_rate)||0, purchase_cost: parseFloat(form.purchase_cost)||0, year: parseInt(form.year)||2026 });
      if (res.success) { setShowAddModal(false); load(); }
    } catch (e) { console.error(e); } finally { setSaving(false); }
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>Fleet Inventory</div>
            <div className="header-modern-sub">Asset management & vehicle status tracking</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* SEARCH & FILTERS GLASS CARD */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search plate or unit number..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
             </div>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <div style={{ position: 'relative' }}>
                   <IonIcon icon={funnelOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 35px 0 12px', fontSize: '12px', fontWeight: 800, appearance: 'none', outline: 'none' }}
                      value={statusFilter}
                      onChange={(e) => setStatusFilter(e.target.value)}
                   >
                      <option value="all">All Status</option>
                      <option value="active">Active</option>
                      <option value="maintenance">Maintenance</option>
                      <option value="coding">Coding</option>
                   </select>
                </div>
                <div style={{ position: 'relative' }}>
                   <IonIcon icon={settingsOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 35px 0 12px', fontSize: '12px', fontWeight: 800, appearance: 'none', outline: 'none' }}
                      value={sortBy}
                      onChange={(e) => setSortBy(e.target.value)}
                   >
                      <option value="plate-asc">Plate A-Z</option>
                      <option value="plate-desc">Plate Z-A</option>
                   </select>
                </div>
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Active Fleet List</div>
             <IonButton fill="clear" size="small" onClick={() => setShowAddModal(true)} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> NEW VEHICLE
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !units.length ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {filtered.map((u: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '20px', borderRadius: '24px' }} onClick={() => history.push(`/app/units/${u.id}/detail`)}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                           <div style={{ width: '48px', height: '48px', borderRadius: '16px', background: '#f8fafc', color: '#ca8a04', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }}>
                              <IonIcon icon={carSportOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '16px', fontWeight: 900, color: '#0f172a' }}>{u.plate_number}</div>
                              <div style={{ fontSize: '11px', color: '#64748b', fontWeight: 700 }}>UNIT #{u.id} | {u.model}</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '9px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: u.status?.toLowerCase() === 'active' ? '#dcfce7' : (u.status?.toLowerCase() === 'maintenance' ? '#fef9c3' : '#fee2e2'),
                           color: u.status?.toLowerCase() === 'active' ? '#166534' : (u.status?.toLowerCase() === 'maintenance' ? '#854d0e' : '#991b1b')
                        }}>
                           {(u.status || 'ACTIVE').toUpperCase()}
                        </div>
                     </div>
                     
                     <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', background: '#f8fafc', padding: '12px', borderRadius: '16px', marginBottom: '12px' }}>
                        <div>
                           <div className="executive-label" style={{ fontSize: '8px' }}>Primary Driver</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>{u.driver_name || 'Unassigned'}</div>
                        </div>
                        <div style={{ borderLeft: '1px solid #e2e8f0', paddingLeft: '12px' }}>
                           <div className="executive-label" style={{ fontSize: '8px' }}>Sec. Driver</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>{u.secondary_driver_name || 'None'}</div>
                        </div>
                     </div>

                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', gap: '6px' }}>
                           <div style={{ fontSize: '9px', fontWeight: 800, padding: '2px 8px', borderRadius: '6px', background: '#eff6ff', color: '#3b82f6' }}>{u.year}</div>
                           <div style={{ fontSize: '9px', fontWeight: 800, padding: '2px 8px', borderRadius: '6px', background: '#f1f5f9', color: '#64748b' }}>{u.color}</div>
                        </div>
                        <div style={{ fontSize: '14px', fontWeight: 900, color: '#0f172a' }}>₱{u.boundary_rate?.toLocaleString()}</div>
                     </div>
                  </div>
               ))}
               {filtered.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={carSportOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No units found in fleet.</p>
                  </div>
               )}
            </div>
          )}

        </div>

        {/* REGISTRATION MODAL */}
        <IonModal isOpen={showAddModal} onDidDismiss={() => setShowAddModal(false)} initialBreakpoint={0.9} breakpoints={[0, 0.9]}>
           <div className="modal-content-modern" style={{ padding: '24px', background: '#fff', height: '100%', overflowY: 'auto' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
                 <div>
                    <div className="executive-label">Fleet Registration</div>
                    <div style={{ fontSize: '20px', fontWeight: 900, color: '#0f172a' }}>Register New Vehicle</div>
                 </div>
                 <IonButton fill="clear" color="dark" onClick={() => setShowAddModal(false)}><IonIcon icon={closeOutline} /></IonButton>
              </div>

              <div className="modern-field" style={{ marginBottom: '20px' }}>
                 <label>Plate Number</label>
                 <input type="text" placeholder="e.g. ABC 1234" value={form.plate_number} onChange={(e) => handlePlateChange(e.target.value)} style={{ paddingLeft: '16px' }} />
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '20px' }}>
                 <div className="modern-field">
                    <label>Make</label>
                    <input type="text" placeholder="Toyota" value={form.make} onChange={(e) => setForm({...form, make: e.target.value})} />
                 </div>
                 <div className="modern-field">
                    <label>Model</label>
                    <input type="text" placeholder="Vios" value={form.model} onChange={(e) => setForm({...form, model: e.target.value})} />
                 </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                 <div className="modern-field">
                    <label>Boundary Rate</label>
                    <input type="number" value={form.boundary_rate} onChange={(e) => setForm({...form, boundary_rate: e.target.value})} />
                 </div>
                 <div className="modern-field">
                    <label>Year Model</label>
                    <input type="number" value={form.year} onChange={(e) => setForm({...form, year: e.target.value})} />
                 </div>
              </div>

              <div className="glass-card" style={{ padding: '16px', borderRadius: '16px', background: '#f8fafc', border: '1px solid #e2e8f0', marginBottom: '24px' }}>
                 <div className="executive-label" style={{ marginBottom: '12px' }}>MMDA Coding Prediction</div>
                 <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                    <div>
                       <div style={{ fontSize: '9px', fontWeight: 800, color: '#64748b' }}>Assigned Day</div>
                       <div style={{ fontSize: '13px', fontWeight: 900, color: '#ca8a04' }}>{form.coding_day || '---'}</div>
                    </div>
                    <div>
                       <div style={{ fontSize: '9px', fontWeight: 800, color: '#64748b' }}>Next Restriction</div>
                       <div style={{ fontSize: '13px', fontWeight: 900, color: '#1e293b' }}>{form.next_coding_date || '---'}</div>
                    </div>
                 </div>
              </div>

              <div style={{ display: 'flex', gap: '12px', marginTop: '10px' }}>
                 <button 
                   style={{ flex: 1, height: '54px', borderRadius: '16px', background: '#ca8a04', color: '#fff', fontWeight: 900, border: 'none', fontSize: '15px' }}
                   onClick={handleSaveUnit}
                   disabled={saving || !form.plate_number}
                 >
                    {saving ? <IonSpinner name="crescent" /> : 'Confirm & Register Asset'}
                 </button>
              </div>
           </div>
        </IonModal>

      </IonContent>
    </IonPage>
  );
}
