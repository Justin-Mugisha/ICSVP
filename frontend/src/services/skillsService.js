import api from './api';

export function getAllSkills() {
  return api.get('/skills');
}
