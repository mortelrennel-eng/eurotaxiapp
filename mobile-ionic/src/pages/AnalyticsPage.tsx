import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonRefresher, IonRefresherContent, IonIcon, IonSpinner, 
  IonButtons, IonMenuButton, IonGrid, IonRow, IonCol, 
  IonButton, IonCard, IonCardContent, useIonToast, IonDatetime, IonModal
} from '@ionic/react';
import { 
  warningOutline, 
  trendingUpOutline, 
  trendingDownOutline, 
  walletOutline,
  checkmarkCircleOutline,
  closeCircleOutline,
  alertCircleOutline,
  peopleOutline,
  trendingUpSharp,
  trendingDownSharp,
  calendarOutline,
  barChartOutline,
  statsChartOutline,
  pieChartOutline,
  bulbOutline
} from 'ionicons/icons';
import { getAnalytics } from '../api';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, BarElement, Title, Tooltip, Legend, Filler, ArcElement
} from 'chart.js';
import { Line, Pie, Bar } from 'react-chartjs-2';
import { format, parseISO } from 'date-fns';

ChartJS.register(
  CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement,
  Title, Tooltip, Legend, Filler
);

const AnalyticsPage: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [presentToast] = useIonToast();

  const today = new Date().toISOString();
  const [fromDate, setFromDate] = useState<string>(new Date(new Date().setMonth(new Date().getMonth() - 6)).toISOString());
  const [toDate, setToDate] = useState<string>(today);

  const [showFromModal, setShowFromModal] = useState(false);
  const [showToModal, setShowToModal] = useState(false);

  const load = useCallback(async (f?: string, t?: string) => {
    setLoading(true);
    setError(null);
    try {
      const params = {
        from: format(parseISO(f || fromDate), 'yyyy-MM-dd'),
        to: format(parseISO(t || toDate), 'yyyy-MM-dd')
      };
      const res = await getAnalytics(params);
      if (res.success) {
        setData(res.data);
      } else {
        setError(res.message || 'Failed to sync analytics');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, [fromDate, toDate]);

  useEffect(() => { load(); }, []);

  const formatCurrency = (val: number) => '₱' + Math.round(val || 0).toLocaleString();
  const formatDateLabel = (iso: string) => format(parseISO(iso), 'MMM dd, yyyy');

  if (loading && !data) {
    return (
      <IonPage>
        <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
      </IonPage>
    );
  }

  const kpis = data?.kpi || {};
  const charts = data?.charts || {};
  const insights = data?.insights || {};

  const sparklineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } },
    scales: { x: { display: false }, y: { display: false } },
    elements: { point: { radius: 0 }, line: { borderWidth: 2, tension: 0.4 } }
  };

  const getSparkData = (values: number[] | undefined, color: string) => ({
    labels: (values || []).map((_, i) => i),
    datasets: [{
      data: values || [],
      borderColor: color,
      backgroundColor: color + '11',
      fill: true
    }]
  });

  const revVsExpTrendData = {
    labels: charts.rev_vs_exp?.map((t: any) => t.month) || [],
    datasets: [
      {
        label: 'Revenue',
        data: charts.rev_vs_exp?.map((t: any) => t.revenue) || [],
        borderColor: '#ca8a04',
        backgroundColor: 'rgba(202, 138, 4, 0.1)',
        fill: true,
        tension: 0.4,
        yAxisID: 'y',
      },
      {
        label: 'Expenses',
        data: charts.rev_vs_exp?.map((t: any) => t.expenses) || [],
        borderColor: '#64748b',
        borderDash: [5, 5],
        backgroundColor: 'rgba(100, 116, 139, 0.05)',
        fill: true,
        tension: 0.4,
        yAxisID: 'y',
      }
    ]
  };

  const expensePieData = {
    labels: charts.expense_dist?.map((e: any) => e.category) || [],
    datasets: [{
      data: charts.expense_dist?.map((e: any) => e.total) || [],
      backgroundColor: ['#ca8a04', '#1e293b', '#64748b', '#94a3b8', '#cbd5e1'],
      borderWidth: 0,
      hoverOffset: 15
    }]
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>Executive Insights</div>
            <div className="header-modern-sub">Performance Analytics & ROI Reports</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* SELECTOR GLASS CARD */}
          <div className="glass-card" style={{ padding: '16px', borderRadius: '24px', marginBottom: '24px', display: 'flex', gap: '12px' }}>
            <div style={{ flex: 1 }} onClick={() => setShowFromModal(true)}>
               <div className="executive-label" style={{ marginBottom: '4px' }}>Range Start</div>
               <div style={{ fontSize: '12px', fontWeight: 800, color: '#ca8a04' }}>{formatDateLabel(fromDate)}</div>
            </div>
            <div style={{ width: '1px', background: '#e2e8f0' }}></div>
            <div style={{ flex: 1 }} onClick={() => setShowToModal(true)}>
               <div className="executive-label" style={{ marginBottom: '4px' }}>Range End</div>
               <div style={{ fontSize: '12px', fontWeight: 800, color: '#ca8a04' }}>{formatDateLabel(toDate)}</div>
            </div>
            <div style={{ background: '#f8fafc', borderRadius: '12px', padding: '8px', display: 'flex', alignItems: 'center' }}>
               <IonIcon icon={calendarOutline} color="medium" />
            </div>
          </div>

          {/* KPI GRID */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
             
             <div className="glass-card premium-gradient-primary" style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2' }}>
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>Total Revenue Performance</div>
                <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{formatCurrency(kpis.revenue?.value)}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                   <IonIcon icon={kpis.revenue?.change >= 0 ? trendingUpSharp : trendingDownSharp} />
                   {Math.abs(kpis.revenue?.change || 0).toFixed(1)}% vs Previous
                </div>
                <div style={{ height: '40px', marginTop: '12px' }}>
                  <Line data={getSparkData(kpis.revenue?.sparks, '#fff')} options={sparklineOptions} />
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">Net Income</div>
                <div className="executive-value" style={{ color: '#ca8a04' }}>{formatCurrency(kpis.net_income?.value)}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#22c55e' }}>
                   +{Math.abs(kpis.net_income?.change || 0).toFixed(1)}% PROFIT
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">Total Expenses</div>
                <div className="executive-value" style={{ color: '#ef4444' }}>{formatCurrency(kpis.expenses?.value)}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>
                   OPERATIONAL COST
                </div>
             </div>
          </div>

          {/* TREND SECTION */}
          <div className="web-section-header">Financial Growth Trend</div>
          <IonCard style={{ margin: '0 0 24px 0', borderRadius: '24px' }}>
             <IonCardContent>
                <Line data={revVsExpTrendData} options={{
                   responsive: true,
                   interaction: { mode: 'index' as const, intersect: false },
                   plugins: { legend: { display: false } },
                   scales: { 
                      y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 }, callback: v => '₱' + (Number(v)/1000).toFixed(0) + 'k' } },
                      x: { grid: { display: false }, ticks: { font: { size: 9 } } } 
                   }
                }} />
                <div style={{ display: 'flex', justifyContent: 'center', gap: '20px', marginTop: '15px' }}>
                   <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '10px', fontWeight: 700 }}>
                      <span style={{ width: '10px', height: '10px', background: '#ca8a04', borderRadius: '2px' }}></span> REVENUE
                   </div>
                   <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '10px', fontWeight: 700 }}>
                      <span style={{ width: '10px', height: '10px', background: '#64748b', borderRadius: '2px' }}></span> EXPENSES
                   </div>
                </div>
             </IonCardContent>
          </IonCard>

          {/* EXPENSE DISTRIBUTION */}
          <div className="web-section-header">Expense Allocation</div>
          <IonCard style={{ margin: '0 0 24px 0', borderRadius: '24px' }}>
             <IonCardContent style={{ padding: '24px', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                <div style={{ height: '200px', width: '200px' }}>
                   <Pie data={expensePieData} options={{ 
                      plugins: { legend: { display: false } } 
                   }} />
                </div>
                <div style={{ width: '100%', marginTop: '20px' }}>
                   {charts.expense_dist?.map((e: any, i: number) => (
                      <div key={i} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                         <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                            <div style={{ width: '8px', height: '8px', borderRadius: '2px', background: expensePieData.datasets[0].backgroundColor[i] }}></div>
                            <span style={{ fontSize: '11px', fontWeight: 700, color: '#475569' }}>{e.category}</span>
                         </div>
                         <span style={{ fontSize: '11px', fontWeight: 900, color: '#0f172a' }}>{formatCurrency(e.total)}</span>
                      </div>
                   ))}
                </div>
             </IonCardContent>
          </IonCard>

          {/* MAINTENANCE ANALYTICS */}
          <div className="web-section-header">Fleet Maintenance Intelligence</div>
          <div className="glass-card" style={{ padding: '0', borderRadius: '24px', overflow: 'hidden', marginBottom: '24px' }}>
             <div style={{ padding: '16px', background: '#f8fafc', borderBottom: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between' }}>
                <div className="executive-label">High-Cost Unit Monitoring</div>
                <div className="executive-label" style={{ color: '#ef4444' }}>ALERT LEVEL: ACTIVE</div>
             </div>
             {charts.maintenance_per_unit?.map((u: any, idx: number) => (
                <div key={idx} style={{ padding: '16px', borderBottom: '1px solid #f1f5f9', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                   <div>
                      <div style={{ fontSize: '14px', fontWeight: 900, color: '#0f172a' }}>{u.unit_number}</div>
                      <div style={{ fontSize: '10px', color: '#64748b' }}>Frequency: {u.frequency} Service Events</div>
                   </div>
                   <div style={{ textAlign: 'right' }}>
                      <div style={{ fontSize: '14px', fontWeight: 900, color: '#ef4444' }}>{formatCurrency(u.total_cost)}</div>
                      <div style={{ fontSize: '9px', fontWeight: 800, color: u.action === 'CONSIDER RETIREMENT' ? '#b91c1c' : '#15803d' }}>
                         {u.action}
                      </div>
                   </div>
                </div>
             ))}
          </div>

          {/* INSIGHTS */}
          <div className="web-section-header">Strategic Executive Insights</div>
          <div style={{ display: 'grid', gap: '12px' }}>
             {[
               { icon: bulbOutline, title: 'Growth Strategy', text: insights.buy_new, color: '#ca8a04' },
               { icon: statsChartOutline, title: 'Margin Optimization', text: insights.lower_boundary, color: '#16a34a' },
               { icon: warningOutline, title: 'Fleet Decommissioning', text: insights.retire, color: '#ef4444' }
             ].map((ins, i) => (ins.text && (
               <div key={i} className="glass-card" style={{ padding: '20px', borderRadius: '20px', display: 'flex', gap: '16px' }}>
                  <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: ins.color + '15', color: ins.color, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>
                     <IonIcon icon={ins.icon} />
                  </div>
                  <div>
                     <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a', marginBottom: '4px' }}>{ins.title}</div>
                     <div style={{ fontSize: '11px', color: '#64748b', lineHeight: '1.5' }}>{ins.text}</div>
                  </div>
               </div>
             )))}
          </div>

        </div>

        <IonModal isOpen={showFromModal} onDidDismiss={() => setShowFromModal(false)} initialBreakpoint={0.5} breakpoints={[0, 0.5, 0.8]}>
           <div style={{ padding: '16px', background: '#fff' }}>
              <IonDatetime presentation="date" value={fromDate} max={today} onIonChange={(e: any) => { const v = Array.isArray(e.detail.value) ? e.detail.value[0] : e.detail.value; setFromDate(v!); setShowFromModal(false); load(v!, toDate); }} />
           </div>
        </IonModal>
        <IonModal isOpen={showToModal} onDidDismiss={() => setShowToModal(false)} initialBreakpoint={0.5} breakpoints={[0, 0.5, 0.8]}>
           <div style={{ padding: '16px', background: '#fff' }}>
              <IonDatetime presentation="date" value={toDate} min={fromDate} max={today} onIonChange={(e: any) => { const v = Array.isArray(e.detail.value) ? e.detail.value[0] : e.detail.value; setToDate(v!); setShowToModal(false); load(fromDate, v!); }} />
           </div>
        </IonModal>

      </IonContent>
    </IonPage>
  );
};

export default AnalyticsPage;
