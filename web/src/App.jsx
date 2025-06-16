import { useEffect } from 'react'
import './App.css'
import { AUTH_CONFIG } from './configs/config';
import { useNavigate } from 'react-router-dom';
import logo from './assets/tecno-guard-logo.png';

function generateRandomString(length) {
  const possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let text = '';
  for (let i = 0; i < length; i++) {
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  }
  return text;
}

function base64urlencode(buffer) {
  return btoa(String.fromCharCode(...new Uint8Array(buffer)))
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=/g, '');
}

async function generateCodeChallenge(codeVerifier) {
  const encoder = new TextEncoder();
  const data = encoder.encode(codeVerifier);
  const hash = await crypto.subtle.digest('SHA-256', data);
  return base64urlencode(hash);
}

function App() {
  const navigate = useNavigate();

  const handleLogin = async () => {
    const codeVerifier = generateRandomString(128);
    const codeChallenge = await generateCodeChallenge(codeVerifier);

    sessionStorage.setItem('pkce_code_verifier', codeVerifier);

    const params = new URLSearchParams({
      client_id: AUTH_CONFIG.publicClientId, 
      redirect_uri: AUTH_CONFIG.reactAppCallbackUrl,
      response_type: 'code',
      scope: '*' , 
      state: generateRandomString(32), 
      code_challenge: codeChallenge,
      code_challenge_method: 'S256',
    });

    const authUrl = `${AUTH_CONFIG.tecnoGuardApiUrl}/oauth/authorize?${params.toString()}`;
    
    window.open(authUrl, 'Login', 'width=600,height=700');
  };

  const handleRegister = () => {
    const registerUrl = `${AUTH_CONFIG.tecnoGuardApiUrl}/register`;
    window.open(registerUrl, 'Register', 'width=600,height=700');
  };

  useEffect(() => {
    const handleMessage = (event) => {
      if (event.origin !== AUTH_CONFIG.reactAppOrigin) {
        console.warn('Mensaje de origen desconocido', event.origin);
        return;
      }

      const { type, payload } = event.data;

      if (type === 'AUTH_SUCCESS' && payload.access_token) {
        localStorage.setItem('access_token', payload.access_token);
        if (payload.refresh_token) {
          localStorage.setItem('refresh_token', payload.refresh_token);
        }
        if (payload.user_data) {
          localStorage.setItem('user_data', JSON.stringify(payload.user_data));
        }
        navigate('/home');
      } else if (type === 'AUTH_ERROR' && payload.error) {
        console.error('Error de autenticación desde pop-up:', payload.error);
      }
    };

    window.addEventListener('message', handleMessage);

    return () => {
      window.removeEventListener('message', handleMessage);
    };
  }, [navigate]);

  return (
    <div style={{
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
      minHeight: '100vh',
      backgroundColor: '#eaebe7',
      fontFamily: 'Arial, sans-serif'
    }}>
      <div style={{
        backgroundColor: 'white',
        borderRadius: '10px',
        boxShadow: '0 4px 8px rgba(0,0,0,0.1)',
        padding: '40px',
        textAlign: 'center',
        marginRight: '50px',
        width: '300px'
      }}>
        <h2 style={{ color: '#333', marginBottom: '30px' }}>Inicio de Sesión</h2>
        <button
          onClick={handleLogin}
          style={{
            padding: '12px 25px',
            fontSize: '16px',
            cursor: 'pointer',
            backgroundColor: '#139BFF',
            color: 'white',
            border: 'none',
            borderRadius: '5px',
            marginBottom: '20px',
            width: '100%'
          }}
        >
          Iniciar Sesión
        </button>

        <h2 style={{ color: '#333', marginBottom: '30px', marginTop: '30px' }}>Crear Cuenta</h2>
        <button
          onClick={handleRegister}
          style={{
            padding: '12px 25px',
            fontSize: '16px',
            cursor: 'pointer',
            backgroundColor: '#66B2FF',
            color: 'white',
            border: 'none',
            borderRadius: '5px',
            width: '100%'
          }}
        >
          Registrarse
        </button>
      </div>

      <div style={{ textAlign: 'center' }}>
        <img src={logo} alt="Logo TecnoGuard" style={{ maxWidth: '300px', marginBottom: '20px' }} /> 
      </div>
    </div>
  )
}

export default App
