import { Users } from 'lucide-react';
import MemberAvatar from './MemberAvatar';
import SearchableSelect from './SearchableSelect';

/**
 * Picker for a support member — shows each person's avatar (native <select>
 * can't render images), and a name can be typed instead of picked from the
 * list. Drop-in replacement for a name select.
 *
 * Props:
 *   value      current selected name ('' = none / "all")
 *   onChange   (name) => void
 *   options    array of member names
 *   placeholder text shown when nothing is selected and there's no allLabel
 *   allLabel   when set, adds a leading "all members" option with value ''
 */
const MemberSelect = ({ value, onChange, options, placeholder = 'Select a member…', allLabel = null }) => {
  const items = [
    ...(allLabel ? [{ value: '', label: allLabel, all: true }] : []),
    ...options.map((n) => ({ value: n, label: n })),
  ];

  const renderIcon = (item, where, isSel) => {
    if (where === 'field') {
      return item ? (
        <MemberAvatar name={item.value} sizeClass="w-6 h-6" ringClass="ring-1 ring-teal-100" textClass="text-[10px]" />
      ) : (
        <span className="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
          <Users className="w-3.5 h-3.5 text-slate-400" />
        </span>
      );
    }
    return item.all ? (
      <span className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
        <Users className="w-4 h-4 text-slate-400" />
      </span>
    ) : (
      <MemberAvatar name={item.value} sizeClass="w-8 h-8" ringClass={`ring-2 ${isSel ? 'ring-teal-300' : 'ring-teal-100'}`} />
    );
  };

  return (
    <SearchableSelect
      value={value}
      onChange={onChange}
      items={items}
      placeholder={placeholder}
      renderIcon={renderIcon}
      listMinWidth="min-w-[220px]"
    />
  );
};

export default MemberSelect;
