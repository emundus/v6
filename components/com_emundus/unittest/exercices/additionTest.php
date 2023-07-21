<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', true );
error_reporting(E_ALL);

include_once ( dirname(__DIR__). '/exercices/addition.php');

class additionTest extends TestCase
{

	private $calculatrice;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->calculatrice = new Calculatrice();
	}
	public function testFoo()
	{
		$foo = true;
		$this->assertSame(true, $foo);
	}


	public function testAddition()
	{
        // TODO: test addition
	}
}