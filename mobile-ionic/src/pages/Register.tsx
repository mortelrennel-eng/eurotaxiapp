import React, { useState } from 'react'
import {
  IonPage, IonContent, IonInput, IonSelect, IonSelectOption,
  IonSpinner, IonToast, IonIcon,
} from '@ionic/react'
import {
  personAddOutline, mailOutline, phonePortraitOutline,
  checkmarkCircleOutline, arrowBackOutline, refreshOutline,
} from 'ionicons/icons'
import { useHistory } from 'react-router-dom'
import { registerAccount, verifyRegistrationOtp, resendRegistrationOtp } from '../api'
import logoImg from '../assets/logo.png'

type Step = 'form' | 'otp'
type OtpMethod = 'email' | 'sms'

export default function Register() {
  const history = useHistory()
  const [step, setStep] = useState<Step>('form')
  const [loading, setLoading] = useState(false)
  const [resending, setResending] = useState(false)
  const [otp, setOtp] = useState('')
  const [otpMethod, setOtpMethod] = useState<OtpMethod>('email')
  const [toast, setToast] = useState<{ show: boolean; message: string; color: string }>({ show: false, message: '', color: 'danger' })

  const [form, setForm] = useState({
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    phone_number: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
  })

  function updateField(field: string, value: string) {
    setForm(prev => ({ ...prev, [field]: value }))
  }

  function showToast(message: string, color = 'danger') {
    setToast({ show: true, message, color })
  }

  async function handleRegister() {
    const { first_name, last_name, phone_number, email, role, password, password_confirmation } = form

    if (!first_name.trim()) return showToast('First name is required', 'warning')
    if (!last_name.trim()) return showToast('Last name is required', 'warning')
    if (!phone_number.trim()) return showToast('Phone number is required', 'warning')
    if (!/^9[0-9]{9}$/.test(phone_number)) return showToast('Phone must start with 9 followed by 9 digits (e.g. 9123456789)', 'warning')
    if (!email.trim()) return showToast('Email is required', 'warning')
    if (!/^[a-zA-Z0-9.]{5,30}@gmail\.com$/i.test(email)) return showToast('Only Gmail addresses accepted (e.g. yourname@gmail.com)', 'warning')
    if (!role) return showToast('Please select a role', 'warning')
    if (!password) return showToast('Password is required', 'warning')
    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])/.test(password)) return showToast('Password must have uppercase, lowercase, number, and symbol', 'warning')
    if (password !== password_confirmation) return showToast('Passwords do not match', 'warning')

    setLoading(true)
    try {
      const res = await registerAccount({ ...form, otp_method: otpMethod })
      if (res.success) {
        showToast(`Verification code sent via ${otpMethod === 'sms' ? 'SMS' : 'Email'}!`, 'success')
        setStep('otp')
      } else {
        showToast(res.message || 'Registration failed')
      }
    } catch (e: any) {
      const errors = e.response?.data?.errors
      const msg = errors
        ? Object.values(errors as Record<string, string[]>).flat()[0]
        : e.response?.data?.message || 'Network error. Please try again.'
      showToast(msg)
    } finally {
      setLoading(false)
    }
  }

  async function handleVerifyOtp() {
    if (!otp || otp.length !== 6) return showToast('Please enter the 6-digit code', 'warning')

    setLoading(true)
    try {
      const res = await verifyRegistrationOtp(form.email, otp)
      if (res.success) {
        showToast('Account verified! You can now log in.', 'success')
        setTimeout(() => history.replace('/login'), 1500)
      } else {
        showToast(res.message || 'Invalid or expired code')
      }
    } catch (e: any) {
      showToast(e.response?.data?.message || 'Verification failed')
    } finally {
      setLoading(false)
    }
  }

  async function handleResend(newMethod?: OtpMethod) {
    const method = newMethod || otpMethod
    if (newMethod) setOtpMethod(newMethod)
    setResending(true)
    try {
      const res = await resendRegistrationOtp(form.email, method)
      if (res.success) {
        showToast(`New code sent via ${method === 'sms' ? 'SMS' : 'Email'}!`, 'success')
        setOtp('')
      } else {
        showToast(res.message || 'Resend failed')
      }
    } catch (e: any) {
      showToast(e.response?.data?.message || 'Could not resend code')
    } finally {
      setResending(false)
    }
  }

  const inputStyle = {
    '--background': '#f8fafc',
    '--padding-start': '16px',
    '--border-radius': '14px',
    border: '1.5px solid #e2e8f0',
    height: '50px',
    borderRadius: '14px',
  } as React.CSSProperties

  const labelStyle: React.CSSProperties = {
    display: 'block',
    fontSize: '11px',
    fontWeight: '800',
    color: '#64748b',
    marginBottom: '6px',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  }

  return (
    <IonPage>
      <IonContent scrollY={true}>
        <div style={{ minHeight: '100%', background: '#f8fafc', padding: '20px 20px 40px' }}>

          {/* Header */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24, paddingTop: 12 }}>
            <button
              onClick={() => step === 'otp' ? setStep('form') : history.replace('/login')}
              style={{ background: 'none', border: 'none', padding: 8, cursor: 'pointer', color: '#64748b' }}
            >
              <IonIcon icon={arrowBackOutline} style={{ fontSize: 22 }} />
            </button>
            <div>
              <div style={{ fontSize: 20, fontWeight: 900, color: '#0f172a' }}>
                {step === 'otp' ? 'Verify Your Code' : 'Create Account'}
              </div>
              <div style={{ fontSize: 12, color: '#64748b' }}>EuroTaxi Fleet Management</div>
            </div>
          </div>

          {/* Logo */}
          <div style={{ textAlign: 'center', marginBottom: 20 }}>
            <img src={logoImg} alt="EuroTaxi" style={{ width: 90, height: 'auto' }} />
          </div>

          {step === 'form' ? (
            /* ── Registration Form ── */
            <div style={{ background: '#fff', borderRadius: 24, padding: 24, boxShadow: '0 4px 24px rgba(0,0,0,0.06)' }}>

              {/* Name Row */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 14 }}>
                <div>
                  <label style={labelStyle}>First Name *</label>
                  <IonInput placeholder="Juan" value={form.first_name}
                    onIonInput={(e: any) => updateField('first_name', e.target.value || '')}
                    disabled={loading} style={inputStyle} />
                </div>
                <div>
                  <label style={labelStyle}>Middle Name</label>
                  <IonInput placeholder="Santos" value={form.middle_name}
                    onIonInput={(e: any) => updateField('middle_name', e.target.value || '')}
                    disabled={loading} style={inputStyle} />
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr auto', gap: 10, marginBottom: 14 }}>
                <div>
                  <label style={labelStyle}>Last Name *</label>
                  <IonInput placeholder="Dela Cruz" value={form.last_name}
                    onIonInput={(e: any) => updateField('last_name', e.target.value || '')}
                    disabled={loading} style={inputStyle} />
                </div>
                <div style={{ width: 90 }}>
                  <label style={labelStyle}>Suffix</label>
                  <IonSelect value={form.suffix} onIonChange={(e: any) => updateField('suffix', e.detail.value)}
                    interface="action-sheet" placeholder="N/A" disabled={loading}
                    style={{ ...inputStyle, '--padding-start': '10px' } as any}>
                    <IonSelectOption value="">N/A</IonSelectOption>
                    <IonSelectOption value="Jr.">Jr.</IonSelectOption>
                    <IonSelectOption value="Sr.">Sr.</IonSelectOption>
                    <IonSelectOption value="II">II</IonSelectOption>
                    <IonSelectOption value="III">III</IonSelectOption>
                    <IonSelectOption value="IV">IV</IonSelectOption>
                    <IonSelectOption value="V">V</IonSelectOption>
                  </IonSelect>
                </div>
              </div>

              {/* Phone */}
              <div style={{ marginBottom: 14 }}>
                <label style={labelStyle}>Phone Number * (9XXXXXXXXX)</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <div style={{
                    background: '#f1f5f9', border: '1.5px solid #e2e8f0', borderRadius: 14,
                    padding: '0 12px', height: 50, display: 'flex', alignItems: 'center',
                    fontSize: 14, fontWeight: 700, color: '#475569', flexShrink: 0
                  }}>+63</div>
                  <IonInput type="tel" placeholder="9123456789" value={form.phone_number}
                    onIonInput={(e: any) => updateField('phone_number', e.target.value || '')}
                    disabled={loading} maxlength={10}
                    style={{ ...inputStyle, flex: 1 } as any} />
                </div>
              </div>

              {/* Email */}
              <div style={{ marginBottom: 14 }}>
                <label style={labelStyle}>Gmail Address *</label>
                <IonInput type="email" placeholder="yourname@gmail.com" value={form.email}
                  onIonInput={(e: any) => updateField('email', e.target.value || '')}
                  disabled={loading} style={inputStyle} />
              </div>

              {/* Role */}
              <div style={{ marginBottom: 14 }}>
                <label style={labelStyle}>Role *</label>
                <IonSelect value={form.role} onIonChange={(e: any) => updateField('role', e.detail.value)}
                  interface="action-sheet" placeholder="Select your role" disabled={loading}
                  style={inputStyle as any}>
                  <IonSelectOption value="staff">Staff</IonSelectOption>
                  <IonSelectOption value="secretary">Secretary</IonSelectOption>
                  <IonSelectOption value="manager">Manager</IonSelectOption>
                  <IonSelectOption value="dispatcher">Dispatcher</IonSelectOption>
                </IonSelect>
              </div>

              {/* Password */}
              <div style={{ marginBottom: 14 }}>
                <label style={labelStyle}>Password *</label>
                <IonInput type="password" placeholder="Min 6: upper, lower, number, symbol"
                  value={form.password}
                  onIonInput={(e: any) => updateField('password', e.target.value || '')}
                  disabled={loading} style={inputStyle} />
              </div>

              <div style={{ marginBottom: 20 }}>
                <label style={labelStyle}>Confirm Password *</label>
                <IonInput type="password" placeholder="Re-enter your password"
                  value={form.password_confirmation}
                  onIonInput={(e: any) => updateField('password_confirmation', e.target.value || '')}
                  disabled={loading} style={inputStyle} />
              </div>

              {/* OTP Method Selector */}
              <div style={{ marginBottom: 24 }}>
                <label style={labelStyle}>Send verification code via</label>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
                  {(['email', 'sms'] as OtpMethod[]).map(m => (
                    <button
                      key={m}
                      onClick={() => setOtpMethod(m)}
                      style={{
                        height: 52, border: `2px solid ${otpMethod === m ? '#ca8a04' : '#e2e8f0'}`,
                        borderRadius: 14, background: otpMethod === m ? '#fef9c3' : '#f8fafc',
                        cursor: 'pointer', display: 'flex', alignItems: 'center',
                        justifyContent: 'center', gap: 8, fontSize: 13, fontWeight: 800,
                        color: otpMethod === m ? '#92400e' : '#64748b',
                        transition: 'all 0.2s',
                      }}
                    >
                      <IonIcon icon={m === 'email' ? mailOutline : phonePortraitOutline} style={{ fontSize: 18 }} />
                      {m === 'email' ? 'Email OTP' : 'SMS OTP'}
                    </button>
                  ))}
                </div>
                <p style={{ fontSize: 11, color: '#94a3b8', margin: '8px 0 0', textAlign: 'center' }}>
                  {otpMethod === 'email'
                    ? `Code will be sent to ${form.email || 'your Gmail'}`
                    : `Code will be sent to +63${form.phone_number || 'XXXXXXXXXX'}`}
                </p>
              </div>

              {/* Submit */}
              <button onClick={handleRegister} disabled={loading} style={{
                width: '100%', height: 54, borderRadius: 16,
                background: loading ? '#d97706' : '#ca8a04', color: '#fff',
                fontWeight: 900, fontSize: 15, border: 'none',
                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                boxShadow: '0 10px 20px rgba(202,138,4,0.2)',
                cursor: loading ? 'not-allowed' : 'pointer',
              }}>
                {loading
                  ? <IonSpinner name="crescent" style={{ width: 24, height: 24 }} />
                  : <><IonIcon icon={personAddOutline} /> Create Account</>}
              </button>

              <div style={{ textAlign: 'center', marginTop: 20 }}>
                <span style={{ fontSize: 13, color: '#64748b' }}>Already have an account? </span>
                <button onClick={() => history.replace('/login')}
                  style={{ background: 'none', border: 'none', color: '#ca8a04', fontWeight: 800, fontSize: 13, cursor: 'pointer' }}>
                  Sign In
                </button>
              </div>
            </div>

          ) : (
            /* ── OTP Verification Step ── */
            <div style={{ background: '#fff', borderRadius: 24, padding: 28, boxShadow: '0 4px 24px rgba(0,0,0,0.06)' }}>

              {/* Icon */}
              <div style={{
                width: 72, height: 72, borderRadius: '50%', background: '#fef3c7',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                margin: '0 auto 16px', fontSize: 34, color: '#ca8a04'
              }}>
                <IonIcon icon={otpMethod === 'sms' ? phonePortraitOutline : mailOutline} />
              </div>

              <h2 style={{ fontSize: 18, fontWeight: 900, color: '#0f172a', margin: '0 0 8px', textAlign: 'center' }}>
                {otpMethod === 'sms' ? 'Check Your SMS' : 'Check Your Email'}
              </h2>
              <p style={{ fontSize: 13, color: '#64748b', margin: '0 0 8px', textAlign: 'center' }}>
                We sent a 6-digit code to<br />
                <strong style={{ color: '#0f172a' }}>
                  {otpMethod === 'sms' ? `+63${form.phone_number}` : form.email}
                </strong>
              </p>

              {/* Switch Method */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, marginBottom: 24 }}>
                {(['email', 'sms'] as OtpMethod[]).map(m => (
                  <button
                    key={m}
                    onClick={() => handleResend(m)}
                    disabled={resending || loading}
                    style={{
                      height: 44, border: `2px solid ${otpMethod === m ? '#ca8a04' : '#e2e8f0'}`,
                      borderRadius: 12, background: otpMethod === m ? '#fef9c3' : '#f8fafc',
                      cursor: 'pointer', display: 'flex', alignItems: 'center',
                      justifyContent: 'center', gap: 6, fontSize: 12, fontWeight: 800,
                      color: otpMethod === m ? '#92400e' : '#94a3b8',
                    }}
                  >
                    <IonIcon icon={m === 'email' ? mailOutline : phonePortraitOutline} style={{ fontSize: 16 }} />
                    {m === 'email' ? 'Via Email' : 'Via SMS'}
                  </button>
                ))}
              </div>

              {/* OTP Input */}
              <div style={{ marginBottom: 20 }}>
                <label style={{ ...labelStyle, textAlign: 'center' as any }}>Enter 6-Digit Code</label>
                <IonInput
                  type="number" placeholder="000000" value={otp}
                  onIonInput={(e: any) => setOtp(e.target.value || '')}
                  disabled={loading} maxlength={6}
                  style={{
                    ...inputStyle, textAlign: 'center', fontSize: 28,
                    fontWeight: 900, letterSpacing: 8,
                  } as any}
                />
              </div>

              <button onClick={handleVerifyOtp} disabled={loading} style={{
                width: '100%', height: 54, borderRadius: 16,
                background: '#ca8a04', color: '#fff',
                fontWeight: 900, fontSize: 15, border: 'none',
                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                boxShadow: '0 10px 20px rgba(202,138,4,0.2)',
                cursor: loading ? 'not-allowed' : 'pointer', marginBottom: 16,
              }}>
                {loading
                  ? <IonSpinner name="crescent" style={{ width: 24, height: 24 }} />
                  : <><IonIcon icon={checkmarkCircleOutline} /> Verify & Activate</>}
              </button>

              <button onClick={() => handleResend()} disabled={resending || loading}
                style={{
                  width: '100%', background: 'none', border: 'none', color: '#64748b',
                  fontSize: 13, cursor: 'pointer', display: 'flex', alignItems: 'center',
                  justifyContent: 'center', gap: 6,
                }}>
                {resending
                  ? <><IonSpinner name="crescent" style={{ width: 16, height: 16 }} /> Resending...</>
                  : <><IonIcon icon={refreshOutline} /> Didn't receive? <span style={{ color: '#ca8a04', fontWeight: 700 }}>Resend Code</span></>}
              </button>
            </div>
          )}

        </div>

        <IonToast isOpen={toast.show} message={toast.message} color={toast.color}
          duration={3500} position="top"
          onDidDismiss={() => setToast({ ...toast, show: false })} />
      </IonContent>
    </IonPage>
  )
}
