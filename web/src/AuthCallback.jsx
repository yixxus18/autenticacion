import { useEffect, useState, useRef } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { AUTH_CONFIG } from './configs/config';

function AuthCallback() {
  const navigate = useNavigate();
  const location = useLocation();
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const effectRan = useRef(false);

  useEffect(() => {
    if (effectRan.current === true) {
      return;
    }
    effectRan.current = true;

    const handleCallback = async () => {
      const searchParams = new URLSearchParams(location.search);
      const code = searchParams.get('code');
      const storedCodeVerifier = localStorage.getItem('pkce_code_verifier');

      if (!code) {
        setError('No se encontró el código de autorización.');
        setLoading(false);
        return;
      }
      if (!storedCodeVerifier) {
        setError('No se encontró el verificador PKCE. Por favor, intenta iniciar sesión de nuevo.');
        setLoading(false);
        return;
      }

      try {
        const formData = new URLSearchParams();
        formData.append('grant_type', 'authorization_code');
        formData.append('client_id', AUTH_CONFIG.publicClientId);
        formData.append('redirect_uri', AUTH_CONFIG.reactAppCallbackUrl);
        formData.append('code', code);
        formData.append('code_verifier', storedCodeVerifier);

        const response = await fetch(
          `${AUTH_CONFIG.tecnoGuardApiUrl}/oauth/token`,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'Accept': 'application/json',
            },
            body: formData,
          }
        );

        const responseData = await response.json();

        if (!response.ok) {
          throw new Error(responseData.error_description || responseData.message || 'Error desconocido del servidor.');
        }

        localStorage.removeItem('pkce_code_verifier');
        localStorage.setItem('access_token', responseData.access_token);
        if (responseData.refresh_token) {
          localStorage.setItem('refresh_token', responseData.refresh_token);
        }

        navigate('/home');

      } catch (err) {
        setError(err.message);
        localStorage.removeItem('pkce_code_verifier');
      } finally {
        setLoading(false);
      }
    };

    handleCallback();
  }, [location.search, navigate]);

  if (loading) {
    return (
        <div style={{ fontFamily: 'Arial, sans-serif', textAlign: 'center', marginTop: '100px'}}>
            <h2>Verificando...</h2>
        </div>
    );
  }

  if (error) {
    return (
        <div style={{ 
            fontFamily: 'Arial, sans-serif',
            textAlign: 'center', 
            padding: '40px', 
            color: '#333',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            height: 'calc(100vh - 80px)'
        }}>
            <h2 style={{ color: '#d9534f' }}>Error de Autenticación</h2>
            <p style={{ fontSize: '1.1em', color: '#555' }}>{error}</p>
            <p>Esto puede suceder si la página fue recargada o el código de autorización expiró.</p>
            <button
              onClick={() => navigate('/')}
              style={{
                marginTop: '20px',
                padding: '12px 25px',
                fontSize: '16px',
                cursor: 'pointer',
                backgroundColor: '#139BFF',
                color: 'white',
                border: 'none',
                borderRadius: '5px',
                transition: 'background-color 0.2s'
              }}
              onMouseOver={(e) => e.target.style.backgroundColor = '#007acc'}
              onMouseOut={(e) => e.target.style.backgroundColor = '#139BFF'}
            >
              Volver al Inicio de Sesión
            </button>
        </div>
    );
  }

  return null;
}

export default AuthCallback;