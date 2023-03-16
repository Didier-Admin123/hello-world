<!DOCTYPE html>
<html>

<head>
        <title>Insert Page page</title>
</head>

<body>
        <center>
<?php
/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$host = "10.98.236.189";
try{

    $pdo = new PDO("mysql:host=$host;dbname=employees", "root", "password");
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Print host information
    echo "Connect Successfully. Host: $host ";

  // Define and perform the SQL SELECT query
  $sql = "SELECT * FROM records";

    echo '</br>';
    $result = $pdo->query($sql);

    if($result !== false) {
        $cols = $result->columnCount();           // Number of returned columns
    echo '</br>';
        echo 'Number of returned columns: '. $cols. '<br />';
    echo '</br>';
        // Parse the result set
        foreach($result as $row) {
        echo $row['ID']. ' - '. $row['FirstName']. ' - '. $row['LastName']. ' - '. $row['Address']. ' - '. $row['City'].'<br />';
        }
    }

    $ID =  $_REQUEST['ID'];
    $FirstName = $_REQUEST['FirstName'];
    $LastName =  $_REQUEST['LastName'];
    $Address = $_REQUEST['Address'];
    $City = $_REQUEST['City'];

    $sql = "INSERT INTO `records`(`ID`, `FirstName`, `LastName`, `Address`, `City`) VALUES ('$ID','$FirstName','$LastName','$Address','$City')";
    $result = $pdo->query($sql);
  
    echo "</br>";
    echo nl2br("\nID: $ID\n FirstName: $FirstName\n "
    . "LastName: $LastName\n Address: $Address\n City: $City");
    echo "</br>";

$pdo->getAttribute(constant("PDO::ATTR_CONNECTION_STATUS"));
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
</center>


<form action="index.php" method="post">

                        <input type="submit" value="Back">
                </form>



</body>
</html>
