<?php

declare(strict_types=1);

namespace Drupal\Tests\infrastructure_news\Kernel;

use Drupal\domain\Entity\Domain;
use Drupal\KernelTests\KernelTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

/**
 * Infrastructure news items are pinned to the ACCESS Support domain.
 *
 * The feed imports these on cron, outside any entity form. Domain 3.x only
 * defaults field_domain_access on entity forms, so without a fallback imported
 * items have no domain. See D8-2770.
 *
 * The field is required on the site, and Feeds validates each item before it
 * saves it. A default applied at presave passed a save-only test while every
 * real import failed validation, so the test field is required here and new
 * items are validated before they are saved, in the order Feeds uses.
 *
 * @group infrastructure_news
 */
class InfrastructureNewsDomainTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'domain',
    'infrastructure_news',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);

    NodeType::create([
      'type' => 'infrastructure_news',
      'name' => 'Infrastructure News',
    ])->save();

    // Read unconditionally by infrastructure_news_entity_presave().
    FieldStorageConfig::create([
      'field_name' => 'field_affected_resources_txt',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => -1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_affected_resources_txt',
      'entity_type' => 'node',
      'bundle' => 'infrastructure_news',
    ])->save();

    // domain_access itself is not enabled: only the stored reference is under
    // test, not the node access grants built from it.
    FieldStorageConfig::create([
      'field_name' => 'field_domain_access',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => ['target_type' => 'domain'],
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_domain_access',
      'entity_type' => 'node',
      'bundle' => 'infrastructure_news',
      'required' => TRUE,
    ])->save();

    // The reference is validated too, so the pinned domain has to exist.
    Domain::create([
      'id' => 'amp_cyberinfrastructure_org',
      'hostname' => 'support.access-ci.org',
      'name' => 'ACCESS Support',
    ])->save();
  }

  /**
   * Returns the validation messages for a node's field_domain_access.
   *
   * @return string[]
   *   The violation messages, keyed by property path.
   */
  protected function domainViolations(NodeInterface $node): array {
    $messages = [];
    foreach ($node->validate() as $violation) {
      if (str_starts_with($violation->getPropertyPath(), 'field_domain_access')) {
        $messages[$violation->getPropertyPath()] = (string) $violation->getMessage();
      }
    }
    return $messages;
  }

  /**
   * Returns the domain ids stored on a saved node, reloaded from storage.
   *
   * @return string[]
   *   The referenced domain ids.
   */
  protected function storedDomainIds(NodeInterface $node): array {
    $reloaded = Node::load($node->id());
    return array_column($reloaded->get('field_domain_access')->getValue(), 'target_id');
  }

  /**
   * A new item with no domain, as the feed creates it, gets ACCESS Support.
   */
  public function testNewItemWithoutDomainGetsAccessSupport(): void {
    $node = Node::create(['type' => 'infrastructure_news', 'title' => 'Outage']);
    $this->assertSame([], $this->domainViolations($node));
    $node->save();
    $this->assertSame(['amp_cyberinfrastructure_org'], $this->storedDomainIds($node));
  }

  /**
   * A domain set before the first save is kept.
   */
  public function testExplicitDomainKept(): void {
    $node = Node::create([
      'type' => 'infrastructure_news',
      'title' => 'Outage',
      'field_domain_access' => ['ccmnet_org'],
    ]);
    $node->save();
    $this->assertSame(['ccmnet_org'], $this->storedDomainIds($node));
  }

  /**
   * An existing item an editor has cleared is not re-pinned on update.
   */
  public function testExistingItemNotRepinned(): void {
    $node = Node::create(['type' => 'infrastructure_news', 'title' => 'Outage']);
    $node->save();
    $node->set('field_domain_access', []);
    $node->save();
    $this->assertSame([], $this->storedDomainIds($node));
  }

}
