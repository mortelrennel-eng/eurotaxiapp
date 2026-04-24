import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonItem, IonLabel, IonRefresher, IonRefresherContent,
  IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonBadge, IonCard, IonSearchbar, IonButton, IonGrid, IonRow, IonCol,
  IonSegment, IonSegmentButton
} from '@ionic/react';
import { 
  addOutline, personCircleOutline, warningOutline, shieldCheckmarkOutline, 
  mailOutline, chevronForwardOutline, funnelOutline, peopleOutline
} from 'ionicons/icons';
import { getStaff } from '../api';
import { useHistory } from 'react-router-dom';

// Diagnostic touch to trigger TypeScript refresh
export default function Staff() {
  const [data, setData] = useState<any>({ admin: [], general: [], stats: {} });
  const [adminFiltered, setAdminFiltered] = useState<any[]>([]);
  const [generalFiltered, setGeneralFiltered] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [viewMode, setViewMode] = useState<'admin' | 'general'>('admin');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const history = useHistory();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getStaff();
      if (res.success) {
        const payload = res.data || { admin: [], general: [], stats: {} };
        setData(payload);
        setAdminFiltered(payload.admin || []);
        setGeneralFiltered(payload.general || []);
      } else {
        setError(res.message || 'Failed to sync staff records');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    if (!search.trim()) {
      setAdminFiltered(data.admin || []);
      setGeneralFiltered(data.general || []);
      return;
    }
    const q = search.toLowerCase();
    setAdminFiltered((data.admin || []).filter((s: any) =>
      (s?.name || '').toLowerCase().includes(q) || (s?.role || '').toLowerCase().includes(q)
    ));
    setGeneralFiltered((data.general || []).filter((s: any) =>
      (s?.name || '').toLowerCase().includes(q) || (s?.role || '').toLowerCase().includes(q)
    ));
  }, [search, data]);

  const stats = data.stats || {};

  const renderStaffCard = (s: any, isGeneral: boolean) => (
    <IonCard key={s.id} className="boundary-card animate-in" onClick={() => isGeneral ? history.push(`/app/staff/${s.id}/edit`) : null}>
       <div style={{ display: 'flex', padding: '18px', gap: '15px', alignItems: 'center' }}>
          <div style={{
             width: 48, height: 48, borderRadius: '16px',
             background: isGeneral ? '#f8fafc' : '#eff6ff', 
             border: `1px solid ${isGeneral ? '#e2e8f0' : '#dbeafe'}`,
             display: 'flex', alignItems: 'center', justifyContent: 'center',
             fontSize: '20px', color: isGeneral ? '#64748b' : '#3b82f6',
             fontWeight: 'bold'
          }}>
             {s?.name?.charAt(0).toUpperCase()}
          </div>
          <div style={{ flex: 1 }}>
             <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div style={{ fontSize: '16px', fontWeight: '900', color: '#0f172a' }}>{s?.name || '---'}</div>
                <span className={`status-pill ${s.status === 'active' ? 'status-active' : 'status-danger'}`}>
                   {(s?.status || 'Active').toUpperCase()}
                </span>
             </div>
             <div style={{ fontSize: '12px', fontWeight: '700', color: isGeneral ? '#64748b' : '#3b82f6', marginBottom: '8px' }}>
                {s?.role || 'Staff'}
             </div>
             <div style={{ borderTop: '1px solid #f1f5f9', paddingTop: '10px', marginTop: '5px', display: 'flex', justifyContent: 'space-between', fontSize: '11px', color: '#94a3b8' }}>
                <span style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                   <IonIcon icon={isGeneral ? personCircleOutline : shieldCheckmarkOutline} style={{ fontSize: '14px' }} /> 
                   {isGeneral ? 'Personnel' : 'Admin'}
                </span>
                <span style={{ fontWeight: '800', color: '#475569' }}>{s?.phone || 'No Phone'}</span>
             </div>
          </div>
          {isGeneral && <IonIcon icon={chevronForwardOutline} style={{ color: '#cbd5e1', fontSize: '20px' }} />}
       </div>
    </IonCard>
  );

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">Personnel & Staff Management</div>
             <div className="header-modern-sub">Manage administrative roles and general staff records</div>
          </div>
        </IonToolbar>

        {/* PREMIUM WHITE SEARCHBAR */}
        <div style={{ padding: '0 16px 16px 16px', background: '#fff' }}>
           <div className="search-container-modern">
              <IonIcon icon={peopleOutline} className="search-icon-deco" />
              <input 
                 type="text" 
                 className="modern-input" 
                 placeholder="Search staff by name or role..." 
                 value={search}
                 onChange={(e) => setSearch(e.target.value)}
              />
           </div>
        </div>

        <IonToolbar style={{ '--background': '#fff', '--min-height': 'auto', paddingBottom: '8px' }}>
          <IonSegment 
            value={viewMode} 
            onIonChange={(e: any) => setViewMode(e.detail.value)} 
            mode="md" 
            style={{ background: '#f1f5f9', borderRadius: '12px', margin: '0 16px' }}
          >
            <IonSegmentButton value="admin" style={{ '--indicator-color': '#fff', '--color-checked': '#3b82f6' }}>
              <IonLabel style={{ fontWeight: 700 }}>Admin Staff</IonLabel>
            </IonSegmentButton>
            <IonSegmentButton value="general" style={{ '--indicator-color': '#fff', '--color-checked': '#ca8a04' }}>
              <IonLabel style={{ fontWeight: 700 }}>General Staff</IonLabel>
            </IonSegmentButton>
          </IonSegment>
        </IonToolbar>
      </IonHeader>

      <IonContent style={{ '--background': '#f8fafc' }}>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        {loading ? (
          <div className="loading-center"><IonSpinner name="lines" color="primary" /></div>
        ) : error ? (
           <div style={{ textAlign: 'center', marginTop: '100px', padding: '20px' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '60px', color: '#ef4444' }} />
              <p style={{ color: '#64748b' }}>{error}</p>
              <IonButton fill="outline" color="primary" onClick={load}>Retry Staff Sync</IonButton>
           </div>
        ) : (
          <div className="animate-in" style={{ paddingBottom: '80px' }}>
            
            {/* STATS ROW */}
            <div style={{ padding: '15px 15px 5px 15px' }}>
               <IonGrid className="ion-no-padding">
                  <IonRow style={{ gap: '10px', flexWrap: 'nowrap', overflowX: 'auto' }}>
                     <IonCol size="4" className="mini-stat-card" style={{ minWidth: '110px', padding: '12px' }}>
                        <div style={{ fontSize: '10px', color: '#64748b', fontWeight: 'bold' }}>ADMINS</div>
                        <div style={{ fontSize: '20px', fontWeight: '900', color: '#0f172a' }}>{stats.admin_count}</div>
                        <div style={{ fontSize: '9px', color: '#3b82f6' }}>Accounts</div>
                     </IonCol>
                     <IonCol size="4" className="mini-stat-card" style={{ minWidth: '110px', padding: '12px' }}>
                        <div style={{ fontSize: '10px', color: '#64748b', fontWeight: 'bold' }}>RECORDS</div>
                        <div style={{ fontSize: '20px', fontWeight: '900', color: '#ca8a04' }}>{stats.general_count}</div>
                        <div style={{ fontSize: '9px', color: '#64748b' }}>Personnel</div>
                     </IonCol>
                     <IonCol size="4" className="mini-stat-card" style={{ minWidth: '110px', padding: '12px' }}>
                        <div style={{ fontSize: '10px', color: '#64748b', fontWeight: 'bold' }}>ACTIVE</div>
                        <div style={{ fontSize: '20px', fontWeight: '900', color: '#10b981' }}>{stats.active_count}</div>
                        <div style={{ fontSize: '9px', color: '#10b981' }}>On-duty</div>
                     </IonCol>
                  </IonRow>
               </IonGrid>
            </div>

            {/* CONDITIONAL RENDERING BASED ON SEGMENT */}
            {viewMode === 'admin' ? (
              <div className="animate-in">
                <div className="section-header-modern admin">
                   <div className="sh-icon"><IonIcon icon={shieldCheckmarkOutline} /></div>
                   <div className="sh-body">
                      <div className="sh-title">Admin Staff</div>
                      <div className="sh-desc">Personnel with web system accounts</div>
                   </div>
                </div>

                <div className="staff-table-mobile">
                   <div className="table-header">
                      <div style={{ flex: 1.2 }}>Name</div>
                      <div style={{ flex: 1, textAlign: 'center' }}>Role</div>
                      <div style={{ width: 60, textAlign: 'right' }}>Status</div>
                   </div>
                   {adminFiltered.map(s => (
                      <div key={s.id} className="table-row">
                         <div className="row-name">
                            <div className="avatar admin">{s.name?.charAt(0)}</div>
                            <span>{s.name}</span>
                         </div>
                         <div className="row-role">{s.role}</div>
                         <div className="row-status">
                            <span className={`pill ${s.status === 'active' ? 'active' : 'inactive'}`}>
                               {s.status === 'active' ? 'Active' : 'Inactive'}
                            </span>
                         </div>
                      </div>
                   ))}
                    {adminFiltered.length === 0 && <div className="empty-state">No admin personnel found</div>}
                </div>
              </div>
            ) : (
              <div className="animate-in">
                <div className="section-header-modern general">
                   <div className="sh-icon"><IonIcon icon={peopleOutline} /></div>
                   <div className="sh-body">
                      <div className="sh-title">General Staff</div>
                      <div className="sh-desc">Personnel records without system accounts</div>
                   </div>
                   <IonButton 
                      size="small" 
                      className="add-btn-modern" 
                      onClick={() => history.push('/app/staff/new')}
                   >
                      <IonIcon slot="start" icon={addOutline} /> Add Record
                   </IonButton>
                </div>

                <div className="staff-table-mobile">
                   <div className="table-header">
                      <div style={{ flex: 1.2 }}>Name</div>
                      <div style={{ flex: 1, textAlign: 'center' }}>Role</div>
                      <div style={{ width: 60, textAlign: 'right' }}>Status</div>
                   </div>
                   {generalFiltered.map(s => (
                      <div key={s.id} className="table-row actionable" onClick={() => history.push(`/app/staff/${s.id}/edit`)}>
                         <div className="row-name">
                            <div className="avatar general">{s.name?.charAt(0)}</div>
                            <span>{s.name}</span>
                         </div>
                         <div className="row-role">{s.role}</div>
                         <div className="row-status">
                            <span className={`pill ${s.status === 'active' ? 'active' : 'inactive'}`}>
                               {s.status === 'active' ? 'Active' : 'Inactive'}
                            </span>
                         </div>
                      </div>
                   ))}
                    {generalFiltered.length === 0 && <div className="empty-state">No general records found</div>}
                </div>
              </div>
            )}
          </div>
        )}
      </IonContent>

      <style>{`
          .header-modern { background: #fff; }
          .search-container-modern { position: relative; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 4px; display: flex; align-items: center; }
          .search-icon-deco { position: absolute; left: 14px; color: #94a3b8; font-size: 18px; }
          .modern-input { width: 100%; border: none; background: transparent; padding: 10px 10px 10px 40px; font-size: 14px; color: #0f172a; outline: none; }
          
          .section-header-modern { display: flex; align-items: center; gap: 12px; padding: 16px 20px; margin-top: 10px; }
          .sh-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
          .admin .sh-icon { background: #eff6ff; color: #3b82f6; }
          .general .sh-icon { background: #fffbeb; color: #ca8a04; }
          .sh-body { flex: 1; }
          .sh-title { font-size: 17px; font-weight: 900; color: #0f172a; }
          .sh-desc { font-size: 11px; color: #64748b; margin-top: 2px; }
          .add-btn-modern { --background: #ca8a04; --border-radius: 10px; font-size: 11px; font-weight: 800; --padding-start: 12px; --padding-end: 12px; height: 35px; }

          .staff-table-mobile { background: #fff; margin: 0 16px 24px 16px; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
          .table-header { display: flex; padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 9px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
          .table-row { display: flex; padding: 14px 16px; align-items: center; border-bottom: 1px solid #f1f5f9; }
          .table-row:last-child { border-bottom: none; }
          .table-row.actionable:active { background: #f8fafc; }
          .row-name { flex: 1.2; display: flex; align-items: center; gap: 10px; }
          .row-name span { font-size: 13px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
          .avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; }
          .avatar.admin { background: #eff6ff; color: #3b82f6; }
          .avatar.general { background: #fffbeb; color: #ca8a04; }
          .row-role { flex: 1; font-size: 12px; color: #64748b; text-align: center; }
          .row-status { width: 60px; text-align: right; }
          .pill { font-size: 9px; font-weight: 900; padding: 4px 10px; border-radius: 20px; }
          .pill.active { background: #dcfce7; color: #166534; }
          .pill.inactive { background: #fee2e2; color: #991b1b; }
          .empty-state { text-align: center; padding: 40px; color: #94a3b8; font-size: 12px; }

          .mini-stat-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 12px -2px rgb(0 0 0 / 0.05); border: 1px solid #f1f5f9; }
          .animate-in { animation: fadeIn 0.4s ease-out; }
          @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </IonPage>
  );
}
