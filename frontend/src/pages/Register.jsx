import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

function Register() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [role, setRole] = useState('volunteer');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const { register } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await register(email, password, role, name);
      navigate('/dashboard');
    } catch (err) {
      const message = err.response?.data?.error || 'Registration failed. Please try again.';
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
      <h1 style={{ fontSize: '1.6rem', textAlign: 'center' }}>Create an Account</h1>

      {error && (
        <p style={{ color: '#ff6b6b', backgroundColor: '#3a1f1f', padding: '0.6rem', borderRadius: '4px', fontSize: '0.9rem' }}>
          {error}
        </p>
      )}

      <form onSubmit={handleSubmit}>
        <div style={fieldWrapStyle}>
          <label style={labelStyle}>I am a:</label>
          <select value={role} onChange={(e) => setRole(e.target.value)} style={inputStyle}>
            <option value="volunteer">Volunteer</option>
            <option value="individual">Individual (need help)</option>
            <option value="organization">Organization</option>
          </select>
        </div>

        <div style={fieldWrapStyle}>
          <label style={labelStyle}>{role === 'organization' ? 'Organization Name' : 'Full Name'}</label>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
            style={inputStyle}
          />
        </div>

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
          <div style={{ position: 'relative' }}>
            <input
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={6}
              style={{ ...inputStyle, paddingRight: '3rem' }}
            />
            <button
              type="button"
              onClick={() => setShowPassword((prev) => !prev)}
              style={{
                position: 'absolute',
                right: '0.6rem',
                top: '50%',
                transform: 'translateY(-50%)',
                background: 'none',
                border: 'none',
                color: '#999',
                cursor: 'pointer',
                fontSize: '0.8rem',
                padding: 0,
              }}
            >
              {showPassword ? 'Hide' : 'Show'}
            </button>
          </div>
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
          {loading ? 'Creating account...' : 'Register'}
        </button>
      </form>

      <p style={{ marginTop: '1.2rem', textAlign: 'center', fontSize: '0.9rem' }}>
        Already have an account? <Link to="/login" style={{ color: '#4a7dff' }}>Log in</Link>
      </p>
    </div>
  );
}

export default Register;
