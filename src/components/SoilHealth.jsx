import React from 'react';
import { Sprout, Droplet, FlaskConical } from 'lucide-react';
import './CardStyles.css'; // Shared styles for cards

const SoilHealth = ({ t, soilData }) => {
    // Default values while loading
    const data = soilData || {
        moisture: 'Loading...', // Or fetch from weather
        ph: '7.0',
        nitrogen: '0.1%',
        organicCarbon: '1.2%',
        type: 'Fetching...'
    };

    // Helper to determine status color and text
    const getStatus = (value, type) => {
        const num = parseFloat(value);
        if (isNaN(num)) return { label: '', color: 'tag-gray' };

        if (type === 'moisture') {
            if (num < 30) return { label: 'Dry', color: 'tag-warning' }; // Need warning style
            if (num > 70) return { label: 'Wet', color: 'tag-blue' };
            return { label: 'Good', color: 'tag-green' };
        }
        if (type === 'nitrogen') {
            if (num < 0.1) return { label: 'Low', color: 'tag-warning' };
            if (num > 0.5) return { label: 'High', color: 'tag-blue' };
            return { label: 'Good', color: 'tag-green' };
        }
        if (type === 'ph') {
            if (num < 6) return { label: 'Acidic', color: 'tag-warning' };
            if (num > 7.5) return { label: 'Alkaline', color: 'tag-blue' };
            return { label: 'Neutral', color: 'tag-green' };
        }
        return { label: '', color: 'tag-gray' };
    };

    const moistureStatus = getStatus(data.moisture, 'moisture');
    const phStatus = getStatus(data.ph, 'ph');
    const nitrogenStatus = getStatus(data.nitrogen, 'nitrogen');

    return (
        <div className="card-container">
            <h3 className="card-title">
                <Sprout size={18} color="#795548" />
                {t('soil_health')} - <span style={{ fontSize: '0.8rem', fontWeight: 400 }}>{t(data.type) || data.type}</span>
            </h3>

            <div className="metric-row">
                <div className="metric-header">
                    <div className="metric-label">
                        <Droplet size={14} className="icon-blue" />
                        <span>{t('moisture')}</span>
                    </div>
                    {/* Fake moisture logic: if data is loading, show text, else show number */}
                    <span className="metric-value">
                        {data.moisture === 'Loading...' ? t('loading') : t.n(data.moisture)}
                        {moistureStatus.label && <span className={`tag ${moistureStatus.color}`} style={{ marginLeft: '6px' }}>{t(moistureStatus.label)}</span>}
                    </span>
                </div>
                {/* Remove bar if text is 'Loading' or non-numeric */}
                <div className="progress-bg">
                    <div className="progress-fill fill-blue" style={{ width: data.moisture === 'Loading...' ? '0%' : (parseInt(data.moisture) || 50) + '%' }}></div>
                </div>
            </div>

            <div className="metric-row">
                <div className="metric-header">
                    <div className="metric-label">
                        <FlaskConical size={14} className="icon-purple" />
                        <span>{t('ph_level')}</span>
                    </div>
                    <span className="metric-value">
                        {t.n(data.ph)}
                        <span className={`tag ${phStatus.color}`}>{t(phStatus.label)}</span>
                    </span>
                </div>

                <div className="metric-header" style={{ marginTop: '12px' }}>
                    <div className="metric-label">
                        <span style={{ fontSize: '12px' }}>{t('nitrogen')}</span>
                    </div>
                    <span className="metric-value">
                        {t.n(data.nitrogen)}
                        {nitrogenStatus.label && <span className={`tag ${nitrogenStatus.color}`} style={{ marginLeft: '6px' }}>{t(nitrogenStatus.label)}</span>}
                    </span>
                </div>
            </div>

            {/* Soil Texture Breakdown for Farmers */}
            {data.composition && (
                <div className="metric-row" style={{ marginTop: '12px', paddingTop: '12px', borderTop: '1px solid #eee' }}>
                    <div className="metric-header" style={{ marginBottom: '6px' }}>
                        <span style={{ fontSize: '12px', fontWeight: 600, color: '#555' }}>{t('texture')}: {t(data.type)}</span>
                    </div>

                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '11px', color: '#666', marginBottom: '4px' }}>
                        <div style={{ color: '#d4a373' }}>{t('sand')}: <b>{t.n(data.composition.sand)}%</b></div>
                        <div style={{ color: '#908677' }}>{t('silt')}: <b>{t.n(data.composition.silt)}%</b></div>
                        <div style={{ color: '#6d4c41' }}>{t('clay')}: <b>{t.n(data.composition.clay)}%</b></div>
                    </div>

                    <div style={{ display: 'flex', height: '8px', borderRadius: '4px', overflow: 'hidden', width: '100%' }}>
                        <div style={{ width: `${data.composition.sand}%`, background: '#d4a373' }} title="Sand"></div>
                        <div style={{ width: `${data.composition.silt}%`, background: '#bcaaa4' }} title="Silt"></div>
                        <div style={{ width: `${data.composition.clay}%`, background: '#6d4c41' }} title="Clay"></div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default SoilHealth;
