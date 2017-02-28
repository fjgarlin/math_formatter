<?php

namespace Drupal\math_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\math_formatter\Math\Parser;
use Drupal\math_formatter\Math\PostfixLexer;

/**
 * Plugin implementation of the 'math_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "math_formatter",
 *   label = @Translation("Math formatter"),
 *   field_types = {
 *     "text",
 *   }
 * )
 */
class MathFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // TODO: dependency injection?
    $lexer = new PostfixLexer();
    $parser = new Parser();

    foreach ($items as $delta => $item) {
      $string = strip_tags($item->value);
      $postfix = $lexer->transform($string);
      $result = $parser->evaluate($postfix);

      $elements[$delta] = ['#markup' => Html::escape($string . ' = ' . $result)];
    }

    return $elements;
  }

}
