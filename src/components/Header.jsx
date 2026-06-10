import React, { useState } from 'react';
import { Leaf, MapPin, Globe, Search, X, Moon, Sun, ChevronDown } from 'lucide-react';
import './Header.css';

const LANGUAGES = [
    { code: 'en', label: 'English' },
    { code: 'hi', label: 'हिन्दी' },
    { code: 'gu', label: 'ગુજરાતી' },
];



const Header = ({ location, t, lang, onLangChange, onSearch, darkMode, toggleDark }) => {
    const [isSearching, setIsSearching] = useState(false);
    const [query, setQuery] = useState('');
    const [langOpen, setLangOpen] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (query.trim()) {
            onSearch(query);
            setIsSearching(false);
            setQuery('');
        }
    };
    const currentLabel = LANGUAGES.find(l => l.code === lang)?.label || 'English';

    return (
        <header className="header">
            <div className="header-top">
                {isSearching ? (
                    <form className="search-form" onSubmit={handleSubmit}>
                        <Search size={18} className="search-icon-input" />
                        <input
                            type="text"
                            className="search-input"
                            placeholder={t('search_placeholder')}
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            autoFocus
                        />
                        <button type="button" className="close-search" onClick={() => setIsSearching(false)}>
                            <X size={20} />
                        </button>
                    </form>
                ) : (
                    <>
                        <div className="logo-section">
                            <img src="/logo.png" alt="Leaf Logo" className="logo-img" />
                            <div>
                                <h1 className="app-name">{t('app_name')}</h1>
                                <div className="location" onClick={() => setIsSearching(true)}>
                                    <MapPin size={12} />
                                    <span>{location || t('detecting_loc')}</span>
                                </div>
                            </div>
                        </div>
                        <div className="header-actions">
                            <button className="icon-btn" onClick={() => setIsSearching(true)} title="Search location">
                                <Search size={20} />
                            </button>
                            {/* Dark Mode Toggle */}
                            <button
                                className="dark-btn"
                                onClick={toggleDark}
                                title={darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'}
                            >
                                {darkMode ? <Sun size={17} color="#F59E0B" /> : <Moon size={17} color="#6B7280" />}
                            </button>
                            {/* Language Dropdown */}
                            <div className="lang-dropdown-wrapper">
                                <button className="lang-btn" onClick={() => setLangOpen(!langOpen)}>
                                    <Globe size={16} />
                                    <span>{currentLabel}</span>
                                    <ChevronDown size={14} className={`lang-chevron ${langOpen ? 'open' : ''}`} />
                                </button>
                                {langOpen && (
                                    <>
                                        <div className="lang-dropdown-backdrop" onClick={() => setLangOpen(false)} />
                                        <div className="lang-dropdown">
                                            {LANGUAGES.map(l => (
                                                <button
                                                    key={l.code}
                                                    className={`lang-option ${l.code === lang ? 'active' : ''}`}
                                                    onClick={() => { onLangChange(l.code); setLangOpen(false); }}
                                                >
                                                    {l.label}
                                                </button>
                                            ))}
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </>
                )}
            </div>
        </header>
    );
};

export default Header;
