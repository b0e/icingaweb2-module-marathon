<?php

namespace Icinga\Module\Marathon;

use Marathon\Common\Marathon;
use Icinga\Application\Config;

class MarathonClient
{
  protected $url;
  protected $user;
  protected $password;
  protected $port;

  public function __construct($url, $user='', $password='', $port=443)
  {
    $this->url = $url;
    $this->port = $port;

    if (!empty($user) && !empty($password)) {
      $this->user = $user;
      $this->password = $password;
    } else {
      $this->user = '';
      $this->password = '';
    }
  }


  protected function getMarathonJson($path, $query='') {
    $url = "{$this->url}/{$path}?{$query}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_PORT, $this->port);
    if (!empty($this->user) && !empty($this->password)) {
      curl_setopt($ch, CURLOPT_USERPWD, $this->user.":".$this->password);  
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url);
    $result=curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result);
    return $json;
  }

  /* 
    public function getGroups() {

      $json = $this->getMarathonJson('v2/groups');
      $objects = array();

      foreach ($json->groups as $entry) {
        $group = (object) array();
        $group->id = $entry->id;

        $id_arr = preg_split('/\//', $entry->id);
        $group->name = end($id_arr);

        $objects[] = $object = $group;
      }

      return $objects;
    }
   */

  public function getGroups($filter='') {

    $json = $this->getMarathonJson('v2/apps', $filter);
    $objects = array();

    foreach ($json->apps as $entry) {
      $app = (object) array();
      #$app->path = $entry->id;

      $group = preg_split('/\//', $entry->id);
      $app->id = $group[count($group)-2];
      if( !in_array($app,$objects)) array_push($objects,$app);
    }

    return $objects;
  }

  public function getApps($filter='') {

    $json = $this->getMarathonJson('v2/apps', $filter);
    $objects = array();

    foreach ($json->apps as $entry) {
      $app = (object) array();
      $app->id = $entry->id;

      $group = preg_split('/\//', $entry->id);
      $app->group = $group[count($group)-2];
      array_pop($group);

      $app->http = array();
      $app->https = array();
      $app->labels = array();
      $app->env = (object) array();
      foreach ($entry->labels as $key => $value) {
        if (preg_match('/_VHOST$/', $key)) {
          $this->extendArrayFromString($app->http, $value);
        } elseif (preg_match('/_SNI_SERVERNAME$/', $key)) {
          $this->extendArrayFromString($app->https, $value);
        } else {
          $app->labels[$key] = $value;
        }
      }

      foreach ($entry->env as $key => $value) {
          $app->env->$key = $value;
      }

      if (isset($app->http{0})) {
        $app->http_primary =  $app->http{0};
      }
      if (isset($app->https{0})) {
        $app->https_primary = $app->https{0};
      }

      $objects[] = $object = $app;
    }

    return $objects;
  }

  protected function extendArrayFromString(& $array, $string)
  {
    $value = preg_split('/,/', $string);
    foreach ($value as $val) {
      $array[] = $val;
    }
  }
}
