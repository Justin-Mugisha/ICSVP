import api from './api';

export function getProfile() {
  return api.get('/volunteers/profile');
}

export function updateProfile(data) {
  return api.put('/volunteers/profile', data);
}

export function getSkills() {
  return api.get('/volunteers/skills');
}

export function addSkill(skillId) {
  return api.post('/volunteers/skills', { skill_id: skillId });
}

export function removeSkill(skillId) {
  return api.delete('/volunteers/skills/' + skillId);
}

export function getOpenRequests() {
  return api.get('/requests');
}

export function getRequestDetail(id) {
  return api.get('/requests/' + id);
}

export function applyToRequest(id) {
  return api.post('/requests/' + id + '/apply');
}

export function getMyApplications() {
  return api.get('/volunteers/applications');
}
