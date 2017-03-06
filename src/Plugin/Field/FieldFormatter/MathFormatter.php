<?php

namespace Drupal\math_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\math_formatter\Math\Parser;
use Drupal\math_formatter\Math\PostfixLexer;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class MathFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Lexer object.
   *
   * @var PostfixLexer
   */
  protected $lexer;

  /**
   * Parser object.
   *
   * @var Parser
   */
  protected $parser;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PostfixLexer $lexer, Parser $parser) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->lexer = $lexer;
    $this->parser = $parser;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $string = strip_tags($item->value);
      $postfix = $this->lexer->transform($string);
      $result = $this->parser->evaluate($postfix);

      $elements[$delta] = ['#markup' => Html::escape($string . ' = ' . $result)];
    }

    return $elements;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('math_formatter.lexer'),
      $container->get('math_formatter.parser')
    );
  }

}
