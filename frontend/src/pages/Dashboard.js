import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store';
import { deviceAPI } from '../api';
import DeviceList from '../components/DeviceList';
import './Dashboard.css';

function Dashboard() {
  const [devices, setDevices] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    type: '',
    location: '',
    manufacturer: '',
    serialNumber: '',
  });

  const navigate = useNavigate();
  const user = useAuthStore((state) => state.user);
  const logout = useAuthStore((state) => state.logout);

  useEffect(() => {
    if (!user) {
      navigate('/login');
      return;
    }
    loadDevices();
  }, [user, navigate]);

  const loadDevices = async () => {
    try {
      setLoading(true);
      const response = await deviceAPI.getList();
      setDevices(response.data);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to load devices');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await deviceAPI.create(formData);
      setFormData({
        name: '',
        type: '',
        location: '',
        manufacturer: '',
        serialNumber: '',
      });
      setShowForm(false);
      loadDevices();
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to create device');
    }
  };

  const handleScan = () => {
    navigate('/scan');
  };

  return (
    <div className="dashboard">
      <header className="dashboard-header">
        <h1>Device Dashboard</h1>
        <div className="header-actions">
          <button onClick={handleScan} className="btn btn-primary">
            📱 Scan QR Code
          </button>
          <button onClick={() => setShowForm(!showForm)} className="btn btn-success">
            ➕ New Device
          </button>
          <button onClick={() => { logout(); navigate('/login'); }} className="btn btn-danger">
            Logout
          </button>
        </div>
      </header>

      {error && <div className="alert alert-error">{error}</div>}

      {showForm && (
        <div className="form-container">
          <h2>Add New Device</h2>
          <form onSubmit={handleSubmit}>
            <input
              type="text"
              placeholder="Device Name"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              required
            />
            <input
              type="text"
              placeholder="Type (Elevator, Pump, etc.)"
              value={formData.type}
              onChange={(e) => setFormData({ ...formData, type: e.target.value })}
              required
            />
            <input
              type="text"
              placeholder="Location"
              value={formData.location}
              onChange={(e) => setFormData({ ...formData, location: e.target.value })}
              required
            />
            <input
              type="text"
              placeholder="Manufacturer"
              value={formData.manufacturer}
              onChange={(e) => setFormData({ ...formData, manufacturer: e.target.value })}
              required
            />
            <input
              type="text"
              placeholder="Serial Number"
              value={formData.serialNumber}
              onChange={(e) => setFormData({ ...formData, serialNumber: e.target.value })}
              required
            />
            <div className="form-actions">
              <button type="submit" className="btn btn-success">Create</button>
              <button type="button" onClick={() => setShowForm(false)} className="btn btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      )}

      {loading ? (
        <p>Loading devices...</p>
      ) : devices.length === 0 ? (
        <p>No devices yet. Create one to get started!</p>
      ) : (
        <DeviceList devices={devices} onRefresh={loadDevices} />
      )}
    </div>
  );
}

export default Dashboard;
