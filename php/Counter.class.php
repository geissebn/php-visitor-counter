<?php
require_once 'Configuration.class.php';

class Counter {

  private $_db;
  
  function __construct() {
    $this->_db = new PDO(Configuration::getDataSourceName(),
      Configuration::getUsername(), Configuration::getPassword());
    // Create required tables
    $this->_db->exec(
      'CREATE TABLE IF NOT EXISTS counter (
      id INTEGER PRIMARY KEY,
      count INTEGER)');
    $this->_db->exec(
      'CREATE TABLE IF NOT EXISTS fingerprints (
      id VARCHAR(32) PRIMARY KEY,
      counter_id INTEGER,
      timestamp INTEGER)');
  }
  
  function __destruct() {
    $this->_db = NULL;
  }

  private function _haveSeenFingerprintBefore() {
    $this->_db->beginTransaction();
    $delete = $this->_db->prepare(
      'DELETE FROM fingerprints
      WHERE counter_id = :counter_id AND timestamp < :oldest');
    $delete->bindValue(':counter_id', Configuration::getId());
    $delete->bindValue(':oldest', time() - Configuration::getReloadTimeout());
    $delete->execute();
    
    $query = $this->_db->prepare(
      'SELECT timestamp
      FROM fingerprints
      WHERE id = :id AND counter_id = :counter_id');
    $query->bindValue(':counter_id', Configuration::getId());
    $query->bindValue(':id', $this->_getFingerprint());
    $query->execute();
    $result = $query->fetchColumn();
    $seen = $result > 0;
    
    if (!$seen) {
      $insert = $this->_db->prepare(
        'INSERT INTO fingerprints (id, counter_id, timestamp)
        VALUES (:id, :counter_id, :timestamp)');
      $insert->bindValue(':id', $this->_getFingerprint());
      $insert->bindValue(':counter_id', Configuration::getId());
      $insert->bindValue(':timestamp', time());
      $insert->execute();
    }
    $this->_db->commit();
    return  $seen;
  } 

  private function _getFingerprint() {
    $id = '';
    // HTTP Request header field which are client dependant
    $relevant = array(
      'REMOTE_ADDR', 
      'HTTP_USER_AGENT',
      'HTTP_DNT',
      'HTTP_ACCEPT_ENCODING',
      'HTTP_ACCEPT_LANGUAGE');
    foreach ($relevant as $key) {
      if (!isset($_SERVER[$key])) {
        continue;
      }
      // seperate by slash makes it a bit more debug friendly
      $id .= $_SERVER[$key] . ' / ';
    }
    return md5($id);
  }
    
  
  private function _getAndIncCounter() {
    // do it eager because that function uses a transaction too
    $seenBefore = $this->_haveSeenFingerprintBefore();
    
    $this->_db->beginTransaction();
    $stmt = $this->_db->prepare(
      'SELECT count
      FROM counter
      WHERE id = :id LIMIT 1');
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
      $stmt2 = $this->_db->prepare($updateStmt);
      $stmt2->bindValue(':id', Configuration::getId());              
      $stmt2->bindValue(':count', $count);
      $stmt2->execute();
    } 
    $this->_db->commit();   
    
    return $count;
  }

  public function asText() {
    return $this->_getAndIncCounter();
  }
  
  private function _findRelativeImageDirectoryURL() {
    // How are calling script and this script related?
    $thisDir =  realpath(dirname(__FILE__)) . '/';
    $callingDir = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/';
    $dirPrefix = '';
    if ($callingDir != $thisDir) {
      // Cut of the common prefix part
      while (strlen($callingDir) > 0 && strlen($thisDir) > 0 && $callingDir[0] == $thisDir[0]) {
        $callingDir = substr($callingDir, 1);
        $thisDir = substr($thisDir, 1);
      }
      
      if (strlen($callingDir) == 0) {
        // This script was called from a parent directory
        if ($thisDir[0] == '/') {
          $thisDir = substr($thisDir, 1);
        }
        $dirPrefix = $thisDir;
      } else {
        // This script was called by an 'brother' directory (same ancestor)
        // -> go one directeory up for each '/' found in remaining $callingDir
        for ($i = 0; $i < substr_count($callingDir, '/'); $i++) {
          $dirPrefix .= '../';
        }
        // -> and then go down
        $dirPrefix .= $thisDir;
      }
    }
    return $dirPrefix . 'styles/' . Configuration::getStyle() . '/';
  }
  
  public function asHTMLImages() {
    $count = $this->_getAndIncCounter();
    $gap = Configuration::getExtraGap();
    foreach (str_split($count) as $digit) {
      echo '<img src ="' . $this->_findRelativeImageDirectoryURL() . $digit . '.png" alt="'
        . $digit . '" style="padding-right:'. $gap . 'px" />';
    }
  }
}
?>
