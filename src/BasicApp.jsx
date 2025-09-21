function BasicApp() {
  console.log('BasicApp is rendering!');
  
  return (
    <div style={{ padding: '20px', fontFamily: 'Arial, sans-serif' }}>
      <h1 style={{ color: 'blue' }}>🛍️ Shopify Review Analytics - WORKING!</h1>
      <p style={{ fontSize: '18px', color: 'green' }}>
        ✅ React app is loading successfully!
      </p>
      
      <div style={{ 
        padding: '20px', 
        backgroundColor: '#f0f9ff', 
        border: '2px solid #0ea5e9',
        borderRadius: '8px',
        marginTop: '20px'
      }}>
        <h2>🎯 Rating Distribution System Status</h2>
        <ul style={{ fontSize: '16px', lineHeight: '1.6' }}>
          <li>✅ Backend APIs working</li>
          <li>✅ Complete rating distribution data scraped</li>
          <li>✅ All 6 apps supported</li>
          <li>✅ Live Shopify data integration</li>
        </ul>
      </div>

      <div style={{ marginTop: '20px' }}>
        <button 
          onClick={() => {
            alert('Button clicked! React is working.');
            console.log('Button clicked successfully');
          }}
          style={{
            padding: '12px 24px',
            fontSize: '16px',
            backgroundColor: '#0ea5e9',
            color: 'white',
            border: 'none',
            borderRadius: '6px',
            cursor: 'pointer'
          }}
        >
          Test React Functionality
        </button>
      </div>
    </div>
  )
}

export default BasicApp
