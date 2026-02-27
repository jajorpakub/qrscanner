import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store';
import { deviceAPI, recordAPI } from '../api';
import RecordForm from '../components/RecordForm';
import RecordList from '../components/RecordList';
import './DeviceDetail.css';

function DeviceDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [device, setDevice] = useState(null);
  const [records, setRecords] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [qrImage, setQrImage] = useState(null);
  const [showRecordForm, setShowRecordForm] = useState(false);

  const user = useAuthStore((state) => state.user);
  const isOwner = user && (user.role === 'owner' || user.role === 'technician');

  useEffect(() => {
    loadDevice();
  }, [id]);

  const loadDevice = async () => {
    try {
      setLoading(true);
      const response = await deviceAPI.getFullPublic(id);
      setDevice(response.data);
      setRecords(response.data.records || []);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to load device');
    } finally {
      setLoading(false);
    }
  };

  const handleGenerateQR = async () => {
    try {
      const response = await deviceAPI.generateQR(id);
      setQrImage(response.data.qr);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to generate QR code');
    }
  };

  const handleDownloadQR = () => {
    if (!qrImage) return;
    const link = document.createElement('a');
    link.href = `data:image/png;base64,${qrImage}`;
    link.download = `device-${id}-qr.png`;
    link.click();
  };

  const handlePrintQR = () => {
    if (!qrImage) return;
    const printWindow = window.open('', '', 'width=400,height=400');
    printWindow.document.write(`
      <html><head><title>Device QR Code</title></head>
      <body style="text-align:center;">
        <h2>${device.name}</h2>
        <img src="data:image/png;base64,${qrImage}" style="width:300px;height:300px;" />
        <p>Serial: ${device.serial_number}</p>
      </body></html>
    `);
    printWindow.document.close();
    printWindow.print();
  };

  if (loading) return <div className="device-detail"><p>Loading...</p></div>;
  if (!device) return <div className="device-detail"><p>Device not found</p></div>;

  return (
    <div className="device-detail">
      <button onClick={() => navigate(-1)} className="btn btn-secondary">← Back</button>

      <div className="device-card">
        <h1>{device.name}</h1>
        <div className="device-info">
          <p><strong>Type:</strong> {device.type}</p>
          <p><strong>Location:</strong> {device.location}</p>
          <p><strong>Manufacturer:</strong> {device.manufacturer}</p>
          <p><strong>Serial Number:</strong> {device.serial_number}</p>
          <p><strong>Install Date:</strong> {device.install_date}</p>
        </div>

        {isOwner && (
          <div className="device-actions">
            <button onClick={handleGenerateQR} className="btn btn-primary">📱 Generate QR</button>
            {qrImage && (
              <>
                <button onClick={handleDownloadQR} className="btn btn-success">💾 Download QR</button>
                <button onClick={handlePrintQR} className="btn btn-info">🖨️ Print QR</button>
              </>
            )}
          </div>
        )}

        {qrImage && (
          <div className="qr-display">
            <img src={`data:image/png;base64,${qrImage}`} alt="Device QR Code" />
          </div>
        )}
      </div>

      {error && <div className="alert alert-error">{error}</div>}

      {isOwner && (
        <>
          <button onClick={() => setShowRecordForm(!showRecordForm)} className="btn btn-success">
            ➕ Add Technical Record
          </button>
          {showRecordForm && (
            <RecordForm
              deviceId={id}
              onSuccess={() => {
                setShowRecordForm(false);
                loadDevice();
              }}
            />
          )}
        </>
      )}

      <h2>Technical Records</h2>
      <RecordList records={records} />
    </div>
  );
}

export default DeviceDetail;
