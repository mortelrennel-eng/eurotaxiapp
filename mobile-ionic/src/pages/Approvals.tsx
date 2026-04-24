import React, { useState, useEffect, useCallback } from 'react';
import {
  IonContent, IonHeader, IonPage, IonTitle, IonToolbar, IonButtons, IonMenuButton,
  IonRefresher, IonRefresherContent, IonList, IonCard,
  IonCardContent, IonIcon, IonButton, IonSpinner,
  IonToast, IonSegment, IonSegmentButton, IonAlert, IonSearchbar, IonLabel
} from '@ionic/react';
import { checkmarkCircleOutline, documentTextOutline, checkmarkOutline, closeOutline, warningOutline, carSportOutline } from 'ionicons/icons';
import { getFranchises, approveFranchise, rejectFranchise } from '../api';

const Approvals: React.FC = () => {
  const [items, setItems] = useState<any[]>([]);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('pending');
  const [toast, setToast] = useState({ show: false, message: '', color: 'success' });
  const [alert, setAlert] = useState<{ show: boolean, id: number | null, action: 'approve' | 'reject' }>({ show: false, id: null, action: 'approve' });

  const loadData = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getFranchises({ status: filter });
      if (res && res.success) {
        const data = Array.isArray(res.data) ? res.data : [];
        setItems(data);
        setFiltered(data);
      } else {
        setError(res?.message || 'Failed to sync decisions');
        setItems([]);
        setFiltered([]);
      }
    } catch (err: any) {
      setError(err?.message || 'Connection error');
      setItems([]);
      setFiltered([]);
    } finally {

      setLoading(false);
    }
  }, [filter]);

  useEffect(() => { loadData(); }, [loadData]);

  useEffect(() => {
    const list = Array.isArray(items) ? items : [];
    if (!search.trim()) { setFiltered(list); return; }
    const q = search.toLowerCase();
    setFiltered(list.filter(i => 
       (i?.applicant_name || '').toLowerCase().includes(q) ||
       (i?.case_no || '').toLowerCase().includes(q)
    ));
  }, [search, items]);

  const handleAction = async () => {
    const { id, action } = alert;
    if (!id) return;
    try {
      const res = action === 'approve' ? await approveFranchise(id) : await rejectFranchise(id);
      if (res.success) {
        setToast({ show: true, message: `Case ${action}ed successfully!`, color: 'success' });
        loadData();
      } else {
        setToast({ show: true, message: res.message || 'Action failed', color: 'danger' });
      }
    } catch (err) {
      setToast({ show: true, message: 'Action error', color: 'danger' });
    } finally {
      setAlert({ show: false, id: null, action: 'approve' });
    }
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">Decision & Approval Center</div>
             <div className="header-modern-sub">Review and manage pending franchise approvals and legal records</div>
          </div>
        </IonToolbar>
        <IonToolbar>
          <IonSegment value={filter} onIonChange={e => setFilter(e.detail.value as string)} mode="md">
            <IonSegmentButton value="pending"><IonLabel>Pending</IonLabel></IonSegmentButton>
            <IonSegmentButton value="approved"><IonLabel>Approved</IonLabel></IonSegmentButton>
            <IonSegmentButton value="rejected"><IonLabel>Rejected</IonLabel></IonSegmentButton>
          </IonSegment>
        </IonToolbar>
        <IonToolbar>
          <IonSearchbar
            value={search}
            onIonInput={(e: any) => setSearch(e.target.value || '')}
            placeholder="Search by case or name..."
            debounce={250}
          />
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => loadData().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        {loading ? (
          <div className="loading-center"><IonSpinner name="lines" color="primary" /></div>
        ) : error ? (
           <div style={{ textAlign: 'center', marginTop: '100px', padding: '20px' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '60px', color: '#ef4444' }} />
              <p style={{ color: '#64748b' }}>{error}</p>
              <IonButton fill="outline" onClick={loadData}>Retry Sync</IonButton>
           </div>
        ) : (
          <div className="animate-in">
             <div className="module-stats-row">
              <div className="mini-stat-card">
                <div className="mini-stat-label">Queue</div>
                <div className="mini-stat-value">{items.length}</div>
                <div className="mini-stat-sub">Waiting cases</div>
              </div>
              <div className="mini-stat-card">
                <div className="mini-stat-label">Status</div>
                <div className="mini-stat-value" style={{ color: filter === 'pending' ? '#b45309' : '#166534' }}>{filter.toUpperCase()}</div>
                <div className="mini-stat-sub">Active view</div>
              </div>
            </div>

            {(!filtered || (Array.isArray(filtered) && filtered.length === 0)) ? (
               <div className="empty-state">
                  <IonIcon icon={checkmarkCircleOutline} style={{ color: '#10b981' }} />
                  <p>No records found in this category.</p>
               </div>
            ) : (
              <IonList style={{ background: 'transparent' }}>
                {(filtered || []).map((item, idx) => {
                  if (!item) return null;
                  return (
                  <IonCard key={item.id || idx} className="animate-in">
                     <IonCardContent style={{ padding: '20px' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '15px' }}>
                           <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                              <div style={{ width: 40, height: 40, borderRadius: '10px', background: '#fef3c7', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '18px', color: '#ca8a04' }}>
                                 <IonIcon icon={documentTextOutline} />
                              </div>
                              <div style={{ flex: 1, marginLeft: '12px' }}>
                                 <div className="list-item-title">CASE: {item?.case_no || 'TBD'}</div>
                                 <div className="list-item-sub">{item?.applicant_name || 'UNDEFINED'}</div>
                              </div>
                           </div>
                           <span className={`status-chip ${item?.status === 'approved' ? 'status-active' : (item?.status === 'rejected' ? 'status-danger' : 'status-maint')}`}>
                              {(item?.status || 'PENDING').toUpperCase()}
                           </span>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px', background: '#f8fafc', padding: '12px', borderRadius: '12px', marginBottom: '15px' }}>
                            <div>
                               <div style={{ fontSize: '9px', fontWeight: '800', color: '#94a3b8', textTransform: 'uppercase' }}>Type</div>
                               <div style={{ fontSize: '11px', fontWeight: '700', color: '#1e293b' }}>{item?.type_of_application || 'Default'}</div>
                            </div>
                            <div>
                               <div style={{ fontSize: '9px', fontWeight: '800', color: '#94a3b8', textTransform: 'uppercase' }}>Filed Date</div>
                               <div style={{ fontSize: '11px', fontWeight: '700', color: '#1e293b' }}>{item?.date_filed || 'Recent'}</div>
                            </div>
                        </div>

                        {item?.status === 'pending' && (
                           <div style={{ display: 'flex', gap: '10px' }}>
                              <IonButton expand="block" color="success" style={{ flex: 1, '--border-radius': '12px', height: '45px', fontWeight: '900' }} onClick={() => setAlert({ show: true, id: item.id, action: 'approve' })}>
                                 <IonIcon slot="start" icon={checkmarkOutline} /> APPROVE
                              </IonButton>
                              <IonButton expand="block" color="danger" fill="outline" style={{ flex: 1, '--border-radius': '12px', height: '45px', fontWeight: '900' }} onClick={() => setAlert({ show: true, id: item.id, action: 'reject' })}>
                                 <IonIcon slot="start" icon={closeOutline} /> REJECT
                              </IonButton>
                           </div>
                        )}
                     </IonCardContent>
                  </IonCard>
                )})}
              </IonList>
            )}
          </div>
        )}

        <IonAlert
          isOpen={alert.show}
          onDidDismiss={() => setAlert({ ...alert, show: false })}
          header={'CONFIRM DECISION'}
          message={`Are you sure you want to ${alert.action} this application?`}
          buttons={[
            { text: 'Cancel', role: 'cancel' },
            { text: 'Confirm', handler: handleAction }
          ]}
        />
        <IonToast
          isOpen={toast.show}
          message={toast.message}
          color={toast.color}
          duration={2000}
          onDidDismiss={() => setToast({ ...toast, show: false })}
        />
      </IonContent>
    </IonPage>
  );
};

export default Approvals;
