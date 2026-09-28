import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

function NavBar() {
  const { user } = useAuth();

  return (
    <nav
      style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: '1rem 1.5rem',
        backgroundColor: '#1a1a1a',
        borderBottom: '1px solid #333',
      }}
    >
      <Link to="/" style={{ color: '#e0e0e0', fontWeight: 'bold', fontSize: '1.1rem', textDecoration: 'none' }}>
        ICSVP
      </Link>

      {user ? (
        <Link to="/dashboard" style={{ color: '#4a7dff', textDecoration: 'none', fontSize: '0.9rem' }}>
          Dashboard
        </Link>
      ) : (
        <div style={{ display: 'flex', gap: '1rem' }}>
          <Link to="/login" style={{ color: '#4a7dff', textDecoration: 'none', fontSize: '0.9rem' }}>
            Log In
          </Link>
          <Link to="/register" style={{ color: '#4a7dff', textDecoration: 'none', fontSize: '0.9rem' }}>
            Register
          </Link>
        </div>
      )}
    </nav>
  );
}

export default NavBar;
