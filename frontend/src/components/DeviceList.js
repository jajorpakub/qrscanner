import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store';
import { deviceAPI } from '../api';

function DeviceList({ devices, onRefresh }) {
  const navigate = useNavigate();
  const user = useAuthStore((state) => state.user);
  const [deleting, setDeleting] = useState(null);

  const isOwner = user && (user.role === 'owner' || user.role === 'technician');

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure?')) return;

    try {
      setDeleting(id);
      await deviceAPI.delete(id);
      onRefresh();
    } catch (err) {
      alert('Failed to delete device');
    } finally {
      setDeleting(null);
    }
  };

  return (
    <div className="device-list">
      {devices.map((device) => (
        <div
          key={device.id}
          className="device-item"
          onClick={() => navigate(`/device/${device.id}`)}
        >
          <h3>{device.name}</h3>
          <p>Type: {device.type}</p>
          <p>Location: {device.location}</p>
          <p>Serial: {device.serial_number}</p>
          {isOwner && (
            <button
              onClick={(e) => {
                e.stopPropagation();
                handleDelete(device.id);
              }}
              disabled={deleting === device.id}
              className="btn btn-danger btn-sm"
            >
              {deleting === device.id ? 'Deleting...' : 'Delete'}
            </button>
          )}
        </div>
      ))}
    </div>
  );
}

export default DeviceList;
