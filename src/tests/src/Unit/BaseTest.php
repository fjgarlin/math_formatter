<?php

namespace Drupal\Tests\math_formatter\Unit;

use Drupal\math_formatter\Math\Parser;
use Drupal\math_formatter\Math\PostfixLexer;
use Drupal\Tests\UnitTestCase;

/**
 * Class BaseTests.
 *
 * @package Drupal\Tests\math_formatter\Unit
 */
class BaseTest extends UnitTestCase {

  /**
   * Base tests for lexer.
   *
   * @param string $input
   *   Input to test.
   * @param string $result
   *   Data to test.
   *
   * @dataProvider lexerData
   */
  public function testLexerParser($input, $result) {
    $lexer = new PostfixLexer();
    $parser = new Parser();

    $this->assertEquals($parser->evaluate($lexer->transform($input)), $result);
  }

  /**
   * Provides the test data for the lexer.
   *
   * @return array
   *   Data to pass to the test
   */
  public static function lexerData() {
    return [
      ['3 * ( 5 + 3 + 5 ) * 6 ', '234'],
      ['3 * 6 ', '18'],
      ['3*6 ', '18'],
      ['3*6+9+1*2 ', '29'],
      ['3    *   6   ', '18'],
      ['*3*6 ', 'NaN'],
      ['3*6* ', 'NaN'],
      ['Hello', 'NaN'],
    ];
  }

}
