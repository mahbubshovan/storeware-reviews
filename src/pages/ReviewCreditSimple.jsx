import { useState, useEffect, useRef } from 'react';
import { Users, BarChart3, Star, Calendar, ChevronDown, RefreshCw, Check, ShoppingBag, LayoutGrid } from 'lucide-react';
import { getGravatarUrl, getAgentByName } from '../config/agents';
import { getAppIcon } from '../config/appConfig';

// Resolve agent config — maps "From App" to Organic
const resolveAgent = (agentName) => {
  if (!agentName) return null;
  if (agentName === 'From App' || agentName === 'Organic') return { name: 'Organic', hash: null };
  return getAgentByName(agentName) || null;
};

// Avatar component — always renders exactly ONE element (no Fragment) so flex layouts work correctly
const AgentAvatarImg = ({ agentName, sizeClass = 'w-9 h-9', ringClass = 'ring-2 ring-teal-100' }) => {
  const [imgError, setImgError] = useState(false);
  const agent = resolveAgent(agentName);

  const fallback = (
    <span className={`${sizeClass} rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center flex-shrink-0 ${ringClass}`}>
      <ShoppingBag className="w-4 h-4 text-white" />
    </span>
  );

  if (!agent || agent.hash === null || imgError) return fallback;

  return (
    <img
      src={getGravatarUrl(agent.hash, 80)}
      alt={agentName}
      className={`${sizeClass} rounded-full object-cover flex-shrink-0 ${ringClass}`}
      onError={() => setImgError(true)}
    />
  );
};

const ORGANIC_AGENTS = new Set(['Organic', 'Organic Review', 'From App']);

// App icon card — shows real Shopify CDN icon with a gradient letter fallback
const AppIconCard = ({ appName, reviewCount, pct, grad, iconUrl, initial, showEarnings }) => {
  const [imgError, setImgError] = useState(false);
  return (
    <div className="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
      <div className={`h-1.5 bg-gradient-to-r ${grad}`} style={{ width: `${pct}%` }} />
      <div className="p-4">
        <div className="flex items-center gap-2 mb-2">
          {iconUrl && !imgError ? (
            <img
              src={iconUrl}
              alt={appName}
              className="w-10 h-10 rounded-xl object-cover shadow-sm border border-slate-100 flex-shrink-0"
              onError={() => setImgError(true)}
            />
          ) : (
            <span className={`w-10 h-10 rounded-xl bg-gradient-to-br ${grad} flex items-center justify-center text-white text-sm font-bold flex-shrink-0`}>
              {initial}
            </span>
          )}
          <p className="text-sm font-semibold text-slate-700 truncate">{appName}</p>
        </div>
        <p className={`text-2xl font-bold bg-gradient-to-r ${grad} bg-clip-text text-transparent`}>
          {reviewCount}
        </p>
        <p className="text-xs text-slate-400">{reviewCount === 1 ? 'Review Earned' : 'Reviews Earned'}</p>

        {/* Earnings strip */}
        <div className="border-t border-slate-100 mt-3 pt-3">
          {showEarnings && appName === 'StoreSEO' ? (
            <div className="flex items-center justify-between gap-2">
              <div className="min-w-0">
                <p className="text-xs font-semibold text-slate-600 flex items-center gap-1">
                  💰 StoreSEO earnings
                </p>
                <p className="text-xs text-emerald-600 font-medium mt-0.5">
                  {reviewCount} reviews × $20
                </p>
              </div>
              <span className="text-sm font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 whitespace-nowrap flex-shrink-0">
                ${reviewCount * 20}
              </span>
            </div>
          ) : (
            <p className="text-xs text-slate-400">No earnings tracked for this app</p>
          )}
        </div>
      </div>
    </div>
  );
};

