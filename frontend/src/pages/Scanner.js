import React, { useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import Html5QrcodePlugin from '../components/Html5QrcodePlugin';

function Scanner() {
  const navigate = useNavigate();

  const onNewScanResult = (decodedText) => {
    // Extract device ID from QR code URL
    const match = decodedText.match(/device\/(\d+)/);
    if (match) {
      navigate(`/device/${match[1]}`);
    }
  };

  return (
    <div className="scanner-container">
      <h1>Scan QR Code</h1>
      <button onClick={() => navigate('/dashboard')} className="btn btn-secondary">
        ← Back
      </button>
      <Html5QrcodePlugin
        fps={10}
        qrbox={250}
        disableFlip={false}
        qrCodeSuccessCallback={onNewScanResult}
      />
    </div>
  );
}

export default Scanner;
