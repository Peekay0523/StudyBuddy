<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Update application fees for all institutions
$fees = [
    1 => 100,  // University of Cape Town
    2 => 100,  // University of the Witwatersrand
    3 => 120,  // Stellenbosch University
    4 => 150,  // University of Pretoria
    5 => 100,  // University of KwaZulu-Natal
    6 => 80,   // Cape Peninsula University of Technology
    7 => 80,   // Tshwane University of Technology
    8 => 80,   // Durban University of Technology
    9 => 120,  // University of Johannesburg
    10 => 100, // Rhodes University
    11 => 100, // North-West University
    12 => 100, // University of the Free State
    13 => 100, // Nelson Mandela University
    14 => 80,  // University of Limpopo
    15 => 100  // University of the Western Cape
];

foreach ($fees as $id => $fee) {
    $stmt = $db->prepare('UPDATE institutions SET application_fee = ? WHERE id = ?');
    $stmt->execute([$fee, $id]);
}

echo "Application fees updated successfully!\n\n";

// Verify
$stmt = $db->query('SELECT id, name, application_fee FROM institutions ORDER BY id');
echo "Current Application Fees:\n";
echo str_repeat("-", 60) . "\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%2d. %-45s R%3d\n", $row['id'], substr($row['name'], 0, 45), $row['application_fee']);
}
