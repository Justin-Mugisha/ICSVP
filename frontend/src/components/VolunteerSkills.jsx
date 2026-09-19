import { useState, useEffect } from 'react';
import { getSkills, addSkill, removeSkill } from '../services/volunteerService';
import { getAllSkills } from '../services/skillsService';

function VolunteerSkills() {
  const [mySkills, setMySkills] = useState([]);
  const [allSkills, setAllSkills] = useState([]);
  const [selectedSkillId, setSelectedSkillId] = useState('');
  const [message, setMessage] = useState('');

  const loadData = () => {
    getSkills().then((response) => setMySkills(response.data.skills));
    getAllSkills().then((response) => setAllSkills(response.data.skills));
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleAdd = async (e) => {
    e.preventDefault();
    if (!selectedSkillId) return;

    setMessage('');
    try {
      await addSkill(selectedSkillId);
      setSelectedSkillId('');
      loadData();
    } catch (err) {
      const msg = err.response?.data?.error || 'Failed to add skill.';
      setMessage(msg);
    }
  };

  const handleRemove = async (skillId) => {
    setMessage('');
    try {
      await removeSkill(skillId);
      loadData();
    } catch (err) {
      setMessage('Failed to remove skill.');
    }
  };

  const mySkillIds = mySkills.map((s) => s.id);
  const availableSkills = allSkills.filter((s) => !mySkillIds.includes(s.id));

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3 style={{ fontSize: '1rem', marginBottom: '0.8rem' }}>My Skills</h3>

      {message && <p style={{ color: '#ff6b6b', fontSize: '0.9rem' }}>{message}</p>}

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem', marginBottom: '1rem' }}>
        {mySkills.length === 0 && <p style={{ color: '#999', fontSize: '0.9rem' }}>No skills added yet.</p>}
        {mySkills.map((skill) => (
          <span
            key={skill.id}
            style={{
              backgroundColor: '#2a3a5a',
              color: '#a8c4ff',
              padding: '0.3rem 0.7rem',
              borderRadius: '999px',
              fontSize: '0.85rem',
              display: 'flex',
              alignItems: 'center',
              gap: '0.4rem',
            }}
          >
            {skill.name}
            <button
              onClick={() => handleRemove(skill.id)}
              style={{
                background: 'none',
                border: 'none',
                color: '#a8c4ff',
                cursor: 'pointer',
                fontWeight: 'bold',
                padding: 0,
              }}
            >
              ×
            </button>
          </span>
        ))}
      </div>

      <form onSubmit={handleAdd} style={{ display: 'flex', gap: '0.5rem' }}>
        <select
          value={selectedSkillId}
          onChange={(e) => setSelectedSkillId(e.target.value)}
          style={{
            flex: 1,
            padding: '0.5rem',
            borderRadius: '4px',
            border: '1px solid #444',
            backgroundColor: '#1a1a1a',
            color: '#e0e0e0',
          }}
        >
          <option value="">Select a skill to add...</option>
          {availableSkills.map((skill) => (
            <option key={skill.id} value={skill.id}>{skill.name}</option>
          ))}
        </select>
        <button
          type="submit"
          disabled={!selectedSkillId}
          style={{
            padding: '0.5rem 1rem',
            backgroundColor: '#4a7dff',
            color: '#fff',
            border: 'none',
            borderRadius: '4px',
            cursor: 'pointer',
          }}
        >
          Add
        </button>
      </form>
    </div>
  );
}

export default VolunteerSkills;
