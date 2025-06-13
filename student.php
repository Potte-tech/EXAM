<?php
// Get the variables sent via POST from the USSD gateway
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

require 'db.php'; // include DB connection

// Explode user input to track levels in the menu
$inputArray = explode("*", $text);
$level = count($inputArray);

if ($text == "") {
    // Main menu
    $response  = "CON Welcome to the Marks Appeal System\n";
    $response .= "1. Check my marks\n";
    $response .= "2. Appeal my marks\n";
    $response .= "3. Exit";
} else if ($inputArray[0] == "1") {
    // Check Marks
    if ($level == 1) {
        $response = "CON Enter your Student Reg number:";
    } else {
        $studentRegno = $inputArray[1];
        $stmt = $pdo->prepare("SELECT m.module_name, mk.mark FROM marks mk JOIN modules m ON mk.module_id = m.id WHERE mk.student_regno = ?");
        $stmt->execute([$studentRegno]);
        $marks = $stmt->fetchAll();

        if ($marks) {
            $response = "END Your Marks:\n";
            foreach ($marks as $m) {
                $response .= "{$m['module_name']}: {$m['mark']}\n";
            }
        } else {
            $response = "END Student Reg number not found. Please try again.";
        }
    }
} else if ($inputArray[0] == "2") {
    // Appeal process
    if ($level == 1) {
        $response = "CON Enter your Student Reg number:";
    } else if ($level == 2) {
        $studentRegno = $inputArray[1];
        // fetch modules
        $stmt = $pdo->prepare("SELECT m.module_name, mk.mark, m.id FROM marks mk JOIN modules m ON mk.module_id = m.id WHERE mk.student_regno = ?");
        $stmt->execute([$studentRegno]);
        $modules = $stmt->fetchAll();

        if ($modules) {
            $response = "CON Select the module to appeal:\n";
            foreach ($modules as $index => $mod) {
                $response .= ($index + 1) . ". {$mod['module_name']}: {$mod['mark']}\n";
            }
        } else {
            $response = "END Student Reg number not found.";
        }
    } else if ($level == 3) {
        $moduleIndex = (int)$inputArray[2] - 1;
        $studentRegno = $inputArray[1];
        // Fetch the correct module
        $stmt = $pdo->prepare("SELECT m.module_name, mk.mark, m.id FROM marks mk JOIN modules m ON mk.module_id = m.id WHERE mk.student_regno = ?");
        $stmt->execute([$studentRegno]);
        $modules = $stmt->fetchAll();
        $selectedModule = $modules[$moduleIndex] ?? null;

        if ($selectedModule) {
            $response = "CON Enter the reason for your appeal:";
        } else {
            $response = "END Invalid module selection.";
        }
    } else if ($level == 4) {
$studentRegno = $inputArray[1];
$moduleIndex = (int)$inputArray[2] - 1;
$reason = trim(end($inputArray)); // Safely get the reason

try {
    // Fetch module id
    $stmt = $pdo->prepare("SELECT m.id FROM marks mk JOIN modules m ON mk.module_id = m.id WHERE mk.student_regno = ?");
    $stmt->execute([$studentRegno]);
    $modules = $stmt->fetchAll();

    if (isset($modules[$moduleIndex])) {
        $moduleId = $modules[$moduleIndex]['id'];

        // Insert appeal
        $stmt = $pdo->prepare("INSERT INTO appeals(student_regno, module_id, reason, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$studentRegno, $moduleId, $reason]);

        $response = "END Thank you. Your appeal has been submitted.";
    } else {
        $response = "END Invalid module selected.";
    }
} catch (Exception $e) {
    $response = "END Sorry, a system error occurred. Please try again.";
}

    }
} else if ($inputArray[0] == "3") {
    $response = "END Thank you for using the system.";
} else {
    $response = "END Invalid input. Please try again.";
}

header('Content-type: text/plain');
echo $response;
