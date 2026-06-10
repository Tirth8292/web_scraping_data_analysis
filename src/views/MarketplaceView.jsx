import React from 'react';
import MarketApp from '../market/MarketApp';

// MarketplaceView delegates to MarketApp (React + Firebase)
// Passes t() for multilingual support
const MarketplaceView = ({ t }) => {
    return <MarketApp t={t} />;
};

export default MarketplaceView;
