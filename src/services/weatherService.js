import axios from 'axios';

// Access keys from .env file (Vite prefix is required)
const KEYS = {
    OPEN_WEATHER: import.meta.env.VITE_OPENWEATHER_API_KEY,
    OPEN_WEATHER_2: import.meta.env.VITE_OPENWEATHER_API_KEY_2,  // Backup key
    WEATHER_API: import.meta.env.VITE_WEATHERAPI_KEY,
    STORM_GLASS: import.meta.env.VITE_STORMGLASS_API_KEY
};

// Fallback logic to get distinct icon names that map to our UI (optional)
const getIcon = (condition) => {
    const c = condition.toLowerCase();
    if (c.includes('rain')) return 'cloud-rain';
    if (c.includes('cloud')) return 'cloud';
    if (c.includes('sun') || c.includes('clear')) return 'sun';
    return 'sun-cloud'; // default
};

// 1. OpenWeatherMap Implementation
const fetchOpenWeather = async (lat, lon) => {
    if (!KEYS.OPEN_WEATHER || KEYS.OPEN_WEATHER.includes('your_')) throw new Error('No OpenWeather Key');

    // Parallel calls for current weather and forecast
    const [weatherRes, forecastRes] = await Promise.all([
        axios.get(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&units=metric&appid=${KEYS.OPEN_WEATHER}`),
        axios.get(`https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&units=metric&appid=${KEYS.OPEN_WEATHER}`)
    ]);

    const data = weatherRes.data;
    const forecastData = forecastRes.data;

    // Process forecast: Take next 5 items (next 15 hours approx)
    const forecast = forecastData.list.slice(0, 5).map(item => ({
        time: new Date(item.dt * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        temp: Math.round(item.main.temp),
        icon: item.weather[0].main,
        chanceOfRain: Math.round(item.pop * 100) // Probability of precipitation
    }));

    return {
        temp: Math.round(data.main.temp),
        feelsLike: Math.round(data.main.feels_like), // Real Feel
        condition: data.weather[0].main,
        conditionLocal: data.weather[0].description,
        humidity: data.main.humidity,
        rainfall: data.rain ? data.rain['1h'] || 0 : 0,
        windSpeed: Math.round(data.wind.speed * 3.6),
        pressure: data.main.pressure,
        visibility: data.visibility / 1000, // km
        locationName: data.name,
        source: 'OpenWeather',
        forecast: forecast
    };
};

// 2. WeatherAPI Implementation
const fetchWeatherAPI = async (lat, lon) => {
    if (!KEYS.WEATHER_API || KEYS.WEATHER_API.includes('your_')) throw new Error('No WeatherAPI Key');

    const url = `https://api.weatherapi.com/v1/current.json?key=${KEYS.WEATHER_API}&q=${lat},${lon}`;
    const res = await axios.get(url);
    const data = res.data;

    return {
        temp: Math.round(data.current.temp_c),
        condition: data.current.condition.text,
        conditionLocal: data.current.condition.text,
        humidity: data.current.humidity,
        rainfall: data.current.precip_mm,
        windSpeed: Math.round(data.current.wind_kph),
        locationName: data.location.name,
        source: 'WeatherAPI'
    };
};

// 3. StormGlass Implementation (More complex, requires header)
const fetchStormGlass = async (lat, lon) => {
    if (!KEYS.STORM_GLASS || KEYS.STORM_GLASS.includes('your_')) throw new Error('No StormGlass Key');

    const params = 'airTemperature,humidity,precipitation,windSpeed';
    const url = `https://api.stormglass.io/v2/weather/point?lat=${lat}&lng=${lon}&params=${params}`;

    const res = await axios.get(url, {
        headers: { 'Authorization': KEYS.STORM_GLASS }
    });

    // StormGlass returns hours list. Get current hour.
    const current = res.data.hours[0];

    return {
        temp: Math.round(current.airTemperature.sg),
        condition: 'StormGlass Data', // SG doesn't give simple text condition easily
        conditionLocal: 'Detailed Metrics',
        humidity: Math.round(current.humidity.sg),
        rainfall: current.precipitation.sg,
        windSpeed: Math.round(current.windSpeed.sg * 3.6),
        locationName: 'StormGlass Location',
        source: 'StormGlass'
    };
};

// 4. Open-Meteo Implementation (Free, No Key)
const fetchOpenMeteo = async (lat, lon) => {
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,precipitation,weather_code,surface_pressure,wind_speed_10m,apparent_temperature,visibility&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=auto`;
    const res = await axios.get(url);
    const data = res.data;
    const current = data.current;

    // Open-Meteo WMO Weather interpretation
    const getCondition = (code) => {
        if (code === 0) return 'Clear';
        if (code >= 1 && code <= 3) return 'Partly Cloudy';
        if (code >= 45 && code <= 48) return 'Foggy';
        if (code >= 51 && code <= 67) return 'Rain';
        if (code >= 71 && code <= 77) return 'Snow';
        if (code >= 80 && code <= 82) return 'Showers';
        if (code >= 95 && code <= 99) return 'Thunderstorm';
        return 'Unknown';
    };

    // Process forecast
    const forecast = data.daily.time.slice(0, 5).map((time, index) => ({
        time: new Date(time).toLocaleDateString([], { weekday: 'short' }),
        temp: Math.round(data.daily.temperature_2m_max[index]),
        icon: getCondition(data.daily.weather_code[index]),
        chanceOfRain: data.daily.precipitation_sum[index] > 0 ? 50 : 0 // Rough estimate
    }));

    return {
        temp: Math.round(current.temperature_2m),
        condition: getCondition(current.weather_code),
        conditionLocal: getCondition(current.weather_code),
        humidity: current.relative_humidity_2m,
        rainfall: current.precipitation,
        windSpeed: Math.round(current.wind_speed_10m),
        pressure: current.surface_pressure,
        locationName: 'Unknown Location (Open-Meteo)',
        source: 'Open-Meteo (Free)',
        feelsLike: Math.round(current.apparent_temperature),
        visibility: (current.visibility / 1000).toFixed(1), // km
        forecast: forecast
    };
};

export const fetchCoordinates = async (city) => {
    try {
        // Use Open-Meteo Geocoding (Free)
        const url = `https://geocoding-api.open-meteo.com/v1/search?name=${city}&count=1&language=en&format=json`;
        const res = await axios.get(url);

        if (!res.data.results || res.data.results.length === 0) {
            throw new Error('City not found');
        }

        const location = res.data.results[0];
        return {
            lat: location.latitude,
            lon: location.longitude,
            name: location.name,
            country: location.country
        };
    } catch (error) {
        console.error("Geocoding failed:", error);
        throw error;
    }
};

// Main Fetch Function with Fallback Strategy
export const fetchWeatherData = async (lat, lon) => {
    console.log(`Fetching weather for ${lat}, ${lon}...`);

    try {
        // Priority 1: OpenWeatherMap (primary key)
        return await fetchOpenWeather(lat, lon);
    } catch (err1) {
        console.warn('OpenWeather (primary) failed, trying backup key...', err1.message);
        try {
            // Priority 2: OpenWeatherMap (backup key)
            return await fetchOpenWeather2(lat, lon);
        } catch (err2) {
            console.warn('OpenWeather (backup) failed, trying WeatherAPI...', err2.message);
            try {
                // Priority 3: WeatherAPI
                return await fetchWeatherAPI(lat, lon);
            } catch (err3) {
                console.warn('WeatherAPI failed, trying StormGlass...', err3.message);
                try {
                    // Priority 4: StormGlass
                    return await fetchStormGlass(lat, lon);
                } catch (err4) {
                    console.warn('StormGlass failed, trying Open-Meteo...', err4.message);
                    try {
                        // Priority 5: Open-Meteo (always free, no key)
                        return await fetchOpenMeteo(lat, lon);
                    } catch (err5) {
                        console.error('All APIs failed.', err5.message);
                        const isQuotaError = [err1, err2, err3, err4].some(e => e?.response?.status === 429 || e?.response?.status === 401);
                        return {
                            temp: 32,
                            condition: 'Partly Cloudy',
                            conditionLocal: 'API Unavailable',
                            humidity: 65,
                            rainfall: 2,
                            windSpeed: 12,
                            locationName: isQuotaError ? 'Demo Mode (API Limit)' : 'Demo Mode (Network/API Error)',
                            source: 'Mock Data',
                            feelsLike: 34,
                            visibility: 10,
                            forecast: []
                        };
                    }
                }
            }
        }
    }
};
