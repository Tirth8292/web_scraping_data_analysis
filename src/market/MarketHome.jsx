import React, { useState, useEffect } from 'react';
import { Search, MapPin } from 'lucide-react';
import { getAllProducts, getNearbyProducts } from './marketService';

const CATEGORIES = ['All', 'Pesticides', 'Fertilizers', 'Seeds', 'Farm Equipment', 'Irrigation', 'Animal Feed', 'Organic Products', 'Tools'];

const CAT_EMOJI = {
    All: '🌾', Pesticides: '🐛', Fertilizers: '🌿', Seeds: '🌱',
    'Farm Equipment': '🚜', Irrigation: '💧', 'Animal Feed': '🐄',
    'Organic Products': '♻️', Tools: '🔧',
};

const CAT_KEY = {
    All: 'market_title', Pesticides: 'market_cat_pesticides', Fertilizers: 'market_cat_fertilizers',
    Seeds: 'market_cat_seeds', 'Farm Equipment': 'market_cat_equipment', Irrigation: 'market_cat_irrigation',
    'Animal Feed': 'market_cat_feed', 'Organic Products': 'market_cat_organic', Tools: 'market_cat_tools',
};

const MarketHome = ({ userLocation, onProductClick, t }) => {
    const [products, setProducts] = useState([]);
    const [category, setCategory] = useState('All');
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(true);
    const [useLocation, setUseLocation] = useState(!!userLocation);

    useEffect(() => {
        loadProducts();
    }, [category, useLocation]);

    const loadProducts = async () => {
        setLoading(true);
        try {
            let data;
            if (useLocation && userLocation) {
                data = await getNearbyProducts(userLocation.lat, userLocation.lng, category === 'All' ? null : category);
            } else {
                data = await getAllProducts(category === 'All' ? null : category);
            }
            setProducts(data || []);
        } catch (err) {
            console.error('Failed to load products:', err);
            setProducts([]);
        }
        setLoading(false);
    };

    const filtered = products.filter(p =>
        p.name.toLowerCase().includes(search.toLowerCase()) ||
        (p.description || '').toLowerCase().includes(search.toLowerCase())
    );

    return (
        <div>
            {/* Search bar */}
            <div style={{ position: 'relative', marginBottom: '0.75rem' }}>
                <Search size={16} style={{ position: 'absolute', left: 12, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-secondary)' }} />
                <input
                    className="market-input"
                    style={{ paddingLeft: '2.25rem' }}
                    placeholder={t('market_search_placeholder')}
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                />
            </div>

            {/* Location toggle */}
            {userLocation && (
                <button
                    onClick={() => setUseLocation(u => !u)}
                    style={{
                        display: 'flex', alignItems: 'center', gap: 6, marginBottom: '0.75rem',
                        padding: '5px 12px', borderRadius: 20, border: '1.5px solid',
                        borderColor: useLocation ? '#2e7d32' : 'var(--text-secondary)',
                        background: useLocation ? '#e8f5e9' : 'transparent',
                        color: useLocation ? '#2e7d32' : 'var(--text-secondary)',
                        fontSize: '0.82rem', fontWeight: 600, cursor: 'pointer'
                    }}
                >
                    <MapPin size={13} />
                    {useLocation ? t('market_nearby_on') : t('market_nearby_off')}
                </button>
            )}

            {/* Category chips */}
            <div className="market-cat-filter">
                {CATEGORIES.map(cat => (
                    <button
                        key={cat}
                        className={`cat-chip ${category === cat ? 'active' : ''}`}
                        onClick={() => setCategory(cat)}
                    >
                        {CAT_EMOJI[cat]} {t(CAT_KEY[cat])}
                    </button>
                ))}
            </div>

            {/* Products */}
            {loading ? (
                <div className="market-loading">
                    <div className="market-spinner" />
                    <span>{t('market_loading')}</span>
                </div>
            ) : filtered.length === 0 ? (
                <div className="empty-state">
                    <span style={{ fontSize: '3rem' }}>🌾</span>
                    <p style={{ fontWeight: 600 }}>{t('market_no_products')}</p>
                    <p style={{ fontSize: '0.85rem' }}>{t('market_try_different')}</p>
                </div>
            ) : (
                <div className="market-grid">
                    {filtered.map(product => (
                        <div key={product.id} className="product-card" onClick={() => onProductClick(product)}>
                            <div className="product-img">
                                {product.imageUrl
                                    ? <img src={product.imageUrl} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                    : CAT_EMOJI[product.category] || '📦'}
                            </div>
                            <div className="product-info">
                                <div className="product-name">{product.name}</div>
                                <div className="product-price">₹{product.price}</div>
                                {product.shopName && <div className="product-shop">🏪 {product.shopName}</div>}
                                {product.distanceKm && <div className="product-dist">📍 {product.distanceKm} km</div>}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default MarketHome;
