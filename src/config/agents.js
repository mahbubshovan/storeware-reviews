/**
 * Centralized agent configuration shared across Access Reviews and Appwise Reviews pages.
 * MD5 hashes are pre-computed from each agent's work email for Gravatar lookups.
 */
export const AGENTS = [
  { name: 'Ashik',   hash: '150102ffaa35adfdfaffed33ed647d5b' },
  { name: 'Zeba',    hash: '7cd4661a28ea855acd5d0b2cc3422461' },
  { name: 'Amin',    hash: '70ca980dca08edbaab427989902d8d68' },
  { name: 'Nadvi',   hash: 'd76e132df055da26509abb5d849c41e6' },
  { name: 'Amit',    hash: 'b9d40c9f81870ca840ee21992eaac92c' },
  { name: 'Vinz',    hash: 'f924fc59aa9243603ab5e6dd6520b781' },
  { name: 'Jen',     hash: 'a115a5a3e66afb8a08a88f11ef4a0247' },
  { name: 'Abid',    hash: '0783d525385898fd587571fc44f95330' },
  { name: 'Pial',    hash: '953917c9bce73bff51356b7e7d77f276' },
  { name: 'Organic', hash: null }, // Uses ShoppingBag icon — no Gravatar
];

/**
 * Returns the Gravatar URL for an MD5 hash.
 * Falls back to identicon if no real avatar exists.
 */
export const getGravatarUrl = (hash, size = 40) =>
  hash ? `https://www.gravatar.com/avatar/${hash}?s=${size}&d=identicon` : null;

/**
 * Look up an agent by name (case-sensitive).
 * Returns null if not found.
 */
export const getAgentByName = (name) =>
  AGENTS.find((a) => a.name === name) || null;

