/**
 * Product Form Page
 * Add or Edit product
 */

import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { productAPI, categoryAPI } from '../services/api';
import './ProductForm.css';

const ProductForm = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const isEdit = !!id;

  const [formData, setFormData] = useState({
    name: '',
    category_id: '',
    quantity: 0,
    min_stock_level: 10,
    expiry_date: '',
    supplier_name: '',
    product_image: '',
    description: '',
    unit_price: 0.00,
  });

  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    loadCategories();
    if (isEdit) {
      loadProduct();
    }
  }, [id]);

  const loadCategories = async () => {
    try {
      const response = await categoryAPI.getAll();
      setCategories(response.data.data);
      if (!isEdit && response.data.data.length > 0) {
        setFormData(prev => ({ ...prev, category_id: response.data.data[0].id }));
      }
    } catch (error) {
      console.error('Error loading categories:', error);
    }
  };

  const loadProduct = async () => {
    try {
      const response = await productAPI.getById(id);
      const product = response.data.data;
      setFormData({
        name: product.name || '',
        category_id: product.category_id || '',
        quantity: product.quantity || 0,
        min_stock_level: product.min_stock_level || 10,
        expiry_date: product.expiry_date || '',
        supplier_name: product.supplier_name || '',
        product_image: product.product_image || '',
        description: product.description || '',
        unit_price: product.unit_price || 0.00,
      });
    } catch (error) {
      setError('Error loading product');
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: name === 'category_id' || name === 'quantity' || name === 'min_stock_level' 
        ? parseInt(value) || 0
        : name === 'unit_price'
        ? parseFloat(value) || 0.00
        : value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      if (isEdit) {
        await productAPI.update(id, formData);
      } else {
        await productAPI.create(formData);
      }
      navigate('/products');
    } catch (error) {
      setError(error.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="product-form-page">
      <h2>{isEdit ? 'Edit Product' : 'Add New Product'}</h2>

      <div className="card">
        {error && <div className="alert alert-danger">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="form-row">
            <div className="form-group">
              <label>Product Name *</label>
              <input
                type="text"
                name="name"
                className="form-control"
                value={formData.name}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label>Category *</label>
              <select
                name="category_id"
                className="form-control"
                value={formData.category_id}
                onChange={handleChange}
                required
              >
                <option value="">Select Category</option>
                {categories.map((cat) => (
                  <option key={cat.id} value={cat.id}>
                    {cat.name}
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label>Quantity *</label>
              <input
                type="number"
                name="quantity"
                className="form-control"
                value={formData.quantity}
                onChange={handleChange}
                min="0"
                required
              />
            </div>

            <div className="form-group">
              <label>Minimum Stock Level *</label>
              <input
                type="number"
                name="min_stock_level"
                className="form-control"
                value={formData.min_stock_level}
                onChange={handleChange}
                min="0"
                required
              />
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label>Expiry Date (Optional)</label>
              <input
                type="date"
                name="expiry_date"
                className="form-control"
                value={formData.expiry_date}
                onChange={handleChange}
              />
            </div>

            <div className="form-group">
              <label>Unit Price</label>
              <input
                type="number"
                name="unit_price"
                className="form-control"
                value={formData.unit_price}
                onChange={handleChange}
                min="0"
                step="0.01"
              />
            </div>
          </div>

          <div className="form-group">
            <label>Supplier Name</label>
            <input
              type="text"
              name="supplier_name"
              className="form-control"
              value={formData.supplier_name}
              onChange={handleChange}
            />
          </div>

          <div className="form-group">
            <label>Description</label>
            <textarea
              name="description"
              className="form-control"
              rows="3"
              value={formData.description}
              onChange={handleChange}
            />
          </div>

          <div className="form-group">
            <label>Product Image URL (Optional)</label>
            <input
              type="url"
              name="product_image"
              className="form-control"
              value={formData.product_image}
              onChange={handleChange}
              placeholder="https://example.com/image.jpg"
            />
          </div>

          <div className="form-actions">
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => navigate('/products')}
            >
              Cancel
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={loading}
            >
              {loading ? 'Saving...' : isEdit ? 'Update Product' : 'Create Product'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ProductForm;
