import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonRefresher, IonRefresherContent,
  IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonButton, IonToast, IonAlert
} from '@ionic/react';
import { 
  archiveOutline, warningOutline, carSportOutline, personOutline, 
  cashOutline, constructOutline, refreshOutline, trashOutline,
  notificationsOutline, calendarOutline, chevronBackOutline,
  receiptOutline, peopleOutline, shieldCheckmarkOutline, arrowBackOutline,
  refreshCircleOutline
} from 'ionicons/icons';
import { getArchive, restoreArchive, deletePermanent } from '../api';
import { useHistory } from 'react-router-dom';

export default function Archive() {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [actioning, setActioning] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [segment, setSegment] = useState('units');
  const [toast, setToast] = useState({ show: false, message: '', color: 'dark' });
  const [alert, setAlert] = useState({ show: false, id: 0, type: '' });
  const history = useHistory();

  const loadData = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getArchive();
      if (res.success) {
        setData(res.data);
      } else {
        setError(res.message || 'Failed to sync archive');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { loadData(); }, [loadData]);

  const handleAction = async (type: string, id: number, action: 'restore' | 'delete') => {
    setActioning(true);
    try {
        const typeMap: any = { 'units': 'unit', 'drivers': 'driver', 'expenses': 'expense', 'maintenance': 'maintenance', 'boundaries': 'boundary', 'staff': 'staff' };
        const backendType = typeMap[type];
        
        let res;
        if (action === 'restore') {
            res = await restoreArchive(backendType, id);
        } else {
            if (deletePermanent) res = await deletePermanent(backendType, id);
            else res = { success: false, message: 'Delete Permanent not implemented in API' };
        }

        if (res.success) {
            setToast({ show: true, message: `Record ${action === 'restore' ? 'restored' : 'deleted'} permanently`, color: 'success' });
            loadData();
        } else {
            setToast({ show: true, message: res.message || 'Action failed', color: 'danger' });
        }
    } catch (e: any) {
        setToast({ show: true, message: 'Error: ' + e.message, color: 'danger' });
    } finally {
        setActioning(false);
    }
  };

  const currentList = data?.[segment] || [];

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-yellow"></span>Vault Control</div>
            <div className="header-modern-sub">Data recovery & permanent resource deletion</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => loadData().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* SEGMENT SWITCHER */}
          <div className="glass-card" style={{ padding: '4px', borderRadius: '18px', marginBottom: '24px', display: 'flex', overflowX: 'auto', gap: '4px' }}>
             {['units', 'drivers', 'expenses', 'maintenance', 'boundaries', 'staff'].map(key => (
                <div 
                   key={key}
                   onClick={() => setSegment(key)}
                   style={{ 
                      padding: '10px 16px', borderRadius: '14px', whiteSpace: 'nowrap', fontSize: '10px', fontWeight: 900,
                      background: segment === key ? '#ca8a04' : 'transparent',
                      color: segment === key ? '#fff' : '#64748b'
                   }}
                >
                   {key.toUpperCase()} ({(data?.[key] || []).length})
                </div>
             ))}
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Archived {segment}</div>
             <div style={{ fontSize: '11px', fontWeight: 800, color: '#94a3b8' }}>{currentList.length} TOTAL</div>
          </div>

          {/* LIST */}
          {loading && !data ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {currentList.map((item: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '20px', borderRadius: '24px' }}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                           <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: '#f8fafc', color: '#64748b', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>
                              <IonIcon icon={segment === 'units' ? carSportOutline : (segment === 'expenses' ? receiptOutline : personOutline)} />
                           </div>
                           <div>
                              <div style={{ fontSize: '15px', fontWeight: 900, color: '#0f172a' }}>
                                 {segment === 'units' ? item.plate_number : (segment === 'expenses' ? item.category : (item.full_name || item.name))}
                              </div>
                              <div style={{ fontSize: '10px', color: '#64748b', fontWeight: 700 }}>REMOVED {new Date(item.deleted_at).toLocaleDateString()}</div>
                           </div>
                        </div>
                     </div>
                     
                     <div style={{ display: 'flex', gap: '8px', marginTop: '12px' }}>
                        <IonButton 
                           style={{ flex: 1, '--border-radius': '12px', '--color': '#ca8a04', '--border-color': '#ca8a04', fontSize: '11px', fontWeight: 900 }}
                           fill="outline" 
                           onClick={() => handleAction(segment, item.id, 'restore')}
                           disabled={actioning}
                        >
                           <IonIcon icon={refreshCircleOutline} slot="start" /> RESTORE
                        </IonButton>
                        <IonButton 
                           style={{ flex: 1, '--border-radius': '12px', '--color': '#ef4444', '--border-color': '#ef4444', fontSize: '11px', fontWeight: 900 }}
                           fill="outline"
                           onClick={() => setAlert({ show: true, id: item.id, type: segment })}
                           disabled={actioning}
                        >
                           <IonIcon icon={trashOutline} slot="start" /> PURGE
                        </IonButton>
                     </div>
                  </div>
               ))}
               {currentList.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '80px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>Vault is secure. No deleted {segment} found.</p>
                  </div>
               )}
            </div>
          )}

        </div>
      </IonContent>

      <IonAlert
         isOpen={alert.show}
         onDidDismiss={() => setAlert({ ...alert, show: false })}
         header="Permanent Erasure"
         message={`Purge this ${alert.type} record? This action cannot be reversed.`}
         buttons={[
            { text: 'Cancel', role: 'cancel' },
            { text: 'Erase Forever', role: 'destructive', handler: () => handleAction(alert.type, alert.id, 'delete') }
         ]}
      />

      <style>{`
         .glass-card ion-button { font-size: 11px; }
      `}</style>
      
      <IonToast isOpen={toast.show} message={toast.message} color={toast.color} duration={2000} onDidDismiss={() => setToast({ ...toast, show: false })} />
    </IonPage>
  );
}
