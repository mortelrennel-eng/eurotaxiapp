import React, { useState, useEffect, useCallback } from 'react';
import {
  IonContent, IonHeader, IonPage, IonTitle, IonToolbar, IonButtons, IonMenuButton,
  IonRefresher, IonRefresherContent, IonList, IonItem, IonLabel, IonBadge, IonCard,
  IonCardHeader, IonCardTitle, IonCardContent, IonIcon, IonSpinner, IonButton,
  IonSegment, IonSegmentButton, IonGrid, IonRow, IonCol, IonSearchbar
} from '@ionic/react';
import { 
  timeOutline, carSportOutline, alertCircleOutline, calendarOutline, 
  warningOutline, personOutline, chevronForwardOutline, funnelOutline,
  searchOutline, carOutline, constructOutline, settingsOutline,
  codeOutline, shieldCheckmarkOutline
} from 'ionicons/icons';
import { getCoding } from '../api';

const Coding: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [viewMode, setViewMode] = useState<'today' | 'weekly'>('today');

  const loadData = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getCoding();
      if (res && res.success) {
        setData(res.data || {});
      } else {
        setError(res?.message || 'Failed to sync coding schedule');
      }
    } catch (err: any) {
      setError(err.message || 'Connection error');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { loadData(); }, [loadData]);

  const todayUnits = Array.isArray(data?.today_units) ? data.today_units : [];
  const filteredToday = todayUnits.filter((u: any) => {
    const q = search.toLowerCase();
    const plate = (u?.plate_number || '').toLowerCase();
    const unitNo = (u?.unit_number || '').toString().toLowerCase();
    return plate.includes(q) || unitNo.includes(q);
  });

  const todayName = data?.today_name || '...';

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator"></span>Coding Restrictions</div>
            <div className="header-modern-sub">MMDA Unified Traffic Management</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => { loadData().then(() => e.detail.complete()); }}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* HEADER CONTROL GLASS CARD */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
                <div>
                   <div className="executive-label">Today's Schedule</div>
                   <div style={{ fontSize: '18px', fontWeight: 900, color: '#ca8a04' }}>{todayName.toUpperCase()}</div>
                </div>
                <div style={{ width: '44px', height: '44px', borderRadius: '12px', background: '#fef3c7', color: '#ca8a04', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>
                   <IonIcon icon={timeOutline} />
                </div>
             </div>
             
             <IonSegment value={viewMode} onIonChange={(e: any) => setViewMode(e.detail.value)} mode="md" style={{ '--background': '#f1f5f9', borderRadius: '12px', padding: '2px' }}>
                <IonSegmentButton value="today" style={{ '--color-checked': '#ca8a04', fontWeight: 800, fontSize: '12px' }}>Today</IonSegmentButton>
                <IonSegmentButton value="weekly" style={{ '--color-checked': '#ca8a04', fontWeight: 800, fontSize: '12px' }}>Calendar</IonSegmentButton>
             </IonSegment>
          </div>

          {loading ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : viewMode === 'today' ? (
             <div className="animate-in">
                <div className="web-section-header">Active Restrictions Today</div>
                <div style={{ position: 'relative', marginBottom: '16px' }}>
                   <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b', zIndex: 10 }} />
                   <input 
                     type="text" 
                     placeholder="Search restricted units..." 
                     style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                     value={search}
                     onChange={(e) => setSearch(e.target.value)}
                   />
                </div>

                <div style={{ display: 'grid', gap: '12px' }}>
                   {filteredToday.length === 0 ? (
                      <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                         <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                         <p style={{ fontSize: '12px', fontWeight: 700 }}>No coding restrictions today.</p>
                      </div>
                   ) : (
                      filteredToday.map((u: any, idx: number) => (
                        <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px', borderLeft: '4px solid #ef4444' }}>
                           <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                 <div style={{ width: '36px', height: '36px', borderRadius: '10px', background: '#fee2e2', color: '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '18px' }}>
                                    <IonIcon icon={carSportOutline} />
                                 </div>
                                 <div>
                                    <div style={{ fontSize: '14px', fontWeight: 900, color: '#0f172a' }}>{u.plate_number}</div>
                                    <div style={{ fontSize: '10px', color: '#64748b' }}>Unit #{u.unit_number}</div>
                                 </div>
                              </div>
                              <div style={{ fontSize: '8px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px', background: '#fee2e2', color: '#991b1b' }}>RESTRICTED</div>
                           </div>
                           <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', background: '#f8fafc', padding: '10px', borderRadius: '12px' }}>
                              <div>
                                 <div className="executive-label" style={{ fontSize: '8px' }}>Assigned D1</div>
                                 <div style={{ fontSize: '11px', fontWeight: 700, color: '#475569' }}>{u.driver1_name || 'N/A'}</div>
                              </div>
                              <div style={{ borderLeft: '1px solid #e2e8f0', paddingLeft: '12px' }}>
                                 <div className="executive-label" style={{ fontSize: '8px' }}>Assigned D2</div>
                                 <div style={{ fontSize: '11px', fontWeight: 700, color: '#475569' }}>{u.driver2_name || 'N/A'}</div>
                              </div>
                           </div>
                        </div>
                      ))
                   )}
                </div>
             </div>
          ) : (
             <div className="animate-in">
                <div className="web-section-header">Weekly Strategic Calendar</div>
                <div style={{ display: 'flex', overflowX: 'auto', gap: '12px', paddingBottom: '12px', margin: '0 -16px', padding: '0 16px 12px 16px' }}>
                   {['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].map((day) => {
                      const dayUnits = data?.coding_calendar?.[day] || [];
                      const isToday = day === todayName;
                      return (
                        <div key={day} className="glass-card" style={{ minWidth: '180px', padding: '16px', borderRadius: '20px', border: isToday ? '2px solid #ca8a04' : '1px solid #f1f5f9' }}>
                           <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '12px' }}>
                              <span style={{ fontSize: '10px', fontWeight: 900, color: isToday ? '#ca8a04' : '#64748b' }}>{day.toUpperCase()}</span>
                              <IonBadge color={isToday ? 'warning' : 'light'} style={{ fontSize: '10px' }}>{dayUnits.length}</IonBadge>
                           </div>
                           <div style={{ display: 'grid', gap: '8px' }}>
                              {dayUnits.slice(0, 5).map((u: any, ui: number) => (
                                 <div key={ui} style={{ background: '#f8fafc', padding: '6px 10px', borderRadius: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                    <span style={{ fontSize: '11px', fontWeight: 800, color: '#0f172a' }}>{u.plate_number}</span>
                                    <span style={{ fontSize: '9px', color: '#94a3b8' }}>#{u.unit_number}</span>
                                 </div>
                              ))}
                              {dayUnits.length > 5 && <div style={{ fontSize: '9px', textAlign: 'center', color: '#94a3b8', fontWeight: 700 }}>+ {dayUnits.length - 5} more</div>}
                           </div>
                        </div>
                      );
                   })}
                </div>

                <div className="web-section-header" style={{ marginTop: '24px' }}>MMDA Policy Engine</div>
                {data?.rules?.map((rule: any, idx: number) => (
                   <div key={idx} className="glass-card" style={{ padding: '20px', borderRadius: '20px', marginBottom: '12px' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '12px' }}>
                         <div style={{ fontSize: '15px', fontWeight: 900, color: '#0f172a' }}>{rule.coding_day}</div>
                         <div style={{ fontSize: '8px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px', background: rule.coding_type === 'full_ban' ? '#fee2e2' : '#f1f5f9', color: rule.coding_type === 'full_ban' ? '#991b1b' : '#64748b' }}>
                            {rule.coding_type?.replace('_', ' ').toUpperCase()}
                         </div>
                      </div>
                      <div style={{ fontSize: '12px', color: '#64748b', marginBottom: '12px' }}>
                         Restricted Endings: <strong style={{ color: '#ca8a04', fontSize: '14px' }}>{rule.restricted_plate_numbers}</strong>
                      </div>
                      {rule.time_start && (
                         <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '10px', fontWeight: 800, color: '#3b82f6', background: '#eff6ff', padding: '10px', borderRadius: '12px' }}>
                            <IonIcon icon={timeOutline} /> WINDOW: {rule.time_start} - {rule.time_end}
                         </div>
                      )}
                   </div>
                ))}
             </div>
          )}

        </div>
      </IonContent>
    </IonPage>
  );
};

export default Coding;
