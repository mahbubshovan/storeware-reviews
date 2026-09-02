import { useState, useEffect, useCallback, useRef } from 'react';
import { useCache } from '../context/CacheContext';
import { Star, ChevronLeft, ChevronRight, Edit2, Check, X, Zap, RefreshCw, ShoppingBag, Search, Send, Loader2, AlertCircle } from 'lucide-react';
import { AGENTS, getGravatarUrl, getAgentByName } from '../config/agents';
import { APPS, getAppIcon } from '../config/appConfig';

// Avatar component — Gravatar image or ShoppingBag icon for Organic
const AgentAvatar = ({ agent, size = 'sm', ringClass = 'ring-teal-100' }) => {
  const dim = size === 'sm' ? 'w-6 h-6' : size === 'lg' ? 'w-9 h-9' : 'w-8 h-8';
  if (!agent || agent.name === 'Organic') {
    return (
      <span className={`${dim} rounded-full bg-teal-50 border border-teal-200 flex items-center justify-center flex-shrink-0`}>
        <ShoppingBag className="w-3 h-3 text-teal-500" />
      </span>
    );
  }
  return (
    <img
      src={getGravatarUrl(agent.hash)}
      alt={agent.name}
      className={`${dim} rounded-full ring-2 ${ringClass} object-cover flex-shrink-0`}
    />
  );
};

// App tab button with real icon + graceful letter fallback
const AppTabButton = ({ app, isActive, onClick }) => {
  const [imgError, setImgError] = useState(false);
  const initial = app.name?.charAt(0)?.toUpperCase() || '?';
  return (
    <button
      onClick={onClick}
      className={`flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold transition-all duration-200 ${
        isActive
          ? 'bg-gradient-to-r from-cyan-500 to-emerald-500 text-white shadow-md shadow-cyan-500/20'
          : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800'
      }`}
    >
      {app.icon && !imgError ? (
        <img
          src={app.icon}
          alt={app.name}
          className="w-5 h-5 rounded-md object-cover flex-shrink-0"
          onError={() => setImgError(true)}
        />
      ) : (
        <span className={`w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-bold flex-shrink-0 ${
          isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'
        }`}>
          {initial}
        </span>
      )}
      <span>{app.name}</span>
    </button>
  );
};

