import { useState, useEffect, useRef } from 'react';
import { useCache } from '../context/CacheContext';
import { BarChart3, Globe, Users, ChevronDown, ShoppingBag } from 'lucide-react';
import { getGravatarUrl, getAgentByName } from '../config/agents';
import { getAppIcon } from '../config/appConfig';
import AgentEarnings from '../components/AgentEarnings';

// Small inline app icon with graceful letter fallback
const AppIconInline = ({ appName }) => {
  const [imgError, setImgError] = useState(false);
  const iconUrl = getAppIcon(appName);
  const initial = appName?.charAt(0)?.toUpperCase() || '?';
  if (iconUrl && !imgError) {
    return (
      <img
        src={iconUrl}
        alt={appName}
        className="w-5 h-5 rounded-md object-cover flex-shrink-0 border border-slate-100"
        onError={() => setImgError(true)}
      />
    );
  }
  return (
    <span className="w-5 h-5 rounded-md bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0">
      {initial}
    </span>
  );
};

const ReviewCount = () => {
  // Use global cache from context
  const { getCachedData, setCachedData } = useCache();

  // Clean up country names from database format
  const getCountryName = (countryData) => {
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

    // Map common country variations to clean names with flags
    const countryMap = {
      'United States': '🇺🇸 United States',
      'Canada': '🇨🇦 Canada',
      'United Kingdom': '🇬🇧 United Kingdom',
      'Australia': '🇦🇺 Australia',
      'Germany': '🇩🇪 Germany',
      'France': '🇫🇷 France',
      'South Africa': '🇿🇦 South Africa',
      'India': '🇮🇳 India',
      'Japan': '🇯🇵 Japan',
      'Singapore': '🇸🇬 Singapore',
      'Costa Rica': '🇨🇷 Costa Rica',
      'Netherlands': '🇳🇱 Netherlands',
      'Sweden': '🇸🇪 Sweden',
      'Norway': '🇳🇴 Norway',
      'Denmark': '🇩🇰 Denmark',
      'Finland': '🇫🇮 Finland',
      'Belgium': '🇧🇪 Belgium',
      'Switzerland': '🇨🇭 Switzerland',
      'Austria': '🇦🇹 Austria',
      'Ireland': '🇮🇪 Ireland',
      'New Zealand': '🇳🇿 New Zealand',
      'Portugal': '🇵🇹 Portugal',
      'Spain': '🇪🇸 Spain',
      'China': '🇨🇳 China',
      'Malaysia': '🇲🇾 Malaysia',
      'Mexico': '🇲🇽 Mexico',
      'Ukraine': '🇺🇦 Ukraine',
      'Vietnam': '🇻🇳 Vietnam',
      'Poland': '🇵🇱 Poland',
      'Hungary': '🇭🇺 Hungary',
      'Czech Republic': '🇨🇿 Czech Republic',
      'Romania': '🇷🇴 Romania',
      'Greece': '🇬🇷 Greece',
      'Italy': '🇮🇹 Italy',
      'Brazil': '🇧🇷 Brazil',
      'Argentina': '🇦🇷 Argentina',
      'Chile': '🇨🇱 Chile',
      'Colombia': '🇨🇴 Colombia',
      'Thailand': '🇹🇭 Thailand',
      'Indonesia': '🇮🇩 Indonesia',
      'Philippines': '🇵🇭 Philippines',
      'South Korea': '🇰🇷 South Korea',
      'Taiwan': '🇹🇼 Taiwan',
      'Hong Kong': '🇭🇰 Hong Kong',
      'Pakistan': '🇵🇰 Pakistan',
      'Bangladesh': '🇧🇩 Bangladesh',
      'Turkey': '🇹🇷 Turkey',
      'Saudi Arabia': '🇸🇦 Saudi Arabia',
      'United Arab Emirates': '🇦🇪 United Arab Emirates',
      'Israel': '🇮🇱 Israel',
      'Egypt': '🇪🇬 Egypt',
      'Nigeria': '🇳🇬 Nigeria',
      'Kenya': '🇰🇪 Kenya',
      'Peru': '🇵🇪 Peru',
      'Bulgaria': '🇧🇬 Bulgaria'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };
  const [apps, setApps] = useState([]);
  const [selectedApp, setSelectedApp] = useState('');
  const [agentStats, setAgentStats] = useState([]);
  const [countryStats, setCountryStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [countryLoading, setCountryLoading] = useState(false);
  const [error, setError] = useState(null);
  const [countryError, setCountryError] = useState(null);
  const [timeFilter, setTimeFilter] = useState('last_30_days'); // Default to last 30 days
  const [customDateRange, setCustomDateRange] = useState({ start: '', end: '' });
  const [showCustomDatePicker, setShowCustomDatePicker] = useState(false);
  const [appDropdownOpen, setAppDropdownOpen] = useState(false);
  const appDropdownRef = useRef(null);

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

  // Fetch available apps on component mount
  useEffect(() => {
    fetchApps();
  }, []);

  // Fetch agent stats and country stats when selected app or filter changes
  useEffect(() => {
    if (selectedApp) {
      fetchAgentStats(selectedApp);
      fetchCountryStats(selectedApp);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedApp, timeFilter, customDateRange.start, customDateRange.end]);

  const fetchApps = async () => {
    try {
      const response = await fetch('/backend/api/apps.php');
      if (!response.ok) throw new Error('Failed to fetch apps');
      const data = await response.json();
      setApps(data);
      // Don't set a default app - let user choose
    } catch (err) {
      setError('Failed to load apps');
      console.error('Error fetching apps:', err);
    }
  };

  const fetchAgentStats = async (appName) => {
    // Check global cache first
    const cacheKey = `agent_stats_${appName}_${timeFilter}_${customDateRange.start}_${customDateRange.end}`;
    const cachedData = getCachedData(appName, null, cacheKey);
    if (cachedData) {
      console.log('✅ Loading agent stats from global cache:', cacheKey);
      setAgentStats(cachedData);
      setLoading(false);
      setError(null);
      return;
    }

    setLoading(true);
    setError(null);
    try {
      // Add cache-busting for real-time updates
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/agent-stats.php?app_name=${encodeURIComponent(appName)}&filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch agent stats');
      const data = await response.json();

      // Handle the case where there are reviews but no assignments
      if (data.message === 'no_assignments') {
        setAgentStats([]);
        setError(`📊 ${data.info} You can assign reviews in the Access Review (Tabs) page.`);
      } else {
        setAgentStats(data);
        // Cache the data globally
        setCachedData(appName, data, null, cacheKey);
      }
    } catch (err) {
      setError('Failed to load agent statistics');
      console.error('Error fetching agent stats:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchCountryStats = async (appName) => {
    // Check global cache first
    const cacheKey = `country_stats_${appName}_${timeFilter}_${customDateRange.start}_${customDateRange.end}`;
    const cachedData = getCachedData(appName, null, cacheKey);
    if (cachedData) {
      console.log('✅ Loading country stats from global cache:', cacheKey);
      setCountryStats(cachedData);
      setCountryLoading(false);
      setCountryError(null);
      return;
    }

    setCountryLoading(true);
    setCountryError(null);
    try {
      // Add cache-busting for real-time updates
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/country-stats.php?app_name=${encodeURIComponent(appName)}&filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch country stats');
      const data = await response.json();
      if (data.success) {
        setCountryStats(data.country_stats || []);
        // Cache the data globally
        setCachedData(appName, data.country_stats || [], null, cacheKey);
      } else {
        throw new Error(data.message || 'Failed to fetch country stats');
      }
    } catch (err) {
      setCountryError('Failed to load country statistics');
      console.error('Error fetching country stats:', err);
      setCountryStats([]);
    } finally {
      setCountryLoading(false);
    }
  };

  // Handle app selection and clear old data
  const handleAppSelect = (app) => {
    setSelectedApp(app);
    // Clear old data immediately when app changes
    setAgentStats([]);
    setCountryStats([]);
    setError(null);
    setCountryError(null);
  };

  // Helper function to format app names consistently
  const formatAppName = (appName) => {
    if (!appName) return '';

    const nameMap = {
      'BetterDocs FAQ': 'BetterDocs FAQ',
      'StoreFAQ': 'StoreFAQ',
      'StoreSEO': 'StoreSEO',
      'EasyFlow': 'EasyFlow',
      'TrustSync': 'TrustSync'
    };

    // Return mapped name or apply consistent formatting
    if (nameMap[appName]) {
      return nameMap[appName];
    }

    // For any other app names, apply consistent formatting
    return appName
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ');
  };


  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center shadow-md">
            <BarChart3 className="w-5 h-5 text-white" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Appwise Reviews Dashboard</h1>
            <p className="text-sm text-slate-500">Track and analyze support performance across all apps</p>
          </div>
        </div>
      </div>

      {/* Controls */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-5 space-y-4">
        <div className="flex flex-wrap items-center gap-4">
          {/* App Dropdown */}
          <div className="flex items-center gap-2 flex-1 min-w-[220px]">
            <label className="text-sm font-semibold text-slate-600 whitespace-nowrap flex items-center gap-1">
              <BarChart3 className="w-4 h-4 text-cyan-500" /> Select App:
            </label>
            <div className="relative flex-1" ref={appDropdownRef}>
              <button
                type="button"
                onClick={() => setAppDropdownOpen(o => !o)}
                className="w-full flex items-center gap-2 pl-3 pr-8 py-2 border border-slate-200 rounded-xl text-sm bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-400 cursor-pointer shadow-sm text-left"
              >
                {selectedApp ? (
                  <>
                    <AppIconInline appName={selectedApp} />
                    <span className="flex-1 truncate">{formatAppName(selectedApp)}</span>
                  </>
                ) : (
                  <span className="flex-1 text-slate-400">Select an App to Get Started</span>
                )}
              </button>
              <ChevronDown className={`absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200 ${appDropdownOpen ? 'rotate-180' : ''}`} />
              {appDropdownOpen && (
                <div className="absolute left-0 top-full mt-1 w-full bg-white rounded-xl shadow-2xl border border-slate-100 z-[9999] overflow-hidden">
                  <ul className="py-1 max-h-64 overflow-y-auto">
                    {apps.map((app) => (
                      <li key={app}>
                        <button
                          type="button"
                          onClick={() => { handleAppSelect(app); setAppDropdownOpen(false); }}
                          className={`w-full flex items-center gap-3 px-3 py-2 text-left text-sm transition-colors ${
                            selectedApp === app ? 'bg-cyan-50 text-cyan-700 font-semibold' : 'hover:bg-slate-50 text-slate-700'
                          }`}
                        >
                          <AppIconInline appName={app} />
                          <span className="flex-1 truncate">{formatAppName(app)}</span>
                        </button>
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          </div>

          {/* Time Filter */}
          <div className="inline-flex bg-slate-100 rounded-xl p-1 gap-1">
            {[
              { key: 'last_30_days', label: '📊 Last 30 Days' },
              { key: 'all_time', label: '🏆 All Time' },
              { key: 'custom', label: '📅 Custom' },
            ].map(({ key, label }) => (
              <button
                key={key}
                onClick={() => {
                  if (key === 'custom') {
                    setShowCustomDatePicker(!showCustomDatePicker);
                    if (!showCustomDatePicker) setTimeFilter('custom');
                  } else {
                    setTimeFilter(key);
                    setShowCustomDatePicker(false);
                  }
                }}
                className={`px-3 py-1.5 rounded-lg text-sm font-semibold transition-all whitespace-nowrap ${
                  timeFilter === key
                    ? 'bg-gradient-to-r from-cyan-500 to-emerald-500 text-white shadow-md'
                    : 'text-slate-600 hover:text-slate-800 hover:bg-white'
                }`}
              >
                {label}
              </button>
            ))}
          </div>
        </div>

        {/* Custom Date Picker */}
        {showCustomDatePicker && (
          <div className="bg-slate-50 rounded-xl p-4 border border-slate-200 flex flex-wrap items-end gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-500 mb-1">Start Date</label>
              <input
                type="date"
                value={customDateRange.start}
                onChange={(e) => setCustomDateRange({ ...customDateRange, start: e.target.value })}
                className="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 bg-white"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-500 mb-1">End Date</label>
              <input
                type="date"
                value={customDateRange.end}
                onChange={(e) => setCustomDateRange({ ...customDateRange, end: e.target.value })}
                className="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 bg-white"
              />
            </div>
            <button
              onClick={() => {
                if (customDateRange.start && customDateRange.end) {
                  setTimeFilter('custom');
                  if (selectedApp) { fetchAgentStats(selectedApp); fetchCountryStats(selectedApp); }
                } else { alert('Please select both start and end dates'); }
              }}
              disabled={!customDateRange.start || !customDateRange.end}
              className="px-4 py-2 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white text-sm font-semibold rounded-lg disabled:opacity-40 disabled:cursor-not-allowed transition-all hover:shadow-md"
            >
              Apply Filter
            </button>
            {customDateRange.start && customDateRange.end && (
              <span className="text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                📊 {customDateRange.start} → {customDateRange.end}
              </span>
            )}
          </div>
        )}
      </div>


      {/* Main Content */}
      {!selectedApp ? (
        <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-16 flex flex-col items-center justify-center gap-4 text-center">
          <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center shadow-lg">
            <BarChart3 className="w-10 h-10 text-white" />
          </div>
          <h2 className="text-xl font-bold text-slate-700">Select an App to Get Started</h2>
          <p className="text-slate-500 max-w-md">Choose an app from the dropdown above to view agent and country review statistics</p>
        </div>
      ) : (
        <div className="space-y-6">
          {/* Agent Stats Section */}
          <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
            <div className="flex items-center gap-3 mb-5">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-md">
                <Users className="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 className="text-lg font-bold text-slate-800">Agent Performance</h2>
                <p className="text-xs text-slate-500">{formatAppName(selectedApp)}</p>
              </div>
            </div>

            {loading ? (
              <div className="flex flex-col items-center justify-center py-12 gap-3">
                <div className="w-10 h-10 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin" />
                <p className="text-slate-500 font-medium">Loading agent stats…</p>
              </div>
            ) : error ? (
              <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">{error}</div>
            ) : agentStats.length === 0 ? (
              <div className="flex flex-col items-center py-10 gap-2 text-center">
                <Users className="w-10 h-10 text-slate-300" />
                <p className="text-slate-500 font-medium">No agent stats available</p>
              </div>
            ) : (() => {
              // Merge "From App", "Organic Review", and "Organic" into a single "Organic" entry
              const normalized = agentStats.reduce((acc, stat) => {
                const isOrganicEntry =
                  stat.agent_name === 'Organic' ||
                  stat.agent_name === 'Organic Review' ||
                  stat.agent_name === 'From App';
                if (isOrganicEntry) {
                  const existing = acc.find(a => a.agent_name === 'Organic');
                  if (existing) {
                    existing.review_count += stat.review_count;
                  } else {
                    acc.push({ ...stat, agent_name: 'Organic' });
                  }
                  return acc;
                }
                acc.push(stat);
                return acc;
              }, []);

              const sorted = [...normalized].sort((a, b) => b.review_count - a.review_count);
              const maxCount = sorted[0]?.review_count || 1;
              const topThree = sorted.slice(0, 3);
              const rest = sorted.slice(3);

              const ringColors = [
                'ring-yellow-400',   // 🥇 1st
                'ring-slate-400',    // 🥈 2nd
                'ring-amber-600',    // 🥉 3rd
              ];
              const medals = ['🥇 Top Performer', '🥈 2nd Place', '🥉 3rd Place'];

              const AgentCard = ({ stat, index }) => {
                // All organic variants are already merged into 'Organic' by the normalization above
                const isOrganic = stat.agent_name === 'Organic';
                const agent = isOrganic ? { name: 'Organic', hash: null } : getAgentByName(stat.agent_name);
                const pct = Math.round((stat.review_count / maxCount) * 100);
                const ring = index < 3 ? ringColors[index] : 'ring-teal-200';
                const isTopThree = index < 3;

                return (
                  <div
                    key={stat.agent_name}
                    className={`bg-white rounded-2xl border shadow-sm overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg ${
                      isTopThree ? 'border-slate-200' : 'border-slate-100'
                    }`}
                  >
                    {/* Top accent bar — teal for all */}
                    <div className="h-1.5 bg-gradient-to-r from-teal-400 to-emerald-500 rounded-t-2xl" style={{ width: `${pct}%` }} />
                    <div className="p-4">
                      <div className="flex items-center gap-3 mb-3">
                        {/* Avatar */}
                        {isOrganic ? (
                          <span className={`w-12 h-12 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center shadow ring-2 ring-offset-2 ring-teal-200 flex-shrink-0`}>
                            <ShoppingBag className="w-6 h-6 text-white" />
                          </span>
                        ) : agent?.hash ? (
                          <img
                            src={getGravatarUrl(agent.hash, 80)}
                            alt={stat.agent_name}
                            className={`w-12 h-12 rounded-full object-cover ring-2 ring-offset-2 ${ring} flex-shrink-0`}
                            onError={(e) => {
                              e.target.style.display = 'none';
                              e.target.nextSibling.style.display = 'flex';
                            }}
                          />
                        ) : null}
                        {/* Fallback initial — hidden unless img errors */}
                        {!isOrganic && (
                          <span
                            className={`w-12 h-12 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-lg shadow ring-2 ring-offset-2 ${ring} flex-shrink-0`}
                            style={{ display: 'none' }}
                          >
                            {stat.agent_name?.charAt(0)?.toUpperCase() || '?'}
                          </span>
                        )}

                        {/* Name + badge */}
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-semibold text-slate-800 truncate">
                            {isOrganic ? 'Organic Review' : stat.agent_name}
                          </p>
                          {isOrganic ? (
                            <span className="text-xs font-semibold text-teal-600">🛍️ Store Organic</span>
                          ) : isTopThree ? (
                            <span className="text-xs font-semibold text-amber-600">{medals[index]}</span>
                          ) : null}
                        </div>

                        {/* Review count */}
                        <span className="text-2xl font-bold text-teal-600 tabular-nums">
                          {stat.review_count}
                        </span>
                      </div>

                      {/* Progress bar */}
                      <div className="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div
                          className="h-full bg-gradient-to-r from-teal-400 to-emerald-500 rounded-full transition-all"
                          style={{ width: `${pct}%` }}
                        />
                      </div>
                      <p className="text-xs text-slate-400 mt-1 text-right">{pct}% of top</p>
                    </div>
                  </div>
                );
              };

              return (
                <div className="space-y-5">
                  {/* Top Performers section */}
                  {topThree.length > 0 && (
                    <div>
                      <p className="text-xs font-bold text-amber-600 uppercase tracking-wider mb-3 flex items-center gap-1">
                        🏆 Top Performers
                      </p>
                      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {topThree.map((stat, i) => (
                          <AgentCard key={stat.agent_name} stat={stat} index={i} />
                        ))}
                      </div>
                    </div>
                  )}

                  {/* Divider */}
                  {rest.length > 0 && (
                    <div className="flex items-center gap-3">
                      <div className="flex-1 h-px bg-slate-200" />
                      <span className="text-xs text-slate-400 font-medium">All Agents</span>
                      <div className="flex-1 h-px bg-slate-200" />
                    </div>
                  )}

                  {/* Remaining agents */}
                  {rest.length > 0 && (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                      {rest.map((stat, i) => (
                        <AgentCard key={stat.agent_name} stat={stat} index={i + 3} />
                      ))}
                    </div>
                  )}
                </div>
              );
            })()}
          </div>

          {/* Agent Earnings Section — StoreSEO only */}
          {selectedApp === 'StoreSEO' && (
            <AgentEarnings
              agentStats={agentStats}
              timeFilter={timeFilter}
              customDateRange={customDateRange}
            />
          )}

          {/* Country Stats Section */}
          <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
            <div className="flex items-center gap-3 mb-5">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center shadow-md">
                <Globe className="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 className="text-lg font-bold text-slate-800">Reviews by Country</h2>
                <p className="text-xs text-slate-500">Geographic distribution of reviews</p>
              </div>
            </div>

            {countryLoading ? (
              <div className="flex flex-col items-center justify-center py-12 gap-3">
                <div className="w-10 h-10 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin" />
                <p className="text-slate-500 font-medium">Loading country stats…</p>
              </div>
            ) : countryError ? (
              <div className="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-800">{countryError}</div>
            ) : countryStats.length === 0 ? (
              <div className="flex flex-col items-center py-10 gap-2 text-center">
                <Globe className="w-10 h-10 text-slate-300" />
                <p className="text-slate-500 font-medium">No country data available</p>
              </div>
            ) : (
              <>
                {/* Summary stat tiles */}
                <div className="grid grid-cols-3 gap-3 mb-5">
                  {[
                    { label: 'Countries', value: countryStats.length, color: 'from-cyan-500 to-blue-500' },
                    { label: 'Total Reviews', value: countryStats.reduce((s, c) => s + c.review_count, 0), color: 'from-emerald-500 to-teal-500' },
                    { label: 'Avg / Country', value: countryStats.length > 0 ? Math.round(countryStats.reduce((s, c) => s + c.review_count, 0) / countryStats.length) : 0, color: 'from-violet-500 to-purple-500' },
                  ].map(({ label, value, color }) => (
                    <div key={label} className="bg-white rounded-xl p-4 border border-slate-100 shadow-sm text-center">
                      <p className={`text-2xl font-bold bg-gradient-to-r ${color} bg-clip-text text-transparent`}>{value}</p>
                      <p className="text-xs text-slate-500 font-medium mt-1">{label}</p>
                    </div>
                  ))}
                </div>

                {/* Country cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  {countryStats
                    .sort((a, b) => b.review_count - a.review_count)
                    .map((stat, index) => {
                      const maxCount = countryStats[0]?.review_count || 1;
                      const pct = Math.round((stat.review_count / maxCount) * 100);
                      const countryGrads = [
                        'from-amber-500 to-orange-500',
                        'from-cyan-500 to-blue-500',
                        'from-emerald-500 to-teal-500',
                        'from-violet-500 to-purple-500',
                        'from-rose-500 to-pink-500',
                        'from-indigo-500 to-blue-600',
                      ];
                      const grad = countryGrads[index % countryGrads.length];
                      const badge = index === 0 ? '👑 #1' : index < 3 ? `⭐ Top ${index + 1}` : null;
                      return (
                        <div key={stat.country_name} className="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-all">
                          <div className={`h-1.5 bg-gradient-to-r ${grad}`} style={{ width: `${pct}%` }} />
                          <div className="p-4">
                            <div className="flex items-start justify-between gap-2 mb-2">
                              <p className="text-sm font-semibold text-slate-800 leading-tight">{getCountryName(stat.country_name)}</p>
                              {badge && (
                                <span className={`text-xs font-bold px-2 py-0.5 rounded-full bg-gradient-to-r ${grad} text-white whitespace-nowrap flex-shrink-0`}>{badge}</span>
                              )}
                            </div>
                            <p className={`text-2xl font-bold bg-gradient-to-r ${grad} bg-clip-text text-transparent`}>
                              {stat.review_count}
                            </p>
                            <p className="text-xs text-slate-400">{stat.percentage}% · {
                              stat.percentage >= 20 ? 'Major Market' :
                              stat.percentage >= 10 ? 'Significant' :
                              stat.percentage >= 5 ? 'Growing' : 'Emerging'
                            }</p>
                            <div className="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                              <div className={`h-full bg-gradient-to-r ${grad} rounded-full`} style={{ width: `${pct}%` }} />
                            </div>
                          </div>
                        </div>
                      );
                    })}
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default ReviewCount;

