<?php
/**
 * Configure the counter herein
 */
class Configuration {
  /**
   * Datasource URL for counter
   * 
   * @see http://php.net/manual/en/pdo.construct.php
   */
  public static function getDataSourceName() {
    // Example: Use a local SQLite3 database
    return 'sqlite:' . dirname(__FILE__) . '/counter.sqlite3';
    
    // Example use mySQL
    // return 'mysql:host=localhost;port=3306;dbname=demo';
  }
  
  /**
   * Datasource username
   */
  public static function getUsername() {
    return 'demo';
  }
  
  /**
   * Datasource password
   */
  public static function getPassword() {
    return 'demo';
    
    // or with some obfuscation as base64-encoded string:
    // return base64_decode('ZGVtbw==');
  }
  
  /**
   * ID of this counter
   */
  public static function getID() {
    return 1;
  }
  
  /**
   * Image Style for the Counter
   */
  public static function getStyle() {
    return 'odometer';
  }
  
  /**
   * Some styles require an extra gap, so define it here
   * (in pixels) 
   */
  public static function getExtraGap() {
    return 0;
  }
  
  /**
   *  Domain & Path where the counter is located
   */
  public static function getCounterUrl() {
    return '../';
  }
  
  /**
   * A Reload doesn't could that long (seconds).
   * 
   * Use 0 to disable this feature
   */
  public static function getReloadTimeout() {
    return 60 * 10; // 10 minutes
  }
}
?>