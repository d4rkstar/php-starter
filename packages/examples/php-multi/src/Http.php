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

namespace nuvolaris\phpstarter;

use GuzzleHttp\Client;

class Http
{
  public static function say(string $what): string
  {
    print "Saying $what";
    try {
      $client = new Client();
      $response = $client->post('http://httpbin.org/post', [
        'body' => $what
      ]);
      $statusCode = $response->getStatusCode();
      $statusMessage = "Server returned code $statusCode";
      print $statusMessage;
      if ($statusCode===200) {
        $content = $response->getBody()->getContents();
        print "Content is $content";
        $responseObject = json_decode($content);
        $data = $responseObject->data;
      } else {
        $data = $statusMessage;
      }
      return $data;
    } catch (\Exception $ex) {
      print "Got error: " . $ex->getTraceAsString();
      return 'Error';
    }
  }
}