import React, { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import './AuthGuard.css';

const AuthGuard = ({ children }) => {
  const { isAuthenticated, isLoading, sessionInfo, login } = useAuth();
  const [password, setPassword] = useState('');
  const [loginError, setLoginError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleLogin = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setLoginError('');

    const result = await login(password);
    
    if (!result.success) {
      setLoginError(result.error || 'Login failed');
      setPassword(''); // Clear password on error
    }
    
    setIsSubmitting(false);
  };

  // Show loading spinner while checking session
  if (isLoading) {
    return (
      <div className="auth-loading">
        <div className="loading-spinner"></div>
        <p>Checking authentication...</p>
      </div>
    );
  }

  // Show login form if not authenticated
  if (!isAuthenticated) {
    return (
      <div className="auth-container">
        <div className="auth-card">
          <div className="auth-header">
            <h2>🔐 Access Required</h2>
            <p>Please enter the password to access the Shopify Reviews Dashboard</p>
          </div>
          
          <form onSubmit={handleLogin} className="auth-form">
            <div className="form-group">
              <label htmlFor="password">Password:</label>
              <input
                type="password"
                id="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter password"
                disabled={isSubmitting}
                autoFocus
                required
              />
            </div>
            
            {loginError && (
              <div className="error-message">
                ❌ {loginError}
              </div>
            )}
            
            <button 
              type="submit" 
              className="login-button"
              disabled={isSubmitting || !password.trim()}
            >
              {isSubmitting ? (
                <>
                  <span className="button-spinner"></span>
                  Authenticating...
                </>
              ) : (
                'Login'
              )}
            </button>
          </form>
          
          <div className="auth-footer">
            <p>💡 Hint: The password is <code>admin123</code></p>
            <p>🕐 Sessions last for 12 hours</p>
          </div>
        </div>
      </div>
    );
  }

  // Show authenticated content with session info
  return (
    <div className="authenticated-container">
      {sessionInfo && (
        <div className="session-info">
          <span className="session-status">
            ✅ Authenticated | Session expires in {sessionInfo.remainingHours}h
          </span>
        </div>
      )}
      {children}
    </div>
  );
};

export default AuthGuard;
