<?php
/**
 * @file
 * Contains \Drupal\vehicle_selection\Form\SelectVehicleForm.
 */
namespace Drupal\vehicle_selection\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\views\Views;

/**
 *
 * @see \Drupal\Core\Form\FormBase
 */
class SelectVehicleForm extends FormBase {

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */

  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['year'] = array(
    '#type' => 'select',
    '#options' => $this->getYears(1995),
    '#required' => true,
    // '#title' => t('Year'),
      '#ajax' => [
        'callback' => '::updateMake',
        'wrapper' => 'make-wrapper',
      ],
    );


    //make elemenet
    $form['make_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'make-wrapper'],
    ];

    $get_year = $form_state->getValue('year');
    $veh_make_data = [];
    if(!empty($get_year)){
      $veh_make_data = $this->getVehicleMakes();
    }
    
    $form['make_wrapper']['vehicle_make'] = [
        '#type' => 'select',
        // '#title' => $this->t('Make'),
        '#options' => $veh_make_data,
        '#required' => true,
        '#empty_option' => $this->t('- Select make -'),
        '#ajax' => [
          'callback' => '::updateVehicleModel',
          'wrapper' => 'model-wrapper',
         ],
    ];

      // model element
      $form['model_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'model-wrapper'],
      ];

      $vehicle_make = $form_state->getValue('vehicle_make');
      $veh_models_data = [];

      if(!empty($get_year) && !empty($vehicle_make)){
        $veh_models_data = $this->getVehicleModel($get_year, $vehicle_make);
      }
      
      $form['model_wrapper']['vehicle_model'] = [
        '#type' => 'select',
        // '#title' => $this->t('Model'),
        '#empty_option' => $this->t('- Select model -'),
        '#options' => $veh_models_data,
        '#required' => true,
        '#ajax' => [
          'callback' => '::updateVehicleEngine',
          'wrapper' => 'engine-wrapper',
         ],
      ];
    
      // engine element
      $form['engine_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'engine-wrapper'],
      ];

      $veh_engine_data = [];
      $form['engine_wrapper']['vehicle_engine'] = [
        '#type' => 'select',
        // '#title' => $this->t('Engine'),
        '#empty_option' => $this->t('- Select engine -'),
        '#options' => $veh_engine_data,
        '#required' => true,
      ];

  
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'select_vehicle_application_form';
  }


  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

    /**
   * Ajax callback for vehicle make.
   */
  public function updateMake(array $form, FormStateInterface $form_state) {
    return $form['make_wrapper'];
  }

      /**
   * Ajax callback for vehicle model.
   */
  public function updateVehicleModel(array $form, FormStateInterface $form_state) {
    return $form['model_wrapper'];
  }


  /**
   * Ajax callback for vehicle engine.
   */
  public function updateVehicleEngine(array $form, FormStateInterface $form_state) {
    return $form['engine_wrapper'];
  }

 /**
   * get years.
   * @param $start
   * @param $end
   */
  public function getYears($end){
    $start = date('Y');
    $years = [];
    for($y=$start; $y>=$end; $y--){
      $years[$y] = $y;
    }
    return $years;
  }

   /**
   * getVehicleMakes is used to fetch all makes from an external api
   */
  public function getVehicleMakes(){
    $output = [];
    $response = \Drupal::httpClient()
    ->get('https://vpic.nhtsa.dot.gov/api//vehicles/GetAllMakes?format=json');
    
    if($response->getStatusCode() == 200){
      $response_body = $response->getBody();
      $content = json_decode($response_body->getContents(), true);
      foreach($content['Results'] as $k=>$v){
        $output[$k][$v['Make_Name']] = $v['Make_Name'];
      }
      $output = array_slice($output,0,100); // data is more so getting top 100 for now. Couldn't find any filter for api via year.
      return $output;
    } else {
      \Drupal::logger('vehicle_selection')->error('Unable to get vehicle make data');
      return $output;
    }

  }


     /**
   * getVehicleModel is used to fetch all model from an external api
   */
  public function getVehicleModel($year, $make){
    $output = [];
    $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeYear/make/'.$make.'/modelyear/'.$year.'?format=json';
    $response = \Drupal::httpClient()->get($url);
    if($response->getStatusCode() == 200){
      $response_body = $response->getBody();
      $content = json_decode($response_body->getContents(), true);
      foreach($content['Results'] as $k=>$v){
        $output[$k] = $v['Model_Name'];
      }
      return $output;
    } else {
      \Drupal::logger('vehicle_selection')->error('Unable to get vehicle model data');
      return $output;
    }

  }


}

