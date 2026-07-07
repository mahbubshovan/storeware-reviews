import { useState, useEffect, useCallback } from 'react';
import {
  ShoppingCart, Plus, Search, Pencil, Trash2, X, Calendar,
  Store, Users, ExternalLink, RefreshCw, Package, Filter,
  ClipboardList, BarChart3, Mail, PieChart,
} from 'lucide-react';
import { getAppIcon } from '../config/appConfig';
import { AGENTS } from '../config/agents';
import TeamPerformanceDashboard from '../components/TeamPerformanceDashboard';
import AppPerformanceReport from '../components/AppPerformanceReport';
import MemberAvatar from '../components/MemberAvatar';
import MemberSelect from '../components/MemberSelect';
import AppSelect from '../components/AppSelect';

const API = '/backend/api/sales-tracker.php';

// Salespeople = real team members (exclude the "Organic" pseudo-agent)
const SALESPEOPLE = AGENTS.filter((a) => a.name !== 'Organic').map((a) => a.name);

const todayStr = () => {
  const d = new Date();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${m}-${day}`;
};

const emptyForm = () => ({
  id: null,
  store_name: '',
  store_link: '',
  customer_email: '',
  app_name: '',
  salesperson: '',
  sale_date: todayStr(),
});

// Small icon+label cell for an app
const AppBadge = ({ appName }) => {
  const [err, setErr] = useState(false);
  const icon = getAppIcon(appName);
  return (
    <span className="inline-flex items-center gap-2">
      {icon && !err ? (
        <img src={icon} alt={appName} onError={() => setErr(true)}
          className="w-6 h-6 rounded-md object-cover border border-slate-100 flex-shrink-0" />
      ) : (
        <span className="w-6 h-6 rounded-md bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
          {appName?.charAt(0)?.toUpperCase() || '?'}
        </span>
      )}
      <span className="text-sm text-slate-700 truncate">{appName}</span>
    </span>
  );
};

const SalesTracker = () => {
  const [section, setSection] = useState('entries'); // entries | performance
  const [records, setRecords] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Filters
  const [search, setSearch] = useState('');
  const [fpSalesperson, setFpSalesperson] = useState('');
  const [fpApp, setFpApp] = useState('');
  const [fpStart, setFpStart] = useState('');
  const [fpEnd, setFpEnd] = useState('');

  // Modal form
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(emptyForm());
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);

  const fetchRecords = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params = new URLSearchParams({ action: 'list' });
      if (search.trim()) params.set('search', search.trim());
      if (fpSalesperson) params.set('salesperson', fpSalesperson);
      if (fpApp) params.set('app_name', fpApp);
      if (fpStart) params.set('start_date', fpStart);
      if (fpEnd) params.set('end_date', fpEnd);

      const res = await fetch(`${API}?${params.toString()}`);
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Failed to load records');
      setRecords(data.records || []);
    } catch (err) {
      console.error('SalesTracker load error:', err);
      setError(err.message || 'Failed to load sales records');
      setRecords([]);
    } finally {
      setLoading(false);
    }
  }, [search, fpSalesperson, fpApp, fpStart, fpEnd]);

  // Debounced reload whenever filters change
  useEffect(() => {
    const t = setTimeout(fetchRecords, 300);
    return () => clearTimeout(t);
  }, [fetchRecords]);

  const openCreate = () => {
    setForm(emptyForm());
    setFormError(null);
    setShowForm(true);
  };

  const openEdit = (rec) => {
    setForm({
      id: rec.id,
      store_name: rec.store_name || '',
      store_link: rec.store_link || '',
      customer_email: rec.customer_email || '',
      app_name: rec.app_name || '',
      salesperson: rec.salesperson || '',
      sale_date: rec.sale_date || todayStr(),
    });
    setFormError(null);
    setShowForm(true);
  };

  const setField = (key) => (e) => setForm((f) => ({ ...f, [key]: e.target.value }));

  const submitForm = async (e) => {
    e.preventDefault();
    setFormError(null);

    if (!form.store_name.trim() && !form.store_link.trim()) {
      setFormError('Please provide a store name or store link.');
      return;
    }
    if (!form.app_name) { setFormError('Please select an app.'); return; }
    if (!form.salesperson) { setFormError('Please select a salesperson.'); return; }
    if (!form.sale_date) { setFormError('Please choose a sale date.'); return; }

    setSaving(true);
    try {
      const payload = { ...form, action: form.id ? 'update' : 'create' };
      const res = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Save failed');
      setShowForm(false);
      await fetchRecords();
    } catch (err) {
      console.error('SalesTracker save error:', err);
      setFormError(err.message || 'Failed to save record');
    } finally {
      setSaving(false);
    }
  };

  const deleteRecord = async (rec) => {
    if (!window.confirm(`Delete sale record for "${rec.store_name}"?`)) return;
    try {
      const res = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: rec.id }),
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Delete failed');
      await fetchRecords();
    } catch (err) {
      console.error('SalesTracker delete error:', err);
      alert(err.message || 'Failed to delete record');
    }
  };

  const clearFilters = () => {
    setSearch('');
    setFpSalesperson('');
    setFpApp('');
    setFpStart('');
    setFpEnd('');
  };

  const hasFilters = search || fpSalesperson || fpApp || fpStart || fpEnd;

  const inputCls =
    'w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-white';

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md">
              <ShoppingCart className="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-slate-800">Sales Tracker</h1>
              <p className="text-sm text-slate-500">Track app sales generated by the support team</p>
            </div>
          </div>
          {section === 'entries' && (
            <button
              onClick={openCreate}
              className="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all"
            >
              <Plus className="w-4 h-4" /> Add Sale
            </button>
          )}
        </div>
      </div>

      {/* Section toggle */}
      <div className="flex flex-wrap sm:inline-flex bg-white/80 backdrop-blur rounded-2xl shadow-md p-1.5 gap-1">
        {[
          { key: 'entries', label: 'Sales Entries', icon: ClipboardList },
          { key: 'performance', label: 'Team Performance', icon: BarChart3 },
          { key: 'app-report', label: 'App Performance', icon: PieChart },
        ].map((s) => {
          const Icon = s.icon;
          return (
            <button key={s.key} onClick={() => setSection(s.key)}
              className={`px-4 py-2 rounded-xl font-medium text-sm transition-all inline-flex items-center gap-2 ${
                section === s.key
                  ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg shadow-teal-500/30'
                  : 'text-slate-600 hover:text-teal-600 hover:bg-slate-50'
              }`}>
              <Icon className="w-4 h-4" /> {s.label}
            </button>
          );
        })}
      </div>

      {section === 'performance' && <TeamPerformanceDashboard />}

      {section === 'app-report' && <AppPerformanceReport />}

      {/* Filters */}
      {section === 'entries' && (
      <>
      <div className="bg-white rounded-2xl border border-slate-200 shadow-lg p-5 space-y-4">
        <div className="flex items-center gap-2 text-slate-600">
          <Filter className="w-4 h-4 text-teal-500" />
          <span className="text-sm font-semibold">Search & Filter</span>
          {hasFilters && (
            <button onClick={clearFilters}
              className="ml-auto text-xs text-slate-500 hover:text-teal-600 underline">
              Clear all
            </button>
          )}
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
          {/* Search */}
          <div className="sm:col-span-2 lg:col-span-2 relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search store, customer, app…"
              className={`${inputCls} pl-9`}
            />
          </div>
          {/* Salesperson filter */}
          <MemberSelect value={fpSalesperson} onChange={setFpSalesperson} options={SALESPEOPLE} allLabel="All salespeople" />
          {/* App filter */}
          <AppSelect value={fpApp} onChange={setFpApp} allLabel="All apps" />
          {/* Date range */}
          <div className="sm:col-span-2 lg:col-span-2 flex items-center gap-2 min-w-0">
            <input type="date" value={fpStart} onChange={(e) => setFpStart(e.target.value)}
              className={`${inputCls} flex-1 min-w-0`} title="From date" aria-label="From date" />
            <span className="text-slate-400 text-sm flex-shrink-0">–</span>
            <input type="date" value={fpEnd} onChange={(e) => setFpEnd(e.target.value)}
              className={`${inputCls} flex-1 min-w-0`} title="To date" aria-label="To date" />
          </div>
        </div>
      </div>

      {/* Records table */}
      <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg overflow-hidden">
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100">
          <h2 className="text-base font-bold text-slate-700">
            Sales Records
            <span className="ml-2 text-xs font-medium text-slate-400 bg-slate-100 rounded-full px-2 py-0.5 tabular-nums">
              {records.length}
            </span>
          </h2>
          <button onClick={fetchRecords}
            className="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-teal-600 transition-colors">
            <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} /> Refresh
          </button>
        </div>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-3">
            <div className="w-10 h-10 border-4 border-teal-500 border-t-transparent rounded-full animate-spin" />
            <p className="text-slate-500 font-medium">Loading sales records…</p>
          </div>
        ) : error ? (
          <div className="flex flex-col items-center justify-center py-20 gap-3 text-center">
            <span className="text-5xl">⚠️</span>
            <h3 className="text-lg font-bold text-slate-700">Couldn’t load records</h3>
            <p className="text-slate-500 max-w-md">{error}</p>
            <button onClick={fetchRecords}
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all">
              <RefreshCw className="w-4 h-4" /> Retry
            </button>
          </div>
        ) : records.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4 text-center">
            <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg">
              <Package className="w-10 h-10 text-white" />
            </div>
            <h3 className="text-xl font-bold text-slate-700">
              {hasFilters ? 'No matching records' : 'No sales recorded yet'}
            </h3>
            <p className="text-slate-500 max-w-md">
              {hasFilters
                ? 'Try adjusting your search or filters.'
                : 'Add your first sale to start tracking team-generated app sales.'}
            </p>
            {!hasFilters && (
              <button onClick={openCreate}
                className="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all">
                <Plus className="w-4 h-4" /> Add Sale
              </button>
            )}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[760px] text-sm">
              <thead>
                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide bg-slate-50/70">
                  <th className="px-6 py-3">Store</th>
                  <th className="px-6 py-3">Customer Email</th>
                  <th className="px-6 py-3">App</th>
                  <th className="px-6 py-3">Salesperson</th>
                  <th className="px-6 py-3">Sale Date</th>
                  <th className="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {records.map((rec) => (
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
                    <td className="px-6 py-3 text-slate-600">
                      {rec.customer_email ? (
                        <a href={`mailto:${rec.customer_email}`}
                          className="inline-flex items-center gap-1 text-teal-600 hover:underline">
                          <Mail className="w-3.5 h-3.5 flex-shrink-0" />
                          <span className="truncate max-w-[200px]">{rec.customer_email}</span>
                        </a>
                      ) : (
                        <span className="text-slate-300">—</span>
                      )}
                    </td>
                    <td className="px-6 py-3"><AppBadge appName={rec.app_name} /></td>
                    <td className="px-6 py-3">
                      <span className="inline-flex items-center gap-2 text-slate-700">
                        <MemberAvatar name={rec.salesperson} sizeClass="w-7 h-7" ringClass="ring-1 ring-slate-100" textClass="text-xs" />
                        {rec.salesperson}
                      </span>
                    </td>
                    <td className="px-6 py-3 text-slate-600 tabular-nums">{rec.sale_date}</td>
                    <td className="px-6 py-3">
                      <div className="flex items-center justify-end gap-1">
                        <button onClick={() => openEdit(rec)} title="Edit"
                          className="p-2 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-teal-50 transition-colors">
                          <Pencil className="w-4 h-4" />
                        </button>
                        <button onClick={() => deleteRecord(rec)} title="Delete"
                          className="p-2 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
      </>
      )}

      {/* Create / Edit modal */}
      {showForm && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onClick={() => setShowForm(false)} />
          <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg animate-fade-in">
            <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-t-2xl">
              <h3 className="text-lg font-bold text-white flex items-center gap-2">
                <ShoppingCart className="w-5 h-5" />
                {form.id ? 'Edit Sale' : 'Add Sale'}
              </h3>
              <button onClick={() => setShowForm(false)} className="text-white/80 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={submitForm} className="p-6 space-y-4">
              {formError && (
                <div className="text-sm text-rose-700 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2">
                  {formError}
                </div>
              )}

              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
                  <Store className="w-3.5 h-3.5 text-teal-500" /> Store Name
                </label>
                <input type="text" value={form.store_name} onChange={setField('store_name')}
                  placeholder="e.g. Acme Store" className={inputCls} />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
                  <ExternalLink className="w-3.5 h-3.5 text-teal-500" /> Store Link <span className="text-slate-400 font-normal">(optional)</span>
                </label>
                <input type="url" value={form.store_link} onChange={setField('store_link')}
                  placeholder="https://store.myshopify.com" className={inputCls} />
                <p className="text-[11px] text-slate-400 mt-1">Provide a store name or a store link (at least one).</p>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
                  <Mail className="w-3.5 h-3.5 text-teal-500" /> Customer Mail ID <span className="text-slate-400 font-normal">(optional)</span>
                </label>
                <input type="email" value={form.customer_email} onChange={setField('customer_email')}
                  placeholder="e.g. customer@store.com" className={inputCls} />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
                    <Package className="w-3.5 h-3.5 text-teal-500" /> App <span className="text-rose-500">*</span>
                  </label>
                  <AppSelect value={form.app_name} onChange={(v) => setForm((f) => ({ ...f, app_name: v }))} placeholder="Select an app…" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
                    <Users className="w-3.5 h-3.5 text-teal-500" /> Salesperson <span className="text-rose-500">*</span>
                  </label>
                  <MemberSelect value={form.salesperson} onChange={(v) => setForm((f) => ({ ...f, salesperson: v }))} options={SALESPEOPLE} placeholder="Select a member…" />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1 flex items-center gap-1">
                  <Calendar className="w-3.5 h-3.5 text-teal-500" /> Sale Date <span className="text-rose-500">*</span>
                </label>
                <input type="date" value={form.sale_date} onChange={setField('sale_date')} className={inputCls} required />
              </div>

              <div className="flex items-center justify-end gap-3 pt-2">
                <button type="button" onClick={() => setShowForm(false)}
                  className="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                  Cancel
                </button>
                <button type="submit" disabled={saving}
                  className="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg disabled:opacity-50 transition-all">
                  {saving ? <RefreshCw className="w-4 h-4 animate-spin" /> : <Plus className="w-4 h-4" />}
                  {form.id ? 'Save Changes' : 'Add Sale'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default SalesTracker;
