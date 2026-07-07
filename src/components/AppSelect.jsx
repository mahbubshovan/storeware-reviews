import { useState, useRef, useEffect } from 'react';
import { ChevronDown, Check, LayoutGrid } from 'lucide-react';
import { APPS, getAppIcon } from '../config/appConfig';

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
 * Custom dropdown for picking an app — shows each app's icon (native <select>
 * can't render images). Drop-in replacement for an app name select.
 *
 * Props:
 *   value       current app name ('' = none / "all")
 *   onChange    (name) => void
 *   placeholder text shown when nothing is selected and there's no allLabel
 *   allLabel    when set, adds a leading "all apps" option with value ''
 */
const AppSelect = ({ value, onChange, placeholder = 'Select an app…', allLabel = null }) => {
  const [open, setOpen] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    const handle = (e) => { if (ref.current && !ref.current.contains(e.target)) setOpen(false); };
    document.addEventListener('mousedown', handle);
    return () => document.removeEventListener('mousedown', handle);
  }, []);

  const pick = (v) => { onChange(v); setOpen(false); };

  const items = [
    ...(allLabel ? [{ value: '', label: allLabel, all: true }] : []),
    ...APPS.map((a) => ({ value: a.name, label: a.name })),
  ];
  const displayLabel = value || allLabel || placeholder;

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        className="w-full flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm text-slate-700 hover:border-teal-400 transition-all"
      >
        {value ? (
          <AppAvatar appName={value} sizeClass="w-6 h-6" textClass="text-[10px]" />
        ) : (
          <span className="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
            <LayoutGrid className="w-3.5 h-3.5 text-slate-400" />
          </span>
        )}
        <span className={`flex-1 text-left truncate ${value ? '' : 'text-slate-400'}`}>{displayLabel}</span>
        <ChevronDown className={`w-4 h-4 text-slate-400 flex-shrink-0 transition-transform duration-200 ${open ? 'rotate-180' : ''}`} />
      </button>

      {open && (
        <div className="absolute left-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 z-[9999] w-full min-w-[240px] overflow-hidden">
          <ul className="py-1 max-h-72 overflow-y-auto">
            {items.map((it) => {
              const isSel = value === it.value;
              return (
                <li key={it.value || '__all'}>
                  <button
                    type="button"
                    onClick={() => pick(it.value)}
                    className={`w-full flex items-center gap-2.5 px-3 py-2 transition-colors ${isSel ? 'bg-teal-50' : 'hover:bg-slate-50'}`}
                  >
                    {it.all ? (
                      <span className="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <LayoutGrid className="w-4 h-4 text-slate-400" />
                      </span>
                    ) : (
                      <AppAvatar appName={it.value} sizeClass="w-8 h-8" />
                    )}
                    <span className={`text-sm font-medium flex-1 text-left truncate ${isSel ? 'text-teal-700' : 'text-slate-700'}`}>
                      {it.label}
                    </span>
                    {isSel && <Check className="w-4 h-4 text-teal-500 flex-shrink-0" />}
                  </button>
                </li>
              );
            })}
          </ul>
        </div>
      )}
    </div>
  );
};

export default AppSelect;
