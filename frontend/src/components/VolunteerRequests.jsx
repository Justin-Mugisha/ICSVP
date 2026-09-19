import { useState, useEffect } from 'react';
import { getOpenRequests, applyToRequest, getMyApplications } from '../services/volunteerService';

function VolunteerRequests() {
  const [requests, setRequests] = useState([]);
  const [applications, setApplications] = useState([]);
  const [message, setMessage] = useState('');

  const loadData = () => {
    getOpenRequests().then((response) => setRequests(response.data.requests));
    getMyApplications().then((response) => setApplications(response.data.applications));
  };

  useEffect(() => {
    loadData();
  }, []);

  const appliedRequestIds = applications.map((a) => a.request_id);

  const handleApply = async (requestId) => {
    setMessage('');
    try {
      await applyToRequest(requestId);
      loadData();
    } catch (err) {
      const msg = err.response?.data?.error || 'Failed to apply.';
      setMessage(msg);
    }
  };

  const statusColors = {
    pending: { bg: '#3a3a1f', color: '#ffe066' },
    accepted: { bg: '#1f3a24', color: '#7fd97f' },
    rejected: { bg: '#3a1f1f', color: '#ff6b6b' },
  };

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3 style={{ fontSize: '1rem', marginBottom: '0.8rem' }}>Open Requests</h3>

      {message && <p style={{ color: '#ff6b6b', fontSize: '0.9rem' }}>{message}</p>}

      {requests.length === 0 && <p style={{ color: '#999', fontSize: '0.9rem' }}>No open requests right now.</p>}

      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem', marginBottom: '2rem' }}>
        {requests.map((req) => {
          const alreadyApplied = appliedRequestIds.includes(req.id);
          return (
            <div
              key={req.id}
              style={{
                backgroundColor: '#1a1a1a',
                padding: '1rem',
                borderRadius: '6px',
                border: '1px solid #333',
              }}
            >
              <h4 style={{ margin: '0 0 0.4rem 0', fontSize: '1rem' }}>{req.title}</h4>
              <p style={{ margin: '0 0 0.6rem 0', color: '#999', fontSize: '0.9rem' }}>{req.description}</p>
              {req.requester_name && (
                <p style={{ margin: '0 0 0.6rem 0', color: '#777', fontSize: '0.8rem' }}>By {req.requester_name}</p>
              )}
              <button
                onClick={() => handleApply(req.id)}
                disabled={alreadyApplied}
                style={{
                  padding: '0.4rem 1rem',
                  backgroundColor: alreadyApplied ? '#333' : '#4a7dff',
                  color: alreadyApplied ? '#777' : '#fff',
                  border: 'none',
                  borderRadius: '4px',
                  cursor: alreadyApplied ? 'default' : 'pointer',
                  fontSize: '0.85rem',
                }}
              >
                {alreadyApplied ? 'Already Applied' : 'Apply'}
              </button>
            </div>
          );
        })}
      </div>

      <h3 style={{ fontSize: '1rem', marginBottom: '0.8rem' }}>My Applications</h3>

      {applications.length === 0 && <p style={{ color: '#999', fontSize: '0.9rem' }}>You haven't applied to anything yet.</p>}

      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
        {applications.map((app) => {
          const colors = statusColors[app.status] || statusColors.pending;
          return (
            <div
              key={app.id}
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                backgroundColor: '#1a1a1a',
                padding: '0.7rem 1rem',
                borderRadius: '6px',
              }}
            >
              <span style={{ fontSize: '0.9rem' }}>{app.title}</span>
              <span
                style={{
                  backgroundColor: colors.bg,
                  color: colors.color,
                  padding: '0.2rem 0.6rem',
                  borderRadius: '999px',
                  fontSize: '0.75rem',
                  textTransform: 'capitalize',
                }}
              >
                {app.status}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
}

export default VolunteerRequests;
