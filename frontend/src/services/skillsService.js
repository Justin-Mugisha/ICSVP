import api from './api';

export function getAllSkills() {
  return api.get('/skills');
}

export function createSkillAsRequester(name) {
  return api.post('/skills', { name: name });
}
