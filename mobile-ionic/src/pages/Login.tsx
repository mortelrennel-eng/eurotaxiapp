import React, { useState } from 'react'
import {
  IonPage, IonContent, IonButton, IonInput, IonSpinner, IonToast, IonIcon,
} from '@ionic/react'
import { logInOutline } from 'ionicons/icons'
import { login } from '../api'
import { useHistory } from 'react-router-dom'
import logoImg from '../assets/logo.png'

export default function Login() {
  const [identifier, setIdentifier] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [toast, setToast] = useState<{ show: boolean; message: string; color: string }>({ show: false, message: '', color: 'danger' })
  const history = useHistory()

  async function handleLogin() {
    if (!identifier.trim()) {
      setToast({ show: true, message: 'Please enter your email or phone', color: 'warning' })
      return
    }
    if (!password.trim()) {
      setToast({ show: true, message: 'Please enter your password', color: 'warning' })
      return
    }

    setLoading(true)
    try {
      const res = await login(identifier.trim(), password)
      if (res.success && res.data?.token) {
        localStorage.setItem('token', res.data.token)
        if (res.data.user) {
          localStorage.setItem('user', JSON.stringify(res.data.user))
        }
        setToast({ show: true, message: 'Welcome back!', color: 'success' })
        setTimeout(() => history.replace('/app/dashboard'), 400)
      } else {
        setToast({ show: true, message: res.message || 'Login failed', color: 'danger' })
      }
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Network error'
      setToast({ show: true, message: msg, color: 'danger' })
    } finally {
      setLoading(false)
    }
  }

  return (
    <IonPage>
      <IonContent scrollY={false}>
        <div className="login-container" style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', minHeight: '100%', padding: '20px', background: '#f8fafc' }}>
          {/* Branding */}
          <div className="login-brand animate-in" style={{ marginBottom: 40, textAlign: 'center' }}>
            <img
              src={logoImg}
              alt="EuroTaxi Logo"
              style={{ width: 180, height: 'auto', marginBottom: 8 }}
            />
            <h1 style={{ fontSize: '18px', fontWeight: '900', color: '#ca8a04', letterSpacing: '2px', margin: 0 }}>FLEET MANAGEMENT</h1>
          </div>

          {/* Login Card */}
          <div className="login-card animate-in" style={{ background: '#fff', borderRadius: '24px', padding: '32px', boxShadow: '0 10px 40px rgba(0,0,0,0.06)', border: '1px solid #f1f5f9' }}>
            <div style={{ marginBottom: 20 }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: '800', color: '#64748b', marginBottom: 8, textTransform: 'uppercase' }}>Email or Phone</label>
              <IonInput
                type="email"
                placeholder="admin@eurotaxi.com"
                value={identifier}
                onIonInput={(e: any) => setIdentifier(e.target.value || '')}
                disabled={loading}
                style={{ '--background': '#f8fafc', '--padding-start': '16px', '--border-radius': '14px', border: '1.5px solid #e2e8f0', height: '50px' }}
              />
            </div>

            <div style={{ marginBottom: 28 }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: '800', color: '#64748b', marginBottom: 8, textTransform: 'uppercase' }}>Password</label>
              <IonInput
                type="password"
                placeholder="••••••••"
                value={password}
                onIonInput={(e: any) => setPassword(e.target.value || '')}
                disabled={loading}
                onKeyDown={(e: any) => e.key === 'Enter' && handleLogin()}
                style={{ '--background': '#f8fafc', '--padding-start': '16px', '--border-radius': '14px', border: '1.5px solid #e2e8f0', height: '50px' }}
              />
            </div>

            <button
              onClick={handleLogin}
              disabled={loading}
              style={{
                width: '100%',
                height: '54px',
                borderRadius: '16px',
                background: '#ca8a04',
                color: '#fff',
                fontWeight: '900',
                fontSize: '15px',
                border: 'none',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '8px',
                boxShadow: '0 10px 20px rgba(202, 138, 4, 0.2)'
              }}
            >
              {loading ? (
                <IonSpinner name="crescent" style={{ width: 24, height: 24 }} />
              ) : (
                <>
                  <IonIcon icon={logInOutline} />
                  Sign In
                </>
              )}
            </button>

            <div style={{ textAlign: 'center', marginTop: 20 }}>
              <span style={{ fontSize: 13, color: '#64748b' }}>No account yet? </span>
              <button
                onClick={() => history.push('/register')}
                style={{ background: 'none', border: 'none', color: '#ca8a04', fontWeight: 800, fontSize: 13, cursor: 'pointer', padding: 0 }}
              >
                Sign Up
              </button>
            </div>
          </div>

        </div>

        <IonToast
          isOpen={toast.show}
          message={toast.message}
          color={toast.color}
          duration={2500}
          position="top"
          onDidDismiss={() => setToast({ ...toast, show: false })}
        />
      </IonContent>
    </IonPage>
  )
}
