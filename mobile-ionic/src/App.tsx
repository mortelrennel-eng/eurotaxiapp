import React from 'react'
import { Redirect, Route, useHistory } from 'react-router-dom'
import {
  IonIcon,
  IonLabel,
  IonRouterOutlet,
  IonMenu,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonItem,
  IonMenuToggle,
  IonSplitPane
} from '@ionic/react'
import {
  gridOutline,
  carSportOutline,
  peopleOutline,
  mapOutline,
  documentTextOutline,
  cashOutline,
  constructOutline,
  timeOutline,
  warningOutline,
  receiptOutline,
  walletOutline,
  barChartOutline,
  trendingUpOutline,
  archiveOutline,
  logOutOutline,
  chevronForwardOutline
} from 'ionicons/icons'
import { isLoggedIn, getStoredUser, logoutApi } from './api'
import Login from './pages/Login'
import Register from './pages/Register'
import Dashboard from './pages/Dashboard'
import Units from './pages/Units'
import UnitDetail from './pages/UnitDetail'
import UnitForm from './pages/UnitForm'
import Drivers from './pages/Drivers'
import DriverForm from './pages/DriverForm'
import Boundaries from './pages/Boundaries'
import BoundaryForm from './pages/BoundaryForm'
import Expenses from './pages/Expenses'
import ExpenseForm from './pages/ExpenseForm'
import Maintenance from './pages/Maintenance'
import MaintenanceForm from './pages/MaintenanceForm'
import Franchises from './pages/Franchises'
import FranchiseForm from './pages/FranchiseForm'
import Coding from './pages/Coding'
import StaffForm from './pages/StaffForm'
import Salaries from './pages/Salaries'
import SalaryForm from './pages/SalaryForm'
import Profile from './pages/Profile'
import Tracking from './pages/Tracking'
import Behavior from './pages/Behavior'
import BehaviorForm from './pages/BehaviorForm'
import AnalyticsPage from './pages/AnalyticsPage'
import Profitability from './pages/Profitability'
import Archive from './pages/Archive'
import logoImg from './assets/logo.png'

const PrivateRoute: React.FC<{ path: string; exact?: boolean; children: React.ReactNode }> = ({ children, ...rest }) => {
  return (
    <Route {...rest} render={() => isLoggedIn() ? <>{children}</> : <Redirect to="/login" />} />
  )
}

const appPages = [
  { title: 'Dashboard', url: '/app/dashboard', icon: gridOutline },
  { title: 'Unit Management', url: '/app/units', icon: carSportOutline },
  { title: 'Driver Management', url: '/app/drivers', icon: peopleOutline },
  { title: 'Live Tracking', url: '/app/tracking', icon: mapOutline },
  { title: 'Franchise', url: '/app/franchises', icon: documentTextOutline },
  { title: 'Boundaries', url: '/app/boundaries', icon: cashOutline },
  { title: 'Maintenance', url: '/app/maintenance', icon: constructOutline },
  { title: 'Coding Management', url: '/app/coding', icon: timeOutline },
  { title: 'Driver Behavior', url: '/app/behavior', icon: warningOutline },
  { title: 'Office Expenses', url: '/app/expenses', icon: receiptOutline },
  { title: 'Salary Management', url: '/app/salaries', icon: walletOutline },
  { title: 'Analytics', url: '/app/analytics', icon: barChartOutline },
  { title: 'Unit Profitability', url: '/app/profitability', icon: trendingUpOutline },
  { title: 'Staff Records', url: '/app/staff', icon: peopleOutline },
  { title: 'Requests & Approvals', url: '/app/approvals', icon: constructOutline },
  { title: 'Archive Management', url: '/app/archive', icon: archiveOutline },
];

