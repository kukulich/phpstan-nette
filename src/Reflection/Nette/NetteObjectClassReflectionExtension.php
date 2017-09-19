<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Nette;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class NetteObjectClassReflectionExtension implements MethodsClassReflectionExtension, PropertiesClassReflectionExtension
{

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if (!$this->inheritsFromNetteObject($classReflection)) {
			return false;
		}

		if (substr($propertyName, 0, 2) === 'on' && strlen($propertyName) > 2) {
			return false; // prevent infinite loop from hasMethod
		}

		$getterMethod = $this->getMethodByProperty($classReflection, $propertyName);
		if ($getterMethod === null) {
			return false;
		}
		if ($getterMethod->isStatic()) {
			return false;
		}

		return $getterMethod->isPublic();
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param string $propertyName
	 * @return \PHPStan\Reflection\MethodReflection|null
	 */
	private function getMethodByProperty(ClassReflection $classReflection, string $propertyName)
	{
		$getterMethodName = sprintf('get%s', ucfirst($propertyName));
		if (!$classReflection->hasExtendedMethod($getterMethodName)) {
			return null;
		}

		return $classReflection->getExtendedMethod($getterMethodName);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		/** @var \PHPStan\Reflection\MethodReflection $getterMethod */
		$getterMethod = $this->getMethodByProperty($classReflection, $propertyName);
		return new NetteObjectPropertyReflection($classReflection, $getterMethod->getReturnType());
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		$traitNames = $classReflection->getTraitNames();
		if (!in_array(\Nette\SmartObject::class, $traitNames, true) && !$this->inheritsFromNetteObject($classReflection)) {
			return false;
		}

		if (substr($methodName, 0, 2) !== 'on' || strlen($methodName) <= 2) {
			return false;
		}

		return $classReflection->hasExtendedProperty($methodName) && $classReflection->getExtendedProperty($methodName)->isPublic();
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		return new NetteObjectEventListenerMethodReflection($methodName, $classReflection);
	}

	private function inheritsFromNetteObject(ClassReflection $classReflection): bool
	{
		while (($class = $classReflection->getParentClass()) !== null) {
			if ($class->getName() === 'Nette\Object') {
				return true;
			}
		}

		return false;
	}

}
