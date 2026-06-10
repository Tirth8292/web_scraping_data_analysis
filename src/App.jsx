import React, { useEffect, useState } from 'react';
import WeatherDetailView from './views/WeatherDetailView';
import SoilDetailView from './views/SoilDetailView';
import AlertsView from './views/AlertsView';
import MarketplaceView from './views/MarketplaceView';
import Header from './components/Header';
import WeatherCard from './components/WeatherCard';
import StatusRow from './components/StatusRow';
import SoilHealth from './components/SoilHealth';
import CropHealth from './components/CropHealth';
import ActionList from './components/ActionList';
import CropCalendar from './components/CropCalendar';
import BottomNav from './components/BottomNav';
import { fetchWeatherData, fetchCoordinates } from './services/weatherService';
import { translations } from './translations';
import './App.css';
import { fetchSoilData } from './services/soilService';
import { AlertCircle } from 'lucide-react';
import InstallPrompt from './components/InstallPrompt';
import LoadingScreen from './components/LoadingScreen';


function App() {
  const [weather, setWeather] = useState(() => {
    const saved = localStorage.getItem('lastWeather');
    return saved ? JSON.parse(saved) : null;
  });
  const [soil, setSoil] = useState(() => {
    const saved = localStorage.getItem('lastSoil');
    return saved ? JSON.parse(saved) : null;
  });
  const [lang, setLang] = useState(() => localStorage.getItem('appLang') || 'en');
  const [darkMode, setDarkMode] = useState(() => localStorage.getItem('darkMode') === 'true');
  const [activeTab, setActiveTab] = useState('Home');
  const [isLoading, setIsLoading] = useState(false);
  const [isAppLoading, setIsAppLoading] = useState(true);

  // Digit mappings for localization
  const GU_DIGITS = ['૦', '૧', '૨', '૩', '૪', '૫', '૬', '૭', '૮', '૯'];
  const HI_DIGITS = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

  const t = (key) => translations[lang][key] || key;
  t.n = (val) => {
    if (lang === 'gu') return String(val).replace(/[0-9]/g, d => GU_DIGITS[d]);
    if (lang === 'hi') return String(val).replace(/[0-9]/g, d => HI_DIGITS[d]);
    return String(val);
  };


  const onLangChange = (newLang) => {
    setLang(newLang);
    localStorage.setItem('appLang', newLang);
  };

  const toggleDark = () => {
    setDarkMode(prev => {
      const next = !prev;
      localStorage.setItem('darkMode', String(next));
      return next;
    });
  };

  // Apply data-theme to <html> so CSS variables cascade everywhere
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
  }, [darkMode]);

  // Set initial theme immediately on mount
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
  }, []);

  const handleSearch = async (city) => {
    try {
      setIsLoading(true);
      console.log("Searching for:", city);
      const coords = await fetchCoordinates(city);

      const weatherData = await fetchWeatherData(coords.lat, coords.lon);
      weatherData.locationName = `${coords.name}, ${coords.country}`;
      setWeather(weatherData);
      localStorage.setItem('lastWeather', JSON.stringify(weatherData));

      const soilData = await fetchSoilData(coords.lat, coords.lon);
      setSoil(soilData);
      localStorage.setItem('lastSoil', JSON.stringify(soilData));

      // Save coordinates for auto-refresh
      localStorage.setItem('lastCoords', JSON.stringify({ lat: coords.lat, lon: coords.lon }));
    } catch (error) {
      console.error("Search failed:", error);
      alert("Location not found or API error");
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    const loadData = async () => {
      // If we have saved coordinates, refresh them
      const savedCoords = localStorage.getItem('lastCoords');
      const defaultLat = 19.9975;
      const defaultLon = 73.7898;

      try {
        if (savedCoords) {
          const { lat, lon } = JSON.parse(savedCoords);
          await Promise.all([
            fetchWeatherData(lat, lon).then(setWeather),
            fetchSoilData(lat, lon).then(setSoil)
          ]);
        } else if (navigator.geolocation) {
          // Wrap geolocation in a promise to handle it within async flow
          const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 10000 });
          }).catch(err => {
            console.warn("Geolocation failed or timed out", err);
            return null;
          });

          if (position) {
            const { latitude, longitude } = position.coords;
            const [wData, sData] = await Promise.all([
              fetchWeatherData(latitude, longitude),
              fetchSoilData(latitude, longitude)
            ]);
            setWeather(wData);
            setSoil(sData);
            localStorage.setItem('lastWeather', JSON.stringify(wData));
            localStorage.setItem('lastSoil', JSON.stringify(sData));
            localStorage.setItem('lastCoords', JSON.stringify({ lat: latitude, lon: longitude }));
          } else {
            // Fallback inside geolocation path
            const [wData, sData] = await Promise.all([
              fetchWeatherData(defaultLat, defaultLon),
              fetchSoilData(defaultLat, defaultLon)
            ]);
            setWeather(wData);
            setSoil(sData);
          }
        } else {
          // No geolocation support
          const [wData, sData] = await Promise.all([
            fetchWeatherData(defaultLat, defaultLon),
            fetchSoilData(defaultLat, defaultLon)
          ]);
          setWeather(wData);
          setSoil(sData);
        }
      } catch (error) {
        console.error("Initialization error:", error);
      } finally {
        // Minimum loading time for animation to feel good
        setTimeout(() => {
          setIsAppLoading(false);
        }, 800);
      }
    };

    loadData();
  }, []);

  if (isAppLoading) {
    return <LoadingScreen t={t} />;
  }

  return (
    <div className="container" style={{ opacity: isLoading ? 0.7 : 1, transition: 'opacity 0.3s' }}>
      <Header
        location={isLoading ? "Updating..." : weather?.locationName}
        t={t}
        lang={lang}
        onLangChange={onLangChange}
        onSearch={handleSearch}
        darkMode={darkMode}
        toggleDark={toggleDark}
      />
      <div className="content">
        {weather?.source === 'Mock Data' && (
          <div className="demo-banner">
            <AlertCircle size={16} />
            <span>{t('api_limit_warning')}</span>
          </div>
        )}
        {isLoading && <div className="loading-spinner"></div>}
        {activeTab === 'Weather' ? (
          <WeatherDetailView weather={weather} t={t} />
        ) : activeTab === 'Soil' ? (
          <SoilDetailView soilData={soil} weather={weather} t={t} />
        ) : activeTab === 'Alerts' ? (
          <AlertsView weather={weather} soil={soil} t={t} />
        ) : activeTab === 'Market' ? (
          <MarketplaceView t={t} />
        ) : (
          <>
            <WeatherCard data={weather} t={t} />
            <StatusRow t={t} weather={weather} soil={soil} />
            <SoilHealth t={t} soilData={soil} />
            <CropHealth t={t} weather={weather} soil={soil} />
            <CropCalendar t={t} />
            <ActionList t={t} />
          </>
        )}
      </div>
      <BottomNav t={t} activeTab={activeTab} setActiveTab={setActiveTab} />
      <InstallPrompt t={t} />
    </div>
  );
}

export default App;
