import React, { useState, useEffect } from 'react';
import { AUTH_CONFIG } from '../configs/config';

const Home = () => {
  const [healthResponse, setHealthResponse] = useState(null);
  const [meResponse, setMeResponse] = useState(null);
  const [cerradaResponse, setCerradaResponse] = useState(null);
  const [userData, setUserData] = useState(null);

  useEffect(() => {
    const storedUserData = localStorage.getItem('user_data');
    if (storedUserData) {
      setUserData(JSON.parse(storedUserData));
    }
  }, []);

  const fetchHealthData = async () => {
    try {
      const response = await fetch(`http://localhost:8001/health`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });
      const data = await response.json();
      setHealthResponse({ status: response.status, data: data });
    } catch (error) {
      console.error('Error al obtener datos de health:', error);
      setHealthResponse({ status: 'Error', data: error.message });
    }
  };

  const fetchMeData = async () => {
    const accessToken = localStorage.getItem('access_token');
    if (!accessToken) {
      setMeResponse({ status: 'Error', data: 'No se encontró el token de acceso.' });
      return;
    }

    try {
      const response = await fetch(`http://localhost:8001/me`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'Accept': 'application/json',
        },
      });
      const data = await response.json();
      setMeResponse({ status: response.status, data: data });
    } catch (error) {
      console.error('Error al obtener datos de /me:', error);
      setMeResponse({ status: 'Error', data: error.message });
    }
  };

  const createCerrada = async () => {
    const accessToken = localStorage.getItem('access_token');
    if (!accessToken) {
      setCerradaResponse({ status: 'Error', data: 'No se encontró el token de acceso.' });
      return;
    }

    try {
      const response = await fetch(`http://localhost:8001/api/v1/admin/cerradas`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          nombre: 'Cerrada de Prueba',
          direccion: 'Dirección de prueba',
          // Agrega más campos según necesites
        }),
      });
      const data = await response.json();
      setCerradaResponse({ status: response.status, data: data });
    } catch (error) {
      console.error('Error al crear cerrada:', error);
      setCerradaResponse({ status: 'Error', data: error.message });
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('user_data');
    window.location.href = '/';
  };

  return (
    <div style={{ padding: '20px', maxWidth: '800px', margin: 'auto' }}>
      <h1>Bienvenido al Home de la React App</h1>
      {userData && (
        <div style={{ marginBottom: '20px', padding: '15px', border: '1px solid #ddd', borderRadius: '8px' }}>
          <h3>Información del Usuario (desde Tecno Guard API):</h3>
          <p><strong>Nombre:</strong> {userData.name}</p>
          <p><strong>Email:</strong> {userData.email}</p>
        </div>
      )}

      <div style={{ marginBottom: '20px' }}>
        <h2>Probar API de Autenticación (Puerto 8000)</h2>
        <button
          onClick={fetchHealthData}
          style={{
            padding: '10px 20px',
            fontSize: '16px',
            cursor: 'pointer',
            backgroundColor: '#17a2b8',
            color: 'white',
            border: 'none',
            borderRadius: '5px',
            marginRight: '10px',
          }}
        >
          Probar /health (Pública)
        </button>
        <button
          onClick={fetchMeData}
          style={{
            padding: '10px 20px',
            fontSize: '16px',
            cursor: 'pointer',
            backgroundColor: '#28a745',
            color: 'white',
            border: 'none',
            borderRadius: '5px',
          }}
        >
          Probar /me (Con Token)
        </button>

        {healthResponse && (
          <div style={{ marginTop: '20px', padding: '10px', border: '1px solid #17a2b8', borderRadius: '5px', backgroundColor: '#d1ecf1' }}>
            <h3>Respuesta /health - Estado: {healthResponse.status}</h3>
            <pre style={{
              maxHeight: '300px',
              overflow: 'auto',
              background: '#f8f9fa',
              padding: '10px'
            }}>
              {JSON.stringify(healthResponse.data, null, 2)}
            </pre>
          </div>
        )}

        {meResponse && (
          <div style={{ marginTop: '20px', padding: '10px', border: '1px solid #28a745', borderRadius: '5px', backgroundColor: '#d4edda' }}>
            <h3>Respuesta /me - Estado: {meResponse.status}</h3>
            <pre style={{
              maxHeight: '300px',
              overflow: 'auto',
              background: '#f8f9fa',
              padding: '10px'
            }}>
              {JSON.stringify(meResponse.data, null, 2)}
            </pre>
          </div>
        )}
      </div>

      <div style={{ marginBottom: '20px' }}>
        <h2>Probar API de Negocio (Puerto 8001)</h2>
        <button
          onClick={createCerrada}
          style={{
            padding: '10px 20px',
            fontSize: '16px',
            cursor: 'pointer',
            backgroundColor: '#6f42c1',
            color: 'white',
            border: 'none',
            borderRadius: '5px',
          }}
        >
          Crear Cerrada (Admin)
        </button>

        {cerradaResponse && (
          <div style={{ marginTop: '20px', padding: '10px', border: '1px solid #6f42c1', borderRadius: '5px', backgroundColor: '#e2d9f3' }}>
            <h3>Respuesta Crear Cerrada - Estado: {cerradaResponse.status}</h3>
            <pre style={{
              maxHeight: '300px',
              overflow: 'auto',
              background: '#f8f9fa',
              padding: '10px'
            }}>
              {JSON.stringify(cerradaResponse.data, null, 2)}
            </pre>
          </div>
        )}
      </div>

      <button
        onClick={handleLogout}
        style={{
          padding: '10px 20px',
          fontSize: '16px',
          cursor: 'pointer',
          backgroundColor: '#dc3545',
          color: 'white',
          border: 'none',
          borderRadius: '5px',
          marginTop: '20px',
        }}
      >
        Cerrar Sesión (React App)
      </button>
    </div>
  );
};

export default Home; 