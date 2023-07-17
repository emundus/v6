<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', true );
error_reporting(E_ALL);

include_once ( dirname(__DIR__). '/exercices/email.php');

class emailTest extends TestCase
{

	private $calculatrice;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
	}
	public function testFoo()
	{
		$foo = true;
		$this->assertSame(true, $foo);
	}


	public function testCorrectEmail()
	{
		// Il a eu un cas sur Paris 2 où les étudiants utilisait une adresse mail de type test.test@etudiant.gmail.com qui ne passait pas le check DNS et donc maintenant il veut vérifier seulement le domaine parent (paris-2.fr)
		$this->assertFalse(correctEmail('test.test@canexistepasdutout.gmail.com'), 'La fonction correctEmail doit retourner false si l\'adresse mail est invalide');
	}
}