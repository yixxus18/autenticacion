import React, { useState, useEffect } from 'react';

const API_URL = 'http://localhost:8001/api/v1/admin/users';
const estatusOpciones = ['Activos', 'Inactivos', 'Todos'];
const roles = [
  { value: 'jefe_cerrada', label: 'Jefe de cerrada' },
  { value: 'guardia', label: 'Guardia' },
  { value: 'jefe_familia', label: 'Jefe de familia' },
  { value: 'familiar', label: 'Familiar' },
];

const Usuarios = () => {
  const [usuarios, setUsuarios] = useState([]);
  const [filtroEstatus, setFiltroEstatus] = useState('Todos');
  const [busqueda, setBusqueda] = useState('');
  const [modalAbierto, setModalAbierto] = useState(false);
  const [modalUsuario, setModalUsuario] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    const fetchUsuarios = async () => {
      setLoading(true);
      setError('');
      try {
        const res = await fetch(API_URL, {
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
            'Accept': 'application/json',
          },
        });
        const data = await res.json();
        if (res.ok && data.data) {
          setUsuarios(data.data.map(u => ({
            id: u.id, nombre: u.name, email: u.email, telefono: u.phone, direccion: u.direccion || '', estatus: u.is_active ? 'Activo' : 'Inactivo',
          })));
        } else {
          setError(data.message || 'Error al obtener usuarios');
        }
      } catch {
        setError('Error de red al obtener usuarios');
      }
      setLoading(false);
    };
    fetchUsuarios();
  }, []);

  const usuariosFiltrados = usuarios.filter(u =>
    (filtroEstatus === 'Todos' || (filtroEstatus === 'Activos' ? u.estatus === 'Activo' : u.estatus === 'Inactivo')) &&
    (u.nombre.toLowerCase().includes(busqueda.toLowerCase()) || u.email.toLowerCase().includes(busqueda.toLowerCase()) || u.telefono.includes(busqueda))
  );

  const abrirModal = (usuario = null) => {
    setModalUsuario(usuario);
    setModalAbierto(true);
  };
  const cerrarModal = () => {
    setModalAbierto(false);
    setModalUsuario(null);
    setError('');
    setSuccess('');
  };
  const guardarUsuario = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');
    const form = e.target;
    const nuevo = {
      name: form.nombre.value, email: form.email.value, phone: form.telefono.value, direccion: form.direccion.value, role: form.role.value, password: form.password ? form.password.value : undefined,
    };
    try {
      let res, data;
      if (modalUsuario) {
        res = await fetch(`${API_URL}/${modalUsuario.id}`, {
          method: 'PUT', headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Content-Type': 'application/json', 'Accept': 'application/json', }, body: JSON.stringify(nuevo),
        });
      } else {
        res = await fetch(API_URL, {
          method: 'POST', headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Content-Type': 'application/json', 'Accept': 'application/json', }, body: JSON.stringify(nuevo),
        });
      }
      data = await res.json();
      if (res.ok && data.data) {
        setSuccess('Usuario guardado correctamente');
        cerrarModal();
        const res2 = await fetch(API_URL, {
          headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json', },
        });
        const data2 = await res2.json();
        setUsuarios(data2.data.map(u => ({
          id: u.id, nombre: u.name, email: u.email, telefono: u.phone, direccion: u.direccion || '', estatus: u.is_active ? 'Activo' : 'Inactivo',
        })));
      } else {
        setError(data.message || 'Error al guardar usuario');
      }
    } catch {
      setError('Error de red al guardar usuario');
    }
    setLoading(false);
  };
  const eliminarUsuario = async (id) => {
    setLoading(true);
    setError('');
    setSuccess('');
    try {
      const res = await fetch(`${API_URL}/${id}`, {
        method: 'DELETE', headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json', },
      });
      const data = await res.json();
      if (res.ok) {
        setSuccess('Usuario eliminado');
        setUsuarios(usuarios.filter(u => u.id !== id));
      } else {
        setError(data.message || 'Error al eliminar usuario');
      }
    } catch {
      setError('Error de red al eliminar usuario');
    }
    setLoading(false);
  };

  return (
    <div className="usuarios-container" style={{ padding: 40, background: '#eaebe7', minHeight: 'calc(100vh - 70px)' }}>
      <h2 style={{ textAlign: 'center', marginBottom: 30 }}>Gestión de Usuarios</h2>
      <div className="usuarios-filtros-row" style={{ display: 'flex', justifyContent: 'center', gap: 20, marginBottom: 20 }}>
        <select value={filtroEstatus} onChange={e => setFiltroEstatus(e.target.value)} style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc', background: '#fff', color: '#222' }}>
          {estatusOpciones.map(e => <option key={e} value={e}>{e}</option>)}
        </select>
        <button style={{ background: '#22b14c', color: '#fff', border: 'none', borderRadius: 6, padding: '8px 24px', fontWeight: 600, boxShadow: '1px 2px 2px #bbb', cursor: 'pointer' }}>Filtrar</button>
      </div>
      <div className="usuarios-buscar-row" style={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 10, marginBottom: 10 }}>
        <input type="text" placeholder="Search" value={busqueda} onChange={e => setBusqueda(e.target.value)} style={{ padding: 6, borderRadius: 6, border: '1px solid #ccc', background: '#fff', color: '#222', minWidth: 80, maxWidth: 220, width: '100%' }} />
        <button onClick={() => abrirModal()} style={{ background: '#ffd200', color: '#222', border: 'none', borderRadius: 6, padding: '8px 18px', fontWeight: 600, marginLeft: 0, cursor: 'pointer', minWidth: 110 }}>Añadir Usuario</button>
      </div>
      {loading && <div style={{ color: '#139BFF', textAlign: 'center', marginBottom: 10 }}>Cargando...</div>}
      {error && <div style={{ color: 'red', textAlign: 'center', marginBottom: 10 }}>{error}</div>}
      {success && <div style={{ color: 'green', textAlign: 'center', marginBottom: 10 }}>{success}</div>}
      <div style={{ background: '#fff', borderRadius: 10, overflow: 'auto', boxShadow: '0 2px 8px rgba(0,0,0,0.05)', margin: '0 auto', maxWidth: 900 }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: 600 }}>
          <thead>
            <tr style={{ background: '#139BFF', color: '#fff' }}>
              <th style={{ padding: 10 }}>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Dirección</th><th>Estatus</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {usuariosFiltrados.map(u => (
              <tr key={u.id} style={{ borderBottom: '1px solid #eee' }}>
                <td style={{ textAlign: 'center' }}>{u.id}</td><td>{u.nombre}</td><td>{u.email}</td><td>{u.telefono}</td><td>{u.direccion}</td>
                <td>
                  <span style={{ background: u.estatus === 'Activo' ? '#22b14c' : '#e53935', color: '#fff', borderRadius: 8, padding: '4px 16px', fontWeight: 600, fontSize: 14 }}>{u.estatus}</span>
                </td>
                <td style={{ textAlign: 'center', minWidth: 110 }}>
                  <button title="Ver/Editar" style={{ background: '#139BFF', border: 'none', cursor: 'pointer', fontSize: 22, marginRight: 10, color: '#fff', borderRadius: 6, padding: '10px 14px', boxShadow: '0 1px 2px #0002', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }} onClick={() => abrirModal(u)}>
                    <i className="fas fa-eye"></i>
                  </button>
                  <button title="Eliminar" style={{ background: '#e53935', border: 'none', cursor: 'pointer', fontSize: 22, color: '#fff', borderRadius: 6, padding: '10px 14px', boxShadow: '0 1px 2px #0002', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }} onClick={() => eliminarUsuario(u.id)}>
                    <i className="fas fa-times"></i>
                  </button>
                </td>
              </tr>
            ))}
            {usuariosFiltrados.length === 0 && (
              <tr><td colSpan={7} style={{ textAlign: 'center', color: '#888', padding: 20 }}>Sin resultados</td></tr>
            )}
          </tbody>
        </table>
      </div>
      
      <style>{`
        @media (max-width: 900px) {
          .usuarios-filtros-row, .usuarios-buscar-row { flex-direction: column !important; gap: 10px !important; align-items: stretch !important; }
          .usuarios-buscar-row { justify-content: flex-start !important; }
        }
        @media (max-width: 700px) {
          .usuarios-container { padding: 20px !important; }
          .usuarios-filtros-row, .usuarios-buscar-row { margin: 10px !important; padding: 0 !important; }
          table { font-size: 13px; }
        }
        @media (max-width: 600px) {
          .usuarios-container { padding: 15px !important; }
          .usuarios-filtros-row, .usuarios-buscar-row { flex-direction: column !important; gap: 8px !important; margin: 0 2vw !important; }
          table { min-width: 500px; }
          .usuarios-buscar-row input[type='text'] { min-width: 60px !important; max-width: 100% !important; font-size: 15px !important; }
          td, th { padding: 7px 4px !important; }
          td button { font-size: 20px !important; padding: 8px 10px !important; }
        }
      `}</style>
      
      {modalAbierto && (
        <div style={{ position: 'fixed', top: 0, left: 0, width: '100vw', height: '100vh', background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }}>
          <form onSubmit={guardarUsuario} style={{ background: '#fff', borderRadius: 12, padding: 32, minWidth: 320, maxWidth: '90%', boxShadow: '0 4px 16px rgba(0,0,0,0.2)', display: 'flex', flexDirection: 'column', gap: 16 }}>
            <h3>{modalUsuario ? 'Editar Usuario' : 'Añadir Usuario'}</h3>
            <input name="nombre" defaultValue={modalUsuario?.nombre || ''} placeholder="Nombre" required style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <input name="email" defaultValue={modalUsuario?.email || ''} placeholder="Email" required type="email" style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <input name="telefono" defaultValue={modalUsuario?.telefono || ''} placeholder="Teléfono" required style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <input name="direccion" defaultValue={modalUsuario?.direccion || ''} placeholder="Dirección" required style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <select name="role" defaultValue={modalUsuario?.role || roles[0].value} style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }}>
              {roles.map(r => <option key={r.value} value={r.value}>{r.label}</option>)}
            </select>
            {!modalUsuario && (
              <input name="password" placeholder="Contraseña" required type="password" style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            )}
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 10, marginTop: 10 }}>
              <button type="button" onClick={cerrarModal} style={{ background: '#eee', border: 'none', borderRadius: 6, padding: '8px 18px', fontWeight: 600, cursor: 'pointer', color: '#222' }}>Cancelar</button>
              <button type="submit" style={{ background: '#139BFF', color: '#fff', border: 'none', borderRadius: 6, padding: '8px 18px', fontWeight: 600, cursor: 'pointer' }}>{modalUsuario ? 'Guardar' : 'Crear'}</button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};

export default Usuarios;