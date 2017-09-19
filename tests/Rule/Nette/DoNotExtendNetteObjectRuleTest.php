<?php declare(strict_types = 1);

namespace PHPStan\Rule\Nette;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;

class DoNotExtendNetteObjectRuleTest extends \PHPUnit\Framework\TestCase
{

	public function testSmartObjectChild()
	{
		$classReflection = $this->createMock(ClassReflection::class);
		$classReflection->method('getParentClass')->willReturn(null);

		$broker = $this->createMock(Broker::class);
		$broker->method('hasClass')->willReturn(true);
		$broker->method('getClass')->willReturn($classReflection);

		$scope = $this->createMock(Scope::class);
		$node = $this->createMock(Node::class);
		$node->namespacedName = 'PHPStan\Tests\SmartObjectChild';

		$rule = new DoNotExtendNetteObjectRule($broker);
		$result = $rule->processNode($node, $scope);

		$this->assertEmpty($result);
	}

	public function testNetteObjectChild()
	{
		if (PHP_VERSION_ID >= 70200) {
			$this->markTestSkipped('PHP 7.2 is incompatible with Nette\Object.');
		}

		$parentClassReflection = $this->createMock(ClassReflection::class);
		$parentClassReflection->method('getName')->willReturn('Nette\Object');

		$classReflection = $this->createMock(ClassReflection::class);
		$classReflection->method('getParentClass')->willReturn($parentClassReflection);

		$broker = $this->createMock(Broker::class);
		$broker->method('hasClass')->willReturn(true);
		$broker->method('getClass')->willReturn($classReflection);

		$scope = $this->createMock(Scope::class);
		$node = $this->createMock(Node::class);
		$node->namespacedName = 'PHPStan\Tests\NetteObjectChild';

		$rule = new DoNotExtendNetteObjectRule($broker);
		$result = $rule->processNode($node, $scope);

		$this->assertSame(['Class PHPStan\Tests\NetteObjectChild extends Nette\Object - it\'s better to use Nette\SmartObject trait.'], $result);
	}

}
