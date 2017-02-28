<?php

namespace Drupal\math_formatter\Math;

/**
 * Take an infix math string and transform it into a postfix expression.
 *
 * @package Drupal\math_formatter\Math
 */
class PostfixLexer {

  /**
   * Operand.
   *
   * @const string
   */
  const TOKEN_OPERAND = 'operand';

  /**
   * Operator.
   *
   * @const string
   */
  const TOKEN_OPERATOR = 'operator';

  /**
   * Parenthesis.
   *
   * @const string
   */
  const TOKEN_PARENTHESIS = 'parenthesis';

  /**
   * Parenthesis left position.
   *
   * @const string
   */
  const PARENTHESIS_LEFT = 'left';

  /**
   * Parenthesis right position.
   *
   * @const string
   */
  const PARENTHESIS_RIGHT = 'right';

  /**
   * Input variable to transform.
   *
   * @var string
   *  Input variable.
   */
  protected $string;

  /**
   * Array containing all the tokens.
   *
   * @var array
   *   Tokens array.
   */
  protected $tokens;

  /**
   * Operators to consider.
   *
   * @var array
   *   Key is the operator, Value is the priority.
   *   List of operators to parse.
   */
  protected $operators = [
    '+' => 0,
    '-' => 0,
    '*' => 1,
    '/' => 1,
  ];

  /**
   * Transform given string into postfix notation.
   *
   * @param string $string
   *   Input value to transform.
   *
   * @return array
   *   Sanitized postfix expression.
   */
  public function transform(string $string) {
    $this->string = trim($string);

    $this->tokenize();
    $this->postfix();

    return (array) $this->tokens;
  }

  /**
   * Tokenize the string that was given.
   */
  protected function tokenize() {
    $this->tokens = [];

    // TODO: hook Sebastian method here with more sophisticathed regex?
    $tokens = (array) explode(' ', $this->string);

    // kint($tokens);die;
    foreach ($tokens as $t) {
      $token = NULL;

      if (array_key_exists($t, $this->operators)) {
        $token = [
          'type' => self::TOKEN_OPERATOR,
          'value' => (string) $t,
          'priority' => $this->operators[$t],
        ];
      }
      elseif (is_numeric($t)) {
        $token = [
          'type' => self::TOKEN_OPERAND,
          'value' => (float) $t,
        ];
      }
      elseif ('(' === $t) {
        $token = [
          'type' => self::TOKEN_PARENTHESIS,
          'position' => self::PARENTHESIS_LEFT,
        ];
      }
      elseif (')' === $t) {
        $token = [
          'type' => self::TOKEN_PARENTHESIS,
          'position' => self::PARENTHESIS_RIGHT,
        ];
      }

      if (!is_null($token)) {
        $this->tokens[] = $token;
      }
    }

    return $this->tokens;
  }

  /**
   * Reorder the tokens in a postfix notation.
   *
   * @see https://en.wikipedia.org/wiki/Shunting-yard_algorithm
   *   The solution intends to follow the steps shown in the above link.
   */
  protected function postfix() {
    $original = $this->tokens;
    $postfix = [];
    $stack = [];

    while (count($original) > 0) {
      // Current token.
      $t = array_shift($original);
      $type = $t['type'];

      if ($type == 'operand') {
        // Send operand to output.
        array_push($postfix, $t);
      }
      elseif ($type == 'operator') {
        $priority = isset($t['priority']) ? $t['priority'] : 0;
        // Pop operators from stack to output.
        while (($stack_operator = end($stack)) and
            $stack_operator['type'] != 'parenthesis' and
            $priority <= $stack_operator['priority']) {
          array_push($postfix, array_pop($stack));
        }
        // And include our operator in the stack.
        array_push($stack, $t);
      }
      elseif ($type == 'parenthesis' and $t['position'] == 'left') {
        // Push parenthesis to stack.
        array_push($stack, $t);
      }
      elseif ($type == 'parenthesis' and $t['position'] == 'right') {
        // Add operators to the output.
        while (($stack_operator = end($stack)) and
            $stack_operator['type'] != 'parenthesis') {
          array_push($postfix, array_pop($stack));
        }
        // kint($stack);die;
        // Remove parenthesis from stack.
        $stack_operator = end($stack);
        if ($stack_operator['type'] == 'parenthesis') {
          array_pop($stack);
        }
        else {
          // Error, mismatched parenthesis.
          $original = [];
          $postfix = [];
        }
      }
    }

    if (!empty($stack)) {
      $error = FALSE;
      while (($item = array_pop($stack)) and !$error) {
        if ($item['type'] == 'parenthesis') {
          $error = TRUE;
        }
        else {
          array_push($postfix, $item);
        }
      }

      if ($error) {
        // Error, clean the array as it's not a valid expression.
        $postfix = [];
      }
    }
    // kint($postfix);die;
    // Assign newly ordered postfix tokens into tokens property.
    $this->tokens = $postfix;
  }

}
