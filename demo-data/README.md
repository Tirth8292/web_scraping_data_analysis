
# Smart Farmer AI - Demo Data Package

Complete demo data package for portfolio, hackathons, and final year projects.

## 📁 Files Included

1. **demoData.js** - JavaScript module with all demo data
2. **completeDemoData.js** - Full complete demo data (50 products, 15 stores, etc.)
3. **supabase-schema.sql** - PostgreSQL database schema for Supabase
4. **README.md** - This file

## 📊 Data Overview

### Products - 50 Items
- Categories: Seeds, Fertilizers, Pesticides, Farm Equipment, Irrigation, Animal Feed, Organic Products, Tools
- Brands: UPL, Bayer, Syngenta, IFFCO, Coromandel, Tata Agrico, Jain Irrigation, Mahindra Agro
- Realistic pricing and discounts
- Product images (AI-generated via Trae's text-to-image API)
- Stock, ratings, reviews, and sales counts

### Stores - 15 Locations
- Locations in and around Vadodara, Gujarat
- Store types: Seed Store, Fertilizer Shop, Agro Chemical Store, Farm Equipment Dealer, Organic Farming Store
- Latitude & Longitude for distance calculations
- Store images

### Farmers - 20 Profiles
- Complete farmer details (name, village, farm size, crops, soil type, income, etc.)
- Profiles from Vadodara and surrounding districts

### Orders - 60 Orders
- Order history with various payment and delivery statuses
- Order dates from May 2026 to June 2026

### Product Reviews - 50 Reviews
- 1-5 star ratings
- Realistic review text from farmers

### Weather Alerts - 15 Alerts
- Alert types: Heavy Rain, Heat Wave, Pest Attack, Drought Warning, High Wind Alert
- Severity levels: Low, Medium, High

### Crop Advisories - 40 Advisories
- Crops: Cotton, Wheat, Rice, Groundnut, Sugarcane, Maize, Tomato, Onion
- Seasonal recommendations
- Actionable advice for farmers

### AI Disease Detection - 20 Scans
- Diseases: Leaf Spot, Rust, Powdery Mildew, Bacterial Blight, Early Blight, Late Blight
- Confidence scores and recommended treatments
- Detection images

### Home Banners - 10 Banners
- Promotional banners for marketplace
- Call-to-action buttons

## 🚀 Usage in App

The data is integrated directly into the app's marketService.js file!

### Features
1. **Offline Fallback**: Works even without Firebase
2. **Seamless Integration**: Falls back to demo data if Firebase fails
3. **Realistic Data**: Perfect for portfolio and hackathons

### Key Files Modified
- `src/market/marketService.js` - Updated to use demo data
- `demo-data/demoData.js` - Demo data module

## 📦 Schema Details (Supabase)

The schema includes all required tables:
1. products
2. categories
3. stores
4. farmers
5. orders
6. order_items
7. product_reviews
8. crop_advisories
9. disease_detection_history
10. weather_alerts
11. banners
12. notifications
13. cart_items
14. wishlist
15. analytics

### Features of Schema
- Primary & Foreign Keys
- Indexes for performance
- Constraints
- Created & Updated timestamps
- RLS (Row Level Security) policies
- Triggers for auto-updating timestamps

## 🌐 App is Running!

Your Smart Farmer AI platform is now live at:
**http://localhost:5173/**

The marketplace features all the demo products, stores, and features!

## 📝 Notes

- The demo data is production-ready for portfolio use
- All images are AI-generated and accessible
- The app works offline without Firebase using the demo data
- Perfect for final year projects, hackathons, and interviews
