<?php

namespace PhpSchool\CliMenuTest;

use PhpSchool\CliMenu\CliMenuBuilder;
use PhpSchool\CliMenu\Exception\InvalidInstantiationException;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Util\ColourUtil;
use PhpSchool\Terminal\Terminal;
use PhpSchool\Terminal\UnixTerminal;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class MenuStyleTest extends TestCase
{
    private function getMenuStyle(int $colours = 8) : MenuStyle
    {
        // Use the CliMenuBuilder & reflection to get the style Obj
        $builder = new CliMenuBuilder();
        $menu    = $builder->build();

        $reflectionMenu = new \ReflectionObject($menu);
        $styleProperty  = $reflectionMenu->getProperty('style');
        $styleProperty->setAccessible(true);
        $style = $styleProperty->getValue($menu);

        $reflectionStyle  = new \ReflectionObject($style);
        $terminalProperty = $reflectionStyle->getProperty('terminal');
        $terminalProperty->setAccessible(true);
        $terminalProperty->setValue($style, $this->getMockTerminal($colours));

        // Force recalculate terminal widths now terminal is set
        $style->setWidth(100);
        
        return $style;
    }

    private function getMockTerminal(int $colours = 8) : MockObject
    {
        $terminal = $this
            ->getMockBuilder(UnixTerminal::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWidth', 'getColourSupport'])
            ->getMock();

        $terminal
            ->expects(self::any())
            ->method('getWidth')
            ->will(self::returnValue(500));

        $terminal
            ->expects(self::any())
            ->method('getColourSupport')
            ->will(self::returnValue($colours));

        return $terminal;
    }

    public function testMenuStyleCanBeInstantiatedByCliMenuBuilder() : void
    {
        $builder = new CliMenuBuilder();
        $menu    = $builder->build();

        $reflectionMenu = new \ReflectionObject($menu);
        self::assertTrue($reflectionMenu->hasProperty('style'));

        $styleProperty = $reflectionMenu->getProperty('style');
        $styleProperty->setAccessible(true);
        $style = $styleProperty->getValue($menu);

        self::assertSame(MenuStyle::class, get_class($style));
    }

    public function testGetColoursSetCode() : void
    {
        self::assertSame("\e[37;44m", $this->getMenuStyle()->getColoursSetCode());
    }

    public function testGetColoursResetCode() : void
    {
        self::assertSame("\e[0m", $this->getMenuStyle()->getColoursResetCode());
    }

    public function testGetInvertedColoursSetCode() : void
    {
        self::assertSame("\e[7m", $this->getMenuStyle()->getInvertedColoursSetCode());
    }

    public function testGetInvertedColoursUnsetCode() : void
    {
        self::assertSame("\e[27m", $this->getMenuStyle()->getInvertedColoursUnsetCode());
    }

    public function testGetterAndSetters() : void
    {
        $style = $this->getMenuStyle();

        self::assertSame('blue', $style->getBg());
        self::assertSame('white', $style->getFg());
        self::assertSame('○', $style->getUnselectedMarker());
        self::assertSame('●', $style->getSelectedMarker());
        self::assertSame('✔', $style->getItemExtra());
        self::assertFalse($style->getDisplaysExtra());
        self::assertSame('=', $style->getTitleSeparator());
        self::assertSame(100, $style->getWidth());
        self::assertSame(2, $style->getMargin());
        self::assertSame(2, $style->getPadding());
        self::assertSame(0, $style->getBorderTopWidth());
        self::assertSame(0, $style->getBorderRightWidth());
        self::assertSame(0, $style->getBorderBottomWidth());
        self::assertSame(0, $style->getBorderLeftWidth());
        self::assertSame('white', $style->getBorderColour());

        $style->setBg('red');
        $style->setFg('yellow');
        $style->setUnselectedMarker('-');
        $style->setSelectedMarker('>');
        $style->setItemExtra('EXTRA!');
        $style->setDisplaysExtra(true);
        $style->setTitleSeparator('+');
        $style->setWidth(200);
        $style->setMargin(10);
        $style->setPadding(10);
        $style->setBorderTopWidth(1);
        $style->setBorderRightWidth(2);
        $style->setBorderBottomWidth(3);
        $style->setBorderLeftWidth(4);
        $style->setBorderColour('green');

        self::assertSame('red', $style->getBg());
        self::assertSame('yellow', $style->getFg());
        self::assertSame('-', $style->getUnselectedMarker());
        self::assertSame('>', $style->getSelectedMarker());
        self::assertSame('EXTRA!', $style->getItemExtra());
        self::assertTrue($style->getDisplaysExtra());
        self::assertSame('+', $style->getTitleSeparator());
        self::assertSame(200, $style->getWidth());
        self::assertSame(10, $style->getMargin());
        self::assertSame(10, $style->getPadding());
        self::assertSame(1, $style->getBorderTopWidth());
        self::assertSame(2, $style->getBorderRightWidth());
        self::assertSame(3, $style->getBorderBottomWidth());
        self::assertSame(4, $style->getBorderLeftWidth());
        self::assertSame('green', $style->getBorderColour());
    }
    
    public function testSetBorderShorthandFunction() : void
    {
        $style = $this->getMenuStyle();
        $style->setBorder(3);
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(3, $style->getBorderRightWidth());
        self::assertSame(3, $style->getBorderBottomWidth());
        self::assertSame(3, $style->getBorderLeftWidth());
        self::assertSame('white', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 4);
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(4, $style->getBorderRightWidth());
        self::assertSame(3, $style->getBorderBottomWidth());
        self::assertSame(4, $style->getBorderLeftWidth());
        self::assertSame('white', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 4, 5);
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(4, $style->getBorderRightWidth());
        self::assertSame(5, $style->getBorderBottomWidth());
        self::assertSame(4, $style->getBorderLeftWidth());
        self::assertSame('white', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 4, 5, 6);
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(4, $style->getBorderRightWidth());
        self::assertSame(5, $style->getBorderBottomWidth());
        self::assertSame(6, $style->getBorderLeftWidth());
        self::assertSame('white', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 4, 5, 6, 'red');
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(4, $style->getBorderRightWidth());
        self::assertSame(5, $style->getBorderBottomWidth());
        self::assertSame(6, $style->getBorderLeftWidth());
        self::assertSame('red', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 4, 5, 'red');
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(4, $style->getBorderRightWidth());
        self::assertSame(5, $style->getBorderBottomWidth());
        self::assertSame(4, $style->getBorderLeftWidth());
        self::assertSame('red', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 4, 'red');
        self::assertSame(3, $style->getBorderTopWidth());
        self::assertSame(4, $style->getBorderRightWidth());
        self::assertSame(3, $style->getBorderBottomWidth());
        self::assertSame(4, $style->getBorderLeftWidth());
        self::assertSame('red', $style->getBorderColour());

        $style = $this->getMenuStyle();
        $style->setBorder(3, 'red');
        static::assertSame(3, $style->getBorderTopWidth());
        static::assertSame(3, $style->getBorderRightWidth());
        static::assertSame(3, $style->getBorderBottomWidth());
        static::assertSame(3, $style->getBorderLeftWidth());
        static::assertSame('red', $style->getBorderColour());
    }

    public function test256ColoursCodes() : void
    {
        $style = $this->getMenuStyle(256);
        $style->setBg(16, 'white');
        $style->setFg(206, 'red');
        static::assertSame('16', $style->getBg());
        static::assertSame('206', $style->getFg());
        static::assertSame("\033[38;5;206;48;5;16m", $style->getColoursSetCode());
        
        $style = $this->getMenuStyle(8);
        $style->setBg(16, 'white');
        $style->setFg(206, 'red');
        static::assertSame('white', $style->getBg());
        static::assertSame('red', $style->getFg());
        static::assertSame("\033[31;47m", $style->getColoursSetCode());
    }

    public function testSetFgThrowsExceptionWhenColourCodeIsNotInRange() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid colour code');

        $style = $this->getMenuStyle(256);
        $style->setFg(512, 'white');
    }

    public function testSetBgThrowsExceptionWhenColourCodeIsNotInRange() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid colour code');

        $style = $this->getMenuStyle(256);
        $style->setBg(257, 'white');
    }

    public function testGetMarkerReturnsTheCorrectMarkers() : void
    {
        $style = $this->getMenuStyle();

        $style->setSelectedMarker('>');
        $style->setUnselectedMarker('x');

        static::assertSame('>', $style->getMarker(true));
        static::assertSame('x', $style->getMarker(false));
    }

    public function testWidthCalculation() : void
    {
        $style = $this->getMenuStyle();
        $style->setPadding(0);
        $style->setMargin(0);
        $style->setBorder(0);


        $style->setWidth(300);
        static::assertSame(300, $style->getContentWidth());

        $style->setPadding(5);
        static::assertSame(290, $style->getContentWidth());

        $style->setMargin(5);
        static::assertSame(290, $style->getContentWidth());

        $style->setBorder(2);
        static::assertSame(286, $style->getContentWidth());
    }

    public function testRightHandPaddingCalculation() : void
    {
        $style = $this->getMenuStyle();
        $style->setPadding(0);
        $style->setMargin(0);
        $style->setBorder(0);

        $style->setWidth(300);
        static::assertSame(250, $style->getRightHandPadding(50));

        $style->setPadding(5);
        static::assertSame(245, $style->getRightHandPadding(50));

        $style->setMargin(5);
        static::assertSame(245, $style->getRightHandPadding(50));

        $style->setBorder(2);
        static::assertSame(241, $style->getRightHandPadding(50));
    }

    public function testRightHandPaddingReturnsZeroWhenContentLengthTooLong() : void
    {
        $style = $this->getMenuStyle();
        $style->setPadding(0);
        $style->setMargin(0);
        $style->setBorder(0);

        $style->setWidth(100);
        
        self::assertEquals(0, $style->getRightHandPadding(100));
        self::assertEquals(0, $style->getRightHandPadding(150));
    }

    public function testRightHandPaddingReturnsZeroWhenContentLengthTooLongBecauseOfBorder() : void
    {
        $style = $this->getMenuStyle();
        $style->setPadding(10);
        $style->setMargin(0);
        $style->setBorder(10);

        $style->setWidth(100);

        self::assertEquals(11, $style->getRightHandPadding(59));
        self::assertEquals(10, $style->getRightHandPadding(60));
        self::assertEquals(0, $style->getRightHandPadding(70));
        self::assertEquals(0, $style->getRightHandPadding(71));
        self::assertEquals(0, $style->getRightHandPadding(100));
    }

    public function testMargin() : void
    {
        $style = $this->getMenuStyle();

        $style->setWidth(300);
        $style->setPadding(5);
        $style->setMargin(5);

        self::assertSame(5, $style->getMargin());
    }
    
    public function testMarginAutoCenters() : void
    {
        $style = $this->getMenuStyle();

        $style->setWidth(300);
        $style->setPadding(5);
        $style->setMarginAuto();

        self::assertSame(100, $style->getMargin());
        self::assertSame(290, $style->getContentWidth());
    }

    public function testModifyWithWhenMarginAutoIsEnabledRecalculatesMargin() : void
    {
        $style = $this->getMenuStyle();

        $style->setWidth(300);
        $style->setPadding(5);
        $style->setMarginAuto();

        self::assertSame(100, $style->getMargin());
        self::assertSame(290, $style->getContentWidth());
        
        $style->setWidth(400);

        self::assertSame(50, $style->getMargin());
        self::assertSame(390, $style->getContentWidth());
    }
}
