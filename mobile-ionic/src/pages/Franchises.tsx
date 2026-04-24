import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonList, IonItem, IonLabel, IonRefresher, IonRefresherContent,
  IonFab, IonFabButton, IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonBadge, IonCard, IonButton, IonGrid, IonRow, IonCol
} from '@ionic/react';
import { 
  addOutline, documentTextOutline, timeOutline, carSportOutline, warningOutline,
  searchOutline, notificationsOutline, briefcaseOutline, shieldCheckmarkOutline,
  alertCircleOutline, closeOutline, funnelOutline, sparklesOutline
} from 'ionicons/icons';
import { getFranchises } from '../api';
import { useHistory } from 'react-router-dom';

export default function Franchises() {
  const [data, setData] = useState<any>({ records: [], stats: {} });
  const [filtered, setFiltered] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const history = useHistory();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getFranchises();
      if (res && res.success) {
        const payload = res.data || { records: [], stats: {} };
        setData(payload);
        setFiltered(payload.records || []);
      } else {
        setError(res?.message || 'Failed to sync franchise data');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    const list = Array.isArray(data.records) ? data.records : [];
    if (!search.trim()) { setFiltered(list); return }
    const q = search.toLowerCase()
    setFiltered(list.filter((f: any) =>
      (f?.case_no || '').toLowerCase().includes(q) ||
      (f?.applicant_name || '').toLowerCase().includes(q) ||
      (f?.type_of_application || '').toLowerCase().includes(q)
    ))
  }, [search, data]);

  const stats = data.stats || {};

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-blue"></span>Legal & Compliance</div>
            <div className="header-modern-sub">LTFRB regulatory records & case tracking</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* STATS GRID */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
             <div className="glass-card premium-gradient-success" style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2' }}>
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>Total Regulatory Volume</div>
                <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{stats.total_cases || 0} Case Files</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                   <IonIcon icon={shieldCheckmarkOutline} />
                   Fleet currently 98% compliant with LTFRB
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">Pending Renewal</div>
                <div className="executive-value" style={{ color: '#ca8a04' }}>{stats.expiring_soon || 0}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>NEXT 30 DAYS</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Expired Cases</div>
                <div className="executive-value" style={{ color: '#ef4444' }}>{stats.expired || 0}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>ACTION REQUIRED</div>
             </div>
          </div>

          {/* SEARCH */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search case #, applicant, or type..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Case Ledger</div>
             <IonButton fill="clear" size="small" onClick={() => history.push('/app/franchises/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> NEW FILING
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !filtered.length ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {filtered.map((f: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '20px', borderRadius: '24px' }} onClick={() => history.push(`/app/franchises/${f.id}/edit`)}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                           <div style={{ width: '48px', height: '48px', borderRadius: '16px', background: '#f8fafc', color: '#3b82f6', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }}>
                              <IonIcon icon={documentTextOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '15px', fontWeight: 900, color: '#0f172a' }}>{f.case_no}</div>
                              <div style={{ fontSize: '11px', color: '#64748b', fontWeight: 700 }}>{f.type_of_application}</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '9px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: f.status?.toLowerCase() === 'approved' ? '#dcfce7' : '#fee2e2',
                           color: f.status?.toLowerCase() === 'approved' ? '#166534' : '#991b1b'
                        }}>
                           {(f.status || 'PENDING').toUpperCase()}
                        </div>
                     </div>
                     
                     <div style={{ background: '#f8fafc', padding: '12px', borderRadius: '16px', marginBottom: '12px' }}>
                        <div className="executive-label" style={{ fontSize: '8px' }}>Registered Applicant</div>
                        <div style={{ fontSize: '13px', fontWeight: 800, color: '#475569' }}>{f.applicant_name}</div>
                     </div>

                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
                           <IonIcon icon={timeOutline} style={{ color: '#94a3b8', fontSize: '14px' }} />
                           <span style={{ fontSize: '11px', fontWeight: 800, color: f.status === 'expired' ? '#ef4444' : '#94a3b8' }}>Exp: {f.expiry_date || 'N/A'}</span>
                        </div>
                        <div style={{ fontSize: '11px', fontWeight: 900, color: '#0f172a' }}>{f.unit_count} UNITS</div>
                     </div>
                  </div>
               ))}
               {filtered.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={briefcaseOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No matching regulatory records.</p>
                  </div>
               )}
            </div>
          )}

        </div>
      </IonContent>
    </IonPage>
  );
}
