<?php


$validates = [
 
    'first_name' => [
        'filters' => FILTER_VALIDATE_REGEXP,
        'my_options' => ['options' => ['regexp' => '/^[\p{L}]{2,20}$/u']],
        'error' => 'الاسم الأول لازم يكون من 2 إلى 20 حرف'
    ],
 
    'last_name' => [
        'filters' => FILTER_VALIDATE_REGEXP,
        'my_options' => ['options' => ['regexp' => '/^[\p{L}]{2,20}$/u']],
        'error' => 'الاسم الأخير لازم يكون من 2 إلى 20 حرف'
    ],
 
    'email' => [
        'filters' => FILTER_VALIDATE_EMAIL,
        'error' => 'البريد الإلكتروني غير صحيح'
    ],
 
    'password' => [
        'filters' => FILTER_VALIDATE_REGEXP,
        'my_options' => ['options' => ['regexp' => '/^.{6,30}$/']],
        'error' => 'الباسورد لازم يكون من 6 إلى 30 حرف'
    ],
 
];



 ?>