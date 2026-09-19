import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost/icsvp/backend/public/api',
  withCredentials: true,
});

export default api;
