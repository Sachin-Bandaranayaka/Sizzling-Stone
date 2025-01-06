# Sizzling Stone Restaurant Management System

A comprehensive restaurant management system built with PHP, featuring menu management, reservations, reviews, and an admin panel.

## Features

- **User Management**
  - User registration and authentication
  - Role-based access control (Admin/Customer)

- **Menu Management**
  - Display menu items by categories
  - Admin can add, edit, and delete menu items
  - Image upload for menu items

- **Reservation System**
  - Customers can make table reservations
  - Admin can manage and confirm reservations

- **Review System**
  - Customers can leave reviews
  - Star rating system
  - Admin can moderate reviews

- **Admin Panel**
  - Dashboard with overview statistics
  - Menu item management
  - Reservation management
  - Review moderation
  - User management

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Sachin-Bandaranayaka/Sizzling-Stone.git
   ```

2. Import the database:
   - Create a new MySQL database
   - Import the SQL files from the `database` folder in this order:
     1. users.sql
     2. reviews.sql
     3. menu_items.sql
     4. sample_data.sql

3. Configure the database connection:
   - Copy `config/database.example.php` to `config/database.php`
   - Update the database credentials in `config/database.php`

4. Set up the web server:
   - Point your web server to the `public` directory
   - Ensure PHP version 7.4 or higher is installed

5. Create required directories:
   ```bash
   mkdir -p public/images/menu
   mkdir -p public/images/uploads
   chmod 777 public/images/menu
   chmod 777 public/images/uploads
   ```

## Usage

1. Access the website through your web server
2. Default admin credentials:
   - Username: admin
   - Password: password

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

Sachin Bandaranayaka - [GitHub](https://github.com/Sachin-Bandaranayaka)
