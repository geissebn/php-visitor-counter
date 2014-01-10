<?php
require_once 'Configuration.class.php';

class Counter {

  private $db;
  
  function __construct() {
    $this->db = new PDO(Configuration::getDataSourceName(),
      Configuration::getUsername(), Configuration::getPassword());
    // Create required tables
    $this->db->exec(
      'CREATE TABLE IF NOT EXISTS counter (
      id INTEGER PRIMARY KEY,
      count INTEGER)');
    $this->db->exec(
      'CREATE TABLE IF NOT EXISTS fingerprints (
      id VARCHAR(32) PRIMARY KEY,
      counter_id INTEGER,
      timestamp INTEGER)');
  }
  
  function __destruct() {
    $this->db = NULL;
  }

  private function haveSeenFingerprintBefore() {
    $this->db->beginTransaction();
    $delete = $this->db->prepare('DELETE FROM fingerprints WHERE counter_id = :counter_id AND timestamp < :oldest');
    $delete->bindValue(':counter_id', Configuration::getId());
    $delete->bindValue(':oldest', time() - Configuration::getReloadTimeout());
    $delete->execute();
    
    $query = $this->db->prepare('SELECT timestamp FROM fingerprints WHERE id = :id AND counter_id = :counter_id');
    $query->bindValue(':counter_id', Configuration::getId());
    $query->bindValue(':id', $this->getFingerprint());
    $query->execute();
    $result = $query->fetchColumn();
    $seen = $result > 0;
    
    if (!$seen) {
      $insert = $this->db->prepare('INSERT INTO fingerprints (id, counter_id, timestamp) VALUES (:id, :counter_id, :timestamp)');
      $insert->bindValue(':id', $this->getFingerprint());
      $insert->bindValue(':counter_id', Configuration::getId());
      $insert->bindValue(':timestamp', time());
      $insert->execute();
    }
    $this->db->commit();
    return  $seen;
  } 

  private function getFingerprint() {
    $id = '';
    // HTTP Request header field which are client dependant
    $relevant = array('REMOTE_ADDR', 'HTTP_USER_AGENT', 'HTTP_DNT', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT_LANGUAGE');
    foreach ($relevant as $key) {
      if (!isset($_SERVER[$key])) {
        continue;
      }
      // seperate by slash makes it a bit more debug friendly
      $id .= $_SERVER[$key] . ' / ';
    }
    return md5($id);
  }
    
  
  private function getAndIncCounter() {
    // do it eager because that function uses a transaction too
    $seenBefore = $this->haveSeenFingerprintBefore();
    
    $this->db->beginTransaction();
    $stmt = $this->db->prepare('SELECT count FROM counter WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', Configuration::getId());
    $stmt->execute();  
    $result = $stmt->fetchColumn();
    
    // First insert or update?
    $count = $result ? $result : 0;
    if (!$seenBefore) {
      $count++;
      $updateStmt = $result
        ? 'UPDATE counter SET count = :count WHERE id = :id'
        : 'INSERT INTO counter (id, count) VALUES (:id, :count)';
      $stmt2 = $this->db->prepare($updateStmt);
      $stmt2->bindValue(':id', Configuration::getId());              
      $stmt2->bindValue(':count', $count);
      $stmt2->execute();
    } 
    $this->db->commit();   
    
    return $count;
  }

  public function asText() {
    return $this->getAndIncCounter();
  }
  
  public function asHTMLImages() {
    $count = $this->getAndIncCounter();
    $gap = Configuration::getExtraGap();
    foreach (str_split($count) as $digit) {
      echo '<img src ="' . Configuration::getCounterUrl() . 'counter/styles/' . Configuration::getStyle() . '/' . $digit . '.png" alt="' . $digit . '" style="padding-right:'. $gap . 'px" />';
    }
  }
}
?>
