<?php
require_once('includes/config.php');
require_once('includes/db_connect.php');

$conn = getDBConnection();

$query_events = "SELECT 
    e.*, 
    COALESCE(r.registrant_count, 0) as current_participants
FROM 
    events e
LEFT JOIN (
    SELECT 
        event_id, 
        COUNT(*) as registrant_count
    FROM 
        registrations
    GROUP BY 
        event_id
) r ON e.id = r.event_id
ORDER BY 
    CASE 
        WHEN e.status = 'active' THEN 1
        WHEN e.status = 'upcoming' THEN 2
        ELSE 3 
    END,
    e.date ASC";


$result_events = mysqli_query($conn, $query_events);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SukaEvent - Event Registration System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        .event-card {
            position: relative;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            padding: 0.5rem;
            height: auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .banner-image {
            height: 120px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .event-card:hover .banner-image {
            transform: scale(1.05);
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: background-color 0.3s ease;
        }

        .participants-badge {
            background: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .event-info {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .login-prompt {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .event-card:hover .login-prompt {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <img src="logo/logoUAS.png" alt="Logo" class="h-8 w-auto mr-2">
                        <span class="text-xl font-bold text-gray-800">SukaEvent</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="user/login.php" class="text-gray-600 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="user/register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Banner -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome to SukaEvent</h1>
            <p class="text-gray-600">Discover and join amazing events in your area. Login or register to participate!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (mysqli_num_rows($result_events) > 0): ?>
                <?php while($event = mysqli_fetch_assoc($result_events)): 
                    $status = htmlspecialchars($event['status']);

                    switch ($status) {
                        case 'upcoming':
                            $status_class = 'bg-blue-100 text-blue-800';
                            $status_icon = 'fa-clock';
                            break;
                        case 'active':
                            $status_class = 'bg-green-100 text-green-800';
                            $status_icon = 'fa-check-circle';
                            break;
                        case 'canceled':
                            $status_class = 'bg-red-100 text-red-800';
                            $status_icon = 'fa-times-circle';
                            break;
                        case 'completed':
                            $status_class = 'bg-gray-100 text-gray-800';
                            $status_icon = 'fa-flag-checkered';
                            break;
                        default:
                            $status_class = 'bg-gray-100 text-gray-800';
                            $status_icon = 'fa-info-circle';
                    }
                ?>
                    <div class="event-card rounded-xl">
                        <?php if($event['banner']): ?>
                            <div class="overflow-hidden">
                                <img src="<?php echo htmlspecialchars($event['banner']); ?>" 
                                    class="banner-image" 
                                    alt="Event banner">
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h2 class="text-xl font-bold mb-3 text-gray-900">
                                <?php echo htmlspecialchars($event['name']); ?>
                            </h2>
                            
                            <p class="text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="event-info">
                                <i class="fas fa-calendar-alt text-blue-500"></i>
                                <span><?php echo date('F d, Y', strtotime($event['date'])); ?>
                                at <?php echo date('h:i A', strtotime($event['time'])); ?></span>
                            </div>
                            
                            <div class="event-info">
                                <i class="fas fa-map-marker-alt text-red-500"></i>
                                <span><?php echo htmlspecialchars($event['location']); ?></span>
                            </div>

                            <div class="mt-6 flex items-center justify-between">
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <i class="fas <?php echo $status_icon; ?>"></i>
                                    <?php echo ucfirst($status); ?>
                                </span>
                                
                                <div class="participants-badge">
                                    <i class="fas fa-users text-gray-500"></i>
                                    <span><?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Login Prompt Overlay -->
                        <div class="login-prompt">
                            <div class="text-center">
                                <a href="user/login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-block mb-2">
                                    Login to View Details
                                </a>
                                <p class="text-sm text-gray-600">or <a href="user/register.php" class="text-blue-500 hover:text-blue-600">register</a> for a new account</p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-yellow-400 text-2xl mr-4"></i>
                            <div>
                                <h3 class="text-lg font-medium text-yellow-800">No Events Available</h3>
                                <p class="text-yellow-700">Check back later for upcoming events!</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>