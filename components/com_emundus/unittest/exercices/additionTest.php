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
		$this->assertSame(3, $this->calculatrice->addition(2,2), "La fonction addition doit retourner la somme des deux paramètres");
		$this->assertSame(2, $this->calculatrice->addition(4,-2), "La fonction addition doit retourner la somme des deux paramètres");
		$this->assertSame(0, $this->calculatrice->addition("Test", 2), "Si l'un des deux paramètres n'est pas un entier, la fonction addition doit retourner 0");
	}

	public function testAdditionCorrigé()
	{
		$this->assertSame(4, $this->calculatrice->additionCorrigé(2,2), "La fonction addition doit retourner la somme des deux paramètres");
		$this->assertSame(2, $this->calculatrice->additionCorrigé(4,-2), "La fonction addition doit retourner la somme des deux paramètres");
		$this->assertSame(0, $this->calculatrice->additionCorrigé("Test", 2), "Si l'un des deux paramètres n'est pas un entier, la fonction addition doit retourner 0");
	}

}