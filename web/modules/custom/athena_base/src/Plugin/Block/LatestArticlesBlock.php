<?php

namespace Drupal\athena_base\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The the block to display latest articles.
 */
#[
  Block(
    id: 'athena_latest_articles',
    admin_label: new TranslatableMarkup('Athena: Latest articles'),
    category: new TranslatableMarkup('Athena'),
  )
]
class LatestArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The node storage controller.
   *
   * @var \Drupal\Core\Node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * Constructs a Drupal\athena_base\Plugin\Block\LatestArticlesBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Node\NodeStorageInterface $nodeStorage
   *   The node storage.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    NodeStorageInterface $nodeStorage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $nodeStorage;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $nids = $this->nodeStorage->getQuery()
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, $config['numbers_of_items'])
      ->accessCheck(FALSE)
      ->execute();
    $articles = $this->nodeStorage->loadMultiple($nids);

    return [
      '#theme' => 'athena_latest_articles_block',
      '#articles' => $articles,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['numbers_of_items'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of items'),
      '#min' => 1,
      '#max' => 50,
      '#step' => 1,
      '#default_value' => $this->configuration['numbers_of_items'],
      '#required' => TRUE,
    ];

    $form['cache_expiry_hours'] = [
      '#type' => 'number',
      '#title' => $this->t('Cache Expiry Interval (hours)'),
      '#description' => $this->t('Specify the interval (in hours) for cache expiry.'),
      '#default_value' => $this->configuration['cache_expiry_hours'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['numbers_of_items'] = $values['numbers_of_items'];
    $this->configuration['cache_expiry_hours'] = $values['cache_expiry_hours'];
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheMaxAge() {
    return $this->configuration['cache_expiry_hours'] * 60 * 60;
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'numbers_of_items' => 5,
      'cache_expiry_hours' => 24,
    ];
  }

}
