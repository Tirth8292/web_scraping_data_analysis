import { db } from '../services/firebase';
import {
  collection, doc, getDocs, getDoc, addDoc, setDoc,
  updateDoc, deleteDoc, query, where, serverTimestamp
} from 'firebase/firestore';
import { demoData } from '../../demo-data/demoData';

function requireDb() {
  if (!db) throw new Error('Firebase is not configured. Please add environment variables.');
  return db;
}

// ─── Haversine Distance (km) ───────────────────────────────────────────────
export function haversineDistance(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLng = ((lng2 - lng1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos((lat1 * Math.PI) / 180) *
        Math.cos((lat2 * Math.PI) / 180) *
        Math.sin(dLng / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// ─── Products ──────────────────────────────────────────────────────────────
export async function getAllProducts(category = null) {
  if (!db) {
    // Fallback to demo data
    let products = [...demoData.products];
    if (category && category !== 'All') {
      products = products.filter(p => p.category === category);
    }
    return products;
  }
  try {
    const ref = collection(db, 'products');
    const q = category ? query(ref, where('category', '==', category)) : ref;
    const snap = await getDocs(q);
    const products = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    if (products.length === 0) return [...demoData.products];
    return products;
  } catch (e) {
    console.log("Using demo data", e);
    let products = [...demoData.products];
    if (category && category !== 'All') {
      products = products.filter(p => p.category === category);
    }
    return products;
  }
}

export async function getProductById(productId) {
  if (!db) {
    return demoData.products.find(p => p.id === parseInt(productId)) || null;
  }
  try {
    const snap = await getDoc(doc(db, 'products', productId));
    if (snap.exists()) {
      return { id: snap.id, ...snap.data() };
    } else {
      return demoData.products.find(p => p.id === parseInt(productId)) || null;
    }
  } catch (e) {
    console.log("Using demo product", e);
    return demoData.products.find(p => p.id === parseInt(productId)) || null;
  }
}

export async function getNearbyProducts(userLat, userLng, category = null, radiusKm = 50) {
    if (!db) {
      // Fallback to demo data
      const nearbyShopIds = [];
      demoData.stores.forEach(s => {
        if (s.status === 'active' && s.lat && s.lng) {
          const dist = haversineDistance(userLat, userLng, s.lat, s.lng);
          if (dist <= radiusKm) nearbyShopIds.push({ id: s.store_id, dist: dist.toFixed(1), name: s.name });
        }
      });
      
      let products = [...demoData.products];
      return products
        .filter(p => {
            const shop = nearbyShopIds.find(s => s.id === p.shopId);
            if (!shop) return false;
            if (category && category !== 'All' && p.category !== category) return false;
            p.shopName = shop.name;
            p.distanceKm = shop.dist;
            return true;
        })
        .sort((a, b) => parseFloat(a.distanceKm) - parseFloat(b.distanceKm));
    }
    try {
      // Fetch all shops first, filter by distance
      const shopsSnap = await getDocs(collection(db, 'shops'));
      const nearbyShopIds = [];
      shopsSnap.docs.forEach(d => {
        const s = d.data();
        if (s.status === 'active' && s.lat && s.lng) {
          const dist = haversineDistance(userLat, userLng, s.lat, s.lng);
          if (dist <= radiusKm) nearbyShopIds.push({ id: d.id, dist: dist.toFixed(1), name: s.name });
        }
      });

      // If no shops found, use demo data
      if (nearbyShopIds.length === 0) {
        return getNearbyProducts(userLat, userLng, category, radiusKm); // Recursive call to demo version
      }

      // Get products from nearby shops
      const productsSnap = await getDocs(collection(db, 'products'));
      let products = productsSnap.docs
        .map(d => ({ id: d.id, ...d.data() }));
      
      if (products.length === 0) {
        // No products, use demo data
        return getNearbyProducts(userLat, userLng, category, radiusKm);
      }

      return products
        .filter(p => {
            const shop = nearbyShopIds.find(s => s.id === p.shopId);
            if (!shop) return false;
            if (category && category !== 'All' && p.category !== category) return false;
            p.shopName = shop.name;
            p.distanceKm = shop.dist;
            return true;
        })
        .sort((a, b) => parseFloat(a.distanceKm) - parseFloat(b.distanceKm));
    } catch (e) {
      console.log("Using demo data for nearby products", e);
      return getNearbyProducts(userLat, userLng, category, radiusKm);
    }
}

// ─── Shops ──────────────────────────────────────────────────────────────────
export async function registerShop(uid, shopData) {
    if (!db) {
        // Demo mode
        const demoShop = {
            id: Date.now(),
            ownerUid: uid,
            ...shopData,
            status: 'pending',
            createdAt: new Date().toISOString(),
            name: shopData.store_name || shopData.name || 'My Shop',
            lat: shopData.latitude || shopData.lat || 22.3072,
            lng: shopData.longitude || shopData.lng || 73.1818
        };
        // Save to localStorage for demo
        let demoShops = JSON.parse(localStorage.getItem('demoShops') || '[]');
        demoShops.push(demoShop);
        localStorage.setItem('demoShops', JSON.stringify(demoShops));
        localStorage.setItem(`shop_${uid}`, JSON.stringify(demoShop));
        return demoShop.id;
    }
    const shopRef = await addDoc(collection(db, 'shops'), {
        ownerUid: uid,
        ...shopData,
        status: 'pending',
        createdAt: serverTimestamp(),
    });
    return shopRef.id;
}

export async function getShopByOwner(uid) {
    if (!db) {
        // Demo mode
        const savedShop = localStorage.getItem(`shop_${uid}`);
        if (savedShop) {
            return JSON.parse(savedShop);
        }
        const demoShops = JSON.parse(localStorage.getItem('demoShops') || '[]');
        return demoShops.find(s => s.ownerUid === uid) || null;
    }
    try {
        const q = query(collection(db, 'shops'), where('ownerUid', '==', uid));
        const snap = await getDocs(q);
        if (snap.empty) return null;
        return { id: snap.docs[0].id, ...snap.docs[0].data() };
    } catch (e) {
        console.log("Get shop error", e);
        return null;
    }
}

// ─── Shop Products ────────────────────────────────────────────────────────────
export async function addProduct(shopId, productData) {
    if (!db) {
        // Demo mode
        const newProduct = {
            id: Date.now(),
            ...productData,
            shopId,
            shopName: 'My Shop',
            seller_name: 'My Shop',
            seller_location: 'Vadodara, Gujarat',
            unit: 'Unit',
            availability: true,
            rating: 5,
            review_count: 0,
            sales_count: 0,
            image_url: productData.image_url || `https://coresg-normal.trae.ai/api/v1/text_to_image?prompt=${encodeURIComponent(productData.name)}&image_size=square`
        };
        let demoProducts = JSON.parse(localStorage.getItem('demoProducts') || JSON.stringify(demoData.products));
        demoProducts.push(newProduct);
        localStorage.setItem('demoProducts', JSON.stringify(demoProducts));
        return newProduct;
    }
    try {
        const productRef = await addDoc(collection(db, 'products'), {
            ...productData,
            shopId,
            createdAt: serverTimestamp(),
        });
        return { id: productRef.id, ...productData };
    } catch (e) {
        console.log("Add product error", e);
        throw e;
    }
}

export async function getShopProducts(shopId) {
    if (!db) {
        // Demo mode
        const demoProducts = JSON.parse(localStorage.getItem('demoProducts') || JSON.stringify(demoData.products));
        return demoProducts.filter(p => p.shopId === shopId || p.shopId === 1); // Fallback to demo shop 1
    }
    try {
        const q = query(collection(db, 'products'), where('shopId', '==', shopId));
        const snap = await getDocs(q);
        return snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch (e) {
        console.log("Get shop products error", e);
        return [];
    }
}

export async function deleteProduct(shopId, productId) {
    if (!db) {
        // Demo mode
        let demoProducts = JSON.parse(localStorage.getItem('demoProducts') || JSON.stringify(demoData.products));
        demoProducts = demoProducts.filter(p => p.id !== productId);
        localStorage.setItem('demoProducts', JSON.stringify(demoProducts));
        return;
    }
    try {
        await deleteDoc(doc(db, 'products', productId));
    } catch (e) {
        console.log("Delete product error", e);
        throw e;
    }
}

// ─── Shop Orders ──────────────────────────────────────────────────────────────
export async function getShopOrders(shopId) {
    if (!db) {
        // Demo orders
        return [
            { order_id: 1, farmer_name: 'Ramesh Patel', product_name: 'UPL NPK 20:20:20 Fertilizer 50kg', quantity: 10, amount: 11000, delivery_status: 'Processing', order_date: '2024-01-15' },
            { order_id: 2, farmer_name: 'Suresh Desai', product_name: 'Bayer Confidor Insecticide 250ml', quantity: 5, amount: 3900, delivery_status: 'Shipped', order_date: '2024-01-14' },
            { order_id: 3, farmer_name: 'Amit Shah', product_name: 'Syngenta Hybrid Cotton Seeds 450g', quantity: 20, amount: 17800, delivery_status: 'Delivered', order_date: '2024-01-12' },
        ];
    }
    try {
        const q = query(collection(db, 'orders'), where('shopId', '==', shopId));
        const snap = await getDocs(q);
        return snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch (e) {
        console.log("Get shop orders error", e);
        return [];
    }
}

// Demo mode helpers
const getDemoCart = (uid) => {
    const key = `demo_cart_${uid}`;
    return JSON.parse(localStorage.getItem(key) || '[]');
};
const saveDemoCart = (uid, items) => {
    localStorage.setItem(`demo_cart_${uid}`, JSON.stringify(items));
};
const getDemoOrders = (uid) => {
    const key = `demo_orders_${uid}`;
    return JSON.parse(localStorage.getItem(key) || '[]');
};
const saveDemoOrders = (uid, orders) => {
    localStorage.setItem(`demo_orders_${uid}`, JSON.stringify(orders));
};

// ─── Cart ────────────────────────────────────────────────────────────────────
export async function getCart(uid) {
    if (!db) {
        return getDemoCart(uid);
    }
    try {
        const snap = await getDocs(collection(db, 'carts', uid, 'items'));
        return snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch (e) {
        return [];
    }
}

export async function addToCart(uid, product, qty = 1) {
    if (!db) {
        const cart = getDemoCart(uid);
        const existingIndex = cart.findIndex(i => i.id === String(product.id));
        if (existingIndex !== -1) {
            cart[existingIndex].qty += qty;
        } else {
            cart.push({
                id: String(product.id),
                productId: product.id,
                name: product.name,
                price: product.discount_price || product.price,
                shopId: product.shopId,
                imageUrl: product.image_url || '',
                qty,
            });
        }
        saveDemoCart(uid, cart);
        return;
    }
    try {
        const itemRef = doc(db, 'carts', uid, 'items', String(product.id));
        const existing = await getDoc(itemRef);
        if (existing.exists()) {
            await updateDoc(itemRef, { qty: existing.data().qty + qty });
        } else {
            await setDoc(itemRef, {
                productId: product.id,
                name: product.name,
                price: product.discount_price || product.price,
                shopId: product.shopId,
                imageUrl: product.image_url || '',
                qty,
            });
        }
    } catch (e) {
        console.log("Cart error", e);
    }
}

export async function updateCartQty(uid, itemId, qty) {
    if (!db) {
        const cart = getDemoCart(uid);
        if (qty <= 0) {
            saveDemoCart(uid, cart.filter(i => i.id !== itemId));
        } else {
            const index = cart.findIndex(i => i.id === itemId);
            if (index !== -1) {
                cart[index].qty = qty;
                saveDemoCart(uid, cart);
            }
        }
        return;
    }
    try {
        if (qty <= 0) {
            await deleteDoc(doc(db, 'carts', uid, 'items', itemId));
        } else {
            await updateDoc(doc(db, 'carts', uid, 'items', itemId), { qty });
        }
    } catch (e) {
        console.log("Cart update error", e);
    }
}

export async function removeFromCart(uid, itemId) {
    if (!db) {
        saveDemoCart(uid, getDemoCart(uid).filter(i => i.id !== itemId));
        return;
    }
    try {
        await deleteDoc(doc(db, 'carts', uid, 'items', itemId));
    } catch (e) {
        console.log("Remove from cart error", e);
    }
}

export async function clearCart(uid) {
    if (!db) {
        saveDemoCart(uid, []);
        return;
    }
    try {
        const snap = await getDocs(collection(db, 'carts', uid, 'items'));
        await Promise.all(snap.docs.map(d => deleteDoc(d.ref)));
    } catch (e) {
        console.log("Clear cart error", e);
    }
}

// ─── Orders ──────────────────────────────────────────────────────────────────
export async function placeOrder(uid, cartItems, total) {
    if (!db) {
        const orders = getDemoOrders(uid);
        const newOrder = {
            id: 'demo-order-' + Date.now(),
            userId: uid,
            items: cartItems,
            total,
            status: 'pending',
            createdAt: new Date().toISOString(),
        };
        orders.unshift(newOrder);
        saveDemoOrders(uid, orders);
        await clearCart(uid);
        return newOrder.id;
    }
    try {
        const orderRef = await addDoc(collection(db, 'orders'), {
            userId: uid,
            items: cartItems,
            total,
            status: 'pending',
            createdAt: serverTimestamp(),
        });
        await clearCart(uid);
        return orderRef.id;
    } catch (e) {
        console.log("Order error", e);
        return 'demo-order-' + Date.now();
    }
}

export async function getUserOrders(uid) {
    if (!db) {
        return getDemoOrders(uid);
    }
    try {
        const q = query(collection(db, 'orders'), where('userId', '==', uid));
        const snap = await getDocs(q);
        return snap.docs.map(d => ({ id: d.id, ...d.data() }));
    } catch (e) {
        console.log("Orders error", e);
        return [];
    }
}

// ─── Seed Data (dev only) ────────────────────────────────────────────────────
export async function seedMarketData() {
    if (!db) {
        return 'Demo data ready - no Firebase configured ✅';
    }
    try {
      // Create sample shops
      const shopPromises = demoData.stores.map(async (shop) => {
        const { store_id, ...shopData } = shop;
        return await addDoc(collection(db, 'shops'), {
          ownerUid: 'seed',
          ...shopData,
          createdAt: serverTimestamp()
        });
      });
      
      const shopSnapshots = await Promise.all(shopPromises);
      const shopIds = shopSnapshots.map(s => s.id);
      
      // Create products linked to shops
      const productPromises = demoData.products.map(async (product, index) => {
        const { id, ...productData } = product;
        const shopIndex = index % shopIds.length;
        return await addDoc(collection(db, 'products'), {
          ...productData,
          shopId: shopIds[shopIndex],
          createdAt: serverTimestamp()
        });
      });
      
      await Promise.all(productPromises);

      return 'Seeded ' + shopIds.length + ' shops + ' + productPromises.length + ' products ✅';
    } catch (e) {
      console.log("Seed failed, using demo data", e);
      return 'Demo data ready - seed failed ✅';
    }
}
