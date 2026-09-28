import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

function Home() {
  const { user } = useAuth();

  return (
    <div style={{ maxWidth: '600px', margin: '5rem auto', padding: '0 1.5rem', textAlign: 'center', fontFamily: 'sans-serif' }}>
      <h1 style={{ fontSize: '2rem', marginBottom: '1rem' }}>ICSVP</h1>
      <p style={{ color: '#999', marginBottom: '2rem', lineHeight: 1.5 }}>
        Inclusive Community Support and Volunteer Coordination Platform.
        Connecting volunteers with organizations and individuals who need help.
      </p>

      {user ? (
        <Link
          to="/dashboard"
          style={{
            display: 'inline-block',
            padding: '0.75rem 1.5rem',
            backgroundColor: '#4a7dff',
            color: '#fff',
            borderRadius: '4px',
            textDecoration: 'none',
            fontWeight: 'bold',
          }}
        >
          Go to Dashboard
        </Link>
      ) : (
        <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center' }}>
          <Link
            to="/login"
            style={{
              padding: '0.75rem 1.5rem',
              backgroundColor: 'transparent',
              color: '#4a7dff',
              border: '1px solid #4a7dff',
              borderRadius: '4px',
              textDecoration: 'none',
              fontWeight: 'bold',
            }}
          >
            Log In
          </Link>
          <Link
            to="/register"
            style={{
              padding: '0.75rem 1.5rem',
              backgroundColor: '#4a7dff',
              color: '#fff',
              borderRadius: '4px',
              textDecoration: 'none',
              fontWeight: 'bold',
            }}
          >
            Get Started
          </Link>
        </div>
      )}
    </div>
  );
}

export default Home;
