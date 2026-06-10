import React from 'react';
import './LoadingScreen.css';

const LoadingScreen = ({ t }) => {
    return (
        <div className="loading-screen">
            <div className="animation-container">
                <div className="watering-can-wrapper">
                    <svg className="watering-can" viewBox="0 0 100 80" xmlns="http://www.w3.org/2000/svg">
                        {/* Can Body */}
                        <path d="M30 40 L60 40 L60 70 L30 70 Z" fill="#4CAF50" />
                        {/* Spout on the Right - Angled slightly down for pouring */}
                        <path d="M60 45 L90 55 L85 65 L55 55 Z" fill="#388E3C" />
                        {/* Handle on the Left */}
                        <path d="M30 45 Q15 45 15 55 Q15 65 30 65" fill="none" stroke="#388E3C" strokeWidth="4" />
                        {/* Top opening */}
                        <ellipse cx="45" cy="40" rx="15" ry="5" fill="#2E7D32" />
                    </svg>
                    <div className="water-droplets">
                        <div className="drop d1"></div>
                        <div className="drop d2"></div>
                        <div className="drop d3"></div>
                    </div>
                </div>



                <div className="plant-wrapper">
                    <svg viewBox="0 0 100 100" className="plant-logo">
                        {/* Elegant plant SVG for a clean loading feel */}
                        <path d="M50 95 V60" stroke="#8D6E63" strokeWidth="4" strokeLinecap="round" />
                        <path d="M50 60 C70 45 85 55 50 20 C15 55 30 45 50 60" fill="#4CAF50" />
                        <path d="M50 80 C30 70 15 80 40 55" fill="#81C784" />
                        <path d="M50 80 C70 70 85 80 60 55" fill="#81C784" />
                    </svg>
                </div>


            </div>
            <h2 className="loading-text">{t ? t('app_name') : 'Smart Farmer'}</h2>
            <p className="loading-subtext">{t ? t('loading') : 'Preparing your data...'}</p>
        </div>
    );
};

export default LoadingScreen;
