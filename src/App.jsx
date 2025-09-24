import { useState, useEffect } from 'react'
import './App.css'
import Analytics from './components/Analytics'
import AccessTabbed from './pages/AccessTabbed'
import ReviewCount from './pages/ReviewCount'
import ReviewCredit from './pages/ReviewCreditSimple'

function App() {
  const [currentView, setCurrentView] = useState('analytics'); // 'analytics', 'access-tabbed', 'appwise-reviews', or 'agent-reviews'

  console.log('App rendering with currentView:', currentView);
  console.log('Testing Agent Review update...');

  // Update document title based on current view
  useEffect(() => {
    const titles = {
      'analytics': 'Analytics Dashboard - Shopify App Review Analytics',
      'access-tabbed': 'Access Reviews - Shopify App Review Analytics',
      'appwise-reviews': 'Appwise Reviews - Shopify App Review Analytics',
      'agent-reviews': 'Agent Reviews - Shopify App Review Analytics'
    };
    document.title = titles[currentView] || 'Shopify App Review Analytics';
  }, [currentView]);

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
            Analytics
          </button>
          <button
            className={`nav-tab ${currentView === 'access-tabbed' ? 'active' : ''}`}
            onClick={() => setCurrentView('access-tabbed')}
            style={{
              padding: '10px 20px',
              marginRight: '10px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'access-tabbed' ? '#007bff' : '#f8f9fa',
              color: currentView === 'access-tabbed' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Access Reviews
          </button>
          <button
            className={`nav-tab ${currentView === 'appwise-reviews' ? 'active' : ''}`}
            onClick={() => setCurrentView('appwise-reviews')}
            style={{
              padding: '10px 20px',
              marginRight: '10px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'appwise-reviews' ? '#28a745' : '#f8f9fa',
              color: currentView === 'appwise-reviews' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Appwise Reviews
          </button>
          <button
            className={`nav-tab ${currentView === 'agent-reviews' ? 'active' : ''}`}
            onClick={() => setCurrentView('agent-reviews')}
            style={{
              padding: '10px 20px',
              border: 'none',
              borderRadius: '5px',
              backgroundColor: currentView === 'agent-reviews' ? '#17a2b8' : '#f8f9fa',
              color: currentView === 'agent-reviews' ? 'white' : '#333',
              cursor: 'pointer'
            }}
          >
            Agent Reviews
          </button>
        </div>
      </header>

      <main className="app-main">
        {currentView === 'analytics' ? (
          <Analytics />
        ) : currentView === 'access-tabbed' ? (
          <AccessTabbed />
        ) : currentView === 'appwise-reviews' ? (
          <ReviewCount />
        ) : currentView === 'agent-reviews' ? (
          <ReviewCredit />
        ) : (
          <Analytics />
        )}
      </main>
    </div>
  )
}

export default App