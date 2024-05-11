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

//--web true
//--kind php:default
//--param POSTGRES_URL $POSTGRES_URL

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

function get_create_table() : string {
  $sql=<<<SQL
  CREATE TABLE IF NOT EXISTS nuvolaris_table (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      message varchar(100)        
  );
SQL;
    return $sql;
}

function main(array $args): array
{
  try {
    $url_parts = parse_url(get_arg($args,'POSTGRES_URL'));
    $url_parts['db'] = str_replace('/','', $url_parts['path']);
    $url_parts['port'] = get_arg($url_parts,'port',5432);
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $url_parts['host'],$url_parts['port'],$url_parts['db']);


    $pdo = new PDO($dsn, $url_parts['user'], $url_parts['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');
    $pdo->exec(get_create_table());

    $message = "Nuvolaris Postgres is up and running!";
    $stmt = $pdo->prepare('INSERT INTO nuvolaris_table (message) VALUES (:message)');    
    $stmt->bindParam(':message', $message, PDO::PARAM_STR); 
    $stmt->execute();

    $stmt = $pdo->prepare('SELECT * FROM nuvolaris_table');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->exec('DROP table nuvolaris_table');
    return ['body' => $rows ];
  }
  catch(\PDOException $ex) {
    return ['body' => ['error'=>'Error in connection: ' . $ex->getMessage()] ];
  }
  catch(\Exception $ex) {
    return ['body' => ['error'=>$ex->getMessage()] ];
  }
}
