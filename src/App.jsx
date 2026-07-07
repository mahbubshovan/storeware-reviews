import { useState, useEffect } from 'react'
import { BarChart3, FileText, TrendingUp, Users, ShoppingCart } from 'lucide-react'
import storewareIcon from './assets/storeware-icon.png'
import Analytics from './components/Analytics'
import AccessTabbed from './pages/AccessTabbed'
import ReviewCount from './pages/ReviewCount'
import ReviewCredit from './pages/ReviewCreditSimple'
import SalesTracker from './pages/SalesTracker'
import { CacheProvider } from './context/CacheContext'

function App() {
  const [currentView, setCurrentView] = useState('analytics');

  // Update document title based on current view
  useEffect(() => {
    const titles = {
      'analytics': 'Analytics Dashboard - Shopify App Review Analytics',
      'access-tabbed': 'Access Reviews - Shopify App Review Analytics',
      'appwise-reviews': 'Appwise Reviews - Shopify App Review Analytics',
      'agent-reviews': 'Agent Reviews - Shopify App Review Analytics',
      'sales-tracker': 'Sales Tracker - Shopify App Review Analytics'
    };
    document.title = titles[currentView] || 'Shopify App Review Analytics';
  }, [currentView]);

  const tabs = [
    { id: 'analytics', label: 'Analytics', icon: BarChart3 },
    { id: 'access-tabbed', label: 'Access Reviews', icon: FileText },
    { id: 'appwise-reviews', label: 'Appwise Reviews', icon: TrendingUp },
    { id: 'agent-reviews', label: 'Agent Reviews', icon: Users },
    { id: 'sales-tracker', label: 'Sales Tracker', icon: ShoppingCart },
  ];

  return (
    <CacheProvider>
      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
        {/* Animated Background */}
        <div className="fixed inset-0 opacity-30 pointer-events-none">
          <svg className="absolute top-0 left-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style={{ stopColor: '#0891b2', stopOpacity: 0.2 }} />
                <stop offset="100%" style={{ stopColor: '#10b981', stopOpacity: 0.3 }} />
              </linearGradient>
            </defs>
            <circle cx="10%" cy="20%" r="300" fill="url(#grad1)" className="animate-pulse" />
            <circle cx="90%" cy="80%" r="200" fill="url(#grad1)" className="animate-pulse" style={{ animationDelay: '1s' }} />
          </svg>
        </div>

        {/* Sticky Top Navbar */}
        <nav className="sticky top-0 z-50 backdrop-blur-lg bg-white/80 border-b border-slate-200/80 shadow-md">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between h-16">
              {/* Logo */}
              <div className="flex items-center space-x-3">
                <img
                  src={storewareIcon}
                  alt="Storeware"
                  className="h-10 w-10 rounded-xl object-cover shadow-lg"
                />
                <div>
                  <h1 className="text-xl font-bold bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent">
                    Storeware Reviews
                  </h1>
                  <p className="text-xs text-slate-500">Shopify App Review Analytics</p>
                </div>
              </div>

              {/* Pill Navigation */}
              <div className="hidden md:flex items-center bg-white/80 rounded-2xl shadow-md p-1.5">
                {tabs.map((tab) => {
                  const Icon = tab.icon;
                  return (
                    <button
                      key={tab.id}
                      onClick={() => setCurrentView(tab.id)}
                      className={`
                        relative px-4 py-2 rounded-xl font-medium text-sm transition-all duration-300
                        flex items-center space-x-2
                        ${currentView === tab.id
                          ? 'bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-lg shadow-teal-500/40'
                          : 'text-slate-600 hover:text-teal-600 hover:bg-slate-50'
                        }
                      `}
                    >
                      <Icon className="w-4 h-4" />
                      <span>{tab.label}</span>
                      {currentView === tab.id && (
                        <div className="absolute inset-0 rounded-xl bg-white/20" />
                      )}
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Mobile Navigation */}
            <div className="md:hidden pb-3 flex space-x-1 overflow-x-auto">
              {tabs.map((tab) => {
                const Icon = tab.icon;
                return (
                  <button
                    key={tab.id}
                    onClick={() => setCurrentView(tab.id)}
                    className={`
                      flex-shrink-0 px-3 py-1.5 rounded-lg font-medium text-xs transition-all
                      flex items-center space-x-1.5
                      ${currentView === tab.id
                        ? 'bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-md shadow-teal-500/30'
                        : 'bg-white/50 text-slate-600 hover:text-teal-600'
                      }
                    `}
                  >
                    <Icon className="w-3.5 h-3.5" />
                    <span>{tab.label}</span>
                  </button>
                );
              })}
            </div>
          </div>
        </nav>

        {/* Main Content */}
        <main className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
          {currentView === 'analytics' ? (
            <Analytics />
          ) : currentView === 'access-tabbed' ? (
            <AccessTabbed />
          ) : currentView === 'appwise-reviews' ? (
            <ReviewCount />
          ) : currentView === 'agent-reviews' ? (
            <ReviewCredit />
          ) : currentView === 'sales-tracker' ? (
            <SalesTracker />
          ) : (
            <Analytics />
          )}
        </main>
      </div>
    </CacheProvider>
  )
}

export default App