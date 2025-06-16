import React, { useState, useEffect } from 'react';
import { AUTH_CONFIG } from '../configs/config';

const Home = () => {
  const [unauthorizedResponse, setUnauthorizedResponse] = useState(null);
  const [authorizedResponse, setAuthorizedResponse] = useState(null);
  const [userData, setUserData] = useState(null);

  useEffect(() => {
    const storedUserData = localStorage.getItem('user_data');
    if (storedUserData) {
      setUserData(JSON.parse(storedUserData));
    }
  }, []);

  const fetchUnauthorizedData = async () => {
    try {
      const response = await fetch(`${AUTH_CONFIG.tecnoGuardApiUrl}/api/user`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });
      const data = await response.json();
      setUnauthorizedResponse({ status: response.status, data: data });
    } catch (error) {
      console.error('Error al obtener datos no autorizados:', error);
      setUnauthorizedResponse({ status: 'Error', data: error.message });
    }
  };

  const fetchAuthorizedData = async () => {
    const accessToken = localStorage.getItem('access_token');
    if (!accessToken) {
      setAuthorizedResponse({ status: 'Error', data: 'No se encontró el token de acceso.' });
      return;
    }

    try {
      const response = await fetch(`${AUTH_CONFIG.tecnoGuardApiUrl}/api/user`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'Accept': 'application/json',
        },
      });
      const data = await response.json();
      setAuthorizedResponse({ status: response.status, data: data });
    } catch (error) {
      console.error('Error al obtener datos autorizados:', error);
      setAuthorizedResponse({ status: 'Error', data: error.message });
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
        <h2>Probar Consumo de API de Negocio</h2>
        <p>
          Para que estas pruebas funcionen, asegúrate de que tu `BUSINESS_API_URL` esté configurada en `web/src/config.js` y que tu API de negocio tenga una ruta `/api/some-protected-route` protegida con autenticación Passport.
        </p>
        <button
          onClick={fetchUnauthorizedData}
          style={{
            padding: '10px 20px',
            fontSize: '16px',
            cursor: 'pointer',
            backgroundColor: '#ffc107',
            color: 'black',
            border: 'none',
            borderRadius: '5px',
            marginRight: '10px',
          }}
        >
          Probar API (Sin Token)
        </button>
        <button
          onClick={fetchAuthorizedData}
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
          Probar API (Con Token)
        </button>

        {unauthorizedResponse && (
          <div style={{ marginTop: '20px', padding: '10px', border: '1px solid #dc3545', borderRadius: '5px', backgroundColor: '#f8d7da' }}>
            <h3>Respuesta (Sin Token) - Estado: {unauthorizedResponse.status}</h3>
            <pre>{JSON.stringify(unauthorizedResponse.data, null, 2)}</pre>
          </div>
        )}

        {authorizedResponse && (
          <div style={{ marginTop: '20px', padding: '10px', border: '1px solid #28a745', borderRadius: '5px', backgroundColor: '#d4edda' }}>
            <h3>Respuesta (Con Token) - Estado: {authorizedResponse.status}</h3>
            <pre>{JSON.stringify(authorizedResponse.data, null, 2)}</pre>
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