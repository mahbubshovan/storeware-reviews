import React, { useState, useEffect } from 'react';

const Access = () => {
  const [reviews, setReviews] = useState({});
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [editingReview, setEditingReview] = useState(null);
  const [editValue, setEditValue] = useState('');

  useEffect(() => {
    fetchAccessReviews();
  }, []);

  const fetchAccessReviews = async () => {
    try {
      setLoading(true);
      const response = await fetch('http://localhost:8000/api/access-reviews.php');
      const data = await response.json();
      
      if (data.success) {
        setReviews(data.reviews);
        setStats(data.stats);
      } else {
        console.error('Failed to fetch access reviews:', data.message);
      }
    } catch (error) {
      console.error('Error fetching access reviews:', error);
    } finally {
      setLoading(false);
    }
  };

  const syncAccessReviews = async () => {
    try {
      setSyncing(true);
      const response = await fetch('http://localhost:8000/api/access-reviews.php', {
        method: 'POST'
      });
      const data = await response.json();
      
      if (data.success) {
        await fetchAccessReviews(); // Refresh data
      } else {
        console.error('Failed to sync access reviews:', data.message);
      }
    } catch (error) {
      console.error('Error syncing access reviews:', error);
    } finally {
      setSyncing(false);
    }
  };

  const updateEarnedBy = async (reviewId, earnedBy) => {
    try {
      const response = await fetch('http://localhost:8000/api/access-reviews.php', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          earned_by: earnedBy
        })
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Update local state
        setReviews(prevReviews => {
          const newReviews = { ...prevReviews };
          Object.keys(newReviews).forEach(appName => {
            newReviews[appName] = newReviews[appName].map(review => 
              review.id === reviewId ? { ...review, earned_by: earnedBy } : review
            );
          });
          return newReviews;
        });
        
        // Update stats
        setStats(prevStats => ({
          ...prevStats,
          assigned_reviews: prevStats.assigned_reviews + (earnedBy ? 1 : -1),
          unassigned_reviews: prevStats.unassigned_reviews + (earnedBy ? -1 : 1)
        }));
      } else {
        console.error('Failed to update earned by:', data.message);
      }
    } catch (error) {
      console.error('Error updating earned by:', error);
    }
  };

  const handleEditStart = (reviewId, currentValue) => {
    setEditingReview(reviewId);
    setEditValue(currentValue || '');
  };

  const handleEditSave = async (reviewId) => {
    await updateEarnedBy(reviewId, editValue);
    setEditingReview(null);
    setEditValue('');
  };

  const handleEditCancel = () => {
    setEditingReview(null);
    setEditValue('');
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const truncateContent = (content, maxLength = 150) => {
    if (content.length <= maxLength) return content;
    return content.substring(0, maxLength) + '...';
  };

  const getCountryName = (countryCode) => {
    const countryMap = {
      'US': 'United States',
      'AU': 'Australia',
      'FR': 'France',
      'UK': 'United Kingdom',
      'RS': 'Serbia',
      'HU': 'Hungary',
      'CA': 'Canada',
      'DE': 'Germany',
      'NL': 'Netherlands',
      'NZ': 'New Zealand',
      'IN': 'India',
      'JP': 'Japan',
      'SG': 'Singapore',
      'CR': 'Costa Rica',
      'PL': 'Poland',
      'IT': 'Italy',
      'ES': 'Spain',
      'BR': 'Brazil',
      'MX': 'Mexico',
      'ZA': 'South Africa',
      'IE': 'Ireland',
      'BE': 'Belgium',
      'CH': 'Switzerland',
      'AT': 'Austria',
      'DK': 'Denmark',
      'SE': 'Sweden',
      'NO': 'Norway',
      'FI': 'Finland'
    };
    return countryMap[countryCode] || countryCode;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span className="ml-2">Loading access reviews...</span>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Access Reviews</h1>
          <p className="text-gray-600 mt-2">
            Manage and assign reviews from the last 30 days across all apps
          </p>
        </div>
        <button
          onClick={syncAccessReviews}
          disabled={syncing}
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {syncing ? 'Syncing...' : 'Sync Reviews'}
        </button>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Total Reviews</p>
            <p className="text-2xl font-bold">{stats.total_reviews}</p>
          </div>

          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Assigned</p>
            <p className="text-2xl font-bold text-green-600">{stats.assigned_reviews}</p>
          </div>

          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Unassigned</p>
            <p className="text-2xl font-bold text-orange-600">{stats.unassigned_reviews}</p>
          </div>

          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Apps</p>
            <p className="text-2xl font-bold text-purple-600">{stats.reviews_by_app.length}</p>
          </div>
        </div>
      )}

      {/* Reviews by App */}
      <div className="space-y-6">
        {Object.keys(reviews).length === 0 ? (
          <div className="bg-white p-8 rounded-lg shadow text-center">
            <h3 className="text-lg font-semibold text-gray-600">No Reviews Found</h3>
            <p className="text-gray-500">No reviews from the last 30 days. Try syncing to get the latest data.</p>
          </div>
        ) : (
          Object.entries(reviews).map(([appName, appReviews]) => (
            <div key={appName} className="bg-white rounded-lg shadow">
              <div className="p-4 border-b">
                <div className="flex items-center justify-between">
                  <h2 className="text-xl font-semibold">{appName}</h2>
                  <span className="bg-gray-100 px-2 py-1 rounded text-sm">{appReviews.length} reviews</span>
                </div>
              </div>
              <div className="p-4">
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b">
                        <th className="text-left p-2 font-medium">Date</th>
                        <th className="text-left p-2 font-medium">Review Content</th>
                        <th className="text-left p-2 font-medium">Country</th>
                        <th className="text-left p-2 font-medium">Earned By</th>
                      </tr>
                    </thead>
                    <tbody>
                      {appReviews.map((review) => (
                        <tr key={review.id} className="border-b hover:bg-gray-50">
                          <td className="p-2 text-sm">
                            {formatDate(review.review_date)}
                          </td>
                          <td className="p-2 text-sm max-w-md">
                            <div className="break-words">
                              {truncateContent(review.review_content)}
                            </div>
                          </td>
                          <td className="p-2 text-sm">
                            <span className="bg-gray-100 px-2 py-1 rounded text-xs">{getCountryName(review.country_name)}</span>
                          </td>
                          <td className="p-2">
                            {editingReview === review.id ? (
                              <div className="flex items-center gap-2">
                                <input
                                  type="text"
                                  value={editValue}
                                  onChange={(e) => setEditValue(e.target.value)}
                                  placeholder="Enter name..."
                                  className="border rounded px-2 py-1 w-32"
                                  onKeyPress={(e) => {
                                    if (e.key === 'Enter') {
                                      handleEditSave(review.id);
                                    } else if (e.key === 'Escape') {
                                      handleEditCancel();
                                    }
                                  }}
                                  autoFocus
                                />
                                <button
                                  onClick={() => handleEditSave(review.id)}
                                  className="bg-green-600 text-white px-2 py-1 rounded text-sm"
                                >
                                  Save
                                </button>
                                <button
                                  onClick={handleEditCancel}
                                  className="bg-gray-600 text-white px-2 py-1 rounded text-sm"
                                >
                                  Cancel
                                </button>
                              </div>
                            ) : (
                              <div
                                className="cursor-pointer hover:bg-gray-100 p-1 rounded min-h-[24px] min-w-[100px] flex items-center"
                                onClick={() => handleEditStart(review.id, review.earned_by)}
                              >
                                {review.earned_by ? (
                                  <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">{review.earned_by}</span>
                                ) : (
                                  <span className="text-gray-400 text-sm">Click to assign</span>
                                )}
                              </div>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default Access;
