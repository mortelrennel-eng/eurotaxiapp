import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonItem, IonLabel, IonRefresher, IonRefresherContent,
  IonFab, IonFabButton, IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonBadge, IonCard, IonSearchbar, IonButton, IonModal, IonAlert, IonGrid, IonRow, IonCol
} from '@ionic/react';
import { 
  addOutline, personOutline, warningOutline, callOutline, carSportOutline, 
  star, starOutline, ellipsisVertical, createOutline, trashOutline, 
  calendarOutline, idCardOutline, walletOutline, fitnessOutline,
  closeOutline, peopleOutline, eyeOutline, ribbonOutline,
  statsChartOutline, sparklesOutline, searchOutline, funnelOutline
} from 'ionicons/icons';
import { getDrivers, deleteDriver } from '../api';
import { useHistory } from 'react-router-dom';

export default function Drivers() {
  const [drivers, setDrivers] = useState<any[]>([]);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedDriver, setSelectedDriver] = useState<any | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [showDeleteAlert, setShowDeleteAlert] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [activeTab, setActiveTab] = useState('basic');
  const history = useHistory();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getDrivers();
      if (res && res.success) {
        const data = Array.isArray(res.data) ? res.data : [];
        setDrivers(data);
        setFiltered(data);
      } else {
        setError(res?.message || 'Failed to sync driver list.');
      }
    } catch (e: any) { 
      setError(e?.message || 'Cannot connect to server.');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    const list = Array.isArray(drivers) ? drivers : [];
    if (!search.trim()) { setFiltered(list); return }
    const q = search.toLowerCase()
    setFiltered(list.filter((d: any) =>
      (d?.name || '').toLowerCase().includes(q) ||
      (d?.unit?.plate_number || '').toLowerCase().includes(q) ||
      (d?.license || '').toLowerCase().includes(q)
    ))
  }, [search, drivers])

  const renderStars = (rating: number = 4.8) => {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
      stars.push(
        <IonIcon key={i} icon={i <= Math.floor(rating) ? star : starOutline} style={{ color: '#fbbf24', fontSize: '12px' }} />
      );
    }
    return <div style={{ display: 'flex', gap: '2px' }}>{stars}</div>;
  };

  const handleDelete = async () => {
    if (!selectedDriver) return;
    setDeleting(true);
    try {
      const res = await deleteDriver(selectedDriver.id);
      if (res.success) { setShowModal(false); load(); }
    } catch (e) { console.error(e); } finally { setDeleting(false); setShowDeleteAlert(false); }
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-blue"></span>Personnel Fleet</div>
            <div className="header-modern-sub">Manage drivers, vetting & performance</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* SEARCH GLASS CARD */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search by name, license or unit..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Authorized Drivers</div>
             <IonButton fill="clear" size="small" onClick={() => history.push('/app/drivers/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> ADD DRIVER
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !drivers.length ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {filtered.map((d: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '20px', borderRadius: '24px' }} onClick={() => { setSelectedDriver(d); setShowModal(true); }}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                           <div style={{ width: '48px', height: '48px', borderRadius: '16px', background: '#eff6ff', color: '#3b82f6', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px', fontWeight: 900 }}>
                              {d.name?.charAt(0)}
                           </div>
                           <div>
                              <div style={{ fontSize: '15px', fontWeight: 900, color: '#0f172a' }}>{d.name}</div>
                              <div style={{ fontSize: '10px', color: '#64748b', fontWeight: 700 }}>VERIFIED PERSONNEL</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '9px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: d.driver_status?.toLowerCase() === 'available' ? '#dcfce7' : '#f1f5f9',
                           color: d.driver_status?.toLowerCase() === 'available' ? '#166534' : '#64748b'
                        }}>
                           {(d.driver_status || 'OFF-DUTY').toUpperCase()}
                        </div>
                     </div>
                     
                     <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', background: '#f8fafc', padding: '12px', borderRadius: '16px', marginBottom: '12px' }}>
                        <div>
                           <div className="executive-label" style={{ fontSize: '8px' }}>Assigned Unit</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>{d.assigned_plate || 'None'}</div>
                        </div>
                        <div style={{ borderLeft: '1px solid #e2e8f0', paddingLeft: '12px' }}>
                           <div className="executive-label" style={{ fontSize: '8px' }}>License Status</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#16a34a' }}>VALID</div>
                        </div>
                     </div>

                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', gap: '4px' }}>{renderStars(4.8)}</div>
                        <div style={{ fontSize: '11px', fontWeight: 800, color: '#94a3b8' }}>Rank: <span style={{ color: '#ca8a04' }}>Gold Driver</span></div>
                     </div>
                  </div>
               ))}
            </div>
          )}

        </div>

        {/* DRIVER DETAIL MODAL */}
        <IonModal isOpen={showModal} onDidDismiss={() => setShowModal(false)} initialBreakpoint={0.75} breakpoints={[0, 0.75, 0.9]}>
           <div className="modal-content-modern" style={{ padding: '24px', background: '#fff', height: '100%', overflowY: 'auto' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
                 <div style={{ display: 'flex', gap: '15px', alignItems: 'center' }}>
                    <div style={{ width: '56px', height: '56px', borderRadius: '18px', background: '#ca8a04', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px', fontWeight: 900 }}>{selectedDriver?.name?.charAt(0)}</div>
                    <div>
                       <div style={{ fontSize: '20px', fontWeight: 900, color: '#0f172a' }}>{selectedDriver?.name}</div>
                       <div style={{ fontSize: '11px', color: '#64748b', fontWeight: 700 }}>ID: #DRV-{selectedDriver?.id}</div>
                    </div>
                 </div>
                 <IonButton fill="clear" color="dark" onClick={() => setShowModal(false)}><IonIcon icon={closeOutline} /></IonButton>
              </div>

              <div style={{ display: 'flex', overflowX: 'auto', gap: '20px', marginBottom: '24px', borderBottom: '1px solid #f1f5f9' }}>
                 {['basic', 'license', 'performance'].map(t => (
                    <div key={t} className={`tab-item ${activeTab === t ? 'active' : ''}`} onClick={() => setActiveTab(t)} style={{ fontSize: '12px', fontWeight: 800, paddingBottom: '12px', color: activeTab === t ? '#ca8a04' : '#94a3b8', borderBottom: activeTab === t ? '2px solid #ca8a04' : 'none', textTransform: 'uppercase', cursor: 'pointer' }}>{t}</div>
                 ))}
              </div>

              <div className="animate-in">
                 {activeTab === 'basic' && (
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                       <div>
                          <div className="executive-label">Phone</div>
                          <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selectedDriver?.phone || '---'}</div>
                       </div>
                       <div>
                          <div className="executive-label">Unit</div>
                          <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selectedDriver?.assigned_plate || 'Unassigned'}</div>
                       </div>
                       <div>
                          <div className="executive-label">Daily Boundary</div>
                          <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>₱1,100.00</div>
                       </div>
                       <div>
                          <div className="executive-label">Join Date</div>
                          <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selectedDriver?.hire_date || 'N/A'}</div>
                       </div>
                    </div>
                 )}
                 {activeTab === 'license' && (
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '20px' }}>
                       <div>
                          <div className="executive-label">License Number</div>
                          <div style={{ fontSize: '18px', fontWeight: 900, color: '#0f172a' }}>{selectedDriver?.license_number || '---'}</div>
                       </div>
                       <div>
                          <div className="executive-label">Expiry Date</div>
                          <div style={{ fontSize: '14px', fontWeight: 800, color: '#ef4444' }}>{selectedDriver?.license_expiry || '---'}</div>
                       </div>
                    </div>
                 )}
                 {activeTab === 'performance' && (
                    <div style={{ textAlign: 'center', padding: '30px 0' }}>
                       <IonIcon icon={sparklesOutline} style={{ fontSize: '48px', color: '#ca8a04', opacity: 0.2, marginBottom: '12px' }} />
                       <p style={{ fontSize: '12px', color: '#64748b' }}>Driver performance analytics and risk assessment will appear as records grow.</p>
                    </div>
                 )}
              </div>

              <div style={{ display: 'flex', gap: '12px', marginTop: '40px' }}>
                 <button 
                    style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#3b82f6', color: '#fff', fontWeight: 800, border: 'none', fontSize: '14px' }}
                    onClick={() => { setShowModal(false); history.push(`/app/drivers/${selectedDriver.id}/edit`); }}
                 >Edit Profile</button>
                 <button 
                    style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#fef2f2', color: '#ef4444', fontWeight: 800, border: 'none', fontSize: '14px' }}
                    onClick={() => setShowDeleteAlert(true)}
                 >Delete</button>
              </div>
           </div>
        </IonModal>

        <IonAlert
          isOpen={showDeleteAlert}
          onDidDismiss={() => setShowDeleteAlert(false)}
          header="Confirm Deletion"
          message={`Delete ${selectedDriver?.name}?`}
          buttons={[{ text: 'Cancel', role: 'cancel' }, { text: 'Delete', cssClass: 'danger-alert-btn', handler: handleDelete }]}
        />
      </IonContent>
    </IonPage>
  );
}
