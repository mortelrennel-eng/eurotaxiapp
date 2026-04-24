import React, { useEffect, useState, useCallback } from 'react'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonRefresher, IonRefresherContent, IonSpinner, IonIcon,
  IonButtons, IonMenuButton, IonGrid, IonRow, IonCol, IonButton,
  IonCard, IonCardContent, IonModal, IonList, IonItem, IonLabel,
  IonBadge, IonText, IonSegment, IonSegmentButton
} from '@ionic/react'
import {
  carSportOutline, cashOutline, constructOutline, warningOutline,
  trendingUpOutline, peopleOutline, trendingDownOutline, codeOutline,
  chevronForwardOutline, receiptOutline, calendarOutline, speedometerOutline,
  timeOutline, personOutline, storefrontOutline
} from 'ionicons/icons'
import { 
  getDashboard, 
  getIncomeDetails, 
  getDashboardMaintenance, 
  getDashboardCoding, 
  getDashboardDrivers 
} from '../api'
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, BarElement, Title, Tooltip, Legend, Filler, ArcElement
} from 'chart.js';
import { Line, Doughnut, Bar } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement,
  Title, Tooltip, Legend, Filler
);

// --- MODAL COMPONENTS ---

const DashboardModal = ({ isOpen, onClose, title, icon, children }: any) => (
  <IonModal isOpen={isOpen} onDidDismiss={onClose} breakpoints={[0, 0.5, 0.9]} initialBreakpoint={0.9}>
    <IonContent className="ion-padding">
      <div style={{ textAlign: 'center', marginBottom: '24px' }}>
        <div className="modal-detail-icon" style={{ background: '#f8fafc', color: '#ca8a04' }}>
          <IonIcon icon={icon} />
        </div>
        <h2 style={{ fontWeight: 900, fontSize: '20px', color: '#0f172a', margin: '0' }}>{title}</h2>
      </div>
      {children}
      <IonButton expand="block" fill="clear" onClick={onClose} style={{ marginTop: '20px' }}>CLOSE</IonButton>
    </IonContent>
  </IonModal>
);

// --- CHART COMPONENTS ---

const RevenueTrendChart = ({ data }: { data: any[] }) => {
  const safeData = Array.isArray(data) ? data : [];
  const chartData = {
    labels: safeData.map(d => d.date),
    datasets: [{
      label: 'Revenue',
      data: safeData.map(d => parseFloat(d.revenue) || 0),
      borderColor: '#ca8a04',
      backgroundColor: 'rgba(202, 138, 4, 0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: safeData.length < 15 ? 4 : 0,
    }]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 } } },
      x: { grid: { display: false }, ticks: { font: { size: 9 }, autoSkip: true } }
    }
  };

  return <div style={{ height: '200px' }}><Line data={chartData} options={options} /></div>;
};

// --- MAIN DASHBOARD ---

