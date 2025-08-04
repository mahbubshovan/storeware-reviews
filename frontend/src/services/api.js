import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// API endpoints
export const reviewsAPI = {
  getThisMonthReviews: (appName = null) => {
    const params = appName ? { app_name: appName } : {};
    return api.get('/this-month-reviews.php', { params });
  },
  getLast30DaysReviews: (appName = null) => {
    const params = appName ? { app_name: appName } : {};
    return api.get('/last-30-days-reviews.php', { params });
  },
  getAverageRating: (appName = null) => {
    const params = appName ? { app_name: appName } : {};
    return api.get('/average-rating.php', { params });
  },
  getReviewDistribution: (appName = null) => {
    const params = appName ? { app_name: appName } : {};
    return api.get('/review-distribution.php', { params });
  },
  getLatestReviews: (appName = null) => {
    const params = appName ? { app_name: appName } : {};
    return api.get('/latest-reviews.php', { params });
  },
  getAvailableApps: () => api.get('/available-apps.php'),
  scrapeApp: (appName) => api.post('/scrape-app.php', { app_name: appName }, { timeout: 60000 }),
};

export default api;
