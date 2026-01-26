<?php
namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Model\Config\Backend\CategoryCommissions;

class CategoryCommissionsTest extends TestCase
{
    private CategoryCommissions $model;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->method('serialize')->willReturn('[]');
        $serializer->method('unserialize')->willReturn([]);

        $this->model = new CategoryCommissions(
            $context,
            $registry,
            $scopeConfig,
            $cacheTypeList,
            $serializer,
            null,
            null,
            []
        );
    }

    public function testBeforeSaveRejectsNonNumericCommission(): void
    {
        $this->model->setValue([
            'row_1' => ['category_id' => '12', 'commission_value' => 'abc'],
        ]);

        $this->expectException(ValidatorException::class);
        $this->model->beforeSave();
    }

    public function testBeforeSaveRejectsNegativeCommission(): void
    {
        $this->model->setValue([
            'row_1' => ['category_id' => '12', 'commission_value' => -1],
        ]);

        $this->expectException(ValidatorException::class);
        $this->model->beforeSave();
    }

    public function testBeforeSaveRejectsMissingCategory(): void
    {
        $this->model->setValue([
            'row_1' => ['category_id' => '', 'commission_value' => 10],
        ]);

        $this->expectException(ValidatorException::class);
        $this->model->beforeSave();
    }

    public function testBeforeSaveSkipsEmptyRow(): void
    {
        $this->model->setValue([
            '__empty' => ['category_id' => '', 'commission_value' => ''],
        ]);

        // Should not throw
        $this->model->beforeSave();
        $this->assertTrue(true);
    }

    public function testBeforeSaveAcceptsValidRows(): void
    {
        $this->model->setValue([
            'row_1' => ['category_id' => '12', 'commission_value' => 10],
            'row_2' => ['category_id' => '34', 'commission_value' => '25.5'],
        ]);

        // Should not throw
        $this->model->beforeSave();
        $this->assertTrue(true);
    }
}