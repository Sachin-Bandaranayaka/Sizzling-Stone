<style>
    .footer {
        background: #333;
        color: #fff;
        padding: 3rem 0 1rem;
        margin-top: 3rem;
    }
    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    .footer-section h3 {
        color: #fff;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }
    .footer-section p {
        color: #ccc;
        line-height: 1.6;
    }
    .footer-section ul {
        list-style: none;
        padding: 0;
    }
    .footer-section ul li {
        margin-bottom: 0.5rem;
    }
    .footer-section ul li a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s;
    }
    .footer-section ul li a:hover {
        color: #fff;
    }
    .footer-bottom {
        text-align: center;
        padding-top: 2rem;
        border-top: 1px solid #444;
        color: #888;
    }
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .footer-section {
            margin-bottom: 2rem;
        }
    }
</style>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Sizzling Stone is a premier dining destination offering unique stone-grilled dishes and exceptional service in a warm, welcoming atmosphere.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>menu.php">Menu</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reservations.php">Reservations</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reviews.php">Reviews</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p>
                    123 Restaurant Street<br>
                    City, State 12345<br>
                    Phone: (123) 456-7890<br>
                    Email: info@sizzlingstone.com
                </p>
            </div>
            <div class="footer-section">
                <h3>Hours</h3>
                <p>
                    Monday - Friday: 11:00 AM - 10:00 PM<br>
                    Saturday - Sunday: 10:00 AM - 11:00 PM
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Sizzling Stone. All rights reserved.</p>
        </div>
    </div>
</footer>
