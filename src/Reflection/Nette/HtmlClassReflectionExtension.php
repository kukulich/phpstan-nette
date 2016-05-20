<?php declare(strict_types=1);

namespace PHPStan\Reflection\Nette;

use Nette\Utils\Html;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class HtmlClassReflectionExtension implements MethodsClassReflectionExtension, PropertiesClassReflectionExtension
{

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		return $classReflection->getName() === Html::class || $classReflection->isSubclassOf(Html::class);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		return new HtmlMethodReflection($methodName, $classReflection);
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getName() === Html::class || $classReflection->isSubclassOf(Html::class);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return new HtmlPropertyReflection($classReflection);
	}


}
