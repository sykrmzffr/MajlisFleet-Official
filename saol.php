<?php
session_start();

// Create connection
$conn = new mysqli("localhost", "root", "", "ppm");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if username is set in session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username']; // Use session username
} else {
    $username = 'Guest'; // Default to 'Admin' if no session username
}

// Set dynamic schedule dates
$current_date = date('d-m-Y'); // Current date in 'YYYY-MM-DD' format

// Get and set default filter values with dynamic dates
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
$status_progress = isset($_GET['status_progress']) ? $_GET['status_progress'] : 'All'; // Default to 'All' if not set
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10; // Default to 10
$search = $_POST['search'] ?? '';

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for SQL query

// Prepare the base SQL query
$sql = "SELECT * FROM levels WHERE 1"; 

// Apply filters if provided
if (!empty($date_start)) {
    $sql .= " AND schedule_start >= '" . $conn->real_escape_string($date_start) . "'";
}
if (!empty($date_end)) {
    $sql .= " AND schedule_end <= '" . $conn->real_escape_string($date_end) . "'";
}
if ($status_progress !== 'All') {
    $sql .= " AND status_progress = '" . $conn->real_escape_string($status_progress) . "'";
}
if (!empty($search)) {
    $sql .= " AND (pcName LIKE '%" . $search . "%' OR remarks LIKE '%" . $search . "%')";
}

// Add LIMIT and OFFSET for pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query to fetch data with filters and pagination
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get schedule start and end dates from the database row
        $schedule_start = $row['schedule_start'];
        $schedule_end = $row['schedule_end'];

        // Initialize the status_progress variable
        $status_progress = '';

        // Calculate status_progress dynamically for each row based on schedule dates
        if (!empty($schedule_start) && !empty($schedule_end)) {
            // Convert dates to timestamp for comparison
            $days_until_start = (strtotime($schedule_start) - strtotime($current_date)) / 86400;
            $days_until_end = (strtotime($schedule_end) - strtotime($current_date)) / 86400;

            // Determine the status based on the calculated days
            if ($days_until_end > 0) {
                $status_progress = 'Upcoming';
            } elseif ($days_until_start <= 0 && $days_until_end >= 0) {
                $status_progress = 'Due';
            } elseif ($days_until_end < 0) {
                $status_progress = 'Overdue';
            }

            // Update status_progress in the database
            $update_sql = "UPDATE levels SET status_progress = '" . $conn->real_escape_string($status_progress) . "' WHERE id = " . $row['id'];
            $conn->query($update_sql);
        } else {
            // Set 'No Schedule' if no start or end date is available
            $status_progress = 'No Schedule';

            // Update status_progress in the database
            $update_sql = "UPDATE levels SET status_progress = '" . $conn->real_escape_string($status_progress) . "' WHERE id = " . $row['id'];
            $conn->query($update_sql);
        }

        // Add row to data array with the updated status_progress
        $row['status_progress'] = $status_progress;
        $data[] = $row;
    }
}

// Count total rows to calculate total pages for pagination
$sql_count = "SELECT COUNT(*) AS total FROM levels WHERE 1"; // '1' to start the query

// Reapply filters for the count query
if (!empty($date_start)) {
    $sql_count .= " AND schedule_start >= '" . $conn->real_escape_string($date_start) . "'";
}
if (!empty($date_end)) {
    $sql_count .= " AND schedule_end <= '" . $conn->real_escape_string($date_end) . "'";
}
if ($status_progress !== 'All') {
    $sql_count .= " AND status_progress = '" . $conn->real_escape_string($status_progress) . "'";
}
if (!empty($search)) {
    $sql_count .= " AND (pcName LIKE '%" . $conn->real_escape_string($search) . "%' OR remarks LIKE '%" . $conn->real_escape_string($search) . "%')";
}

// Execute the count query to get total number of rows
$count_result = $conn->query($sql_count);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit); // Calculate total pages

