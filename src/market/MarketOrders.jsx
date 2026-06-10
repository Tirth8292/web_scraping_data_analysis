import React, { useState, useEffect } from 'react';
import { ChevronLeft, Package } from 'lucide-react';
import { getUserOrders } from './marketService';

const STATUS_STYLE = {
    pending: { bg: '#fff8e1', color: '#f57f17', label: 'Pending' },
    confirmed: { bg: '#e3f2fd', color: '#1565c0', label: 'Confirmed' },
    delivered: { bg: '#e8f5e9', color: '#2e7d32', label: 'Delivered' },
    cancelled: { bg: '#ffebee', color: '#c62828', label: 'Cancelled' },
};

const MarketOrders = ({ user, onBack, onLoginRequired, t }) => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (user) loadOrders();
        else setLoading(false);
    }, [user]);

    const loadOrders = async () => {
        setLoading(true);
        const data = await getUserOrders(user.uid);
        data.sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0));
        setOrders(data);
        setLoading(false);
    };

    if (!user) {
        return (
            <div className="empty-state">
                <span style={{ fontSize: '3rem' }}>🔒</span>
                <p style={{ fontWeight: 600 }}>{t('market_login_required')} {t('market_orders').toLowerCase()}</p>
                <button className="btn-primary" style={{ width: '80%' }} onClick={onLoginRequired}>{t('market_login')}</button>
            </div>
        );
    }

    if (loading) return <div className="market-loading"><div className="market-spinner" /></div>;

    if (orders.length === 0) {
        return (
            <div>
                <button className="btn-back-dark" onClick={onBack}><ChevronLeft size={18} /> {t('market_back')}</button>
                <div className="empty-state">
                    <Package size={48} strokeWidth={1} />
                    <p style={{ fontWeight: 600 }}>{t('market_no_orders')}</p>
                    <p style={{ fontSize: '0.85rem' }}>{t('market_no_orders_desc')}</p>
                    <button className="btn-primary" style={{ width: '80%' }} onClick={onBack}>{t('market_start_shopping')}</button>
                </div>
            </div>
        );
    }

    return (
        <div>
            <button className="btn-back-dark" onClick={onBack}><ChevronLeft size={18} /> {t('market_back')}</button>
            <p className="market-section-title">📋 {t('market_orders')} ({orders.length})</p>
            {orders.map(order => {
                const st = STATUS_STYLE[order.status] || STATUS_STYLE.pending;
                const date = order.createdAt?.seconds
                    ? new Date(order.createdAt.seconds * 1000).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
                    : '';
                return (
                    <div key={order.id} className="order-card">
                        <div className="order-header">
                            <div>
                                <div className="order-id">#{order.id.slice(0, 8).toUpperCase()}</div>
                                <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>{date}</div>
                            </div>
                            <span className="order-status" style={{ background: st.bg, color: st.color }}>{st.label}</span>
                        </div>
                        <div className="order-items">
                            {(order.items || []).map((item, i) => (
                                <div key={i}>• {item.name} × {item.qty}</div>
                            ))}
                        </div>
                        <div className="order-total">{t('market_total')}: ₹{order.total}</div>
                    </div>
                );
            })}
        </div>
    );
};

export default MarketOrders;
