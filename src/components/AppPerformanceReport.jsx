import { useState, useEffect, useCallback } from 'react';
import {
  Package, Store, Trophy, Search, RefreshCw, ExternalLink, Calendar,
  CalendarRange, ChevronLeft, ChevronRight, ArrowUp, ArrowDown, ArrowUpDown, Mail,
} from 'lucide-react';
import { APPS, getAppIcon } from '../config/appConfig';
import MemberAvatar from './MemberAvatar';
import AppSelect from './AppSelect';

const API = '/backend/api/sales-tracker.php';
const PAGE_SIZE = 10;

const RANK_STYLES = [
  { badge: 'bg-gradient-to-br from-amber-400 to-yellow-500 text-white', ring: 'ring-amber-200' },
  { badge: 'bg-gradient-to-br from-slate-300 to-slate-400 text-white', ring: 'ring-slate-200' },
  { badge: 'bg-gradient-to-br from-orange-400 to-amber-600 text-white', ring: 'ring-orange-200' },
];

const inputCls =
  'w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-white';

const AppIcon = ({ appName }) => {
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

const AppPerformanceReport = () => {
  const [app, setApp] = useState(APPS[0]?.name || '');
  const [rangeMode, setRangeMode] = useState('last_30_days'); // last_30_days | custom
  const [start, setStart] = useState('');
  const [end, setEnd] = useState('');
  const [search, setSearch] = useState('');
  const [sortBy, setSortBy] = useState('sale_date');
  const [order, setOrder] = useState('desc');
  const [page, setPage] = useState(1);

  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchReport = useCallback(async () => {
    if (!app) { setData(null); return; }
    setLoading(true);
    setError(null);
    try {
      const params = new URLSearchParams({
        action: 'app_report',
        app_name: app,
        range: rangeMode,
        sort: sortBy,
        order,
        page: String(page),
        limit: String(PAGE_SIZE),
      });
      if (rangeMode === 'custom') {
        if (start) params.set('start_date', start);
        if (end) params.set('end_date', end);
      }
      if (search.trim()) params.set('search', search.trim());

      const res = await fetch(`${API}?${params.toString()}`);
      const json = await res.json();
      if (!json.success) throw new Error(json.error || 'Failed to load report');
      setData(json);
    } catch (err) {
      console.error('AppPerformanceReport load error:', err);
      setError(err.message || 'Failed to load app report');
      setData(null);
    } finally {
      setLoading(false);
    }
  }, [app, rangeMode, start, end, search, sortBy, order, page]);

  // Reset to first page whenever the report scope / search / sort changes.
  useEffect(() => { setPage(1); }, [app, rangeMode, start, end, search, sortBy, order]);

  useEffect(() => {
    const t = setTimeout(fetchReport, 250);
    return () => clearTimeout(t);
  }, [fetchReport]);

  const toggleSort = (col) => {
    if (sortBy === col) {
      setOrder((o) => (o === 'asc' ? 'desc' : 'asc'));
    } else {
      setSortBy(col);
      setOrder(col === 'sale_date' ? 'desc' : 'asc');
    }
  };

  const SortHeader = ({ col, children, className = '' }) => {
    const active = sortBy === col;
    const Icon = !active ? ArrowUpDown : order === 'asc' ? ArrowUp : ArrowDown;
    return (
      <th className={`px-6 py-3 ${className}`}>
        <button onClick={() => toggleSort(col)}
          className={`inline-flex items-center gap-1 uppercase tracking-wide text-xs font-semibold transition-colors ${active ? 'text-teal-600' : 'text-slate-500 hover:text-slate-700'}`}>
          {children}
          <Icon className="w-3.5 h-3.5" />
        </button>
      </th>
    );
  };

  const summary = data?.summary || { total_sales: 0, unique_stores: 0 };
  const ranking = data?.ranking || [];
  const records = data?.records || { items: [], total: 0, page: 1, limit: PAGE_SIZE, pages: 0 };
  const topSeller = ranking[0]?.salesperson || '—';
  const maxRank = Math.max(1, ...ranking.map((r) => Number(r.count)));

  const rangeLabel = rangeMode === 'last_30_days'
    ? 'Last 30 Days'
    : (start || end) ? `${start || '…'} → ${end || '…'}` : 'Custom range';

  const summaryCards = [
    { label: 'Total Sales', value: summary.total_sales, icon: <Package className="w-5 h-5 text-teal-500" />, bg: 'bg-teal-50', grad: 'from-teal-500 to-cyan-500' },
    { label: 'Unique Stores', value: summary.unique_stores, icon: <Store className="w-5 h-5 text-violet-500" />, bg: 'bg-purple-50', grad: 'from-violet-500 to-purple-500' },
    { label: 'Top Seller', value: topSeller, icon: <Trophy className="w-5 h-5 text-amber-500" />, bg: 'bg-amber-50', grad: 'from-amber-500 to-orange-500' },
  ];

  return (
    <div className="space-y-6">
      {/* Controls */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-5">
        <div className="flex flex-col lg:flex-row lg:items-end gap-4">
          {/* App select */}
          <div className="flex-1 min-w-[200px]">
            <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
              <Package className="w-3.5 h-3.5 text-teal-500" /> App Name
            </label>
            <AppSelect value={app} onChange={setApp} />
          </div>

          {/* Range segmented */}
          <div className="flex-shrink-0">
            <label className="block text-xs font-semibold text-slate-600 mb-1">Report Range</label>
            <div className="inline-flex bg-slate-100 rounded-xl p-1 gap-1">
              {[
                { key: 'last_30_days', label: 'Last 30 Days', icon: <Calendar className="w-3.5 h-3.5" /> },
                { key: 'custom', label: 'Custom', icon: <CalendarRange className="w-3.5 h-3.5" /> },
              ].map(({ key, label, icon }) => (
                <button key={key} onClick={() => setRangeMode(key)}
                  className={`px-3 py-1.5 rounded-lg text-sm font-semibold transition-all inline-flex items-center gap-1.5 whitespace-nowrap ${
                    rangeMode === key
                      ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md'
                      : 'text-slate-600 hover:text-slate-800 hover:bg-white'
                  }`}>
                  {icon} {label}
                </button>
              ))}
            </div>
          </div>

          {/* Custom date pickers */}
          {rangeMode === 'custom' && (
            <div className="flex items-end gap-2 flex-shrink-0 min-w-0">
              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1">From</label>
                <input type="date" value={start} onChange={(e) => setStart(e.target.value)} className={`${inputCls} min-w-0`} />
              </div>
              <span className="text-slate-400 pb-2">–</span>
              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1">To</label>
                <input type="date" value={end} onChange={(e) => setEnd(e.target.value)} className={`${inputCls} min-w-0`} />
              </div>
            </div>
          )}
        </div>

        {/* Context strip */}
        <div className="flex flex-wrap items-center gap-2 text-xs mt-4">
          <span className="inline-flex items-center gap-1.5 bg-teal-50 text-teal-700 border border-teal-100 rounded-full px-3 py-1 font-medium">
            <AppIcon appName={app} /> {app}
          </span>
          <span className="inline-flex items-center gap-1 bg-slate-50 text-slate-600 border border-slate-200 rounded-full px-3 py-1 font-medium">
            <Calendar className="w-3.5 h-3.5" /> {rangeLabel}
          </span>
        </div>
      </div>

      {loading && !data ? (
        <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg flex flex-col items-center justify-center py-20 gap-3">
          <div className="w-10 h-10 border-4 border-teal-500 border-t-transparent rounded-full animate-spin" />
          <p className="text-slate-500 font-medium">Loading report…</p>
        </div>
      ) : error ? (
        <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg flex flex-col items-center justify-center py-20 gap-3 text-center">
          <span className="text-5xl">⚠️</span>
          <h3 className="text-lg font-bold text-slate-700">Couldn’t load report</h3>
          <p className="text-slate-500 max-w-md">{error}</p>
          <button onClick={fetchReport}
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all">
            <RefreshCw className="w-4 h-4" /> Retry
          </button>
        </div>
      ) : (
        <>
          {/* Summary cards */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {summaryCards.map(({ label, value, icon, bg, grad }) => (
              <div key={label} className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div className={`w-9 h-9 rounded-lg ${bg} flex items-center justify-center mb-3`}>{icon}</div>
                <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">{label}</p>
                <p className={`text-3xl font-bold bg-gradient-to-r ${grad} bg-clip-text text-transparent leading-tight truncate`}>
                  {value}
                </p>
              </div>
            ))}
          </div>

          {/* Support member ranking */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-6">
            <h3 className="text-base font-bold text-slate-700 mb-4 flex items-center gap-2">
              <Trophy className="w-4 h-4 text-amber-500" /> Support Member Ranking
              <span className="text-xs font-normal text-slate-400">— by sales for {app}</span>
            </h3>
            {ranking.length === 0 ? (
              <p className="text-sm text-slate-400 text-center py-8">No sales in this period.</p>
            ) : (
              <div className="space-y-2.5">
                {ranking.map((r, i) => {
                  const style = RANK_STYLES[i];
                  const pct = Math.round((Number(r.count) / maxRank) * 100);
                  return (
                    <div key={r.salesperson} className="flex items-center gap-3">
                      <span className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 tabular-nums ${style ? style.badge : 'bg-slate-100 text-slate-500'}`}>
                        {i + 1}
                      </span>
                      <MemberAvatar name={r.salesperson} sizeClass="w-9 h-9" ringClass={`ring-2 ${style ? style.ring : 'ring-slate-100'}`} />
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between mb-1">
                          <span className="text-sm font-semibold text-slate-700 truncate">{r.salesperson}</span>
                          <span className="text-xs text-slate-500 flex-shrink-0 ml-2">
                            <span className="font-bold text-slate-700 tabular-nums">{r.count}</span> sold
                            <span className="text-slate-400"> · {r.unique_stores} {Number(r.unique_stores) === 1 ? 'store' : 'stores'}</span>
                          </span>
                        </div>
                        <div className="h-2 bg-slate-100 rounded-full overflow-hidden">
                          <div className="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all" style={{ width: `${pct}%` }} />
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          {/* Records table */}
          <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg overflow-hidden">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
              <h3 className="text-base font-bold text-slate-700 flex items-center gap-2">
                {app} Sales
                <span className="text-xs font-medium text-slate-400 bg-slate-100 rounded-full px-2 py-0.5 tabular-nums">
                  {records.total}
                </span>
              </h3>
              <div className="relative sm:w-64">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input type="text" value={search} onChange={(e) => setSearch(e.target.value)}
                  placeholder="Search store or member…" className={`${inputCls} pl-9`} />
              </div>
            </div>

            {records.items.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-16 gap-3 text-center">
                <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg">
                  <Package className="w-8 h-8 text-white" />
                </div>
                <h4 className="text-lg font-bold text-slate-700">No sales found</h4>
                <p className="text-slate-500 max-w-md">
                  {search ? 'No records match your search.' : `No ${app} sales for ${rangeLabel}.`}
                </p>
              </div>
            ) : (
              <>
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[720px] text-sm">
                    <thead>
                      <tr className="text-left bg-slate-50/70">
                        <SortHeader col="store_name">Store Name</SortHeader>
                        <th className="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Customer</th>
                        <SortHeader col="salesperson">Salesperson</SortHeader>
                        <SortHeader col="sale_date">Sale Date</SortHeader>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {records.items.map((rec) => (
                        <tr key={rec.id} className="hover:bg-slate-50/60 transition-colors">
                          <td className="px-6 py-3">
                            <div className="flex flex-col">
                              <span className="font-medium text-slate-800">{rec.store_name}</span>
                              {rec.store_link && (
                                <a href={rec.store_link} target="_blank" rel="noreferrer"
                                  className="inline-flex items-center gap-1 text-xs text-teal-600 hover:underline truncate max-w-[220px]">
                                  <ExternalLink className="w-3 h-3 flex-shrink-0" />
                                  <span className="truncate">{rec.store_link}</span>
                                </a>
                              )}
                            </div>
                          </td>
                          <td className="px-6 py-3">
                            {rec.customer_email ? (
                              <a href={`mailto:${rec.customer_email}`} className="inline-flex items-center gap-1 text-teal-600 hover:underline">
                                <Mail className="w-3.5 h-3.5 flex-shrink-0" />
                                <span className="truncate max-w-[180px]">{rec.customer_email}</span>
                              </a>
                            ) : <span className="text-slate-300">—</span>}
                          </td>
                          <td className="px-6 py-3">
                            <span className="inline-flex items-center gap-2 text-slate-700">
                              <MemberAvatar name={rec.salesperson} sizeClass="w-7 h-7" ringClass="ring-1 ring-slate-100" />
                              {rec.salesperson}
                            </span>
                          </td>
                          <td className="px-6 py-3 text-slate-600 tabular-nums">{rec.sale_date}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* Pagination */}
                <div className="flex flex-col sm:flex-row items-center justify-between gap-3 px-6 py-4 border-t border-slate-100">
                  <p className="text-xs text-slate-500">
                    Showing <span className="font-semibold text-slate-700">{(records.page - 1) * records.limit + 1}</span>–
                    <span className="font-semibold text-slate-700">{Math.min(records.page * records.limit, records.total)}</span> of{' '}
                    <span className="font-semibold text-slate-700">{records.total}</span>
                  </p>
                  <div className="flex items-center gap-2">
                    <button onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={records.page <= 1}
                      className="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                      <ChevronLeft className="w-4 h-4" /> Prev
                    </button>
                    <span className="text-sm text-slate-500 tabular-nums">
                      Page {records.page} of {records.pages || 1}
                    </span>
                    <button onClick={() => setPage((p) => Math.min(records.pages || 1, p + 1))} disabled={records.page >= (records.pages || 1)}
                      className="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                      Next <ChevronRight className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </>
            )}
          </div>
        </>
      )}
    </div>
  );
};

export default AppPerformanceReport;
