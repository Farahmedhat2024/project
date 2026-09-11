<?php


//  1-// if / else  Check Age

$age = 20;

if ($age > 18) {
    echo "You are allowed";
} else {
    echo "You are under 18";
}

echo '<br>';

// 2- 2) Function 
function calculate($num1, $num2)
{
    echo "Sum = " . ($num1 + $num2) . "<br>";
    echo "Difference = " . ($num1 - $num2) . "<br>";
    echo "Multiplication = " . ($num1 * $num2) . "<br>";
    echo "Division = " . ($num1 / $num2) . "<br>";
}

calculate(20, 5);



// 3) Function with Array 

$numbers = array(1, 5, 4, 7);

function total($arr)
{
    $sum = 0;

    foreach ($arr as $number) {
        $sum += $number;
    }

    return $sum;
}

echo total($numbers);

echo '<br>';


// 4) Search داخل Array

$films = array("Fast", "Predestination", "Persuit", "Prestige");

$keyword = "avatar";

$result = "no";

foreach ($films as $film) {

    if ($film == $keyword) {
        $result = "yes";
        break;
    }
}

echo $result;


echo '<br>';


// 5) Bubble Sort Function


$tests = array(5, 4, 9, 3, 1, 7, 5, 8, 6);

function RouteBubble($arr)
{
    $length = count($arr);

    for ($i = 0; $i < $length; $i++) {

        for ($j = 0; $j < $length - $i - 1; $j++) {

            if ($arr[$j] > $arr[$j + 1]) {

                $temp = $arr[$j];

                $arr[$j] = $arr[$j + 1];

                $arr[$j + 1] = $temp;
            }
        }
    }

    return $arr;
}

$result = RouteBubble($tests);

foreach ($result as $number) {
    echo $number . " ";
}

echo '<br>';




// 6) Maximum Number


$tests = array(5, 4, 9, 3, 1, 7, 5, 8, 6);

$max = $tests[0];

foreach ($tests as $number) {

    if ($number > $max) {
        $max = $number;
    }
}

echo $max;


echo '<br>';


// 7) Counting


$films = array("avatar", "Prestige", "avatar", "Prestige");

$keyword = "avatar";

$count = 0;

foreach ($films as $film) {

    if ($film == $keyword) {
        $count++;
    }
}

echo $count;

echo '<br>';



// 8) Random Password Function

function RouteRandomPass($length)
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $password = "";

    for ($i = 0; $i < $length; $i++) {

        $index = random_int(0, strlen($characters) - 1);

        $password .= $characters[$index];
    }

    return $password;
}

echo RouteRandomPass(10);



echo '<br>';


// 9) Boolean

$tests = array(1, "tariq", 1.5, true, 7, 's', false);

foreach ($tests as $value) {

    if (is_bool($value)) {

        if ($value == true) {
            echo "Yes<br>";
        } else {
            echo "No<br>";
        }

    }
}


echo '<br>';



// 10) Sorting
  
////////////////////  for /////

$tests = array(6, 4, 9, 3, 12, 8, 7);

$length = count($tests);

for ($i = 0; $i < $length; $i++) {

    for ($j = 0; $j < $length - $i - 1; $j++) {

        if ($tests[$j] > $tests[$j + 1]) {

            $temp = $tests[$j];

            $tests[$j] = $tests[$j + 1];

            $tests[$j + 1] = $temp;
        }
    }
}

foreach ($tests as $number) {
    echo $number . " ";
}

///////////////// while //////////


$tests = array(6, 4, 9, 3, 12, 8, 7);

$length = count($tests);

$i = 0;

while ($i < $length) {

    $j = 0;

    while ($j < $length - $i - 1) {

        if ($tests[$j] > $tests[$j + 1]) {

            $temp = $tests[$j];

            $tests[$j] = $tests[$j + 1];

            $tests[$j + 1] = $temp;
        }

        $j++;
    }

    $i++;
}

foreach ($tests as $number) {
    echo $number . " ";
}


echo '<br>';

// 11) Same Values  Array 2

$arr1 = array('a', 'b', 'c', 'd');

$arr2 = array('c', 'd', 'e', 'f');

foreach ($arr1 as $value1) {

    foreach ($arr2 as $value2) {

        if ($value1 == $value2) {

            echo $value1 . " - ";
        }
    }
}


echo '<br>';


//  المسأله 12 (form "get", "post")



$result = "";
$error = "";

if (isset($_POST['submit'])) {

    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    
    if ($price == "" || $quantity == "") {

        $error = "Please enter all fields.";

   
    } elseif (!is_numeric($price) || !is_numeric($quantity)) {

        $error = "Please enter numbers only.";

  
    } elseif ($price < 0 || $quantity < 0) {

        $error = "Negative numbers are not allowed.";

    } else {

        
        $total = $price * $quantity;

        
        if ($total > 1000) {

            $discount = 15;

        } else {

            $discount = 10;
        }

       
        $discountValue = $total * ($discount / 100);

        
        $finalPrice = $total - $discountValue;

        $result = "
            Total Price: $total <br>
            Discount: $discount% <br>
            Discount Value: $discountValue <br>
            Final Price: $finalPrice
        ";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Discount</title>
</head>
<body>
    

<h2>E-commerce Discount</h2>

    <form method="POST">

        <label>Product Price</label>

        <br>

        <input type="text" name="price">

        <br><br>

        <label>Quantity</label>

        <br>

        <input type="text" name="quantity">

        <br><br>

        <button type="submit" name="submit">
            Calculate
        </button>

    </form>


    <?php if ($error != ""): ?>

        <p style="color:red;">
            <?php echo $error; ?>
        </p>

    <?php endif; ?>


    <?php if ($result != ""): ?>

        <p style="color:green;">
            <?php echo $result; ?>
        </p>

    <?php endif; ?>
    
</body>
</html>


