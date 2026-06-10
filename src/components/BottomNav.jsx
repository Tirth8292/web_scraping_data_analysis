import React, { useState } from 'react';
import { Home, CloudSun, Sprout, Bell, ShoppingCart } from 'lucide-react';
import './BottomNav.css';

const BottomNav = ({ t, activeTab, setActiveTab }) => {
    // const [active, setActive] = useState('Home'); // Lifted to App.jsx

    const navItems = [
        { name: 'Home', label: t('nav_home'), icon: Home },
        { name: 'Weather', label: t('nav_weather'), icon: CloudSun },
        { name: 'Soil', label: t('nav_soil'), icon: Sprout },
        { name: 'Alerts', label: t('nav_alerts'), icon: Bell },
        { name: 'Market', label: t('nav_market'), icon: ShoppingCart }
    ];

    return (
        <div className="bottom-nav">
            {navItems.map((item) => (
                <button
                    key={item.name}
                    className={`nav-item ${activeTab === item.name ? 'active' : ''}`}
                    onClick={() => setActiveTab(item.name)}
                >
                    <div className={`icon-container ${activeTab === item.name ? 'active-bg' : ''}`}>
                        <item.icon size={20} className={activeTab === item.name ? 'icon-active' : 'icon-inactive'} />
                    </div>
                    <span className="nav-label">{item.label}</span>
                </button>
            ))}
        </div>
    );
};

export default BottomNav;
