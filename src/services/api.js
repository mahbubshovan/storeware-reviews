import axios from 'axios';

// Use relative path for single domain deployment
const API_BASE_URL = '/backend/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000, // Increased to 30 seconds
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request tracking to prevent concurrent requests
const activeRequests = new Map();

// Add request interceptor for debugging and request deduplication
api.interceptors.request.use(
  (config) => {
    // Create a unique key for this request
    const requestKey = `${config.method?.toUpperCase()}_${config.url}_${JSON.stringify(config.params || {})}`;

    // Check if this exact request is already in progress
    if (activeRequests.has(requestKey)) {
      console.log('🔄 Duplicate request detected, using existing:', requestKey);
      return activeRequests.get(requestKey);
    }

    // Store this request
    activeRequests.set(requestKey, config);

    console.log('🚀 API Request:', config.method?.toUpperCase(), config.url, config.params);
    return config;
  },
  (error) => {
    console.error('❌ API Request Error:', error);
    return Promise.reject(error);
  }
);

// Add response interceptor for error handling
api.interceptors.response.use(
  (response) => {
    // Remove from active requests when completed
    const requestKey = `${response.config.method?.toUpperCase()}_${response.config.url}_${JSON.stringify(response.config.params || {})}`;
    activeRequests.delete(requestKey);

    console.log('✅ API Response:', response.config.url, response.status, 'Success');
    return response;
  },
  (error) => {
    // Remove from active requests when failed
    if (error.config) {
      const requestKey = `${error.config.method?.toUpperCase()}_${error.config.url}_${JSON.stringify(error.config.params || {})}`;
      activeRequests.delete(requestKey);
    }

    console.error('❌ API Response Error:', error.config?.url, error.response?.status, error.message);

    // Provide more specific error messages
    if (error.code === 'ECONNABORTED' && error.message.includes('timeout')) {
      throw new Error('Request timeout - server is taking too long to respond');
    } else if (error.response?.status === 404) {
      throw new Error(`API endpoint not found: ${error.config?.url}`);
    } else if (error.response?.status >= 500) {
      throw new Error(`Server error (${error.response?.status}): Please try again`);
    } else if (error.code === 'ECONNREFUSED') {
      throw new Error('Cannot connect to backend server. Please ensure it is running on port 8000.');
    } else if (error.code === 'NETWORK_ERROR') {
      throw new Error('Network error. Please check your internet connection.');
    } else if (error.response?.status === 429) {
      throw new Error('Too many requests. Please wait a moment and try again.');
    }

    return Promise.reject(error);
  }
);

// API endpoints
export const reviewsAPI = {
  getThisMonthReviews: (appName = null) => {
    const params = appName ? { app_name: appName, _t: Date.now(), _cache_bust: Math.random() } : { _t: Date.now(), _cache_bust: Math.random() };
    return api.get('/this-month-reviews.php', { params });
  },
  getLast30DaysReviews: (appName = null) => {
    const params = appName ? { app_name: appName, _t: Date.now(), _cache_bust: Math.random() } : { _t: Date.now(), _cache_bust: Math.random() };
    return api.get('/last-30-days-reviews.php', { params });
  },
  getAverageRating: (appName = null) => {
    const params = appName ? { app_name: appName, _t: Date.now(), _cache_bust: Math.random() } : { _t: Date.now(), _cache_bust: Math.random() };
    return api.get('/average-rating.php', { params });
  },
  getReviewDistribution: (appName = null) => {
    const params = appName ? { app_name: appName, _t: Date.now() } : { _t: Date.now() };
    return api.get('/review-distribution.php', { params });
  },
  getLatestReviews: (appName = null) => {
    const params = appName ? { app_name: appName, _t: Date.now(), _cache_bust: Math.random() } : { _t: Date.now(), _cache_bust: Math.random() };
    return api.get('/latest-reviews.php', { params });
  },
  getFreshReviews: (appName) => {
    const params = { app_name: appName, _t: Date.now() };
    return api.get('/fresh-reviews.php', { params });
  },
  getPaginatedReviews: (params = {}) => {
    const queryParams = { ...params, _t: Date.now() };
    return api.get('/reviews-paginated.php', { params: queryParams });
  },
  getAvailableApps: () => api.get('/available-apps.php', { params: { _t: Date.now() } }),
  scrapeApp: (appName) => api.post('/scrape-app.php', { app_name: appName }, { timeout: 60000 }),
  getAccessReviews: () => api.get('/access-reviews.php', { params: { date_range: '30_days', _t: Date.now(), _cache_bust: Math.random() } }),
  updateEarnedBy: (reviewId, earnedBy) => api.post('/update-earned-by.php', { review_id: reviewId, earned_by: earnedBy }),
  healthCheck: () => api.get('/health.php'),
};

export default api;
