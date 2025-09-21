import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';
import { useFreshData } from '../hooks/useFreshData';

const SummaryStats = ({ selectedApp, refreshKey }) => {
  const [stats, setStats] = useState({
    thisMonth: 0,
    last30Days: 0,
    averageRating: 0.0
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Use fresh data hook for intelligent data management
  const { isRefreshing, refreshData } = useFreshData(selectedApp);

  useEffect(() => {
    const fetchStats = async () => {
      // Don't fetch if no app is selected - reset to default values
      if (!selectedApp) {
        setStats({
          thisMonth: 0,
          last30Days: 0,
          averageRating: 0.0
        });
        setLoading(false);
        setError(null);
        return;
      }

      try {
        setLoading(true);
        setError(null);

        // Use the new homepage-stats API for comprehensive data
        const response = await fetch(`/backend/api/homepage-stats.php?app_name=${selectedApp}`);
        const data = await response.json();

        console.log('📊 Stats loaded for', selectedApp, 'using monitoring system');

        if (data.success) {
          setStats({
            thisMonth: data.stats.this_month || 0,
            last30Days: data.stats.last_30_days || 0,
            averageRating: data.stats.avg_rating || 0.0
          });
        } else {
          throw new Error(data.error || 'Failed to fetch stats');
        }
        setError(null);
      } catch (err) {
        // Only show error if it's not a timeout from a previous request
        if (!err.message?.includes('timeout') || selectedApp) {
          setError(`Failed to fetch statistics: ${err.message || 'Unknown error'}`);
          console.error('Error fetching stats for', selectedApp, ':', err.message);
        }
      } finally {
        setLoading(false);
      }
    };

    // Debounce the API call
    const timeoutId = setTimeout(fetchStats, 300);
    return () => clearTimeout(timeoutId);
  }, [selectedApp, refreshKey]);

  // Listen for fresh data updates
  useEffect(() => {
    const handleFreshData = async () => {
      if (selectedApp && !isRefreshing) {
        // Refresh stats when fresh data is available
        fetchStats();
      }
    };

    handleFreshData();
  }, [isRefreshing, selectedApp]);

  if (loading && selectedApp) {
    return <div className="loading">Loading statistics...</div>;
  }

  if (error) {
    return <div className="error">{error}</div>;
  }

  return (
    <section className="summary-stats">
      <div className="stat-card">
        <h3>This Month</h3>
        <div className="stat-value">{stats.thisMonth}</div>
        <div className="stat-label">Total Reviews</div>
      </div>
      
      <div className="stat-card">
        <h3>Last 30 Days</h3>
        <div className="stat-value">{stats.last30Days}</div>
        <div className="stat-label">Total Reviews</div>
      </div>
      
      <div className="stat-card">
        <h3>Average Rating</h3>
        <div className="stat-value">{stats.averageRating}</div>
        <div className="stat-label">⭐ Stars</div>
      </div>
    </section>
  );
};

export default SummaryStats;
