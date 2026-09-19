import { useState, useEffect } from 'react';
import { getMyRequests, getMatches, updateApplicationStatus, deleteRequest } from '../services/requesterService';

function MyRequests({ refreshKey }) {
  const [requests, setRequests] = useState([]);
  const [expandedId, setExpandedId] = useState(null);
  const [matches, setMatches] = useState([]);
  const [loadingMatches, setLoadingMatches] = useState(false);
  const [message, setMessage] = useState('');

  const loadRequests = () => {
    getMyRequests().then((response) => setRequests(response.data.requests));
  };

  useEffect(() => {
    loadRequests();
  }, [refreshKey]);

  const toggleExpand = async (requestId) => {
    if (expandedId === requestId) {
      setExpandedId(null);
      return;
    }
    setExpandedId(requestId);
    setLoadingMatches(true);
    try {
      const response = await getMatches(requestId);
      setMatches(response.data.matches);
    } catch (err) {
      setMatches([]);
    } finally {
      setLoadingMatches(false);
    }
  };

  const handleStatusChange = async (applicationId, status, requestId) => {
    setMessage('');
    try {
      await updateApplicationStatus(applicationId, status);
      const response = await getMatches(requestId);
      setMatches(response.data.matches);
    } catch (err) {
      setMessage('Failed to update application.');
    }
  };

  const handleDelete = async (requestId) => {
    if (!window.confirm('Delete this request? This cannot be undone.')) return;
    try {
      await deleteRequest(requestId);
      setExpandedId(null);
      loadRequests();
    } catch (err) {
      setMessage('Failed to delete request.');
    }
  };

  const statusColors = {
    pending: { bg: '#3a3a1f', color: '#ffe066' },
    accepted: { bg: '#1f3a24', color: '#7fd97f' },
    rejected: { bg: '#3a1f1f', color: '#ff6b6b' },
  };

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3 style={{ fontSize: '1rem', marginBottom: '0.8rem' }}>My Requests</h3>

      {message && <p style={{ color: '#ff6b6b', fontSize: '0.9rem' }}>{message}</p>}

      {requests.length === 0 && <p style={{ color: '#999', fontSize: '0.9rem' }}>You haven't created any requests yet.</p>}

      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem' }}>
        {requests.map((req) => (
          <div
            key={req.id}
            style={{
              backgroundColor: '#1a1a1a',
              padding: '1rem',
              borderRadius: '6px',
              border: '1px solid #333',
            }}
          >
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
              <div>
                <h4 style={{ margin: '0 0 0.4rem 0', fontSize: '1rem' }}>{req.title}</h4>
                <p style={{ margin: 0, color: '#999', fontSize: '0.9rem' }}>{req.description}</p>
                <span
                  style={{
                    display: 'inline-block',
                    marginTop: '0.4rem',
                    fontSize: '0.75rem',
                    color: req.status === 'open' ? '#7fd97f' : '#999',
                  }}
                >
                  Status: {req.status}
                </span>
              </div>
              <div style={{ display: 'flex', gap: '0.5rem' }}>
                <button
                  onClick={() => toggleExpand(req.id)}
                  style={{
                    padding: '0.4rem 0.8rem',
                    backgroundColor: '#4a7dff',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: 'pointer',
                    fontSize: '0.8rem',
                  }}
                >
                  {expandedId === req.id ? 'Hide' : 'View'} Applicants
                </button>
                <button
                  onClick={() => handleDelete(req.id)}
                  style={{
                    padding: '0.4rem 0.8rem',
                    backgroundColor: 'transparent',
                    color: '#ff6b6b',
                    border: '1px solid #ff6b6b',
                    borderRadius: '4px',
                    cursor: 'pointer',
                    fontSize: '0.8rem',
                  }}
                >
                  Delete
                </button>
              </div>
            </div>

            {expandedId === req.id && (
              <div style={{ marginTop: '1rem', borderTop: '1px solid #333', paddingTop: '1rem' }}>
                {loadingMatches && <p style={{ color: '#999', fontSize: '0.85rem' }}>Loading applicants...</p>}

                {!loadingMatches && matches.length === 0 && (
                  <p style={{ color: '#999', fontSize: '0.85rem' }}>No applicants yet.</p>
                )}

                {!loadingMatches && matches.map((m) => {
                  const colors = statusColors[m.status] || statusColors.pending;
                  return (
                    <div
                      key={m.id}
                      style={{
                        display: 'flex',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        backgroundColor: '#242424',
                        padding: '0.7rem 1rem',
                        borderRadius: '6px',
                        marginBottom: '0.5rem',
                      }}
                    >
                      <div>
                        <div style={{ fontSize: '0.9rem' }}>{m.full_name || m.email}</div>
                        <div style={{ fontSize: '0.75rem', color: '#999' }}>{m.email}</div>
                      </div>

                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.7rem' }}>
                        <span
                          style={{
                            fontSize: '0.8rem',
                            color: m.match_percentage >= 50 ? '#7fd97f' : '#ffe066',
                            fontWeight: 'bold',
                          }}
                        >
                          {m.match_percentage}% match
                        </span>

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
                          {m.status}
                        </span>

                        {m.status === 'pending' && (
                          <>
                            <button
                              onClick={() => handleStatusChange(m.id, 'accepted', req.id)}
                              style={{
                                padding: '0.3rem 0.6rem',
                                backgroundColor: '#1f3a24',
                                color: '#7fd97f',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: 'pointer',
                                fontSize: '0.75rem',
                              }}
                            >
                              Accept
                            </button>
                            <button
                              onClick={() => handleStatusChange(m.id, 'rejected', req.id)}
                              style={{
                                padding: '0.3rem 0.6rem',
                                backgroundColor: '#3a1f1f',
                                color: '#ff6b6b',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: 'pointer',
                                fontSize: '0.75rem',
                              }}
                            >
                              Reject
                            </button>
                          </>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}

export default MyRequests;
