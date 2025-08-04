import React, { useState, useEffect } from 'react';

const ReviewCredit = () => {
  const [reviewers, setReviewers] = useState([]);
  const [selectedReviewer, setSelectedReviewer] = useState('');
  const [reviewerStats, setReviewerStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch available reviewers on component mount
  useEffect(() => {
    fetchReviewers();
  }, []);

  // Fetch reviewer stats when selected reviewer changes
  useEffect(() => {
    if (selectedReviewer) {
      fetchReviewerStats(selectedReviewer);
    }
  }, [selectedReviewer]);

  const fetchReviewers = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/reviewers.php');
      if (!response.ok) throw new Error('Failed to fetch reviewers');
      const data = await response.json();
      setReviewers(data);
      // Set first reviewer as default selection
      if (data.length > 0) {
        setSelectedReviewer(data[0]);
      }
    } catch (err) {
      setError('Failed to load reviewers');
      console.error('Error fetching reviewers:', err);
    }
  };

  const fetchReviewerStats = async (reviewerName) => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`http://localhost:8000/api/reviewer-stats.php?reviewer_name=${encodeURIComponent(reviewerName)}`);
      if (!response.ok) throw new Error('Failed to fetch reviewer stats');
      const data = await response.json();
      setReviewerStats(data);
    } catch (err) {
      setError('Failed to load reviewer statistics');
      console.error('Error fetching reviewer stats:', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="review-credit-page">
      <div className="container" style={{ padding: '20px', maxWidth: '1400px', margin: '0 auto' }}>
        <div className="page-header" style={{ marginBottom: '30px' }}>
          <h1 style={{
            fontSize: '2.5rem',
            fontWeight: 'bold',
            color: '#333',
            marginBottom: '10px'
          }}>
            Review Credit
          </h1>
          <p style={{
            fontSize: '1.1rem',
            color: '#666',
            marginBottom: '0'
          }}>
            Reviewer performance and app statistics
          </p>
        </div>

        <div className="two-section-layout" style={{
          display: 'grid',
          gridTemplateColumns: '300px 1fr',
          gap: '30px',
          height: 'calc(100vh - 200px)'
        }}>
          {/* Left Section - Reviewer Selection */}
          <div className="reviewer-selection-section" style={{
            backgroundColor: 'white',
            borderRadius: '8px',
            padding: '20px',
            boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
            height: 'fit-content'
          }}>
            <h3 style={{
              fontSize: '1.3rem',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '20px',
              borderBottom: '2px solid #17a2b8',
              paddingBottom: '10px'
            }}>
              Select Reviewer
            </h3>

            {selectedReviewer && (
              <div style={{
                backgroundColor: '#e8f4f8',
                padding: '15px',
                borderRadius: '6px',
                marginBottom: '20px',
                border: '1px solid #17a2b8'
              }}>
                <div style={{ fontSize: '0.9rem', color: '#666', marginBottom: '5px' }}>
                  Currently Selected:
                </div>
                <div style={{ fontSize: '1.1rem', fontWeight: 'bold', color: '#17a2b8' }}>
                  {selectedReviewer}
                </div>
              </div>
            )}

            <div className="reviewer-list">
              {reviewers.map((reviewer) => (
                <button
                  key={reviewer}
                  onClick={() => setSelectedReviewer(reviewer)}
                  style={{
                    width: '100%',
                    padding: '12px 16px',
                    marginBottom: '8px',
                    border: selectedReviewer === reviewer ? '2px solid #17a2b8' : '1px solid #ddd',
                    borderRadius: '6px',
                    backgroundColor: selectedReviewer === reviewer ? '#f0f8ff' : 'white',
                    color: selectedReviewer === reviewer ? '#17a2b8' : '#333',
                    cursor: 'pointer',
                    textAlign: 'left',
                    fontSize: '1rem',
                    fontWeight: selectedReviewer === reviewer ? 'bold' : 'normal',
                    transition: 'all 0.2s ease'
                  }}
                  onMouseEnter={(e) => {
                    if (selectedReviewer !== reviewer) {
                      e.target.style.backgroundColor = '#f8f9fa';
                      e.target.style.borderColor = '#17a2b8';
                    }
                  }}
                  onMouseLeave={(e) => {
                    if (selectedReviewer !== reviewer) {
                      e.target.style.backgroundColor = 'white';
                      e.target.style.borderColor = '#ddd';
                    }
                  }}
                >
                  {reviewer}
                </button>
              ))}
            </div>
          </div>

          {/* Right Section - Reviewer's App Statistics */}
          <div className="reviewer-stats-section" style={{
            backgroundColor: 'white',
            borderRadius: '8px',
            padding: '20px',
            boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
          }}>
            <h3 style={{
              fontSize: '1.3rem',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '20px',
              borderBottom: '2px solid #17a2b8',
              paddingBottom: '10px'
            }}>
              App Statistics
              {selectedReviewer && (
                <span style={{ fontSize: '1rem', fontWeight: 'normal', color: '#666' }}>
                  {' '}for {selectedReviewer} (All Time)
                </span>
              )}
            </h3>

            {loading && (
              <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
                <div style={{ fontSize: '2rem', marginBottom: '10px' }}>⏳</div>
                Loading reviewer statistics...
              </div>
            )}

            {error && (
              <div style={{
                backgroundColor: '#f8d7da',
                color: '#721c24',
                padding: '15px',
                borderRadius: '6px',
                border: '1px solid #f5c6cb'
              }}>
                {error}
              </div>
            )}

            {!loading && !error && reviewerStats.length === 0 && selectedReviewer && (
              <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
                <div style={{ fontSize: '2rem', marginBottom: '10px' }}>📊</div>
                No review data found for {selectedReviewer}
              </div>
            )}

            {!loading && !error && reviewerStats.length > 0 && (
              <div className="stats-list">
                {reviewerStats.map((stat, index) => (
                  <div
                    key={index}
                    style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      padding: '15px',
                      marginBottom: '10px',
                      backgroundColor: '#f8f9fa',
                      borderRadius: '6px',
                      border: '1px solid #e9ecef'
                    }}
                  >
                    <div style={{
                      fontSize: '1.1rem',
                      fontWeight: '500',
                      color: '#333'
                    }}>
                      {stat.app_name}
                    </div>
                    <div style={{
                      fontSize: '1.2rem',
                      fontWeight: 'bold',
                      color: '#17a2b8',
                      backgroundColor: 'white',
                      padding: '5px 12px',
                      borderRadius: '20px',
                      border: '1px solid #17a2b8'
                    }}>
                      {stat.review_count} {stat.review_count === 1 ? 'review' : 'reviews'}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReviewCredit;
