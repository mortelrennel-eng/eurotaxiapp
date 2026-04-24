import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButtons, IonBackButton, IonSegment, IonSegmentButton,
  IonLabel, IonList, IonItem, IonInput, IonButton,
  IonIcon, IonSpinner, IonToast, IonGrid, IonRow, IonCol,
  IonCard, IonCardContent, IonBadge, IonNote, IonSelect, IonSelectOption
} from '@ionic/react';
import { 
  saveOutline, carSportOutline, peopleOutline, 
  cashOutline, barChartOutline, mapOutline,
  constructOutline, calendarOutline, colorPaletteOutline,
  speedometerOutline, alertCircleOutline, closeOutline,
  shieldCheckmarkOutline, timeOutline, trendingUpOutline
} from 'ionicons/icons';
import { useParams, useHistory } from 'react-router-dom';
import { getUnit, updateUnit, getDrivers, Unit, getMaintenances, getProfitability } from '../api';

export default function UnitDetail() {
  const { id } = useParams<{ id: string }>();
  const history = useHistory();
  const [tab, setTab] = useState('specs');
  const [unit, setUnit] = useState<Partial<Unit>>({});
  const [drivers, setDrivers] = useState<any[]>([]);
  const [maintenances, setMaintenances] = useState<any[]>([]);
  const [profitability, setProfitability] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', color: 'success' });

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [uRes, dRes, mRes, pRes] = await Promise.all([
        getUnit(parseInt(id)),
        getDrivers(),
        getMaintenances({ unit_id: id }),
        getProfitability({ unit_id: id })
      ]);
      if (uRes.success) setUnit(uRes.data);
      if (dRes.success) setDrivers(dRes.data);
      if (mRes.success) setMaintenances(mRes.data);
      if (pRes.success) setProfitability(pRes.data);
    } catch (e: any) {
      setToast({ show: true, message: e.message, color: 'danger' });
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => { load(); }, [load]);

  const handleSave = async () => {
    setSaving(true);
    try {
      const res = await updateUnit(parseInt(id), unit);
      if (res.success) {
        setToast({ show: true, message: 'Unit updated successfully', color: 'success' });
      } else {
        setToast({ show: true, message: res.message || 'Save failed', color: 'danger' });
      }
    } catch (e: any) {
      setToast({ show: true, message: e.message, color: 'danger' });
    } finally {
      setSaving(false);
    }
  };

  if (loading) return (
    <IonPage>
      <div className="loading-center"><IonSpinner name="crescent" color="primary" /></div>
    </IonPage>
  );

  return (
    <IonPage>
      <IonHeader className="ion-no-border header-modern">
        <IonToolbar style={{ '--background': '#fff', '--padding-top': '8px' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/app/units" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">Unit Profile & Intelligence</div>
             <div className="header-modern-sub">Technical specifications, assignment history, and performance monitoring</div>
          </div>
        </IonToolbar>

        {/* HERO BANNER */}
        <div className="unit-hero-banner">
           <div className="hero-left">
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '4px' }}>
                 <div className="hero-plate">{unit.plate_number}</div>
                 <span className="hero-pill status">{unit.status?.toUpperCase()}</span>
                 <span className="hero-pill type">NEW</span>
              </div>
              <div className="hero-sub">{unit.make} {unit.model} ({unit.year})</div>
              <div className="hero-sub">Unit: {unit.unit_number}</div>
           </div>
           <div className="hero-right">
              <div className="hero-price">₱{unit.boundary_rate?.toLocaleString()}.00</div>
              <div className="hero-price-sub">Daily Boundary Rate</div>
           </div>
        </div>

        <IonToolbar style={{ '--background': '#fff', '--min-height': 'auto' }}>
          <IonSegment value={tab} onIonChange={(e: any) => setTab(e.detail.value)} scrollable mode="md" className="advanced-tabs">
            <IonSegmentButton value="specs"><IonLabel>Overview</IonLabel></IonSegmentButton>
            <IonSegmentButton value="assignment"><IonLabel>Drivers</IonLabel></IonSegmentButton>
            <IonSegmentButton value="coding"><IonLabel>Coding</IonLabel></IonSegmentButton>
            <IonSegmentButton value="boundary"><IonLabel>Boundary</IonLabel></IonSegmentButton>
            <IonSegmentButton value="maintenance"><IonLabel>Maintenance</IonLabel></IonSegmentButton>
            <IonSegmentButton value="roi"><IonLabel>ROI</IonLabel></IonSegmentButton>
            <IonSegmentButton value="location"><IonLabel>Location</IonLabel></IonSegmentButton>
            <IonSegmentButton value="dashcam"><IonLabel>Dashcam</IonLabel></IonSegmentButton>
          </IonSegment>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <div className="animate-in" style={{ padding: '16px' }}>
          
          {tab === 'specs' && (
            <div className="tab-pane animate-in">
              {/* KPI ROW */}
              <div className="unit-kpi-row">
                 <div className="mini-kpi-box">
                    <div className="kpi-icon blue"><IonIcon icon={peopleOutline} /></div>
                    <div className="kpi-content">
                       <div className="kpi-label">Drivers</div>
                       <div className="kpi-val">2/2</div>
                    </div>
                 </div>
                 <div className="mini-kpi-box">
                    <div className="kpi-icon green"><IonIcon icon={timeOutline} /></div>
                    <div className="kpi-content">
                       <div className="kpi-label">Next Coding</div>
                       <div className="kpi-val">{unit.coding_day || 'N/A'}</div>
                    </div>
                 </div>
                 <div className="mini-kpi-box">
                    <div className="kpi-icon purple"><IonIcon icon={trendingUpOutline} /></div>
                    <div className="kpi-content">
                       <div className="kpi-label">ROI</div>
                       <div className="kpi-val">{profitability?.roi_percentage || '0.0'}%</div>
                    </div>
                 </div>
                 <div className="mini-kpi-box">
                    <div className="kpi-icon orange"><IonIcon icon={constructOutline} /></div>
                    <div className="kpi-content">
                       <div className="kpi-label">Maint</div>
                       <div className="kpi-val">{maintenances?.length || 0}</div>
                    </div>
                 </div>
              </div>

              {/* TWO COLUMN GRID */}
              <IonGrid className="ion-no-padding" style={{ marginTop: '16px' }}>
                <IonRow>
                  <IonCol size="12" sizeMd="6">
                     <div className="detail-section-modern">
                        <div className="section-header-modern">
                           <IonIcon icon={alertCircleOutline} /> Basic Info
                        </div>
                        <div className="info-list-modern">
                           <div className="info-row"><span>Unit Number:</span> <strong>{unit.unit_number}</strong></div>
                           <div className="info-row"><span>Plate Number:</span> <strong>{unit.plate_number}</strong></div>
                           <div className="info-row"><span>Vehicle:</span> <strong>{unit.make} {unit.model}</strong></div>
                           <div className="info-row"><span>Year:</span> <strong>{unit.year}</strong></div>
                           <div className="info-row"><span>Color:</span> <strong>{unit.color || 'N/A'}</strong></div>
                           <div className="info-row"><span>Fuel Status:</span> <strong>{unit.fuel_status?.toUpperCase() || 'FULL'}</strong></div>
                           <div className="info-row"><span>Status:</span> <span className={`pill-status-mini ${unit.status}`}>{unit.status?.toUpperCase()}</span></div>
                           <div className="info-row" style={{ borderTop: '2px solid #f1f5f9', marginTop: '10px', paddingTop: '10px' }}>
                              <span>Boundary:</span> <strong style={{ color: '#3b82f6', fontSize: '16px' }}>₱{unit.boundary_rate?.toLocaleString()}.00</strong>
                           </div>
                        </div>
                     </div>
                  </IonCol>
                  
                  <IonCol size="12" sizeMd="6">
                     <div className="detail-section-modern">
                        <div className="section-header-modern">
                           <IonIcon icon={peopleOutline} /> Assignment
                        </div>
                        <div className="info-list-modern">
                           <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '15px' }}>
                              <div className="info-row" style={{ margin: 0 }}><span>Drivers:</span> <strong>2/2</strong></div>
                              <div className="info-row" style={{ margin: 0 }}><span>Status:</span> <span className="pill-status-mini warning">Full</span></div>
                           </div>
                           <div className="assignment-card-mini">
                              <div className="a-name">{unit.driver_name || 'No D1 Assigned'}</div>
                              <div className="a-contact">Primary Driver</div>
                           </div>
                           <div className="assignment-card-mini">
                              <div className="a-name">{unit.secondary_driver_name || 'No D2 Assigned'}</div>
                              <div className="a-contact">Secondary Driver</div>
                           </div>
                        </div>
                     </div>
                  </IonCol>
                </IonRow>
              </IonGrid>
            </div>
          )}

          {tab === 'assignment' && (
            <div className="tab-pane animate-in">
              <div className="section-title" style={{ padding: '0 10px', fontWeight: '900', fontSize: '15px', color: '#0f172a', marginBottom: '10px' }}>Assigned Staff</div>
              <IonCard className="form-card" style={{ margin: 0, borderRadius: '20px', border: '1px solid #f1f5f9' }}>
                <IonList lines="none">
                  <IonItem>
                    <div className="input-group" style={{ width: '100%', padding: '10px 0' }}>
                      <label style={{ fontSize: '12px', fontWeight: '800', color: '#64748b' }}><IonIcon icon={peopleOutline} /> Primary Driver (D1)</label>
                      <IonSelect 
                        value={unit.driver_id} 
                        onIonChange={(e) => setUnit({...unit, driver_id: e.detail.value})}
                        style={{ borderBottom: '1px solid #f1f5f9' }}
                      >
                        <IonSelectOption value={null}>NO ASSIGNED DRIVER</IonSelectOption>
                        {drivers.map(d => (
                          <IonSelectOption key={d.id} value={d.id}>{d.name}</IonSelectOption>
                        ))}
                      </IonSelect>
                    </div>
                  </IonItem>
                  <IonItem>
                    <div className="input-group" style={{ width: '100%', padding: '10px 0' }}>
                      <label style={{ fontSize: '12px', fontWeight: '800', color: '#64748b' }}><IonIcon icon={peopleOutline} /> Secondary Driver (D2)</label>
                      <IonSelect 
                        value={unit.secondary_driver_id} 
                        onIonChange={(e) => setUnit({...unit, secondary_driver_id: e.detail.value})}
                      >
                        <IonSelectOption value={null}>NO ASSIGNED DRIVER</IonSelectOption>
                        {drivers.map(d => (
                          <IonSelectOption key={d.id} value={d.id}>{d.name}</IonSelectOption>
                        ))}
                      </IonSelect>
                    </div>
                  </IonItem>
                </IonList>
              </IonCard>
            </div>
          )}

          {tab === 'coding' && (
            <div className="tab-pane animate-in">
               <div className="detail-section-modern">
                  <div className="section-header-modern"><IonIcon icon={timeOutline} /> Coding Schedule</div>
                  <div className="info-list-modern">
                     <div className="coding-card-big">
                        <div className="c-day">{unit.coding_day || 'N/A'}</div>
                        <div className="c-label">Assigned Coding Day</div>
                     </div>
                     <div className="info-badge-modern blue" style={{ marginTop: '20px' }}>
                        <IonIcon icon={calendarOutline} />
                        Next expected coding: <strong>Friday, April 17, 2026</strong>
                     </div>
                  </div>
               </div>
            </div>
          )}

          {tab === 'boundary' && (
            <div className="tab-pane animate-in">
               <div className="detail-section-modern">
                  <div className="section-header-modern"><IonIcon icon={cashOutline} /> Boundary Settings</div>
                  <div className="info-list-modern">
                     <div className="info-row"><span>Daily Tagged Rate:</span> <strong>₱{unit.boundary_rate?.toLocaleString()}.00</strong></div>
                     <div className="info-row"><span>Monthly Target:</span> <strong>₱{(unit.boundary_rate ? unit.boundary_rate * 30 : 0).toLocaleString()}.00</strong></div>
                     <div className="info-badge-modern green" style={{ marginTop: '15px' }}>
                        <IonIcon icon={shieldCheckmarkOutline} /> Correct boundary rate ensures accurate profitability tracking.
                     </div>
                  </div>
               </div>
            </div>
          )}

          {tab === 'maintenance' && (
            <div className="tab-pane animate-in">
               <div className="detail-section-modern">
                  <div className="section-header-modern"><IonIcon icon={constructOutline} /> Maintenance History</div>
                  <div className="info-list-modern ion-no-padding">
                     {(!maintenances || !Array.isArray(maintenances) || maintenances.length === 0) ? (
                        <div style={{ padding: '40px 20px', textAlign: 'center', color: '#94a3b8' }}>
                           <IonIcon icon={constructOutline} style={{ fontSize: '40px', marginBottom: '10px' }} />
                           <div style={{ fontSize: '12px', fontWeight: '800' }}>No Maintenance Records</div>
                        </div>
                     ) : (
                        maintenances.map((m: any, i: number) => (
                           <div key={i} className="history-row-item">
                              <div style={{ flex: 1 }}>
                                 <div className="h-title">{m.maintenance_type || 'Unspecified'}</div>
                                 <div className="h-date">{m.date_started || 'Date N/A'}</div>
                              </div>
                              <div style={{ textAlign: 'right' }}>
                                 <div className="h-amount">₱{m.cost?.toLocaleString() || '0'}</div>
                                 <div className={`h-status ${m.status || ''}`}>{m.status?.toUpperCase() || 'UNKNOWN'}</div>
                              </div>
                           </div>
                        ))
                     )}
                  </div>
               </div>
            </div>
          )}

          {tab === 'roi' && (
            <div className="tab-pane animate-in">
               <div className="detail-section-modern">
                  <div className="section-header-modern"><IonIcon icon={trendingUpOutline} /> Profitability analysis</div>
                  <div className="info-list-modern">
                     <div className="roi-summary-card">
                        <div className="roi-stat">
                           <div className="l">Purchase Cost</div>
                           <div className="v">₱{unit.purchase_cost?.toLocaleString() || '0'}</div>
                        </div>
                        <div className="roi-stat" style={{ borderTop: '1px solid #f1f5f9', paddingTop: '10px', marginTop: '10px' }}>
                           <div className="l">Total Collected</div>
                           <div className="v" style={{ color: '#16a34a' }}>₱{profitability?.total_collected?.toLocaleString() || '0'}</div>
                        </div>
                        <div className="roi-stat" style={{ borderTop: '1px solid #f1f5f9', paddingTop: '10px', marginTop: '10px' }}>
                           <div className="l">Net ROI</div>
                           <div className="v" style={{ color: '#3b82f6', fontSize: '20px' }}>{profitability?.roi_percentage || '0.0'}%</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
          )}

          {tab === 'location' && (
            <div className="tab-pane animate-in">
               <div className="detail-section-modern">
                  <div className="section-header-modern"><IonIcon icon={mapOutline} /> GPS Tracking</div>
                  <div className="info-list-modern" style={{ textAlign: 'center', padding: '40px 20px' }}>
                     <IonIcon icon={mapOutline} style={{ fontSize: '60px', color: '#3b82f6', marginBottom: '20px' }} />
                     <div style={{ fontSize: '15px', fontWeight: '800', color: '#1e293b' }}>Active GPS Monitoring</div>
                     <div style={{ fontSize: '12px', color: '#64748b', marginBottom: '25px' }}>Track this unit real-time via TracksolidPro integration.</div>
                     <IonButton expand="block" color="primary" mode="ios" onClick={() => window.open(unit.gps_link, '_blank')} disabled={!unit.gps_link}>
                        Open Live Tracking
                     </IonButton>
                  </div>
               </div>
            </div>
          )}

          {tab === 'dashcam' && (
            <div className="tab-pane animate-in">
               <div className="detail-section-modern">
                  <div className="section-header-modern"><IonIcon icon={shieldCheckmarkOutline} /> Dashcam Details</div>
                  <div className="info-list-modern" style={{ textAlign: 'center', padding: '40px 20px' }}>
                     <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '60px', color: '#16a34a', marginBottom: '20px' }} />
                     <div style={{ fontSize: '15px', fontWeight: '800', color: '#1e293b' }}>Unified Video Surveillance</div>
                     <div style={{ fontSize: '12px', color: '#64748b' }}>Dashcam hardware is active and recording. Link data will be available upon historical sync.</div>
                  </div>
               </div>
            </div>
          )}

        </div>
      </IonContent>

      <IonToast
        isOpen={toast.show}
        message={toast.message}
        color={toast.color}
        duration={2000}
        onDidDismiss={() => setToast({ ...toast, show: false })}
      />

      <style>{`
        .modal-header-icon { width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .unit-hero-banner { background: #3b82f6; padding: 16px 20px 24px 20px; display: flex; justify-content: space-between; align-items: flex-end; color: #fff; }
        .hero-plate { font-size: 24px; font-weight: 900; }
        .hero-pill { font-size: 9px; font-weight: 800; padding: 3px 8px; border-radius: 20px; text-transform: uppercase; background: rgba(255,255,255,0.25); color: #fff; }
        .hero-pill.status { background: #60a5fa; }
        .hero-sub { font-size: 11px; opacity: 0.9; margin-top: 2px; }
        .hero-price { font-size: 22px; font-weight: 900; }
        .hero-price-sub { font-size: 9px; opacity: 0.8; text-align: right; }

        .advanced-tabs { border-bottom: 1px solid #f1f5f9; }
        .advanced-tabs ion-segment-button { --color-checked: #3b82f6; --indicator-color: #3b82f6; min-height: 45px; }
        .advanced-tabs ion-label { font-size: 11px; font-weight: 800; }

        .unit-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; padding: 16px 0; }
        .mini-kpi-box { background: #fff; padding: 10px; border-radius: 12px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; }
        .kpi-icon { width: 32px; height: 32px; border-radius: 80%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .kpi-icon.blue { background: #eff6ff; color: #3b82f6; }
        .kpi-icon.green { background: #f0fdf4; color: #16a34a; }
        .kpi-icon.purple { background: #faf5ff; color: #a855f7; }
        .kpi-icon.orange { background: #fff7ed; color: #f97316; }
        .kpi-label { font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
        .kpi-val { font-size: 13px; font-weight: 900; color: #1e293b; }

        .detail-section-modern { background: #fff; border: 1px solid #f1f5f9; border-radius: 20px; height: 100%; }
        .section-header-modern { padding: 15px 20px; border-bottom: 1px solid #f8fafc; font-size: 13px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .section-header-modern ion-icon { color: #3b82f6; }
        .info-list-modern { padding: 15px 20px; }
        .info-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 12px; }
        .info-row span { color: #64748b; font-weight: 600; }
        .info-row strong { color: #1e293b; }
        
        .pill-status-mini { font-size: 9px; font-weight: 900; padding: 2px 8px; border-radius: 6px; }
        .pill-status-mini.active { background: #dcfce7; color: #166534; }
        .pill-status-mini.coding { background: #fee2e2; color: #991b1b; }
        .pill-status-mini.warning { background: #fef9c3; color: #854d0e; }

        .assignment-card-mini { background: #f8fafc; padding: 10px 15px; border-radius: 12px; margin-bottom: 10px; border: 1px solid #f1f5f9; }
        .a-name { font-size: 13px; font-weight: 800; color: #1e293b; }
        .a-contact { font-size: 11px; color: #94a3b8; margin-top: 2px; }

        .tab-pane { padding: 0 0 40px 0; }

        .coding-card-big { background: #f0f9ff; border: 1px solid #bae6fd; padding: 30px 20px; border-radius: 20px; text-align: center; }
        .c-day { font-size: 32px; font-weight: 900; color: #0369a1; text-transform: uppercase; }
        .c-label { font-size: 11px; font-weight: 800; color: #0ea5e9; text-transform: uppercase; margin-top: 5px; }
        
        .info-badge-modern { display: flex; align-items: center; gap: 10px; padding: 12px 15px; border-radius: 14px; font-size: 12px; font-weight: 600; }
        .info-badge-modern.blue { background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; }
        .info-badge-modern.green { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
        .info-badge-modern ion-icon { font-size: 18px; }

        .history-row-item { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; border-bottom: 1px solid #f8fafc; }
        .h-title { font-size: 13px; font-weight: 800; color: #1e293b; }
        .h-date { font-size: 11px; color: #94a3b8; }
        .h-amount { font-size: 13px; font-weight: 900; color: #0f172a; }
        .h-status { font-size: 9px; font-weight: 900; }
        .h-status.completed { color: #16a34a; }
        .h-status.pending { color: #eab308; }

        .roi-summary-card { padding: 5px 0; }
        .roi-stat { display: flex; justify-content: space-between; align-items: center; }
        .roi-stat .l { font-size: 12px; font-weight: 700; color: #64748b; }
        .roi-stat .v { font-size: 15px; font-weight: 900; color: #1e293b; }

        .animate-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </IonPage>
  );
}
