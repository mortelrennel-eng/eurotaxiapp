import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonList, IonRefresher, IonRefresherContent,
  IonFab, IonFabButton, IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonBadge, IonCard, IonButton, IonGrid, IonRow, IonCol, IonSelect, IonSelectOption
} from '@ionic/react';
import { 
  addOutline, buildOutline, warningOutline, timeOutline, carSportOutline, 
  funnelOutline, optionsOutline, searchOutline, notificationsOutline,
  cashOutline, constructOutline, ribbonOutline, closeOutline,
  settingsOutline, hammerOutline
} from 'ionicons/icons';
import { getMaintenances } from '../api';
import { useHistory } from 'react-router-dom';

export default function Maintenance() {
  const [data, setData] = useState<any>({ records: [], stats: {} });
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const history = useHistory();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getMaintenances({ 
        search, 
        status: statusFilter, 
        type: typeFilter 
      });
      if (res.success) {
        setData(res.data || { records: [], stats: {} });
      } else {
        setError(res.message || 'Failed to sync maintenance logs');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, [search, statusFilter, typeFilter]);

  useEffect(() => { load(); }, [load]);

  const formatCurrency = (val: any) => '₱' + Math.round(parseFloat(val) || 0).toLocaleString();

  const stats = data.stats || {};
  const records = Array.isArray(data.records) ? data.records : [];

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-yellow"></span>Technical Ops</div>
            <div className="header-modern-sub">Vehicle workshop logs & technical status</div>
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
             <div className="glass-card premium-gradient-warning" style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2' }}>
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>Total Operational Cost</div>
                <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{formatCurrency(stats.total_cost)}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                   <IonIcon icon={buildOutline} />
                   Average maintenance downtime: 1.4 days
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">Fleet Records</div>
                <div className="executive-value">{stats.total_records || 0}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>TOTAL LOG VOLUME</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Workshop Queue</div>
                <div className="executive-value" style={{ color: '#ca8a04' }}>{stats.pending_count || 0}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>AWAITING SERVICE</div>
             </div>
          </div>

          {/* FILTERS */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search by unit or mechanic..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
             </div>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <div style={{ position: 'relative' }}>
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 12px', fontSize: '11px', fontWeight: 800, outline: 'none' }}
                      value={statusFilter}
                      onChange={(e) => setStatusFilter(e.target.value)}
                   >
                      <option value="">Status Map</option>
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                   </select>
                </div>
                <div style={{ position: 'relative' }}>
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 12px', fontSize: '11px', fontWeight: 800, outline: 'none' }}
                      value={typeFilter}
                      onChange={(e) => setTypeFilter(e.target.value)}
                   >
                      <option value="">Category</option>
                      <option value="preventive">Preventive</option>
                      <option value="corrective">Corrective</option>
                      <option value="emergency">Emergency</option>
                   </select>
                </div>
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Technical Job Ledger</div>
             <IonButton fill="clear" size="small" onClick={() => history.push('/app/maintenance/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> NEW JOB
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !records.length ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {records.map((m: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px' }} onClick={() => history.push(`/app/maintenance/${m.id}/edit`)}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                           <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: '#f8fafc', color: '#10b981', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '16px' }}>
                              <IonIcon icon={settingsOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{m.maintenance_type?.toUpperCase()}</div>
                              <div style={{ fontSize: '10px', color: '#64748b' }}>{new Date(m.date_started).toLocaleDateString()}</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '8px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: m.status?.toLowerCase() === 'completed' ? '#dcfce7' : (m.status?.toLowerCase() === 'in_progress' ? '#fef9c3' : '#fee2e2'),
                           color: m.status?.toLowerCase() === 'completed' ? '#166534' : (m.status?.toLowerCase() === 'in_progress' ? '#854d0e' : '#991b1b')
                        }}>
                           {(m.status || 'PENDING').toUpperCase().replace('_', ' ')}
                        </div>
                     </div>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
                        <div>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Vehicle Asset</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>UNIT {m.unit_number} • {m.plate_number}</div>
                           <div style={{ fontSize: '10px', color: '#94a3b8', fontStyle: 'italic', marginTop: '4px' }}>"{m.description}"</div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Repair Cost</div>
                           <div style={{ fontSize: '16px', fontWeight: 900, color: '#0f172a' }}>{formatCurrency(m.cost)}</div>
                        </div>
                     </div>
                  </div>
               ))}
               {records.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={hammerOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No active maintenance jobs.</p>
                  </div>
               )}
            </div>
          )}

        </div>
      </IonContent>
    </IonPage>
  );
}
