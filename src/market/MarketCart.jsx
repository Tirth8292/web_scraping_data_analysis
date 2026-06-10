import React, { useState, useEffect } from 'react';
import { ShoppingBag, Trash2, ChevronLeft } from 'lucide-react';
import { getCart, updateCartQty, removeFromCart, placeOrder } from './marketService';

const MarketCart = ({ user, onBack, onOrderPlaced, onLoginRequired, t }) => {
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [placing, setPlacing] = useState(false);

    useEffect(() => {
        if (user) loadCart();
        else setLoading(false);
    }, [user]);

    const loadCart = async () => {
        setLoading(true);
        const data = await getCart(user.uid);
        setItems(data);
        setLoading(false);
    };

    const handleQty = async (item, delta) => {
        const newQty = item.qty + delta;
        setItems(prev => newQty <= 0
            ? prev.filter(i => i.id !== item.id)
            : prev.map(i => i.id === item.id ? { ...i, qty: newQty } : i)
        );
        await updateCartQty(user.uid, item.id, newQty);
    };

    const handleRemove = async (item) => {
        setItems(prev => prev.filter(i => i.id !== item.id));
        await removeFromCart(user.uid, item.id);
    };

    const handlePlaceOrder = async () => {
        if (!user) { onLoginRequired(); return; }
        setPlacing(true);
        const total = items.reduce((s, i) => s + i.price * i.qty, 0);
        await placeOrder(user.uid, items, total);
        setItems([]);
        setPlacing(false);
        onOrderPlaced();
    };

    const total = items.reduce((s, i) => s + i.price * i.qty, 0);
    const totalItems = items.reduce((s, i) => s + i.qty, 0);

    if (!user) {
        return (
            <div className="empty-state">
                <span style={{ fontSize: '3rem' }}>🔒</span>
                <p style={{ fontWeight: 600 }}>{t('market_login_required')} cart</p>
                <button className="btn-primary" style={{ width: '80%' }} onClick={onLoginRequired}>{t('market_login')}</button>
            </div>
        );
    }

    if (loading) return <div className="market-loading"><div className="market-spinner" /></div>;

    if (items.length === 0) {
        return (
            <div>
                <button className="btn-back-dark" onClick={onBack}><ChevronLeft size={18} /> {t('market_browse')}</button>
                <div className="empty-state">
                    <span style={{ fontSize: '3rem' }}>🛒</span>
                    <p style={{ fontWeight: 600 }}>{t('market_cart_empty')}</p>
                    <p style={{ fontSize: '0.85rem' }}>{t('market_cart_empty_desc')}</p>
                    <button className="btn-primary" style={{ width: '80%' }} onClick={onBack}>{t('market_browse')}</button>
                </div>
            </div>
        );
    }

    return (
        <div>
            <button className="btn-back-dark" onClick={onBack}><ChevronLeft size={18} /> {t('market_continue_shopping')}</button>
            <p className="market-section-title">🛒 {t('market_orders')} ({items.length})</p>

            {items.map(item => (
                <div key={item.id} className="cart-item">
                    <div className="cart-item-icon">📦</div>
                    <div className="cart-item-info">
                        <div className="cart-item-name">{item.name}</div>
                        <div className="cart-item-price">₹{item.price} × {item.qty} = ₹{item.price * item.qty}</div>
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6, alignItems: 'center' }}>
                        <div className="qty-controls">
                            <button className="qty-btn" onClick={() => handleQty(item, -1)}>−</button>
                            <span className="qty-num">{item.qty}</span>
                            <button className="qty-btn" onClick={() => handleQty(item, 1)}>+</button>
                        </div>
                        <button onClick={() => handleRemove(item)} style={{ background: 'none', border: 'none', color: '#ef5350', cursor: 'pointer', padding: 4 }}>
                            <Trash2 size={16} />
                        </button>
                    </div>
                </div>
            ))}

            <div className="cart-total-bar">
                <div className="cart-total-row">
                    <span>{t('market_total')} ({totalItems} {t('market_items')})</span>
                    <span className="cart-total-amount">₹{total}</span>
                </div>
                <button
                    className="btn-primary"
                    style={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}
                    onClick={handlePlaceOrder}
                    disabled={placing}
                >
                    <ShoppingBag size={18} />
                    {placing ? t('market_placing') : t('market_place_order')}
                </button>
            </div>
        </div>
    );
};

export default MarketCart;
