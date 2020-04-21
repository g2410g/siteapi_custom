<?php

namespace Drupal\siteapi_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Access\AccessResult;

/**
 * Returns responses for siteapi_custom module routes.
 */
class JsonNodeController extends ControllerBase {
  /**
   * The Serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   *  Constructor.
   *
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The Serializer service.
   */
  public function __construct(Serializer $serializer) {
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('serializer'));
  }

  /**
   * Builds the node representation JSON page.
   *
   * @param $site_api_key
   *   Site API Key.
   * @param $nid
   *   Node nid.
   *
   * @return JsonResponse Array of page elements to render.
   */
  public function jsonnode($site_api_key, $nid) {
    if ($this->nodeexists($nid)) {
      $node = Node::load($nid);
      $data = $this->serializer->serialize($node, 'json', ['plugin_id' => 'entity']);

      return new JsonResponse([
        'data' => $data,
      ]);
    }
  }

  /**
   * Access to node representation JSON page.
   *
   * @param $site_api_key
   *   Site API Key.
   * @param $nid
   *   Node nid.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden Array of page elements to render.
   */
  public function jsonnodeaccess($site_api_key, $nid) {
    if ($this->nodeexists($nid)) {
      $node = Node::load($nid);
      if ($site_api_key == \Drupal::config('system.site')->get('siteapikey')
        && $node->bundle() == 'page') {
        return AccessResult::allowed();
      }
    }
      return AccessResult::forbidden();
  }

  /**
   * Check whether node exists or not.
   *
   * @param $nid
   *   Node nid.
   *
   * @return bool
   */
  public function nodeexists($nid) {
    $values = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();
    if (!empty($values)) {
      return TRUE;
    }
    return FALSE;
  }

}