// Close the database connection after operations
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Report</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General styles */
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: #f8f9fc;
            display: flex;
            align-items: flex-start; /* Align sidebar and content side by side */
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
			margin-right: 100px;
			margin-bottom: 5px;
        }

        /* Sidebar styles */
        .sidebar {
            width: 180px;
            height: 100vh;
            background-color: #ffffff;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: fixed;
            border-radius: 0 12px 12px 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
            color: #007bff;
        }

        .sidebar a {
            text-decoration: none;
            color: #495057;
            padding: 10px 18px;
            margin: 6px 0;
            border-radius: 10px;
            font-size: 15px;
            display: flex;
            align-items: center;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #f0f4fa;
            color: #007bff;
            font-weight: 500;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin-bottom: 12px;
        }

        /* Content container styles */
        .content-container {
            margin-left: 230px;
            width: calc(100% - 240px);
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
		
		form {
			display: flex;
			align-items: center;
			gap: 10px; /* Space between form elements */
			flex-wrap: wrap; /* Allows wrapping on smaller screens */
			margin: 0; /* Remove default margin */
			padding: 0; /* Remove default padding */
		}

		form label {
			display: inline-block; /* Align label horizontally */
			margin-bottom: 0; /* Remove default margin */
			font-size: 14px;
			color: #2c3e50;
		}

		form input[type="date"],[type="search"],
		form select, {
			display: inline-block; /* Ensure inputs align in a row */
			padding: 6px;
			font-size: 13px;
			color: #495057;
			border: 1px solid #ced4da;
			border-radius: 8px;
			box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: border-color 0.3s ease;
			 font-family: 'Poppins';
		}

       
		form button {
			display: inline-block; /* Ensure inputs align in a row */
			padding: 6px;
			font-size: 13px;
			color: #fff;
			border: 1px solid #ced4da;
			border-radius: 8px;
			box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: border-color 0.3s ease;
			 font-family: 'Poppins';
			 width: 60px;
		}

		button:hover {
			background-color: #0056b3;
		}

        .form-container {
            width: 90%;
            max-width: 1400px;
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
			display: wrap;
			margin-right: 10px;
        }
		
		.table-container {
            width: 90%;
            max-width: 1300px;
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
			display: wrap;
			margin-right: 10px;
			margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 20px;
        }

        table thead {
            background: #007bff;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.05em;
        }

        table th, table td {
            padding: 8px 14px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

		#username {
			background-color: #2196F3; /* Green background for the bubble */
			color: white; /* White text color */
			padding: 8px 15px; /* Reduced padding for less gap */
			border-radius: 20px; /* Rounded corners to create a bubble shape */
			display: inline-block; /* Ensures the bubble doesn't stretch to full width */
			margin-left: 8px;
		}

		h4 {
			font-size: 15px; /* Adjust the font size */
			margin: 0; /* Remove default margin to reduce gap */
		}

		.separator {
			border: 1px solid #ccc; /* Light gray color for the separator line */
			margin: 5px 0; /* Reduced space above and below the line */
		}

        label {
            display: inline-block;
            font-weight: 500;
            color: #2c3e50;
        }

        .container {
            display: flex;
            justify-content: space-between;
            gap: 5px;
        }

        input[type="date"], input[type="text"], select {
            padding: 8px 10px;
            font-size: 14px;
            color: #495057;
            border: 1px solid #ced4da;
            border-radius: 8px;
            transition: border-color 0.3s ease;
			display: inline-block;
			cursor: pointer;
        }
        
        button {
            background-color: #007bff;
            color: white;
            font-size: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
			padding: 6px 10px;
			font-family: 'Poppins';
        }

        button:hover {
            background-color: #0056b3;
        }
		
		/* CSS for the status progress */
		td.overdue {
			color: #e74c3c;
			font-weight: bold;
		}

		td.due {
			color: #f39c12;
			font-weight: bold;
		}

		td.upcoming {
			color: #2ecc71;
			font-weight: bold;
		}
		
		
		#printPreviewBtn {
			background-color: #003366; /* Green background color */
			color: white; /* White text */
			border: none; /* No border */
			padding: 6px 6px; /* Padding around the text */
			text-align: center; /* Center text inside button */
			text-decoration: none; /* Remove underline from text */
			display: inline-block; /* Make it an inline block element */
			font-size: 13px; /* Increase font size */
			cursor: pointer; /* Pointer cursor on hover */
			border-radius: 8px; /* Rounded corners */
			transition: background-color 0.3s, transform 0.2s ease-in-out; /* Smooth hover effects */
			align-items: inherit;
		}

		/* Hover effect for the print button */
		#printPreviewBtn:hover {
			background-color: #0056b3; /* Darker green background on hover */
}
		
		/* Print-specific styles */
        @media print {
        body {
            font-size: 12px; /* Adjust font size for printing */
            background: none; /* Remove background colors or images */
            color: black; /* Use black text for readability */
			justify-content: center
        }

        /* Hide elements not needed in print */
        .sidebar,
        .form-container,
        .separator {
            display: none;
        }

        /* Make the table fill the width of the page */
        table {
            width: 90%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ddd; /* Add borders for better readability */
            padding: 10px;
            font-size: 10px; /* Adjust font size for table content */
        }

        /* Center the heading */
        h1 {
            text-align: center;
            font-size: 18px;
        }

        /* Remove margins and paddings for better print alignment */
        .content-container {
            margin: 10px;
            padding: 0;
			margin-bottom: 20px;
        }

        /* Ensure page content fits on the page */
        html, body {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
        }
		
		#printPreviewBtn {
			display: none;
		}
		.filter-container {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 100px; /* Optional: Adds spacing between items */
			margin: 10px 0; /* Optional: Adds some space above and below the container */
		}

		.filter-limit {
			margin-right: auto; /* Ensures it's aligned to the left */
		}

		.filter-search {
			margin-left: auto; /* Ensures it's aligned to the right */
		}
		
		.pagination {
		text-align: center;
		margin-top: 20px;
		}

		.pagination a {
			text-decoration: none;
			color: #007bff;
			padding: 8px 12px;
			margin: 0 5px;
			border: 1px solid #ddd;
			border-radius: 5px;
		}

		.pagination a:hover {
			background-color: #007bff;
			color: white;
		}

		.pagination a.active {
			background-color: #007bff;
			color: white;
			border: 1px solid #007bff;
		}

		.pagination a:disabled {
			background-color: #f0f0f0;
			color: #ccc;
			pointer-events: none;
		}
		
		.label search {
			justify-content: space-between;
			gap: 100px; /* Optional: Adds spacing between items */
		}

    </style>
