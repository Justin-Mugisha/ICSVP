import api from './api';

export function getProfile() {
  return api.get('/requesters/profile');
}

export function updateProfile(data) {
  return api.put('/requesters/profile', data);
}

export function createRequest(data) {
  return api.post('/requesters/requests', data);
}

export function getMyRequests() {
  return api.get('/requesters/requests');
}

export function updateRequest(id, data) {
  return api.put('/requesters/requests/' + id, data);
}

export function deleteRequest(id) {
  return api.delete('/requesters/requests/' + id);
}

export function getApplicants(id) {
  return api.get('/requesters/requests/' + id + '/applications');
}

export function getMatches(id) {
  return api.get('/requesters/requests/' + id + '/matches');
}

export function updateApplicationStatus(applicationId, status) {
  return api.put('/requesters/applications/' + applicationId + '/status', { status });
}
