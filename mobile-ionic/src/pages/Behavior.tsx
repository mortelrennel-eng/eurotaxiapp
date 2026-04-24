import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonRefresher, IonRefresherContent,
  IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonSearchbar, IonSelect, IonSelectOption, IonButton,
  useIonAlert, useIonToast, IonModal
} from '@ionic/react';
import { 
  warningOutline, addOutline, alertCircleOutline, flashOutline,
  informationCircleOutline, closeOutline, videocamOutline,
  calendarOutline, carSportOutline, personOutline, trashOutline,
  shieldCheckmarkOutline, barChartOutline, searchOutline, funnelOutline
} from 'ionicons/icons';
import { getBehavior, deleteBehavior } from '../api';
import { useHistory } from 'react-router-dom';

export default function Behavior() {
  const [incidents, setIncidents] = useState<any[]>([]);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<any>(null);
  
  const [search, setSearch] = useState('');
  const [selectedType, setSelectedType] = useState('all');
  const [selectedSeverity, setSelectedSeverity] = useState('all');
  
  const history = useHistory();
  const [presentAlert] = useIonAlert();
  const [presentToast] = useIonToast();

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getBehavior();
      if (res.success) {
        setIncidents(res.data || []);
      } else {
        setError(res.message || 'Failed to sync behavioral info');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const applyFilters = useCallback(() => {
    let list = [...incidents];
    if (search.trim()) {
      const q = search.toLowerCase();
      list = list.filter(i => 
        (i?.driver_name || '').toLowerCase().includes(q) || 
        (i?.unit_number || '').toLowerCase().includes(q) ||
        (i?.description || '').toLowerCase().includes(q)
      );
    }
    if (selectedType && selectedType !== 'all') {
      list = list.filter(i => i?.incident_type === selectedType);
    }
    if (selectedSeverity && selectedSeverity !== 'all') {
      list = list.filter(i => i?.severity?.toLowerCase() === selectedSeverity.toLowerCase());
    }
    setFiltered(list);
  }, [search, selectedType, selectedSeverity, incidents]);

  useEffect(() => { applyFilters(); }, [applyFilters]);

  const openDetail = (incident: any) => {
    setSelected(incident);
    setShowModal(true);
  };

  const handleDelete = (id: number) => {
    presentAlert({
      header: 'Confirm Delete',
      message: 'Remove this incident record permanently?',
      buttons: [
        { text: 'Cancel', role: 'cancel' },
        {
          text: 'Delete',
          role: 'destructive',
          handler: async () => {
            try {
              const res = await deleteBehavior(id);
              if (res.success) {
                setShowModal(false);
                presentToast({ message: 'Incident deleted', duration: 2000, color: 'success' });
                load();
              }
            } catch (e: any) {}
          }
        }
      ]
    });
  };

  const last30Days = incidents.filter(i => {
    const d = new Date(i.timestamp);
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    return d >= thirtyDaysAgo;
  }).length;

  const criticalCount = incidents.filter(i => i?.severity?.toLowerCase() === 'critical').length;
  const highCount = incidents.filter(i => i?.severity?.toLowerCase() === 'high').length;

  const formatType = (type: string) => (type||'').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-red"></span>Risk Surveillance</div>
            <div className="header-modern-sub">Driver behavior & safety incident logs</div>
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
                <div className="executive-label" style={{ color: 'rgba(255,255,255,0.7)' }}>Critical Risk Factor</div>
                <div style={{ fontSize: '28px', fontWeight: 900, color: 'white', margin: '4px 0' }}>{criticalCount + highCount} Alerts</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px', fontWeight: 700, color: 'white' }}>
                   <IonIcon icon={warningOutline} />
                   Requires immediate management intervention
                </div>
             </div>

             <div className="mini-stat-card glass-card">
                <div className="executive-label">30D Volume</div>
                <div className="executive-value">{last30Days}</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>RECENT INCIDENTS</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label">Safety Rating</div>
                <div className="executive-value" style={{ color: '#16a34a' }}>94%</div>
                <div style={{ fontSize: '9px', fontWeight: 800, color: '#94a3b8' }}>FLEET COMPLIANCE</div>
             </div>
          </div>

          {/* FILTERS */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Search by driver or unit..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
             </div>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <div style={{ position: 'relative' }}>
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 12px', fontSize: '11px', fontWeight: 800, outline: 'none' }}
                      value={selectedType}
                      onChange={(e) => setSelectedType(e.target.value)}
                   >
                      <option value="all">Every Type</option>
                      <option value="speeding">Speeding</option>
                      <option value="hard_braking">Hard Braking</option>
                      <option value="cornering">Cornering</option>
                      <option value="other">Other</option>
                   </select>
                </div>
                <div style={{ position: 'relative' }}>
                   <select 
                      style={{ width: '100%', height: '44px', borderRadius: '12px', border: '1.5px solid #e2e8f0', background: '#f8fafc', padding: '0 12px', fontSize: '11px', fontWeight: 800, outline: 'none' }}
                      value={selectedSeverity}
                      onChange={(e) => setSelectedSeverity(e.target.value)}
                   >
                      <option value="all">Any Severity</option>
                      <option value="low">Low</option>
                      <option value="high">High</option>
                      <option value="critical">Critical</option>
                   </select>
                </div>
             </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
             <div className="web-section-header" style={{ marginBottom: '0' }}>Incident Log</div>
             <IonButton fill="clear" size="small" onClick={() => history.push('/app/behavior/new')} style={{ '--color': '#ca8a04', fontWeight: 800 }}>
                <IonIcon icon={addOutline} slot="start" /> RECORD EVENT
             </IonButton>
          </div>

          {/* LIST */}
          {loading && !incidents.length ? (
             <div style={{ textAlign: 'center', padding: '40px' }}><IonSpinner name="crescent" /></div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
               {filtered.map((i: any, idx: number) => (
                  <div key={idx} className="glass-card" style={{ padding: '16px', borderRadius: '20px' }} onClick={() => openDetail(i)}>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                           <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: '#f8fafc', color: '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '16px' }}>
                              <IonIcon icon={flashOutline} />
                           </div>
                           <div>
                              <div style={{ fontSize: '13px', fontWeight: 900, color: '#0f172a' }}>{formatType(i.incident_type)}</div>
                              <div style={{ fontSize: '10px', color: '#64748b' }}>{new Date(i.timestamp).toLocaleDateString()} at {new Date(i.timestamp).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })}</div>
                           </div>
                        </div>
                        <div style={{ 
                           fontSize: '8px', fontWeight: 900, padding: '4px 10px', borderRadius: '8px',
                           background: i.severity?.toLowerCase() === 'critical' ? '#fee2e2' : (i.severity?.toLowerCase() === 'high' ? '#ffedd5' : '#f1f5f9'),
                           color: i.severity?.toLowerCase() === 'critical' ? '#991b1b' : (i.severity?.toLowerCase() === 'high' ? '#9a3412' : '#64748b')
                        }}>
                           {(i.severity || 'LOW').toUpperCase()}
                        </div>
                     </div>
                     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
                        <div>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Operated By</div>
                           <div style={{ fontSize: '12px', fontWeight: 700, color: '#475569' }}>{i.driver_name || 'System User'}</div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                           <div className="executive-label" style={{ fontSize: '9px' }}>Vehicle Asset</div>
                           <div style={{ fontSize: '12px', fontWeight: 900, color: '#0f172a' }}>UNIT {i.unit_number}</div>
                        </div>
                     </div>
                  </div>
               ))}
               {filtered.length === 0 && (
                  <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                     <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                     <p style={{ fontSize: '12px', fontWeight: 700 }}>No safety incidents recorded recently.</p>
                  </div>
               )}
            </div>
          )}

        </div>

        {/* DETAIL MODAL */}
        <IonModal isOpen={showModal} onDidDismiss={() => setShowModal(false)} initialBreakpoint={0.75} breakpoints={[0, 0.75, 1]}>
           <div className="modal-content-modern" style={{ padding: '24px', background: '#fff', height: '100%', overflowY: 'auto' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
                 <div>
                    <div className="executive-label">Event Investigation</div>
                    <div style={{ fontSize: '18px', fontWeight: 900, color: '#0f172a' }}>Critical Insight Detail</div>
                 </div>
                 <IonButton fill="clear" color="dark" onClick={() => setShowModal(false)}><IonIcon icon={closeOutline} /></IonButton>
              </div>

              {selected && (
                <div className="animate-in">
                   <div style={{ display: 'flex', gap: '16px', alignItems: 'center', marginBottom: '24px', background: '#f8fafc', padding: '16px', borderRadius: '20px' }}>
                      <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: '#fee2e2', color: '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }}>
                        <IonIcon icon={warningOutline} />
                      </div>
                      <div>
                         <div style={{ fontSize: '15px', fontWeight: 900, color: '#0f172a' }}>{formatType(selected.incident_type)}</div>
                         <div style={{ fontSize: '11px', color: '#64748b', fontWeight: 700 }}>VERIFIED BY TELEMETRY</div>
                      </div>
                   </div>

                   <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '24px' }}>
                      <div>
                         <div className="executive-label">Unit Asset</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selected.unit_number} ({selected.plate_number})</div>
                      </div>
                      <div>
                         <div className="executive-label">Impact Severity</div>
                         <div style={{ fontSize: '13px', fontWeight: 900, color: selected.severity === 'critical' ? '#ef4444' : '#f97316' }}>{(selected.severity || 'LOW').toUpperCase()}</div>
                      </div>
                      <div>
                         <div className="executive-label">Primary Driver</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{selected.driver_name}</div>
                      </div>
                      <div>
                         <div className="executive-label">Occurrence Date</div>
                         <div style={{ fontSize: '14px', fontWeight: 800, color: '#0f172a' }}>{new Date(selected.timestamp).toLocaleDateString()}</div>
                      </div>
                   </div>

                   <div className="executive-label" style={{ marginBottom: '8px' }}>Internal Narrative</div>
                   <div style={{ background: '#f1f5f9', padding: '16px', borderRadius: '16px', fontSize: '13px', color: '#334155', lineHeight: '1.6', marginBottom: '24px' }}>
                      "{selected.description || 'No detailed narrative provided for this incident.'}"
                   </div>

                   {selected.video_url && (
                      <IonButton expand="block" fill="outline" style={{ '--border-radius': '14px', marginBottom: '24px', '--color': '#ca8a04', '--border-color': '#ca8a04' }} onClick={() => window.open(selected.video_url, '_blank')}>
                         <IonIcon icon={videocamOutline} slot="start" /> REVIEW FOOTAGE
                      </IonButton>
                   )}

                   <div style={{ display: 'flex', gap: '12px' }}>
                      <button 
                         style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#3b82f6', color: '#fff', fontWeight: 800, border: 'none', fontSize: '14px' }}
                         onClick={() => { setShowModal(false); history.push(`/app/behavior/${selected.id}/edit`); }}
                      >Update Record</button>
                      <button 
                         style={{ flex: 1, height: '48px', borderRadius: '14px', background: '#fef2f2', color: '#ef4444', fontWeight: 800, border: 'none', fontSize: '14px' }}
                         onClick={() => handleDelete(selected.id)}
                      >Remove Log</button>
                   </div>
                </div>
              )}
           </div>
        </IonModal>
      </IonContent>
    </IonPage>
  );
}
