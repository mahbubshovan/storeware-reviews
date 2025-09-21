import { useState, useEffect, useCallback } from 'react';

const API_BASE_URL = 'http://localhost:8000';

export const useAuth = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [sessionInfo, setSessionInfo] = useState(null);

  // Check session status
  const checkSession = useCallback(async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/auth-session.php`, {
        method: 'GET',
        credentials: 'include', // Important for session cookies
        headers: {
          'Content-Type': 'application/json',
        },
      });

      const data = await response.json();
      
      if (data.success && data.authenticated) {
        setIsAuthenticated(true);
        setSessionInfo({
          sessionId: data.session_id,
          loginTime: data.login_time,
          expiresAt: data.expires_at,
          remainingHours: data.remaining_hours
        });
      } else {
        setIsAuthenticated(false);
        setSessionInfo(null);
      }
    } catch (error) {
      console.error('Session check failed:', error);
      setIsAuthenticated(false);
      setSessionInfo(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Login function
  const login = useCallback(async (password) => {
    try {
      setIsLoading(true);
      
      const response = await fetch(`${API_BASE_URL}/api/auth-session.php`, {
        method: 'POST',
        credentials: 'include', // Important for session cookies
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ password }),
      });

      const data = await response.json();
      
      if (data.success) {
        setIsAuthenticated(true);
        setSessionInfo({
          sessionId: data.session_id,
          expiresAt: data.expires_at,
          expiresInHours: data.expires_in_hours
        });
        return { success: true, message: data.message };
      } else {
        setIsAuthenticated(false);
        setSessionInfo(null);
        return { success: false, error: data.error };
      }
    } catch (error) {
      console.error('Login failed:', error);
      setIsAuthenticated(false);
      setSessionInfo(null);
      return { success: false, error: 'Network error during login' };
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Logout function
  const logout = useCallback(async () => {
    try {
      await fetch(`${API_BASE_URL}/api/auth-session.php`, {
        method: 'DELETE',
        credentials: 'include', // Important for session cookies
        headers: {
          'Content-Type': 'application/json',
        },
      });
    } catch (error) {
      console.error('Logout request failed:', error);
    } finally {
      setIsAuthenticated(false);
      setSessionInfo(null);
    }
  }, []);

  // Check session on component mount and set up periodic checks
  useEffect(() => {
    checkSession();
    
    // Check session every 5 minutes to handle expiration
    const interval = setInterval(checkSession, 5 * 60 * 1000);
    
    return () => clearInterval(interval);
  }, [checkSession]);

  // Auto-refresh session check when page becomes visible (handles browser refresh)
  useEffect(() => {
    const handleVisibilityChange = () => {
      if (!document.hidden) {
        checkSession();
      }
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, [checkSession]);

  return {
    isAuthenticated,
    isLoading,
    sessionInfo,
    login,
    logout,
    checkSession
  };
};
