import React from 'react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import 'chart.js/auto';

const metricas = [
  {
    title: 'Total de Usuarios',
    value: '1,250',
    icon: <i className="fas fa-user" style={{ fontSize: 32 }}></i>,
    gradient: 'linear-gradient(90deg, #21e6c1 0%, #1fa2ff 100%)',
  },
  {
    title: 'Puertas Activas',
    value: '1,250',
    icon: <i className="fas fa-door-open" style={{ fontSize: 32 }}></i>,
    gradient: 'linear-gradient(90deg, #f7971e 0%, #ffd200 100%)',
  },
  {
    title: 'Pagos Pendientes',
    value: '1,250',
    icon: <i className="fas fa-money-bill-wave" style={{ fontSize: 32 }}></i>,
    gradient: 'linear-gradient(90deg, #21d4fd 0%, #b721ff 100%)',
  },
  {
    title: 'Accesos Hoy',
    value: '1,250',
    icon: <i className="fas fa-door-open" style={{ fontSize: 32 }}></i>,
    gradient: 'linear-gradient(90deg, #f7971e 0%, #ff5858 100%)',
  },
];

const barData = {
  labels: ['Cerrada 1', 'Cerrada 2', 'Cerrada 3'],
  datasets: [
    {
      label: 'Cerradas más transitadas',
      data: [80, 60, 70],
      backgroundColor: '#6ad3e6',
      borderRadius: 0,
    },
  ],
};

const doughnutData = {
  labels: ['Red', 'Blue', 'Yellow'],
  datasets: [
    {
      data: [300, 50, 100],
      backgroundColor: ['#ff6384', '#36a2eb', '#ffce56'],
      hoverBackgroundColor: ['#ff6384', '#36a2eb', '#ffce56'],
    },
  ],
};

const lineData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Visitantes',
      data: [30, 50, 80, 60, 70, 80],
      fill: false,
      borderColor: '#36a2eb',
      tension: 0.4,
    },
    {
      label: 'Accesos',
      data: [20, 40, 60, 80, 60, 90],
      fill: false,
      borderColor: '#8e5ea2',
      tension: 0.4,
    },
  ],
};

const Home = () => (
  <div style={{ width: '100%', minHeight: '100%', background: '#eaebe7' }}>
    {/* Métricas */}
    <div style={{ display: 'flex', gap: 30, margin: '30px 40px 0 40px', flexWrap: 'wrap' }}>
      {metricas.map((m, i) => (
        <div key={i} style={{ flex: 1, background: m.gradient, color: '#fff', borderRadius: 16, padding: 24, display: 'flex', flexDirection: 'column', alignItems: 'flex-start', minWidth: 180, minHeight: 90 }}>
          <div style={{ display: 'flex', alignItems: 'center', marginBottom: 10 }}>{m.icon}<span style={{ marginLeft: 12, fontWeight: 500 }}>{m.title}</span></div>
          <div style={{ fontSize: 32, fontWeight: 700 }}>{m.value}</div>
        </div>
      ))}
    </div>
    {/* Gráficas */}
    <div style={{ display: 'flex', gap: 30, margin: '30px 40px', flexWrap: 'wrap' }}>
      <div style={{ flex: 2, background: '#fff', borderRadius: 12, padding: 24, minWidth: 320 }}>
        <div style={{ fontWeight: 600, marginBottom: 10 }}>Cerradas más transitadas</div>
        <Bar data={barData} options={{ plugins: { legend: { display: false } } }} height={220} />
      </div>
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 30 }}>
        <div style={{ background: '#fff', borderRadius: 12, padding: 24 }}>
          <Doughnut data={doughnutData} options={{ plugins: { legend: { position: 'top' } } }} height={120} />
        </div>
        <div style={{ background: '#fff', borderRadius: 12, padding: 24 }}>
          <Line data={lineData} options={{ plugins: { legend: { position: 'top' } } }} height={120} />
        </div>
      </div>
    </div>
    <style>{`
      @media (max-width: 900px) {
        .metricas-row, .graficas-row {
          flex-direction: column !important;
          gap: 15px !important;
        }
      }
      @media (max-width: 600px) {
        .metricas-row, .graficas-row {
          margin: 10px !important;
          padding: 0 !important;
        }
      }
    `}</style>
  </div>
);

export default Home; 