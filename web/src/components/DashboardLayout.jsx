import React, { useState } from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import logo from '../assets/logo_2_sin_fondo.png';

const SIDEBAR_WIDTH = 220;
const HEADER_HEIGHT = 70;

const menu = [
  { label: 'Dashboard', icon: 'fas fa-th-large', to: '/home' },
  { label: 'Gestión de usuarios', icon: 'fas fa-users', to: '/home/usuarios' },
  { label: 'Gestión de cerradas', icon: 'fas fa-door-open', to: '/home/cerradas' },
  { label: 'Gestión de pagos', icon: 'fas fa-money-bill-wave', to: '/home/pagos' },
  { label: 'Tokens de acceso', icon: 'fas fa-key', to: '/home/tokens' },
  { label: 'Configuración', icon: 'fas fa-cog', to: '/home/configuracion' },
];

const DashboardLayout = () => {
  const [drawerOpen, setDrawerOpen] = useState(false);

  const sidebarContent = (
    <>
      <img src={logo} alt="Logo" style={{ width: 90, marginBottom: 30 }} />
      <nav style={{ width: '100%' }}>
        {menu.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.to === '/home'}
            style={({ isActive }) => ({
              display: 'flex', alignItems: 'center', background: isActive ? '#139BFF' : 'transparent', color: isActive ? '#fff' : '#222', padding: '12px 20px', borderRadius: 8, margin: '8px 16px', textDecoration: 'none', fontWeight: isActive ? 600 : 500
            })}
            onClick={() => setDrawerOpen(false)}
          >
            <i className={item.icon} style={{ marginRight: 12 }}></i> <span>{item.label}</span>
          </NavLink>
        ))}
      </nav>
    </>
  );

  return (
    <div style={{ height: '100vh', overflow: 'hidden', background: '#eaebe7', display: 'flex' }}>
      <aside className="sidebar-fixed" style={{ width: SIDEBAR_WIDTH, background: '#fff', display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '30px 0', boxShadow: '2px 0 8px rgba(0,0,0,0.04)', position: 'fixed', top: 0, left: 0, bottom: 0, zIndex: 10, transition: 'width 0.3s ease' }}>
        {sidebarContent}
      </aside>
      
      {drawerOpen && (
        <div className="sidebar-drawer-overlay" onClick={() => setDrawerOpen(false)} style={{ position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(0,0,0,0.5)', zIndex: 100 }}>
          <aside className="sidebar-drawer" onClick={e => e.stopPropagation()} style={{ width: 220, background: '#fff', height: '100%', boxShadow: '2px 0 16px rgba(0,0,0,0.2)', position: 'fixed', top: 0, left: 0, zIndex: 101, display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '30px 0', animation: 'slideInSidebar .2s ease-out' }}>
            <button onClick={() => setDrawerOpen(false)} style={{ position: 'absolute', top: 12, right: 12, background: 'none', border: 'none', fontSize: 22, color: '#139BFF', cursor: 'pointer' }} title="Cerrar menú">
              <i className="fas fa-times"></i>
            </button>
            {sidebarContent}
          </aside>
        </div>
      )}

      <div className="main-area" style={{ marginLeft: SIDEBAR_WIDTH, flex: 1, display: 'flex', flexDirection: 'column', transition: 'margin-left 0.3s ease' }}>
        <header className="dashboard-header" style={{ height: HEADER_HEIGHT, background: '#139BFF', color: '#fff', display: 'flex', alignItems: 'center', zIndex: 20, justifyContent: 'space-between', padding: '0 40px', position: 'sticky', top: 0, flexShrink: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
            <button className="sidebar-hamburger" onClick={() => setDrawerOpen(true)} style={{ display: 'none', background: 'none', border: 'none', color: '#fff', fontSize: 28, cursor: 'pointer', padding: 0, lineHeight: 1 }} title="Abrir menú">
              <i className="fas fa-bars"></i>
            </button>
            <span style={{ fontSize: 24, fontWeight: 600, letterSpacing: 1 }}>Dashboard</span>
          </div>
          <button
            onClick={() => {
              localStorage.clear();
              window.location.href = '/';
            }}
            style={{ background: '#fff', border: 'none', borderRadius: '50%', width: 40, height: 40, display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 700, color: '#139BFF', fontSize: 16, cursor: 'pointer', boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }}
            title="Cerrar sesión"
          >
            CS
          </button>
        </header>
        
        <main style={{ flex: 1, overflow: 'auto', background: '#eaebe7' }}>
          <Outlet />
        </main>
      </div>

      <style>{`
        @keyframes slideInSidebar {
          from { transform: translateX(-100%); }
          to { transform: translateX(0); }
        }

        @media (max-width: 900px) {
          aside.sidebar-fixed {
            width: 70px !important;
          }
          aside.sidebar-fixed nav a {
            justify-content: center !important;
            margin: 8px 10px !important;
          }
          aside.sidebar-fixed nav a i {
            margin-right: 0 !important;
            font-size: 20px;
          }
          aside.sidebar-fixed nav a span {
            display: none !important;
          }
          .main-area {
            margin-left: 70px !important;
          }
        }

        @media (max-width: 600px) {
          aside.sidebar-fixed {
            display: none !important;
          }
          .sidebar-hamburger {
            display: block !important;
          }
          .main-area {
            margin-left: 0 !important;
            width: 100% !important;
          }
          .dashboard-header {
            padding: 0 16px !important;
          }
          .dashboard-header span {
            font-size: 20px !important;
          }
        }
        
        @media (min-width: 601px) {
          .sidebar-drawer-overlay {
            display: none !important;
          }
        }
      `}</style>
    </div>
  );
};

export default DashboardLayout;