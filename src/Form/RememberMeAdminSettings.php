<?php

namespace Drupal\remember_me\Form;

/**
 * @file
 * Contains \Drupal\remember_me\Form\RememberMeAdminSettings.
 */

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\SessionConfiguration;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the site configuration form.
 */
class RememberMeAdminSettings extends ConfigFormBase {

  /**
  * {@inherithoc}
  */
  public function getFormId(){
    return 'remember_me_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'remember_me.settings'
    ];
  }

  function build_options(array $time_intervals, $granularity = 2, $langcode = NULL) {
    $callback = function ($value) use ($granularity, $langcode) {
    return \Drupal::service('date.formatter')->formatInterval($value, $granularity, $langcode);
    };
    return array_combine($time_intervals, array_map($callback, $time_intervals));
  }

  /**
  * {@inherithoc}
  */
  public function buildForm(array $form,FormStateInterface $form_state){
    $config = $this->config('remember_me.settings');
    $account = \Drupal::currentUser();
    $intervals = array(3600, 10800, 21600, 43200, 86400, 172800, 259200, 604800, 1209600, 2592000, 5184000, 7776000, 15552000, 31104000);
  	$options = $this->build_options($intervals);
    $userData = \Drupal::service('user.data');
    $remember_me_data = $userData->get('remember_me', $account->id(), 'UserKeys');
    $value = (ini_get('session.cookie_lifetime'));
    $format = \Drupal::service('date.formatter')->formatInterval($value, 2, NULL);
    $vars = array(
      'remember' => array(
        '#type' => 'item',
        '#title' => t('Remember me'),
        '#value' => isset($remember_me_data) ? t('Yes') : t('No'),
        '#description' => t('Current user chose at log in.'),
      ),
      'session' => array(
        '#type' => 'item',
        '#title' => t('Session lifetime'),
        '#value' => $format,
        '#description' => t('Currently configured session cookie lifetime.'),
      ),
    );
    $form['legend'] = array(
      '#theme' => 'remember_me_settings_display',
      '#vars' => $vars,
    );
    $form['remember_me_managed'] = array(
      '#type' => 'checkbox',
      '#title' => t('Manage session lifetime'),
      '#default_value' => $config->get('remember_me_managed'),
      '#description' => t('Choose to manually overwrite the configuration value from settings.php.'),
    );
    $form['remember_me_lifetime'] = array(
      '#type' => 'select',
      '#title' => t('Lifetime'),
      '#default_value' => $config->get('remember_me_lifetime'),
      '#options' => $options,
      '#description' => t('Duration a user will be remembered for. This setting is ignored if Manage session lifetime (above) is disabled.'),
    );
    $form['remember_me_checkbox'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remember me field'),
      '#default_value' => $config->get('remember_me_checkbox'),
      '#description' => t('Default state of the "Remember me" field on the login forms.'),
    );
    $form['remember_me_checkbox_visible'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remember me field visible'),
      '#default_value' => $config->get('remember_me_checkbox_visible'),
      '#description' => t('Should the "Remember me" field be visible on the login forms.'),
    );
      return parent::buildForm($form, $form_state);
    }
      
  /**
  * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    \Drupal::configFactory()->getEditable('remember_me.settings')
    // Set the submitted configuration setting
    ->set('remember_me_managed', $form_state->getValue('remember_me_managed'))
    ->set('remember_me_lifetime', $form_state->getValue('remember_me_lifetime'))
    ->set('remember_me_checkbox', $form_state->getValue('remember_me_checkbox'))
    ->set('remember_me_checkbox_visible', $form_state->getValue('remember_me_checkbox_visible'))
    ->save();
    parent::submitForm($form, $form_state);
  }
}	