import { useState, useEffect, useCallback } from 'react';
import {
  Users, Package, Store, LayoutGrid, RefreshCw, TrendingUp,
  CalendarDays, CalendarRange, Infinity as InfinityIcon,
} from 'lucide-react';
import { APPS, getAppIcon } from '../config/appConfig';
import { AGENTS } from '../config/agents';
import MemberSelect from './MemberSelect';

const API = '/backend/api/sales-tracker.php';
const SALESPEOPLE = AGENTS.filter((a) => a.name !== 'Organic').map((a) => a.name);

const GRADIENTS = [
  'from-cyan-500 to-blue-500',
  'from-emerald-500 to-teal-500',
  'from-violet-500 to-purple-500',
  'from-amber-500 to-orange-500',
  'from-rose-500 to-pink-500',
  'from-indigo-500 to-blue-500',
];

const currentMonth = () => {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
};

const inputCls =
  'w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-white';

// App icon + gradient-letter fallback
const AppMini = ({ appName }) => {
  const [err, setErr] = useState(false);
  const icon = getAppIcon(appName);
  return icon && !err ? (
    <img src={icon} alt={appName} onError={() => setErr(true)}
      className="w-8 h-8 rounded-lg object-cover border border-slate-100 flex-shrink-0" />
  ) : (
    <span className="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
      {appName?.charAt(0)?.toUpperCase() || '?'}
    </span>
  );
};

