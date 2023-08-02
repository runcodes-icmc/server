<?php
App::uses('AppModel', 'Model');
/**
 * Commit Model
 *
 * @property User $User
 * @property ExerciseCase $ExerciseCase
 */
class Log extends AppModel
{

  public $order = "datetime DESC";

  public function __construct($id = false, $table = null, $ds = null)
  {
    parent::__construct($id, $table, $ds);
  }

  public static function register($message, $user = null)
  {
    $model = new Log();
    $log = array();
    $log['Log']['action'] = $message;
    $log['Log']['user_email'] = isset($user['email']) ? $user['email'] : "SYSTEM";
    $log['Log']['ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0";
    if (!$model->save($log)) {
      $log = array();
      $log['Log']['action'] = "Error on register a log message";
      $log['Log']['user_email'] = "SYSTEM";
      $log['Log']['ip'] = "localhost";
      $model->save($log);
    }
  }

  public static function slackNotification($title, $message, $links, $color = "warning")
  {
    App::uses('HttpSocket', 'Network/Http');
    $data = array();
    $payload = array();

    $title = str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("&", "&amp;", $title)));
    $message = str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("&", "&amp;", $message)));
    $data["pretext"] = "Notificação enviada de <https://run.codes>";
    $data["fallback"] = $title . "\n" . $message;
    $data["color"] = $color;
    foreach ($links as $link) {
      $message .= " " . "<" . $link . ">";
    }
    $data["title"] = $title;
    $data["text"] = $message;

    $payload["attachments"][0] = $data;

    // $HttpSocket = new HttpSocket();
    // $results = $HttpSocket->post(
    //   'https://hooks.slack.com/services/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',
    //   'payload=' . json_encode($payload)
    // );
  }

  public static function registerException($e, $file = null, $method = null)
  {
    self::register($file . "(method: " . $method . ") ERROR[" . $e->getCode() . "] " . $e->getMessage());
  }

  public static function registerEvent($metric_name, $dimension_name, $dimension_value)
  {
  }
}