</head>
<body>
    <div class="sidebar">
        <h4><span id="username">Welcome, <?php echo htmlspecialchars($username); ?></span></h4>
        <hr class="separator">
        <h2>Report</h2>
        <ul>
            <li><a href="mainPage.php">Dashboard</a></li>
            <li><a href="#" class="active">Report</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>
    </div>

    <div class="content-container">
        <h1>Schedule Report</h1>

        <div class="form-container">
            <form method="GET" action="">
                <label for="date_start">Date Start:</label>
                <input type="date" id="date_start" name="date_start" value="<?php echo htmlspecialchars($date_start); ?>">

                <label for="date_end">Date End:</label>
                <input type="date" id="date_end" name="date_end" value="<?php echo htmlspecialchars($date_end); ?>">

                <label for="status_progress">Progress:</label>
                <select id="status_progress" name="status_progress">
                    <option value="All" <?php echo (isset($_GET['status_progress']) && $_GET['status_progress'] == 'All') ? 'selected' : ''; ?>>All</option>
					<option value="Upcoming" <?php echo (isset($_GET['status_progress']) && $_GET['status_progress'] == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
					<option value="Due" <?php echo (isset($_GET['status_progress']) && $_GET['status_progress'] == 'Due') ? 'selected' : ''; ?>>Due</option>
					<option value="Overdue" <?php echo (isset($_GET['status_progress']) && $_GET['status_progress'] == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                </select>

                <button type="submit">Filter</button>
				<button id="printPreviewBtn" onclick="printListView()">Print</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
				
				<div class="filter-container">
				<label for="limit">Show: 
					<select id="limit" name="limit">
						<option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
						<option value="30" <?php echo $limit == 30 ? 'selected' : ''; ?>>30</option>
						<option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
						<option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
					</select>
				</label>

				<label for="search">Search:</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search">
        </label>

        <button type="submit">Apply</button>
			</div>
				
                    <tr>
					<th><input type="checkbox" id="select-all" class="columncheck" /></th> <!-- Select All Checkbox -->
                        <th>No</th>
                        <th>PC Name</th>
                        <th>Schedule</th>
                        <th>Progress</th>
						<th>Action</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
              <tbody>
<?php
if (!empty($data)) {
    // Set the timezone to Malaysia (Asia/Kuala_Lumpur)
    date_default_timezone_set('Asia/Kuala_Lumpur');
    
    // Get the current date in 'Y-m-d' format
    $current_date = date('d-m-Y'); 

   // Loop through data to process each appointment
    foreach ($data as $index => $row) {
        // Get the start and end schedule from the row
        $schedule_start = isset($row['schedule_start']) ? $row['schedule_start'] : null;
        $schedule_end = isset($row['schedule_end']) ? $row['schedule_end'] : null;

        // Initialize the status variables
        $status_class = '';
        $status_text = '';
        $status_progress = '';

        // Calculate days left until start and end, if applicable
        if ($schedule_start && $schedule_end) {
            $days_until_start = (strtotime($schedule_start) - strtotime($current_date)) / 86400;
            $days_until_end = (strtotime($schedule_end) - strtotime($current_date)) / 86400;

            // Determine the status based on days count
            if ($days_until_end > 0) {
                // The task is upcoming
                $status_class = 'upcoming';
                $status_text = 'Upcoming';
                $status_progress = 'Upcoming';
                
            } elseif ($days_until_start <= 0 && $days_until_end >= 0) {
                // The task is due or ongoing
                $status_class = 'due';
                $status_text = 'Due';
                $status_progress = 'Due';
                
            } elseif ($days_until_end < 0) {
                // The task is overdue
                $status_class = 'overdue';
                $status_text = 'Overdue';
                $status_progress = 'Overdue';
            }
        } else {
            // Default to 'No schedule' if dates are missing
            $status_class = 'no-schedule';
            $status_text = 'No Schedule';
            $status_progress = 'No Schedule';
        }

        // Display the table row with the status class
        echo "<tr class='" . htmlspecialchars($status_class) . "'>";

        // Checkbox for each row
        echo "<td><input type='checkbox' name='selected[]' value='" . htmlspecialchars($row['pcName']) . "' /></td>";

        // Index calculation
        echo "<td>" . ($index + 1) . "</td>";

        // Display PC name
        echo "<td>" . htmlspecialchars($row['pcName']) . "</td>";

        // Display schedule start and end, if defined
        if ($schedule_start && $schedule_end) {
            echo "<td>Start: " . htmlspecialchars(date('d-m-Y', strtotime($schedule_start))) . 
                 "<br>End: " . htmlspecialchars(date('d-m-Y', strtotime($schedule_end))) . "</td>";
        } else {
            echo "<td>No schedule data</td>";
        }

        // Display status in the progress column (based on the schedule status)
        echo "<td class='" . htmlspecialchars($status_class) . "'>" . htmlspecialchars($status_text) . "</td>";
		
		// Actions column with "Update" button
        echo "<td><button type='update-button' class='update-button' onclick='updateRow(" . htmlspecialchars(json_encode($row)) . ")'>Update</button></td>";

        // Display remarks
        echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No data available</td></tr>";
}
?>

</tbody>
</table>
			
		<div class="pagination">
			<?php if ($page > 1): ?>
				<a href="?page=<?= $page - 1 ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>&status_progress=<?= urlencode($status_progress) ?>&search=<?= urlencode($search) ?>">Previous</a>
			<?php endif; ?>

			<?php for ($i = 1; $i <= $total_pages; $i++): ?>
				<a href="?page=<?= $i ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>&status_progress=<?= $status_progress ?>&search=<?= $search ?>" 
				   class="<?= ($i === $page) ? 'active' : '' ?>">
				   <?= $i ?>
				</a>
			<?php endfor; ?>

			<?php if ($page < $total_pages): ?>
				<a href="?page=<?= $page - 1 ?>&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>&status_progress=<?= urlencode($status_progress) ?>&search=<?= urlencode($search) ?>">Next</a>
			<?php endif; ?>
       </div>

<!-- JavaScript for List View Printing -->
<script>
    function printListView() {
        // Get selected rows
        const checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
        const selectedData = [];

        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const pcName = row.children[2].textContent; // PC Name
            const schedule = row.children[3].textContent; // Schedule (Start and End)
            const progress = row.children[4].textContent; // Status (Upcoming, Due, Overdue)
            const remarks = row.children[5].textContent; // Remarks

            selectedData.push({ pcName, schedule, progress, remarks });
        });

        if (selectedData.length > 0) {
            // Store selected data in localStorage and open print page
            localStorage.setItem('selectedData', JSON.stringify(selectedData));
            window.open('print.php', '_blank');
        } else {
            alert('Please select at least one row to print.');
        }
    }
</script>

<!-- JavaScript for Row Update -->
<script>
    function updateRow(rowData) {
        // Construct URL for the update form with query parameters
        const updateUrl = updateform.php?id=${encodeURIComponent(rowData.id)}&remarks=${encodeURIComponent(rowData.remarks)};
        
        // Redirect to the update form page
        window.location.href = updateUrl;
    }
</script>

<!-- DataTables and Checkbox Handling -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#schedule-table').DataTable();

        // Select/Deselect all checkboxes
        $('#select-all').click(function() {
            $('input[type="checkbox"]').prop('checked', this.checked);
        });
    });
</script>
	
</body>
</html>