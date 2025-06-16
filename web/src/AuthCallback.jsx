import { useEffect, useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { AUTH_CONFIG } from './configs/config';

function AuthCallback() {
  const navigate = useNavigate();
  const location = useLocation();
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const handleCallback = async () => {
      const searchParams = new URLSearchParams(location.search);
      const code = searchParams.get('code');
      const storedCodeVerifier = sessionStorage.getItem('pkce_code_verifier');

      if (!code) {
        if (window.opener) {
          window.opener.postMessage({ type: 'AUTH_ERROR', payload: { error: 'No se encontró el código de autorización.' } }, window.opener.location.origin);
        }
        window.close();
        return;
      }

      try {
        if (!code || !storedCodeVerifier) {
          throw new Error('Faltan parámetros necesarios para la autenticación');
        }

        const formData = new URLSearchParams();
        formData.append('grant_type', 'authorization_code');
        formData.append('client_id', AUTH_CONFIG.publicClientId);
        formData.append('redirect_uri', AUTH_CONFIG.reactAppCallbackUrl);
        formData.append('code', code);
        formData.append('code_verifier', storedCodeVerifier);

        if (import.meta.env.DEV) {
          console.log('Parámetros de la solicitud:', {
            grant_type: 'authorization_code',
            client_id: AUTH_CONFIG.publicClientId,
            redirect_uri: AUTH_CONFIG.reactAppCallbackUrl,
            code: code.substring(0, 10) + '...',
            code_verifier: storedCodeVerifier.substring(0, 10) + '...'
          });
        }

        const response = await fetch(
          `${AUTH_CONFIG.tecnoGuardApiUrl}/oauth/token`,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'Accept': 'application/json',
            },
            credentials: 'include',
            body: formData,
          }
        );

        const responseData = await response.json();

        if (!response.ok) {
          let errorMessage = 'Error al intercambiar el código.';
          
          if (responseData.error) {
            errorMessage = `Error del servidor: ${responseData.error}`;
            if (responseData.error_description) {
              errorMessage += ` - ${responseData.error_description}`;
            }
            if (import.meta.env.DEV) {
              console.error('Detalles del error:', responseData);
            }
          } else if (responseData.message) {
            errorMessage = responseData.message;
          }

          throw new Error(errorMessage);
        }

        localStorage.setItem('access_token', responseData.access_token);
        if (responseData.refresh_token) {
          localStorage.setItem('refresh_token', responseData.refresh_token);
        }

        sessionStorage.removeItem('pkce_code_verifier');

        let userData = null;
        try {
          const userResponse = await fetch(
            `${AUTH_CONFIG.tecnoGuardApiUrl}/api/user`,
            {
              headers: {
                'Authorization': `Bearer ${responseData.access_token}`,
                'Accept': 'application/json',
              },
            }
          );

          if (userResponse.ok) {
            userData = await userResponse.json();
            localStorage.setItem('user_data', JSON.stringify(userData));
          }
        } catch (userError) {
          console.error('Error al obtener datos del usuario:', userError);
        }

        if (window.opener) {
          window.opener.postMessage({
            type: 'AUTH_SUCCESS',
            payload: { access_token: responseData.access_token, refresh_token: responseData.refresh_token, user_data: userData }
          }, window.opener.location.origin);
        }
        window.close();

      } catch (err) {
        console.error('Error en el callback de autenticación:', err.message);
        setError(err.message);
        sessionStorage.removeItem('pkce_code_verifier');
        if (window.opener) {
          window.opener.postMessage({ type: 'AUTH_ERROR', payload: { error: err.message } }, window.opener.location.origin);
        }
        window.close();
      } finally {
        setLoading(false);
      }
    };

    handleCallback();
  }, [location.search, navigate]);

  if (loading) {
    return <div>Cargando...</div>;
  }

  if (error) {
    return <div>Error: {error}</div>;
  }

  return null;
}

export default AuthCallback; 