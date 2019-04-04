<?php
namespace Drupal\taxonomy_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ImportForm
 * @package Drupal\taxonomy_import\Form
 * @ingroup taxonomy_import
 */
class ImportForm extends FormBase {
  public function getFormId() {
    return 'taxonomy_import_settings';
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('taxonomy_name');
    $vid = $form_state->getValue('machine_name');
    $desc = $form_state->getValue('description');
    $path = ImportForm::getFilePath($form_state->getValue('filename'));
    $vocabs = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    if (!isset($vocabs[$vid]) && !is_null($path)) {
      $vocab = \Drupal\taxonomy\Entity\Vocabulary::create(array(
        'vid' => $vid,
        'description' => $desc,
        'name' => $name,
      ));
      $vocab->save();

      drupal_set_message($this->t('The Taxonomy Vocabulary %vocab has been created.', ['%vocab' => $name]));
      ImportForm::loadVocabFromFile($path, $vid, $name);

    } else {
      drupal_set_message($this->t('The Taxonomy Vocabulary %vocab already exists, checking for additional terms...', ['%vocab' => $name]));
      ImportForm::loadVocabFromFile($path, $vid, $name);
    }
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['taxonomy_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Taxonomy Name'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );
    $form['machine_name'] = array(
      '#type' => 'machine_name',
      '#title' => t('Machine Name'),
      '#default_value' => '',
      '#max_length' => 255,
      '#machine_name' => array(
        'exists' => array(
          $this,
          'exists',
        ),
        'source' => array('taxonomy_name'),
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
      ),
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#size' => 60,
      '#maxlength' => 255,
    );
    $form['filename'] = array(
      '#type' => 'textfield',
      '#title' => t('Filename'),
      '#description' => t('Filename to create terms from, one element per line, working directory is taxonomy_import/src/data'),
      '#default_value' => 'IowaCounties.txt',
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );
    return $form;
  }

  function validataForm(array &$form, FormStateInterface $form_state) {
    $path = getFilePath(trim($form_state->getValues('filename')));
    if (!file_exists($path)) {
      $form_state->setErrorByName('filename', t('Error: File not Found'));
    }
  }

  function getFilePath($filename) {
    $path = $filename;
    if (substr($filename, 0, 1) != '/') {
      return DRUPAL_ROOT . '/' . drupal_get_path('module', 'taxonomy_import') . '/src/data' . '/' . $filename;
    } else {
      return $filename;
    }
  }

  function loadVocabFromFile($path, $vid, $name) {
    if($file = fopen($path, 'r')) {
      $count_added = 0;
      $count_skipped = 0;
      while(!feof($file)) {
        $term = trim(fgets($file));
        $query = \Drupal::entityQuery('taxonomy_term')->condition('vid', $vid)->condition('name', $term)->execute();
        if (count($query) < 1 && $term != NULL) {
          $term = \Drupal\taxonomy\Entity\Term::create([
            'vid' => $vid,
            'name' => $term,
          ]);
          $term->save();
          $count_added += 1;
        } else if ($term != NULL) {
          $count_skipped += 1;
        }
      }
      //Only use $this when in the form
      if (debug_backtrace()[1]['function'] == 'submitForm') {
        drupal_set_message($this->t('The Taxonomy Vocabulary %vocab added %added terms and skipped %skipped terms.', ['%vocab' => $name, '%added' => $count_added, '%skipped' => $count_skipped]));
      }
    }
  }

  /**
   * Allow any machine name, import more terms to existing
   */
  function exists($name) {
    return false;
  }
}
