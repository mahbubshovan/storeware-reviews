import { useState } from 'react'

function TestApp() {
  const [selectedApp, setSelectedApp] = useState('');

  return (
    <div style={{ padding: '20px' }}>
      <h1>Test App - Shopify Review Analytics</h1>
      
      <div style={{ marginBottom: '20px' }}>
        <label>Select App: </label>
        <select 
          value={selectedApp} 
          onChange={(e) => setSelectedApp(e.target.value)}
          style={{ padding: '5px', marginLeft: '10px' }}
        >
          <option value="">Choose an app to analyze</option>
          <option value="StoreSEO">StoreSEO</option>
          <option value="EasyFlow">EasyFlow</option>
          <option value="StoreFAQ">StoreFAQ</option>
        </select>
      </div>

      {selectedApp ? (
        <div style={{ border: '1px solid #ccc', padding: '20px', borderRadius: '5px' }}>
          <h2>Selected App: {selectedApp}</h2>
          <p>This is a test to see if app selection works.</p>
          
          <div style={{ marginTop: '20px' }}>
            <h3>API Test</h3>
            <button 
              onClick={async () => {
                try {
                  const response = await fetch(`/backend/api/this-month-reviews.php?app_name=${selectedApp}`);
                  const data = await response.json();
                  console.log('API Response:', data);
                  alert(`API Response: ${JSON.stringify(data)}`);
                } catch (error) {
                  console.error('API Error:', error);
                  alert(`API Error: ${error.message}`);
                }
              }}
              style={{ padding: '10px', backgroundColor: '#007bff', color: 'white', border: 'none', borderRadius: '3px' }}
            >
              Test API Call
            </button>
          </div>
        </div>
      ) : (
        <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
          <h2>Choose an app to analyze</h2>
          <p>Select an app from the dropdown above to view its analytics.</p>
        </div>
      )}
    </div>
  )
}

export default TestApp
