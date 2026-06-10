<?php
//session_start();
echo '<footer id="footer-sho">
<div class="back-to-top">
    <a href="#header-sho" class="backtotop_a" >Back To Top</a>
</div>
<div class="footer-links">
    <div class="footer-items">
        <div class="footer-item">
            <ul class="ul-list-footer">
                <li style="font-size: 25px;">Connect With Us</li>
                <li class="list-items">Instagram</li>
                <li class="list-items">Facebook</li>
                <li class="list-items">Twitter</li>
            </ul>
        </div>
        <div class="footer-item">
        <ul class="ul-list-footer">
                <li style="font-size: 25px;">Let us Help You</li>
                <li class="list-items">';
                if (isset($_SESSION['userid'])) {
                    echo '<a href="account_page.php">Your Account</a></li>';
                }
                else { 
                    echo 'Your Account</li>';
                }
                echo '<li class="list-items">';
                if (isset($_SESSION['userid'])) {
                    echo '<a href="account_change_password.php">Change Password</a></li>';
                }
                else { 
                    echo 'Change Password</li>';
                }
                echo '<li class="list-items">';
                if (isset($_SESSION['userid'])) {
                    echo '<a href="account_order.php">Your Orders</a></li>';
                }
                else { 
                    echo 'Your Orders</li>';
                }
                echo '<li class="list-items">';
                if (isset($_SESSION['userid'])) {
                    echo '<a href="account_order_history.php">Orders History</a></li>';
                }
                else { 
                    echo 'Orders History</li>';
                }
                echo '<li class="list-items">Help</li>
                <li class="list-items">';
                if (isset($_SESSION['userid'])) {
                    echo '<form action="login.php" method="post" style="display: contents;">
                    <button name="logout" value="1" style="background: transparent;
                    font-size: 16px;
                    border: transparent;
                    color: whitesmoke;
                    position: relative;
                    left: -5px;
                }
                            ">
                        Logout
                    </button>
                </form></li>';
                }
                else { 
                    echo 'Logout</li>';
                }
            echo '</ul> 
        </div>
        <div class="footer-item">
        <ul class="ul-list-footer">
                <li style="font-size: 25px;">Get To Know Us</li>
                <li class="list-items">About Us</li>
                <li class="list-items">Careers</li>
                <li class="list-items">100% Purchase Protection</li>
            </ul>
        </div>
    </div>
</div>
<div class="final-footer">
    <div class="footer-final-line">
        ©️ Krishi Market — Smart Farmer Platform
    </div>
</div>
</footer>';
?>