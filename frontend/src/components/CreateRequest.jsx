import { useState, useEffect } from 'react';
import { createRequest } from '../services/requesterService';
import { getAllSkills } from '../services/skillsService';

function CreateRequest({ onCreated }) {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [allSkills, setAllSkills] = useState([]);
  const [selectedSkillIds, setSelectedSkillIds] = useState([]);
  const [message, setMessage] = useState('');
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    getAllSkills().then((response) => setAllSkills(response.data.skills));
  }, []);

  const toggleSkill = (skillId) => {
    setSelectedSkillIds((prev) =>
      prev.includes(skillId) ? prev.filter((id) => id !== skillId) : [...prev, skillId]
    );
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');

    if (selectedSkillIds.length === 0) {
      setMessage('Select at least one required skill.');
      return;
    }

    setSaving(true);
    try {
      await createRequest({ title, description, skill_ids: selectedSkillIds });
      setTitle('');
      setDescription('');
      setSelectedSkillIds([]);
      setMessage('Request created successfully.');
      if (onCreated) onCreated();
    } catch (err) {
      const msg = err.response?.data?.error || 'Failed to create request.';
      setMessage(msg);
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

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3 style={{ fontSize: '1rem', marginBottom: '0.8rem' }}>Create a New Request</h3>

      {message && <p style={{ color: message.includes('success') ? '#7fd97f' : '#ff6b6b', fontSize: '0.9rem' }}>{message}</p>}

      <form onSubmit={handleSubmit}>
        <label style={{ fontSize: '0.85rem' }}>Title</label>
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          required
          style={inputStyle}
        />

        <label style={{ fontSize: '0.85rem' }}>Description</label>
        <textarea
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          rows={3}
          style={inputStyle}
        />

        <label style={{ fontSize: '0.85rem', display: 'block', marginBottom: '0.4rem' }}>Required Skills</label>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem', marginBottom: '1rem' }}>
          {allSkills.map((skill) => {
            const isSelected = selectedSkillIds.includes(skill.id);
            return (
              <button
                type="button"
                key={skill.id}
                onClick={() => toggleSkill(skill.id)}
                style={{
                  padding: '0.3rem 0.7rem',
                  borderRadius: '999px',
                  fontSize: '0.85rem',
                  border: isSelected ? '1px solid #4a7dff' : '1px solid #444',
                  backgroundColor: isSelected ? '#2a3a5a' : '#1a1a1a',
                  color: isSelected ? '#a8c4ff' : '#999',
                  cursor: 'pointer',
                }}
              >
                {skill.name}
              </button>
            );
          })}
        </div>

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
          {saving ? 'Creating...' : 'Create Request'}
        </button>
      </form>
    </div>
  );
}

export default CreateRequest;
