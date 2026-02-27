import Dexie from 'dexie';

export const db = new Dexie('QRScannerDB');

db.version(1).stores({
  devices: '++id, userId',
  records: '++id, deviceId',
  scans: '++id, deviceId'
});

// Offline data store
export const offlineStore = {
  saveDevice: async (device) => {
    return db.devices.put(device);
  },
  
  getDevices: async (userId) => {
    return db.devices.where('userId').equals(userId).toArray();
  },
  
  getDevice: async (id) => {
    return db.devices.get(id);
  },
  
  deleteDevice: async (id) => {
    return db.devices.delete(id);
  },
  
  saveRecord: async (record) => {
    return db.records.put(record);
  },
  
  getRecords: async (deviceId) => {
    return db.records.where('deviceId').equals(deviceId).toArray();
  },
  
  saveScan: async (scan) => {
    return db.scans.put(scan);
  },
  
  getScans: async () => {
    return db.scans.toArray();
  }
};
