import React, { useState, useEffect } from 'react';
import {
  IonContent, IonHeader, IonPage, IonTitle, IonToolbar, IonButtons, IonBackButton,
  IonList, IonItem, IonLabel, IonInput, IonSelect, IonSelectOption,
  IonButton, IonIcon, IonSpinner, useIonToast, IonFooter, IonNote
} from '@ionic/react';
import { 
  saveOutline, 
  cashOutline, 
  personOutline, 
  timeOutline, 
  calculatorOutline,
  receiptOutline
} from 'ionicons/icons';
import { getStaff, createSalary } from '../api';
import { useHistory } from 'react-router-dom';

const SalaryForm: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const [employees, setEmployees] = useState<any[]>([]);
  const [formData, setFormData] = useState({
    employee_id: '',
    basic_salary: 0,
    overtime_pay: 0,
    allowance: 0,
    deductions: 0,
    pay_date: new Date().toISOString().split('T')[0],
    notes: '',
  });

  const history = useHistory();
  const [presentToast] = useIonToast();

  useEffect(() => {
    const loadEmployees = async () => {
      try {
        const res = await getStaff();
        if (res.success) setEmployees(res.data || []);
      } catch (e) {
        console.error('Failed to load employees', e);
      }
    };
    loadEmployees();
  }, []);

  const totalSalary = Number(formData.basic_salary) + 
                     Number(formData.overtime_pay) + 
                     Number(formData.allowance) - 
                     Number(formData.deductions);

  const handleSubmit = async () => {
    if (!formData.employee_id || !formData.basic_salary) {
      presentToast({ message: 'Please complete all required fields', duration: 2000, color: 'warning' });
      return;
    }

    setLoading(true);
    try {
      const res = await createSalary({
        ...formData,
        total_salary: totalSalary
      });
      if (res.success) {
        presentToast({ message: 'Salary record saved successfully', duration: 2000, color: 'success' });
        history.goBack();
      } else {
        presentToast({ message: res.message || 'Failed to save record', duration: 2000, color: 'danger' });
      }
    } catch (e: any) {
      presentToast({ message: 'Connection error', duration: 2000, color: 'danger' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar>
          <IonButtons slot="start"><IonBackButton defaultHref="/app/salaries" /></IonButtons>
          <IonTitle style={{ fontWeight: 900, textTransform: 'uppercase', fontSize: '1rem' }}>Generate Payroll</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent className="ion-padding">
        <div className="animate-in">
          <div style={{ textAlign: 'center', marginBottom: '24px' }}>
             <div className="modal-detail-icon" style={{ background: '#fef9c3', color: '#ca8a04' }}>
                <IonIcon icon={cashOutline} />
             </div>
             <h2 style={{ fontWeight: 900, fontSize: '20px', color: '#0f172a', margin: '0' }}>New Salary Record</h2>
             <p style={{ fontSize: '12px', color: '#94a3b8' }}>Create an itemized payroll for staff or drivers</p>
          </div>

          <div className="glass-card" style={{ padding: '20px', borderRadius: '24px', marginBottom: '80px' }}>
            <IonList lines="none" style={{ background: 'transparent' }}>
              
              <div className="executive-label" style={{ marginBottom: '8px', marginLeft: '8px' }}>Employee Assignment</div>
              <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                <IonLabel position="stacked">SELECT EMPLOYEE</IonLabel>
                <IonSelect 
                  value={formData.employee_id} 
                  onIonChange={e => setFormData({...formData, employee_id: e.detail.value})}
                  interface="action-sheet"
                >
                  {employees.map(emp => (
                    <IonSelectOption key={emp.id} value={emp.id}>{emp.name} ({emp.role})</IonSelectOption>
                  ))}
                </IonSelect>
              </IonItem>

              <div className="executive-label" style={{ marginBottom: '8px', marginLeft: '8px' }}>Earnings & Rates</div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                  <IonLabel position="stacked">BASIC SALARY</IonLabel>
                  <IonInput 
                    type="number" 
                    value={formData.basic_salary} 
                    onIonInput={e => setFormData({...formData, basic_salary: Number(e.detail.value)})} 
                  />
                </IonItem>
                <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                  <IonLabel position="stacked">OVERTIME (OT)</IonLabel>
                  <IonInput 
                    type="number" 
                    value={formData.overtime_pay} 
                    onIonInput={e => setFormData({...formData, overtime_pay: Number(e.detail.value)})} 
                  />
                </IonItem>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                  <IonLabel position="stacked">ALLOWANCE</IonLabel>
                  <IonInput 
                    type="number" 
                    value={formData.allowance} 
                    onIonInput={e => setFormData({...formData, allowance: Number(e.detail.value)})} 
                  />
                </IonItem>
                <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                  <IonLabel position="stacked">DEDUCTIONS</IonLabel>
                  <IonInput 
                    type="number" 
                    value={formData.deductions} 
                    onIonInput={e => setFormData({...formData, deductions: Number(e.detail.value)})} 
                    style={{ color: '#ef4444' }}
                  />
                </IonItem>
              </div>

              <div className="executive-label" style={{ marginBottom: '8px', marginLeft: '8px' }}>Schedule & Notes</div>
              <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                <IonLabel position="stacked">PAYMENT DATE</IonLabel>
                <IonInput 
                  type="date" 
                  value={formData.pay_date} 
                  onIonChange={e => setFormData({...formData, pay_date: e.detail.value!})} 
                />
              </IonItem>

              <IonItem className="custom-filter-select" style={{ marginBottom: '16px' }}>
                <IonLabel position="stacked">REMARKS / NOTES</IonLabel>
                <IonInput 
                  placeholder="Additional information..." 
                  value={formData.notes} 
                  onIonInput={e => setFormData({...formData, notes: e.detail.value!})} 
                />
              </IonItem>
            </IonList>

            <div className="receipt-paper" style={{ marginTop: '20px' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div>
                  <div className="executive-label">Total Net Payable</div>
                  <div style={{ fontSize: '28px', fontWeight: 900, color: '#166534' }}>₱{totalSalary.toLocaleString()}</div>
                </div>
                <IonIcon icon={calculatorOutline} style={{ fontSize: '32px', color: '#cbd5e1' }} />
              </div>
            </div>
          </div>
        </div>
      </IonContent>

      <IonFooter className="ion-no-border">
        <div style={{ padding: '16px', background: 'white' }}>
          <IonButton expand="block" onClick={handleSubmit} disabled={loading} className="btn-amber" style={{ height: '56px', borderRadius: '16px' }}>
            {loading ? <IonSpinner name="crescent" /> : (
              <>
                <IonIcon icon={saveOutline} slot="start" />
                CREATE PAYROLL RECORD
              </>
            )}
          </IonButton>
        </div>
      </IonFooter>
    </IonPage>
  );
};

export default SalaryForm;
