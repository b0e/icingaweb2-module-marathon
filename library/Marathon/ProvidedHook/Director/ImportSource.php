<?php

namespace Icinga\Module\Marathon\ProvidedHook\Director;

use Icinga\Module\Director\Hook\ImportSourceHook;
use Icinga\Module\Director\Web\Form\QuickForm;
use Icinga\Module\Marathon\MarathonClient;
#use Icinga\Module\Marathon\MarathonKey;
use Icinga\Application\Benchmark;

class ImportSource extends ImportSourceHook
{
  protected $db;

  public function fetchData()
  {
    $client = new MarathonClient(
      $this->getSetting('marathon_url'),
      $this->getSetting('marathon_user'),
      $this->getSetting('marathon_password'),
      $this->getSetting('marathon_port')
    );

    switch ($this->getObjectType()) {
    case 'marathon_apps':
      return $client->getApps($this->getSetting('marathon_filter'));
    case 'marathon_groups':
      return $client->getGroups($this->getSetting('marathon_filter'));
    }
  }

  protected function getObjectType()
  {
    $type = $this->getSetting('object_type', 'marathon_apps');
    if (! in_array($type, array('marathon_groups', 'marathon_apps'))) {
      throw new ConfigurationError(
        'Got no invalid Marathon object type: "%s"',
        $type
      );
    }

    return $type;
  }


  public function listColumns()
  {
    switch ($this->getObjectType()) {
    case 'marathon_apps':
      return array(
        'id',
        'http',
        'http_primary',
        'https',
        'https_primary',
        'group',
        'labels',
        'env'
      );
    case 'marathon_groups':
      return array(
        'id',
        'path'
      );
    }
  }

  public static function getDefaultKeyColumnName()
  {
    return 'id';
  }

  public static function addSettingsFormFields(QuickForm $form)
  {
    $form->addElement('text', 'marathon_url', array(
      'label'        => 'Marathon url',
      'required'     => true,
    )
  );
    $form->addElement('text', 'marathon_port', array(
      'label'        => 'Marathon port',
      'required'     => false,
    )
  );
    $form->addElement('text', 'marathon_user', array(
      'label'        => 'Marathon user',
      'required'     => false,
    )
  );
    $form->addElement('text', 'marathon_password', array(
      'label'        => $form->translate('Marathon password'),
      'required'     => false,
    )
  );

    $form->addElement('select', 'object_type', array(
      'label'        => 'Object type',
      'required'     => true,
      'description'  => $form->translate(
        'Marathon object type'
      ),
      'multiOptions' => $form->optionalEnum(
        static::enumObjectTypes($form)
      ),
      'class'        => 'autosubmit',
    ));

    $form->addElement('text', 'marathon_filter', array(
      'label'        => $form->translate('Marathon filter'),
      'required'     => false,
      'description'  => $form->translate(
        'You can use any filter like described in the Marathon API for <a href="https://mesosphere.github.io/marathon/docs/rest-api.html#get-v2apps">GET /v2/apps</a>.'
      ),
    )
  );
  }

  protected static function enumObjectTypes($form)
  {
    return array(
      'marathon_groups' => $form->translate('Marathon groups'),
      'marathon_apps'   => $form->translate('Marathon apps'),
    );
  }
}
