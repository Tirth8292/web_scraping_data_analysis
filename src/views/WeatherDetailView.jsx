import React from 'react';
import WeatherCard from '../components/WeatherCard';
import { Droplet, Thermometer, Wind, Eye, Gauge, CloudRain, TrendingUp, Calendar } from 'lucide-react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Bar, ComposedChart } from 'recharts';
import '../components/CardStyles.css';
import './WeatherDetailView.css';

/* ── Tiny custom tooltip ─────────────────────────── */
const ChartTooltip = ({ active, payload, label, t }) => {
    if (!active || !payload?.length) return null;
    return (
        <div className="chart-tooltip">
            <p className="chart-tooltip-label">{label}</p>
            {payload.map((entry, i) => (
                <p key={i} style={{ color: entry.color }} className="chart-tooltip-row">
                    {entry.name}: <strong>{t.n(entry.value)}{entry.name === (t('chance_of_rain') || 'Rain %') ? '%' : t('unit_temp')}</strong>
                </p>
            ))}
        </div>
    );
};

const WeatherDetailView = ({ weather, t }) => {
    if (!weather) return <div className="loading">{t('loading')}</div>;

    const hasForecast = weather.forecast && weather.forecast.length > 0;

    /* Build chart data from forecast */
    const chartData = hasForecast
        ? weather.forecast.map(item => ({
            time: item.time,
            temp: item.temp,
            rain: item.chanceOfRain
        }))
        : [];

    return (
        <div className="view-container weather-detail-view">
            <WeatherCard data={weather} t={t} />

            {/* ── Current Details ──────────────────────── */}
            <div className="card-container">
                <h3 className="card-title">{t('current_details')}</h3>
                <div className="details-grid">
                    <div className="detail-item">
                        <div className="detail-icon-wrap icon-bg-orange">
                            <Thermometer size={18} />
                        </div>
                        <div>
                            <span className="detail-label">{t('feels_like')}</span>
                            <span className="detail-value">{t.n(weather.feelsLike)}{t('unit_temp')}</span>
                        </div>
                    </div>
                    <div className="detail-item">
                        <div className="detail-icon-wrap icon-bg-blue">
                            <Droplet size={18} />
                        </div>
                        <div>
                            <span className="detail-label">{t('humidity')}</span>
                            <span className="detail-value">{t.n(weather.humidity)}%</span>
                        </div>
                    </div>
                    <div className="detail-item">
                        <div className="detail-icon-wrap icon-bg-cyan">
                            <CloudRain size={18} />
                        </div>
                        <div>
                            <span className="detail-label">{t('precipitation')}</span>
                            <span className="detail-value">{t.n(weather.rainfall)} {t('unit_rain')}</span>
                        </div>
                    </div>
                    <div className="detail-item">
                        <div className="detail-icon-wrap icon-bg-teal">
                            <Wind size={18} />
                        </div>
                        <div>
                            <span className="detail-label">{t('wind_speed')}</span>
                            <span className="detail-value">{t.n(weather.windSpeed)} {t('unit_wind')}</span>
                        </div>
                    </div>
                    <div className="detail-item">
                        <div className="detail-icon-wrap icon-bg-purple">
                            <Gauge size={18} />
                        </div>
                        <div>
                            <span className="detail-label">{t('pressure')}</span>
                            <span className="detail-value">{t.n(weather.pressure)} {t('unit_pressure')}</span>
                        </div>
                    </div>
                    <div className="detail-item">
                        <div className="detail-icon-wrap icon-bg-green">
                            <Eye size={18} />
                        </div>
                        <div>
                            <span className="detail-label">{t('visibility')}</span>
                            <span className="detail-value">{t.n(weather.visibility)} {t('unit_visibility')}</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── Forecast Chart ──────────────────────── */}
            {hasForecast && (
                <div className="card-container forecast-chart-card">
                    <h3 className="card-title">
                        <TrendingUp size={18} className="icon-blue" />
                        {t('forecast_chart') || 'Temperature & Rain Trend'}
                    </h3>
                    <div className="forecast-chart-wrap">
                        <ResponsiveContainer width="100%" height={220}>
                            <ComposedChart data={chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                <defs>
                                    <linearGradient id="tempGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stopColor="#FF6B35" stopOpacity={0.4} />
                                        <stop offset="100%" stopColor="#FF6B35" stopOpacity={0.05} />
                                    </linearGradient>
                                    <linearGradient id="rainGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stopColor="#2196F3" stopOpacity={0.6} />
                                        <stop offset="100%" stopColor="#2196F3" stopOpacity={0.1} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid strokeDasharray="3 3" stroke="var(--chart-grid, #e0e0e0)" />
                                <XAxis
                                    dataKey="time"
                                    tick={{ fontSize: 11, fill: 'var(--text-secondary)' }}
                                    axisLine={false}
                                    tickLine={false}
                                />
                                <YAxis
                                    yAxisId="temp"
                                    tick={{ fontSize: 11, fill: 'var(--text-secondary)' }}
                                    axisLine={false}
                                    tickLine={false}
                                    unit="°"
                                />
                                <YAxis
                                    yAxisId="rain"
                                    orientation="right"
                                    tick={{ fontSize: 11, fill: '#2196F3' }}
                                    axisLine={false}
                                    tickLine={false}
                                    unit="%"
                                    domain={[0, 100]}
                                    hide
                                />
                                <Tooltip content={<ChartTooltip t={t} />} />
                                <Area
                                    yAxisId="temp"
                                    type="monotone"
                                    dataKey="temp"
                                    name="Temp"
                                    stroke="#FF6B35"
                                    strokeWidth={2.5}
                                    fill="url(#tempGrad)"
                                    dot={{ r: 4, fill: '#FF6B35', strokeWidth: 2, stroke: '#fff' }}
                                    activeDot={{ r: 6 }}
                                />
                                <Bar
                                    yAxisId="rain"
                                    dataKey="rain"
                                    name="Rain %"
                                    fill="url(#rainGrad)"
                                    barSize={20}
                                    radius={[4, 4, 0, 0]}
                                />
                            </ComposedChart>
                        </ResponsiveContainer>
                    </div>
                    <div className="chart-legend">
                        <span className="legend-item">
                            <span className="legend-dot" style={{ background: '#FF6B35' }}></span>
                            {t('temperature')}
                        </span>
                        <span className="legend-item">
                            <span className="legend-dot" style={{ background: '#2196F3' }}></span>
                            {t('chance_of_rain')}
                        </span>
                    </div>
                </div>
            )}

            {/* ── Forecast Cards ─────────────────────── */}
            {hasForecast && (
                <div className="card-container forecast-cards-section">
                    <h3 className="card-title">
                        <Calendar size={18} className="icon-blue" />
                        {t('forecast_3h') || 'Upcoming Forecast'}
                    </h3>
                    <div className="forecast-scroll">
                        {weather.forecast.map((item, index) => (
                            <div key={index} className="forecast-card">
                                <span className="fc-time">{t.n(item.time)}</span>
                                <span className="fc-icon">{item.icon}</span>
                                <span className="fc-temp">{t.n(item.temp)}{t('unit_temp')}</span>
                                <div className="fc-rain-bar">
                                    <div
                                        className="fc-rain-fill"
                                        style={{ height: `${Math.min(item.chanceOfRain, 100)}%` }}
                                    ></div>
                                </div>
                                <span className="fc-rain-label">
                                    <Droplet size={10} /> {t.n(item.chanceOfRain)}%
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default WeatherDetailView;
