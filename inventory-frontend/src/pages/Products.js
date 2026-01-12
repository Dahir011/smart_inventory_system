/**
 * Products Page
 * Product listing and management
 */

import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { productAPI, categoryAPI } from '../services/api';
import { aiAnalyticsAPI } from '../services/api';
import './Products.css';

const Products = () => {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [filters, setFilters] = useState({
    category_id: '',
    low_stock: false,
    near_expiry: false,
    expired: false,
    search: '',
  });
  const [loading, setLoading] = useState(true);
  const [aiInsights, setAiInsights] = useState({});

  useEffect(() => {
    loadCategories();
    loadProducts();
  }, [filters]);

  const loadCategories = async () => {
    try {
      const response = await categoryAPI.getAll();
      setCategories(response.data.data);
    } catch (error) {
      console.error('Error loading categories:', error);
    }
  };

  const loadProducts = async () => {
    setLoading(true);
    try {
      const params = {};
      if (filters.category_id) params.category_id = filters.category_id;
      if (filters.low_stock) params.low_stock = '1';
      if (filters.near_expiry) params.near_expiry = '1';
      if (filters.expired) params.expired = '1';
      if (filters.search) params.search = filters.search;

      const response = await productAPI.getAll(params);
      setProducts(response.data.data);
      
      // Load AI insights for products
      loadProductInsights(response.data.data);
    } catch (error) {
      console.error('Error loading products:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadProductInsights = async (productList) => {
    const insights = {};
    for (const product of productList.slice(0, 10)) { // Limit to first 10 for performance
      try {
        const response = await aiAnalyticsAPI.getInsights(product.id);
        insights[product.id] = response.data.data;
      } catch (error) {
        console.error(`Error loading insights for product ${product.id}:`, error);
      }
    }
    setAiInsights(insights);
  };

  const handleFilterChange = (key, value) => {
    setFilters({ ...filters, [key]: value });
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this product?')) {
      return;
    }

    try {
      await productAPI.delete(id);
      loadProducts();
    } catch (error) {
      alert('Error deleting product');
    }
  };

  const getStockStatus = (product) => {
    if (product.expiry_date) {
      const expiryDate = new Date(product.expiry_date);
      const today = new Date();
      const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
      
      if (daysUntilExpiry < 0) return { label: 'Expired', class: 'expired' };
      if (daysUntilExpiry <= 7) return { label: 'Near Expiry', class: 'near-expiry' };
    }
    
    if (product.quantity <= product.min_stock_level) {
      return { label: 'Low Stock', class: 'low-stock' };
    }
    
    return { label: 'In Stock', class: 'in-stock' };
  };

  if (loading) {
    return <div className="spinner"></div>;
  }

  return (
    <div className="products-page">
      <div className="page-header">
        <h2>Product Management</h2>
        <Link to="/products/add" className="btn btn-primary">
          + Add Product
        </Link>
      </div>

      {/* Filters */}
      <div className="card filters-card">
        <div className="filters">
          <div className="form-group">
            <label>Search</label>
            <input
              type="text"
              className="form-control"
              placeholder="Search products..."
              value={filters.search}
              onChange={(e) => handleFilterChange('search', e.target.value)}
            />
          </div>

          <div className="form-group">
            <label>Category</label>
            <select
              className="form-control"
              value={filters.category_id}
              onChange={(e) => handleFilterChange('category_id', e.target.value)}
            >
              <option value="">All Categories</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label>
              <input
                type="checkbox"
                checked={filters.low_stock}
                onChange={(e) => handleFilterChange('low_stock', e.target.checked)}
              />{' '}
              Low Stock
            </label>
          </div>

          <div className="form-group">
            <label>
              <input
                type="checkbox"
                checked={filters.near_expiry}
                onChange={(e) => handleFilterChange('near_expiry', e.target.checked)}
              />{' '}
              Near Expiry
            </label>
          </div>

          <div className="form-group">
            <label>
              <input
                type="checkbox"
                checked={filters.expired}
                onChange={(e) => handleFilterChange('expired', e.target.checked)}
              />{' '}
              Expired
            </label>
          </div>
        </div>
      </div>

      {/* Products Table */}
      <div className="card">
        <table className="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Category</th>
              <th>Quantity</th>
              <th>Min Level</th>
              <th>Expiry Date</th>
              <th>Status</th>
              <th>AI Insights</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {products.length === 0 ? (
              <tr>
                <td colSpan="8" className="text-center">
                  No products found
                </td>
              </tr>
            ) : (
              products.map((product) => {
                const status = getStockStatus(product);
                const insights = aiInsights[product.id];
                
                return (
                  <tr key={product.id}>
                    <td>{product.name}</td>
                    <td>{product.category_name}</td>
                    <td>{product.quantity}</td>
                    <td>{product.min_stock_level}</td>
                    <td>{product.expiry_date || 'N/A'}</td>
                    <td>
                      <span className={`status-badge ${status.class}`}>
                        {status.label}
                      </span>
                    </td>
                    <td>
                      {insights?.stockout_prediction ? (
                        <div className="ai-insight">
                          <small>
                            ⚡ Out in {insights.stockout_prediction.days_remaining} days
                          </small>
                        </div>
                      ) : (
                        <small>-</small>
                      )}
                    </td>
                    <td>
                      <div className="action-buttons">
                        <Link
                          to={`/products/edit/${product.id}`}
                          className="btn btn-secondary btn-sm"
                        >
                          Edit
                        </Link>
                        <button
                          className="btn btn-danger btn-sm"
                          onClick={() => handleDelete(product.id)}
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Products;
