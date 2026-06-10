import React, { useState, useEffect } from 'react';
import {
    Bell, AlertTriangle, Wind, CloudLightning, ShieldCheck,
    MessageSquare, Thermometer, Droplets, Leaf, Sprout, Info
} from 'lucide-react';
import '../components/CardStyles.css';

const AlertsView = ({ weather, soil, t }) => {
    const [permission, setPermission] = useState(Notification.permission);
    const [phoneNumber, setPhoneNumber] = useState('');
    const [alerts, setAlerts] = useState([]);

    const sendSMS = () => {
        if (!phoneNumber) { alert(t('enter_phone')); return; }
        alert(`✅ ${t('sms_sent')} ${phoneNumber}`);
        const message = "WARNING: Severe weather/crop risk detected in your area. Please take action. - Smart Farmer App";
        window.open(`sms:${phoneNumber}?body=${encodeURIComponent(message)}`, '_blank');
    };

    useEffect(() => {
        if (!weather) return;
        const newAlerts = [];

        const temp = weather.temp ?? 28;
        const humidity = weather.humidity ?? 60;
        const windSpeed = weather.windSpeed ?? 0;
        const rainfall = weather.rainfall ?? 0;
        const condition = weather.condition?.toLowerCase() ?? '';
        const ph = parseFloat(soil?.ph) || 6.5;
        const moisture = parseFloat(soil?.moisture) || 45;

        // ── 1. Frost Alert ─────────────────────────────────────────────
        if (temp < 5) {
            newAlerts.push({
                type: 'danger',
                title: `❄️ ${t('alert_frost_title')}`,
                message: t('alert_frost_msg'),
                icon: <Thermometer size={24} color="#1565C0" />
            });
        }

        // ── 2. Heat Stress Alert ───────────────────────────────────────
        if (temp > 40) {
            newAlerts.push({
                type: 'danger',
                title: `🌡 ${t('alert_heat_title')}`,
                message: t('alert_heat_msg'),
                icon: <Thermometer size={24} color="#D32F2F" />
            });
        }

        // ── 3. High Wind Alert ─────────────────────────────────────────
        if (windSpeed > 20) {
            newAlerts.push({
                type: 'warning',
                title: `💨 ${t('warning')} — ${t('wind_speed')}`,
                message: `${t('wind_speed')}: ${t.n(windSpeed)} ${t('unit_wind')}. ${t('tip_wind')}`,
                icon: <Wind size={24} color="#F57C00" />
            });
        }

        // ── 4. Storm / Heavy Rain Alert ────────────────────────────────
        if (condition.includes('storm') || condition.includes('thunder') || rainfall > 10) {
            newAlerts.push({
                type: 'danger',
                title: `⛈ ${t('storm_alert')}`,
                message: `${t('rainfall')}: ${t.n(rainfall)}${t('unit_rain')}. ${t('tip_storm')}`,
                icon: <CloudLightning size={24} color="#D32F2F" />
            });
        }

        // ── 5. High Humidity / Fungal Risk ────────────────────────────
        if (humidity > 85) {
            newAlerts.push({
                type: 'warning',
                title: `🍄 ${t('alert_fungal_title')}`,
                message: t('alert_fungal_msg'),
                icon: <Droplets size={24} color="#7B1FA2" />
            });
        }

        // ── 6. Pest Risk Alert ─────────────────────────────────────────
        if (humidity > 80 && temp >= 25 && temp <= 35) {
            newAlerts.push({
                type: 'warning',
                title: `🐛 ${t('alert_pest_title')}`,
                message: t('alert_pest_msg'),
                icon: <Leaf size={24} color="#558B2F" />
            });
        }

        // ── 7. Soil pH Critical ────────────────────────────────────────
        if (soil && (ph < 5.5 || ph > 8.0)) {
            newAlerts.push({
                type: 'warning',
                title: `🧪 ${t('alert_ph_title')} (${t.n(ph)})`,
                message: ph < 5.5 ? t('alert_ph_acidic') : t('alert_ph_alkaline'),
                icon: <Info size={24} color="#F57C00" />
            });
        }

        // ── 8. Irrigation Advisory ─────────────────────────────────────
        if (moisture < 25 && rainfall === 0) {
            newAlerts.push({
                type: 'warning',
                title: `💧 ${t('alert_irrigation_title')}`,
                message: t('alert_irrigation_msg'),
                icon: <Droplets size={24} color="#0288D1" />
            });
        }

        // ── 9. Spray Advisory (Positive) ──────────────────────────────
        if (windSpeed < 10 && rainfall === 0 && humidity < 80) {
            newAlerts.push({
                type: 'info',
                title: `✅ ${t('alert_spray_title')}`,
                message: t('alert_spray_msg'),
                icon: <Sprout size={24} color="#388E3C" />
            });
        }

        // ── Fallback: All Clear ────────────────────────────────────────
        const nonInfo = newAlerts.filter(a => a.type !== 'info');
        if (nonInfo.length === 0) {
            newAlerts.unshift({
                type: 'success',
                title: `✅ ${t('alert_no_warnings')}`,
                message: t('alert_no_warnings_msg'),
                icon: <ShieldCheck size={24} color="#388E3C" />
            });
        }

        setAlerts(newAlerts);
    }, [weather, soil]);

    // Works on both desktop (new Notification) and mobile (SW showNotification)
    const sendNotification = async (title, body, icon = '/logo.png') => {
        if (Notification.permission !== 'granted') return;
        try {
            if ('serviceWorker' in navigator) {
                const registration = await navigator.serviceWorker.ready;
                await registration.showNotification(title, {
                    body,
                    icon,
                    badge: '/logo.png',
                    vibrate: [200, 100, 200],
                });
            } else {
                new Notification(title, { body, icon });
            }
        } catch (err) {
            console.warn('SW notification failed, falling back:', err);
            new Notification(title, { body, icon });
        }
    };

    const requestNotificationPermission = async () => {
        if (!('Notification' in window)) { alert("This browser does not support notifications"); return; }
        const result = await Notification.requestPermission();
        setPermission(result);
        if (result === 'granted') {
            sendNotification(
                t('app_name'),
                "Notifications enabled! You'll be alerted of severe weather & crop risks."
            );
        }
    };

    const simulateStorm = async () => {
        if (permission !== 'granted') {
            alert("Please enable notifications first!");
            return;
        }
        await sendNotification(
            "⚠️ " + t("storm_alert"),
            "Cyclone Warning: High winds (80km/h) expected in 1 hour. Seek shelter immediately."
        );
    };

    // Color map for alert types
    const alertStyle = {
        danger: { border: '#D32F2F', bg: 'var(--card-bg)' },
        warning: { border: '#F57C00', bg: 'var(--card-bg)' },
        info: { border: '#0288D1', bg: 'var(--card-bg)' },
        success: { border: '#388E3C', bg: 'var(--card-bg)' },
    };

    return (
        <div className="view-container">
            <h2 className="view-title">{t('active_alerts')}</h2>

            {/* Permission Banner */}
            {permission !== 'granted' && (
                <div className="recommendation-card" style={{ marginBottom: '20px', borderLeftColor: '#2196F3' }}>
                    <Bell size={24} color="#2196F3" style={{ marginRight: '16px' }} />
                    <div style={{ flex: 1 }}>
                        <h4 style={{ margin: 0, fontSize: '16px' }}>{t('enable_notifications')}</h4>
                        <p style={{ margin: '4px 0 0', fontSize: '12px', color: '#666' }}>{t('notify_desc')}</p>
                    </div>
                    <button onClick={requestNotificationPermission} style={{ background: '#2196F3', color: 'white', padding: '8px 16px', borderRadius: '8px', fontWeight: 600, cursor: 'pointer' }}>
                        {t('enable')}
                    </button>
                </div>
            )}

            {/* Alert Count Summary */}
            {alerts.filter(a => a.type === 'danger' || a.type === 'warning').length > 0 && (
                <div style={{ marginBottom: '16px', padding: '10px 14px', background: 'rgba(230, 81, 0, 0.1)', borderRadius: '10px', display: 'flex', alignItems: 'center', gap: '10px', border: '1px solid rgba(230, 81, 0, 0.2)' }}>
                    <AlertTriangle size={18} color="#E65100" />
                    <span style={{ fontSize: '13px', color: '#E65100', fontWeight: 600 }}>
                        {t.n(alerts.filter(a => a.type === 'danger').length)} {t('critical')}, {t.n(alerts.filter(a => a.type === 'warning').length)} {t('warning')}
                    </span>
                </div>
            )}

            {/* Alert List */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                {alerts.map((alert, index) => (
                    <div key={index} className="recommendation-card" style={{
                        borderLeftColor: alertStyle[alert.type]?.border ?? '#888',
                        background: alertStyle[alert.type]?.bg ?? '#fff',
                        alignItems: 'flex-start'
                    }}>
                        <div style={{ marginRight: '14px', marginTop: '2px' }}>{alert.icon}</div>
                        <div>
                            <h4 style={{ margin: 0, fontSize: '15px', color: 'var(--text-primary)' }}>{alert.title}</h4>
                            <p style={{ margin: '4px 0 0', fontSize: '13px', color: 'var(--text-secondary)', lineHeight: '1.4' }}>{alert.message}</p>
                        </div>
                    </div>
                ))}
            </div>

            {/* Safety Tips */}
            {alerts.some(a => a.type === 'warning' || a.type === 'danger') && (
                <div style={{ marginTop: '20px', padding: '16px', background: 'rgba(245, 124, 0, 0.08)', borderRadius: '12px', borderLeft: '4px solid #F57C00', border: '1px solid rgba(245, 124, 0, 0.2)' }}>
                    <h4 style={{ margin: '0 0 10px 0', display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-primary)' }}>
                        <ShieldCheck size={18} color="#F57C00" />
                        {t('safety_tips')}
                    </h4>
                    <ul style={{ margin: 0, paddingLeft: '20px', fontSize: '13px', color: 'var(--text-secondary)', lineHeight: '1.8' }}>
                        {alerts.some(a => a.type === 'warning' && a.title.includes('Wind')) && <li>{t('tip_wind')}</li>}
                        {alerts.some(a => a.type === 'danger' && a.title.includes('Storm')) && <li>{t('tip_storm')}</li>}
                        {alerts.some(a => a.title.includes(t('alert_pest_title').slice(0, 5)) || a.title.includes('Pest') || a.title.includes('જીવાત')) && <li>{t('tip_pest')}</li>}
                        {alerts.some(a => a.title.includes(t('alert_fungal_title').slice(0, 3)) || a.title.includes('Fungal') || a.title.includes('ફૂગ')) && <li>{t('tip_fungal')}</li>}
                        {alerts.some(a => a.title.includes(t('alert_frost_title').slice(0, 3)) || a.title.includes('Frost') || a.title.includes('હિમ')) && <li>{t('tip_frost')}</li>}
                        {alerts.some(a => a.title.includes(t('alert_heat_title').slice(0, 3)) || a.title.includes('Heat') || a.title.includes('ગરમ')) && <li>{t('tip_heat')}</li>}
                        <li>{t('tip_general')}</li>
                    </ul>
                </div>
            )}

            {/* Test Simulation */}
            <div style={{ marginTop: '20px', padding: '16px', background: 'rgba(245, 124, 0, 0.08)', borderRadius: '12px', border: '1px solid rgba(245, 124, 0, 0.15)' }}>
                <h4 style={{ margin: '0 0 10px 0', display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-primary)' }}>
                    <AlertTriangle size={18} color="#F57C00" />
                    Test Simulation
                </h4>
                <p style={{ fontSize: '12px', marginBottom: '12px', color: 'var(--text-secondary)' }}>Test how a push notification looks on your device:</p>
                <button onClick={simulateStorm} style={{ background: '#D32F2F', color: 'white', padding: '10px 20px', borderRadius: '8px', width: '100%', fontWeight: 'bold', cursor: 'pointer' }}>
                    🚨 Simulate Cyclone Alert
                </button>
            </div>

            {/* SMS Section */}
            <div style={{ marginTop: '16px', padding: '16px', background: 'rgba(2, 136, 209, 0.08)', borderRadius: '12px', border: '1px solid rgba(2, 136, 209, 0.15)' }}>
                <h4 style={{ margin: '0 0 10px 0', display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-primary)' }}>
                    <MessageSquare size={18} color="#1565C0" />
                    {t('sms_alerts')}
                </h4>
                <div style={{ display: 'flex', gap: '8px', flexDirection: 'column' }}>
                    <input
                        type="tel"
                        placeholder="+91 98765 43210"
                        value={phoneNumber}
                        onChange={(e) => setPhoneNumber(e.target.value)}
                        style={{ padding: '10px', borderRadius: '8px', border: '1px solid var(--border-color)', width: '100%', boxSizing: 'border-box', background: 'var(--input-bg)', color: 'var(--input-text)' }}
                    />
                    <button onClick={sendSMS} style={{ background: '#1565C0', color: 'white', padding: '10px 20px', borderRadius: '8px', fontWeight: 'bold', cursor: 'pointer' }}>
                        {t('send_sms')}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default AlertsView;
