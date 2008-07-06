<?php

/**
 * sfGuardUser form.
 *
 * @package    form
 * @subpackage sf_guard_user
 * @version    SVN: $Id$
 */
class sfGuardUserForm extends BasesfGuardUserForm
{
  public function configure()
  {
    unset(
      $this['last_login'],
      $this['created_at'],
      $this['salt'],
      $this['algorithm'],
      $this['is_active'],
      $this['is_super_admin'],
      $this['sf_guard_user_group_list'],
      $this['sf_guard_user_permission_list']
    );

    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password']->setOption('required', false);
    $this->widgetSchema['password_again'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password_again'] = clone $this->validatorSchema['password'];

    $this->widgetSchema->moveField('password_again', 'after', 'password');

    $this->mergePostValidator(new sfValidatorSchemaCompare('password', '==', 'password_again', array(), array('invalid' => 'The two passwords must be the same.')));

    // profile form?
    $profileFormClass = sfConfig::get('app_sf_guard_plugin_profile_class', 'sfGuardUserProfile').'Form';
    if (class_exists($profileFormClass))
    {
      $profileForm = new $profileFormClass();
      unset($profileForm[sfConfig::get('app_sf_guard_plugin_profile_field_name', 'user_id')]);

      $this->mergeForm($profileForm);
    }
  }

  public function updateObject()
  {
    parent::updateObject();

    // update defaults for profile
    if (!is_null($profile = $this->getProfile()))
    {
      $values = $this->getValues();

      $profile->fromArray($values, BasePeer::TYPE_FIELDNAME);
      $profile->save();
    }

    return $this->object;
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    // update defaults for profile
    if (!is_null($profile = $this->getProfile()))
    {
      // update defaults for the main object
      if ($this->isNew)
      {
        $this->setDefaults(array_merge($profile->toArray(BasePeer::TYPE_FIELDNAME), $this->getDefaults()));
      }
      else
      {
        $this->setDefaults(array_merge($this->getDefaults(), $profile->toArray(BasePeer::TYPE_FIELDNAME)));
      }
    }
  }

  protected function getProfile()
  {
    try
    {
      return $this->object->getProfile();
    }
    catch (sfException $e)
    {
      // no profile
      return null;
    }
  }
}
