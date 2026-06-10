import React, { useState } from 'react';
import { ShoppingCart, ChevronLeft, Package } from 'lucide-react';
import { addToCart } from './marketService';

const CAT_EMOJI = {
    Pesticides: '🐛', Fertilizers: '🌿', Seeds: '🌱',
    'Farm Equipment': '🚜', Irrigation: '💧', 'Animal Feed': '🐄',
    'Organic Products': '♻️', Tools: '🔧',
};

const MarketProductDetail = ({ product, user, onBack, onGoToCart, onLoginRequired, t }) => {
    const [qty, setQty] = useState(1);
    const [adding, setAdding] = useState(false);
    const [added, setAdded] = useState(false);

    const stockLabel = product.stock > 10 ? 'in-stock' : product.stock > 0 ? 'low-stock' : 'out-stock';
    const stockText = product.stock > 10
        ? `${t('market_in_stock')} (${product.stock})`
        : product.stock > 0
            ? `${t('market_low_stock')} (${product.stock})`
            : t('market_out_stock');

    const handleAddToCart = async () => {
        if (!user) { onLoginRequired(); return; }
        setAdding(true);
        try {
            await addToCart(user.uid, product, qty);
            setAdded(true);
            setTimeout(() => setAdded(false), 2500);
        } catch (err) { console.error(err); }
        setAdding(false);
    };

    return (
        <div className="product-detail">
            <button className="btn-back-dark" onClick={onBack}>
                <ChevronLeft size={18} /> {t('market_back')}
            </button>

            <div className="product-detail-img">
                {product.imageUrl
                    ? <img src={product.imageUrl} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: 16 }} />
                    : CAT_EMOJI[product.category] || '📦'}
            </div>

            <div style={{ display: 'flex', gap: 8, marginBottom: '0.5rem', flexWrap: 'wrap' }}>
                <span style={{ background: 'var(--breakdown-ok-bg)', color: 'var(--breakdown-ok-color)', padding: '3px 10px', borderRadius: 10, fontSize: '0.8rem', fontWeight: 600 }}>
                    {CAT_EMOJI[product.category]} {product.category}
                </span>
                {product.shopName && (
                    <span style={{ background: 'var(--bg-secondary)', color: 'var(--text-secondary)', padding: '3px 10px', borderRadius: 10, fontSize: '0.8rem' }}>
                        🏪 {product.shopName}
                    </span>
                )}
                {product.distanceKm && (
                    <span style={{ background: 'var(--bg-secondary)', color: 'var(--text-secondary)', padding: '3px 10px', borderRadius: 10, fontSize: '0.8rem' }}>
                        📍 {product.distanceKm} km
                    </span>
                )}
            </div>

            <h2>{product.name}</h2>
            <div className="price-big">₹{product.price}</div>
            <span className={`stock-badge ${stockLabel}`}>{stockText}</span>

            {product.description && <p className="desc">{product.description}</p>}

            <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: '1rem' }}>
                <span style={{ fontWeight: 600, color: 'var(--text-primary)' }}>{t('market_qty')}:</span>
                <div className="qty-controls">
                    <button className="qty-btn" onClick={() => setQty(q => Math.max(1, q - 1))}>−</button>
                    <span className="qty-num">{qty}</span>
                    <button className="qty-btn" onClick={() => setQty(q => Math.min(product.stock || 999, q + 1))}>+</button>
                </div>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                <button
                    className="btn-primary"
                    onClick={handleAddToCart}
                    disabled={adding || product.stock === 0}
                    style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}
                >
                    <ShoppingCart size={18} />
                    {adding ? t('market_adding') : added ? `✅ ${t('market_added')}` : `${t('market_add_to_cart')} — ₹${product.price * qty}`}
                </button>
                {added && (
                    <button className="btn-secondary" onClick={onGoToCart} style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}>
                        <Package size={16} /> {t('market_view_cart')}
                    </button>
                )}
            </div>
        </div>
    );
};

export default MarketProductDetail;