export default function Dashboard() {
  const [stats, setStats] = useState<any>({})
  const [loading, setLoading] = useState(true)
  const [activeModal, setActiveModal] = useState<string | null>(null)
  const [modalData, setModalData] = useState<any[]>([])
  const [modalLoading, setModalLoading] = useState(false)

  const load = useCallback(async () => {
    setLoading(true)
    try {
      const res = await getDashboard()
      if (res.success) setStats(res.data || {})
    } catch (e) { console.error(e) }
    finally { setLoading(false) }
  }, [])

  useEffect(() => { load() }, [load])

  const openBreakdown = async (type: string) => {
    setActiveModal(type)
    setModalLoading(true)
    try {
      let res;
      if (type === 'income') res = await getIncomeDetails()
      else if (type === 'maintenance') res = await getDashboardMaintenance()
      else if (type === 'coding') res = await getDashboardCoding()
      else if (type === 'drivers') res = await getDashboardDrivers()
      
      if (res?.success) setModalData(res.data)
    } catch (e) { console.error(e) }
    finally { setModalLoading(false) }
  }

  const formatCurrency = (val: any) => '₱' + Math.round(parseFloat(val) || 0).toLocaleString();

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>Executive Dashboard</div>
            <div className="header-modern-sub">Sync: {new Date().toLocaleTimeString()}</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        {loading ? (
          <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
        ) : (
          <div className="animate-in" style={{ padding: '20px 16px 100px 16px' }}>
            
            {/* GLASS STATS GRID */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
              
              {/* PRIMARY CARD: NET INCOME */}
              <div 
                className="glass-card premium-gradient-primary" 
                style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2', position: 'relative', overflow: 'hidden' }}
                onClick={() => openBreakdown('income')}
              >
                <div style={{ position: 'absolute', right: '-10px', top: '-10px', opacity: 0.1 }}>
                  <IonIcon icon={trendingUpOutline} style={{ fontSize: '100px' }} />
                </div>
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.8)' }}>Daily Net Performance</div>
                <div style={{ fontSize: '32px', fontWeight: 900, margin: '8px 0', color: 'white' }}>{formatCurrency(stats?.net_income)}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <div className="card-inner-blur" style={{ fontSize: '10px', fontWeight: 700, color: 'white' }}>
                    <IonIcon icon={receiptOutline} /> VIEW BREAKDOWN
                  </div>
                </div>
              </div>

              {/* SECONDARY STATS */}
              <div className="mini-stat-card glass-card" onClick={() => openBreakdown('drivers')}>
                <div className="executive-label">Fleet Duty</div>
                <div className="executive-value">{stats?.active_drivers ?? 0}</div>
                <div style={{ fontSize: '9px', color: '#22c55e', fontWeight: 700, marginTop: '4px' }}>
                  <IonIcon icon={peopleOutline} /> ACTIVE NOW
                </div>
              </div>

              <div className="mini-stat-card glass-card" onClick={() => openBreakdown('maintenance')}>
                <div className="executive-label">Workshop</div>
                <div className="executive-value" style={{ color: '#ef4444' }}>{stats?.maintenance_units ?? 0}</div>
                <div style={{ fontSize: '9px', color: '#64748b', fontWeight: 700, marginTop: '4px' }}>
                  <IonIcon icon={constructOutline} /> IN SHOP
                </div>
              </div>

              <div className="mini-stat-card glass-card" onClick={() => openBreakdown('coding')}>
                <div className="executive-label">Restrictions</div>
                <div className="executive-value">{stats?.coding_units ?? 0}</div>
                <div style={{ fontSize: '9px', color: '#f59e0b', fontWeight: 700, marginTop: '4px' }}>
                  <IonIcon icon={codeOutline} /> CODING TODAY
                </div>
              </div>

              <div className="mini-stat-card glass-card">
                <div className="executive-label">Total Fleet</div>
                <div className="executive-value">{stats?.active_units ?? 0}</div>
                <div style={{ fontSize: '9px', color: '#64748b', fontWeight: 700, marginTop: '4px' }}>
                  {stats?.roi_units ?? 0} ROI ACHIEVED
                </div>
              </div>
            </div>

            {/* DASHBOARD CHARTS */}
            <div className="web-section-header">Operational Revenue Trend</div>
            <IonCard style={{ margin: '0 0 24px 0', borderRadius: '24px' }}>
              <IonCardContent>
                <RevenueTrendChart data={stats?.revenue_trend} />
              </IonCardContent>
            </IonCard>

            <div className="web-section-header">Top Performing Units</div>
            <IonCard style={{ margin: '0 0 24px 0', borderRadius: '24px' }}>
              <IonCardContent>
                {stats?.unit_performance?.map((up: any, i: number) => (
                  <div key={i} style={{ marginBottom: '12px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                      <span style={{ fontWeight: 800, fontSize: '12px' }}>{up.unit}</span>
                      <span style={{ fontWeight: 900, color: '#ca8a04', fontSize: '11px' }}>{formatCurrency(up.performance)}</span>
                    </div>
                    <div style={{ height: '6px', background: '#f1f5f9', borderRadius: '10px' }}>
                      <div style={{ 
                        width: `${Math.min(100, (up.performance / up.target) * 100)}%`, 
                        height: '100%', 
                        background: 'var(--executive-gold)', 
                        borderRadius: '10px' 
                      }} />
                    </div>
                  </div>
                ))}
              </IonCardContent>
            </IonCard>

          </div>
        )}

        {/* --- DETAIL MODALS --- */}
        
        {/* 1. INCOME MODAL */}
        <DashboardModal 
          isOpen={activeModal === 'income'} 
          onClose={() => setActiveModal(null)} 
          title="Daily Financial Details" 
          icon={receiptOutline}
        >
          {modalLoading ? <IonSpinner name="lines" /> : (
            <div className="receipt-paper">
              <div style={{ textAlign: 'center', marginBottom: '16px' }}>
                <div style={{ fontSize: '24px', fontWeight: 900 }}>{formatCurrency(stats?.net_income)}</div>
                <div style={{ fontSize: '10px', color: '#64748b' }}>NET SURPLUS FOR TODAY</div>
              </div>
              <div className="receipt-dashed" />
              <IonList lines="none" style={{ background: 'transparent' }}>
                <IonItem style={{ '--background': 'transparent' }}>
                  <IonLabel>Total Revenue Today</IonLabel>
                  <IonText color="success" style={{ fontWeight: 800 }}>+{formatCurrency(stats?.today_boundary)}</IonText>
                </IonItem>
                <IonItem style={{ '--background': 'transparent' }}>
                  <IonLabel>Operating Expenses</IonLabel>
                  <IonText color="danger" style={{ fontWeight: 800 }}>-{formatCurrency(stats?.total_expenses_today)}</IonText>
                </IonItem>
              </IonList>
              <div className="receipt-dashed" />
              <div style={{ fontSize: '9px', fontWeight: 900, color: '#94a3b8', textAlign: 'center' }}>TIMESTAMP: {new Date().toLocaleString()}</div>
            </div>
          )}
        </DashboardModal>

        {/* 2. MAINTENANCE MODAL */}
        <DashboardModal 
          isOpen={activeModal === 'maintenance'} 
          onClose={() => setActiveModal(null)} 
          title="Units in Maintenance" 
          icon={constructOutline}
        >
          {modalLoading ? <IonSpinner /> : (
            <IonList>
              {modalData.map((m: any) => (
                <IonItem key={m.id}>
                  <IonIcon icon={carSportOutline} slot="start" />
                  <IonLabel>
                    <div style={{ fontWeight: 800 }}>{m.plate_number}</div>
                    <div style={{ fontSize: '12px', color: '#64748b' }}>{m.maintenance_type} — {m.description || 'Ongoing'}</div>
                  </IonLabel>
                  <IonBadge slot="end" color="warning">IN SHOP</IonBadge>
                </IonItem>
              ))}
              {modalData.length === 0 && <div style={{ textAlign: 'center', padding: '20px' }}>No units currently in maintenance.</div>}
            </IonList>
          )}
        </DashboardModal>

        {/* 3. CODING MODAL */}
        <DashboardModal 
          isOpen={activeModal === 'coding'} 
          onClose={() => setActiveModal(null)} 
          title="Coding Restrictions" 
          icon={codeOutline}
        >
          <div style={{ padding: '0 8px' }}>
            <p style={{ fontSize: '12px', color: '#64748b', marginBottom: '16px' }}>The following units are restricted today based on their plate ending.</p>
            <IonList>
              {modalData.map((u: any) => (
                <IonItem key={u.id}>
                  <IonIcon icon={warningOutline} slot="start" color="warning" />
                  <IonLabel>
                    <div style={{ fontWeight: 800 }}>{u.plate_number}</div>
                    <div style={{ fontSize: '12px', color: '#64748b' }}>{u.unit_number}</div>
                  </IonLabel>
                  <IonBadge slot="end" color="danger">RESTRICTED</IonBadge>
                </IonItem>
              ))}
              {modalData.length === 0 && <div style={{ textAlign: 'center', padding: '20px' }}>No coding restrictions for today.</div>}
            </IonList>
          </div>
        </DashboardModal>

        {/* 4. ACTIVE DRIVERS MODAL */}
        <DashboardModal 
          isOpen={activeModal === 'drivers'} 
          onClose={() => setActiveModal(null)} 
          title="Drivers on Shift" 
          icon={peopleOutline}
        >
          <IonList>
            {modalData.map((d: any) => (
              <IonItem key={d.id}>
                <IonIcon icon={personOutline} slot="start" />
                <IonLabel>
                  <div style={{ fontWeight: 800 }}>{d.name}</div>
                  <div style={{ fontSize: '12px', color: '#64748b' }}>Assigned: {d.plate_number}</div>
                </IonLabel>
                <div slot="end" style={{ fontSize: '10px', color: '#22c55e', fontWeight: 900 }}>
                  <span className="pulse-indicator"></span> ACTIVE
                </div>
              </IonItem>
            ))}
            {modalData.length === 0 && <div style={{ textAlign: 'center', padding: '20px' }}>No active drivers assigned.</div>}
          </IonList>
        </DashboardModal>

      </IonContent>
    </IonPage>
  );
}
