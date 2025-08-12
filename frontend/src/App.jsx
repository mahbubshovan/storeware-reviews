import { useState, useEffect } from 'react'
import './App.css'
import AppSelector from './components/AppSelector'
import SummaryStats from './components/SummaryStats'
import ReviewDistribution from './components/ReviewDistribution'
import LatestReviews from './components/LatestReviews'
import Access from './pages/Access'
import ReviewCount from './pages/ReviewCount'
import ReviewCredit from './pages/ReviewCredit'

function App() {
  const [selectedApp, setSelectedApp] = useState(''); // No default selection
  const [refreshKey, setRefreshKey] = useState(0);
  const [currentView, setCurrentView] = useState('analytics'); // 'analytics', 'access', 'review-count', or 'review-credit'

  console.log('App rendering with:', { selectedApp, currentView, refreshKey });

  // Update document title based on current view
  useEffect(() => {
    const titles = {
      'analytics': 'Analytics Dashboard - Shopify App Review Analytics',
      'access': 'Access Reviews - Shopify App Review Analytics',
      'review-count': 'Review Count - Shopify App Review Analytics',
      'review-credit': 'Review Credit - Shopify App Review Analytics'
    };
    document.title = titles[currentView] || 'Shopify App Review Analytics';
  }, [currentView]);

  const handleAppSelect = (appName) => {
    setSelectedApp(appName);
    // Force refresh when switching apps to clear any cached data
    setRefreshKey(prev => prev + 1);
  };

  const handleScrapeComplete = (appName, scrapedCount) => {
    console.log(`Scraping completed for ${appName}: ${scrapedCount} new reviews`);
    // Trigger refresh of all components
    setRefreshKey(prev => prev + 1);
  };

  return (
    <div className="app">
      <header className="app-header">
        <h1>Shopify App Review Analytics</h1>
        <p>Comprehensive analytics dashboard for tracking and analyzing Shopify app reviews</p>

        {/* Navigation Tabs */}
        <div className="nav-tabs" style={{ marginTop: '20px' }}>
          <button
            className={`nav-tab ${currentView === 'analytics' ? 'active' : ''}`}
            onClick={() => setCurrentView('analytics')}
            style={{
              padding: '10px 20px',
              marginRight: '10px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'analytics' ? '#007bff' : '#f8f9fa',
              color: currentView === 'analytics' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Analytics Dashboard
          </button>
          <button
            className={`nav-tab ${currentView === 'access' ? 'active' : ''}`}
            onClick={() => setCurrentView('access')}
            style={{
              padding: '10px 20px',
              marginRight: '10px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'access' ? '#007bff' : '#f8f9fa',
              color: currentView === 'access' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Access Reviews
          </button>
          <button
            className={`nav-tab ${currentView === 'review-count' ? 'active' : ''}`}
            onClick={() => setCurrentView('review-count')}
            style={{
              padding: '10px 20px',
              marginRight: '10px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'review-count' ? '#28a745' : '#f8f9fa',
              color: currentView === 'review-count' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Review Count
          </button>
          <button
            className={`nav-tab ${currentView === 'review-credit' ? 'active' : ''}`}
            onClick={() => setCurrentView('review-credit')}
            style={{
              padding: '10px 20px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'review-credit' ? '#17a2b8' : '#f8f9fa',
              color: currentView === 'review-credit' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Review Credit
          </button>
        </div>
      </header>

      <main className="app-main">
        {currentView === 'analytics' ? (
          <>
            <AppSelector
              selectedApp={selectedApp}
              onAppSelect={handleAppSelect}
              onScrapeComplete={handleScrapeComplete}
            />

            <SummaryStats
              selectedApp={selectedApp}
              refreshKey={refreshKey}
            />

            {selectedApp && (
              <>
                <ReviewDistribution
                  selectedApp={selectedApp}
                  refreshKey={refreshKey}
                />
                <LatestReviews
                  selectedApp={selectedApp}
                  refreshKey={refreshKey}
                />
              </>
            )}
          </>
        ) : currentView === 'access' ? (
          <Access />
        ) : currentView === 'review-count' ? (
          <ReviewCount />
        ) : currentView === 'review-credit' ? (
          <ReviewCredit />
        ) : (
          <Access />
        )}
      </main>
    </div>
  )
}

export default App
