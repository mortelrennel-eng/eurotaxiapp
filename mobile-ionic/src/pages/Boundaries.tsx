import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonList, IonRefresher, IonRefresherContent,
  IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonBadge, IonCard, IonButton, IonGrid, IonRow, IonCol, IonSelect, IonSelectOption
} from '@ionic/react';
import {
  addOutline, cashOutline, personOutline, timeOutline, warningOutline,
  calendarOutline, funnelOutline, searchOutline, notificationsOutline,
  trendingUpOutline, alertCircleOutline, checkmarkCircleOutline,
  carSportOutline, walletOutline, arrowBackOutline, chevronForwardOutline
} from 'ionicons/icons';
import { getBoundaries } from '../api';
import { useHistory } from 'react-router-dom';

export default function Boundaries() {
  const [data, setData] = useState<any>(null);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [month, setMonth] = useState(new Date().getMonth() + 1);
  const [year, setYear] = useState(new Date().getFullYear());
  const [statusFilter, setStatusFilter] = useState('all');
  const [didFallback, setDidFallback] = useState(false);
  const history = useHistory();

  const extractRecords = (payload: any) => {
    return Array.isArray(payload.records) ? payload.records : (Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : []));
  };

  const load = useCallback(async (m: number, y: number) => {
    setLoading(true);
    setError(null);
    try {
      const res = await getBoundaries(m, y);
      if (res && res.success) {
        const payload = res.data || {};
        const records = extractRecords(payload);

        if (records.length === 0 && !didFallback) {
          setDidFallback(true);
          const fallbackRes = await getBoundaries();
          if (fallbackRes && fallbackRes.success) {
            const fbPayload = fallbackRes.data || {};
            const fbRecords = extractRecords(fbPayload);
            if (fbRecords.length > 0) {
              const latestDate = new Date(fbRecords[0].date);
              setMonth(latestDate.getMonth() + 1);
              setYear(latestDate.getFullYear());
            }
            setData(fbPayload);
            setFiltered(fbRecords);
          } else {
            setData(payload);
            setFiltered(records);
          }
        } else {
          setData(payload);
          setFiltered(records);
        }
      } else {
        setError(res?.message || 'Failed to sync boundaries.');
      }
    } catch (e: any) {
      setError(e?.message || 'Connection error');
    } finally {
      setLoading(false);
    }
  }, [didFallback]);

  useEffect(() => { load(month, year); }, [load, month, year]);

  useEffect(() => {
    const list = extractRecords(data || {});
    let result = list;
    if (statusFilter !== 'all') {
      result = result.filter((b: any) => (b?.status || '').toLowerCase() === statusFilter);
    }
    if (search.trim()) {
      const q = search.toLowerCase();
      result = result.filter((b: any) =>
        (b?.unit_number || '').toString().toLowerCase().includes(q) ||
        (b?.driver_name || '').toLowerCase().includes(q) ||
        (b?.plate_number || '').toLowerCase().includes(q)
      );
    }
    setFiltered(result);
  }, [search, data, statusFilter]);

  const formatCurrency = (val: any) => '₱' + Math.round(parseFloat(val) || 0).toLocaleString();
  const stats = data?.stats || {};

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>Revenue Collections</div>
            <div className="header-modern-sub">Vehicle boundary & daily remit logs</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load(month, year).finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* SEARCH & FILTERS GLASS CARD */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div className="executive-label" style={{ marginBottom: '12px' }}>Intintelligent Filters</div>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search unit or driver..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
             </div>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <div style={{ position: 'relative' }}>
                   <IonIcon icon={calendarOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 35px 0 12px', fontSize: '12px', fontWeight: 800, appearance: 'none', outline: 'none' }}
                      value={month}
                      onChange={(e) => setMonth(parseInt(e.target.value))}
                   >
                      {[1,2,3,4,5,6,7,8,9,10,11,12].map(m => <option key={m} value={m}>{new Date(2024, m-1).toLocaleString('default', { month: 'long' })}</option>)}
                   </select>
                </div>
                <div style={{ position: 'relative' }}>
                   <IonIcon icon={funnelOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 35px 0 12px', fontSize: '12px', fontWeight: 800, appearance: 'none', outline: 'none' }}
                      value={statusFilter}
                      onChange={(e) => setStatusFilter(e.target.value)}
                   >
                      <option value="all">All Status</option>
                      <option value="paid">Fully Paid</option>
                      <option value="shortage">Shortage</option>
                      <option value="pending">Pending</option>
                   </select>
                </div>
             </div>
          </div>

          {/* QUICK STATS */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Total Collected</div>
                <div className="executive-value" style={{ color: '#166534' }}>{formatCurrency(stats.total_collected)}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#22c55e', marginTop: '4px' }}>
                   OVER THE PERIOD
                </div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Total Shortage</div>
                <div className="executive-value" style={{ color: '#ef4444' }}>{formatCurrency(stats.total_shortage)}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#64748b', marginTop: '4px' }}>
                   OUTSTANDING DEBT
                </div>
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Collection Records</div>
             <IonButton fill="clear" size="small" onClick={() => history.push('/app/boundaries/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> NEW COLLECTION
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !data ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {filtered.map((b: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px' }} onClick={() => history.push(`/app/boundaries/${b.id}/edit`)}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                           <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: '#f8fafc', color: '#ca8a04', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '16px' }}>
                              <IonIcon icon={walletOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{b.plate_number}</div>
                              <div style={{ fontSize: '10px', color: '#64748b' }}>{new Date(b.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '9px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: b.status?.toLowerCase() === 'paid' ? '#dcfce7' : (b.shortage > 0 ? '#fee2e2' : '#f1f5f9'),
                           color: b.status?.toLowerCase() === 'paid' ? '#166534' : (b.shortage > 0 ? '#991b1b' : '#64748b')
                        }}>
                           {b.status?.toUpperCase() || 'PENDING'}
                        </div>
                     </div>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
                        <div>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Driver Involved</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>{b.driver_name}</div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Collected Amount</div>
                           <div style={{ fontSize: '16px', fontWeight: 900, color: '#ca8a04' }}>{formatCurrency(b.actual_boundary)}</div>
                        </div>
                     </div>
                  </div>
               ))}
               {filtered.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={cashOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No collection records found.</p>
                  </div>
               )}
            </div>
          )}

        </div>
      </IonContent>
    </IonPage>
  );
}
