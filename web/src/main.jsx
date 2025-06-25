import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.jsx'
import Home from './pages/Home.jsx';
import Usuarios from './pages/Usuarios.jsx';
import Cerradas from './pages/Cerradas.jsx';
import Pagos from './pages/Pagos.jsx';
import Tokens from './pages/Tokens.jsx';
import Configuracion from './pages/Configuracion.jsx';
import DashboardLayout from './components/DashboardLayout.jsx';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import AuthCallback from './AuthCallback.jsx';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />} />
        <Route path="/auth/callback" element={<AuthCallback />} />
        <Route path="/home" element={<DashboardLayout />}>
          <Route index element={<Home />} />
          <Route path="usuarios" element={<Usuarios />} />
          <Route path="cerradas" element={<Cerradas />} />
          <Route path="pagos" element={<Pagos />} />
          <Route path="tokens" element={<Tokens />} />
          <Route path="configuracion" element={<Configuracion />} />
        </Route>
      </Routes>
    </BrowserRouter>
  </StrictMode>,
)
