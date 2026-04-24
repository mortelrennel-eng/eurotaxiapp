import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonSearchbar, IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonButton, IonSelect, IonSelectOption, IonRefresher, IonRefresherContent
} from '@ionic/react';
import { 
  locationOutline, radioButtonOnOutline, warningOutline, 
  notificationsOutline, carSportOutline,
  linkOutline, speedometer, searchOutline, funnelOutline,
  wifiOutline, shieldCheckmarkOutline, arrowForwardOutline,
  closeOutline
} from 'ionicons/icons';
import { getTracking } from '../api';

export default function Tracking() {
  const [units, setUnits] = useState<any[]>([]);
  const [filteredUnits, setFilteredUnits] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedUnit, setSelectedUnit] = useState<any>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('all');

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await getTracking();
      if (res && res.success && res.data) {
        const unitsList = Array.isArray(res.data.units) ? res.data.units : [];
        setUnits(unitsList);
        setFilteredUnits(unitsList);
        if (unitsList.length > 0 && !selectedUnit) setSelectedUnit(unitsList[0]);
      } else {
        setError(res?.message || 'Failed to sync tracking data');
      }
    } catch (e: any) { 
      setError(e.message || 'Connection error');
    } finally { 
      setLoading(false); 
    }
  }, [selectedUnit]);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    let list = Array.isArray(units) ? [...units] : [];
    if (searchTerm.trim()) {
      const q = searchTerm.toLowerCase();
      list = list.filter(u => 
        (u?.unit_number || '').toString().toLowerCase().includes(q) ||
        (u?.plate_number || '').toString().toLowerCase().includes(q)
      );
    }
    if (filterType === 'gps') {
      list = list.filter(u => u?.gps_link);
    } else if (filterType === 'offline') {
      list = list.filter(u => !u?.gps_link || u?.gps_status !== 'active');
    }
    setFilteredUnits(list);
  }, [searchTerm, filterType, units]);

  const safeUnits = Array.isArray(units) ? units : [];
  const linkedCount = safeUnits.filter(u => u?.gps_link).length;
  const activeCount = safeUnits.filter(u => u?.gps_status === 'active').length;

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-green"></span>Telemetry Live</div>
            <div className="header-modern-sub">Real-time geospatial asset monitoring</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <IonRefresher slot="fixed" onIonRefresh={(e) => load().finally(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div className="animate-in" style={{ padding: '16px' }}>
          
          {/* STATS GLASS CARD */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '12px', marginBottom: '24px' }}>
             <div className="mini-stat-card glass-card">
                <div className="executive-label" style={{ fontSize: '8px' }}>Active Fleet</div>
                <div style={{ fontSize: '18px', fontWeight: 900, color: '#0f172a' }}>{safeUnits.length}</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label" style={{ fontSize: '8px' }}>GPS Linked</div>
                <div style={{ fontSize: '18px', fontWeight: 900, color: '#16a34a' }}>{linkedCount}</div>
             </div>
             <div className="mini-stat-card glass-card">
                <div className="executive-label" style={{ fontSize: '8px' }}>In Motion</div>
                <div style={{ fontSize: '18px', fontWeight: 900, color: '#ca8a04' }}>{activeCount}</div>
             </div>
          </div>

          {/* MAP ENGINE VIEW */}
          <div className="glass-card" style={{ borderRadius: '24px', overflow: 'hidden', height: '280px', position: 'relative', marginBottom: '24px', border: '1.5px solid #e2e8f0' }}>
             {selectedUnit?.gps_link ? (
                <iframe src={selectedUnit.gps_link} title="GPS Engine" style={{ width: '100%', height: '100%', border: 'none' }} />
             ) : (
                <div style={{ height: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', background: '#f8fafc', padding: '40px', textAlign: 'center' }}>
                   <IonIcon icon={locationOutline} style={{ fontSize: '48px', color: '#cbd5e1', marginBottom: '12px' }} />
                   <div style={{ fontSize: '14px', fontWeight: 900, color: '#94a3b8' }}>NO ACTIVE TELEMETRY LINK</div>
                   <div style={{ fontSize: '11px', color: '#64748b', marginTop: '4px' }}>Unit {selectedUnit?.plate_number} has not established a GPS handshake</div>
                </div>
             )}
             
             {selectedUnit && (
                <div style={{ position: 'absolute', bottom: '16px', left: '16px', right: '16px', background: 'rgba(255,255,255,0.92)', backdropFilter: 'blur(10px)', padding: '12px', borderRadius: '16px', border: '1px solid #fff', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                   <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                      <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: '#1e293b', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}><IonIcon icon={carSportOutline} /></div>
                      <div>
                         <div style={{ fontSize: '14px', fontWeight: 900, color: '#0f172a' }}>{selectedUnit.plate_number}</div>
                         <div style={{ fontSize: '9px', fontWeight: 800, color: '#16a34a' }}><span className="pulse-indicator" style={{ width: '6px', height: '6px' }}></span> {selectedUnit.gps_status?.toUpperCase() || 'ONLINE'}</div>
                      </div>
                   </div>
                   <div style={{ textAlign: 'right' }}>
                      <div className="executive-label" style={{ fontSize: '8px' }}>Velocity</div>
                      <div style={{ fontSize: '15px', fontWeight: 900, color: '#ca8a04' }}>{selectedUnit.speed || 0} km/h</div>
                   </div>
                </div>
             )}
          </div>

          {/* ASSET SELECTOR */}
          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ position: 'relative', marginBottom: '16px' }}>
                <IonIcon icon={searchOutline} style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', color: '#64748b' }} />
                <input 
                  type="text" 
                  placeholder="Intercept specific asset ID..." 
                  style={{ width: '100%', height: '44px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 12px 0 40px', fontSize: '14px', outline: 'none' }}
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
             </div>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <div 
                   onClick={() => setFilterType('all')}
                   style={{ 
                      padding: '12px', borderRadius: '12px', textAlign: 'center', fontSize: '10px', fontWeight: 900,
                      background: filterType === 'all' ? '#ca8a04' : '#f8fafc',
                      color: filterType === 'all' ? '#fff' : '#64748b',
                      border: '1px solid #e2e8f0'
                   }}
                >ALL ASSETS</div>
                <div 
                   onClick={() => setFilterType('gps')}
                   style={{ 
                      padding: '12px', borderRadius: '12px', textAlign: 'center', fontSize: '10px', fontWeight: 900,
                      background: filterType === 'gps' ? '#ca8a04' : '#f8fafc',
                      color: filterType === 'gps' ? '#fff' : '#64748b',
                      border: '1px solid #e2e8f0'
                   }}
                >LIVE GPS ONLY</div>
             </div>
          </div>

          {/* UNIT LIST */}
          <div style={{ display: 'grid', gap: '12px', paddingBottom: '80px' }}>
             {filteredUnits.map((u: any, idx: number) => (
                <div 
                  key={idx} 
                  className={`glass-card ${selectedUnit?.id === u.id ? 'active-border' : ''}`} 
                  style={{ padding: '16px', borderRadius: '20px', transition: 'all 0.2s' }} 
                  onClick={() => setSelectedUnit(u)}
                >
                   <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                         <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: '#f8fafc', color: u.gps_link ? '#16a34a' : '#94a3b8', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>
                            <IonIcon icon={u.gps_link ? wifiOutline : locationOutline} />
                         </div>
                         <div>
                            <div style={{ fontSize: '14px', fontWeight: 900, color: '#0f172a' }}>{u.plate_number}</div>
                            <div style={{ fontSize: '10px', color: '#64748b', fontWeight: 700 }}>{u.driver_name || 'Idle (Standby)'}</div>
                         </div>
                      </div>
                      <div style={{ textAlign: 'right' }}>
                         <div style={{ fontSize: '13px', fontWeight: 900, color: u.gps_status === 'active' ? '#16a34a' : '#94a3b8' }}>{u.speed || 0} KM/H</div>
                         <div style={{ fontSize: '8px', fontWeight: 800, color: '#94a3b8' }}>TELEMETRY</div>
                      </div>
                   </div>
                </div>
             ))}
             {filteredUnits.length === 0 && (
                <div style={{ textAlign: 'center', padding: '60px 20px', color: '#cbd5e1' }}>
                   <IonIcon icon={searchOutline} style={{ fontSize: '48px', opacity: 0.3 }} />
                   <p style={{ fontSize: '12px', fontWeight: 700 }}>No assets found matching intercepts.</p>
                </div>
             )}
          </div>

        </div>
        
        <style>{`
           .active-border { border-color: #ca8a04 !important; background: #fffbeb !important; }
        `}</style>
      </IonContent>
    </IonPage>
  );
}
