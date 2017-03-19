# php-phone-number-parser
php regex generator for search phone in text and get info about it

Example:

<?php

require "vendor/autoload.php";
use bpteam\Parser\Phone\Phone;
$phone = new Phone('RU');

$text = 'Сдаются 1-,2- и 3-комнатные квартиры, на любой срок по часам .
Все удобства, кабельное ТВ, Wi-Fi, стиральная машина-автомат, посуда, 
постельное бельё. Командированным скидки. Отчетные 8 905 111-22-33 
документы предоставляем .';

$number = current($phone->find($text));
var_dump($number); //79051112233
