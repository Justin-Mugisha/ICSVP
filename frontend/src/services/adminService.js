import api from './api';

export function getDashboardStats() {
  return api.get('/admin/dashboard');
}

export function getAllUsers() {
  return api.get('/admin/users');
}

export function getAllRequests() {
  return api.get('/admin/requests');
}

export function getAllApplications() {
  return api.get('/admin/applications');
}

export function getAllSkillsAdmin() {
  return api.get('/admin/skills');
}

export function createSkill(name) {
  return api.post('/admin/skills', { name });
}
