import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';

const SummaryStats = ({ selectedApp, refreshKey }) => {
  const [stats, setStats] = useState({
    thisMonth: 0,
    last30Days: 0,
    averageRating: 0.0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStats = async () => {
      // Don't fetch if no app is selected
      if (!selectedApp) {
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        const [thisMonthRes, last30DaysRes, avgRatingRes] = await Promise.all([
          reviewsAPI.getThisMonthReviews(selectedApp),
          reviewsAPI.getLast30DaysReviews(selectedApp),
          reviewsAPI.getAverageRating(selectedApp)
        ]);

        setStats({
          thisMonth: thisMonthRes.data.count,
          last30Days: last30DaysRes.data.count,
          averageRating: avgRatingRes.data.average_rating
        });
        setError(null);
      } catch (err) {
        setError('Failed to fetch statistics');
        console.error('Error fetching stats:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [selectedApp, refreshKey]);

  if (loading) {
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
