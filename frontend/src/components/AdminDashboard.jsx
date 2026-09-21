import { useState, useEffect } from 'react';
import {
  getDashboardStats,
  getAllUsers,
  getAllRequests,
  getAllApplications,
  getAllSkillsAdmin,
  createSkill,
} from '../services/adminService';

function AdminDashboard() {
  const [activeTab, setActiveTab] = useState('stats');
  const [stats, setStats] = useState(null);
  const [users, setUsers] = useState([]);
  const [requests, setRequests] = useState([]);
  const [applications, setApplications] = useState([]);
  const [skills, setSkills] = useState([]);
  const [newSkillName, setNewSkillName] = useState('');
  const [message, setMessage] = useState('');

  useEffect(() => {
    getDashboardStats().then((res) => setStats(res.data.stats));
  }, []);

  const loadTabData = (tab) => {
    setActiveTab(tab);
    if (tab === 'users' && users.length === 0) {
      getAllUsers().then((res) => setUsers(res.data.users));
    }
    if (tab === 'requests' && requests.length === 0) {
      getAllRequests().then((res) => setRequests(res.data.requests));
    }
    if (tab === 'applications' && applications.length === 0) {
      getAllApplications().then((res) => setApplications(res.data.applications));
    }
    if (tab === 'skills') {
      getAllSkillsAdmin().then((res) => setSkills(res.data.skills));
    }
  };

  const handleAddSkill = async (e) => {
    e.preventDefault();
    setMessage('');
    try {
      await createSkill(newSkillName);
      setNewSkillName('');
      const res = await getAllSkillsAdmin();
      setSkills(res.data.skills);
      setMessage('Skill added.');
    } catch (err) {
      const msg = err.response?.data?.error || 'Failed to add skill.';
      setMessage(msg);
    }
  };

  const tabStyle = (tab) => ({
    padding: '0.5rem 1rem',
    backgroundColor: activeTab === tab ? '#4a7dff' : 'transparent',
    color: activeTab === tab ? '#fff' : '#999',
    border: 'none',
    borderRadius: '4px',
    cursor: 'pointer',
    fontSize: '0.85rem',
  });

  const tableStyle = { width: '100%', borderCollapse: 'collapse', fontSize: '0.85rem' };
  const thStyle = { textAlign: 'left', padding: '0.5rem', borderBottom: '1px solid #333', color: '#999' };
  const tdStyle = { padding: '0.5rem', borderBottom: '1px solid #2a2a2a' };

  return (
    <div>
      <div style={{ display: 'flex', gap: '0.5rem', marginBottom: '1.5rem', flexWrap: 'wrap' }}>
        <button style={tabStyle('stats')} onClick={() => loadTabData('stats')}>Stats</button>
        <button style={tabStyle('users')} onClick={() => loadTabData('users')}>Users</button>
        <button style={tabStyle('requests')} onClick={() => loadTabData('requests')}>Requests</button>
        <button style={tabStyle('applications')} onClick={() => loadTabData('applications')}>Applications</button>
        <button style={tabStyle('skills')} onClick={() => loadTabData('skills')}>Skills</button>
      </div>

      {activeTab === 'stats' && stats && (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
          {Object.entries(stats).map(([key, value]) => (
            <div key={key} style={{ backgroundColor: '#1a1a1a', padding: '1rem', borderRadius: '6px' }}>
              <div style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>{value}</div>
              <div style={{ fontSize: '0.8rem', color: '#999', textTransform: 'capitalize' }}>
                {key.replace(/_/g, ' ')}
              </div>
            </div>
          ))}
        </div>
      )}

      {activeTab === 'users' && (
        <table style={tableStyle}>
          <thead>
            <tr>
              <th style={thStyle}>Email</th>
              <th style={thStyle}>Role</th>
              <th style={thStyle}>Active</th>
              <th style={thStyle}>Joined</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id}>
                <td style={tdStyle}>{u.email}</td>
                <td style={tdStyle}>{u.role}</td>
                <td style={tdStyle}>{u.is_active ? 'Yes' : 'No'}</td>
                <td style={tdStyle}>{u.created_at}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {activeTab === 'requests' && (
        <table style={tableStyle}>
          <thead>
            <tr>
              <th style={thStyle}>Title</th>
              <th style={thStyle}>Requester</th>
              <th style={thStyle}>Status</th>
              <th style={thStyle}>Created</th>
            </tr>
          </thead>
          <tbody>
            {requests.map((r) => (
              <tr key={r.id}>
                <td style={tdStyle}>{r.title}</td>
                <td style={tdStyle}>{r.requester_name || '—'}</td>
                <td style={tdStyle}>{r.status}</td>
                <td style={tdStyle}>{r.created_at}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {activeTab === 'applications' && (
        <table style={tableStyle}>
          <thead>
            <tr>
              <th style={thStyle}>Request</th>
              <th style={thStyle}>Volunteer</th>
              <th style={thStyle}>Status</th>
              <th style={thStyle}>Applied</th>
            </tr>
          </thead>
          <tbody>
            {applications.map((a) => (
              <tr key={a.id}>
                <td style={tdStyle}>{a.request_title}</td>
                <td style={tdStyle}>{a.volunteer_email}</td>
                <td style={tdStyle}>{a.status}</td>
                <td style={tdStyle}>{a.created_at}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {activeTab === 'skills' && (
        <div>
          {message && <p style={{ color: '#7fd97f', fontSize: '0.9rem' }}>{message}</p>}

          <form onSubmit={handleAddSkill} style={{ display: 'flex', gap: '0.5rem', marginBottom: '1rem' }}>
            <input
              type="text"
              value={newSkillName}
              onChange={(e) => setNewSkillName(e.target.value)}
              placeholder="New skill name"
              required
              style={{
                flex: 1,
                padding: '0.5rem',
                borderRadius: '4px',
                border: '1px solid #444',
                backgroundColor: '#1a1a1a',
                color: '#e0e0e0',
              }}
            />
            <button
              type="submit"
              style={{
                padding: '0.5rem 1rem',
                backgroundColor: '#4a7dff',
                color: '#fff',
                border: 'none',
                borderRadius: '4px',
                cursor: 'pointer',
              }}
            >
              Add Skill
            </button>
          </form>

          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem' }}>
            {skills.map((s) => (
              <span
                key={s.id}
                style={{
                  backgroundColor: '#1a1a1a',
                  padding: '0.3rem 0.7rem',
                  borderRadius: '999px',
                  fontSize: '0.85rem',
                  color: '#999',
                }}
              >
                {s.name}
              </span>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminDashboard;
