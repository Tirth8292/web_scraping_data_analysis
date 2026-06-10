import React, { useState } from 'react';
import { CalendarDays, ChevronDown } from 'lucide-react';
import './CardStyles.css';
import './CropCalendar.css';

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Sow: months of sowing (1-indexed), Grow: growing months, Harvest: harvest months
const CROPS = [
    {
        name: 'Wheat', icon: '🍞', season: 'Rabi',
        sow: [11, 12], grow: [1, 2, 3], harvest: [4, 5]
    },
    {
        name: 'Rice (Paddy)', icon: '🌾', season: 'Kharif',
        sow: [6, 7], grow: [8, 9], harvest: [10, 11]
    },
    {
        name: 'Cotton', icon: '🌿', season: 'Kharif',
        sow: [4, 5, 6], grow: [7, 8, 9], harvest: [10, 11, 12]
    },
    {
        name: 'Maize', icon: '🌽', season: 'Kharif',
        sow: [6, 7], grow: [8, 9], harvest: [10, 11]
    },
    {
        name: 'Sugarcane', icon: '🍬', season: 'Annual',
        sow: [2, 3, 4], grow: [5, 6, 7, 8, 9, 10, 11], harvest: [12, 1]
    },
    {
        name: 'Groundnut', icon: '🥜', season: 'Kharif',
        sow: [6, 7], grow: [8, 9, 10], harvest: [11, 12]
    },
    {
        name: 'Tobacco', icon: '🍂', season: 'Rabi',
        sow: [10, 11], grow: [12, 1, 2, 3], harvest: [4, 5]
    },
    {
        name: 'Bajra (Pearl Millet)', icon: '🌾', season: 'Kharif',
        sow: [6, 7], grow: [8, 9], harvest: [10, 11]
    },
    {
        name: 'Castor', icon: '🌱', season: 'Kharif',
        sow: [7, 8], grow: [9, 10, 11], harvest: [12, 1, 2]
    },
    {
        name: 'Cumin', icon: '🌿', season: 'Rabi',
        sow: [11, 12], grow: [1, 2], harvest: [3, 4]
    },
    {
        name: 'Banana', icon: '🍌', season: 'Annual',
        sow: [6, 7, 8], grow: [9, 10, 11, 12, 1, 2], harvest: [3, 4, 5]
    },
];

const getPhaseForMonth = (crop, month) => {
    if (crop.sow.includes(month)) return 'sow';
    if (crop.grow.includes(month)) return 'grow';
    if (crop.harvest.includes(month)) return 'harvest';
    return 'off';
};

const getActionBadge = (crop, month, t) => {
    const phase = getPhaseForMonth(crop, month);
    const map = {
        sow: { label: t('badge_sow'), color: '#1565C0', bg: '#E3F2FD' },
        grow: { label: t('badge_growing'), color: '#2E7D32', bg: '#E8F5E9' },
        harvest: { label: t('badge_harvest'), color: '#E65100', bg: '#FFF3E0' },
        off: { label: t('badge_off'), color: '#9E9E9E', bg: '#F5F5F5' },
    };
    return map[phase];
};

const phaseColor = { sow: '#42A5F5', grow: '#66BB6A', harvest: '#FFA726', off: '#E0E0E0' };

const CropCalendar = ({ t }) => {
    const currentMonth = new Date().getMonth() + 1; // 1–12
    const [expanded, setExpanded] = useState(null);

    return (
        <div className="card-container">
            <h3 className="card-title">
                <CalendarDays size={18} color="#4CAF50" />
                {t('crop_calendar')}
            </h3>

            {/* Month header strip */}
            <div className="cal-month-strip">
                {MONTHS.map((m, i) => (
                    <div key={m} className={`cal-month-label ${i + 1 === currentMonth ? 'cal-month-current' : ''}`}>
                        {m}
                    </div>
                ))}
            </div>

            {/* Crop rows */}
            {CROPS.map((crop) => {
                const badge = getActionBadge(crop, currentMonth, t);
                const isExpanded = expanded === crop.name;

                return (
                    <div key={crop.name} className="cal-crop-row">
                        {/* Crop label + badge */}
                        <button
                            className="cal-crop-header"
                            onClick={() => setExpanded(isExpanded ? null : crop.name)}
                        >
                            <div className="cal-crop-name">
                                <span className="cal-crop-icon">{crop.icon}</span>
                                <div>
                                    <span className="cal-crop-label">{t(crop.name) || crop.name}</span>
                                    <span className="cal-season-tag">
                                        {crop.season === 'Kharif' ? t('season_kharif') : crop.season === 'Rabi' ? t('season_rabi') : t('season_annual')}
                                    </span>
                                </div>
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                <span className="cal-badge" style={{ color: badge.color, background: badge.bg }}>
                                    {badge.label}
                                </span>
                                <ChevronDown size={14} color="#999" style={{ transform: isExpanded ? 'rotate(180deg)' : 'none', transition: '0.2s' }} />
                            </div>
                        </button>

                        {/* Month color bar */}
                        <div className="cal-bar-strip">
                            {MONTHS.map((_, i) => {
                                const m = i + 1;
                                const phase = getPhaseForMonth(crop, m);
                                const isCurrent = m === currentMonth;
                                return (
                                    <div
                                        key={m}
                                        className={`cal-bar-cell ${isCurrent ? 'cal-bar-current' : ''}`}
                                        style={{ background: phaseColor[phase] }}
                                        title={phase}
                                    />
                                );
                            })}
                        </div>

                        {/* Expanded detail */}
                        {isExpanded && (
                            <div className="cal-detail">
                                <div className="cal-detail-item" style={{ color: '#1565C0' }}>
                                    🌱 <strong>{t('cal_sow_label')}:</strong> {crop.sow.map(m => MONTHS[m - 1]).join(', ')}
                                </div>
                                <div className="cal-detail-item" style={{ color: '#2E7D32' }}>
                                    🌿 <strong>{t('cal_grow_label')}:</strong> {crop.grow.map(m => MONTHS[m - 1]).join(', ')}
                                </div>
                                <div className="cal-detail-item" style={{ color: '#E65100' }}>
                                    🌾 <strong>{t('cal_harvest_label')}:</strong> {crop.harvest.map(m => MONTHS[m - 1]).join(', ')}
                                </div>
                            </div>
                        )}
                    </div>
                );
            })}

            {/* Legend */}
            <div className="cal-legend">
                {[['Sow', '#42A5F5'], ['Growing', '#66BB6A'], ['Harvest', '#FFA726'], ['Off Season', '#E0E0E0']].map(([label, color]) => (
                    <div key={label} className="cal-legend-item">
                        <div className="cal-legend-dot" style={{ background: color }} />
                        <span>{label}</span>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default CropCalendar;
