// Verifica si hay un token de acceso válido y redirige al dashboard si es así
function checkAuthStatus(apiUrl, dashboardUrl) {
  const accessToken = localStorage.getItem('access_token');

  if (!accessToken) {
    // No hay token, no hacer nada
    return false;
  }

  fetch(`${apiUrl}/api/user`, {
    method: 'GET',
    headers: {
      Authorization: `Bearer ${accessToken}`,
      Accept: 'application/json'
    }
  })
    .then(response => {
      if (response.ok) {
        return response.json();
      } else {
        // Token inválido o expirado
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user_data');
        return false;
      }
    })
    .then(userData => {
      if (userData) {
        localStorage.setItem('user_data', JSON.stringify(userData));
        window.location.href = dashboardUrl;
        return true;
      }
    })
    .catch(error => {
      console.error('Error al verificar el token:', error);
      return false;
    });
}

module.exports = { checkAuthStatus };