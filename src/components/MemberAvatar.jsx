import { useState } from 'react';
import { getAgentByName, getGravatarUrl } from '../config/agents';

/**
 * Renders a support member's avatar (Gravatar) with a gradient-initial fallback.
 * Always renders exactly one element so it drops cleanly into flex layouts.
 */
const MemberAvatar = ({
  name,
  sizeClass = 'w-9 h-9',
  ringClass = 'ring-2 ring-teal-100',
  textClass = 'text-sm',
}) => {
  const [err, setErr] = useState(false);
  const agent = getAgentByName(name);
  const url = agent && agent.hash ? getGravatarUrl(agent.hash, 80) : null;

  if (!url || err) {
    return (
      <span className={`${sizeClass} rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold flex-shrink-0 ${textClass} ${ringClass}`}>
        {name?.charAt(0)?.toUpperCase() || '?'}
      </span>
    );
  }

  return (
    <img
      src={url}
      alt={name}
      onError={() => setErr(true)}
      className={`${sizeClass} rounded-full object-cover flex-shrink-0 ${ringClass}`}
    />
  );
};

export default MemberAvatar;
