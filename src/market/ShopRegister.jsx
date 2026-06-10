import React, { useState } from 'react';
import { ChevronLeft, MapPin } from 'lucide-react';
import { registerShop } from './marketService';

const SHOP_CATEGORIES = [
    'Pesticide Dealer', 'Fertilizer Supplier', 'Seed Store',
    'Farm Equipment', 'Irrigation Systems', 'General Agri Store'
];

const ShopRegister = ({ user, onBack, t, onShopRegistered }) => {
    const [form, setForm] = useState({ name: '', address: '', category: '', lat: '', lng: '' });
    const [loading, setLoading] = useState(false);
    const [gpsLoading, setGpsLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }));

    const getGPS = () => {
        setGpsLoading(true);
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                setForm(f => ({ ...f, lat: pos.coords.latitude.toFixed(6), lng: pos.coords.longitude.toFixed(6) }));
                setGpsLoading(false);
            },
            () => { setError('Could not get GPS. Enter manually.'); setGpsLoading(false); }
        );
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true); setError('');
        try {
            const shopId = await registerShop(user.uid, {
                store_name: form.name,
                name: form.name,
                address: form.address,
                category: form.category,
                latitude: parseFloat(form.lat),
                longitude: parseFloat(form.lng),
                lat: parseFloat(form.lat),
                lng: parseFloat(form.lng),
            });
            const newShop = {
                id: shopId,
                ownerUid: user.uid,
                store_name: form.name,
                name: form.name,
                address: form.address,
                category: form.category,
                latitude: parseFloat(form.lat),
                longitude: parseFloat(form.lng),
                lat: parseFloat(form.lat),
                lng: parseFloat(form.lng),
                status: 'pending'
            };
            if (onShopRegistered) {
                onShopRegistered(newShop);
            } else {
                setSuccess(true);
            }
        } catch {
            setError('Registration failed. Please try again.');
        }
        setLoading(false);
    };

    if (success) {
        return (
            <div className="empty-state">
                <span style={{ fontSize: '3.5rem' }}>🎉</span>
                <p style={{ fontWeight: 700, fontSize: '1.1rem' }}>{t('market_reg_success')}</p>
                <p style={{ color: 'var(--text-secondary)', fontSize: '0.9rem', textAlign: 'center' }}>
                    {t('market_reg_success_desc')}
                </p>
                <button className="btn-primary" style={{ width: '80%' }} onClick={onBack}>{t('market_back')}</button>
            </div>
        );
    }

    return (
        <div>
            <button className="btn-back-dark" onClick={onBack}><ChevronLeft size={18} /> {t('market_back')}</button>
            <h3 style={{ margin: '0 0 4px', color: 'var(--text-primary)' }}>🏪 {t('market_shop_reg_title')}</h3>
            <p style={{ color: 'var(--text-secondary)', fontSize: '0.88rem', margin: '0 0 1.25rem' }}>
                {t('market_shop_reg_desc')}
            </p>

            <form className="market-form" onSubmit={handleSubmit}>
                <input className="market-input" placeholder={t('market_shop_name')} value={form.name} onChange={set('name')} required />
                <input className="market-input" placeholder={t('market_shop_address')} value={form.address} onChange={set('address')} required />
                <select className="market-select" value={form.category} onChange={set('category')} required>
                    <option value="">{t('market_shop_cat')}</option>
                    {SHOP_CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
                </select>
                <div>
                    <div style={{ display: 'flex', gap: 8, marginBottom: 8 }}>
                        <input className="market-input" placeholder="Latitude" value={form.lat} onChange={set('lat')} required style={{ flex: 1 }} />
                        <input className="market-input" placeholder="Longitude" value={form.lng} onChange={set('lng')} required style={{ flex: 1 }} />
                    </div>
                    <button
                        type="button"
                        className="btn-secondary"
                        onClick={getGPS}
                        disabled={gpsLoading}
                        style={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, padding: '0.65rem' }}
                    >
                        <MapPin size={16} />
                        {gpsLoading ? t('market_gps_loading') : t('market_gps_btn')}
                    </button>
                </div>
                {error && <div className="market-error">{error}</div>}
                <button className="btn-primary" type="submit" disabled={loading}>
                    {loading ? t('market_submitting') : t('market_submit')}
                </button>
            </form>
        </div>
    );
};

export default ShopRegister;
