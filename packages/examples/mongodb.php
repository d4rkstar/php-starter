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
//--param MONGODB_URL $MONGODB_URL

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

function main(array $args): array
{
   
    try {
        $url_parts = parse_url(get_arg($args, 'MONGODB_URL'));
        $url_parts['database'] = str_replace('/','', $url_parts['path']);
        $auth = '';
        if (get_arg($url_parts,'user')) {
            $auth=sprintf('%s:%s@',$url_parts['user'],$url_parts['pass']);
        }
        $url = sprintf('mongodb://%s%s:%s/%s?%s',
            $auth,
            $url_parts['host'],
            $url_parts['port'],
            $url_parts['database'],
            $url_parts['query']
        );
        $namespace = sprintf("%s.%s", $url_parts['database'],'data');
        $mongoManager = new  MongoDB\Driver\Manager($url,['username'=>$url_parts['user'],'password'=>$url_parts['pass']]);

        $document = ['hello' => 'world'];

        $bulkWrite = new MongoDB\Driver\BulkWrite;
        $bulkWrite->insert($document);
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $mongoManager->executeBulkWrite($namespace, $bulkWrite, $writeConcern);


        $filter = [];
        $query = new MongoDB\Driver\Query($filter);
        $cursor = $mongoManager->executeQuery($namespace, $query);
        $res = $cursor->toArray();

        $bulkDelete = new MongoDB\Driver\BulkWrite;
        $bulkDelete->delete([], ['limit' => 0]);
        $mongoManager->executeBulkWrite($namespace, $bulkDelete);

        return ['body' => $res];

    } catch (\Exception $ex) {
        return ['body' => ['error' => $ex->getMessage()]];
    }
}