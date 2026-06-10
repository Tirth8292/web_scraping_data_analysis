<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Portal - Near Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 800px;
            padding: 2rem;
        }
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .header h1 {
            color: #111827;
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
        }
        .header p {
            color: #6b7280;
            margin: 0;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .role-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #4f46e5;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            transition: background 0.3s, color 0.3s;
        }
        .role-card:hover .icon-box {
            background: #4f46e5;
            color: white;
        }
        .role-card h3 {
            margin: 0 0 0.75rem 0;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .role-card p {
            margin: 0;
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .back-link {
            text-align: center;
            margin-top: 3rem;
        }
        .back-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .back-link a:hover {
            background: #eef2ff;
        }
        @media (max-width: 640px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Partner Portal</h1>
            <p>Choose your role to access the dashboard</p>
        </div>

        <div class="cards-grid">
            <!-- Shop Owner -->
            <a href="shop_login.php" class="role-card">
                <div class="icon-box">
                    <i class="bi bi-shop"></i>
                </div>
                <h3>Shop Owner</h3>
                <p>Register your shop, manage inventory, track orders, and grow your business on Near Shop.</p>
            </a>

            <!-- Delivery Partner -->
            <a href="delivery_login.php" class="role-card">
                <div class="icon-box">
                    <i class="bi bi-truck"></i>
                </div>
                <h3>Delivery Partner</h3>
                <p>View assigned deliveries, navigate to customers, and update order statuses in real-time.</p>
            </a>
        </div>

        <div class="back-link">
            <a href="login.php">
                <i class="bi bi-arrow-left"></i> Back to Customer Login
            </a>
        </div>
    </div>
</body>
</html>
