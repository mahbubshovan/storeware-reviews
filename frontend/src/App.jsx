import { useState, useEffect } from 'react'
import './App.css'
import AppSelector from './components/AppSelector'
import SummaryStats from './components/SummaryStats'
import ReviewDistribution from './components/ReviewDistribution'
import LatestReviews from './components/LatestReviews'
import Access from './pages/Access'

function App() {
  const [selectedApp, setSelectedApp] = useState('StoreSEO'); // Default to StoreSEO
  const [refreshKey, setRefreshKey] = useState(0);
  const [currentView, setCurrentView] = useState('analytics'); // 'analytics' or 'access'

  const handleAppSelect = (appName) => {
    setSelectedApp(appName);
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
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'access' ? '#007bff' : '#f8f9fa',
              color: currentView === 'access' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Access Reviews
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
            <ReviewDistribution
              selectedApp={selectedApp}
              refreshKey={refreshKey}
            />
            <LatestReviews
              selectedApp={selectedApp}
              refreshKey={refreshKey}
            />
          </>
        ) : (
          <Access />
        )}
      </main>
    </div>
  )
}

export default App
