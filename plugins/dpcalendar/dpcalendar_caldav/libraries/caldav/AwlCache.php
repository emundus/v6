<?php

/**
 * A simple Memcached wrapper supporting namespacing of stored values.
 *  
 * @author Andrew McMillan
 * @license LGPL v2 or later
 */

class AwlCache {
  private static $m;
  private static $servers;
  private static $working;

  /**
   * Initialise the cache connection. We use getpid() to give us a persistent connection.
   */
  function __construct() {
    global $c;

    if ( isset(self::$working) ) return;

    self::$working = false;
    if ( isset($c->memcache_servers) && class_exists('Memcached') ) {
      dbg_error_log('Cache', 'Using Memcached interface connection');
      self::$servers = $c->memcache_servers;
      self::$m = new Memcached();
      foreach( self::$servers AS $v ) {
        dbg_error_log('Cache', 'Adding server '.$v);
        $server = explode(',',$v);
        if ( isset($server[2]) )
          self::$m->addServer($server[0],$server[1],$server[2]);
        else
          self::$m->addServer($server[0],$server[1]);
      }
      self::$working = true;
      // Hack to allow the regression tests to flush the cache at start
      if ( isset($_SERVER['HTTP_X_DAVICAL_FLUSH_CACHE'])) $this->flush();
    }
    else {
      dbg_error_log('Cache', 'Using NoCache dummy interface');
    }
  }

  /**
   * So we can find out if we are actually using the cache.
   */
  function isActive() {
    return self::$working;
  }

  /**
   * Construct a string from the namespace & key
   * @param unknown_type $namespace
   * @param unknown_type $key
   */
  private function nskey( $namespace, $key ) {
    return str_replace(' ', '%20', $namespace . (isset($key) ? '~~' . $key: '')); // for now.
  }

  /**
   * get a value from the specified namespace / key
   * @param $namespace
   * @param $key
   */
  function get( $namespace, $key ) {
    if ( !self::$working ) return false;
    $ourkey = self::nskey($namespace,$key);
    $value = self::$m->get($ourkey);
//    var_dump($value);
//    if ( $value !== false ) dbg_error_log('Cache', 'Got value for cache key "'.$ourkey.'" - '.strlen(serialize($value)).' bytes');
    return $value;
  }

  /**
   * Set a value for the specified namespace/key, perhaps with an expiry (default 10 days)
   * @param $namespace
   * @param $key
   * @param $value
   * @param $expiry
   */
  function set( $namespace, $key, $value, $expiry=864000 ) {
    if ( !self::$working ) return false;
    $ourkey = self::nskey($namespace,$key);
    $nskey = self::nskey($namespace,null);
    $keylist = self::$m->get( $nskey, null, $cas_token );
    if ( isset($keylist) && is_array($keylist) ) {
      if ( !isset($keylist[$ourkey]) ) {
        $keylist[$ourkey] = 1;
        $success = self::$m->cas( $cas_token, $nskey, $keylist );
        $i=0;
        while( !$success && $i++ < 10 && self::$m->getResultCode() == Memcached::RES_DATA_EXISTS ) {
          $keylist = self::$m->get( $nskey, null, $cas_token );
          if ( $keylist === false ) return false;
          if ( isset($keylist[$ourkey]) ) break;
          $keylist[$ourkey] = 1;
          $success = self::$m->cas( $cas_token, $nskey, $keylist );
        }
        if ( !$success ) return false;
      }
    } 
    else {
      $keylist = array( $ourkey => 1 );      
      self::$m->set( $nskey, $keylist );
    }
//    var_dump($value);
//    dbg_error_log('Cache', 'Setting value for cache key "'.$ourkey.'" - '.strlen(serialize($value)).' bytes');
    return self::$m->set( $ourkey, $value, $expiry );
  }

  /**
   * Delete a value from a namespace/key, or for everything in a namespace if a 'null' key is supplied.
   * @param $namespace
   * @param $key
   */
  function delete( $namespace, $key ) {
    if ( !self::$working ) return false;
    $nskey = self::nskey($namespace,$key);
    dbg_error_log('Cache', 'Deleting from cache key "'.$nskey.'"');
    if ( isset($key) ) {
      self::$m->delete( $nskey );
    }
    else {
      $keylist = self::$m->get( $nskey, null, $cas_token );
      if ( isset($keylist) ) {
      self::$m->delete( $nskey );
        if ( is_array($keylist) ) {
          foreach( $keylist AS $k => $v ) self::$m->delete( $k );
        } 
      }
    }
  }

  /**
   * Flush the entire cache
   */
  function flush( ) {
    if ( !self::$working ) return false;
    dbg_error_log('Cache', 'Flushing cache');
    self::$m->flush();
  }

  
  /**
   * Acquire a lock on something
   */
  function acquireLock( $something, $wait_for = 5 ) {
    if ( !self::$working ) return $something;
    $wait_until = time() + $wait_for;
    while( self::$m->add('_lock_'+$something,1,5) === false && time() < $wait_until ) {
      usleep(10000);
    }
    return $something;
  }

  
  /**
   * Release a lock
   */
  function releaseLock( $something ) {
    if ( !self::$working ) return;
    self::$m->delete('_lock_'+$something);        
  }
}


function getCacheInstance() {
  static $ourCacheInstance;

  if ( !isset($ourCacheInstance) ) $ourCacheInstance = new AWLCache('Memcached'); 

  return $ourCacheInstance;
}
