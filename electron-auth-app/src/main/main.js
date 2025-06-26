// main.js - VERSIÓN FINAL CON SERVIDOR LOOPBACK
const { app, BrowserWindow, shell, ipcMain } = require('electron');
const path = require('path');
const http = require('http');
const url = require('url');

let mainWindow;

// --- Configuración del Servidor Loopback ---
const LOOPBACK_PORT = 42813;
let server;

function createMainWindow() {
  mainWindow = new BrowserWindow({
    width: 900,
    height: 700,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    }
  });
  mainWindow.loadFile('public/index.html');
}

// Iniciar el servidor para escuchar el callback de OAuth
function startLoopbackServer() {
  server = http.createServer((req, res) => {
    const { query } = url.parse(req.url, true);

    if (query.code) {
      // Código recibido, enviarlo a la ventana principal
      if (mainWindow) {
        mainWindow.focus();
        mainWindow.webContents.send('oauth-code', { code: query.code });
      }
      
      // Responder al navegador para que el usuario pueda cerrar la pestaña
      res.writeHead(200, {'Content-Type': 'text/html'});
      res.end('<h1>Autenticación exitosa.</h1><p>Puedes cerrar esta ventana y volver a la aplicación.</p>');
      
      // Detener el servidor ya que no se necesita más
      server.close();

    } else {
      // Manejar caso de error
      res.writeHead(400, {'Content-Type': 'text/html'});
      res.end('<h1>Error en la autenticación.</h1><p>No se recibió el código de autorización.</p>');
      server.close();
    }
  }).listen(LOOPBACK_PORT);
}

app.whenReady().then(() => {
  startLoopbackServer(); // Inicia el servidor al arrancar la app
  createMainWindow();
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  // Asegurarse de que el servidor se cierre al salir de la app
  if (server) {
    server.close();
  }
});

// El renderer pide abrir la URL de autenticación
ipcMain.on('open-auth-window', (event, authUrl) => {
  shell.openExternal(authUrl); // Abre la URL en el navegador por defecto
});