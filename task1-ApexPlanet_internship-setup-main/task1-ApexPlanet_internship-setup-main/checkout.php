<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$conn       = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("USE bookstore");
?>
<html>
<head>
<style> 
header {
    background-color: rgb(0,51,102);
    width: 100%;
}
header img {
    margin: 1%;
}
header .hi{
    background-color: #fff;
    border: none;
    border-radius: 20px;
    text-align: center;
    transition-duration: 0.5s; 
    padding: 8px 30px;
    cursor: pointer;
    color: #000;
    margin-top: 15%;
}
header .hi:hover{
    background-color: #ccc;
}
form{
    margin-top: 1%;
    float: left;
    width: 40%;
    color: #000;
}
input[type=text] {
    padding: 5px;
    border-radius: 3px;
    box-sizing: border-box;
    border: 2px solid #ccc;
    transition: 0.5s;
    outline: none;
    width:95%;
}
input[type=text]:focus {
    border: 2px solid rgb(0,51,102);
}
textarea {
    outline: none;
    border: 2px solid #ccc;
    width:95%;
}
textarea:focus {
    border: 2px solid rgb(0,51,102);
}
.button{
    background-color: rgb(0,51,102);
    border: none;
    border-radius: 20px;
    text-align: center;
    transition-duration: 0.5s; 
    padding: 8px 30px;
    cursor: pointer;
    color: #fff;
}
.button:hover {
    background-color: rgb(102,255,255);
    color: #000;
}
table {
    border-collapse: collapse;
    width: 60%;
    float: right;
}
th, td {
    text-align: left;
    padding: 8px;
}
tr{background-color: #fff;}
th {
    background-color: rgb(0,51,102);
    color: white;
}
.container {
    width: 50%;
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 20px;
    margin: 0 auto;
}
</style>
</head>
<body style="font-family:Arial; margin: 0 auto; background-color: #f2f2f2;">
<header>
<blockquote>
    <img src="image/logo.png">
    <input class="hi" style="float: right; margin: 2%;" type="button" name="cancel" value="Home" onClick="window.location='index.php';" />
</blockquote>
</header>
<blockquote>
<?php
// ---------- LOGGED-IN USER CHECKOUT ----------
if (isset($_SESSION['id'])) {
    // set CustomerID in cart directly
    $conn->query("
        UPDATE cart 
        SET CustomerID = (SELECT CustomerID FROM customer WHERE UserID = ".$_SESSION['id'].")
    ");

    // insert cart items into orders
    $conn->query("
        INSERT INTO `order`(CustomerID, BookID, DatePurchase, Quantity, TotalPrice, Status)
        SELECT CustomerID, BookID, CURRENT_TIME, Quantity, TotalPrice, 'N' FROM cart
    ");

    $conn->query("DELETE FROM cart");

    $result = $conn->query("
        SELECT c.CustomerName,c.CustomerGender,c.CustomerAddress,c.CustomerEmail,c.CustomerPhone,
               b.BookTitle,b.Price,b.Image,o.DatePurchase,o.Quantity,o.TotalPrice
        FROM customer c
        JOIN `order` o ON o.CustomerID=c.CustomerID
        JOIN book b ON o.BookID=b.BookID
        WHERE o.Status='N' AND c.UserID=".$_SESSION['id']."
    ");

    echo '<div class="container">';
    echo '<blockquote>';
    ?>
    <input class="button" style="float: right;" type="button" name="cancel" value="Continue Shopping" onClick="window.location='index.php';" />
    <?php
    echo '<h2 style="color: #000;">Order Successful</h2>';
    echo "<table style='width:100%'>";
    echo "<tr><th>Order Summary</th><th></th></tr>";

    if ($row=$result->fetch_assoc()) {
        echo "<tr><td>Name:</td><td>".$row['CustomerName']."</td></tr>";
        echo "<tr><td>E-mail:</td><td>".$row['CustomerEmail']."</td></tr>";
        echo "<tr><td>Mobile Number:</td><td>".$row['CustomerPhone']."</td></tr>";
        echo "<tr><td>Gender:</td><td>".$row['CustomerGender']."</td></tr>";
        echo "<tr><td>Address:</td><td>".$row['CustomerAddress']."</td></tr>";
        echo "<tr><td>Date:</td><td>".$row['DatePurchase']."</td></tr>";
        $result->data_seek(0);
    }

    $total=0;
    while ($row=$result->fetch_assoc()) {
        echo "<tr><td style='border-top:2px solid #ccc;'>";
        echo '<img src="'.$row["Image"].'" width="20%"></td><td style="border-top:2px solid #ccc;">';
        echo $row['BookTitle']."<br>₹".$row['Price']."<br>";
        echo "Quantity: ".$row['Quantity']."<br>";
        echo "</td></tr>";
        $total += $row['TotalPrice'];
    }
    echo "<tr><td style='background-color:#ccc;'></td>
          <td style='text-align:right;background-color:#ccc;'>Total Price: <b>₹".$total."</b></td></tr>";
    echo "</table>";
    echo "</div>";

    $conn->query("UPDATE `order` o 
                  JOIN customer c ON o.CustomerID=c.CustomerID
                  SET o.Status='Y' 
                  WHERE c.UserID=".$_SESSION['id']);
}

// ---------- GUEST CHECKOUT ----------
if (!isset($_SESSION['id'])) {
    // show form
    ?>
    <form method="post" action="">
    Name:<br><input type="text" name="name" placeholder="Full Name"><br><br>
    E-mail:<br><input type="text" name="email" placeholder="example@email.com"><br><br>
    Mobile Number:<br><input type="text" name="contact" placeholder="Mobile Number"><br><br>
    <label>Gender:</label><br>
    <input type="radio" name="gender" value="Male">Male
    <input type="radio" name="gender" value="Female">Female<br><br>
    <label>Address:</label><br>
    <textarea name="address" cols="30" rows="5" placeholder="Address"></textarea><br><br>
    <input class="button" type="button" name="cancel" value="Cancel" onClick="window.location='index.php';" />
    <input class="button" type="submit" name="submitButton" value="CHECKOUT">
    </form>
    <?php
}

if (isset($_POST['submitButton']) && !isset($_SESSION['id'])) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $gender  = $_POST['gender'];
    $address = trim($_POST['address']);

    // insert customer
    $conn->query("INSERT INTO customer(CustomerName, CustomerPhone, CustomerEmail, CustomerAddress, CustomerGender)
                  VALUES('".$conn->real_escape_string($name)."',
                         '".$conn->real_escape_string($contact)."',
                         '".$conn->real_escape_string($email)."',
                         '".$conn->real_escape_string($address)."',
                         '".$conn->real_escape_string($gender)."')");
    $newId=$conn->insert_id;

    $conn->query("
        INSERT INTO `order`(CustomerID, BookID, DatePurchase, Quantity, TotalPrice, Status)
        SELECT $newId, BookID, CURRENT_TIME, Quantity, TotalPrice, 'N' FROM cart
    ");
    $conn->query("DELETE FROM cart");

    $result=$conn->query("
        SELECT c.CustomerName,c.CustomerGender,c.CustomerAddress,c.CustomerEmail,c.CustomerPhone,
               b.BookTitle,b.Price,b.Image,o.DatePurchase,o.Quantity,o.TotalPrice
        FROM customer c
        JOIN `order` o ON o.CustomerID=c.CustomerID
        JOIN book b ON o.BookID=b.BookID
        WHERE o.Status='N' AND c.CustomerID=$newId
    ");

    echo '<table style="width:40%">';
    echo "<tr><th>Order Summary</th><th></th></tr>";

    if ($row=$result->fetch_assoc()) {
        echo "<tr><td>Name:</td><td>".$row['CustomerName']."</td></tr>";
        echo "<tr><td>E-mail:</td><td>".$row['CustomerEmail']."</td></tr>";
        echo "<tr><td>Mobile Number:</td><td>".$row['CustomerPhone']."</td></tr>";
        echo "<tr><td>Gender:</td><td>".$row['CustomerGender']."</td></tr>";
        echo "<tr><td>Address:</td><td>".$row['CustomerAddress']."</td></tr>";
        echo "<tr><td>Date:</td><td>".$row['DatePurchase']."</td></tr>";
        $result->data_seek(0);
    }

    $total=0;
    while ($row=$result->fetch_assoc()) {
        echo "<tr><td style='border-top:2px solid #ccc;'>";
        echo '<img src="'.$row["Image"].'" width="20%"></td><td style="border-top:2px solid #ccc;">';
        echo $row['BookTitle']."<br>₹".$row['Price']."<br>";
        echo "Quantity: ".$row['Quantity']."<br>";
        echo "</td></tr>";
        $total += $row['TotalPrice'];
    }
    echo "<tr><td style='background-color:#ccc;'></td>
          <td style='text-align:right;background-color:#ccc;'>Total Price: <b>₹".$total."</b></td></tr>";
    echo "</table>";

    $conn->query("UPDATE `order` SET Status='Y' WHERE CustomerID=$newId");
}
?>
</blockquote>
</body>
</html>
