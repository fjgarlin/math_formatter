<?php

namespace Drupal\math_formatter\Math;

/**
 * Parse an given mathematical expression.
 *
 * @package Drupal\math_formatter\Math
 */
class Parser {

  /**
   * Evaluates a given tokenized expression in postfix format.
   *
   * @param array $expression
   *   Postfix tokens to evaluate.
   *
   * @see https://github.com/aboyadzhiev/php-math-parser/blob/master/src/Math/Parser.php#L84
   *   Simplified version based on the above.
   *
   * @return float
   *   Result of the expression given.
   */
  public function evaluate(array $expression) {
    $stack = [];

    foreach ($expression as $token) {
      // At this point there are no parenthesis.
      $type = isset($token['type']) ? $token['type'] : NULL;
      $value = isset($token['value']) ? $token['value'] : NULL;

      if ($type == PostfixLexer::TOKEN_OPERAND) {
        array_push($stack, $value);
      }
      elseif ($type == PostfixLexer::TOKEN_OPERATOR) {
        switch ($value) {
          case '+':
            array_push($stack, array_pop($stack) + array_pop($stack));
            break;

          case '-':
            $n = array_pop($stack);
            array_push($stack, array_pop($stack) - $n);
            break;

          case '*':
            array_push($stack, array_pop($stack) * array_pop($stack));
            break;

          case '/':
            $n = array_pop($stack);
            array_push($stack, array_pop($stack) / $n);
            break;

          default:
            // TODO: Exception, watchdog, return NaN?
            break;

        }
      }
    }

    return count($stack) ? end($stack) : 'NaN';
  }

}
