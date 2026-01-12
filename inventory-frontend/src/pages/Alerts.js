/**
 * Alerts Page
 * Display and manage system alerts
 */

import React, { useState, useEffect } from 'react';
import { alertAPI } from '../services/api';
import './Alerts.css';

const Alerts = () => {
  const [alerts, setAlerts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [unreadOnly, setUnreadOnly] = useState(false);

  useEffect(() => {
    loadAlerts();
    
    // Check for new alerts periodically
    const interval = setInterval(() => {
      alertAPI.check().then(() => loadAlerts());
    }, 60000); // Check every minute

    return () => clearInterval(interval);
  }, [unreadOnly]);

  const loadAlerts = async () => {
    try {
      const response = await alertAPI.getAll(unreadOnly);
      setAlerts(response.data.data);
    } catch (error) {
      console.error('Error loading alerts:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleMarkAsRead = async (id) => {
    try {
      await alertAPI.markAsRead(id);
      loadAlerts();
    } catch (error) {
      console.error('Error marking alert as read:', error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await alertAPI.markAllAsRead();
      loadAlerts();
    } catch (error) {
      console.error('Error marking all alerts as read:', error);
    }
  };

  const handleCheckAlerts = async () => {
    try {
      await alertAPI.check();
      loadAlerts();
    } catch (error) {
      console.error('Error checking alerts:', error);
    }
  };

  const getAlertIcon = (type) => {
    switch (type) {
      case 'low_stock':
        return '⚠️';
      case 'near_expiry':
        return '⏰';
      case 'expired':
        return '❌';
      default:
        return '🔔';
    }
  };

  const getAlertClass = (type) => {
    switch (type) {
      case 'low_stock':
        return 'alert-warning';
      case 'near_expiry':
        return 'alert-danger';
      case 'expired':
        return 'alert-expired';
      default:
        return 'alert-info';
    }
  };

  if (loading) {
    return <div className="spinner"></div>;
  }

  const unreadCount = alerts.filter(a => !a.is_read).length;

  return (
    <div className="alerts-page">
      <div className="page-header">
        <h2>System Alerts</h2>
        <div className="header-actions">
          <button
            className="btn btn-primary"
            onClick={handleCheckAlerts}
          >
            🔄 Check for Alerts
          </button>
          {unreadCount > 0 && (
            <button
              className="btn btn-success"
              onClick={handleMarkAllAsRead}
            >
              ✓ Mark All as Read
            </button>
          )}
        </div>
      </div>

      <div className="filters-card card">
        <label>
          <input
            type="checkbox"
            checked={unreadOnly}
            onChange={(e) => setUnreadOnly(e.target.checked)}
          />{' '}
          Show unread alerts only ({unreadCount} unread)
        </label>
      </div>

      <div className="alerts-list">
        {alerts.length === 0 ? (
          <div className="card text-center">
            <p>No alerts found</p>
          </div>
        ) : (
          alerts.map((alert) => (
            <div
              key={alert.id}
              className={`card alert-item ${alert.is_read ? 'read' : 'unread'} ${getAlertClass(alert.alert_type)}`}
            >
              <div className="alert-content">
                <div className="alert-icon">{getAlertIcon(alert.alert_type)}</div>
                <div className="alert-details">
                  <h4>{alert.product_name || 'Product'}</h4>
                  <p>{alert.message}</p>
                  <small>
                    {new Date(alert.created_at).toLocaleString()}
                    {' • '}
                    Type: <strong>{alert.alert_type.replace('_', ' ')}</strong>
                  </small>
                </div>
              </div>
              {!alert.is_read && (
                <button
                  className="btn btn-sm btn-secondary"
                  onClick={() => handleMarkAsRead(alert.id)}
                >
                  Mark as Read
                </button>
              )}
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default Alerts;
