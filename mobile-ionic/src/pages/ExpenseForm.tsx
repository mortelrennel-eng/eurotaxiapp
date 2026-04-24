import React, { useEffect, useState, useCallback } from 'react';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonItem, IonLabel, IonInput, IonSelect, IonSelectOption,
  IonButton, IonButtons, IonBackButton, IonSpinner, IonToast,
  IonIcon, IonCard, IonTextarea, IonGrid, IonRow, IonCol
} from '@ionic/react';
import { 
  saveOutline, receiptOutline, businessOutline, 
  calendarOutline, documentTextOutline, searchOutline,
  checkmarkCircleOutline, closeCircleOutline, trashOutline
} from 'ionicons/icons';
import { useParams, useHistory } from 'react-router-dom';
import { 
  getExpense, createExpense, updateExpense, deleteExpense,
  approveExpense, rejectExpense, getUnits, Unit
} from '../api';

export default function ExpenseForm() {
  const { id } = useParams<{ id?: string }>();
  const isEdit = !!id;
  const history = useHistory();

  const [form, setForm] = useState<any>({
    category: 'Utilities',
    description: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    reference_number: '',
    unit_id: '',
    status: 'pending'
  });

  const [units, setUnits] = useState<Unit[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', color: 'success' });

  // Searchable Unit Selector
  const [unitSearch, setUnitSearch] = useState('');
  const [showUnitDrop, setShowUnitDrop] = useState(false);

  const loadData = useCallback(async () => {
    setLoading(true);
    try {
      const uRes = await getUnits();
      if (uRes.success) setUnits(uRes.data);

      if (isEdit && id) {
        const res = await getExpense(parseInt(id));
        if (res.success) {
          const e = res.data;
          setForm({
            category: e.category,
            description: e.description,
            amount: e.amount,
            date: e.date,
            reference_number: e.reference_number || '',
            unit_id: e.unit_id || '',
            status: e.status
          });
          
          if (e.unit_id) {
            const u = uRes.data.find((x: any) => x.id === e.unit_id);
            if (u) setUnitSearch(`${u.plate_number} (${u.unit_number})`);
          }
        }
      }
    } catch (e: any) {
      setToast({ show: true, message: 'Failed to sync expense components', color: 'danger' });
    } finally {
      setLoading(false);
    }
  }, [id, isEdit]);

  useEffect(() => { loadData(); }, [loadData]);

  const handleSave = async () => {
    if (!form.category || !form.description || !form.amount) {
      setToast({ show: true, message: 'Required fields missing', color: 'warning' });
      return;
    }
    setSaving(true);
    try {
      const res = isEdit 
        ? await updateExpense(parseInt(id!), form)
        : await createExpense(form);
      
      if (res.success) {
        setToast({ show: true, message: `Expense ${isEdit ? 'updated' : 'recorded'}!`, color: 'success' });
        setTimeout(() => history.goBack(), 1000);
      } else {
        setToast({ show: true, message: res.message || 'Saving failed', color: 'danger' });
      }
    } catch (e: any) {
      setToast({ show: true, message: e.message, color: 'danger' });
    } finally {
      setSaving(false);
    }
  };

  const handleStatusAction = async (action: 'approve' | 'reject') => {
    setSaving(true);
    try {
      const res = action === 'approve' ? await approveExpense(parseInt(id!)) : await rejectExpense(parseInt(id!));
      if (res.success) {
        setToast({ show: true, message: `Expense ${action}d!`, color: 'success' });
        loadData();
      } else {
        setToast({ show: true, message: res.message, color: 'danger' });
      }
    } catch (e: any) {
      setToast({ show: true, message: e.message, color: 'danger' });
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm('Delete this expense record?')) return;
    setLoading(true);
    try {
      await deleteExpense(parseInt(id!));
      history.goBack();
    } catch (e) {
      setToast({ show: true, message: 'Delete failed', color: 'danger' });
      setLoading(false);
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
            <IonBackButton defaultHref="/app/expenses" color="dark" text="" />
          </IonButtons>
          <div style={{ padding: '0 8px' }}>
             <div className="header-modern-title">{isEdit ? 'Update Expense' : 'Record New Expense'}</div>
             <div className="header-modern-sub">{isEdit ? 'Modify existing operational or unit-specific cost' : 'Enter expenditure details for office or vehicle upkeep'}</div>
          </div>
          <IonButtons slot="end">
            {isEdit && (
              <IonButton color="danger" onClick={handleDelete}><IonIcon icon={trashOutline} slot="icon-only" /></IonButton>
            )}
            <IonButton onClick={handleSave} disabled={saving}>
              {saving ? <IonSpinner name="dots" /> : <IonIcon icon={saveOutline} slot="icon-only" style={{ color: '#ca8a04' }} />}
            </IonButton>
          </IonButtons>
        </IonToolbar>
      </IonHeader>

      <IonContent>
        <div className="animate-in" style={{ padding: '16px' }}>
          
          <div className="section-title">Expense Details</div>
          <IonCard className="form-card">
            <IonList lines="none">
              <IonItem>
                <div className="input-group">
                  <label><IonIcon icon={calendarOutline} /> Expense Date</label>
                  <IonInput type="date" value={form.date} onIonChange={(e: any) => setForm({...form, date: e.detail.value})} />
                </div>
              </IonItem>

              <IonItem>
                <div className="input-group">
                  <label><IonIcon icon={businessOutline} /> Category</label>
                  <IonSelect value={form.category} placeholder="Select Category" onIonChange={(e) => setForm({...form, category: e.detail.value})}>
                    <IonSelectOption value="Utilities">Utilities</IonSelectOption>
                    <IonSelectOption value="Supplies">Supplies</IonSelectOption>
                    <IonSelectOption value="Repairs">Repairs</IonSelectOption>
                    <IonSelectOption value="Communications">Communications</IonSelectOption>
                    <IonSelectOption value="Transportation">Transportation</IonSelectOption>
                    <IonSelectOption value="Other">Other</IonSelectOption>
                  </IonSelect>
                </div>
              </IonItem>

              <IonItem>
                <div className="input-group" style={{ position: 'relative' }}>
                  <label><IonIcon icon={searchOutline} /> Related Unit (Optional)</label>
                  <IonInput 
                    placeholder="Search Plate/Unit #"
                    value={unitSearch} 
                    onIonInput={(e: any) => { setUnitSearch(e.target.value); setShowUnitDrop(true); }}
                    onIonFocus={() => setShowUnitDrop(true)}
                  />
                  {showUnitDrop && (
                    <div className="custom-dropdown">
                        <div className="drop-item" onClick={() => { setForm({...form, unit_id: ''}); setUnitSearch(''); setShowUnitDrop(false); }}>
                             <em>None / Office Specific</em>
                        </div>
                        {units.filter(u => `${u.plate_number} ${u.unit_number}`.toLowerCase().includes(unitSearch.toLowerCase())).slice(0, 5).map(u => (
                            <div key={u.id} className="drop-item" onClick={() => { setForm({...form, unit_id: u.id}); setUnitSearch(`${u.plate_number} (${u.unit_number})`); setShowUnitDrop(false); }}>
                                <strong>{u.plate_number}</strong>
                                <span style={{ fontSize: '10px', color: '#64748b', marginLeft: '10px' }}>Unit {u.unit_number}</span>
                            </div>
                        ))}
                    </div>
                  )}
                </div>
              </IonItem>
            </IonList>
          </IonCard>

          <div className="section-title" style={{ marginTop: 24 }}>Amount & Reference</div>
          <IonCard className="form-card">
              <IonGrid>
                <IonRow>
                  <IonCol size="6" style={{ borderRight: '1px solid #f1f5f9' }}>
                    <div className="input-group">
                       <label><IonIcon icon={receiptOutline} /> Total Amount</label>
                       <IonInput type="number" value={form.amount} placeholder="0.00" onIonChange={(e: any) => setForm({...form, amount: e.detail.value})} style={{ fontSize: '18px', fontWeight: '900', color: '#dc2626' }} />
                    </div>
                  </IonCol>
                  <IonCol size="6">
                    <div className="input-group">
                       <label><IonIcon icon={documentTextOutline} /> Reference #</label>
                       <IonInput value={form.reference_number} placeholder="ex. OR-001" onIonChange={(e: any) => setForm({...form, reference_number: e.detail.value})} />
                    </div>
                  </IonCol>
                </IonRow>
              </IonGrid>
          </IonCard>

          <div className="section-title" style={{ marginTop: 24 }}>Description</div>
          <IonCard className="form-card">
              <IonItem>
                <IonTextarea 
                    rows={4} 
                    placeholder="Provide a detailed description of the expense..." 
                    value={form.description}
                    onIonInput={(e: any) => setForm({...form, description: e.target.value})}
                />
              </IonItem>
          </IonCard>

          {isEdit && form.status === 'pending' && (
              <div style={{ marginTop: 24 }}>
                  <div className="section-title">Administrative Actions</div>
                  <IonGrid className="ion-no-padding">
                    <IonRow>
                        <IonCol size="6" style={{ paddingRight: 6 }}>
                             <IonButton expand="block" color="success" onClick={() => handleStatusAction('approve')} disabled={saving}>
                                 <IonIcon icon={checkmarkCircleOutline} slot="start" /> Approve
                             </IonButton>
                        </IonCol>
                        <IonCol size="6" style={{ paddingLeft: 6 }}>
                             <IonButton expand="block" color="danger" fill="outline" onClick={() => handleStatusAction('reject')} disabled={saving}>
                                 <IonIcon icon={closeCircleOutline} slot="start" /> Reject
                             </IonButton>
                        </IonCol>
                    </IonRow>
                  </IonGrid>
              </div>
          )}

          <IonButton expand="block" className="btn-primary" style={{ marginTop: 32 }} onClick={handleSave} disabled={saving}>
            {saving ? <IonSpinner name="crescent" /> : (isEdit ? 'Update Expense record' : 'Confirm & Post Expense')}
          </IonButton>

          <div style={{ height: 100 }} />
        </div>
      </IonContent>

      <IonToast
        isOpen={toast.show} message={toast.message} color={toast.color} duration={2000}
        onDidDismiss={() => setToast({ ...toast, show: false })}
      />

      <style>{`
        .section-title { font-size: 11px; font-weight: 900; color: #64748b; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 12px; }
        .form-card { margin: 0; border-radius: 18px; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .input-group { width: 100%; padding: 8px 0; }
        .input-group label { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase; }
        .input-group label ion-icon { font-size: 14px; color: #3b82f6; }
        .input-group ion-input, .input-group ion-select { --padding-start: 0; font-size: 15px; font-weight: 600; color: #1e293b; }
        .custom-dropdown { position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); max-height: 200px; overflow-y: auto; }
        .drop-item { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        .drop-item:active { background: #f8fafc; }
      `}</style>
    </IonPage>
  );
}
