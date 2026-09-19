import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import VolunteerProfile from '../components/VolunteerProfile';
import VolunteerSkills from '../components/VolunteerSkills';
import VolunteerRequests from '../components/VolunteerRequests';
import RequesterProfile from '../components/RequesterProfile';
import CreateRequest from '../components/CreateRequest';
import MyRequests from '../components/MyRequests';

function Dashboard() {
  const { user, loading, logout } = useAuth();
  const navigate = useNavigate();
  const [refreshKey, setRefreshKey] = useState(0);

  useEffect(() => {
    if (!loading && !user) {
      navigate('/login');
    }
  }, [loading, user, navigate]);

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const handleRequestCreated = () => {
    setRefreshKey((prev) => prev + 1);
  };

  if (loading || !user) {
    return <p style={{ padding: '2rem' }}>Loading...</p>;
  }

  return (
    <div style={{ maxWidth: '700px', margin: '3rem auto', padding: '0 1.5rem', fontFamily: 'sans-serif' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
        <h1 style={{ fontSize: '1.6rem' }}>Welcome, {user.email}</h1>
        <button
          onClick={handleLogout}
          style={{
            padding: '0.5rem 1rem',
            backgroundColor: '#3a1f1f',
            color: '#ff6b6b',
            border: '1px solid #ff6b6b',
            borderRadius: '4px',
            cursor: 'pointer',
          }}
        >
          Log Out
        </button>
      </div>

      <p style={{ color: '#999', marginBottom: '2rem' }}>
        Role: <strong style={{ color: '#e0e0e0' }}>{user.role}</strong>
      </p>

      {user.role === 'volunteer' && (
        <div style={{ backgroundColor: '#242424', padding: '1.5rem', borderRadius: '8px' }}>
          <VolunteerProfile />
          <VolunteerSkills />
          <VolunteerRequests />
        </div>
      )}

      {(user.role === 'individual' || user.role === 'organization') && (
        <div style={{ backgroundColor: '#242424', padding: '1.5rem', borderRadius: '8px' }}>
          <RequesterProfile />
          <CreateRequest onCreated={handleRequestCreated} />
          <MyRequests refreshKey={refreshKey} />
        </div>
      )}

      {user.role === 'admin' && (
        <div style={{ backgroundColor: '#242424', padding: '1.5rem', borderRadius: '8px' }}>
          <h2 style={{ fontSize: '1.2rem' }}>Admin Dashboard</h2>
          <p style={{ color: '#999' }}>System stats and management coming next.</p>
        </div>
      )}
    </div>
  );
}

export default Dashboard;
