
import React, { useState, useEffect } from 'react';
import {
    LayoutDashboard, Package, ShoppingCart, Settings,
    Plus, Edit2, Trash2, ChevronLeft, DollarSign, Users
} from 'lucide-react';
import { getShopProducts, getShopOrders, addProduct, deleteProduct } from './marketService';

const ShopDashboard = ({ user, shop, onBack, t }) => {
    const [activeTab, setActiveTab] = useState('products');
    const [products, setProducts] = useState([]);
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showAddProduct, setShowAddProduct] = useState(false);
    const [form, setForm] = useState({
        name: '', category: '', brand: '', price: '', stock_quantity: '', description: ''
    });

    useEffect(() => {
        const loadData = async () => {
            setLoading(true);
            try {
                const shopId = shop?.id || 1;
                const shopProducts = await getShopProducts(shopId);
                const shopOrders = await getShopOrders(shopId);
                setProducts(shopProducts);
                setOrders(shopOrders);
            } catch (e) {
                console.error('Error loading shop data:', e);
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, [shop]);

    const handleAddProduct = async (e) => {
        e.preventDefault();
        try {
            const shopId = shop?.id || 1;
            const newProduct = await addProduct(shopId, {
                name: form.name,
                category: form.category,
                brand: form.brand,
                price: parseFloat(form.price),
                stock_quantity: parseInt(form.stock_quantity),
                description: form.description,
                discount_price: null
            });
            setProducts([newProduct, ...products]);
            setShowAddProduct(false);
            setForm({ name: '', category: '', brand: '', price: '', stock_quantity: '', description: '' });
        } catch (e) {
            console.error('Error adding product:', e);
            alert('Failed to add product. Please try again.');
        }
    };

    const handleDeleteProduct = async (id) => {
        if (window.confirm('Are you sure you want to delete this product?')) {
            try {
                const shopId = shop?.id || 1;
                await deleteProduct(shopId, id);
                setProducts(products.filter(p => p.id !== id));
            } catch (e) {
                console.error('Error deleting product:', e);
                alert('Failed to delete product. Please try again.');
            }
        }
    };

    const getStats = () => {
        const totalRevenue = orders.reduce((sum, o) => sum + (o.amount || 0), 0);
        const totalOrders = orders.length;
        const totalProducts = products.length;
        const totalSales = products.reduce((sum, p) => sum + (p.sales_count || 0), 0);
        return { totalRevenue, totalOrders, totalProducts, totalSales };
    };

    const stats = getStats();

    if (loading) {
        return (
            <div className="empty-state">
                <div className="market-spinner"></div>
                <p>Loading Dashboard...</p>
            </div>
        );
    }

    return (
        <div style={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
            <div className="market-topbar">
                <button className="btn-back" onClick={onBack}><ChevronLeft size={18} /> Back</button>
                <h3 style={{ margin: 0 }}>🏪 Shop Dashboard</h3>
                <div style={{ width: 50 }}></div>
            </div>

            <div style={{
                display: 'flex', gap: 1, borderBottom: '1px solid var(--border-color)',
                marginBottom: '1rem', background: 'var(--bg-color)'
            }}>
                {[
                    { id: 'products', icon: Package, label: 'Products' },
                    { id: 'orders', icon: ShoppingCart, label: 'Orders' },
                    { id: 'stats', icon: DollarSign, label: 'Stats' },
                    { id: 'settings', icon: Settings, label: 'Settings' }
                ].map(tab => (
                    <button
                        key={tab.id}
                        onClick={() => setActiveTab(tab.id)}
                        style={{
                            flex: 1, padding: '0.75rem 1rem', background: activeTab === tab.id ? 'var(--primary-50)' : 'transparent',
                            border: 0, cursor: 'pointer', display: 'flex', flexDirection: 'column', gap: 4,
                            alignItems: 'center', color: activeTab === tab.id ? 'var(--primary-color)' : 'var(--text-secondary)'
                        }}
                    >
                        <tab.icon size={20} />
                        <span style={{ fontSize: '0.8rem', fontWeight: 600 }}>{tab.label}</span>
                    </button>
                ))}
            </div>

            <div style={{ flex: 1, overflow: 'auto' }}>
                {activeTab === 'products' && (
                    <div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
                            <h3 style={{ margin: 0 }}>Products ({products.length})</h3>
                            <button
                                className="btn-primary"
                                style={{ padding: '0.5rem 1rem' }}
                                onClick={() => setShowAddProduct(true)}
                            >
                                <Plus size={16} /> Add Product
                            </button>
                        </div>

                        {showAddProduct && (
                            <div style={{ background: 'var(--card-bg)', padding: '1.25rem', borderRadius: '0.75rem', marginBottom: '1rem' }}>
                                <h4 style={{ margin: '0 0 0.75rem' }}>Add New Product</h4>
                                <form onSubmit={handleAddProduct} style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                    <input
                                        className="market-input" placeholder="Product Name" required
                                        value={form.name} onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                                    />
                                    <select
                                        className="market-select" required
                                        value={form.category} onChange={e => setForm(f => ({ ...f, category: e.target.value }))}
                                    >
                                        <option value="">Select Category</option>
                                        <option value="Seeds">Seeds</option>
                                        <option value="Fertilizers">Fertilizers</option>
                                        <option value="Pesticides">Pesticides</option>
                                        <option value="Farm Equipment">Farm Equipment</option>
                                        <option value="Irrigation">Irrigation</option>
                                        <option value="Animal Feed">Animal Feed</option>
                                        <option value="Organic Products">Organic Products</option>
                                        <option value="Tools">Tools</option>
                                    </select>
                                    <input
                                        className="market-input" placeholder="Brand"
                                        value={form.brand} onChange={e => setForm(f => ({ ...f, brand: e.target.value }))}
                                    />
                                    <input
                                        type="number" className="market-input" placeholder="Price" required
                                        value={form.price} onChange={e => setForm(f => ({ ...f, price: e.target.value }))}
                                    />
                                    <input
                                        type="number" className="market-input" placeholder="Stock Quantity" required
                                        value={form.stock_quantity} onChange={e => setForm(f => ({ ...f, stock_quantity: e.target.value }))}
                                    />
                                    <textarea
                                        className="market-input" placeholder="Description" style={{ minHeight: 80 }}
                                        value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))}
                                    />
                                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                                        <button type="submit" className="btn-primary">Add Product</button>
                                        <button
                                            type="button" className="btn-secondary"
                                            onClick={() => setShowAddProduct(false)}
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}

                        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                            {products.map(product => (
                                <div key={product.id} style={{
                                    display: 'flex', gap: '1rem', alignItems: 'center',
                                    background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem'
                                }}>
                                    <div style={{
                                        width: 60, height: 60, borderRadius: '0.5rem', overflow: 'hidden',
                                        background: 'var(--primary-50)', display: 'flex', alignItems: 'center', justifyContent: 'center'
                                    }}>
                                        {product.image_url ? (
                                            <img src={product.image_url} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                        ) : (
                                            <span style={{ fontSize: '1.5rem' }}>📦</span>
                                        )}
                                    </div>
                                    <div style={{ flex: 1 }}>
                                        <div style={{ fontWeight: 600, color: 'var(--text-primary)' }}>{product.name}</div>
                                        <div style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: 2 }}>
                                            {product.category} • Stock: {product.stock_quantity} • Sold: {product.sales_count}
                                        </div>
                                    </div>
                                    <div style={{ fontWeight: 700, color: 'var(--primary-color)' }}>
                                        ₹{product.discount_price || product.price}
                                    </div>
                                    <button
                                        style={{ background: 'none', border: 0, cursor: 'pointer', color: '#dc2626', padding: '0.5rem' }}
                                        onClick={() => handleDeleteProduct(product.id)}
                                    >
                                        <Trash2 size={18} />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {activeTab === 'orders' && (
                    <div>
                        <h3 style={{ margin: '0 0 1rem' }}>Orders ({orders.length})</h3>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                            {orders.map(order => (
                                <div key={order.order_id} style={{
                                    background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem'
                                }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                                        <div>
                                            <div style={{ fontWeight: 600, color: 'var(--text-primary)' }}>
                                                {order.farmer_name}
                                            </div>
                                            <div style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', marginTop: 2 }}>
                                                Order #{order.order_id} • {order.order_date}
                                            </div>
                                        </div>
                                        <div style={{ textAlign: 'right' }}>
                                            <div style={{ fontWeight: 700, color: 'var(--primary-color)' }}>₹{order.amount}</div>
                                            <div style={{ fontSize: '0.8rem', marginTop: 2 }}>
                                                <span style={{
                                                    padding: '0.25rem 0.5rem', borderRadius: '0.375rem',
                                                    background: order.delivery_status === 'Delivered' ? '#d1fae5' :
                                                              order.delivery_status === 'Processing' ? '#fef3c7' : '#f3f4f6',
                                                    color: order.delivery_status === 'Delivered' ? '#065f46' :
                                                          order.delivery_status === 'Processing' ? '#92400e' : '#374151'
                                                }}>
                                                    {order.delivery_status}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style={{ marginTop: '0.5rem', fontSize: '0.85rem', color: 'var(--text-secondary)' }}>
                                        Product: {order.product_name} • Qty: {order.quantity}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {activeTab === 'stats' && (
                    <div>
                        <h3 style={{ margin: '0 0 1rem' }}>Statistics</h3>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1.5rem' }}>
                            <div style={{ background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem' }}>
                                <div style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>Total Revenue</div>
                                <div style={{ fontSize: '1.5rem', fontWeight: 700, color: 'var(--primary-color)' }}>₹{stats.totalRevenue}</div>
                            </div>
                            <div style={{ background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem' }}>
                                <div style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>Total Orders</div>
                                <div style={{ fontSize: '1.5rem', fontWeight: 700 }}>{stats.totalOrders}</div>
                            </div>
                            <div style={{ background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem' }}>
                                <div style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>Products</div>
                                <div style={{ fontSize: '1.5rem', fontWeight: 700 }}>{stats.totalProducts}</div>
                            </div>
                            <div style={{ background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem' }}>
                                <div style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>Total Sales</div>
                                <div style={{ fontSize: '1.5rem', fontWeight: 700 }}>{stats.totalSales}</div>
                            </div>
                        </div>
                    </div>
                )}

                {activeTab === 'settings' && (
                    <div>
                        <h3 style={{ margin: '0 0 1rem' }}>Settings</h3>
                        <div style={{ background: 'var(--card-bg)', padding: '1rem', borderRadius: '0.75rem' }}>
                            <div style={{ padding: '0.5rem 0', borderBottom: '1px solid var(--border-color)' }}>
                                <div style={{ fontWeight: 600 }}>Shop Profile</div>
                            </div>
                            <div style={{ padding: '0.75rem 0', borderBottom: '1px solid var(--border-color)' }}>
                                <div style={{ fontWeight: 600 }}>Opening Hours</div>
                            </div>
                            <div style={{ padding: '0.75rem 0' }}>
                                <div style={{ fontWeight: 600 }}>Bank Details</div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ShopDashboard;
