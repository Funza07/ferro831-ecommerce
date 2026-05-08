<?php
$is_footer_logged_in = isLoggedIn();
?>

<!-- NEWSLETTER -->
<section class="ferro-newsletter">
    <div class="ferro-newsletter-inner">
        <div>
            <p class="ferro-newsletter-eyebrow">STAY IN THE LOOP</p>
            <h3>Get Early Drop Access</h3>
            <p>Be the first to know when new FERRO831 drops hit. No spam, just steel.</p>
        </div>
        <form class="ferro-newsletter-form" onsubmit="return handleNewsletterSubmit(event)">
            <input type="email" id="ferroNewsletterEmail" placeholder="your@email.com" aria-label="Email address">
            <button type="submit">Join</button>
        </form>
    </div>
</section>

<!-- FOOTER -->
<footer class="ferro-footer">
    <div class="ferro-footer-grid">
        <div class="ferro-footer-brand">
            <span class="logo">FERRO<span>831</span></span>
            <p>Born from Jamshedpur's steel soul. Streetwear carrying the character of a city that built a nation.</p>
            <div class="social-links">
                <div class="social-btn">X</div>
                <div class="social-btn">IG</div>
                <div class="social-btn">IN</div>
                <div class="social-btn">YT</div>
            </div>
        </div>

        <div class="ferro-footer-col" data-accordion>
            <button type="button" class="ferro-footer-acc-btn" onclick="toggleFooterAccordion(this)">
                SHOP <span>v</span>
            </button>
            <ul class="ferro-footer-links">
                <li><a href="<?php echo url('products.php'); ?>">All Products</a></li>
                <li><a href="<?php echo url('products.php?category=Street+Line'); ?>">Street Line</a></li>
                <li><a href="<?php echo url('products.php?category=Daily+Line'); ?>">Daily Line</a></li>
                <li><a href="<?php echo url('products.php?category=Desk+Line'); ?>">Desk Line</a></li>
            </ul>
        </div>

        <div class="ferro-footer-col" data-accordion>
            <button type="button" class="ferro-footer-acc-btn" onclick="toggleFooterAccordion(this)">
                HELP <span>v</span>
            </button>
            <ul class="ferro-footer-links">
                <li><a href="<?php echo url('cart.php'); ?>">Cart</a></li>
                <li><a href="<?php echo $is_footer_logged_in ? url('user/orders.php') : url('login.php'); ?>">My Orders</a></li>
                <li><a href="<?php echo $is_footer_logged_in ? url('wishlist.php') : url('login.php'); ?>">Wishlist</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>

        <div class="ferro-footer-col" data-accordion>
            <button type="button" class="ferro-footer-acc-btn" onclick="toggleFooterAccordion(this)">
                BRAND <span>v</span>
            </button>
            <ul class="ferro-footer-links">
                <li><a href="<?php echo url('index.php#brand'); ?>">Our Story</a></li>
                <li><a href="#">Jamshedpur</a></li>
                <li><a href="#">Sustainability</a></li>
                <li><a href="#">Careers</a></li>
            </ul>
        </div>
    </div>

    <div class="ferro-footer-bottom">
        <span>© 2026 Ferro831. All rights reserved.</span>
        <div class="footer-legal">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Use</a>
            <a href="#">Shipping Policy</a>
        </div>
    </div>
</footer>

<!-- FLOATING BUTTONS -->
<div class="floating-btns">
    <div class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">^</div>
    <div class="whatsapp-btn" onclick="window.open('https://wa.me/91XXXXXXXXXX','_blank')" title="Chat on WhatsApp">WA</div>
</div>

<script src="<?php echo url('assets/js/main.js'); ?>"></script>
</body>
</html>

