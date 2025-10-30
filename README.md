# TechHaven - E-commerce Electronics Store

![TechHaven](https://img.shields.io/badge/TechHaven-E--commerce-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-blue)

A modern, fully responsive e-commerce website built with PHP and MySQL for selling electronic devices including cameras, phones, PCs, and accessories.

## 🚀 Live Demo
**URL:** [http://localhost/techhaven](http://localhost/techhaven)  
**Admin Panel:** [http://localhost/techhaven/admin](http://localhost/techhaven/admin)

## ✨ Features

### 🛍️ Customer Features
- **Product Catalog** - Browse products by categories (Smartphones, Laptops, Cameras, Accessories)
- **Advanced Search** - Search products by name, description, or category
- **Product Details** - Detailed product pages with image galleries and specifications
- **WhatsApp Integration** - Direct contact with sellers via WhatsApp with pre-filled product info
- **Responsive Design** - Mobile-first design that works on all devices
- **No Registration Required** - Customers can browse and contact without creating accounts

### 👨‍💼 Admin Features
- **Secure Admin Panel** - Protected login with password hashing
- **Product Management** - Add, edit, delete products with multiple images
- **Category Management** - Organize products into categories and subcategories
- **Inventory Tracking** - Stock management and low stock alerts
- **Dashboard Analytics** - View total products, categories, and visitor statistics
- **Bulk Operations** - Manage multiple products at once
- **Image Management** - Upload and manage product galleries

### 🛠️ Technical Features
- **Modern PHP** - Object-oriented programming practices
- **MySQL Database** - Normalized database schema with foreign key relationships
- **Tailwind CSS** - Utility-first CSS framework for modern UI
- **Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- **Security** - SQL injection prevention, XSS protection, secure file uploads
- **Performance** - Optimized queries and efficient code structure

## 📦 Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional)

### Quick Setup
1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/techhaven.git
   cd techhaven
   ```

2. **Database Setup**
   ```sql
   CREATE DATABASE techhaven_db;
   -- Import the provided SQL schema or run the setup script
   ```

3. **Configuration**
   - Update database credentials in `includes/config.php`
   - Set proper file permissions for uploads directory
   - Configure your web server document root to point to the `public` directory

4. **Access the Application**
   - Main site: `http://localhost/techhaven/public/`
   - Admin panel: `http://localhost/techhaven/admin/`
   - Default admin credentials: username `admin`, password `admin123`

## 🗂️ Project Structure

```
techhaven/
├── public/                 # Frontend files
│   ├── index.php          # Homepage
│   ├── products.php       # Product listing
│   ├── product-detail.php # Single product page
│   ├── search.php         # Search results
│   └── contact.php        # Contact page
├── admin/                 # Admin panel
│   ├── index.php          # Admin dashboard
│   ├── products/          # Product management
│   └── categories/        # Category management
├── includes/              # Core PHP files
│   ├── config.php         # Database configuration
│   ├── functions.php      # Helper functions
│   ├── auth.php           # Authentication system
│   ├── header.php         # Frontend header
│   └── footer.php         # Frontend footer
├── assets/                # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   ├── images/            # Images and icons
│   └── uploads/           # User-uploaded files
└── setup/                 # Installation scripts
```

## 🗄️ Database Schema

### Core Tables
- `admins` - Administrator accounts
- `categories` - Product categories and subcategories
- `products` - Product information and pricing
- `product_images` - Multiple images per product
- `product_views` - Visitor tracking and analytics

## 🎨 Design Highlights

- **Modern UI** - Clean, professional design with Tailwind CSS
- **Product Showcase** - Beautiful product cards with hover effects
- **Image Galleries** - Multiple image support with thumbnail navigation
- **Responsive Grid** - Adaptive layout for all screen sizes
- **Interactive Elements** - Smooth animations and transitions

## 🔧 Customization

### Adding New Product Categories
1. Login to admin panel
2. Navigate to Categories section
3. Add new category with name, description, and image

### Modifying Product Fields
Edit the `products` table and update:
- `admin/products/add.php`
- `admin/products/edit.php`
- `public/product-detail.php`

### Styling Changes
- Modify Tailwind classes in PHP files
- Custom CSS in `assets/css/style.css`
- Admin-specific styles in `admin/assets/css/admin.css`

## 🛡️ Security Features

- **Password Hashing** - bcrypt password encryption
- **SQL Injection Protection** - Prepared statements
- **XSS Prevention** - Input sanitization and output escaping
- **File Upload Security** - Type and size validation
- **Session Management** - Secure admin authentication
- **CSRF Protection** - Form token validation

## 📱 Mobile Optimization

- **Responsive Layout** - Adapts to all screen sizes
- **Touch-Friendly** - Optimized for mobile interactions
- **Fast Loading** - Optimized images and efficient code
- **Mobile-First** - Designed for mobile users first

## 🔄 Future Enhancements

- [ ] Shopping cart and checkout system
- [ ] User registration and profiles
- [ ] Order management system
- [ ] Payment gateway integration
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Email notifications
- [ ] Advanced search filters
- [ ] Multi-language support
- [ ] API for mobile apps

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/yourusername/techhaven/issues) page
2. Create a new issue with detailed description
3. Contact the development team

## 🙏 Acknowledgments

- Tailwind CSS for the amazing utility-first framework
- Font Awesome for beautiful icons
- PHP and MySQL communities for excellent documentation

---

**TechHaven** - Your ultimate destination for cutting-edge electronics and technology products. Built with ❤️ using modern web technologies.


*For demonstration and educational purposes. Not intended for production use without proper security audit.*
