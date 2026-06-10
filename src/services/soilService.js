import axios from 'axios';

// SoilGrids API base URL
const BASE_URL = 'https://rest.isric.org/soilgrids/v2.0/classification/query';

export const fetchSoilData = async (lat, lon) => {
    try {
        console.log(`Fetching soil data for ${lat}, ${lon}...`);

        // Fetch Classification (WRB)
        const res = await axios.get(`${BASE_URL}?lat=${lat}&lon=${lon}`);
        // Note: SoilGrids API structure is complex. We often get probabilities of soil classes.
        // For simple properties like pH and texture, we use the properties endpoint.

        // Properties endpoint
        // Added sand, silt, clay for texture analysis
        const propsUrl = `https://rest.isric.org/soilgrids/v2.0/properties/query?lat=${lat}&lon=${lon}&property=phh2o&property=nitrogen&property=soc&property=sand&property=silt&property=clay&depth=0-5cm&depth=0-30cm&value=mean`;

        const propsRes = await axios.get(propsUrl);
        const data = propsRes.data;

        // Helper to safely get value from layer
        const getValue = (name, factor = 1) => {
            const layer = data.properties.layers.find(l => l.name === name);
            // Default to 0 if not found
            return layer ? Math.round(layer.depths[0].values.mean / factor) : 0;
        };

        // Process pH (phh2o) - stored as pH * 10
        const phValue = getValue('phh2o', 10) || 7.0;

        // Process Nitrogen (nitrogen) - stored as cg/kg
        const nValue = getValue('nitrogen', 100) || 0.1;

        // Process Organic Carbon (soc) - g/kg -> % (approx / 10)
        const socValue = getValue('soc', 10) || 1.2;

        // Process Texture (sand, silt, clay) - stored as g/kg -> % (/10)
        const sand = getValue('sand', 10);
        const silt = getValue('silt', 10);
        const clay = getValue('clay', 10);

        // Determine Soil Texture Name (Simplified USDA Logic)
        let textureName = 'Loam';
        if (clay >= 40) textureName = 'Clay';
        else if (sand >= 50) {
            textureName = (clay >= 20) ? 'Sandy Clay' : 'Sandy Loam';
            if (sand >= 70 && clay < 15) textureName = 'Sandy';
        } else if (silt >= 50) {
            textureName = (clay >= 27) ? 'Silty Clay' : 'Silty Loam';
        }

        // --- SMART REGIONAL NAMING (Heuristic for Indian Context) ---
        // Combine Location + Texture to guess local common name
        const getRegionalSoilName = (lat, lon, texture) => {
            // Deccan Plateau (Maharashtra/Karnataka/MP) -> High Clay = Black Cotton Soil
            if (lat > 15 && lat < 25 && lon > 72 && lon < 82) {
                if (texture.includes('Clay')) return 'Black Cotton Soil (Regur)';
                if (texture.includes('Loam')) return 'Red Soil (Latosol)';
            }
            // Indo-Gangetic Plains (UP/Punjab/Bihar) -> Loam = Alluvial
            if (lat > 25 && lat < 32 && lon > 74 && lon < 88) {
                if (texture.includes('Loam') || texture.includes('Silt')) return 'Alluvial Soil';
            }
            // Desert Region (Rajasthan)
            if (lat > 24 && lat < 30 && lon > 69 && lon < 76) {
                if (texture.includes('Sand')) return 'Desert Soil';
            }
            return texture; // Fallback to scientific name
        };

        const textureNameFinal = getRegionalSoilName(lat, lon, textureName);

        // Fetch Real-Time Moisture from Open-Meteo
        let moistureDisplay = 'Unknown';
        try {
            const moistureUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=soil_moisture_0_to_1cm,soil_moisture_1_to_3cm`;
            const moistureRes = await axios.get(moistureUrl);
            const m1 = moistureRes.data.current.soil_moisture_0_to_1cm;
            const m2 = moistureRes.data.current.soil_moisture_1_to_3cm;

            // Average the top two layers
            const avgMoisture = (m1 + m2) / 2;

            // Convert m³/m³ to percentage (0.35 -> 35%)
            moistureDisplay = (avgMoisture * 100).toFixed(1) + '%';
        } catch (mErr) {
            console.warn("Failed to fetch moisture:", mErr);
            moistureDisplay = '45% (Est)'; // Fallback
        }

        return {
            ph: phValue.toFixed(1),
            nitrogen: nValue.toFixed(2) + '%',
            organicCarbon: socValue.toFixed(1) + '%',
            moisture: moistureDisplay,
            scientificType: res.data.wrb_class_name || 'Unknown',
            type: textureNameFinal, // Regional name (e.g. Black Cotton Soil)
            composition: { sand, silt, clay }
        };

    } catch (error) {
        console.error("SoilGrids API Error:", error);
        return {
            ph: '6.5',
            nitrogen: '0.15%',
            organicCarbon: '1.2%',
            moisture: '60%',
            type: 'Unknown'
        };
    }
};
