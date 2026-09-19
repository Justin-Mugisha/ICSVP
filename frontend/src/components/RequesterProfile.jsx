import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { getProfile, updateProfile } from '../services/requesterService';

function RequesterProfile() {
  const { user } = useAuth();
  const isOrg = user.role === 'organization';

  const [profile, setProfile] = useState(null);
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [location, setLocation] = useState('');
  const [message, setMessage] = useState('');
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    getProfile().then((response) => {
      const p = response.data.profile;
      setProfile(p);
      setName(isOrg ? (p.org_name || '') : (p.full_name || ''));
      setDescription(p.description || '');
      setLocation(p.location || '');
    });
  }, [isOrg]);

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    const payload = isOrg
      ? { org_name: name, description, location }
      : { full_name: name, location };

    try {
      const response = await updateProfile(payload);
      setProfile(response.data.profile);
      setMessage('Profile updated successfully.');
    } catch (err) {
      setMessage('Failed to update profile.');
    } finally {
      setSaving(false);
    }
  };

  const inputStyle = {
    width: '100%',
    padding: '0.5rem',
    borderRadius: '4px',
    border: '1px solid #444',
    backgroundColor: '#1a1a1a',
    color: '#e0e0e0',
    marginBottom: '0.8rem',
  };

  if (!profile) {
    return <p>Loading profile...</p>;
  }

  return (
    <div>
      <h3 style={{ fontSize: '1rem', marginBottom: '0.8rem' }}>My Profile</h3>

      {message && <p style={{ color: '#7fd97f', fontSize: '0.9rem' }}>{message}</p>}

      <form onSubmit={handleSave}>
        <label style={{ fontSize: '0.85rem' }}>{isOrg ? 'Organization Name' : 'Full Name'}</label>
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          style={inputStyle}
        />

        {isOrg && (
          <>
            <label style={{ fontSize: '0.85rem' }}>Description</label>
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              rows={3}
              style={inputStyle}
            />
          </>
        )}

        <label style={{ fontSize: '0.85rem' }}>Location</label>
        <input
          type="text"
          value={location}
          onChange={(e) => setLocation(e.target.value)}
          style={inputStyle}
        />

        <button
          type="submit"
          disabled={saving}
          style={{
            padding: '0.5rem 1.2rem',
            backgroundColor: '#4a7dff',
            color: '#fff',
            border: 'none',
            borderRadius: '4px',
            cursor: 'pointer',
          }}
        >
          {saving ? 'Saving...' : 'Save Profile'}
        </button>
      </form>
    </div>
  );
}

export default RequesterProfile;
