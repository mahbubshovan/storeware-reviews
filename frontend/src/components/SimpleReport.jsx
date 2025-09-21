import React, { useState, useEffect } from 'react'

const SimpleReport = () => {
  const [reportData, setReportData] = useState(null)
  const [loading, setLoading] = useState(true) // Start with loading true
  const [error, setError] = useState(null)

  const generateReport = async () => {
    setLoading(true)
    setError(null)

    try {
      const response = await fetch('http://localhost:8000/api/simple_report.php')
      const data = await response.json()

      if (data.success) {
        setReportData(data)
      } else {
        setError(data.error || 'Failed to generate report')
      }
    } catch (err) {
      setError('Network error: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    generateReport()
  }, [])

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-purple-600 to-blue-600 p-6">
        <div className="max-w-4xl mx-auto">
          <div className="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-white">
            <div className="flex items-center space-x-2 mb-4">
              <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
              <span>Generating Report...</span>
            </div>
          </div>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-purple-600 to-blue-600 p-6">
        <div className="max-w-4xl mx-auto">
          <div className="bg-red-500/20 backdrop-blur-sm rounded-lg p-6 text-white">
            <h2 className="text-xl font-bold mb-2">Error</h2>
            <p>{error}</p>
            <button 
              onClick={generateReport}
              className="mt-4 bg-white/20 hover:bg-white/30 px-4 py-2 rounded transition-colors"
            >
              Retry
            </button>
          </div>
        </div>
      </div>
    )
  }

  if (!reportData) {
    return null
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-600 to-blue-600 p-6">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="bg-white/10 backdrop-blur-sm rounded-lg p-6 mb-6 text-white">
          <div className="flex items-center space-x-2 mb-4">
            <span className="text-2xl">🔍</span>
            <h1 className="text-2xl font-bold">Review Monitoring</h1>
          </div>
          <p className="text-white/80 mb-4">Check for new reviews on first pages of Shopify app stores</p>
          
          <div className="flex space-x-4 mb-4">
            <button className="bg-green-500/80 hover:bg-green-500 px-4 py-2 rounded transition-colors">
              🔍 Monitor StoreSEO
            </button>
            <button className="bg-blue-500/80 hover:bg-blue-500 px-4 py-2 rounded transition-colors">
              🔍 Monitor All Apps
            </button>
          </div>
          
          <p className="text-sm text-white/60">
            Last run: {reportData.last_run}
          </p>
        </div>

        {/* Results */}
        <div className="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-white">
          <div className="flex items-center space-x-2 mb-4">
            <span className="text-green-400">✓</span>
            <h2 className="text-xl font-bold">Monitoring Complete</h2>
          </div>
          
          <p className="text-lg mb-2">
            Total new reviews found: <span className="font-bold">{reportData.total_new_reviews}</span>
          </p>
          
          <p className="text-sm text-white/60 mb-6">
            Completed in {reportData.completed_in}
          </p>
          
          <h3 className="text-lg font-bold mb-4">Updated Statistics:</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {Object.entries(reportData.apps).map(([appName, stats]) => (
              <div key={appName} className="flex justify-between items-center">
                <div className="bg-blue-500/20 px-3 py-1 rounded text-sm font-medium min-w-[120px]">
                  {appName}:
                </div>
                <div className="text-right text-sm">
                  <div>{stats.this_month} this month | {stats.last_30_days} last 30 days</div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Refresh Button */}
        <div className="mt-6 text-center">
          <button 
            onClick={generateReport}
            disabled={loading}
            className="bg-white/20 hover:bg-white/30 disabled:opacity-50 px-6 py-3 rounded-lg text-white font-medium transition-colors"
          >
            {loading ? 'Generating...' : 'Refresh Report'}
          </button>
        </div>
      </div>
    </div>
  )
}

export default SimpleReport
