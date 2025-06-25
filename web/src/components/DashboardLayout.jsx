import React from 'react';
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
  return (
    <div style={{ width: '100vw', height: '100vh', overflow: 'hidden', background: '#eaebe7', display: 'flex' }}>
      {/* Sidebar */}
      <aside style={{ width: SIDEBAR_WIDTH, background: '#fff', display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '30px 0', boxShadow: '2px 0 8px rgba(0,0,0,0.04)', position: 'fixed', top: 0, left: 0, bottom: 0, zIndex: 10 }}>
        <img src={logo} alt="Logo" style={{ width: 90, marginBottom: 30 }} />
        <nav style={{ width: '100%' }}>
          {menu.map((item, i) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.to === '/home'}
              style={({ isActive }) => ({
                display: 'flex', alignItems: 'center', background: isActive ? '#139BFF' : 'transparent', color: isActive ? '#fff' : '#222', padding: '12px 20px', borderRadius: 8, margin: '8px 16px', textDecoration: 'none', fontWeight: isActive ? 600 : 500
              })}
            >
              <i className={item.icon} style={{ marginRight: 12 }}></i> {item.label}
            </NavLink>
          ))}
        </nav>
      </aside>
      {/* Main area */}
      <div style={{ marginLeft: SIDEBAR_WIDTH, width: `calc(100vw - ${SIDEBAR_WIDTH}px)`, height: '100vh', display: 'flex', flexDirection: 'column' }}>
        {/* Header */}
        <header style={{ height: HEADER_HEIGHT, background: '#139BFF', color: '#fff', display: 'flex', alignItems: 'center', paddingLeft: 40, fontSize: 28, fontWeight: 600, letterSpacing: 1, zIndex: 20, justifyContent: 'space-between', paddingRight: 40, position: 'sticky', top: 0 }}>
          <span>Dashboard Administrador</span>
          <button
            onClick={() => {
              localStorage.removeItem('access_token');
              localStorage.removeItem('refresh_token');
              localStorage.removeItem('user_data');
              window.location.href = '/';
            }}
            style={{
              background: '#fff',
              border: 'none',
              borderRadius: '50%',
              width: 40,
              height: 40,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: 700,
              color: '#139BFF',
              fontSize: 16,
              cursor: 'pointer',
              boxShadow: '0 2px 8px rgba(0,0,0,0.06)'
            }}
            title="Cerrar sesión"
          >
            US
          </button>
        </header>
        {/* Contenido de la página */}
        <main style={{ flex: 1, overflow: 'auto', background: '#eaebe7' }}>
          <Outlet />
        </main>
      </div>
      {/* Responsividad básica */}
      <style>{`
        @media (max-width: 900px) {
          aside {
            width: 60px !important;
            min-width: 60px !important;
          }
          aside nav a, aside nav .active {
            justify-content: center !important;
            padding: 12px 0 !important;
          }
          aside nav a i {
            margin-right: 0 !important;
          }
          aside nav a span {
            display: none !important;
          }
          div[style*='marginLeft'] {
            margin-left: 60px !important;
            width: calc(100vw - 60px) !important;
          }
        }
        @media (max-width: 600px) {
          aside {
            display: none !important;
          }
          div[style*='marginLeft'] {
            margin-left: 0 !important;
            width: 100vw !important;
          }
        }
      `}</style>
    </div>
  );
};

export default DashboardLayout; 