import React from 'react';

function RecordList({ records }) {
  if (!records || records.length === 0) {
    return <p>No technical records yet.</p>;
  }

  return (
    <div className="record-list">
      {records.map((record) => (
        <div key={record.id} className="record-item">
          <div className="record-header">
            <h4>{record.record_type}</h4>
            <span className="record-date">{record.record_date}</span>
          </div>
          <p>{record.description}</p>
          {record.technician && <p><strong>Technician:</strong> {record.technician}</p>}
          {record.notes && <p><strong>Notes:</strong> {record.notes}</p>}
        </div>
      ))}
    </div>
  );
}

export default RecordList;
