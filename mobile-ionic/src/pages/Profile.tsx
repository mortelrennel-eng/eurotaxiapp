import React, { useState, useEffect } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonContent,
  IonIcon, IonSpinner, IonButtons, IonMenuButton,
  IonCard, IonLabel, IonButton, IonInput,
  IonToast, IonGrid, IonRow, IonCol
} from '@ionic/react';
import { 
  personOutline, mailOutline, lockClosedOutline, shieldCheckmarkOutline,
  timeOutline, checkmarkCircleOutline, keyOutline, arrowForwardOutline,
  notificationsOutline, calendarOutline, chevronForwardOutline,
  pencilOutline, shieldOutline, settingsOutline, sparklesOutline
} from 'ionicons/icons';
import { getStoredUser, updateProfile, changePassword } from '../api';

export default function Profile() {
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', color: 'dark' });
  
  const [profileForm, setProfileForm] = useState({
    firstName: '', middleName: '', lastName: '', email: ''
  });

  const [passForm, setPassForm] = useState({
    current: '', new: '', confirm: ''
  });

  useEffect(() => {
    const stored = getStoredUser();
    if (stored) {
      setUser(stored);
      const names = stored.name?.split(' ') || [];
      setProfileForm({
        firstName: names[0] || '',
        middleName: names.length > 2 ? names[1] : '',
        lastName: names[names.length - 1] || '',
        email: stored.email || ''
      });
    }
  }, []);

  const handleUpdate = async () => {
    setLoading(true);
    try {
      const res = await updateProfile(profileForm);
      if (res.success) {
        setToast({ show: true, message: 'Identity verified & updated', color: 'success' });
      } else {
        setToast({ show: true, message: res.message || 'Verification failed', color: 'danger' });
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Server unreachable', color: 'danger' });
    } finally {
      setLoading(false);
    }
  };

  const handleChangePass = async () => {
    if (passForm.new !== passForm.confirm) {
       setToast({ show: true, message: 'Security mismatch: Passwords do not align', color: 'warning' });
       return;
    }
    setLoading(true);
    try {
      const res = await changePassword(passForm);
      if (res.success) {
        setToast({ show: true, message: 'Security credentials updated', color: 'success' });
        setPassForm({ current: '', new: '', confirm: '' });
      }
    } catch (e: any) {
       setToast({ show: true, message: 'Error updating credentials', color: 'danger' });
    } finally {
      setLoading(false);
    }
  };

  const initial = user?.name ? user.name[0].toUpperCase() : 'R';

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--padding-top': '12px', '--padding-bottom': '12px' }}>
          <IonButtons slot="start"><IonMenuButton color="dark" /></IonButtons>
          <div style={{ padding: '0 8px' }}>
            <div className="header-modern-title"><span className="pulse-indicator pulse-blue"></span>Account Matrix</div>
            <div className="header-modern-sub">Personal identity & security configuration</div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <div className="animate-in" style={{ padding: '20px 16px 80px 16px' }}>
          
          {/* PROFILE OVERVIEW */}
          <div className="glass-card" style={{ padding: '30px', borderRadius: '30px', marginBottom: '24px', textAlign: 'center' }}>
             <div style={{ position: 'relative', display: 'inline-block', marginBottom: '20px' }}>
                <div style={{ width: '90px', height: '90px', borderRadius: '30px', background: '#ca8a04', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '36px', fontWeight: 900, boxShadow: '0 10px 25px rgba(202, 138, 4, 0.3)' }}>
                   {initial}
                </div>
                <div style={{ position: 'absolute', bottom: '-5px', right: '-5px', width: '28px', height: '28px', borderRadius: '10px', background: '#fff', border: '2px solid #ca8a04', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#ca8a04', fontSize: '14px' }}>
                   <IonIcon icon={checkmarkCircleOutline} />
                </div>
             </div>
             
             <div style={{ fontSize: '22px', fontWeight: 900, color: '#0f172a' }}>{user?.name || 'Authorized User'}</div>
             <div style={{ display: 'flex', justifyContent: 'center', gap: '8px', marginTop: '10px' }}>
                <div style={{ background: '#fef9c3', color: '#854d0e', fontSize: '10px', fontWeight: 900, padding: '4px 12px', borderRadius: '10px' }}>{user?.role?.toUpperCase() || 'EXECUTIVE'}</div>
                <div style={{ background: '#f0f9ff', color: '#0369a1', fontSize: '10px', fontWeight: 900, padding: '4px 12px', borderRadius: '10px' }}>ID: #99281</div>
             </div>
          </div>

          <div className="web-section-header">Identity Credentials</div>
          
          <div className="glass-card" style={{ padding: '24px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '20px' }}>
                <div>
                   <div className="executive-label">First Given Name</div>
                   <input 
                      style={{ width: '100%', height: '48px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 16px', fontSize: '14px', fontWeight: 700, outline: 'none' }}
                      value={profileForm.firstName}
                      onChange={(e) => setProfileForm({...profileForm, firstName: e.target.value})}
                   />
                </div>
                <div>
                   <div className="executive-label">Family Name</div>
                   <input 
                      style={{ width: '100%', height: '48px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 16px', fontSize: '14px', fontWeight: 700, outline: 'none' }}
                      value={profileForm.lastName}
                      onChange={(e) => setProfileForm({...profileForm, lastName: e.target.value})}
                   />
                </div>
                <div>
                   <div className="executive-label">Registered Email</div>
                   <input 
                      style={{ width: '100%', height: '48px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 16px', fontSize: '14px', fontWeight: 700, outline: 'none' }}
                      value={profileForm.email}
                      onChange={(e) => setProfileForm({...profileForm, email: e.target.value})}
                   />
                </div>
                <button 
                   onClick={handleUpdate}
                   style={{ width: '100%', height: '52px', background: '#0f172a', color: '#fff', borderRadius: '16px', fontWeight: 800, border: 'none', fontSize: '15px' }}
                >
                   {loading ? <IonSpinner name="dots" /> : 'SAVE CHANGES'}
                </button>
             </div>
          </div>

          <div className="web-section-header">Security Layer</div>
          
          <div className="glass-card" style={{ padding: '24px', borderRadius: '24px', marginBottom: '24px' }}>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '20px' }}>
                <div>
                   <div className="executive-label">New Password Access</div>
                   <input 
                      type="password"
                      style={{ width: '100%', height: '48px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 16px', fontSize: '14px', fontWeight: 700, outline: 'none' }}
                      value={passForm.new}
                      onChange={(e) => setPassForm({...passForm, new: e.target.value})}
                   />
                </div>
                <div>
                   <div className="executive-label">Verify New Password</div>
                   <input 
                      type="password"
                      style={{ width: '100%', height: '48px', borderRadius: '14px', border: '1.5px solid #e2e8f0', padding: '0 16px', fontSize: '14px', fontWeight: 700, outline: 'none' }}
                      value={passForm.confirm}
                      onChange={(e) => setPassForm({...passForm, confirm: e.target.value})}
                   />
                </div>
                <button 
                   onClick={handleChangePass}
                   style={{ width: '100%', height: '52px', background: '#ca8a04', color: '#fff', borderRadius: '16px', fontWeight: 800, border: 'none', fontSize: '15px' }}
                >
                   {loading ? <IonSpinner name="dots" /> : 'UPDATE CREDENTIALS'}
                </button>
             </div>
          </div>

          <div style={{ textAlign: 'center', padding: '20px' }}>
             <IonIcon icon={shieldOutline} style={{ fontSize: '32px', color: '#ca8a04', opacity: 0.2, marginBottom: '8px' }} />
             <p style={{ fontSize: '11px', color: '#94a3b8', fontWeight: 700 }}>SESSION SECURED BY AES-256 PARITY SYSTEM</p>
          </div>

        </div>
      </IonContent>
      <IonToast isOpen={toast.show} message={toast.message} color={toast.color} duration={2000} onDidDismiss={() => setToast({ ...toast, show: false })} />
    </IonPage>
  );
}
