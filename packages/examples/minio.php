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
//--param MINIO_HOST $MINIO_HOST
//--param MINIO_PORT $MINIO_PORT
//--param MINIO_ACCESS_KEY $MINIO_ACCESS_KEY
//--param MINIO_SECRET_KEY $MINIO_SECRET_KEY
//--param MINIO_DATA_BUCKET $MINIO_DATA_BUCKET
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

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
 * generate Minio configuration from arguments
 * @param array $args the arguments
 * @return array minio configuration array
 */
function get_config($args): array
{
  $endpoint = sprintf('%s://%s:%s', 
    get_arg($args,'MINIO_PROTOCOL','http'),
    get_arg($args,'MINIO_HOST'),
    get_arg($args,'MINIO_PORT'));
  return [
    'endpoint'    => $endpoint,          
    'credentials' => new Credentials(get_arg($args, 'MINIO_ACCESS_KEY'),get_arg($args, 'MINIO_SECRET_KEY')),
    'region'      => get_arg($args, 'MINIO_REGION','us-east-1'),
    'bucket'      => get_arg($args, 'MINIO_DATA_BUCKET'),
    'bucket_endpoint' => false,
    'disable_multiregion_access_points' => true,
    'use_path_style_endpoint' => true,
  ];
}

function main(array $args): array
{
  try {
    $config = get_config($args);
    /** @var Aws\S3\S3ClientInterface $client */
    $s3Client = new S3Client($config);

    // List buckets
    $buckets = $s3Client->listBuckets();  

    return ['body' => ['buckets'=>$buckets['Buckets']] ];
  }
  catch(\Exception $ex) {
    return ['body' => ['error'=>$ex->getMessage()] ];
  }
}