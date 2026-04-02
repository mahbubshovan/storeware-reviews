import { useState } from 'react';
import { DollarSign } from 'lucide-react';
import { getGravatarUrl, getAgentByName } from '../config/agents';

const RATE = 20;
const MEDALS = ['🥇', '🥈', '🥉'];
const ORGANIC = new Set(['Organic', 'Organic Review', 'From App']);

const getInitials = (name) => {
  if (!name) return '??';
  const parts = name.trim().split(/\s+/);
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : name.slice(0, 2).toUpperCase();
};

const AgentAvatar = ({ agentName }) => {
  const [err, setErr] = useState(false);
  const agent = getAgentByName(agentName);
  if (agent?.hash && !err) {
    return (
      <img
        src={getGravatarUrl(agent.hash, 40)}
        alt={agentName}
        className="w-8 h-8 rounded-full object-cover ring-2 ring-teal-100 flex-shrink-0"
        onError={() => setErr(true)}
      />
    );
  }
  return (
    <span className="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
      {getInitials(agentName)}
    </span>
  );
};

const AgentEarnings = ({ agentStats = [], timeFilter, customDateRange }) => {
  const getDateLabel = () => {
    if (timeFilter === 'custom' && customDateRange?.start && customDateRange?.end)
      return `${customDateRange.start} → ${customDateRange.end}`;
    if (timeFilter === 'all_time') return 'All Time';
    return 'Last 30 Days';
  };

  const sorted = [...agentStats]
    .filter((s) => !ORGANIC.has(s.agent_name))
    .sort((a, b) => b.review_count - a.review_count);

  if (sorted.length === 0) return null;

  const maxCount = sorted[0].review_count || 1;
  const totalReviews = sorted.reduce((s, a) => s + a.review_count, 0);
  const totalPayout = totalReviews * RATE;
  const top = sorted[0];

  return (
    <div className="bg-white/70 backdrop-blur-md rounded-2xl border border-white/40 shadow-lg p-6">
      {/* Header */}
      <div className="flex items-center justify-between mb-5">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
            <DollarSign className="w-5 h-5 text-white" />
          </div>
          <h2 className="text-lg font-bold text-slate-800 flex items-center gap-2">
            💰 Agent Earnings
            <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
              StoreSEO only
            </span>
          </h2>
        </div>
        <span className="text-sm font-medium px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 bg-white shadow-sm whitespace-nowrap">
          {getDateLabel()}
        </span>
      </div>

      {/* Summary cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div className="bg-slate-50 rounded-xl p-4 border border-slate-100">
          <p className="text-xs text-slate-500 font-medium">Total reviews</p>
          <p className="text-3xl font-bold text-slate-800 my-1">{totalReviews}</p>
          <p className="text-xs text-slate-400">across all agents</p>
        </div>
        <div className="bg-slate-50 rounded-xl p-4 border border-slate-100">
          <p className="text-xs text-slate-500 font-medium">Total payout</p>
          <p className="text-3xl font-bold text-emerald-600 my-1">${totalPayout.toLocaleString()}</p>
          <p className="text-xs text-slate-400">@ ${RATE}/review</p>
        </div>
        <div className="bg-slate-50 rounded-xl p-4 border border-slate-100">
          <p className="text-xs text-slate-500 font-medium">Top earner</p>
          <p className="text-2xl font-bold text-slate-800 my-1">{top.agent_name}</p>
          <p className="text-xs text-slate-400">${top.review_count * RATE} · {top.review_count} reviews</p>
        </div>
      </div>

      {/* Earnings table */}
      <div className="rounded-xl border border-slate-200 overflow-hidden">
        {/* Table header */}
        <div
          className="grid bg-slate-50 border-b border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide"
          style={{ gridTemplateColumns: '1fr 80px 140px 70px 90px' }}
        >
          <span>Agent</span>
          <span className="text-center">Reviews</span>
          <span>Progress</span>
          <span className="text-right">% of top</span>
          <span className="text-right">Earnings</span>
        </div>

        {/* Table rows */}
        <div className="divide-y divide-slate-100">
          {sorted.map((stat, i) => {
            const pct = Math.round((stat.review_count / maxCount) * 100);
            const earnings = stat.review_count * RATE;
            return (
              <div
                key={stat.agent_name}
                className="grid px-4 py-3 items-center hover:bg-slate-50/70 transition-colors"
                style={{ gridTemplateColumns: '1fr 80px 140px 70px 90px' }}
              >
                {/* Agent avatar + name + medal */}
                <div className="flex items-center gap-3 min-w-0">
                  <AgentAvatar agentName={stat.agent_name} />
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-slate-800 truncate">{stat.agent_name}</p>
                    {i < 3 && <span className="text-xs leading-none">{MEDALS[i]}</span>}
                  </div>
                </div>

                {/* Review count */}
                <span className="text-sm font-bold text-slate-700 text-center tabular-nums">
                  {stat.review_count}
                </span>

                {/* Progress bar */}
                <div className="h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div
                    className="h-full bg-gradient-to-r from-teal-400 to-emerald-500 rounded-full transition-all duration-500"
                    style={{ width: `${pct}%` }}
                  />
                </div>

                {/* % of top */}
                <span className="text-sm text-slate-500 text-right tabular-nums">{pct}%</span>

                {/* Earnings pill */}
                <div className="flex justify-end">
                  <span className="text-sm font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 tabular-nums">
                    ${earnings}
                  </span>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Footnote */}
      <p className="mt-4 text-xs text-slate-400">
        Rate: ${RATE} per review · Resets monthly · StoreSEO app only · Custom date range available via date picker above
      </p>
    </div>
  );
};

export default AgentEarnings;

