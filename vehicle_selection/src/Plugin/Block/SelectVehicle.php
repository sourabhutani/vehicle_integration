<?php

namespace Drupal\vehicle_selection\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Vehicle selection' Block.
 *
 * @Block(
 *   id = "vehicle_selection_custom_block",
 *   admin_label = @Translation("Select Vehicle Block"),
 * )
 */
class SelectVehicle extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
      // return [
      //   '#type' => 'markup',
      //   'markup' => 'This is a custom block'
      // ];
      $form = \Drupal::formBuilder()->getForm('Drupal\vehicle_selection\Form\SelectVehicleForm'); 
      return $form;
  }

}
