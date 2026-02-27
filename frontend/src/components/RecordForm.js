import React, { useState } from 'react';
import { recordAPI } from '../api';

function RecordForm({ deviceId, onSuccess }) {
  const [formData, setFormData] = useState({
    recordDate: new Date().toISOString().split('T')[0],
    recordType: 'inspection',
    description: '',
    technician: '',
    notes: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      await recordAPI.create(deviceId, formData);
      setFormData({
        recordDate: new Date().toISOString().split('T')[0],
        recordType: 'inspection',
        description: '',
        technician: '',
        notes: '',
      });
      onSuccess();
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to create record');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="record-form">
      <form onSubmit={handleSubmit}>
        <input
          type="date"
          value={formData.recordDate}
          onChange={(e) => setFormData({ ...formData, recordDate: e.target.value })}
          required
        />
        <select
          value={formData.recordType}
          onChange={(e) => setFormData({ ...formData, recordType: e.target.value })}
        >
          <option value="inspection">Inspection</option>
          <option value="maintenance">Maintenance</option>
          <option value="repair">Repair</option>
          <option value="testing">Testing</option>
        </select>
        <textarea
          placeholder="Description"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          required
        />
        <input
          type="text"
          placeholder="Technician Name"
          value={formData.technician}
          onChange={(e) => setFormData({ ...formData, technician: e.target.value })}
        />
        <textarea
          placeholder="Notes"
          value={formData.notes}
          onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
        />
        {error && <div className="alert alert-error">{error}</div>}
        <button type="submit" disabled={loading} className="btn btn-success">
          {loading ? 'Saving...' : 'Add Record'}
        </button>
      </form>
    </div>
  );
}

export default RecordForm;
