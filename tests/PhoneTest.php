<?php
namespace bpteam\Parser\Phone;

use \PHPUnit_Framework_Testcase;
use \ReflectionClass;

class PhoneTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionMethod
     */
    protected static function getMethod($name, $className = 'bpteam\BigList\JsonList')
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionProperty
     */
    protected static function getProperty($name, $className = 'bpteam\BigList\JsonList')
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    public function testPhoneUA()
    {
        $phone = new Phone('UA');
        $text = 'Цена: 10 000 $, Площадь: 10 соток,
Продам земельну ділянку 10 соток під житлове будівництво.
Земельна ділянка рівна, прямокутна, поблизу дороги.
Поряд проходить газ (підземка), електрика (три фази), лінія "Фрінет". Ділянка знаходиться по вулиці ~~~~~ в 800 м від центру міста.
Можливий торг. 0991114140 http://olx.ua/xxx';
        $number = current($phone->find($text));
        $this->assertEquals('380991114140', $number);
    }

    public function testPhoneRU()
    {
        $phone = new Phone('RU');
        $text = 'Сдаются 1-,2- и 3-комнатные квартиры, на любой срок по часам .Все удобства, кабельное ТВ, Wi-Fi, стиральная машина-автомат, посуда, постельное бельё. Командированным скидки. Отчетные 8 905 111-30-80 документы предоставляем .';
        $number = current($phone->find($text));
        $this->assertEquals('79051113080', $number);
    }
}