import { useState, useEffect } from 'react';

/**
 * Generate a UUID v4
 */
function generateUUID() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

/**
 * Set a cookie with fallback support
 */
function setCookie(name, value, days = 365) {
  try {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
  } catch (error) {
    console.warn('Failed to set cookie:', error);
  }
}

/**
 * Get a cookie value
 */
function getCookie(name) {
  try {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  } catch (error) {
    console.warn('Failed to get cookie:', error);
    return null;
  }
}

/**
 * Custom hook for managing per-device client ID
 * Uses localStorage as primary storage with cookie fallback
 */
export function useClientId() {
  const [clientId, setClientId] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let id = null;

    try {
      // Try localStorage first (preferred)
      id = localStorage.getItem('shopify_reviews_client_id');
      
      if (!id) {
        // Fallback to cookie
        id = getCookie('shopify_reviews_client_id');
      }

      if (!id) {
        // Generate new UUID if none exists
        id = generateUUID();
        console.log('Generated new client ID:', id);
      }

      // Store in both localStorage and cookie for redundancy
      try {
        localStorage.setItem('shopify_reviews_client_id', id);
      } catch (error) {
        console.warn('localStorage not available:', error);
      }
      
      setCookie('shopify_reviews_client_id', id);

      setClientId(id);
    } catch (error) {
      console.error('Error managing client ID:', error);
      // Generate fallback ID even if storage fails
      id = generateUUID();
      setClientId(id);
    } finally {
      setIsLoading(false);
    }
  }, []);

  /**
   * Force regenerate client ID (useful for testing)
   */
  const regenerateClientId = () => {
    const newId = generateUUID();
    
    try {
      localStorage.setItem('shopify_reviews_client_id', newId);
    } catch (error) {
      console.warn('localStorage not available:', error);
    }
    
    setCookie('shopify_reviews_client_id', newId);
    setClientId(newId);
    
    console.log('Regenerated client ID:', newId);
    return newId;
  };

  /**
   * Clear client ID (useful for testing)
   */
  const clearClientId = () => {
    try {
      localStorage.removeItem('shopify_reviews_client_id');
    } catch (error) {
      console.warn('localStorage not available:', error);
    }
    
    setCookie('shopify_reviews_client_id', '', -1); // Expire cookie
    setClientId(null);
    setIsLoading(true);
    
    // Trigger regeneration
    setTimeout(() => {
      const newId = generateUUID();
      try {
        localStorage.setItem('shopify_reviews_client_id', newId);
      } catch (error) {
        console.warn('localStorage not available:', error);
      }
      setCookie('shopify_reviews_client_id', newId);
      setClientId(newId);
      setIsLoading(false);
    }, 100);
  };

  return {
    clientId,
    isLoading,
    regenerateClientId,
    clearClientId
  };
}

export default useClientId;
