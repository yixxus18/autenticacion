import React, { useState, useEffect } from 'react';

const API_URL = 'http://localhost:8001/api/v1/admin/users';
const cerradas = ['San Joaquín', 'San Lorenzo'];
const estatusOpciones = ['Activos', 'Inactivos', 'Todos'];
const roles = [
  { value: 'jefe_cerrada', label: 'Jefe de cerrada' },
  { value: 'guardia', label: 'Guardia' },
  { value: 'jefe_familia', label: 'Jefe de familia' },
  { value: 'familiar', label: 'Familiar' },
];

const Usuarios = () => {
  const [usuarios, setUsuarios] = useState([]);
  const [filtroCerrada, setFiltroCerrada] = useState('Todos');
  const [filtroEstatus, setFiltroEstatus] = useState('Todos');
  const [busqueda, setBusqueda] = useState('');
  const [modalAbierto, setModalAbierto] = useState(false);
  const [modalUsuario, setModalUsuario] = useState(null);
  const [pagina, setPagina] = useState(1);
  const porPagina = 10;
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Obtener usuarios de la API
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
            id: u.id,
            nombre: u.name,
            cerrada: u.cerrada || 'San Joaquín', // Ajusta si tu API devuelve la cerrada
            estatus: u.is_active === 1 ? 'Activo' : 'Inactivo',
            email: u.email,
            phone: u.phone,
            role: u.role_name || '',
          })));
        } else {
          setError(data.message || 'Error al obtener usuarios');
        }
      } catch (e) {
        setError('Error de red al obtener usuarios');
      }
      setLoading(false);
    };
    fetchUsuarios();
  }, []);

  // Filtros y búsqueda
  const usuariosFiltrados = usuarios.filter(u =>
    (filtroCerrada === 'Todos' || u.cerrada === filtroCerrada) &&
    (filtroEstatus === 'Todos' || (filtroEstatus === 'Activos' ? u.estatus === 'Activo' : u.estatus === 'Inactivo')) &&
    (u.nombre.toLowerCase().includes(busqueda.toLowerCase()) || u.id.toString().includes(busqueda))
  );
  const totalPaginas = Math.ceil(usuariosFiltrados.length / porPagina);
  const usuariosPagina = usuariosFiltrados.slice((pagina - 1) * porPagina, pagina * porPagina);

  // Modal
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
      name: form.nombre.value,
      email: form.email.value,
      phone: form.phone.value,
      role: form.role.value,
      password: form.password ? form.password.value : undefined,
    };
    try {
      let res, data;
      if (modalUsuario) {
        // Editar
        res = await fetch(`${API_URL}/${modalUsuario.id}`, {
          method: 'PUT',
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(nuevo),
        });
      } else {
        // Crear
        res = await fetch(API_URL, {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(nuevo),
        });
      }
      data = await res.json();
      if (res.ok && data.data) {
        setSuccess('Usuario guardado correctamente');
        cerrarModal();
        // Refrescar usuarios
        const res2 = await fetch(API_URL, {
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
            'Accept': 'application/json',
          },
        });
        const data2 = await res2.json();
        setUsuarios(data2.data.map(u => ({
          id: u.id,
          nombre: u.name,
          cerrada: u.cerrada || 'San Joaquín',
          estatus: u.is_active === 1 ? 'Activo' : 'Inactivo',
          email: u.email,
          phone: u.phone,
          role: u.role_name || '',
        })));
      } else {
        setError(data.message || 'Error al guardar usuario');
      }
    } catch (e) {
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
        method: 'DELETE',
        headers: {
          'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
          'Accept': 'application/json',
        },
      });
      const data = await res.json();
      if (res.ok) {
        setSuccess('Usuario eliminado');
        setUsuarios(usuarios.filter(u => u.id !== id));
      } else {
        setError(data.message || 'Error al eliminar usuario');
      }
    } catch (e) {
      setError('Error de red al eliminar usuario');
    }
    setLoading(false);
  };
  // Render
  return (
    <div style={{ padding: 40, background: '#eaebe7', minHeight: '100vh' }}>
      <h2 style={{ textAlign: 'center', marginBottom: 30 }}>Gestión de Usuarios</h2>
      {/* Filtros */}
      <div style={{ display: 'flex', justifyContent: 'center', gap: 20, marginBottom: 20 }}>
        <select value={filtroCerrada} onChange={e => setFiltroCerrada(e.target.value)} style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }}>
          <option value="Todos">Todas</option>
          {cerradas.map(c => <option key={c} value={c}>{c}</option>)}
        </select>
        <select value={filtroEstatus} onChange={e => setFiltroEstatus(e.target.value)} style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }}>
          {estatusOpciones.map(e => <option key={e} value={e}>{e}</option>)}
        </select>
        <button style={{ background: '#22b14c', color: '#fff', border: 'none', borderRadius: 6, padding: '8px 24px', fontWeight: 600, boxShadow: '1px 2px 2px #bbb', cursor: 'pointer' }}>Filtrar</button>
      </div>
      {/* Buscador y añadir */}
      <div style={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 10, marginBottom: 10 }}>
        <input type="text" placeholder="Search" value={busqueda} onChange={e => setBusqueda(e.target.value)} style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
        <button onClick={() => abrirModal()} style={{ background: '#ffd200', color: '#222', border: 'none', borderRadius: 6, padding: '8px 18px', fontWeight: 600, marginLeft: 10, cursor: 'pointer' }}>Añadir Usuario</button>
      </div>
      {/* Mensajes */}
      {loading && <div style={{ color: '#139BFF', textAlign: 'center', marginBottom: 10 }}>Cargando...</div>}
      {error && <div style={{ color: 'red', textAlign: 'center', marginBottom: 10 }}>{error}</div>}
      {success && <div style={{ color: 'green', textAlign: 'center', marginBottom: 10 }}>{success}</div>}
      {/* Tabla */}
      <div style={{ background: '#fff', borderRadius: 10, overflow: 'hidden', boxShadow: '0 2px 8px #0001', margin: '0 auto', maxWidth: 900 }}>
        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
          <thead>
            <tr style={{ background: '#139BFF', color: '#fff' }}>
              <th style={{ padding: 10 }}>ID</th>
              <th>Nombre</th>
              <th>Cerrada</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {usuariosPagina.map(u => (
              <tr key={u.id} style={{ borderBottom: '1px solid #eee' }}>
                <td style={{ textAlign: 'center' }}>{u.id}</td>
                <td>{u.nombre}</td>
                <td>{u.cerrada}</td>
                <td>
                  <span style={{
                    background: u.estatus === 'Activo' ? '#22b14c' : '#e53935',
                    color: '#fff',
                    borderRadius: 8,
                    padding: '4px 16px',
                    fontWeight: 600,
                    fontSize: 14
                  }}>{u.estatus}</span>
                </td>
                <td style={{ textAlign: 'center' }}>
                  <button title="Ver/Editar" style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: 18, marginRight: 8 }} onClick={() => abrirModal(u)}><i className="fas fa-eye"></i></button>
                  <button title="Eliminar" style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: 18, color: '#e53935' }} onClick={() => eliminarUsuario(u.id)}><i className="fas fa-times"></i></button>
                </td>
              </tr>
            ))}
            {usuariosPagina.length === 0 && (
              <tr><td colSpan={5} style={{ textAlign: 'center', color: '#888', padding: 20 }}>Sin resultados</td></tr>
            )}
          </tbody>
        </table>
      </div>
      {/* Paginación */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', margin: '20px auto 0', maxWidth: 900 }}>
        <div>
          Items per page
          <select value={porPagina} disabled style={{ marginLeft: 8, padding: 4, borderRadius: 4 }}>
            <option value={10}>10</option>
          </select>
        </div>
        <div>
          {pagina > 1 && <button onClick={() => setPagina(pagina - 1)} style={{ marginRight: 10 }}>&lt;</button>}
          {pagina} / {totalPaginas}
          {pagina < totalPaginas && <button onClick={() => setPagina(pagina + 1)} style={{ marginLeft: 10 }}>&gt;</button>}
        </div>
      </div>
      {/* Modal */}
      {modalAbierto && (
        <div style={{ position: 'fixed', top: 0, left: 0, width: '100vw', height: '100vh', background: '#0008', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }}>
          <form onSubmit={guardarUsuario} style={{ background: '#fff', borderRadius: 12, padding: 32, minWidth: 320, boxShadow: '0 4px 16px #0003', display: 'flex', flexDirection: 'column', gap: 16 }}>
            <h3>{modalUsuario ? 'Editar Usuario' : 'Añadir Usuario'}</h3>
            <input name="nombre" defaultValue={modalUsuario?.nombre || ''} placeholder="Nombre" required style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <input name="email" defaultValue={modalUsuario?.email || ''} placeholder="Email" required type="email" style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <input name="phone" defaultValue={modalUsuario?.phone || ''} placeholder="Teléfono" required style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            <select name="role" defaultValue={modalUsuario?.role || roles[0].value} style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }}>
              {roles.map(r => <option key={r.value} value={r.value}>{r.label}</option>)}
            </select>
            {!modalUsuario && (
              <input name="password" placeholder="Contraseña" required type="password" style={{ padding: 8, borderRadius: 6, border: '1px solid #ccc' }} />
            )}
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 10 }}>
              <button type="button" onClick={cerrarModal} style={{ background: '#eee', border: 'none', borderRadius: 6, padding: '8px 18px', fontWeight: 600, cursor: 'pointer' }}>Cancelar</button>
              <button type="submit" style={{ background: '#139BFF', color: '#fff', border: 'none', borderRadius: 6, padding: '8px 18px', fontWeight: 600, cursor: 'pointer' }}>{modalUsuario ? 'Guardar' : 'Crear'}</button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};

export default Usuarios; 