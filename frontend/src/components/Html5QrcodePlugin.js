import React, { useEffect, useRef } from 'react';
import { Html5Qrcode } from 'html5-qrcode';

const Html5QrcodePlugin = ({
  fps = 10,
  qrbox = 250,
  disableFlip = false,
  qrCodeSuccessCallback = null,
}) => {
  const qrcodeRegionId = 'html5qr-code-full-region';
  const html5QrcodeScanner = useRef(null);

  useEffect(() => {
    if (qrCodeSuccessCallback) {
      const scanner = new Html5Qrcode(qrcodeRegionId);
      html5QrcodeScanner.current = scanner;

      scanner.start(
        { facingMode: 'environment' },
        {
          fps,
          qrbox,
          disableFlip,
        },
        (decodedText) => {
          qrCodeSuccessCallback(decodedText);
        },
        (error) => {
          // Silently handle scanning errors
        }
      );

      return () => {
        scanner.stop().catch(() => {
          // Silently handle stop errors
        });
      };
    }
  }, [fps, qrbox, disableFlip, qrCodeSuccessCallback]);

  return (
    <div id={qrcodeRegionId} style={{ width: '100%', marginTop: '20px' }} />
  );
};

export default Html5QrcodePlugin;