const TeamPerformanceDashboard = () => {
  const [member, setMember] = useState('');           // '' = all team members
  const [periodMode, setPeriodMode] = useState('month'); // month | custom | all
  const [month, setMonth] = useState(currentMonth());
  const [start, setStart] = useState('');
  const [end, setEnd] = useState('');
  const [summaryMode, setSummaryMode] = useState('daily'); // daily | monthly

  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchPerformance = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params = new URLSearchParams({ action: 'performance' });
      if (member) params.set('salesperson', member);
      if (periodMode === 'month' && month) params.set('month', month);
      if (periodMode === 'custom') {
        if (start) params.set('start_date', start);
        if (end) params.set('end_date', end);
      }
      const res = await fetch(`${API}?${params.toString()}`);
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'Failed to load performance');
      setData(json);
    } catch (err) {
      console.error('TeamPerformance load error:', err);
      setError(err.message || 'Failed to load performance data');
      setData(null);
    } finally {
      setLoading(false);
    }
  }, [member, periodMode, month, start, end]);

  useEffect(() => {
    const t = setTimeout(fetchPerformance, 250);
    return () => clearTimeout(t);
  }, [fetchPerformance]);

  // Default the summary granularity to monthly when viewing all-time.
  useEffect(() => {
    setSummaryMode(periodMode === 'all' ? 'monthly' : 'daily');
  }, [periodMode]);

  const totals = data?.totals || { total_apps_sold: 0, total_unique_stores: 0, apps_covered: 0 };
  const appBreakdown = data?.app_breakdown || [];
  const series = summaryMode === 'daily' ? (data?.daily_summary || []) : (data?.monthly_summary || []);

  const maxAppCount = Math.max(1, ...appBreakdown.map((a) => Number(a.count)));
  const maxSeries = Math.max(1, ...series.map((s) => Number(s.count)));

  const periodLabel =
    periodMode === 'all' ? 'All time'
      : periodMode === 'month' ? month
        : (start || end) ? `${start || '…'} → ${end || '…'}` : 'Custom range';

  const memberLabel = member || 'All team members';

  const statCards = [
    { label: 'Total Apps Sold', value: totals.total_apps_sold, icon: <Package className="w-5 h-5 text-teal-500" />, bg: 'bg-teal-50', grad: 'from-teal-500 to-cyan-500' },
    { label: 'Unique Stores', value: totals.total_unique_stores, icon: <Store className="w-5 h-5 text-violet-500" />, bg: 'bg-purple-50', grad: 'from-violet-500 to-purple-500' },
    { label: 'Apps Covered', value: totals.apps_covered, icon: <LayoutGrid className="w-5 h-5 text-amber-500" />, bg: 'bg-amber-50', grad: 'from-amber-500 to-orange-500' },
  ];

  return (
    <div className="space-y-6">
      {/* Controls */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-5 space-y-4">
        <div className="flex flex-col lg:flex-row lg:items-end gap-4">
          {/* Team member */}
          <div className="flex-1 min-w-[200px]">
            <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
              <Users className="w-3.5 h-3.5 text-teal-500" /> Team Member
            </label>
            <MemberSelect value={member} onChange={setMember} options={SALESPEOPLE} allLabel="All team members" />
          </div>

          {/* Period mode segmented */}
          <div className="flex-shrink-0">
            <label className="block text-xs font-semibold text-slate-600 mb-1">Period</label>
            <div className="inline-flex bg-slate-100 rounded-xl p-1 gap-1">
              {[
                { key: 'month', label: 'Month', icon: <CalendarDays className="w-3.5 h-3.5" /> },
                { key: 'custom', label: 'Custom', icon: <CalendarRange className="w-3.5 h-3.5" /> },
                { key: 'all', label: 'All time', icon: <InfinityIcon className="w-3.5 h-3.5" /> },
              ].map(({ key, label, icon }) => (
                <button key={key} onClick={() => setPeriodMode(key)}
                  className={`px-3 py-1.5 rounded-lg text-sm font-semibold transition-all inline-flex items-center gap-1.5 whitespace-nowrap ${
                    periodMode === key
                      ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md'
                      : 'text-slate-600 hover:text-slate-800 hover:bg-white'
                  }`}>
                  {icon} {label}
                </button>
              ))}
            </div>
          </div>

          {/* Period picker */}
          {periodMode === 'month' && (
            <div className="flex-shrink-0">
              <label className="block text-xs font-semibold text-slate-600 mb-1">Select month</label>
              <input type="month" value={month} onChange={(e) => setMonth(e.target.value)} className={inputCls} />
            </div>
          )}
          {periodMode === 'custom' && (
            <div className="flex items-end gap-2 flex-shrink-0 min-w-0">
              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1">From</label>
                <input type="date" value={start} onChange={(e) => setStart(e.target.value)}
                  className={`${inputCls} min-w-0`} />
              </div>
              <span className="text-slate-400 pb-2">–</span>
              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1">To</label>
                <input type="date" value={end} onChange={(e) => setEnd(e.target.value)}
                  className={`${inputCls} min-w-0`} />
              </div>
            </div>
          )}
        </div>

        {/* Context strip */}
        <div className="flex flex-wrap items-center gap-2 text-xs">
          <span className="inline-flex items-center gap-1 bg-teal-50 text-teal-700 border border-teal-100 rounded-full px-3 py-1 font-medium">
            <Users className="w-3.5 h-3.5" /> {memberLabel}
          </span>
          <span className="inline-flex items-center gap-1 bg-slate-50 text-slate-600 border border-slate-200 rounded-full px-3 py-1 font-medium">
            <CalendarDays className="w-3.5 h-3.5" /> {periodLabel}
          </span>
        </div>
      </div>

      {loading ? (
        <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg flex flex-col items-center justify-center py-20 gap-3">
          <div className="w-10 h-10 border-4 border-teal-500 border-t-transparent rounded-full animate-spin" />
          <p className="text-slate-500 font-medium">Loading performance…</p>
        </div>
      ) : error ? (
        <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg flex flex-col items-center justify-center py-20 gap-3 text-center">
          <span className="text-5xl">⚠️</span>
          <h3 className="text-lg font-bold text-slate-700">Couldn’t load performance</h3>
          <p className="text-slate-500 max-w-md">{error}</p>
          <button onClick={fetchPerformance}
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all">
            <RefreshCw className="w-4 h-4" /> Retry
          </button>
        </div>
      ) : (
        <>
          {/* Stat cards */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {statCards.map(({ label, value, icon, bg, grad }) => (
              <div key={label} className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div className={`w-9 h-9 rounded-lg ${bg} flex items-center justify-center mb-3`}>{icon}</div>
                <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">{label}</p>
                <p className={`text-3xl font-bold bg-gradient-to-r ${grad} bg-clip-text text-transparent leading-tight tabular-nums`}>
                  {value}
                </p>
              </div>
            ))}
          </div>

          {totals.total_apps_sold === 0 ? (
            <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg flex flex-col items-center justify-center py-16 gap-4 text-center">
              <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg">
                <TrendingUp className="w-8 h-8 text-white" />
              </div>
              <h3 className="text-lg font-bold text-slate-700">No sales in this period</h3>
              <p className="text-slate-500 max-w-md">
                {member ? `${member} has no recorded sales for ${periodLabel}.` : `No sales recorded for ${periodLabel}.`}
                {' '}Try a different member or period.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* App-wise breakdown */}
              <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-6">
                <h3 className="text-base font-bold text-slate-700 mb-4 flex items-center gap-2">
                  <LayoutGrid className="w-4 h-4 text-teal-500" /> App-wise Breakdown
                </h3>
                <div className="space-y-3">
                  {appBreakdown.map((a, i) => {
                    const grad = GRADIENTS[i % GRADIENTS.length];
                    const pct = Math.round((Number(a.count) / maxAppCount) * 100);
                    return (
                      <div key={a.app_name} className="flex items-center gap-3">
                        <AppMini appName={a.app_name} />
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center justify-between mb-1">
                            <span className="text-sm font-medium text-slate-700 truncate">{a.app_name}</span>
                            <span className="text-xs text-slate-500 flex-shrink-0 ml-2">
                              <span className="font-bold text-slate-700 tabular-nums">{a.count}</span> sold
                              <span className="text-slate-400"> · {a.unique_stores} {Number(a.unique_stores) === 1 ? 'store' : 'stores'}</span>
                            </span>
                          </div>
                          <div className="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div className={`h-full bg-gradient-to-r ${grad} rounded-full transition-all`} style={{ width: `${pct}%` }} />
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Daily / Monthly summary */}
              <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-6">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-base font-bold text-slate-700 flex items-center gap-2">
                    <TrendingUp className="w-4 h-4 text-teal-500" />
                    {summaryMode === 'daily' ? 'Daily Summary' : 'Monthly Summary'}
                  </h3>
                  <div className="inline-flex bg-slate-100 rounded-lg p-0.5 gap-0.5">
                    {['daily', 'monthly'].map((m) => (
                      <button key={m} onClick={() => setSummaryMode(m)}
                        className={`px-2.5 py-1 rounded-md text-xs font-semibold capitalize transition-all ${
                          summaryMode === m ? 'bg-white text-teal-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'
                        }`}>
                        {m}
                      </button>
                    ))}
                  </div>
                </div>

                {series.length === 0 ? (
                  <p className="text-sm text-slate-400 text-center py-10">No data for this view.</p>
                ) : (
                  <div className="flex items-end gap-2 overflow-x-auto pb-1" style={{ minHeight: '12rem' }}>
                    {series.map((s) => {
                      const key = summaryMode === 'daily' ? s.date : s.month;
                      // Explicit px height (percentage heights need a fixed-height parent, which a
                      // flex column doesn't provide) — scale to a 150px ceiling with a visible floor.
                      const h = Math.max(6, Math.round((Number(s.count) / maxSeries) * 150));
                      const short = summaryMode === 'daily' ? key.slice(5) : key; // MM-DD or YYYY-MM
                      return (
                        <div key={key} className="flex flex-col items-center justify-end gap-1 flex-shrink-0 min-w-[34px]" title={`${key}: ${s.count}`}>
                          <span className="text-xs font-bold text-slate-600 tabular-nums">{s.count}</span>
                          <div className="w-7 bg-gradient-to-t from-emerald-500 to-teal-400 rounded-t-md transition-all"
                            style={{ height: `${h}px` }} />
                          <span className="text-[10px] text-slate-400 tabular-nums whitespace-nowrap">{short}</span>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default TeamPerformanceDashboard;
