/**
 * Centralized application configuration shared across all dashboard pages.
 * Contains both agent roster (with Gravatar hashes) and app icon mappings.
 */

// Re-export agent helpers so callers can import from one place
export { AGENTS, getGravatarUrl, getAgentByName } from './agents';

/**
 * Real app icon URLs from the Shopify App Store CDN.
 * Keys must match the app_name values returned by the backend API.
 * Falls back to a gradient letter avatar when null or when image fails to load.
 */
export const APP_ICONS = {
  'StoreSEO':
    'https://cdn.shopify.com/app-store/listing_images/48671ebc3a6d29e873fbe50b199f2039/icon/CJjHsrX894wDEAE=.png',
  'StoreFAQ':
    'https://cdn.shopify.com/app-store/listing_images/c64d383e322501e8a6c50c13936aa9ec/icon/CMXVz6DLiIoDEAE=.png',
  'EasyFlow':
    'https://cdn.shopify.com/app-store/listing_images/f08acbb1474e3d8959abb778fb2dd664/icon/CNDnsqfFiIoDEAE=.png',
  'TrustSync':
    'https://cdn.shopify.com/app-store/listing_images/339be005e835ad8abca2f5df8e624654/icon/CKOC2-nolYMDEAE=.png',
  'Vidify':
    'https://cdn.shopify.com/app-store/listing_images/9a53f62110dd9563881f8a3a8b5a9712/icon/CPTIwaWIyI0DEAE=.png',
  'BetterDocs FAQ Knowledge Base':
    'https://cdn.shopify.com/app-store/listing_images/47825d38160ade54f44e7a0c9cbed73b/icon/CP663dvqnYMDEAE=.png',
  // Alias used in some backend responses
  'BetterDocs FAQ':
    'https://cdn.shopify.com/app-store/listing_images/47825d38160ade54f44e7a0c9cbed73b/icon/CP663dvqnYMDEAE=.png',
};

/**
 * Ordered list of apps for consistent display across all tabs/dropdowns.
 * Each entry carries the canonical display name, the backend slug, and the icon URL.
 */
export const APPS = [
  { name: 'StoreSEO',                        slug: 'storeseo',               icon: APP_ICONS['StoreSEO'] },
  { name: 'StoreFAQ',                        slug: 'storefaq',               icon: APP_ICONS['StoreFAQ'] },
  { name: 'EasyFlow',                        slug: 'product-options-4',      icon: APP_ICONS['EasyFlow'] },
  { name: 'TrustSync',                       slug: 'customer-review-app',    icon: APP_ICONS['TrustSync'] },
  { name: 'Vidify',                          slug: 'vidify',                 icon: APP_ICONS['Vidify'] },
  { name: 'BetterDocs FAQ Knowledge Base',   slug: 'betterdocs-knowledgebase', icon: APP_ICONS['BetterDocs FAQ Knowledge Base'] },
];

/**
 * Returns the icon URL for an app name. Falls back to null if not found.
 */
export const getAppIcon = (appName) => APP_ICONS[appName] ?? null;

