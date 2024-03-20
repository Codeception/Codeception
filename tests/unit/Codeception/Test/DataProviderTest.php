<?php

declare(strict_types=1);

use Codeception\Exception\InvalidTestException;
use Codeception\Test\DataProvider;
use Codeception\Test\Unit;
use data\data_provider\DataProviderReceivesActorTest;

class DataProviderTest extends Unit
{
    protected \CodeGuy $tester;

    public function testParsesAnnotationContainingMethodNameOnly()
    {
        $result = DataProvider::parseDataProviderAnnotation('getData', 'UnitTest', 'testMethod');
        $this->assertSame(['UnitTest', 'getData'], $result);
    }

    public function testParsesAnnotationContainingClassNameAndMethodName()
    {
        $result = DataProvider::parseDataProviderAnnotation('AnotherClass::getData', 'UnitTest', 'testMethod');
        $this->assertSame(['AnotherClass', 'getData'], $result);
    }

    public function testParsesAnnotationContainingNamespacedClassNameAndMethodName()
    {
        $result = DataProvider::parseDataProviderAnnotation('Namespace\AnotherClass::getData', 'UnitTest', 'testMethod');
        $this->assertSame(['Namespace\AnotherClass', 'getData'], $result);
    }

    public function testParseAnnotationThrowsExceptionIfAnnotationContainsTooManyDoubleColons()
    {
        $this->expectException(InvalidTestException::class);
        $this->expectExceptionMessage('Data provider "AnotherClass::bug::getData" specified for UnitTest::testMethod is invalid');
        DataProvider::parseDataProviderAnnotation('AnotherClass::bug::getData', 'UnitTest', 'testMethod');
    }

    public function testReturnsNullIfMethodHasNoDataProvider()
    {
        $method = new ReflectionMethod($this, __FUNCTION__);
        $this->assertNull(DataProvider::getDataForMethod($method));
    }

    public function testExecutesPublicStaticDataProviderInTheSameClass()
    {
        require_once codecept_data_dir('data_provider/PublicStaticDataProviderTest.php');
        $method = new ReflectionMethod(PublicStaticDataProviderTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            'foo' => ['foo', 2],
            'bar' => ['bar', 3],
            'not baz' => ['baz', 5],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testExecutesPublicDataProviderInTheSameClass()
    {
        require_once codecept_data_dir('data_provider/PublicDataProviderTest.php');
        $method = new ReflectionMethod(PublicDataProviderTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            'foo' => ['foo', 5],
            'bar' => ['bar', 6],
            'not baz' => ['baz', 7],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testExecutesPrivateDataProviderInTheSameClass()
    {
        require_once codecept_data_dir('data_provider/PrivateDataProviderTest.php');
        $method = new ReflectionMethod(PrivateDataProviderTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            ['foo', 5],
            ['bar', 6],
            ['baz', 7],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testExecutesDataProviderSpecifiedUsingAttribute()
    {
        require_once codecept_data_dir('data_provider/AttributeDataProviderTest.php');
        $method = new ReflectionMethod(AttributeDataProviderTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            'foo' => ['foo', 5],
            'bar' => ['bar', 6],
            'not baz' => ['baz', 7],
        ];

        $this->assertSame($expectedResult, $result);
    }



    public function testExecutesPrivateDataProviderInAnotherClass()
    {
        require_once codecept_data_dir('data_provider/DataProviderInAnotherClassTest.php');
        $method = new ReflectionMethod(DataProviderInAnotherClassTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            'foo' => ['foo', 5],
            'bar' => ['bar', 6],
            'not baz' => ['baz', 7],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testExecutesMultipleDataProviders()
    {
        require_once codecept_data_dir('data_provider/MultipleDataProviderTest.php');
        $method = new ReflectionMethod(MultipleDataProviderTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            'foo' => ['foo', 5],
            'bar' => ['bar', 6],
            'not baz' => ['baz', 7],
            'abc' => ['abc', 8],
            'def' => ['def', 9],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testSupportsExampleAnnotations()
    {
        require_once codecept_data_dir('data_provider/ExampleAnnotationTest.php');
        $method = new ReflectionMethod(ExampleAnnotationTest::class, 'testExample');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            ['foo', 5],
            ['bar', 6],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testSupportsExamplesAttribute()
    {
        require_once codecept_data_dir('data_provider/ExamplesAttributeTest.php');
        $method = new ReflectionMethod(ExamplesAttributeTest::class, 'testExample');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            ['foo', 7],
            ['bar', 8],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testCombinesExampleAndDataProviderAnnotations()
    {
        require_once codecept_data_dir('data_provider/CombinedAnnotationDataProviderTest.php');
        $method = new ReflectionMethod(CombinedAnnotationDataProviderTest::class, 'testCombined');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            ['foo1', 1],
            ['foo2', 2],
            'abc' => ['abc', 8],
            'def' => ['def', 9],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testCombinesExampleAndDataProviderAttributes()
    {
        require_once codecept_data_dir('data_provider/CombinedAttributeDataProviderTest.php');
        $method = new ReflectionMethod(CombinedAttributeDataProviderTest::class, 'testCombined');
        $result = DataProvider::getDataForMethod($method);

        $expectedResult = [
            ['xyz1', 2],
            ['xyz2', 3],
            'foo' => ['foo', 5],
            'bar' => ['bar', 6],
            'not baz' => ['baz', 7],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testExecutesPublicDataProviderInAnotherAbstractClass()
    {
        require_once codecept_data_dir('data_provider/AbstractDataProviderTest.php');
        $method = new ReflectionMethod(AbstractDataProviderTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method, new ReflectionClass(AbstractDataProviderTest::class));

        $expectedResult = [
            'foo' => ['foo'],
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testDataProviderReceivesActor()
    {
        require_once codecept_data_dir('data_provider/DataProviderReceivesActorTest.php');
        $method = new ReflectionMethod(DataProviderReceivesActorTest::class, 'testDataProvider');
        $result = DataProvider::getDataForMethod($method, I: $this->tester);

        $expectedResult = [
            'codeGuyMethod() exists'
        ];

        $this->assertSame($expectedResult, $result);
    }
}
