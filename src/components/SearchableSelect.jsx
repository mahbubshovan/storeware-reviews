import { useState, useRef, useEffect } from 'react';
import { ChevronDown, Check } from 'lucide-react';

/**
 * Dropdown you can type into — the shared base of MemberSelect and AppSelect.
 * Typing filters the options; arrow keys move the highlight and Enter picks it.
 * Leaving the field keeps a typed name when it matches one option exactly, or
 * is the only match, so a value can be written instead of clicked. Only listed
 * options can be chosen.
 *
 * Props:
 *   value         current selected value ('' = none / "all")
 *   onChange      (value) => void
 *   items         [{ value, label, all? }] — `all` marks the leading "all …" option
 *   placeholder   text shown when nothing is selected and there's no "all" option
 *   renderIcon    (item, where, isSelected) => element; `where` is 'field' or
 *                 'list', and item is null in the field when nothing is selected
 *   listMinWidth  Tailwind min-width class for the open list
 */
const SearchableSelect = ({ value, onChange, items, placeholder, renderIcon, listMinWidth = 'min-w-[220px]' }) => {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [highlight, setHighlight] = useState(0);
  const inputRef = useRef(null);
  const listRef = useRef(null);

  const allItem = items.find((it) => it.all);
  // A saved value that's no longer listed (e.g. a renamed member) still shows as-is.
  const currentLabel = value ? (items.find((it) => it.value === value)?.label ?? value) : '';
  const needle = query.trim().toLowerCase();
  const matches = needle ? items.filter((it) => it.label.toLowerCase().includes(needle)) : items;

  // Keep the highlighted option in view while moving through the list with the keyboard.
  useEffect(() => {
    if (open) listRef.current?.querySelector(`[data-index="${highlight}"]`)?.scrollIntoView({ block: 'nearest' });
  }, [open, highlight]);

  const openList = () => {
    if (open) return;
    setQuery('');
    setHighlight(Math.max(0, items.findIndex((it) => it.value === value)));
    setOpen(true);
  };

  const close = () => {
    setOpen(false);
    setQuery('');
  };

  const pick = (item) => {
    onChange(item.value);
    close();
  };

  // Leaving the field: keep what was typed if it points at exactly one option.
  const commitTyped = () => {
    if (needle) {
      const chosen = items.find((it) => it.label.toLowerCase() === needle) || (matches.length === 1 ? matches[0] : null);
      if (chosen) onChange(chosen.value);
    }
    close();
  };

  const handleKeyDown = (e) => {
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!open) {
        openList();
        return;
      }
      const step = e.key === 'ArrowDown' ? 1 : -1;
      setHighlight((h) => Math.min(Math.max(h + step, 0), matches.length - 1));
    } else if (e.key === 'Enter' && open) {
      e.preventDefault(); // pick the option rather than submit the surrounding form
      if (matches[highlight]) pick(matches[highlight]);
    } else if (e.key === 'Escape' && open) {
      e.preventDefault();
      close();
    }
  };

  return (
    <div className="relative">
      <div
        onMouseDown={(e) => {
          // The icon and chevron toggle the list without taking focus from the input.
          if (e.target === inputRef.current) return;
          e.preventDefault();
          if (open) {
            close();
          } else {
            inputRef.current?.focus();
            openList();
          }
        }}
        className={`w-full flex items-center gap-2 px-3 py-2 bg-white border rounded-lg shadow-sm text-sm text-slate-700 cursor-text transition-all ${open ? 'border-teal-400' : 'border-slate-200 hover:border-teal-400'}`}
      >
        {renderIcon(value ? { value, label: currentLabel } : null, 'field', false)}
        <input
          ref={inputRef}
          type="text"
          role="combobox"
          aria-expanded={open}
          aria-autocomplete="list"
          autoComplete="off"
          value={open ? query : currentLabel}
          placeholder={open ? currentLabel || allItem?.label || placeholder : allItem?.label || placeholder}
          onFocus={openList}
          onClick={openList}
          onChange={(e) => {
            setQuery(e.target.value);
            setHighlight(0);
            setOpen(true);
          }}
          onKeyDown={handleKeyDown}
          onBlur={commitTyped}
          className="flex-1 min-w-0 truncate bg-transparent outline-none placeholder:text-slate-400"
        />
        <ChevronDown className={`w-4 h-4 text-slate-400 flex-shrink-0 transition-transform duration-200 ${open ? 'rotate-180' : ''}`} />
      </div>

      {open && (
        <div
          onMouseDown={(e) => e.preventDefault()} // keep focus in the input while picking
          className={`absolute left-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 z-[9999] w-full ${listMinWidth} overflow-hidden`}
        >
          <ul ref={listRef} role="listbox" className="py-1 max-h-72 overflow-y-auto">
            {matches.length === 0 && <li className="px-3 py-2 text-sm text-slate-400">No matches</li>}
            {matches.map((it, i) => {
              const isSel = value === it.value;
              return (
                <li key={it.value || '__all'} role="option" aria-selected={isSel} data-index={i}>
                  <button
                    type="button"
                    tabIndex={-1}
                    onClick={() => pick(it)}
                    onMouseEnter={() => setHighlight(i)}
                    className={`w-full flex items-center gap-2.5 px-3 py-2 transition-colors ${isSel ? 'bg-teal-50' : i === highlight ? 'bg-slate-50' : ''}`}
                  >
                    {renderIcon(it, 'list', isSel)}
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

export default SearchableSelect;
