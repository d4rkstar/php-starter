<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

//--kind php:default
//--param REDIS_URL $REDIS_URL
//--param REDIS_PREFIX $REDIS_PREFIX

$REDIS_PREFIX = '';

/***
 * Retrieve a value from arguments, using a key
 * @param array $args the array of arguments
 * @param string $arg the key of the argument to be search
 * @param string|null|int $default the default value
 * @return string
 */
function get_arg($args, $arg, $default = null): string
{
  return array_key_exists($arg, $args) ? $args[$arg] : $default;
}

/***
 * Generate a redis key using global REDIS_PREFIX
 */
function redis_key(string $key): string
{
  global $REDIS_PREFIX;
  return sprintf('%s%s', $REDIS_PREFIX, $key);
}

function main(array $args): array
{
  global $REDIS_PREFIX;
  $REDIS_URL = get_arg($args, 'REDIS_URL');
  $REDIS_PREFIX = get_arg($args, 'REDIS_PREFIX');

  $result = '';
  if (!is_null($REDIS_URL)) {
    $url_parts = parse_url($REDIS_URL);
    $host = isset($url_parts['host']) ? $url_parts['host'] : '127.0.0.1';
    $port = isset($url_parts['port']) ? $url_parts['port'] : 6379;
    $user = isset($url_parts['user']) ? $url_parts['user'] : '';
    $pass = isset($url_parts['pass']) ? $url_parts['pass'] : '';

    $redis = new Redis();
    $redis->connect($host, $port);
    if (!empty($user) && !empty($pass)) {
      $redis->auth($user, $pass);
    } elseif (empty($user) && !empty($pass)) {
      $redis->auth($pass);
    }
    $redis->set(redis_key('hello'), 'world');
    $result = $redis->get(redis_key('hello'));

  } else {
    $result = 'ERROR: REDIS_URL is not set';
  }
  return ['body' => $result];
}