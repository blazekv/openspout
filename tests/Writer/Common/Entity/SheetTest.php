<?php

namespace OpenSpout\Writer\Common\Entity;

use OpenSpout\Common\Helper\StringHelper;
use OpenSpout\Writer\Common\Manager\SheetManager;
use OpenSpout\Writer\Exception\InvalidSheetNameException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SheetTest extends TestCase
{
    private SheetManager $sheetManager;

    protected function setUp(): void
    {
        $this->sheetManager = new SheetManager(new StringHelper());
    }

    public function testGetSheetName(): void
    {
        $sheets = [$this->createSheet(0, 'workbookId1'), $this->createSheet(1, 'workbookId1')];

        static::assertSame('Sheet1', $sheets[0]->getName(), 'Invalid name for the first sheet');
        static::assertSame('Sheet2', $sheets[1]->getName(), 'Invalid name for the second sheet');
    }

    public function testSetSheetNameShouldCreateSheetWithCustomName(): void
    {
        $customSheetName = 'CustomName';
        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);

        static::assertSame($customSheetName, $sheet->getName(), "The sheet name should have been changed to '{$customSheetName}'");
    }

    public function dataProviderForInvalidSheetNames(): array
    {
        return [
            [''],
            ['this title exceeds the 31 characters limit'],
            ['Illegal \\'],
            ['Illegal /'],
            ['Illegal ?'],
            ['Illegal *'],
            ['Illegal :'],
            ['Illegal ['],
            ['Illegal ]'],
            ['\'Illegal start'],
            ['Illegal end\''],
        ];
    }

    /**
     * @dataProvider dataProviderForInvalidSheetNames
     */
    public function testSetSheetNameShouldThrowOnInvalidName(string $customSheetName): void
    {
        $sheet = $this->createSheet(0, 'workbookId1');

        $this->expectException(InvalidSheetNameException::class);
        $sheet->setName($customSheetName);
    }

    public function testSetSheetNameShouldNotThrowWhenSettingSameNameAsCurrentOne(): void
    {
        $customSheetName = 'Sheet name';
        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);
        $sheet->setName($customSheetName);
        $this->expectNotToPerformAssertions();
    }

    public function testSetSheetNameShouldThrowWhenNameIsAlreadyUsed(): void
    {
        $this->expectException(InvalidSheetNameException::class);

        $customSheetName = 'Sheet name';

        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);

        $sheet = $this->createSheet(1, 'workbookId1');
        $sheet->setName($customSheetName);
    }

    public function testSetSheetNameShouldNotThrowWhenSameNameUsedInDifferentWorkbooks(): void
    {
        $customSheetName = 'Sheet name';

        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);

        $sheet = $this->createSheet(0, 'workbookId2');
        $sheet->setName($customSheetName);

        $sheet = $this->createSheet(1, 'workbookId3');
        $sheet->setName($customSheetName);
        $this->expectNotToPerformAssertions();
    }

    private function createSheet(int $sheetIndex, string $associatedWorkbookId): Sheet
    {
        return new Sheet($sheetIndex, $associatedWorkbookId, $this->sheetManager);
    }
}
