import React, { useState, useEffect } from 'react';
import { onAuthStateChanged, signOut } from 'firebase/auth';
import { auth } from '../services/firebase';
import { getCart, seedMarketData, getShopByOwner } from './marketService';
import MarketAuth from './MarketAuth';
import MarketHome from './MarketHome';
import MarketProductDetail from './MarketProductDetail';
import MarketCart from './MarketCart';
import MarketOrders from './MarketOrders';
import ShopRegister from './ShopRegister';
import ShopDashboard from './ShopDashboard';
import { ShoppingCart, ClipboardList, Store, LogOut, LogIn, Sprout, LayoutDashboard } from 'lucide-react';
import './market.css';

const PAGES = { HOME: 'home', PRODUCT: 'product', CART: 'cart', ORDERS: 'orders', SHOP_REG: 'shop_reg', SHOP_DASHBOARD: 'shop_dashboard' };

// Demo auth helpers
const DEMO_CURRENT_USER_KEY = 'demo_current_user';
const clearDemoCurrentUser = () => localStorage.removeItem(DEMO_CURRENT_USER_KEY);
const getDemoCurrentUser = () => {
    try {
        return JSON.parse(localStorage.getItem(DEMO_CURRENT_USER_KEY) || 'null');
    } catch {
        return null;
    }
};

