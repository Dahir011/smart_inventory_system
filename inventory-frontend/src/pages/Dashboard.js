/**
 * Dashboard Page
 * Main dashboard with statistics and charts
 */

import React, { useState, useEffect } from 'react';
import { dashboardAPI, alertAPI } from '../services/api';
import { Doughnut, Bar, Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';
import './Dashboard.css';

ChartJS.register(
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend
);

const Dashboard = () => {
  const [stats, setStats] = useState(null);
  const [categories, setCategories] = useState([]);
  const [monthlyChanges, setMonthlyChanges] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
    
    // Check for alerts on mount
    alertAPI.check().catch(console.error);
  }, []);

  const loadDashboardData = async () => {
    try {
      const [statsRes, categoriesRes, monthlyRes] = await Promise.all([
        dashboardAPI.getStats(),
        dashboardAPI.getCategories(),
        dashboardAPI.getMonthlyChanges(),
      ]);

      setStats(statsRes.data.data);
      setCategories(categoriesRes.data.data);
      setMonthlyChanges(monthlyRes.data.data);
    } catch (error) {
      console.error('Error loading dashboard:', error);
    } finally {
      setLoading(false);
    }
  };

  // Category distribution chart
  const categoryChartData = {
    labels: categories.map(c => c.category),
    datasets: [
      {
        label: 'Products',
        data: categories.map(c => c.count),
        backgroundColor: [
          '#FF6384',
          '#36A2EB',
          '#FFCE56',
          '#4BC0C0',
          '#9966FF',
          '#FF9F40',
        ],
      },
    ],
  };

  // Monthly changes chart
  const monthlyChartData = {
    labels: monthlyChanges.map(m => m.month),
    datasets: [
      {
        label: 'Added',
        data: monthlyChanges.map(m => m.added),
        backgroundColor: '#36A2EB',
      },
      {
        label: 'Removed',
        data: monthlyChanges.map(m => m.removed),
        backgroundColor: '#FF6384',
      },
    ],
  };

  if (loading) {
    return <div className="spinner"></div>;
  }

  return (
    <div className="dashboard">
      <h2>Dashboard Overview</h2>

      {/* Statistics Cards */}
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-icon">📦</div>
          <div className="stat-content">
            <h3>{stats?.total_products || 0}</h3>
            <p>Total Products</p>
          </div>
        </div>

        <div className="stat-card warning">
          <div className="stat-icon">⚠️</div>
          <div className="stat-content">
            <h3>{stats?.low_stock || 0}</h3>
            <p>Low Stock Items</p>
          </div>
        </div>

        <div className="stat-card danger">
          <div className="stat-icon">⏰</div>
          <div className="stat-content">
            <h3>{stats?.near_expiry || 0}</h3>
            <p>Near Expiry</p>
          </div>
        </div>

        <div className="stat-card expired">
          <div className="stat-icon">❌</div>
          <div className="stat-content">
            <h3>{stats?.expired || 0}</h3>
            <p>Expired Products</p>
          </div>
        </div>

        <div className="stat-card info">
          <div className="stat-icon">🔔</div>
          <div className="stat-content">
            <h3>{stats?.unread_alerts || 0}</h3>
            <p>Unread Alerts</p>
          </div>
        </div>
      </div>

      {/* Charts */}
      <div className="charts-grid">
        <div className="card">
          <div className="card-header">Products by Category</div>
          <div className="chart-container">
            {categories.length > 0 ? (
              <Doughnut data={categoryChartData} options={{ responsive: true }} />
            ) : (
              <p className="text-center">No data available</p>
            )}
          </div>
        </div>

        <div className="card">
          <div className="card-header">Monthly Inventory Changes</div>
          <div className="chart-container">
            {monthlyChanges.length > 0 ? (
              <Bar data={monthlyChartData} options={{ responsive: true }} />
            ) : (
              <p className="text-center">No data available</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