const ReviewCreditSimple = () => {
  const [timeFilter, setTimeFilter] = useState('last_30_days');
  const [agents, setAgents] = useState([]);
  const [selectedAgent, setSelectedAgent] = useState(null);
  const [selectedAgentDetails, setSelectedAgentDetails] = useState(null);
  const [loading, setLoading] = useState(false);
  const [detailsLoading, setDetailsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [customDateRange, setCustomDateRange] = useState({ start: '', end: '' });
  const [showCustomDatePicker, setShowCustomDatePicker] = useState(false);
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const dropdownRef = useRef(null);

  // Fetch all agents
  const fetchAgents = async () => {
    console.log('Fetching all agents with filter:', timeFilter);
    setLoading(true);
    setError(null);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/agent-review-stats.php?filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch agents');
      const data = await response.json();
      console.log('Agents loaded:', data);

      if (data.message === 'no_agents') {
        setAgents([]);
        setError('No agents have been assigned reviews yet. You can assign reviews in the Access Review page.');
      } else {
        setAgents(data);
      }
    } catch (err) {
      console.error('Error fetching agents:', err);
      setError('Failed to load agent statistics');
      setAgents([]);
    } finally {
      setLoading(false);
    }
  };

  // Fetch details for a specific agent
  const fetchAgentDetails = async (agentName) => {
    console.log('Fetching details for agent:', agentName);
    setDetailsLoading(true);
    setSelectedAgentDetails(null);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/agent-review-stats.php?agent_name=${encodeURIComponent(agentName)}&filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch agent details');
      const data = await response.json();
      console.log('Agent details loaded:', data);
      setSelectedAgentDetails(data);
    } catch (err) {
      console.error('Error fetching agent details:', err);
      setError('Failed to load agent details');
    } finally {
      setDetailsLoading(false);
    }
  };

  // Load agents on component mount and when filters change
  useEffect(() => {
    console.log('Fetching agents - Filter:', timeFilter, 'Custom Range:', customDateRange);
    fetchAgents();
    // If an agent is already selected, re-fetch their details with the new filter
    if (selectedAgent) {
      fetchAgentDetails(selectedAgent);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [timeFilter, customDateRange.start, customDateRange.end]);

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
        setDropdownOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Handle agent selection
  const handleAgentSelect = (agentName) => {
    console.log('Agent selected:', agentName);
    setSelectedAgent(agentName);
    fetchAgentDetails(agentName);
  };

  const filterLabel = timeFilter === 'last_30_days' ? 'Last 30 Days'
    : timeFilter === 'custom' && customDateRange.start && customDateRange.end
      ? `${customDateRange.start} → ${customDateRange.end}`
      : 'All Time';

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center shadow-md">
            <Users className="w-5 h-5 text-white" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Agent Reviews Dashboard</h1>
            <p className="text-sm text-slate-500">See which agents earned the most reviews across all apps</p>
          </div>
        </div>
      </div>

      {/* Controls */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-5 space-y-4">
        <div className="flex flex-wrap items-center gap-4">
          {/* Agent Dropdown */}
          <div className="flex items-center gap-2 flex-1 min-w-[220px]">
            <label className="text-sm font-semibold text-slate-600 whitespace-nowrap flex items-center gap-1">
              <Users className="w-4 h-4 text-cyan-500" /> Select Agent:
            </label>
            <div className="relative flex-1" ref={dropdownRef}>
              {/* Trigger button */}
              <button
                type="button"
                onClick={() => setDropdownOpen(!dropdownOpen)}
                className="w-full flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm text-slate-700 hover:border-teal-400 hover:shadow-md transition-all"
              >
                {selectedAgent ? (
                  <AgentAvatarImg agentName={selectedAgent} sizeClass="w-8 h-8" ringClass="ring-2 ring-teal-200" />
                ) : (
                  <span className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <Users className="w-4 h-4 text-slate-400" />
                  </span>
                )}
                <span className="flex-1 text-left truncate">
                  {selectedAgent || 'Select an agent to view stats'}
                </span>
                <ChevronDown className={`w-4 h-4 text-slate-400 flex-shrink-0 transition-transform duration-200 ${dropdownOpen ? 'rotate-180' : ''}`} />
              </button>

              {/* Dropdown panel */}
              {dropdownOpen && (
                <div className="absolute left-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 z-[9999] min-w-[280px] w-full overflow-hidden">
                  <p className="text-xs text-slate-400 font-medium px-3 pt-3 pb-1">Choose an agent to analyze</p>
                  <ul className="py-1 max-h-72 overflow-y-auto">
                    {agents.map((agent) => {
                      const isSelected = selectedAgent === agent.agent_name;
                      const displayName = agent.agent_name === 'From App' ? 'Organic Review' : agent.agent_name;
                      return (
                        <li key={agent.agent_name}>
                          <button
                            type="button"
                            onClick={() => { handleAgentSelect(agent.agent_name); setDropdownOpen(false); }}
                            className={`w-full flex items-center gap-3 px-3 py-2.5 transition-colors ${isSelected ? 'bg-teal-50' : 'hover:bg-slate-50'}`}
                          >
                            <AgentAvatarImg
                              agentName={agent.agent_name}
                              sizeClass="w-9 h-9"
                              ringClass={`ring-2 ring-offset-1 ${isSelected ? 'ring-teal-400' : 'ring-teal-100'}`}
                            />
                            <span className={`text-sm font-medium flex-1 text-left ${isSelected ? 'text-teal-700' : 'text-slate-700'}`}>
                              {displayName}
                            </span>
                            <span className="text-xs text-slate-400 bg-slate-100 rounded-full px-2 py-0.5 font-medium tabular-nums flex-shrink-0">
                              {agent.review_count}
                            </span>
                            {isSelected && <Check className="w-4 h-4 text-teal-500 flex-shrink-0" />}
                          </button>
                        </li>
                      );
                    })}
                  </ul>
                </div>
              )}
            </div>
          </div>

          {/* Time Filter Tabs */}
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
                  fetchAgents();
                  if (selectedAgent) fetchAgentDetails(selectedAgent);
                } else {
                  alert('Please select both start and end dates');
                }
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
      {/* Content Panel */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6 min-h-[400px]">
        {loading && !selectedAgent ? (
          <div className="flex flex-col items-center justify-center py-20 gap-3">
            <div className="w-10 h-10 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin" />
            <p className="text-slate-500 font-medium">Loading agent statistics…</p>
          </div>
        ) : detailsLoading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-3">
            <div className="w-10 h-10 border-4 border-teal-500 border-t-transparent rounded-full animate-spin" />
            <p className="text-slate-500 font-medium">Loading agent details…</p>
          </div>
        ) : error && agents.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 gap-3 text-center">
            <span className="text-5xl">⚠️</span>
            <h2 className="text-xl font-bold text-slate-700">No Data Available</h2>
            <p className="text-slate-500 max-w-md">{error}</p>
          </div>
        ) : error && selectedAgent && !selectedAgentDetails ? (
          <div className="flex flex-col items-center justify-center py-20 gap-3 text-center">
            <span className="text-5xl">❌</span>
            <h2 className="text-xl font-bold text-slate-700">Failed to Load Agent Details</h2>
            <p className="text-slate-500">{error}</p>
            <button
              onClick={() => { setError(null); if (selectedAgent) fetchAgentDetails(selectedAgent); }}
              className="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all"
            >
              <RefreshCw className="w-4 h-4" /> Retry
            </button>
          </div>
        ) : !selectedAgent ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4 text-center">
            <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center shadow-lg">
              <Users className="w-10 h-10 text-white" />
            </div>
            <h2 className="text-xl font-bold text-slate-700">Choose an Agent to Analyze</h2>
            <p className="text-slate-500 max-w-md">Select an agent from the dropdown above to view their review statistics across all apps</p>
          </div>
        ) : selectedAgent && selectedAgentDetails ? (
          <>
            {/* Agent header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
              <div className="flex items-center gap-4">
                <div className="relative flex-shrink-0">
                  <AgentAvatarImg
                    agentName={selectedAgentDetails.agent_name}
                    sizeClass="w-16 h-16"
                    ringClass="ring-4 ring-teal-200 ring-offset-2"
                  />
                  <span className="absolute bottom-0.5 right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-400 border-2 border-white" />
                </div>
                <div>
                  <h2 className="text-2xl font-bold text-slate-800">{selectedAgentDetails.agent_name}</h2>
                  <p className="text-sm text-slate-500">{filterLabel} Performance</p>
                </div>
              </div>
            </div>

            {/* Stat cards */}
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
              {[
                { label: 'Reviews Earned', value: selectedAgentDetails.total_reviews, icon: <Star className="w-5 h-5 text-teal-500" />, iconBg: 'bg-teal-50', color: 'from-teal-500 to-cyan-500' },
                { label: 'Apps Supported', value: selectedAgentDetails.by_app.length, icon: <LayoutGrid className="w-5 h-5 text-violet-500" />, iconBg: 'bg-purple-50', color: 'from-violet-500 to-purple-500' },
                { label: 'Time Period', value: filterLabel, icon: <Calendar className="w-5 h-5 text-blue-500" />, iconBg: 'bg-blue-50', color: 'from-blue-500 to-indigo-500' },
              ].map(({ label, value, icon, iconBg, color }) => (
                <div key={label} className="bg-white rounded-xl p-4 border border-slate-100 shadow-sm">
                  <div className={`w-8 h-8 rounded-lg ${iconBg} flex items-center justify-center mb-3`}>
                    {icon}
                  </div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">{label}</p>
                  <p className={`text-2xl font-bold bg-gradient-to-r ${color} bg-clip-text text-transparent leading-tight`}>{value}</p>
                </div>
              ))}
            </div>

            {/* Reviews Earned by App */}
            <div className="bg-slate-50 rounded-xl p-5">
              <h3 className="text-base font-bold text-slate-700 mb-4">Reviews Earned by App</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {selectedAgentDetails.by_app.map((app, index) => {
                  const maxCount = Math.max(...selectedAgentDetails.by_app.map(a => a.review_count));
                  const pct = maxCount > 0 ? Math.round((app.review_count / maxCount) * 100) : 0;
                  const gradients = ['from-cyan-500 to-blue-500','from-emerald-500 to-teal-500','from-violet-500 to-purple-500','from-amber-500 to-orange-500','from-rose-500 to-pink-500','from-indigo-500 to-blue-500'];
                  const grad = gradients[index % gradients.length];
                  const iconUrl = getAppIcon(app.app_name);
                  const initial = app.app_name?.charAt(0)?.toUpperCase() || '?';
                  const isOrganic = ORGANIC_AGENTS.has(selectedAgentDetails.agent_name);
                  return (
                    <AppIconCard
                      key={index}
                      appName={app.app_name}
                      reviewCount={app.review_count}
                      pct={pct}
                      grad={grad}
                      iconUrl={iconUrl}
                      initial={initial}
                      showEarnings={!isOrganic}
                    />
                  );
                })}
              </div>
            </div>
          </>
        ) : null}
      </div>
    </div>
  );
};

export default ReviewCreditSimple;