const AccessTabbed = () => {
  // Use global cache from context
  const { getCachedData, setCachedData, clearAppCache } = useCache();

  // App configuration — sourced from shared config so icons stay in sync
  const apps = APPS;

  // State management
  const [activeTab, setActiveTab] = useState('StoreSEO');
  const [reviews, setReviews] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Pagination state
  const [pagination, setPagination] = useState({
    current_page: 1,
    total_pages: 0,
    total_items: 0,
    items_per_page: 15,
    has_next_page: false,
    has_prev_page: false,
    page_numbers: []
  });
  
  // Current page for each tab
  const [tabPages, setTabPages] = useState({
    'StoreSEO': 1,
    'StoreFAQ': 1,
    'EasyFlow': 1,
    'TrustSync': 1,
    'Vidify': 1,
    'BetterDocs FAQ Knowledge Base': 1
  });
  

  
  // Edit state
  const [editingReview, setEditingReview] = useState(null);
  const [editValue, setEditValue] = useState('');
  const [scrollPosition, setScrollPosition] = useState(0);

  // Dropdown state
  const [dropdownSearch, setDropdownSearch] = useState('');
  // Per-review state for the manual "Send to Slack" button.
  const [slackStatus, setSlackStatus] = useState({});
  const dropdownRef = useRef(null);

  // Request deduplication - track ongoing requests to prevent duplicates
  const ongoingRequestRef = useRef(null);
  const lastRequestKeyRef = useRef(null);

  // Memoize fetchTabReviews with request deduplication and global caching
  const fetchTabReviews = useCallback(async (appName, page = 1) => {
    // Create a unique key for this request
    const requestKey = `${appName}-${page}`;
    const cacheKey = `access_reviews_${appName}_page${page}`;

    // If same request is already in progress, skip it
    if (ongoingRequestRef.current === requestKey) {
      console.log('⚠️ Duplicate request prevented:', requestKey);
      return;
    }

    // If this is the exact same request as the last one, skip it
    if (lastRequestKeyRef.current === requestKey) {
      console.log('⚠️ Duplicate request prevented (same as last):', requestKey);
      return;
    }

    // Check global cache first
    const cachedData = getCachedData(appName, null, cacheKey);
    if (cachedData) {
      console.log('✅ Loading from global cache:', cacheKey);
      setReviews(cachedData.reviews || []);
      setPagination(cachedData.pagination || {});
      setStatistics(cachedData.statistics || {});
      setLoading(false);
      setError(null);
      return;
    }

    // Mark this request as ongoing
    ongoingRequestRef.current = requestKey;
    lastRequestKeyRef.current = requestKey;

    setLoading(true);
    setError(null);

    try {
      console.log('✅ Fetching reviews from API:', requestKey);
      const [reviewsResp, analyticsResp] = await Promise.all([
        fetch(`/backend/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=${page}&limit=15`),
        // Pull the live Shopify total + avg rating so the header matches the Analytics dashboard
        fetch(`/backend/api/enhanced-analytics.php?app=${encodeURIComponent(appName)}`).catch(() => null),
      ]);

      if (!reviewsResp.ok) {
        throw new Error(`HTTP error! status: ${reviewsResp.status}`);
      }

      const responseText = await reviewsResp.text();
      console.log('Response text:', responseText.substring(0, 200));

      if (!responseText) {
        throw new Error('Empty response from server');
      }

      const data = JSON.parse(responseText);

      if (data.success) {
        setReviews(data.data.reviews || []);
        setPagination(data.data.pagination || {});

        let mergedStats = data.data.statistics || {};
        if (analyticsResp && analyticsResp.ok) {
          try {
            const analyticsData = await analyticsResp.json();
            if (analyticsData?.success && analyticsData.data) {
              const liveTotal = analyticsData.data.total_reviews;
              const liveAvg = analyticsData.data.average_rating;
              const assigned = mergedStats.assigned_reviews || 0;
              mergedStats = {
                ...mergedStats,
                total_reviews: liveTotal ?? mergedStats.total_reviews,
                avg_rating: liveAvg ?? mergedStats.avg_rating,
                unassigned_reviews:
                  liveTotal != null ? Math.max(0, liveTotal - assigned) : mergedStats.unassigned_reviews,
              };
            }
          } catch (mergeErr) {
            console.warn('Could not merge analytics stats:', mergeErr);
          }
        }

        setStatistics(mergedStats);
        // Cache the merged statistics so future loads stay consistent
        setCachedData(appName, { ...data.data, statistics: mergedStats }, null, cacheKey);
      } else {
        throw new Error(data.error || 'Failed to fetch reviews');
      }
    } catch (err) {
      console.error('Error fetching reviews:', err);
      setError(err.message);
      setReviews([]);
    } finally {
      setLoading(false);
      // Clear ongoing request marker
      ongoingRequestRef.current = null;
    }
  }, [getCachedData, setCachedData]); // Add cache functions to dependencies

  // Fetch reviews when activeTab changes - single source of truth for tab navigation
  useEffect(() => {
    const currentPage = tabPages[activeTab];
    fetchTabReviews(activeTab, currentPage);
  }, [activeTab]); // Only depend on activeTab, not tabPages or fetchTabReviews

  const handleTabChange = (appName) => {
    if (appName !== activeTab) {
      setActiveTab(appName);
      // Don't call fetchTabReviews here - let useEffect handle it to avoid duplicate requests
    }
  };

  const handlePageChange = (newPage) => {
    // Update the page for current tab
    setTabPages(prev => ({
      ...prev,
      [activeTab]: newPage
    }));

    // Fetch new data immediately
    fetchTabReviews(activeTab, newPage);
  };

  const handleEditClick = (review) => {
    setScrollPosition(window.pageYOffset);
    setEditingReview(review.id);
    setEditValue(review.earned_by || '');
  };

  const handleEditSave = async (reviewId) => {
    try {
      const response = await fetch('/backend/api/access-reviews-tabbed.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          earned_by: editValue.trim()
        })
      });

      const data = await response.json();

      if (data.success) {
        // Find the review being updated to check if it was previously unassigned
        const reviewBeingUpdated = reviews.find(r => r.id === reviewId);
        const wasUnassigned = !reviewBeingUpdated?.earned_by;

        // Update the review in the current list
        setReviews(prevReviews =>
          prevReviews.map(review =>
            review.id === reviewId
              ? { ...review, earned_by: editValue.trim() }
              : review
          )
        );

        // Update statistics immediately
        if (statistics && wasUnassigned && editValue.trim()) {
          setStatistics(prevStats => ({
            ...prevStats,
            assigned_reviews: (prevStats.assigned_reviews || 0) + 1,
            unassigned_reviews: Math.max(0, (prevStats.unassigned_reviews || 0) - 1)
          }));
        }

        // Clear cache for the current app to ensure fresh data on next load
        clearAppCache(activeTab);

        setEditingReview(null);
        setEditValue('');

        // Restore scroll position
        setTimeout(() => {
          window.scrollTo(0, scrollPosition);
        }, 100);
      } else {
        alert('Error updating assignment: ' + data.error);
      }
    } catch (error) {
      console.error('Error updating assignment:', error);
      alert('Error updating assignment');
    }
  };

  // Auto-save when agent is selected from dropdown (no separate Save button needed)
  const handleAgentSelect = async (reviewId, agentName) => {
    setEditValue(agentName);
    setDropdownSearch('');
    try {
      const response = await fetch('/backend/api/access-reviews-tabbed.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ review_id: reviewId, earned_by: agentName })
      });
      const data = await response.json();
      if (data.success) {
        const reviewBeingUpdated = reviews.find(r => r.id === reviewId);
        const wasUnassigned = !reviewBeingUpdated?.earned_by;
        setReviews(prevReviews =>
          prevReviews.map(r => r.id === reviewId ? { ...r, earned_by: agentName } : r)
        );
        if (statistics && wasUnassigned && agentName) {
          setStatistics(prevStats => ({
            ...prevStats,
            assigned_reviews: (prevStats.assigned_reviews || 0) + 1,
            unassigned_reviews: Math.max(0, (prevStats.unassigned_reviews || 0) - 1)
          }));
        }
        clearAppCache(activeTab);
        setEditingReview(null);
        setEditValue('');
        setTimeout(() => { window.scrollTo(0, scrollPosition); }, 100);
      } else {
        alert('Error updating assignment: ' + data.error);
      }
    } catch (err) {
      console.error('Error updating assignment:', err);
      alert('Error updating assignment');
    }
  };

  // Push one review to the Slack channel on demand. Purely additive — it never
  // touches the assignment, so it's safe to press twice.
  const handleSendToSlack = async (reviewId) => {
    setSlackStatus(prev => ({ ...prev, [reviewId]: 'sending' }));
    const settle = (state) => {
      setSlackStatus(prev => ({ ...prev, [reviewId]: state }));
      // Let the result show briefly, then return the button to its resting state.
      setTimeout(() => setSlackStatus(prev => {
        const next = { ...prev };
        delete next[reviewId];
        return next;
      }), 4000);
    };

    try {
      const response = await fetch('/backend/api/access-reviews-tabbed.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ review_id: reviewId, action: 'send_to_slack' })
      });
      const data = await response.json();
      if (!data.success) console.error('Slack send failed:', data.error);
      settle(data.success ? 'sent' : 'error');
    } catch (err) {
      console.error('Slack send failed:', err);
      settle('error');
    }
  };

  // Agents matching the picker's search box — derived once per render rather
  // than recomputed inside every review row.
  const filteredAgents = AGENTS.filter(a =>
    a.name.toLowerCase().includes(dropdownSearch.trim().toLowerCase())
  );

  const handleEditCancel = () => {
    setEditingReview(null);
    setEditValue('');
    setDropdownSearch('');
    setTimeout(() => {
      window.scrollTo(0, scrollPosition);
    }, 100);
  };

  const formatDate = (dateString) => {
    if (!dateString || dateString === '1970-01-01') return 'Unknown Date';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const getCountryName = (countryData) => {
    // ALWAYS return a real country - never show "Unknown"
    console.log('Country data received:', countryData); // Debug log

    // Since we now have accurate country data, handle edge cases gracefully
    if (!countryData || countryData.trim() === '') {
      return '🌍 Unknown Location';
    }

    // Clean up the country data - extract country name from mixed format
    // Handle formats like "StoreName\n      \n          CountryName"
    const cleanCountry = countryData
      .split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .pop(); // Get the last non-empty line (usually the country)

    // Map common country variations to clean names
    const countryMap = {
      'United States': '🇺🇸 United States',
      'USA': '🇺🇸 United States',
      'US': '🇺🇸 United States',
      'America': '🇺🇸 United States',
      'Canada': '🇨🇦 Canada',
      'United Kingdom': '🇬🇧 United Kingdom',
      'UK': '🇬🇧 United Kingdom',
      'Britain': '🇬🇧 United Kingdom',
      'England': '🇬🇧 United Kingdom',
      'Australia': '🇦🇺 Australia',
      'Germany': '🇩🇪 Germany',
      'Deutschland': '🇩🇪 Germany',
      'France': '🇫🇷 France',
      'India': '🇮🇳 India',
      'Brazil': '🇧🇷 Brazil',
      'Brasil': '🇧🇷 Brazil',
      'Netherlands': '🇳🇱 Netherlands',
      'Holland': '🇳🇱 Netherlands',
      'Nederland': '🇳🇱 Netherlands',
      'Spain': '🇪🇸 Spain',
      'España': '🇪🇸 Spain',
      'Italy': '🇮🇹 Italy',
      'Italia': '🇮🇹 Italy',
      'Japan': '🇯🇵 Japan',
      'South Korea': '🇰🇷 South Korea',
      'Mexico': '🇲🇽 Mexico',
      'Argentina': '🇦🇷 Argentina',
      'Switzerland': '🇨🇭 Switzerland',
      'Austria': '🇦🇹 Austria',
      'Ireland': '🇮🇪 Ireland',
      'Belgium': '🇧🇪 Belgium',
      'Sweden': '🇸🇪 Sweden',
      'Norway': '🇳🇴 Norway',
      'Denmark': '🇩🇰 Denmark',
      'Finland': '🇫🇮 Finland',
      'Portugal': '🇵🇹 Portugal',
      'Poland': '🇵🇱 Poland',
      'Czech Republic': '🇨🇿 Czech Republic',
      'Hungary': '🇭🇺 Hungary',
      'Greece': '🇬🇷 Greece',
      'Turkey': '🇹🇷 Turkey',
      'Russia': '🇷🇺 Russia',
      'China': '🇨🇳 China',
      'Singapore': '🇸🇬 Singapore',
      'Malaysia': '🇲🇾 Malaysia',
      'Thailand': '🇹🇭 Thailand',
      'Philippines': '🇵🇭 Philippines',
      'Indonesia': '🇮🇩 Indonesia',
      'Vietnam': '🇻🇳 Vietnam',
      'Hong Kong': '🇭🇰 Hong Kong',
      'Taiwan': '🇹🇼 Taiwan',
      'Chile': '🇨🇱 Chile',
      'Colombia': '🇨🇴 Colombia',
      'Peru': '🇵🇪 Peru',
      'South Africa': '🇿🇦 South Africa',
      'Egypt': '🇪🇬 Egypt',
      'Israel': '🇮🇱 Israel',
      'United Arab Emirates': '🇦🇪 United Arab Emirates',
      'UAE': '🇦🇪 United Arab Emirates',
      'Saudi Arabia': '🇸🇦 Saudi Arabia',
      'New Zealand': '🇳🇿 New Zealand'
    };

    // Check for exact match first
    if (countryMap[cleanCountry]) {
      return countryMap[cleanCountry];
    }

    // Check for case-insensitive match
    const lowerCleanCountry = cleanCountry.toLowerCase();
    for (const [key, value] of Object.entries(countryMap)) {
      if (key.toLowerCase() === lowerCleanCountry) {
        return value;
      }
    }

    // Final safety check - NEVER return "Unknown"
    if (cleanCountry.toLowerCase() === 'unknown' || cleanCountry.trim() === '') {
      console.log('Final fallback triggered for:', cleanCountry);
      return '🇺🇸 United States'; // Default fallback
    }

    // If no match found, return with globe emoji
    console.log('Returning with globe emoji:', cleanCountry);
    return `🌍 ${cleanCountry}`;
  };

  const renderStars = (rating) => {
    const numRating = parseInt(rating);
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return <span className="text-gray-400 text-sm">No rating</span>;
    }
    return (
      <div className="flex items-center gap-0.5">
        {[1,2,3,4,5].map(i => (
          <Star key={i} className={`w-4 h-4 ${i <= numRating ? 'text-amber-400 fill-amber-400' : 'text-gray-300'}`} />
        ))}
      </div>
    );
  };



  return (
    <div className="space-y-6">
      {/* Header & Stats */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Access Reviews</h1>
            <p className="text-sm text-slate-500 mt-0.5">Track which agent earned each customer review</p>
          </div>
          {statistics?.cache_status && (
            <span className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ${
              statistics.cache_status === 'hit'
                ? 'bg-amber-50 text-amber-700 border border-amber-200'
                : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
            }`}>
              {statistics.cache_status === 'hit' ? <Zap className="w-3 h-3" /> : <RefreshCw className="w-3 h-3" />}
              {statistics.cache_status === 'hit' ? 'Cached' : 'Fresh'}
            </span>
          )}
        </div>

        {statistics && (
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {[
              { label: 'Total Reviews',    value: statistics.total_reviews,    color: 'from-cyan-500 to-blue-500' },
              { label: 'Agent Earned',     value: statistics.assigned_reviews, color: 'from-emerald-500 to-teal-500' },
              { label: 'Organic / Pending',value: statistics.unassigned_reviews,color: 'from-orange-400 to-rose-400' },
              { label: 'Avg Rating',       value: `${statistics.avg_rating}★`, color: 'from-amber-400 to-yellow-500' },
            ].map(({ label, value, color }) => (
              <div key={label} className="bg-white/80 rounded-xl p-4 border border-slate-100 shadow-sm">
                <p className="text-xs font-medium text-slate-500 mb-1">{label}</p>
                <p className={`text-2xl font-bold bg-gradient-to-r ${color} bg-clip-text text-transparent`}>{value}</p>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* App Tab Navigation */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-2 flex flex-wrap gap-1.5">
        {apps.map(app => (
          <AppTabButton
            key={app.name}
            app={app}
            isActive={activeTab === app.name}
            onClick={() => handleTabChange(app.name)}
          />
        ))}
      </div>

      {/* Tab Content */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-16 gap-3">
            <div className="w-10 h-10 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin" />
            <p className="text-slate-500 font-medium">Loading {activeTab} reviews…</p>
          </div>
        ) : error ? (
          <div className="flex flex-col items-center justify-center py-16 gap-3">
            <p className="text-rose-500 font-medium">Error: {error}</p>
            <button
              onClick={() => fetchTabReviews(activeTab, tabPages[activeTab])}
              className="px-5 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-sm font-semibold transition-all"
            >
              Retry
            </button>
          </div>
        ) : (
          <>
            {/* Reviews sub-header */}
            <div className="flex items-center justify-between mb-5">
              <h2 className="text-lg font-bold text-slate-700">{activeTab} Reviews</h2>
              <div className="flex items-center gap-2">
                <span className="inline-flex items-center gap-1 text-xs text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-3 py-1 font-medium">
                  📄 Page {pagination.current_page} of {pagination.total_pages}
                </span>
                <span className="inline-flex items-center gap-1 text-xs text-teal-700 bg-teal-50 border border-teal-200 rounded-full px-3 py-1 font-medium">
                  ⭐ {statistics?.total_reviews ?? pagination.total_items} Total Reviews
                </span>
              </div>
            </div>

            {reviews.length === 0 ? (
              <div className="text-center py-12 text-slate-400">
                <p className="text-lg font-medium">No reviews found for {activeTab}</p>
              </div>
            ) : (
              <div className="space-y-3">
                {reviews.map((review) => (
                  <div key={review.id} className="bg-white rounded-xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-all duration-200">
                    {/* Review top row */}
                    <div className="flex flex-wrap items-start justify-between gap-3 mb-3">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="font-bold text-slate-800 text-sm">{review.store_name}</span>
                        <span className="text-xs text-slate-400">{formatDate(review.review_date)}</span>
                        <span className="inline-flex items-center gap-1 bg-emerald-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                          ✅ {getCountryName(review.country_name)}
                        </span>
                      </div>
                      {renderStars(review.rating)}
                    </div>

                    {/* Review body */}
                    {review.review_content && (
                      <p className="text-sm text-slate-600 leading-relaxed mb-3 border-l-2 border-cyan-200 pl-3">
                        {review.review_content}
                      </p>
                    )}

                    {/* Assignment row */}
                    <div className="flex items-center gap-2 flex-wrap">
                      <span className="flex items-center gap-1 text-xs font-semibold text-slate-500">
                        ⭐ Review Earned By:
                      </span>

                      {editingReview === review.id ? (
                        /* ── Custom Agent Dropdown ── */
                        <div className="relative" ref={dropdownRef}>
                          <div className="absolute z-50 w-80 rounded-xl border border-emerald-200 bg-white shadow-lg overflow-hidden">
                            {/* Search */}
                            <div className="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
                              <Search className="h-4 w-4 text-gray-400 flex-shrink-0" />
                              <input
                                type="text"
                                value={dropdownSearch}
                                onChange={(e) => setDropdownSearch(e.target.value)}
                                placeholder="Search agent..."
                                autoFocus
                                className="w-full bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none"
                              />
                            </div>
                            {/* Agent list */}
                            <ul className="py-1 max-h-64 overflow-y-auto">
                              {filteredAgents.length === 0 ? (
                                <li className="px-4 py-3 text-sm text-gray-400">No agent matches “{dropdownSearch}”</li>
                              ) : filteredAgents.map(agent => (
                                <li key={agent.name}>
                                  <button
                                    onClick={() => handleAgentSelect(review.id, agent.name)}
                                    className="flex w-full items-center gap-3 px-4 py-2.5 hover:bg-emerald-50 transition-colors text-left"
                                  >
                                    <AgentAvatar agent={agent} size="lg" ringClass="ring-emerald-100" />
                                    <span className="flex-1 text-sm font-medium text-gray-800">
                                      {agent.name === 'Organic' ? 'From Shopify Store' : agent.name}
                                    </span>
                                    {editValue === agent.name && (
                                      <Check className="h-4 w-4 text-emerald-500 flex-shrink-0" />
                                    )}
                                  </button>
                                </li>
                              ))}
                            </ul>
                            {/* Cancel */}
                            <button
                              onClick={handleEditCancel}
                              className="flex w-full items-center justify-center gap-2 border-t border-gray-100 px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors"
                            >
                              <X className="h-4 w-4" /> Cancel
                            </button>
                          </div>
                        </div>
                      ) : (
                        /* ── Agent Pill (view mode) ── */
                        (() => {
                          const agent = getAgentByName(review.earned_by);
                          const isOrganic = !review.earned_by || review.earned_by === 'Organic';
                          return (
                            <button
                              onClick={() => handleEditClick(review)}
                              title="Click to reassign"
                              className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition-all cursor-pointer ${
                                isOrganic
                                  ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-200 hover:bg-teal-100'
                                  : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100'
                              }`}
                            >
                              {isOrganic ? (
                                <>
                                  <span className="w-5 h-5 rounded-full bg-teal-100 flex items-center justify-center flex-shrink-0">
                                    <ShoppingBag className="w-3 h-3 text-teal-500" />
                                  </span>
                                  <span>Organic Review</span>
                                </>
                              ) : agent ? (
                                <>
                                  <AgentAvatar agent={agent} size="sm" ringClass="ring-emerald-100" />
                                  <span>🌟 Earned by {agent.name}</span>
                                </>
                              ) : (
                                <span>🌟 Earned by {review.earned_by}</span>
                              )}
                              <Edit2 className="w-3 h-3 opacity-40" />
                            </button>
                          );
                        })()
                      )}

                      {/* Manually push this review to the Slack channel */}
                      {(() => {
                        const status = slackStatus[review.id];
                        const busy = status === 'sending';
                        const look = status === 'sent'
                          ? 'bg-emerald-500 text-white ring-emerald-500'
                          : status === 'error'
                            ? 'bg-rose-50 text-rose-600 ring-rose-200 hover:bg-rose-100'
                            : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50 hover:text-slate-700';
                        return (
                          <button
                            onClick={() => handleSendToSlack(review.id)}
                            disabled={busy}
                            title="Send this review to the Slack channel"
                            className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 transition-all disabled:cursor-wait ${look}`}
                          >
                            {busy ? (
                              <Loader2 className="w-3 h-3 animate-spin" />
                            ) : status === 'sent' ? (
                              <Check className="w-3 h-3" />
                            ) : status === 'error' ? (
                              <AlertCircle className="w-3 h-3" />
                            ) : (
                              <Send className="w-3 h-3" />
                            )}
                            <span>
                              {busy ? 'Sending…' : status === 'sent' ? 'Sent' : status === 'error' ? 'Failed' : 'Send to Slack'}
                            </span>
                          </button>
                        );
                      })()}
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Pagination */}
            {pagination.total_pages > 1 && (
              <div className="flex items-center justify-center gap-1.5 mt-6 flex-wrap">
                <button
                  onClick={() => handlePageChange(pagination.current_page - 1)}
                  disabled={!pagination.has_prev_page}
                  className="flex items-center gap-1 px-3 py-2 rounded-xl text-sm font-semibold border transition-all
                    disabled:opacity-40 disabled:cursor-not-allowed
                    border-slate-200 text-slate-600 hover:bg-slate-100"
                >
                  <ChevronLeft className="w-4 h-4" /> Prev
                </button>

                {pagination.page_numbers.map(pageNum => (
                  <button
                    key={pageNum}
                    onClick={() => handlePageChange(pageNum)}
                    className={`w-9 h-9 rounded-xl text-sm font-semibold transition-all ${
                      pageNum === pagination.current_page
                        ? 'bg-gradient-to-r from-cyan-500 to-emerald-500 text-white shadow-md shadow-cyan-500/20'
                        : 'border border-slate-200 text-slate-600 hover:bg-slate-100'
                    }`}
                  >
                    {pageNum}
                  </button>
                ))}

                <button
                  onClick={() => handlePageChange(pagination.current_page + 1)}
                  disabled={!pagination.has_next_page}
                  className="flex items-center gap-1 px-3 py-2 rounded-xl text-sm font-semibold border transition-all
                    disabled:opacity-40 disabled:cursor-not-allowed
                    border-slate-200 text-slate-600 hover:bg-slate-100"
                >
                  Next <ChevronRight className="w-4 h-4" />
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default AccessTabbed;
