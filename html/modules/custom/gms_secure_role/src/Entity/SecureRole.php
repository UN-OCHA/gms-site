<?php

namespace Drupal\gms_secure_role\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the SecureRole entity.
 *
 * @ingroup securerole
 *
 * @ContentEntityType(
 *   id = "securerole",
 *   label = @Translation("Secure Role requests"),
 *   base_table = "securerole",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\gms_secure_role\Entity\Controller\SecureRoleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\securerole\Form\SecureRoleForm",
 *       "edit" = "Drupal\securerole\Form\SecureRoleForm",
 *       "delete" = "Drupal\securerole\Form\SecureRoleDeleteForm",
 *     },
 *   },
 *   list_cache_contexts = { "content" },
 *   entity_keys = {
 *     "id" = "id",
 *     "user_group" = "user_group",
 *     "pool_fund" = "pool_fund",
 *     "comments" = "comments",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/gms_secure_role/{SecureRole}",
 *     "edit-form" = "/gms_secure_role/{SecureRole}/edit",
 *     "delete-form" = "/gms_secure_role/{SecureRole}/delete",
 *     "collection" = "/gms_secure_role/list"
 *   },
 *   field_ui_base_route = "entity.securerole.securerole_settings",
 * )
 */

class SecureRole extends ContentEntityBase implements ContentEntityInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Secure Role entity.'))
      ->setReadOnly(TRUE);

    $fields['user_group'] = BaseFieldDefinition::create('string')
      ->setLabel(t('User Group'))
      ->setDescription(t('The type of User Group.'))
     // ->setDefaultValue('node')
      ->setSettings([
        'max_length' => 64,
      ])
      ->setRequired(TRUE);

    $fields['pool_fund'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Pool Fund'))
      ->setDescription(t('The type of Pool Fund.'))
      // ->setDefaultValue('node')
      ->setSettings([
        'max_length' => 128,
      ])
      ->setRequired(TRUE);

    // The text of the contact message.
    $fields['comments'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Comment'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 0,
        'settings' => [
          'rows' => 4,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'string',
        'weight' => 0,
        'label' => 'above',
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Secure role entity.'))
      ->setReadOnly(TRUE);

    return $fields;
  }
}
