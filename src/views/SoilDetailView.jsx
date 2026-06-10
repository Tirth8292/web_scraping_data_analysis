import React from 'react';
import '../components/CardStyles.css';
import SoilHealth from '../components/SoilHealth';
import CropHealth from '../components/CropHealth';
import CropCalendar from '../components/CropCalendar';

const SoilDetailView = ({ soilData, weather, t }) => {
    if (!soilData) return <div className="loading">{t('loading')}</div>;

    return (
        <div className="view-container">
            <h2 className="view-title">{t('soil_health')} &amp; {t('recommendations')}</h2>

            {/* Reuse existing component for the basics */}
            <SoilHealth t={t} soilData={soilData} />

            {/* Crop Health — same as Home tab */}
            <div style={{ marginTop: '20px' }}>
                <CropHealth t={t} weather={weather} soil={soilData} />
            </div>

            {/* Crop Calendar — same as Home tab */}
            <div style={{ marginTop: '20px' }}>
                <CropCalendar t={t} />
            </div>

            <div className="card-container" style={{ marginTop: '20px' }}>
                <h3 className="card-title">
                    Note
                </h3>
                <p style={{ fontSize: '14px', color: 'var(--text-secondary)', lineHeight: '1.5' }}>
                    {t('soil_note')}
                </p>
            </div>
        </div>
    );
};

export default SoilDetailView;