const MarketApp = ({ t }) => {
    const [user, setUser] = useState(undefined);
    const [page, setPage] = useState(PAGES.HOME);
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [showAuth, setShowAuth] = useState(false);
    const [cartCount, setCartCount] = useState(0);
    const [seeding, setSeeding] = useState(false);
    const [seedDone, setSeedDone] = useState(false);
    const [userShop, setUserShop] = useState(null);

    // Read GPS from localStorage (set by App.jsx when weather loads)
    const savedCoords = localStorage.getItem('lastCoords');
    const rawCoords = savedCoords ? JSON.parse(savedCoords) : null;
    // Normalize: App.jsx saves as {lat, lon}, marketService expects {lat, lng}
    const locationForMarket = rawCoords ? { lat: rawCoords.lat, lng: rawCoords.lon } : null;

    const handleSignOut = async () => {
        if (auth) {
            await signOut(auth);
        } else {
            clearDemoCurrentUser();
            setUser(null);
            setUserShop(null);
            window.dispatchEvent(new CustomEvent('demo-auth-changed', { detail: null }));
        }
    };

    const loadUserShop = async (userId) => {
        if (userId) {
            const shop = await getShopByOwner(userId);
            setUserShop(shop);
        } else {
            setUserShop(null);
        }
    };

    useEffect(() => {
        if (auth) {
            // Firebase mode
            const unsub = onAuthStateChanged(auth, async (u) => {
                setUser(u);
                if (u) {
                    loadUserShop(u.uid);
                } else {
                    setUserShop(null);
                }
            });
            return unsub;
        } else {
            // Demo mode
            const demoUser = getDemoCurrentUser();
            setUser(demoUser);
            if (demoUser) {
                loadUserShop(demoUser.uid);
            }

            const handleDemoAuthChange = (e) => {
                const newUser = e.detail;
                setUser(newUser);
                if (newUser) {
                    loadUserShop(newUser.uid);
                } else {
                    setUserShop(null);
                }
            };
            window.addEventListener('demo-auth-changed', handleDemoAuthChange);
            return () => window.removeEventListener('demo-auth-changed', handleDemoAuthChange);
        }
    }, []);

    useEffect(() => {
        if (user) refreshCartCount();
        else setCartCount(0);
    }, [user, page]);

    const refreshCartCount = async () => {
        try {
            const items = await getCart(user.uid);
            setCartCount(items.reduce((s, i) => s + i.qty, 0));
        } catch { setCartCount(0); }
    };

    const handleProductClick = (product) => {
        setSelectedProduct(product);
        setPage(PAGES.PRODUCT);
    };

    const handleSeed = async () => {
        setSeeding(true);
        try {
            const msg = await seedMarketData();
            setSeedDone(true);
            alert(msg);
        } catch (e) {
            alert('Seed failed: ' + e.message);
        }
        setSeeding(false);
    };

    if (user === undefined) {
        return (
            <div className="market-app">
                <div className="market-loading">
                    <div className="market-spinner" />
                    <span>{t('market_loading_app')}</span>
                </div>
            </div>
        );
    }

    return (
        <div className="market-app">
            {/* Top Bar */}
            <div className="market-topbar">
                {page !== PAGES.HOME && page !== PAGES.SHOP_DASHBOARD ? (
                    <button className="btn-back" onClick={() => setPage(PAGES.HOME)}>←</button>
                ) : page === PAGES.SHOP_DASHBOARD ? (
                    <div />
                ) : (
                    <Sprout size={20} />
                )}
                <h1>🌾 {t('market_title')}</h1>
                <div className="market-topbar-actions">
                    {!seedDone && (
                        <button className="market-icon-btn" onClick={handleSeed} disabled={seeding} title={t('market_seed_btn_title')}>
                            {seeding ? '⏳' : '🌱'}
                        </button>
                    )}
                    {page !== PAGES.SHOP_DASHBOARD && (
                        <button className="market-icon-btn" onClick={() => setPage(PAGES.CART)}>
                            <ShoppingCart size={16} />
                            {cartCount > 0 && <span className="cart-badge">{cartCount}</span>}
                        </button>
                    )}
                    {user && page !== PAGES.SHOP_DASHBOARD && (
                        <button className="market-icon-btn" onClick={() => setPage(PAGES.ORDERS)} title={t('market_orders')}>
                            <ClipboardList size={16} />
                        </button>
                    )}
                    {user && userShop && page !== PAGES.SHOP_DASHBOARD && (
                        <button className="market-icon-btn" onClick={() => setPage(PAGES.SHOP_DASHBOARD)} title="Shop Dashboard">
                            <LayoutDashboard size={16} />
                        </button>
                    )}
                    {user && !userShop && page !== PAGES.SHOP_DASHBOARD && (
                        <button className="market-icon-btn" onClick={() => setPage(PAGES.SHOP_REG)} title={t('market_register_shop')}>
                            <Store size={16} />
                        </button>
                    )}
                    {user ? (
                        <button className="market-icon-btn" onClick={handleSignOut} title={t('market_logout')}>
                            <LogOut size={16} />
                        </button>
                    ) : (
                        <button className="market-icon-btn" onClick={() => setShowAuth(true)}>
                            <LogIn size={16} /> {t('market_login')}
                        </button>
                    )}
                </div>
            </div>

            {/* User greeting */}
            {user && (
                <div className="user-greeting-strip">
                    👋 {t('market_logged_in_as')} {user.email}
                </div>
            )}

            {/* Main Content */}
            <div className="market-content">
                {page === PAGES.HOME && (
                    <MarketHome
                        userLocation={locationForMarket}
                        onProductClick={handleProductClick}
                        t={t}
                    />
                )}
                {page === PAGES.PRODUCT && selectedProduct && (
                    <MarketProductDetail
                        product={selectedProduct}
                        user={user}
                        onBack={() => setPage(PAGES.HOME)}
                        onGoToCart={() => setPage(PAGES.CART)}
                        onLoginRequired={() => setShowAuth(true)}
                        t={t}
                    />
                )}
                {page === PAGES.CART && (
                    <MarketCart
                        user={user}
                        onBack={() => setPage(PAGES.HOME)}
                        onOrderPlaced={() => { refreshCartCount(); setPage(PAGES.ORDERS); }}
                        onLoginRequired={() => setShowAuth(true)}
                        t={t}
                    />
                )}
                {page === PAGES.ORDERS && (
                    <MarketOrders
                        user={user}
                        onBack={() => setPage(PAGES.HOME)}
                        onLoginRequired={() => setShowAuth(true)}
                        t={t}
                    />
                )}
                {page === PAGES.SHOP_REG && (
                    <ShopRegister
                        user={user}
                        onBack={() => setPage(PAGES.HOME)}
                        t={t}
                        onShopRegistered={(shop) => { setUserShop(shop); setPage(PAGES.SHOP_DASHBOARD); }}
                    />
                )}
                {page === PAGES.SHOP_DASHBOARD && (
                    <ShopDashboard
                        user={user}
                        shop={userShop}
                        onBack={() => setPage(PAGES.HOME)}
                        t={t}
                    />
                )}
            </div>

            {showAuth && <MarketAuth onClose={() => setShowAuth(false)} t={t} />}
        </div>
    );
};

export default MarketApp;