export default function App() {
  const user = getStoredUser() || { name: 'Rennel Mortel', role: 'Secretary' };
  const history = useHistory();

  const handleLogout = async () => {
    await logoutApi();
    history.replace('/login');
  };

  return (
    <>
      <Route exact path="/login" component={Login} />
      <Route exact path="/register" component={Register} />
      <Route path="/app">
        <IonSplitPane contentId="main" when="md">
          <IonMenu contentId="main" type="overlay">
            <IonHeader className="ion-no-border">
              <div style={{ padding: '20px 20px 10px 20px', textAlign: 'center', background: '#fff' }}>
                <img src={logoImg} alt="EuroTaxi Logo" style={{ height: '50px', objectFit: 'contain', marginBottom: '8px' }} />
                <div style={{ fontSize: '10px', fontWeight: '900', color: '#ca8a04', letterSpacing: '3px' }}>FLEET MANAGEMENT</div>
              </div>
            </IonHeader>

            <IonContent className="sidebar-content" scrollY={true}>
              <div className="scrollable-menu-section">
                {appPages.map((p, i) => (
                  <IonMenuToggle autoHide={false} key={i}>
                    <IonItem
                      routerLink={p.url}
                      routerDirection="none"
                      className="sidebar-item"
                      detail={false}
                      lines="none"
                    >
                      <IonIcon slot="start" icon={p.icon} />
                      <IonLabel>{p.title}</IonLabel>
                    </IonItem>
                  </IonMenuToggle>
                ))}
              </div>

              {/* Spacer to push content above the fixed footer */}
              <div style={{ height: '160px' }} />
            </IonContent>

            <div className="sidebar-footer-sticky">
              <IonItem lines="none" className="profile-section" detail={false} routerLink="/app/profile">
                <div className="avatar-gold-m">
                  {user.name?.charAt(0) || 'R'}
                </div>
                <IonLabel className="profile-label">
                  <h2>{user.name || 'Rennel Mortel'}</h2>
                  <p>{user.role || 'Secretary'}</p>
                </IonLabel>
                <IonIcon icon={chevronForwardOutline} className="profile-chevron" />
              </IonItem>

              <IonItem lines="none" className="logout-section" onClick={handleLogout}>
                <IonIcon slot="start" icon={logOutOutline} />
                <IonLabel>Logout</IonLabel>
              </IonItem>
            </div>
          </IonMenu>

          <IonRouterOutlet id="main">
            <PrivateRoute exact path="/app/dashboard"><Dashboard /></PrivateRoute>
            <PrivateRoute exact path="/app/units"><Units /></PrivateRoute>
            <PrivateRoute exact path="/app/units/new"><UnitForm /></PrivateRoute>
            <PrivateRoute exact path="/app/units/:id/edit"><UnitDetail /></PrivateRoute>
            <PrivateRoute exact path="/app/drivers"><Drivers /></PrivateRoute>
            <PrivateRoute exact path="/app/drivers/new"><DriverForm /></PrivateRoute>
            <PrivateRoute exact path="/app/drivers/:id/edit"><DriverForm /></PrivateRoute>
            <PrivateRoute exact path="/app/boundaries"><Boundaries /></PrivateRoute>
            <PrivateRoute exact path="/app/boundaries/new"><BoundaryForm /></PrivateRoute>
            <PrivateRoute exact path="/app/boundaries/:id/edit"><BoundaryForm /></PrivateRoute>
            <PrivateRoute exact path="/app/expenses"><Expenses /></PrivateRoute>
            <PrivateRoute exact path="/app/expenses/new"><ExpenseForm /></PrivateRoute>
            <PrivateRoute exact path="/app/expenses/:id/edit"><ExpenseForm /></PrivateRoute>
            <PrivateRoute exact path="/app/maintenance"><Maintenance /></PrivateRoute>
            <PrivateRoute exact path="/app/maintenance/new"><MaintenanceForm /></PrivateRoute>
            <PrivateRoute exact path="/app/maintenance/:id/edit"><MaintenanceForm /></PrivateRoute>
            <PrivateRoute exact path="/app/coding"><Coding /></PrivateRoute>
            <PrivateRoute exact path="/app/staff/:id/edit"><StaffForm /></PrivateRoute>
            <PrivateRoute exact path="/app/salaries"><Salaries /></PrivateRoute>
            <PrivateRoute exact path="/app/salaries/new"><SalaryForm /></PrivateRoute>
            <PrivateRoute exact path="/app/franchises"><Franchises /></PrivateRoute>
            <PrivateRoute exact path="/app/franchises/new"><FranchiseForm /></PrivateRoute>
            <PrivateRoute exact path="/app/franchises/:id/edit"><FranchiseForm /></PrivateRoute>
            <PrivateRoute exact path="/app/profile"><Profile /></PrivateRoute>
            <PrivateRoute exact path="/app/tracking"><Tracking /></PrivateRoute>
            <PrivateRoute exact path="/app/behavior"><Behavior /></PrivateRoute>
            <PrivateRoute exact path="/app/behavior/new"><BehaviorForm /></PrivateRoute>
            <PrivateRoute exact path="/app/analytics"><AnalyticsPage /></PrivateRoute>
            <PrivateRoute exact path="/app/profitability"><Profitability /></PrivateRoute>
            <PrivateRoute exact path="/app/archive"><Archive /></PrivateRoute>
            <Route exact path="/app"><Redirect to="/app/dashboard" /></Route>
          </IonRouterOutlet>
        </IonSplitPane>
      </Route>

      <Route exact path="/"><Redirect to={isLoggedIn() ? '/app/dashboard' : '/login'} /></Route>

      <style>{`
         .sidebar-content { --background: #fff; }
         .scrollable-menu-section { padding: 10px 12px; }
         
         .sidebar-item { --border-radius: 12px; --padding-start: 12px; margin-bottom: 2px; font-weight: 600; font-size: 14px; color: #475569; transition: 0.2s; }
         .sidebar-item ion-icon { font-size: 20px; color: #64748b; }
         
         .sidebar-item.item-active { --background: #fef9c3; --color: #854d0e; font-weight: 800; }
         .sidebar-item.item-active ion-icon { color: #854d0e; }

         .archive-special { --color: #475569; margin-top: 4px; }
         .archive-special.item-active { --background: #fef9c3; --color: #854d0e; }

         .sidebar-footer-sticky { position: absolute; bottom: 0; width: 100%; padding: 15px 12px; border-top: 1px solid #f1f5f9; background: #fff; z-index: 10; }
         .profile-section { cursor: pointer; --padding-start: 0; margin-bottom: 10px; }
         .avatar-gold-m { width: 44px; height: 44px; border-radius: 50%; background: #ca8a04; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; margin-right: 12px; }
         .profile-label h2 { font-size: 15px; font-weight: 900; color: #0f172a; margin: 0; }
         .profile-label p { font-size: 11px; color: #64748b; margin: 1px 0 0 0; text-transform: capitalize; }
         .profile-chevron { font-size: 16px; color: #94a3b8; }

         .logout-section { --border-radius: 12px; --color: #ef4444; font-weight: 800; font-size: 15px; --padding-start: 0; }
         .logout-section ion-icon { color: #ef4444; font-size: 22px; }
         
         /* Hide scrollbar for Chrome, Safari and Opera */
         .sidebar-content::-webkit-scrollbar { display: none; }
         /* Hide scrollbar for IE, Edge and Firefox */
         .sidebar-content { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>
    </>
  )
}
