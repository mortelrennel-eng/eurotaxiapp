import React, { useState, useEffect, useCallback } from 'react';
import {
  IonContent, IonHeader, IonPage, IonTitle, IonToolbar, IonButtons, IonMenuButton,
  IonRefresher, IonRefresherContent, IonIcon,
  IonSpinner, IonSearchbar, IonSelect, IonSelectOption, IonButton,
  IonModal, useIonAlert, useIonToast
} from '@ionic/react';
import { 
  walletOutline, calendarOutline, personOutline, warningOutline,
  statsChartOutline, cashOutline, receiptOutline, closeOutline,
  businessOutline, briefcaseOutline, timeOutline, trashOutline,
  trendingUpOutline, peopleOutline, carSportOutline, addOutline,
  documentTextOutline, checkmarkCircleOutline, arrowForwardOutline,
  funnelOutline, searchOutline
} from 'ionicons/icons';
import { getSalaries, deleteSalary } from '../api'; 
import { useHistory } from 'react-router-dom';

const Salaries: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [month, setMonth] = useState(new Date().getMonth() + 1);
  const [year, setYear] = useState(new Date().getFullYear());
  const [search, setSearch] = useState('');
  
  const [activeTab, setActiveTab] = useState<'salaries' | 'expenses'>('salaries');
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<any>(null);

  const history = useHistory();
  const [presentAlert] = useIonAlert();
  const [presentToast] = useIonToast();

  const loadData = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getSalaries(month, year);
      if (res.success) {
        setData(res.data || null);
      } else {
        setError(res.message || 'Failed to sync payroll data');
      }
    } catch (err: any) {
      setError(err.message || 'Connection error');
    } finally {
      setLoading(false);
    }
  }, [month, year]);

  useEffect(() => { loadData(); }, [loadData]);

  const formatCurrency = (val: number) => '₱' + Math.round(val || 0).toLocaleString();

  const openDetail = (item: any) => {
    setSelected(item);
    setShowModal(true);
  };

  const handleDelete = (id: number) => {
    presentAlert({
      header: 'Confirm Delete',
      message: 'Remove this payroll record?',
      buttons: [
        { text: 'Cancel', role: 'cancel' },
        {
          text: 'Delete',
          role: 'destructive',
          handler: async () => {
            try {
              const res = await deleteSalary(id);
              if (res.success) {
                setShowModal(false);
                presentToast({ message: 'Record deleted', duration: 2000, color: 'success' });
                loadData();
              }
            } catch (e: any) {}
          }
        }
      ]
    });
  };

  const summary = data?.summary || {};
  const salaryRecords = (data?.records || []).filter((s: any) => 
    !search || (s?.employee?.full_name || '').toLowerCase().includes(search.toLowerCase())
  );
  const expenseRecords = (data?.expenses || []).filter((e: any) => 
    !search || (e?.category || '').toLowerCase().includes(search.toLowerCase()) || (e?.description || '').toLowerCase().includes(search.toLowerCase())
  );

  const months = [
    { v: 1, n: 'January' }, { v: 2, n: 'February' }, { v: 3, n: 'March' },
    { v: 4, n: 'April' }, { v: 5, n: 'May' }, { v: 6, n: 'June' },
    { v: 7, n: 'July' }, { v: 8, n: 'August' }, { v: 9, n: 'September' },
    { v: 10, n: 'October' }, { v: 11, n: 'November' }, { v: 12, n: 'December' }
  ];
  const years = [2024, 2025, 2026];

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>Executive Payroll</div>
            <div className="header-modern-sub">Salary disbursements & operational overhead</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => loadData().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* STATS GRID */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
             <div className="glass-card premium-gradient-success" style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2' }}>
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>Total Payroll Disbursement</div>
                <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{formatCurrency(summary.total_salaries)}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                   <IonIcon icon={checkmarkCircleOutline} />
                   Verified for {months.find(m=>m.v===month)?.n} {year}
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">Headcount</div>
                <div className="executive-value">{summary.total_employees || 0}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>ACTIVE PERSONNEL</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Overhead</div>
                <div className="executive-value" style={{ color: '#ef4444' }}>{formatCurrency(summary.total_expenses)}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>OPERATIONAL COST</div>
             </div>
          </div>

          {/* FILTERS */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder={activeTab === 'salaries' ? "Search employee name..." : "Search expenses..."} 
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
                      {months.map(m => <option key={m.v} value={m.v}>{m.n}</option>)}
                   </select>
                </div>
                <div style={{ position: 'relative' }}>
                   <IonIcon icon={calendarOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 35px 0 12px', fontSize: '12px', fontWeight: 800, appearance: 'none', outline: 'none' }}
                      value={year}
                      onChange={(e) => setYear(parseInt(e.target.value))}
                   >
                      {years.map(y => <option key={y} value={y}>{y}</option>)}
                   </select>
                </div>
             </div>
          </div>

          {/* TAB CHIPS */}
          <div style={{ display: 'flex', gap: '8px', marginBottom: '24px' }}>
             <div 
                onClick={() => setActiveTab('salaries')}
                style={{ 
                   flex: 1, padding: '12px', borderRadius: '14px', textAlign: 'center', fontSize: '11px', fontWeight: 900,
                   background: activeTab === 'salaries' ? '#ca8a04' : '#f8fafc',
                   color: activeTab === 'salaries' ? '#fff' : '#64748b',
                   border: activeTab === 'salaries' ? 'none' : '1px solid #e2e8f0'
                }}
             >PAYROLL LEDGER</div>
             <div 
                onClick={() => setActiveTab('expenses')}
                style={{ 
                   flex: 1, padding: '12px', borderRadius: '14px', textAlign: 'center', fontSize: '11px', fontWeight: 900,
                   background: activeTab === 'expenses' ? '#ca8a04' : '#f8fafc',
                   color: activeTab === 'expenses' ? '#fff' : '#64748b',
                   border: activeTab === 'expenses' ? 'none' : '1px solid #e2e8f0'
                }}
             >OFFICE OVERHEAD</div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>{activeTab === 'salaries' ? 'Staff Payouts' : 'Expense Tracking'}</div>
             <IonButton fill="clear" size="small" onClick={() => history.push(activeTab==='salaries' ? '/app/salaries/new' : '/app/expenses/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> NEW ENTRY
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !data ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {(activeTab === 'salaries' ? salaryRecords : expenseRecords).map((s: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px' }} onClick={() => openDetail({...s, _view_type: activeTab === 'salaries' ? 'salary' : 'expense'})}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                           <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: '#f8fafc', color: activeTab === 'salaries' ? '#3b82f6' : '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '16px' }}>
                              <IonIcon icon={activeTab === 'salaries' ? personOutline : receiptOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{activeTab === 'salaries' ? s?.employee?.full_name : s.category}</div>
                              <div style={{ fontSize: '10px', color: '#64748b' }}>{new Date(s.pay_date || s.date).toLocaleDateString()}</div>
                           </div>
                        </div>
                        <div style={{ fontSize: '14px', fontWeight: 900, color: activeTab === 'salaries' ? '#16a34a' : '#ef4444' }}>
                           {formatCurrency(s.total_salary || s.amount)}
                        </div>
                     </div>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                         <div style={{ fontSize: '11px', fontWeight: 700, color: '#94a3b8' }}>{(s.employee_type || s.reference_number || 'N/A').toUpperCase()}</div>
                         <IonIcon icon={arrowForwardOutline} style={{ color: '#ca8a04', fontSize: '14px' }} />
                     </div>
                  </div>
               ))}
               {(activeTab === 'salaries' ? salaryRecords : expenseRecords).length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={walletOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No entries found for this period.</p>
                  </div>
               )}
            </div>
          )}

        </div>

        {/* DETAIL MODAL */}
        <IonModal isOpen={showModal} onDidDismiss={() => setShowModal(false)} initialBreakpoint={0.7} breakpoints={[0, 0.7, 0.9]}>
           <div className="modal-content-modern" style={{ padding: '24px', background: '#fff', height: '100%', overflowY: 'auto' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
                 <div className="executive-label">Transaction Summary</div>
                 <IonButton fill="clear" color="dark" onClick={() => setShowModal(false)}><IonIcon icon={closeOutline} /></IonButton>
              </div>

              {selected && (
                <div className="animate-in">
                   <div style={{ display: 'flex', gap: '16px', alignItems: 'center', marginBottom: '24px' }}>
                      <div style={{ width: '56px', height: '56px', borderRadius: '18px', background: selected._view_type === 'salary' ? '#eff6ff' : '#fef2f2', color: selected._view_type === 'salary' ? '#3b82f6' : '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '28px' }}>
                        <IonIcon icon={selected._view_type === 'salary' ? walletOutline : receiptOutline} />
                      </div>
                      <div>
                         <div style={{ fontSize: '24px', fontWeight: 900, color: selected._view_type === 'salary' ? '#16a34a' : '#ef4444' }}>{formatCurrency(selected.total_salary || selected.amount)}</div>
                         <div style={{ fontSize: '12px', color: '#64748b', fontWeight: 800 }}>RELEASED ASSETS</div>
                      </div>
                   </div>

                   <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '24px' }}>
                      <div>
                         <div className="executive-label">Recipient</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selected._view_type === 'salary' ? selected.employee?.full_name : selected.category}</div>
                      </div>
                      <div>
                         <div className="executive-label">Classification</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selected.employee_type || selected.reference_number || 'General'}</div>
                      </div>
                      <div>
                         <div className="executive-label">Approval Date</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{new Date(selected.pay_date || selected.date).toLocaleDateString()}</div>
                      </div>
                      <div>
                         <div className="executive-label">Status</div>
                         <div style={{ fontSize: '12px', fontWeight: 900, color: '#16a34a' }}>VERIFIED</div>
                      </div>
                   </div>

                   <div style={{ display: 'flex', gap: '12px', marginTop: '20px' }}>
                      <button 
                         style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#fef2f2', color: '#ef4444', fontWeight: 800, border: 'none', fontSize: '14px' }}
                         onClick={() => handleDelete(selected.id)}
                      >Void Record</button>
                   </div>
                </div>
              )}
           </div>
        </IonModal>
      </IonContent>
    </IonPage>
  );
};

export default Salaries;
