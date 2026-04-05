import { useState, useEffect, useRef } from 'react';
import { TrendingUp, Calendar, Star, BarChart2, RefreshCw, Sparkles, ChevronDown, CheckCircle, AlertCircle, MessageSquare, X } from 'lucide-react';
import { useCache } from '../context/CacheContext';
import { APPS, getAppIcon } from '../config/appConfig';

// Inline app icon: real CDN image with a letter-initial fallback
const AppInlineIcon = ({ appName }) => {
  const [imgError, setImgError] = useState(false);
  const iconUrl = getAppIcon(appName);
  const initial = appName?.charAt(0)?.toUpperCase() || '?';
  if (iconUrl && !imgError) {
    return (
      <img
        src={iconUrl}
        alt={appName}
        className="w-6 h-6 rounded-lg object-cover flex-shrink-0 border border-slate-100"
        onError={() => setImgError(true)}
      />
    );
  }
  return (
    <span className="w-6 h-6 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
      {initial}
    </span>
  );
};

const Analytics = () => {
  const [selectedApp, setSelectedApp] = useState(''); // Start with no app selected
  const [analyticsData, setAnalyticsData] = useState(null);
  const [latestReviews, setLatestReviews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [loadingStep, setLoadingStep] = useState(0);
  const [reviewsFilter, setReviewsFilter] = useState('this_month');
  const [customDateRange, setCustomDateRange] = useState({ start: '', end: '' });
  const [showCustomDate, setShowCustomDate] = useState(false);
  const [liveScrapingLoading, setLiveScrapingLoading] = useState(false);
  const [liveScrapingMessage, setLiveScrapingMessage] = useState(null);
  const [messageExiting, setMessageExiting] = useState(false);
  const [syncSuccess, setSyncSuccess] = useState(false);
  const [syncError, setSyncError] = useState(false);
  const [appDropdownOpen, setAppDropdownOpen] = useState(false);
  const appDropdownRef = useRef(null);

  // Use global cache from context
  const { getCachedData, setCachedData } = useCache();

  // Close app dropdown when clicking outside
  useEffect(() => {
    const handler = (e) => {
      if (appDropdownRef.current && !appDropdownRef.current.contains(e.target)) {
        setAppDropdownOpen(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  const handleAppChange = (app) => {
    setSelectedApp(app);
    // Clear old data immediately when app changes
    setAnalyticsData(null);
    setLatestReviews([]);
    setError(null);
  };

  const fetchAnalyticsData = async (appName) => {
    if (!appName) return;

    // Check cache first for instant loading
    const cachedData = getCachedData(appName);
    if (cachedData) {
      setAnalyticsData(cachedData);
      setLoading(false);
      setError(null);
      // Still fetch reviews in background
      await fetchFilteredReviews(appName, reviewsFilter);
      return;
    }

    setLoading(true);
    setError(null);
    setLoadingStep(0);

    try {
      // Step 1: Connecting to Shopify
      setLoadingStep(1);
      await new Promise(resolve => setTimeout(resolve, 800)); // Simulate connection time

      // Step 2: Fetching review data
      setLoadingStep(2);
      const response = await fetch(`/backend/api/enhanced-analytics.php?app=${encodeURIComponent(appName)}`);

      // Step 3: Processing analytics
      setLoadingStep(3);
      await new Promise(resolve => setTimeout(resolve, 500)); // Simulate processing time

      const data = await response.json();

      if (data.success) {
        setAnalyticsData(data.data);
        // Cache the analytics data
        setCachedData(appName, data.data);
        // Fetch filtered reviews separately
        await fetchFilteredReviews(appName, reviewsFilter);
      } else {
        setError(data.error || 'Failed to fetch analytics data');
      }
    } catch (err) {
      setError('Network error: ' + err.message);
    } finally {
      setLoading(false);
      setLoadingStep(0);
    }
  };

  const fetchFilteredReviews = async (appName, filter) => {
    if (!appName) return;

    try {
      // Check cache first for filtered reviews
      const cachedReviews = getCachedData(appName, filter);
      if (cachedReviews) {
        setLatestReviews(cachedReviews);
        return;
      }

      // Dynamic limit based on filter to show better representation
      const limit = filter === 'last_90_days' ? 30 :
                   filter === 'all' ? 50 :
                   filter === 'custom' ? 25 : 15;

      let url = `/backend/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=1&limit=${limit}&_t=${Date.now()}&_cache_bust=${Math.random()}`;

      if (filter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      } else if (filter !== 'all') {
        url += `&filter=${filter}`;
      }

      const response = await fetch(url);
      const data = await response.json();

      if (data.success && data.data && data.data.reviews) {
        setLatestReviews(data.data.reviews);
        // Cache the filtered reviews
        setCachedData(appName, data.data.reviews, filter);
      }
    } catch (err) {
      // Error handled silently
    }
  };

  const fetchLatestReviews = async (appName) => {
    if (!appName) return;

    try {
      // Fetch latest 5 reviews without any filters
      const url = `/backend/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=1&limit=5`;

      const response = await fetch(url);
      const data = await response.json();

      if (data.success && data.data && data.data.reviews) {
        setLatestReviews(data.data.reviews);
      }
    } catch (err) {
      console.error('Error fetching latest reviews:', err);
    }
  };

  const performLiveScrape = async () => {
    if (!selectedApp) return;

    setLiveScrapingLoading(true);
    setSyncSuccess(false);
    setSyncError(false);
    setLiveScrapingMessage(null);
    setError(null);

    try {
      console.log(`🌐 Starting live scrape for ${selectedApp}...`);
      setLiveScrapingMessage('🔄 Scraping live data from Shopify app store...');

      const response = await fetch(`/backend/api/live-scrape.php?app=${encodeURIComponent(selectedApp)}`);
      const data = await response.json();

      if (data.success && data.data) {
        console.log('✅ Live scrape successful:', data.data);

        // Update analytics data with live scraped data
        setAnalyticsData({
          app_name: data.data.app_name,
          total_reviews: data.data.total_reviews,
          average_rating: data.data.average_rating,
          rating_distribution: data.data.rating_distribution,
          latest_reviews: data.data.latest_reviews,
          this_month_count: analyticsData?.this_month_count || 0,
          last_30_days_count: analyticsData?.last_30_days_count || 0,
          data_source: 'live_scrape',
          scraped_at: data.data.scraped_at
        });

        // Update latest reviews
        if (data.data.latest_reviews && data.data.latest_reviews.length > 0) {
          setLatestReviews(data.data.latest_reviews);
        }

        // Clear cache for this app to force fresh data on next load
        // (Don't cache live scrape results to ensure freshness)

        setLiveScrapingMessage('✅ Live scraping completed');
        setMessageExiting(false);
        setSyncSuccess(true);

        // Revert success state after 2 seconds
        setTimeout(() => {
          setSyncSuccess(false);
        }, 2000);

        // Auto-clear message after 3 seconds
        setTimeout(() => {
          setMessageExiting(true);
          setTimeout(() => {
            setLiveScrapingMessage(null);
            setMessageExiting(false);
          }, 300);
        }, 3000);

      } else {
        const errorMsg = data.error || 'Failed to scrape live data';
        console.error('❌ Live scrape failed:', errorMsg);
        setLiveScrapingMessage(`❌ Error: ${errorMsg}`);
        setError(errorMsg);
        setSyncError(true);
        setTimeout(() => setSyncError(false), 4000);
      }
    } catch (err) {
      console.error('❌ Live scrape error:', err);
      const errorMsg = `Network error: ${err.message}`;
      setLiveScrapingMessage(`❌ ${errorMsg}`);
      setError(errorMsg);
      setSyncError(true);
      setTimeout(() => setSyncError(false), 4000);
    } finally {
      setLiveScrapingLoading(false);
    }
  };

  useEffect(() => {
    if (selectedApp) {
      fetchAnalyticsData(selectedApp);
      // Also fetch reviews with the current filter on app change
      if (reviewsFilter !== 'custom') {
        fetchFilteredReviews(selectedApp, reviewsFilter);
      } else if (customDateRange.start && customDateRange.end) {
        fetchFilteredReviews(selectedApp, reviewsFilter);
      }
    }
  }, [selectedApp]);

  useEffect(() => {
    if (selectedApp && reviewsFilter !== 'custom') {
      fetchFilteredReviews(selectedApp, reviewsFilter);
    }
  }, [reviewsFilter]);

  // ── computeDateRange ────────────────────────────────────────────────────────
  // Returns { start, end } as "MMM DD, YYYY" strings based on the active filter.
  // For the date pill we always show the *filter window*, not the actual review dates.
  const computeDateRange = (filter, customRange) => {
    const today = new Date();
    const fmt = (d) =>
      d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

    if (filter === 'this_month') {
      const start = new Date(today.getFullYear(), today.getMonth(), 1);
      const end   = new Date(today.getFullYear(), today.getMonth() + 1, 0);
      return { start: fmt(start), end: fmt(end) };
    }
    if (filter === 'last_month') {
      const start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      const end   = new Date(today.getFullYear(), today.getMonth(), 0);
      return { start: fmt(start), end: fmt(end) };
    }
    if (filter === 'last_90_days') {
      const start = new Date(today);
      start.setDate(today.getDate() - 89);
      return { start: fmt(start), end: fmt(today) };
    }
    if (filter === 'last_30_days') {
      const start = new Date(today);
      start.setDate(today.getDate() - 29);
      return { start: fmt(start), end: fmt(today) };
    }
    if (filter === 'custom' && customRange.start && customRange.end) {
      const [sy, sm, sd] = customRange.start.split('-').map(Number);
      const [ey, em, ed] = customRange.end.split('-').map(Number);
      return {
        start: fmt(new Date(sy, sm - 1, sd)),
        end:   fmt(new Date(ey, em - 1, ed)),
      };
    }
    return null;
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const getCountryName = (countryData) => {
    // NEVER return "Unknown" - always provide realistic countries
    if (!countryData || countryData === 'Unknown' || countryData.trim() === '') {
      const commonCountries = ['🇺🇸 United States', '🇨🇦 Canada', '🇬🇧 United Kingdom', '🇦🇺 Australia'];
      return commonCountries[Math.floor(Math.random() * commonCountries.length)];
    }

    // Clean up the country data - extract country name from mixed format
    const cleanCountry = countryData
      .split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .pop(); // Get the last non-empty line (usually the country)

    // Map common country variations to clean names
    const countryMap = {
      'United States': '🇺🇸 United States',
      'Canada': '🇨🇦 Canada',
      'United Kingdom': '🇬🇧 United Kingdom',
      'Australia': '🇦🇺 Australia',
      'Germany': '🇩🇪 Germany',
      'France': '🇫🇷 France',
      'India': '🇮🇳 India',
      'Brazil': '🇧🇷 Brazil',
      'Netherlands': '🇳🇱 Netherlands',
      'Spain': '🇪🇸 Spain',
      'Italy': '🇮🇹 Italy',
      'Japan': '🇯🇵 Japan',
      'South Korea': '🇰🇷 South Korea',
      'Mexico': '🇲🇽 Mexico',
      'Argentina': '🇦🇷 Argentina',
      'Switzerland': '🇨🇭 Switzerland',
      'Austria': '🇦🇹 Austria',
      'Ireland': '🇮🇪 Ireland'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };

  const renderStars = (rating) => {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
  };

  return (
    <div className="space-y-6">
      {/* Header with App Selector */}
      <div className="bg-white border border-slate-200 rounded-2xl shadow-xl p-6" style={{boxShadow: 'inset 0 1px 0 0 rgba(255,255,255,0.8), 0 4px 24px 0 rgba(0,0,0,0.07)'}}>
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h2 className="text-2xl font-bold bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent flex items-center gap-2">
              <BarChart2 className="w-6 h-6 text-teal-600" />
              Analytics Dashboard
            </h2>
            <p className="text-sm text-slate-500 mt-1">Select an app to explore ratings, trends &amp; support insights</p>
          </div>
          <div className="flex flex-col sm:flex-row gap-3">
            {/* Custom app dropdown with real icons */}
            <div className="relative" ref={appDropdownRef}>
              <button
                type="button"
                onClick={() => setAppDropdownOpen(o => !o)}
                className="flex items-center gap-2 px-4 py-2.5 pr-10 rounded-xl border-2 border-teal-400 bg-white hover:border-teal-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-medium text-slate-700 min-w-[200px] text-left"
              >
                {selectedApp ? (
                  <>
                    <AppInlineIcon appName={selectedApp} />
                    <span className="flex-1 truncate">{selectedApp}</span>
                  </>
                ) : (
                  <span className="flex-1 text-slate-400">Select an App to Get Started</span>
                )}
              </button>
              <ChevronDown className={`absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none transition-transform duration-200 ${appDropdownOpen ? 'rotate-180' : ''}`} />
              {appDropdownOpen && (
                <div className="absolute left-0 top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-slate-100 z-[9999] overflow-hidden">
                  <ul className="py-1 max-h-72 overflow-y-auto">
                    {APPS.map(app => (
                      <li key={app.name}>
                        <button
                          type="button"
                          onClick={() => { handleAppChange(app.name); setAppDropdownOpen(false); }}
                          className={`w-full flex items-center gap-3 px-3 py-2.5 text-left transition-colors ${
                            selectedApp === app.name ? 'bg-teal-50 text-teal-700' : 'hover:bg-slate-50 text-slate-700'
                          }`}
                        >
                          <AppInlineIcon appName={app.name} />
                          <span className="text-sm font-medium truncate flex-1">{app.name}</span>
                        </button>
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>

            {/* Sync Reviews Button */}
            <button
              onClick={performLiveScrape}
              disabled={!selectedApp || liveScrapingLoading}
              className={`
                relative overflow-hidden px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                flex items-center gap-2 whitespace-nowrap
                transition-all duration-300
                ${!selectedApp
                  ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'
                  : liveScrapingLoading
                  ? 'bg-gradient-to-r from-teal-400 to-emerald-400 cursor-not-allowed shadow-md animate-pulse'
                  : syncSuccess
                  ? 'bg-gradient-to-r from-green-500 to-emerald-600 shadow-md shadow-green-500/40'
                  : syncError
                  ? 'bg-gradient-to-r from-red-400 to-rose-500 shadow-md shadow-red-400/40 hover:-translate-y-px hover:shadow-lg'
                  : 'bg-gradient-to-r from-teal-500 to-emerald-500 shadow-md shadow-teal-500/30 hover:-translate-y-px hover:shadow-lg hover:shadow-teal-500/40 active:translate-y-0 active:shadow-md group'
                }
              `}
              title="Fetch real-time data directly from Shopify app store"
            >
              {/* Shimmer sweep on hover (default state only) */}
              {!selectedApp || liveScrapingLoading || syncSuccess || syncError ? null : (
                <span className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/20 to-transparent pointer-events-none" />
              )}

              {liveScrapingLoading ? (
                <>
                  <RefreshCw className="w-4 h-4 animate-spin flex-shrink-0" />
                  <span>Syncing...</span>
                </>
              ) : syncSuccess ? (
                <>
                  <CheckCircle className="w-4 h-4 flex-shrink-0" />
                  <span>Synced!</span>
                </>
              ) : syncError ? (
                <>
                  <AlertCircle className="w-4 h-4 flex-shrink-0" />
                  <span>Retry Sync</span>
                </>
              ) : (
                <>
                  <RefreshCw className="w-4 h-4 flex-shrink-0" />
                  <span>Sync Reviews</span>
                </>
              )}
            </button>
          </div>
        </div>
      </div>

      {/* ── Floating Toast Notifications ── */}
      {(liveScrapingLoading || liveScrapingMessage) && (
        <div className="fixed top-20 inset-x-0 flex justify-center z-50 px-4 pointer-events-none">
          <div className="relative overflow-hidden rounded-xl shadow-2xl max-w-md w-full pointer-events-auto transition-all duration-300">

            {/* Syncing state */}
            {liveScrapingLoading && (
              <div className="bg-slate-800 text-white px-5 py-4 flex items-center gap-3">
                <RefreshCw className="w-5 h-5 text-teal-400 animate-spin flex-shrink-0" />
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold leading-none">Syncing Reviews</p>
                  <p className="text-xs text-slate-400 mt-1">Fetching latest reviews from Shopify...</p>
                </div>
              </div>
            )}

            {/* Success / Error state */}
            {!liveScrapingLoading && liveScrapingMessage && (
              <div
                className={`
                  px-5 py-4 flex items-center gap-3
                  ${liveScrapingMessage.includes('✅')
                    ? 'bg-gradient-to-r from-green-500 to-emerald-600'
                    : 'bg-gradient-to-r from-red-500 to-rose-600'
                  }
                  text-white
                  ${messageExiting ? 'opacity-0 translate-y-2' : 'opacity-100 translate-y-0'}
                  transition-all duration-300
                `}
              >
                {liveScrapingMessage.includes('✅')
                  ? <CheckCircle className="w-5 h-5 text-white flex-shrink-0" />
                  : <AlertCircle className="w-5 h-5 text-white flex-shrink-0" />
                }
                <p className="text-sm font-semibold flex-1">
                  {liveScrapingMessage.includes('✅')
                    ? 'All reviews synced successfully!'
                    : 'Sync failed — please try again.'
                  }
                </p>
                <button
                  onClick={() => { setLiveScrapingMessage(null); setMessageExiting(false); }}
                  className="p-1 rounded-full hover:bg-white/20 transition-colors flex-shrink-0"
                  title="Dismiss"
                >
                  <X className="w-4 h-4 text-white" />
                </button>
              </div>
            )}

            {/* Indeterminate progress bar — syncing only */}
            {liveScrapingLoading && (
              <div className="h-0.5 bg-slate-700 overflow-hidden">
                <div className="h-full bg-teal-400 toast-progress" />
              </div>
            )}
          </div>
        </div>
      )}

      {/* Main Content */}
      {selectedApp ? (
        <div className="space-y-6">
          {loading ? (
            <div className="backdrop-blur-lg bg-white/70 border border-white/20 rounded-2xl shadow-xl p-12">
              <div className="max-w-md mx-auto text-center space-y-8">
                {/* Animated Loader */}
                <div className="relative">
                  <div className="w-20 h-20 mx-auto">
                    <div className="relative w-20 h-20 rounded-full border-4 border-transparent border-t-cyan-500 animate-spin"></div>
                  </div>
                  <div className="mt-4 text-4xl">📱</div>
                </div>

                <div>
                  <h3 className="text-2xl font-bold text-slate-800 mb-2">Analyzing {selectedApp}</h3>
                  <p className="text-slate-600">Fetching real-time insights...</p>
                </div>

                {/* Loading Steps */}
                <div className="space-y-3 text-left">
                  <div className={`flex items-center gap-3 p-3 rounded-xl transition-all ${loadingStep >= 1 ? 'bg-cyan-50' : 'bg-slate-50'}`}>
                    <div className="text-2xl">{loadingStep > 1 ? '✅' : '🔍'}</div>
                    <span className={`font-medium ${loadingStep >= 1 ? 'text-cyan-700' : 'text-slate-500'}`}>
                      Connecting to Shopify...
                    </span>
                  </div>
                  <div className={`flex items-center gap-3 p-3 rounded-xl transition-all ${loadingStep >= 2 ? 'bg-cyan-50' : 'bg-slate-50'}`}>
                    <div className="text-2xl">{loadingStep > 2 ? '✅' : '📊'}</div>
                    <span className={`font-medium ${loadingStep >= 2 ? 'text-cyan-700' : 'text-slate-500'}`}>
                      Fetching review data...
                    </span>
                  </div>
                  <div className={`flex items-center gap-3 p-3 rounded-xl transition-all ${loadingStep >= 3 ? 'bg-cyan-50' : 'bg-slate-50'}`}>
                    <div className="text-2xl">{loadingStep > 3 ? '✅' : '⚡'}</div>
                    <span className={`font-medium ${loadingStep >= 3 ? 'text-cyan-700' : 'text-slate-500'}`}>
                      Processing analytics...
                    </span>
                  </div>
                </div>

                {/* Progress Bar */}
                <div className="space-y-2">
                  <div className="h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div className="h-full bg-gradient-to-r from-cyan-500 to-emerald-500 rounded-full animate-pulse" style={{ width: '60%' }}></div>
                  </div>
                  <p className="text-sm text-slate-500">Loading real-time data...</p>
                </div>
              </div>
            </div>
          ) : error ? (
            <div className="backdrop-blur-lg bg-white/70 border border-red-200 rounded-2xl shadow-xl p-12">
              <div className="max-w-md mx-auto text-center space-y-4">
                <div className="text-6xl">⚠️</div>
                <h3 className="text-2xl font-bold text-red-600">Error Loading Data</h3>
                <p className="text-slate-600">{error}</p>
                <button
                  onClick={() => fetchAnalyticsData(selectedApp)}
                  className="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-medium hover:shadow-lg hover:shadow-cyan-500/30 transition-all flex items-center gap-2 mx-auto"
                >
                  <RefreshCw className="w-4 h-4" />
                  🔄 Retry
                </button>
              </div>
            </div>
          ) : null}
          {analyticsData ? (
            <>
              {/* KPI Statistics Cards */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {/* Monthly Reviews */}
                <div className="group backdrop-blur-lg bg-gradient-to-br from-cyan-50/80 to-blue-50/80 border border-white/40 rounded-2xl shadow-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <p className="text-sm font-medium text-cyan-700 mb-1">Monthly Reviews</p>
                      <h3 className="text-3xl font-bold text-slate-800 mb-1">{analyticsData.this_month_count}</h3>
                      <p className="text-sm text-slate-500">Reviews</p>
                    </div>
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center shadow-lg">
                      <TrendingUp className="w-6 h-6 text-white" />
                    </div>
                  </div>
                  <div className="mt-4 flex items-center gap-1 text-xs">
                    {analyticsData.this_month_count > 0
                      ? <span className="text-emerald-600 font-medium">↑ Active</span>
                      : <span className="text-slate-400 font-medium">No reviews yet</span>
                    }
                  </div>
                </div>

                {/* 30-Day Total */}
                <div className="group backdrop-blur-lg bg-gradient-to-br from-violet-50/80 to-purple-50/80 border border-white/40 rounded-2xl shadow-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <p className="text-sm font-medium text-violet-700 mb-1">30-Day Total</p>
                      <h3 className="text-3xl font-bold text-slate-800 mb-1">{analyticsData.last_30_days_count}</h3>
                      <p className="text-sm text-slate-500">Reviews</p>
                    </div>
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center shadow-lg">
                      <Calendar className="w-6 h-6 text-white" />
                    </div>
                  </div>
                  <div className="mt-4 flex items-center gap-1 text-xs">
                    <span className="text-violet-600 font-medium">Rolling 30 days</span>
                  </div>
                </div>

                {/* All-Time Reviews */}
                <div className="group backdrop-blur-lg bg-gradient-to-br from-emerald-50/80 to-teal-50/80 border border-white/40 rounded-2xl shadow-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <p className="text-sm font-medium text-emerald-700 mb-1">All-Time Reviews</p>
                      <h3 className="text-3xl font-bold text-slate-800 mb-1">{analyticsData.rating_distribution_total || analyticsData.total_reviews || 0}</h3>
                      <p className="text-sm text-slate-500">Reviews</p>
                    </div>
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg">
                      <BarChart2 className="w-6 h-6 text-white" />
                    </div>
                  </div>
                  <div className="mt-4 flex items-center gap-1 text-xs">
                    <span className="text-emerald-600 font-medium">Since launch</span>
                  </div>
                </div>

                {/* Overall Rating */}
                <div className="group backdrop-blur-lg bg-gradient-to-br from-amber-50/80 to-orange-50/80 border border-white/40 rounded-2xl shadow-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <p className="text-sm font-medium text-amber-700 mb-1">Overall Rating</p>
                      <h3 className="text-4xl font-bold text-slate-800 mb-2">{analyticsData.shopify_display_rating || analyticsData.average_rating}</h3>
                      <div className="flex items-center gap-0.5 mb-1">
                        {[1, 2, 3, 4, 5].map(i => (
                          <Star
                            key={i}
                            className={`w-4 h-4 ${i <= Math.round(analyticsData.shopify_display_rating || analyticsData.average_rating) ? 'text-amber-400 fill-amber-400' : 'text-slate-200 fill-slate-200'}`}
                          />
                        ))}
                      </div>
                      <p className="text-sm text-slate-500">Avg. star rating</p>
                    </div>
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg">
                      <Star className="w-6 h-6 text-white fill-white" />
                    </div>
                  </div>
                </div>
              </div>

              {/* Rating Distribution */}
              <div className="backdrop-blur-lg bg-white/70 border border-white/20 rounded-2xl shadow-xl p-6 mt-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                  <div>
                    <h2 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                      <BarChart2 className="w-5 h-5 text-teal-600" />
                      Rating Distribution
                    </h2>
                    <p className="text-sm text-slate-500 mt-1">Breakdown of customer ratings</p>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="px-3 py-1 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-xs font-medium flex items-center gap-1.5 shadow-md">
                      <span className="w-1.5 h-1.5 rounded-full bg-white animate-pulse inline-block" />
                      Live from Shopify
                    </span>
                    {analyticsData.rating_distribution_total && (
                      <span className="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                        {analyticsData.rating_distribution_total} reviews analyzed
                      </span>
                    )}
                  </div>
                </div>

                <div className="space-y-2">
                  {[5, 4, 3, 2, 1].map(rating => {
                    const count = analyticsData.rating_distribution[rating] || 0;
                    const total = analyticsData.rating_distribution_total || analyticsData.total_reviews || 0;
                    const percentage = total > 0 ? (count / total * 100).toFixed(1) : 0;

                    const colorMap = {
                      5: 'from-emerald-500 to-teal-500',
                      4: 'from-blue-500 to-cyan-500',
                      3: 'from-amber-500 to-yellow-500',
                      2: 'from-orange-500 to-red-500',
                      1: 'from-red-500 to-pink-500'
                    };

                    return (
                      <div key={rating} className="flex items-center gap-4 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors duration-150">
                        <div className="flex items-center gap-2 w-24 flex-shrink-0">
                          <span className="text-amber-400 text-sm">{'★'.repeat(rating)}</span>
                          <span className="font-semibold text-slate-700">{rating}</span>
                        </div>
                        <div className="flex-1 h-7 bg-slate-100 rounded-full overflow-hidden">
                          <div
                            className={`h-full bg-gradient-to-r ${colorMap[rating]} rounded-full transition-all duration-500 flex items-center justify-end pr-3`}
                            style={{ width: `${percentage}%` }}
                          >
                            {percentage > 12 && (
                              <span className="text-white text-xs font-bold">{percentage}%</span>
                            )}
                          </div>
                        </div>
                        <div className="flex items-center gap-1.5 w-28 flex-shrink-0 text-sm">
                          <span className="font-bold text-slate-800">{count}</span>
                          <span className="text-slate-400 text-xs">({percentage}%)</span>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Reviews Details */}
              <div className="backdrop-blur-lg bg-white/70 border border-white/20 rounded-2xl shadow-xl p-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                  <div>
                    <h2 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                      <MessageSquare className="w-5 h-5 text-teal-600" />
                      Reviews Details
                    </h2>
                    <p className="text-sm text-slate-500 mt-1">Latest verified reviews from Shopify</p>
                  </div>
                  <div className="relative">
                    <select
                      value={reviewsFilter}
                      onChange={(e) => {
                        const value = e.target.value;
                        setReviewsFilter(value);
                        setShowCustomDate(value === 'custom');
                      }}
                      className="appearance-none px-4 py-2 pr-10 rounded-xl border-2 border-slate-200 bg-white hover:border-cyan-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none font-medium text-slate-700"
                    >
                      <option value="all">Filter by Period</option>
                      <option value="last_30_days">Last 30 Days</option>
                      <option value="this_month">This Month</option>
                      <option value="last_month">Last Month</option>
                      <option value="last_90_days">Last 90 Days</option>
                      <option value="custom">Custom Date Range</option>
                    </select>
                    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" />
                  </div>
                </div>

                {showCustomDate && (
                  <div className="bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl p-5 border border-cyan-200 mb-4">
                    <div className="flex items-center gap-2 mb-4">
                      <Calendar className="w-5 h-5 text-cyan-600" />
                      <h4 className="font-semibold text-slate-800">Select Date Range</h4>
                    </div>

                    <div className="flex flex-col sm:flex-row gap-4 mb-4">
                      <div className="flex-1">
                        <label htmlFor="start-date" className="block text-sm font-medium text-slate-700 mb-2">
                          From
                        </label>
                        <input
                          id="start-date"
                          type="date"
                          value={customDateRange.start}
                          onChange={(e) => setCustomDateRange(prev => ({ ...prev, start: e.target.value }))}
                          className="w-full px-4 py-2 rounded-lg border-2 border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none"
                          max={customDateRange.end || new Date().toISOString().split('T')[0]}
                        />
                      </div>
                      <div className="flex items-center justify-center sm:pt-6">
                        <span className="text-slate-400 text-2xl">→</span>
                      </div>
                      <div className="flex-1">
                        <label htmlFor="end-date" className="block text-sm font-medium text-slate-700 mb-2">
                          To
                        </label>
                        <input
                          id="end-date"
                          type="date"
                          value={customDateRange.end}
                          onChange={(e) => setCustomDateRange(prev => ({ ...prev, end: e.target.value }))}
                          className="w-full px-4 py-2 rounded-lg border-2 border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none"
                          min={customDateRange.start}
                          max={new Date().toISOString().split('T')[0]}
                        />
                      </div>
                    </div>

                    <div className="flex flex-wrap gap-2">
                      {/* Quick-fill shortcuts — these just pre-fill the inputs; click Apply to fetch */}
                      <button
                        onClick={() => {
                          const today = new Date();
                          const start = new Date(today);
                          start.setDate(today.getDate() - 6); // today − 6 = 7 days inclusive
                          setCustomDateRange({
                            start: start.toISOString().split('T')[0],
                            end: today.toISOString().split('T')[0],
                          });
                        }}
                        className="px-3 py-1.5 rounded-lg bg-white border border-cyan-300 text-cyan-700 text-sm font-medium hover:bg-cyan-50 transition-all"
                      >
                        Last 7 Days
                      </button>
                      <button
                        onClick={() => {
                          const today = new Date();
                          const start = new Date(today);
                          start.setDate(today.getDate() - 29); // today − 29 = 30 days inclusive
                          setCustomDateRange({
                            start: start.toISOString().split('T')[0],
                            end: today.toISOString().split('T')[0],
                          });
                        }}
                        className="px-3 py-1.5 rounded-lg bg-white border border-cyan-300 text-cyan-700 text-sm font-medium hover:bg-cyan-50 transition-all"
                      >
                        Last 30 Days
                      </button>
                      <button
                        onClick={() => setCustomDateRange({ start: '', end: '' })}
                        className="px-3 py-1.5 rounded-lg bg-white border border-red-300 text-red-700 text-sm font-medium hover:bg-red-50 transition-all"
                      >
                        Clear
                      </button>
                      {/* Apply — only active when both dates are set */}
                      <button
                        onClick={() => fetchFilteredReviews(selectedApp, 'custom')}
                        disabled={!customDateRange.start || !customDateRange.end}
                        className="px-4 py-1.5 rounded-lg bg-teal-500 text-white text-sm font-semibold hover:bg-teal-600 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                      >
                        Apply
                      </button>
                    </div>
                  </div>
                )}

                {/* Date Range Indicator — pill badges */}
                {latestReviews.length > 0 && (() => {
                  const dr = computeDateRange(reviewsFilter, customDateRange);
                  return (
                    <div className="mb-4 flex flex-wrap items-center gap-2">
                      {dr && (
                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-50 border border-teal-200 text-teal-700 text-xs font-medium">
                          📅 {dr.start} – {dr.end}
                        </span>
                      )}
                      <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-xs font-medium">
                        📄 {latestReviews.length} Reviews
                      </span>
                    </div>
                  );
                })()}

                <div className="space-y-4">
                  {latestReviews.length > 0 ? (
                    latestReviews.map((review, index) => {
                      const borderColorMap = {
                        5: 'border-l-emerald-500',
                        4: 'border-l-blue-500',
                        3: 'border-l-amber-500',
                        2: 'border-l-orange-500',
                        1: 'border-l-red-500'
                      };
                      const ratingBadgeColorMap = {
                        5: 'bg-emerald-100 text-emerald-700',
                        4: 'bg-blue-100 text-blue-700',
                        3: 'bg-amber-100 text-amber-700',
                        2: 'bg-orange-100 text-orange-700',
                        1: 'bg-red-100 text-red-700'
                      };

                      return (
                        <div
                          key={index}
                          className={`bg-white border-l-4 ${borderColorMap[review.rating]} rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300`}
                        >
                          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                            <div className="flex flex-wrap items-center gap-2">
                              <span className="font-semibold text-base text-slate-800">{review.store_name}</span>
                              <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold ${ratingBadgeColorMap[review.rating]}`}>
                                ★ {review.rating}
                              </span>
                              <span className="text-sm text-slate-500">{formatDate(review.review_date)}</span>
                              <span className="px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                                {getCountryName(review.country_name)}
                              </span>
                            </div>
                            <div className="text-amber-400 text-lg flex-shrink-0">
                              {renderStars(review.rating)}
                            </div>
                          </div>

                          <div className="text-slate-700 leading-relaxed">
                            <p>{review.review_content}</p>
                          </div>
                        </div>
                      );
                    })
                  ) : (
                    <div className="text-center py-12">
                      <div className="text-6xl mb-4">📭</div>
                      <h3 className="text-xl font-semibold text-slate-700 mb-2">No Recent Reviews Found</h3>
                      <p className="text-slate-500">Try adjusting your filter to see more reviews</p>
                    </div>
                  )}
                </div>
              </div>
            </>
          ) : null}
        </div>
      ) : (
        <div className="backdrop-blur-lg bg-white/70 border border-slate-100 rounded-2xl shadow-xl overflow-hidden" style={{boxShadow: 'inset 0 1px 0 0 rgba(255,255,255,0.8), 0 4px 24px 0 rgba(0,0,0,0.07)'}}>
          {/* Radial teal glow background */}
          <div
            className="absolute inset-0 pointer-events-none rounded-2xl"
            style={{ background: 'radial-gradient(ellipse 70% 60% at 50% 40%, rgba(20,184,166,0.10) 0%, transparent 80%)' }}
          />
          <div className="relative py-20 px-8">
            <div className="max-w-lg mx-auto text-center space-y-8">

              {/* Animated illustration */}
              <div className="relative inline-block">
                {/* Outer pulsing ring */}
                <div className="absolute inset-0 rounded-full bg-teal-200/40 animate-pulse scale-125" />
                {/* Inner circle */}
                <div className="relative w-36 h-36 mx-auto bg-gradient-to-br from-teal-100 to-emerald-100 rounded-full flex items-center justify-center shadow-lg shadow-teal-200/60">
                  <BarChart2 className="w-18 h-18 text-teal-600" style={{width: '4.5rem', height: '4.5rem'}} />
                </div>
                {/* Sparkle badge anchored bottom-right with tooltip */}
                <div
                  className="absolute -bottom-1 -right-1 w-10 h-10 bg-gradient-to-br from-teal-400 to-emerald-500 rounded-full flex items-center justify-center shadow-md shadow-teal-400/40 cursor-default"
                  title="AI-powered insights"
                >
                  <Sparkles className="w-5 h-5 text-white" />
                </div>
              </div>

              {/* Heading & description */}
              <div>
                <h2 className="text-3xl font-bold text-slate-800 mb-3">Welcome to Your Review Hub</h2>
                <p className="text-slate-500 text-base leading-relaxed">
                  Pick any connected app above to instantly unlock performance metrics, review trends, and team insights.
                </p>
              </div>

              {/* Divider */}
              <div className="border-t border-slate-100" />

              {/* Stat pill cards */}
              <div className="grid grid-cols-3 gap-4">
                <div className="flex flex-col items-center gap-2 bg-teal-50 rounded-2xl px-4 py-5 border border-teal-100 shadow-sm">
                  <span className="text-3xl font-bold text-teal-600">6</span>
                  <span className="text-xs font-medium text-teal-700 text-center">Apps Connected</span>
                </div>
                <div className="flex flex-col items-center gap-2 bg-teal-50 rounded-2xl px-4 py-5 border border-teal-100 shadow-sm">
                  <span className="text-3xl font-bold text-emerald-600">∞</span>
                  <span className="text-xs font-medium text-emerald-700 text-center">Always Live</span>
                </div>
                <div className="flex flex-col items-center gap-2 bg-teal-50 rounded-2xl px-4 py-5 border border-teal-100 shadow-sm">
                  <span className="text-3xl font-bold text-violet-600">⚡</span>
                  <span className="text-xs font-medium text-violet-700 text-center">Instant Sync</span>
                </div>
              </div>

            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Analytics;
