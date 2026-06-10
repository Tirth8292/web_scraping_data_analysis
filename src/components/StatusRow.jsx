import React, { useMemo } from 'react';
import { Bell, Sprout } from 'lucide-react';
import './StatusRow.css';

const StatusRow = ({ t, weather, soil }) => {
    // Compute dynamic alert count from weather + soil conditions
    const alertInfo = useMemo(() => {
        let warnings = 0;
        let critical = 0;

        if (weather) {
            if (weather.temp > 40) critical++;
            else if (weather.temp > 35) warnings++;
            if (weather.temp < 5) critical++;
            else if (weather.temp < 10) warnings++;
            if (weather.rainfall > 30) critical++;
            else if (weather.rainfall > 15) warnings++;
            if (weather.humidity > 85) warnings++;
        }

        if (soil) {
            const ph = parseFloat(soil.ph) || 7;
            const moisture = parseFloat(soil.moisture) || 50;
            if (ph < 4.5 || ph > 8.5) critical++;
            else if (ph < 5.5 || ph > 8.0) warnings++;
            if (moisture < 20) critical++;
            else if (moisture < 30) warnings++;
        }

        const total = warnings + critical;
        return { total, critical, warnings };
    }, [weather, soil]);

    // Compute dynamic soil status from pH and moisture
    const soilStatus = useMemo(() => {
        if (!soil) return { label: t('healthy'), className: 'success' };

        const ph = parseFloat(soil.ph) || 7;
        const moisture = parseFloat(soil.moisture) || 50;

        // Critical: truly extreme conditions only
        if (ph < 4.0 || ph > 9.0 || moisture < 10) {
            return { label: t('critical'), className: 'danger' };
        }
        // At Risk: moderately out of range
        if (ph < 5.0 || ph > 8.5 || moisture < 20 || moisture > 95) {
            return { label: t('at_risk'), className: 'warning' };
        }
        return { label: t('healthy'), className: 'success' };
    }, [soil, t]);

    const alertClass = alertInfo.critical > 0 ? 'danger' : alertInfo.total > 0 ? 'warning' : 'success';
    const alertLabel = alertInfo.total > 0
        ? `${alertInfo.total} ${t('warning')}`
        : t('no_alerts');

    return (
        <div className="status-row">
            <div className={`status-card ${alertClass}`}>
                <div className={`icon-wrapper ${alertClass}-bg`}>
                    <Bell size={20} color={alertClass === 'danger' ? '#D32F2F' : alertClass === 'warning' ? '#F57C00' : '#388E3C'} />
                    {alertInfo.total > 0 && <span className="badge">{alertInfo.total}</span>}
                </div>
                <div className="status-text">
                    <span className="label">{t('active_alerts')}</span>
                    <span className={`value ${alertClass}-text`}>{alertLabel}</span>
                </div>
            </div>

            <div className={`status-card ${soilStatus.className}`}>
                <div className={`icon-wrapper ${soilStatus.className}-bg`}>
                    <Sprout size={20} color={soilStatus.className === 'danger' ? '#D32F2F' : soilStatus.className === 'warning' ? '#F57C00' : '#388E3C'} />
                </div>
                <div className="status-text">
                    <span className="label">{t('soil_status')}</span>
                    <span className={`value ${soilStatus.className}-text`}>{soilStatus.label}</span>
                </div>
            </div>
        </div>
    );
};

export default StatusRow;

