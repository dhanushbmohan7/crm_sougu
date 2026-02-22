<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(
        "sql205.ezyro.com",
        "ezyro_40701233",
        "dhanush@2307",
        "ezyro_40701233_kkcrm"
    );

    echo "✅ Live server DB connected";

} catch (mysqli_sql_exception $e) {
    echo "❌ DB Error: " . $e->getMessage();
}
?>
