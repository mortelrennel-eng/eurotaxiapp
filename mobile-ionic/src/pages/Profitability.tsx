import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonRefresher, IonRefresherContent, IonIcon, IonSpinner, IonList,
  IonButtons, IonMenuButton, IonCard, IonCardContent, IonSearchbar, IonGrid, IonRow, IonCol, IonButton,
  IonModal, IonDatetime
} from '@ionic/react';
import { 
  warningOutline, 
  carSportOutline, 
  trendingUpOutline, 
  trendingDownOutline, 
  calendarOutline,
  checkmarkCircleOutline,
  alertCircleOutline,
  trendingUpSharp,
  trendingDownSharp,
  walletOutline,
  pieChartOutline,
  barChartOutline,
  statsChartOutline
} from 'ionicons/icons';
import { getProfitability } from '../api';
import { format, parseISO } from 'date-fns';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale, LinearScale, PointElement, LineElement,
  Title, Tooltip, Legend, Filler
);

const Profitability: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [dateFrom, setDateFrom] = useState<string>(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString());
  const [dateTo, setDateTo] = useState<string>(new Date().toISOString());
  const today = new Date().toISOString();
  
  const [showFromModal, setShowFromModal] = useState(false);
  const [showToModal, setShowToModal] = useState(false);
  const [showTopModal, setShowTopModal] = useState(false);
  const [showNeedsModal, setShowNeedsModal] = useState(false);

  const [selectedUnitId, setSelectedUnitId] = useState<string>('');

  const load = useCallback(async (f?: string, t?: string, uid?: string) => {
    setLoading(true);
    setError(null);
    try {
      const params: any = {
        date_from: format(parseISO(f || dateFrom), 'yyyy-MM-dd'),
        date_to: format(parseISO(t || dateTo), 'yyyy-MM-dd')
      };
      if (uid || selectedUnitId) {
        params.unit_id = uid || selectedUnitId;
      }

      const res = await getProfitability(params);
      if (res && res.success) {
        setData(res.data);
        setFiltered(res.data.units || []);
      } else {
        setError(res?.message || 'Failed to sync profitability');
      }
    } catch (e: any) { 
      setError(e?.message || 'Connection error'); 
    } finally { 
      setLoading(false); 
    }
  }, [dateFrom, dateTo, selectedUnitId]);

  useEffect(() => { load(); }, []);

  useEffect(() => {
    let list = data?.units || [];
    setFiltered(list);
  }, [data]);

  const formatCurrency = (val: number) => '₱' + Math.round(val || 0).toLocaleString();
  const formatDateLabel = (iso: string) => format(parseISO(iso), 'MMM dd, yyyy');

  const sparklineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } },
    scales: { x: { display: false }, y: { display: false } },
    elements: { point: { radius: 0 }, line: { borderWidth: 2, tension: 0.4 } }
  };

  const getSparkData = (values: number[] | undefined, color: string) => ({
    labels: (values || Array(10).fill(0)).map((_, i) => i),
    datasets: [{
      data: values || Array(10).fill(0),
      borderColor: color,
      backgroundColor: color + '11',
      fill: true
    }]
  });

  const overview = data?.overview || {};
  const sparkData = data?.sparkline_data || [];

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>ROI Analysis</div>
            <div className="header-modern-sub">Vehicle profitability & asset yield</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* SELECTOR GLASS CARD */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div className="executive-label" style={{ marginBottom: '16px' }}>Filter Intelligence</div>
             <div style={{ display: 'flex', gap: '12px', marginBottom: '16px' }}>
                <div style={{ flex: 1 }} onClick={() => setShowFromModal(true)}>
                   <div style={{ fontSize: '10px', fontWeight: 800, color: '#64748b', textTransform: 'uppercase' }}>From</div>
                   <div style={{ fontSize: '12px', fontWeight: 800, color: '#ca8a04' }}>{formatDateLabel(dateFrom)}</div>
                </div>
                <div style={{ width: '1px', background: '#e2e8f0' }}></div>
                <div style={{ flex: 1 }} onClick={() => setShowToModal(true)}>
                   <div style={{ fontSize: '10px', fontWeight: 800, color: '#64748b', textTransform: 'uppercase' }}>To</div>
                   <div style={{ fontSize: '12px', fontWeight: 800, color: '#ca8a04' }}>{formatDateLabel(dateTo)}</div>
                </div>
             </div>
             <div style={{ position: 'relative' }}>
                <IonIcon icon={carSportOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                <select 
                   style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 38px 0 16px', fontSize: '13px', fontWeight: 700, appearance: 'none', outline: 'none' }}
                   value={selectedUnitId}
                   onChange={(e) => { setSelectedUnitId(e.target.value); load(undefined, undefined, e.target.value); }}
                >
                   <option value="">All Fleet Units</option>
                   {(data?.units || []).map((u: any) => (<option key={u.id} value={u.id}>{u.plate_number} (U-{u.unit_number})</option>))}
                </select>
             </div>
          </div>

          {loading && !data ? (
             <div style={{ textAlign: 'center', padding: '100px 0' }}><IonSpinner name="crescent" color="primary" /></div>
          ) : (
             <>
               {/* KPI GRID */}
               <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                  <div className="glass-card premium-gradient-success" style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2' }}>
                     <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>Total Portfolio Yield</div>
                     <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{formatCurrency(overview.net_income)}</div>
                     <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                        <IonIcon icon={trendingUpSharp} /> Average Margin: {Math.round(overview.avg_margin || 0)}%
                     </div>
                     <div style={{ height: '40px', marginTop: '12px' }}>
                        <Line data={getSparkData(sparkData, '#fff')} options={sparklineOptions} />
                     </div>
                  </div>

                  <div className="mini-stat-card glass-card">
                     <div className="executive-label">Total Revenue</div>
                     <div className="executive-value" style={{ color: '#ca8a04' }}>{formatCurrency(overview.total_boundary)}</div>
                  </div>
                  <div className="mini-stat-card glass-card">
                     <div className="executive-label">Asset Overhead</div>
                     <div className="executive-value" style={{ color: '#ef4444' }}>{formatCurrency(overview.total_expenses)}</div>
                  </div>
               </div>

               {/* STRATEGIC TOOLS */}
               <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginBottom: '32px' }}>
                  <div className="glass-card" style={{ padding: '16px', borderRadius: '20px', background: '#f0fdf4', border: '1px solid #dcfce7', display: 'flex', alignItems: 'center', gap: '12px' }} onClick={() => setShowTopModal(true)}>
                     <div style={{ width: '32px', height: '32px', borderRadius: '50%', background: '#dcfce7', color: '#166534', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '18px' }}><IonIcon icon={trendingUpOutline} /></div>
                     <div style={{ fontSize: '11px', fontWeight: 900, color: '#166534' }}>TOP PRODUCERS</div>
                  </div>
                  <div className="glass-card" style={{ padding: '16px', borderRadius: '20px', background: '#fef2f2', border: '1px solid #fee2e2', display: 'flex', alignItems: 'center', gap: '12px' }} onClick={() => setShowNeedsModal(true)}>
                     <div style={{ width: '32px', height: '32px', borderRadius: '50%', background: '#fee2e2', color: '#991b1b', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '18px' }}><IonIcon icon={trendingDownOutline} /></div>
                     <div style={{ fontSize: '11px', fontWeight: 900, color: '#991b1b' }}>IDLE/COSTLY</div>
                  </div>
               </div>

               <div className="web-section-header">Detailed Unit Performance</div>
               <div style={{ display: 'grid', gap: '16px' }}>
                  {filtered.map((u: any, idx: number) => (
                     <div key={idx} className="glass-card" style={{ padding: '20px', borderRadius: '24px' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
                           <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                              <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: '#f8fafc', color: '#ca8a04', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '22px' }}><IonIcon icon={carSportOutline} /></div>
                              <div>
                                 <div style={{ fontSize: '15px', fontWeight: 900, color: '#0f172a' }}>{u.plate_number}</div>
                                 <div style={{ fontSize: '11px', color: '#64748b', fontWeight: 700 }}>UNIT {u.unit_number} | {u.model}</div>
                              </div>
                           </div>
                           <div style={{ textAlign: 'right' }}>
                              <div className="executive-label" style={{ fontSize: '9px' }}>NET YIELD</div>
                              <div style={{ fontSize: '18px', fontWeight: 900, color: u.net_income >= 0 ? '#10b981' : '#ef4444' }}>{formatCurrency(u.net_income)}</div>
                           </div>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '10px', marginBottom: '16px' }}>
                           <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '12px', textAlign: 'center' }}>
                              <div className="executive-label" style={{ fontSize: '8px' }}>REVENUE</div>
                              <div style={{ fontSize: '11px', fontWeight: 900, color: '#0f172a' }}>{formatCurrency(u.total_boundary)}</div>
                           </div>
                           <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '12px', textAlign: 'center' }}>
                              <div className="executive-label" style={{ fontSize: '8px' }}>MAINT.</div>
                              <div style={{ fontSize: '11px', fontWeight: 900, color: '#0f172a' }}>{formatCurrency(u.total_maintenance)}</div>
                           </div>
                           <div style={{ background: '#f8fafc', padding: '10px', borderRadius: '12px', textAlign: 'center' }}>
                              <div className="executive-label" style={{ fontSize: '8px' }}>OVERHEAD</div>
                              <div style={{ fontSize: '11px', fontWeight: 900, color: '#0f172a' }}>{formatCurrency(u.total_expenses)}</div>
                           </div>
                        </div>

                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                           <div style={{ 
                              fontSize: '9px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                              background: u.performance?.toLowerCase() === 'excellent' ? '#dcfce7' : (u.performance?.toLowerCase() === 'poor' ? '#fee2e2' : '#f1f5f9'),
                              color: u.performance?.toLowerCase() === 'excellent' ? '#166534' : (u.performance?.toLowerCase() === 'poor' ? '#991b1b' : '#64748b')
                           }}>
                              {u.performance?.toUpperCase()}
                           </div>
                           <div style={{ fontSize: '11px', fontWeight: 800, color: '#94a3b8' }}>Margin: <span style={{ color: '#ca8a04' }}>{Math.round(u.profit_margin)}%</span></div>
                        </div>
                     </div>
                  ))}
               </div>
             </>
          )}

        </div>

        {/* MODALS */}
        <IonModal isOpen={showFromModal} onDidDismiss={() => setShowFromModal(false)} initialBreakpoint={0.5} breakpoints={[0, 0.5]}>
           <div style={{ padding: '24px', background: '#fff' }}>
              <IonDatetime presentation="date" value={dateFrom} max={today} onIonChange={(e: any) => { const v = Array.isArray(e.detail.value) ? e.detail.value[0] : e.detail.value; setDateFrom(v!); setShowFromModal(false); load(v!, dateTo); }} />
           </div>
        </IonModal>
        <IonModal isOpen={showToModal} onDidDismiss={() => setShowToModal(false)} initialBreakpoint={0.5} breakpoints={[0, 0.5]}>
           <div style={{ padding: '24px', background: '#fff' }}>
              <IonDatetime presentation="date" value={dateTo} min={dateFrom} max={today} onIonChange={(e: any) => { const v = Array.isArray(e.detail.value) ? e.detail.value[0] : e.detail.value; setDateTo(v!); setShowToModal(false); load(dateFrom, v!); }} />
           </div>
        </IonModal>

        {/* TOP PERFORMERS MODAL */}
        <IonModal isOpen={showTopModal} onDidDismiss={() => setShowTopModal(false)}>
           <IonHeader className="ion-no-border">
              <IonToolbar style={{ '--background': '#166534' }}>
                 <IonTitle style={{ color: '#fff', fontWeight: '900' }}>Executive List: Top Performers</IonTitle>
                 <IonButtons slot="end"><IonButton onClick={() => setShowTopModal(false)} style={{ '--color': '#fff', fontWeight: 'bold' }}>DONE</IonButton></IonButtons>
              </IonToolbar>
           </IonHeader>
           <IonContent style={{ '--background': '#f0fdf4' }}>
              <div style={{ padding: '16px' }}>
                 {data?.top_performers?.map((u: any, idx: number) => (
                    <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px', marginBottom: '12px', border: '1.5px solid #dcfce7' }}>
                       <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                             <div style={{ width: '32px', height: '32px', borderRadius: '50%', background: '#dcfce7', color: '#166534', display: 'flex', alignItems: 'center', justifyContent: 'center' }}><IonIcon icon={checkmarkCircleOutline} /></div>
                             <div>
                                <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{u.plate_number}</div>
                                <div style={{ fontSize: '10px', color: '#64748b' }}>Yield: {formatCurrency(u.net_income)}</div>
                             </div>
                          </div>
                          <div style={{ fontSize: '14px', fontWeight: 900, color: '#16a34a' }}>{Math.round(u.profit_margin)}%</div>
                       </div>
                    </div>
                 ))}
              </div>
           </IonContent>
        </IonModal>

        {/* NEEDS ATTENTION MODAL */}
        <IonModal isOpen={showNeedsModal} onDidDismiss={() => setShowNeedsModal(false)}>
           <IonHeader className="ion-no-border">
              <IonToolbar style={{ '--background': '#991b1b' }}>
                 <IonTitle style={{ color: '#fff', fontWeight: '900' }}>Executive List: Risk Alerts</IonTitle>
                 <IonButtons slot="end"><IonButton onClick={() => setShowNeedsModal(false)} style={{ '--color': '#fff', fontWeight: 'bold' }}>DONE</IonButton></IonButtons>
              </IonToolbar>
           </IonHeader>
           <IonContent style={{ '--background': '#fef2f2' }}>
              <div style={{ padding: '16px' }}>
                 {data?.needs_attention?.map((u: any, idx: number) => (
                    <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px', marginBottom: '12px', border: '1.5px solid #fee2e2' }}>
                       <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                             <div style={{ width: '32px', height: '32px', borderRadius: '50%', background: '#fee2e2', color: '#991b1b', display: 'flex', alignItems: 'center', justifyContent: 'center' }}><IonIcon icon={alertCircleOutline} /></div>
                             <div>
                                <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{u.plate_number}</div>
                                <div style={{ fontSize: '10px', color: '#64748b' }}>Loss/Low: {formatCurrency(u.net_income)}</div>
                             </div>
                          </div>
                          <div style={{ fontSize: '14px', fontWeight: 900, color: '#ef4444' }}>{Math.round(u.profit_margin)}%</div>
                       </div>
                    </div>
                 ))}
              </div>
           </IonContent>
        </IonModal>

      </IonContent>
    </IonPage>
  );
};

export default Profitability;
