import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonRefresher, IonRefresherContent,
  IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonSearchbar, IonSelect, IonSelectOption, IonButton,
  useIonAlert, useIonToast, IonModal
} from '@ionic/react';
import { 
  cashOutline, calendarOutline, addOutline, trashOutline,
  closeOutline, businessOutline, statsChartOutline,
  carSportOutline, receiptOutline, trendingUpOutline,
  trendingDownOutline, funnelOutline, searchOutline,
  walletOutline, alertCircleOutline
} from 'ionicons/icons';
import { getExpenses, deleteExpense } from '../api';
import { useHistory } from 'react-router-dom';

export default function Expenses() {
  const [data, setData] = useState<any>({ expenses: [], stats: {} });
  const [filtered, setFiltered] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<any>(null);
  
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [selectedStatus, setSelectedStatus] = useState('all');
  
  const history = useHistory();
  const [presentAlert] = useIonAlert();
  const [presentToast] = useIonToast();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getExpenses();
      if (res.success) {
        const payload = res.data || {};
        const expenses = Array.isArray(payload.expenses) ? payload.expenses : (Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : []));
        const stats = payload.stats || {};
        setData({ expenses, stats });
      } else {
        setError(res.message || 'Failed to sync expense records');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const applyFilters = useCallback(() => {
    let list = [...(data.expenses || [])];
    if (search.trim()) {
      const q = search.toLowerCase();
      list = list.filter(e => 
        (e?.description || '').toLowerCase().includes(q) || 
        (e?.category || '').toLowerCase().includes(q) ||
        (e?.reference_number || '').toLowerCase().includes(q) ||
        (e?.plate_number || '').toLowerCase().includes(q)
      );
    }
    if (selectedCategory && selectedCategory !== 'all') {
      list = list.filter(e => e?.category === selectedCategory);
    }
    if (selectedStatus && selectedStatus !== 'all') {
      list = list.filter(e => e?.status?.toLowerCase() === selectedStatus.toLowerCase());
    }
    setFiltered(list);
  }, [search, selectedCategory, selectedStatus, data]);

  useEffect(() => { applyFilters(); }, [applyFilters]);

  const formatCurrency = (val: any) => '₱' + Math.round(parseFloat(val) || 0).toLocaleString();

  const openDetail = (expense: any) => {
    setSelected(expense);
    setShowModal(true);
  };

  const handleDelete = (id: number) => {
    presentAlert({
      header: 'Confirm Delete',
      message: 'Remove this expense record?',
      buttons: [
        { text: 'Cancel', role: 'cancel' },
        {
          text: 'Delete',
          role: 'destructive',
          handler: async () => {
            try {
              const res = await deleteExpense(id);
              if (res.success) {
                setShowModal(false);
                presentToast({ message: 'Expense deleted', duration: 2000, color: 'success' });
                load();
              }
            } catch (e: any) {}
          }
        }
      ]
    });
  };

  const stats = data.stats || {};
  const categories = Array.from(new Set((data.expenses || []).map((e: any) => e.category))).filter(Boolean);

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-red"></span>Financial Outflows</div>
            <div className="header-modern-sub">Operational overhead & procurement logs</div>
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
             <div className="glass-card premium-gradient-danger" style={{ borderRadius: '24px', padding: '20px', gridColumn: 'span 2' }}>
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>This Month Expenditures</div>
                <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{formatCurrency(stats.this_month)}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                   <IonIcon icon={trendingUpOutline} />
                   Change: {stats.monthly_change || 0}% vs Last Month ({formatCurrency(stats.last_month)})
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">Total Volume</div>
                <div className="executive-value">{stats.total_records || 0}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>ALL-TIME RECORDS</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Pending Approval</div>
                <div className="executive-value" style={{ color: '#ca8a04' }}>{filtered.filter(e => e.status === 'pending').length}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>AWAITING REVIEW</div>
             </div>
          </div>

          {/* FILTERS */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search by description or ref..." 
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
                      value={selectedCategory}
                      onChange={(e) => setSelectedCategory(e.target.value)}
                   >
                      <option value="all">Categories</option>
                      {categories.map((cat: any) => <option key={cat} value={cat}>{cat}</option>)}
                   </select>
                </div>
                <div style={{ position: 'relative' }}>
                   <IonIcon icon={statsChartOutline} style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', color: '#ca8a04', pointerEvents: 'none' }} />
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 35px 0 12px', fontSize: '12px', fontWeight: 800, appearance: 'none', outline: 'none' }}
                      value={selectedStatus}
                      onChange={(e) => setSelectedStatus(e.target.value)}
                   >
                      <option value="all">All Status</option>
                      <option value="approved">Approved</option>
                      <option value="pending">Pending</option>
                      <option value="rejected">Rejected</option>
                   </select>
                </div>
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Expenditure Ledger</div>
             <IonButton fill="clear" size="small" onClick={() => history.push('/app/expenses/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> NEW EXPENSE
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !data.expenses.length ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {filtered.map((e: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px' }} onClick={() => openDetail(e)}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                           <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: '#f8fafc', color: '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '16px' }}>
                              <IonIcon icon={receiptOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{e.category}</div>
                              <div style={{ fontSize: '10px', color: '#64748b' }}>{new Date(e.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '8px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: e.status?.toLowerCase() === 'approved' ? '#dcfce7' : (e.status?.toLowerCase() === 'rejected' ? '#fee2e2' : '#fef9c3'),
                           color: e.status?.toLowerCase() === 'approved' ? '#166534' : (e.status?.toLowerCase() === 'rejected' ? '#991b1b' : '#854d0e')
                        }}>
                           {(e.status || 'PENDING').toUpperCase()}
                        </div>
                     </div>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
                        <div>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Allocation</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>{e.plate_number !== 'N/A' ? e.plate_number : 'Office Operation'}</div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Total Amount</div>
                           <div style={{ fontSize: '16px', fontWeight: 900, color: '#ef4444' }}>{formatCurrency(e.amount)}</div>
                        </div>
                     </div>
                  </div>
               ))}
               {filtered.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={receiptOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No expense records found.</p>
                  </div>
               )}
            </div>
          )}

        </div>

        {/* DETAIL MODAL */}
        <IonModal isOpen={showModal} onDidDismiss={() => setShowModal(false)} initialBreakpoint={0.75} breakpoints={[0, 0.75, 0.9]}>
           <div className="modal-content-modern" style={{ padding: '24px', background: '#fff' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                 <div className="executive-label">Transaction Details</div>
                 <IonButton fill="clear" color="dark" onClick={() => setShowModal(false)}><IonIcon icon={closeOutline} /></IonButton>
              </div>

              {selected && (
                <div className="animate-in">
                   <div style={{ display: 'flex', gap: '16px', alignItems: 'center', marginBottom: '24px' }}>
                      <div style={{ width: '56px', height: '56px', borderRadius: '18px', background: '#fef2f2', color: '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '28px' }}>
                        <IonIcon icon={walletOutline} />
                      </div>
                      <div>
                         <div style={{ fontSize: '24px', fontWeight: 900, color: '#ef4444' }}>{formatCurrency(selected.amount)}</div>
                         <div style={{ fontSize: '12px', color: '#64748b', fontWeight: 800 }}>Ref: {selected.reference_number || '---'}</div>
                      </div>
                   </div>

                   <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '24px' }}>
                      <div>
                         <div className="executive-label" style={{ marginBottom: '4px' }}>Category</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selected.category}</div>
                      </div>
                      <div>
                         <div className="executive-label" style={{ marginBottom: '4px' }}>Status</div>
                         <div style={{ fontSize: '12px', fontWeight: 900, color: selected.status === 'approved' ? '#16a34a' : '#ef4444' }}>{(selected.status || 'PENDING').toUpperCase()}</div>
                      </div>
                      <div>
                         <div className="executive-label" style={{ marginBottom: '4px' }}>Allocation</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selected.plate_number !== 'N/A' ? selected.plate_number : 'Office'}</div>
                      </div>
                      <div>
                         <div className="executive-label" style={{ marginBottom: '4px' }}>Date</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{new Date(selected.date).toLocaleDateString()}</div>
                      </div>
                   </div>

                   <div className="executive-label" style={{ marginBottom: '8px' }}>Description</div>
                   <div style={{ background: '#f8fafc', padding: '16px', borderRadius: '16px', fontSize: '13px', color: '#475569', lineHeight: '1.6', marginBottom: '24px' }}>
                      "{selected.description || 'No description provided.'}"
                   </div>

                   <div style={{ display: 'flex', gap: '12px' }}>
                      <button 
                         style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#3b82f6', color: '#fff', fontWeight: 800, border: 'none', fontSize: '14px' }}
                         onClick={() => { setShowModal(false); history.push(`/app/expenses/${selected.id}/edit`); }}
                      >Edit Transaction</button>
                      <button 
                         style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#fef2f2', color: '#ef4444', fontWeight: 800, border: 'none', fontSize: '14px' }}
                         onClick={() => handleDelete(selected.id)}
                      >Delete</button>
                   </div>
                </div>
              )}
           </div>
        </IonModal>
      </IonContent>
    </IonPage>
  );
}
