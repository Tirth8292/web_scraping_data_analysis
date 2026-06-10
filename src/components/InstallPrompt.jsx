import React, { useState, useEffect } from 'react';
import { Download, X, Share } from 'lucide-react';
import './InstallPrompt.css';

const InstallPrompt = ({ t }) => {
    const [deferredPrompt, setDeferredPrompt] = useState(null);
    const [isVisible, setIsVisible] = useState(false);
    const [isIos, setIsIos] = useState(false);

    useEffect(() => {
        // Advanced iOS detection (includes modern iPads)
        const isIosDevice = [
            'iPad Simulator',
            'iPhone Simulator',
            'iPod Simulator',
            'iPad',
            'iPhone',
            'iPod'
        ].includes(navigator.platform) || (navigator.userAgent.includes("Mac") && "ontouchend" in document);

        const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

        if (isIosDevice && !isStandalone) {
            setIsIos(true);
            // Show iOS prompt after a short delay
            const timer = setTimeout(() => setIsVisible(true), 2000);
            return () => clearTimeout(timer);
        }


        const handleBeforeInstallPrompt = (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            setDeferredPrompt(e);
            // Update UI notify the user they can install the PWA
            setIsVisible(true);
        };

        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

        return () => {
            window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        };
    }, []);

    const handleInstallClick = async () => {
        if (!deferredPrompt) return;

        // Show the install prompt
        deferredPrompt.prompt();

        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`User response to the install prompt: ${outcome}`);

        // We've used the prompt, and can't use it again, throw it away
        setDeferredPrompt(null);
        setIsVisible(false);
    };

    const handleClose = () => {
        setIsVisible(false);
        // Optional: save to session storage so it doesn't pop up again in this session
        sessionStorage.setItem('installPromptDismissed', 'true');
    };

    if (!isVisible || sessionStorage.getItem('installPromptDismissed')) return null;

    return (
        <div className="install-prompt animate-slide-up">
            <div className="install-content">
                <div className="install-icon">
                    <img src="/logo.png" alt="App Logo" className="logo-img-small" />
                </div>
                <div className="install-text">
                    <h3>{t('install_app')}</h3>
                    <p>{t('install_desc')}</p>
                </div>
                <div className="install-actions">
                    {isIos ? (
                        <div className="ios-instructions">
                            <span className="ios-title">{t('ios_install_title')}</span>
                            <div className="ios-steps">
                                <span>{t('ios_install_step1')} <Share size={14} style={{ display: 'inline', verticalAlign: 'middle' }} /></span>
                                <span>{t('ios_install_step2')}</span>
                            </div>
                        </div>
                    ) : (
                        <button className="primary-install-btn" onClick={handleInstallClick}>
                            {t('install_btn')}
                        </button>
                    )}
                    <button className="close-prompt" onClick={handleClose}>
                        <X size={20} />
                    </button>
                </div>
            </div>
        </div>
    );
};

export default InstallPrompt;
