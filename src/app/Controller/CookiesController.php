<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 */
class CookiesController extends AppController
{

  public $components = array('Cookie');

  public function beforeFilter()
  {
    parent::beforeFilter();
  }

  public function isAuthorized($user = null)
  {
    return true;
  }

  public function hidePanel($panel)
  {
    if (is_array($this->Cookie->read('hide_panels'))) {
      $hide_panels = $this->Cookie->read('hide_panels');
    } else {
      $hide_panels = array();
    }
    if (!in_array($panel, $hide_panels)) {
      array_push($hide_panels, $panel);
    }
    $this->Cookie->write('hide_panels', $hide_panels, true, (3600 * 24 * 720));
    $this->autoRender = false;
    $this->response = "json";
  }
  public function showPanel($panel)
  {
    if (is_array($this->Cookie->read('hide_panels'))) {
      $hide_panels = $this->Cookie->read('hide_panels');
      $item = array_search($panel, $hide_panels);
      if ($item !== false) unset($hide_panels[$item]);
      $this->Cookie->write('hide_panels', $hide_panels, true, (3600 * 24 * 720));
    }
    $this->autoRender = false;
    $this->response = "json";
  }
}
