<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">

    <div class="logo">
        StayEase
    </div>

    <div class="nav-links">

        <a href="/final_project/PROJECT/index.php">Home</a>

        <a href="/final_project/PROJECT/properties/index.php">
            Properties
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="/final_project/PROJECT/booking/my-bookings.php">
                My Bookings
            </a>

            <?php if (($_SESSION['role'] ?? 'user') === 'admin'): ?>

                <a href="/final_project/PROJECT/admin/dashboard.php">
                    Admin
                </a>

            <?php endif; ?>

            <a href="/final_project/PROJECT/profile.php">
                Profile
            </a>

            <a href="/final_project/PROJECT/logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="/final_project/PROJECT/login.php">
                Login
            </a>

            <a href="/final_project/PROJECT/register.php">
                Register
            </a>

        <?php endif; ?>

    </div>

</nav>