import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { BarChart3, Users } from 'lucide-react';
import storewareIcon from '../assets/storeware-icon.png';

const Layout = ({ children }) => {
  const location = useLocation();

  const navigation = [
    {
      name: 'Analytics',
      href: '/',
      icon: BarChart3,
      current: location.pathname === '/'
    },
    {
      name: 'Access Reviews',
      href: '/access',
      icon: Users,
      current: location.pathname === '/access'
    }
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Navigation */}
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="flex-shrink-0 flex items-center gap-2.5">
                <img
                  src={storewareIcon}
                  alt="Storeware"
                  className="h-9 w-9 rounded-xl object-cover"
                />
                <div className="flex flex-col leading-tight">
                  <span className="text-base font-bold text-gray-900 leading-none">Storeware Reviews</span>
                  <span className="text-[10px] text-gray-400 leading-tight tracking-wide">Shopify App Review Analytics</span>
                </div>
              </div>
              <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                {navigation.map((item) => {
                  const Icon = item.icon;
                  return (
                    <Link
                      key={item.name}
                      to={item.href}
                      className={`${
                        item.current
                          ? 'border-blue-500 text-gray-900'
                          : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                      } inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium`}
                    >
                      <Icon className="h-4 w-4 mr-2" />
                      {item.name}
                    </Link>
                  );
                })}
              </div>
            </div>
          </div>
        </div>

        {/* Mobile menu */}
        <div className="sm:hidden">
          <div className="pt-2 pb-3 space-y-1">
            {navigation.map((item) => {
              const Icon = item.icon;
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={`${
                    item.current
                      ? 'bg-blue-50 border-blue-500 text-blue-700'
                      : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'
                  } block pl-3 pr-4 py-2 border-l-4 text-base font-medium`}
                >
                  <div className="flex items-center">
                    <Icon className="h-4 w-4 mr-2" />
                    {item.name}
                  </div>
                </Link>
              );
            })}
          </div>
        </div>
      </nav>

      {/* Main content */}
      <main className="flex-1">
        {children}
      </main>
    </div>
  );
};

export default Layout;
