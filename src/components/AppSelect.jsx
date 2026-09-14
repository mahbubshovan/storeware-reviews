import { useState } from 'react';
import { LayoutGrid } from 'lucide-react';
import { APPS, getAppIcon } from '../config/appConfig';
import SearchableSelect from './SearchableSelect';

/**
 * App icon with a gradient-letter fallback (apps don't have Gravatars, they
 * have Shopify CDN icons). Always renders exactly one element.
 */
export const AppAvatar = ({ appName, sizeClass = 'w-7 h-7', textClass = 'text-xs' }) => {
  const [err, setErr] = useState(false);
  const icon = getAppIcon(appName);
  return icon && !err ? (
    <img src={icon} alt={appName} onError={() => setErr(true)}
      className={`${sizeClass} rounded-lg object-cover border border-slate-100 flex-shrink-0`} />
  ) : (
    <span className={`${sizeClass} rounded-lg bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold flex-shrink-0 ${textClass}`}>
      {appName?.charAt(0)?.toUpperCase() || '?'}
    </span>
  );
};

/**
 * Picker for an app — shows each app's icon (native <select> can't render
 * images), and an app name can be typed instead of picked from the list.
 * Drop-in replacement for an app name select.
 *
 * Props:
 *   value       current app name ('' = none / "all")
 *   onChange    (name) => void
 *   placeholder text shown when nothing is selected and there's no allLabel
 *   allLabel    when set, adds a leading "all apps" option with value ''
 */
const AppSelect = ({ value, onChange, placeholder = 'Select an app…', allLabel = null }) => {
  const items = [
    ...(allLabel ? [{ value: '', label: allLabel, all: true }] : []),
    ...APPS.map((a) => ({ value: a.name, label: a.name })),
  ];

  const renderIcon = (item, where) => {
    if (where === 'field') {
      return item ? (
        <AppAvatar appName={item.value} sizeClass="w-6 h-6" textClass="text-[10px]" />
      ) : (
        <span className="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
          <LayoutGrid className="w-3.5 h-3.5 text-slate-400" />
        </span>
      );
    }
    return item.all ? (
      <span className="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
        <LayoutGrid className="w-4 h-4 text-slate-400" />
      </span>
    ) : (
      <AppAvatar appName={item.value} sizeClass="w-8 h-8" />
    );
  };

  return (
    <SearchableSelect
      value={value}
      onChange={onChange}
      items={items}
      placeholder={placeholder}
      renderIcon={renderIcon}
      listMinWidth="min-w-[240px]"
    />
  );
};

export default AppSelect;
