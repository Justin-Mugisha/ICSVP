import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await login(email, password);
      navigate('/dashboard');
    } catch (err) {
      const message = err.response?.data?.error || 'Login failed. Please try again.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  const labelStyle = { display: 'block', marginBottom: '0.4rem', fontSize: '0.9rem' };
  const fieldWrapStyle = { marginBottom: '1.2rem' };
  const inputStyle = {
    width: '100%',
    padding: '0.6rem',
    borderRadius: '4px',
    border: '1px solid #444',
    backgroundColor: '#2a2a2a',
    color: '#e0e0e0',
  };

  return (
    <div style={{ maxWidth: '400px', margin: '4rem auto', padding: '2rem', backgroundColor: '#242424', borderRadius: '8px' }}>
      <h1 style={{ fontSize: '1.6rem', textAlign: 'center' }}>Log In</h1>

      {error && (
        <p style={{ color: '#ff6b6b', backgroundColor: '#3a1f1f', padding: '0.6rem', borderRadius: '4px', fontSize: '0.9rem' }}>
          {error}
        </p>
      )}

      <form onSubmit={handleSubmit}>
        <div style={fieldWrapStyle}>
          <label style={labelStyle}>Email</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            style={inputStyle}
          />
        </div>

        <div style={fieldWrapStyle}>
          <label style={labelStyle}>Password</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            style={inputStyle}
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          style={{
            width: '100%',
            padding: '0.75rem',
            marginTop: '0.5rem',
            backgroundColor: loading ? '#555' : '#4a7dff',
            color: '#fff',
            border: 'none',
            borderRadius: '4px',
            fontWeight: 'bold',
          }}
        >
          {loading ? 'Logging in...' : 'Log In'}
        </button>
      </form>

      <p style={{ marginTop: '1.2rem', textAlign: 'center', fontSize: '0.9rem' }}>
        Don't have an account? <Link to="/register" style={{ color: '#4a7dff' }}>Register</Link>
      </p>
    </div>
  );
}

export default Login;
