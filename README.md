# Smart Farmer Dashboard

A Vite + React weather dashboard for farmers, designed to look like the Smart Farmer app.

## Features
- **Real-time Weather**: Displays Temperature, Condition, Humidity, Rainfall, Wind.
- **Soil Health**: Monitoring Moisture and pH.
- **Crop Health**: Tracking Growth Stage and Risk.
- **Alerts**: Weather warnings and simplified status.
- **Action Items**: Daily tasks for the farmer.

## Setup

1. Install dependencies:
   ```bash
   npm install
   ```

2. Run the development server:
   ```bash
   npm run dev
   ```

## APIs
The app is set up to use the following APIs. providing a unified interface:
- **OpenWeatherMap**
- **WeatherAPI**
- **StormGlass**

To enable real data, edit `src/services/weatherService.js` and add your API keys:

```javascript
const API_KEYS = {
  OPEN_WEATHER: 'YOUR_KEY_HERE',
  WEATHER_API: 'YOUR_KEY_HERE',
  STORM_GLASS: 'YOUR_KEY_HERE'
};
```

Currently, it returns **Mock Data** matching the requested design if keys are missing.
